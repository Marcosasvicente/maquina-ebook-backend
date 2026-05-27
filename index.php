const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const qrcode = require('qrcode-terminal');
const Groq = require('groq-sdk');
const express = require('express');
const pino = require('pino');

// ─────────────────────────────────────────
// CONFIGURAÇÕES
// ─────────────────────────────────────────
const GROQ_API_KEY = process.env.GROQ_API_KEY || 'SUA_CHAVE_GROQ_AQUI';
const LINK_COMPRA  = process.env.LINK_COMPRA  || 'https://SEU-LINK-KIWIFY.com';

const groq = new Groq({ apiKey: GROQ_API_KEY });
const historico = {};

// ─────────────────────────────────────────
// PROMPT DO AGENTE
// ─────────────────────────────────────────
const SYSTEM_PROMPT = `Você é a Sofia, consultora especialista em relacionamentos e psicologia comportamental.
Você representa o ebook "Como Fazer Alguém Pensar em Você Obsessivamente".

PRODUTO:
- Nome: Como Fazer Alguém Pensar em Você Obsessivamente
- Preço: R$ 47,00 (de R$ 97,00) — pagamento único
- Formato: Ebook digital com acesso imediato por e-mail
- Garantia: 7 dias com devolução de 100% sem perguntas
- Link de compra: ${LINK_COMPRA}

O QUE A PESSOA VAI APRENDER:
• Os 7 gatilhos psicológicos que fazem alguém pensar em você sem parar
• Como usar o silêncio estratégico a seu favor
• A técnica do "vazio emocional" que desperta saudade instantânea
• Por que perseguir afasta — e como inverter esse padrão
• O segredo da escassez: como se tornar raro e valioso
• Frases e comportamentos que ativam obsessão saudável
• Como recuperar o controle emocional da situação

ERROS COMUNS QUE O EBOOK RESOLVE:
• Estar sempre disponível (mata o desejo)
• Mandar mensagens demais (demonstra ansiedade)
• Tentar explicar sentimentos com lógica (gera pena, não atração)
• Pedir uma nova chance (diminui seu valor)
• Stalkear redes sociais (valida o afastamento)

OBJEÇÕES E RESPOSTAS:
- "É caro" → Menos que um jantar. Tem garantia de 7 dias. Investimento em você mesma(o).
- "Funciona mesmo?" → Baseado em psicologia comportamental. Se não funcionar, devolução garantida.
- "É manipulação?" → Não. É entender como a atração funciona de forma genuína e saudável.
- "Não tenho experiência?" → Escrito em linguagem simples. Qualquer pessoa aplica hoje.

REGRAS:
1. Seja acolhedora e empática — a pessoa está sofrendo
2. Primeiro OUÇA a situação, depois ofereça a solução
3. Mensagens curtas (máximo 3 parágrafos)
4. Use emojis com moderação (1-2 por mensagem)
5. Linguagem natural, como uma amiga que entende do assunto
6. Ofereça o link quando a pessoa demonstrar interesse
7. Nunca prometa resultados garantidos — use "pode te ajudar", "muitas pessoas relatam"`;

// ─────────────────────────────────────────
// GERAR RESPOSTA COM IA
// ─────────────────────────────────────────
async function gerarResposta(numero, mensagem) {
  if (!historico[numero]) historico[numero] = [];

  historico[numero].push({ role: 'user', content: mensagem });

  if (historico[numero].length > 20) {
    historico[numero] = historico[numero].slice(-20);
  }

  try {
    const completion = await groq.chat.completions.create({
      model: 'llama-3.3-70b-versatile',
      messages: [
        { role: 'system', content: SYSTEM_PROMPT },
        ...historico[numero],
      ],
      max_tokens: 400,
      temperature: 0.75,
    });

    const resposta = completion.choices[0].message.content;
    historico[numero].push({ role: 'assistant', content: resposta });
    return resposta;
  } catch (erro) {
    console.error('Erro na IA:', erro.message);
    return 'Oi! Tive um probleminha aqui. Pode repetir? 😊';
  }
}

// ─────────────────────────────────────────
// WHATSAPP COM BAILEYS
// ─────────────────────────────────────────
async function iniciarWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState('./sessao');
  const { version } = await fetchLatestBaileysVersion();

  const sock = makeWASocket({
    version,
    auth: state,
    logger: pino({ level: 'silent' }),
    printQRInTerminal: false,
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', ({ connection, lastDisconnect, qr }) => {
    if (qr) {
      console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
      console.log('📱 ESCANEIE O QR CODE NO WHATSAPP:');
      console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
      qrcode.generate(qr, { small: true });
      console.log('\nWhatsApp → ⋮ → Dispositivos conectados → Conectar dispositivo\n');
    }

    if (connection === 'close') {
      const deveReconectar = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
      console.log('⚠️  Conexão encerrada. Reconectando:', deveReconectar);
      if (deveReconectar) {
        setTimeout(iniciarWhatsApp, 5000);
      }
    }

    if (connection === 'open') {
      console.log('✅ AGENTE DE VENDAS ATIVO E RESPONDENDO!');
    }
  });

  sock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (type !== 'notify') return;

    for (const msg of messages) {
      // Ignora mensagens próprias, grupos e broadcasts
      if (msg.key.fromMe) return;
      if (msg.key.remoteJid === 'status@broadcast') return;
      if (msg.key.remoteJid.endsWith('@g.us')) return;

      const numero = msg.key.remoteJid;
      const texto = msg.message?.conversation
        || msg.message?.extendedTextMessage?.text
        || '';

      if (!texto.trim()) return;

      console.log(`📩 [${new Date().toLocaleTimeString('pt-BR')}] ${numero}: ${texto}`);

      // Simula digitando
      await sock.sendPresenceUpdate('composing', numero);

      const resposta = await gerarResposta(numero, texto);

      // Delay humano
      const delay = 1500 + Math.random() * 2000;
      await new Promise((r) => setTimeout(r, delay));

      await sock.sendMessage(numero, { text: resposta });
      console.log(`💬 Bot → ${numero}: ${resposta.substring(0, 80)}...`);
    }
  });
}

// ─────────────────────────────────────────
// SERVIDOR HTTP (para Railway manter ativo)
// ─────────────────────────────────────────
const app = express();
app.get('/', (req, res) => {
  res.json({ status: 'online', agente: 'Agente de Vendas WhatsApp', uptime: Math.floor(process.uptime()) + 's' });
});
app.listen(process.env.PORT || 3000, () => {
  console.log('🌐 Servidor HTTP ativo na porta', process.env.PORT || 3000);
});

// Inicia
console.log('🚀 Iniciando agente de vendas...');
iniciarWhatsApp();
