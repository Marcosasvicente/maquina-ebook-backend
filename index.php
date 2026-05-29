<?php
/**
 * INDEX.PHP - PREMIUM V11 (ROTA BLINDADA E LIMPA)
 * Correção de fluxo direto sem loops ou includes indesejados
 */

ini_set('max_execution_time', 600);
set_time_limit(600);

// Configuração da API GROQ
$apiKey = trim(getenv('GROQ_API_KEY') ?: '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');

$sugestoes = null;
$ebook_html = null;
$erro = null;

$videoUrl = $_POST['videoUrl'] ?? $_POST['chosen_url'] ?? '';
$textoBase = $_POST['textoBase'] ?? '';

// Captura de Transcrição
if (!empty($videoUrl) && empty($textoBase)) {
    preg_match("/(?:v=|\/)([a-zA-Z0-9_-]{11})/", $videoUrl, $matches);
    $videoId = $matches[1] ?? null;
    if ($videoId) {
        $transcriptData = @file_get_contents("https://subtitles-youtube.vercel.app/api/transcript?videoId=" . $videoId);
        $transcript = json_decode($transcriptData, true);
        if ($transcript && is_array($transcript)) {
            foreach ($transcript as $line) { $textoBase .= $line['text'] . " "; }
        }
    }
}

// PASSO 1: Gerar "Big Ideas" e Títulos Magnéticos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias']) && !empty($videoUrl)) {
    $promptIdeias = "Com base neste conteúdo: '$textoBase', crie 5 títulos para Ebooks Premium. 
    REGRAS: 
    1. Use o conceito de 'Mecanismo Único' (Dê nomes fortes como 'O Protocolo X', 'O Método Y').
    2. Promessa Inédita e Específica para problemas dolorosos.
    Escreva apenas os títulos, um por linha.";
    
    $payload = ["model" => "llama-3.3-70b-versatile", "messages" => [["role" => "user", "content" => $promptIdeias]], "temperature" => 0.8];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    $linhas = explode("\n", str_replace("\r", "", trim($result['choices'][0]['message']['content'] ?? '')));
    foreach ($linhas as $linha) {
        $linhaLimpa = preg_replace('/^[0-9]+[\.\)\s\-]+/', '', trim($linha));
        if (!empty($linhaLimpa)) $sugestoes[] = $linhaLimpa;
    }
    curl_close($ch);
}

// PASSO 2: Geração do Ebook seguindo RIGOROSAMENTE o Checklist Premium
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook']) && !empty($_POST['tema_escolhido'])) {
    $tema = $_POST['tema_escolhido'];
    
    $promptEbook = "Aja como um Ghostwriter High-Ticket e Especialista em Psicologia Dark. 
    Escreva um EBOOK PREMIUM em HTML sobre o tema: '$tema'. 
    
    CHECKLIST OBRIGATÓRIO DE ESTRUTURA:
    1. BIG IDEA: O conteúdo deve apresentar uma oportunidade inédita, nunca vista no YouTube.
    2. STORYTELLING: Inicie cada capítulo com um breve estudo de caso ou análise comportamental profunda (o 'porquê' biológico).
    3. RETENÇÃO: Use 'Open Loops' ao final de cada capítulo (instigue o que vem no próximo).
    4. ESCANEABILIDADE: Frases curtas, muitos subtítulos (h3) e termos em negrito estratégicos.
    5. CONTEÚDO ACIONÁVEL: Cada capítulo DEVE terminar com um 'PLANO DE AÇÃO: Passo a Passo' e um 'SCRIPT PRONTO' para aplicação.
    6. INIMIGO COMUM: Ataque o senso comum e conselhos fracos durante o texto.
    7. GRAND FINALE: Conclusão com ponte para o futuro e um CTA forte para o próximo nível (Mentoria/VIP).

    FORMATO: Use <h2> para capítulos, <h3> para subtítulos. Não use Markdown (como **), use tags HTML.";

    $payload = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [["role" => "user", "content" => $promptEbook]],
        "temperature" => 0.7,
        "max_tokens" => 8000
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 400);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    $ebook_html = $result['choices'][0]['message']['content'] ?? "Erro na geração.";
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>EbookForge Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">
</head>
<body class="bg-[#0a0a0a] text-white py-12">
    <div class="max-w-4xl mx-auto px-6">
        <h1 class="text-5xl font-black text-[#FF0000] mb-12 text-center" style="font-family: 'Montserrat';">EBOOKFORGE <span class="text-white">PREMIUM</span></h1>
        
        <!-- Formulário do Vídeo -->
        <form method="POST" class="mb-10">
            <input type="hidden" name="buscar_ideias" value="1">
            <div class="flex gap-2">
                <input type="url" name="videoUrl" required value="<?= htmlspecialchars($videoUrl) ?>" placeholder="URL do Vídeo Base" class="flex-1 bg-zinc-900 border border-zinc-800 p-4 rounded-xl focus:border-red-600 outline-none text-white">
                <button type="submit" class="bg-red-600 px-8 py-4 rounded-xl font-bold hover:bg-red-700 transition">ANALISAR</button>
            </div>
        </form>

        <!-- Sugestões de Títulos -->
        <?php if ($sugestoes): ?>
            <div class="grid gap-4">
                <h2 class="text-xl font-bold text-zinc-400 uppercase tracking-widest">Escolha a sua Big Idea:</h2>
                <?php foreach ($sugestoes as $opcao): ?>
                <form method="POST">
                    <input type="hidden" name="criar_ebook" value="1">
                    <input type="hidden" name="chosen_url" value="<?= htmlspecialchars($videoUrl) ?>">
                    <input type="hidden" name="textoBase" value="<?= htmlspecialchars($textoBase) ?>">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($opcao) ?>">
                    <button type="submit" class="w-full text-left p-6 bg-zinc-900 border border-zinc-800 rounded-2xl hover:border-red-600 transition-all font-bold text-lg text-white">
                        <?= htmlspecialchars($opcao) ?>
                    </button>
                </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Caixa de Sucesso com Envio Limpo e Seguro -->
        <?php if ($ebook_html && $ebook_html !== "Erro na geração."): ?>
            <div class="mt-8 p-8 border border-red-950 bg-zinc-950 rounded-2xl text-center shadow-2xl">
                <div class="w-12 h-12 border-4 border-t-red-600 border-zinc-800 rounded-full animate-spin mx-auto mb-4"></div>
                <h2 class="text-xl font-black text-white uppercase tracking-wider mb-2">Conteúdo Premium Compilado!</h2>
                <p class="text-zinc-400 text-sm mb-6">Pronto para receber a diagramação e as imagens cinematográficas.</p>
                
                <!-- Envio Direto via POST padrão sem interceptação de JavaScript -->
                <form action="diagramador.php" method="POST">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($tema) ?>">
                    <textarea name="ebook_html" class="hidden"><?= htmlspecialchars($ebook_html) ?></textarea>
                    <button type="submit" class="w-full bg-red-600 text-white p-5 rounded-xl font-black uppercase tracking-wider text-base hover:bg-red-700 transition shadow-lg shadow-red-900/40 block">
                        ABRIR NO DIAGRAMADOR DARK NOW →
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
