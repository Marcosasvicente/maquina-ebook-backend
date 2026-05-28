<?php
// --- CONFIGURAÇÃO DE SEGURANÇA E CHAVE API ---
$apiKey = trim(getenv('GROQ_API_KEY') ?: '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');

$sugestoes = null;
$ebook_html = null;
$erro = null;

// Captura as variáveis enviadas para persistir o estado entre os cliques
$videoUrl = $_POST['videoUrl'] ?? $_POST['chosen_url'] ?? '';
$textoBase = $_POST['textoBase'] ?? '';

// Função para buscar a transcrição caso ainda não exista no formulário
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

// PASSO 1: O usuário enviou a URL e quer as 5 sugestões virais
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias']) && !empty($videoUrl)) {
    $promptIdeias = "Com base na transcrição deste vídeo, identifique o assunto central e crie 5 títulos de Ebooks Relacionados com altíssimo potencial de cliques e apelo viral para o público geral. Retorne APENAS um array JSON válido no seguinte formato formatado em texto puro:
    [\"Opção 1\", \"Opção 2\", \"Opção 3\", \"Opção 4\", \"Opção 5\"]
    Transcrição: $textoBase";

    $payload = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [["role" => "user", "content" => $promptIdeias]],
        "temperature" => 0.8
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $jsonTexto = $result['choices'][0]['message']['content'] ?? '';
        // Limpa possíveis marcações de markdown que quebrem o JSON
        $jsonTexto = str_replace(['```json', '```'], '', $jsonTexto);
        $sugestoes = json_decode(trim($jsonTexto), true);
    } else {
        $erro = "Erro ao gerar sugestões (HTTP $httpCode).";
    }
}

// PASSO 2: O usuário clicou em uma das 5 opções e quer gerar o Ebook dela
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook']) && !empty($_POST['tema_escolhido'])) {
    $temaEscolhido = $_POST['tema_escolhido'];

    $promptEbook = "Aja como um Editor de Infoprodutos Senior. Desenvolva um EBOOK COMPLETO formatado estritamente em HTML baseado no seguinte tema escolhido pelo usuário: '$temaEscolhido'. Use o contexto do conteúdo complementar: $textoBase.
    
    REGRAS CRÍTICAS:
    - O público-alvo é UNIVERSAL.
    - ESTRUTURA: Uma introdução envolvente + EXATAMENTE 20 CAPÍTULOS DISTINTOS E DETALHADOS + Uma conclusão forte.
    - FORMATO: Use títulos h1 e h2 com a cor #5D2A18, parágrafos formatados com a tag <p>. Não use tags de bloco markdown.";

    $payload = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [["role" => "user", "content" => $promptEbook]],
        "temperature" => 0.6,
        "max_tokens" => 5000 // Garante espaço para os 20 capítulos
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $ebook_html = str_replace(['```html', '
```', '**'], ['', '', '<b>'], $result['choices'][0]['message']['content']);
    } else {
        $erro = "Erro ao forjar o ebook completo (HTTP $httpCode).";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>EbookForge V3 - Laboratório Estratégico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .prose h1, .prose h2 { color: #5D2A18; font-weight: bold; margin-top: 25px; font-size: 1.6rem; }
        .prose p { margin-bottom: 15px; line-height: 1.7; color: #333; }
    </style>
</head>
<body class="bg-[#fdfaf7] py-10 px-4">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-xl border border-stone-200">
        <h1 class="text-4xl font-extrabold text-[#5D2A18] mb-2 text-center">EbookForge V3</h1>
        <p class="text-center text-stone-500 mb-8">Estratégia Viral: Analise, Escolha e Forje</p>
        
        <!-- Formulário Inicial: URL do Vídeo -->
        <form method="POST" class="space-y-4 mb-8">
            <input type="hidden" name="buscar_ideias" value="1">
            <label class="block font-semibold text-stone-700">Link do Vídeo Base do YouTube:</label>
            <input type="url" name="videoUrl" required value="<?php echo htmlspecialchars($videoUrl); ?>" placeholder="Cole o link do YouTube aqui..." 
                   class="w-full p-4 border-2 border-stone-200 rounded-xl focus:border-[#5D2A18] outline-none transition-all">
            <button type="submit" class="w-full bg-[#5D2A18] text-white p-4 rounded-xl font-bold text-lg hover:brightness-110 transition-all shadow-lg">
                ANALISAR VÍDEO E EXTRAIR IDÉIAS
            </button>
        </form>

        <!-- Painel de Seleção das 5 Sugestões Virais -->
        <?php if (!empty($sugestoes) && is_array($sugestoes)): ?>
            <div class="mt-6 p-6 bg-stone-50 rounded-xl border-l-4 border-amber-600 animate-fade-in">
                <h3 class="text-lg font-bold text-stone-800 mb-4">🔥 Escolha um dos 5 temas virais gerados para criar o Ebook:</h3>
                <div class="space-y-3">
                    <?php foreach ($sugestoes as $index => $opcao): ?>
                        <form method="POST">
                            <input type="hidden" name="criar_ebook" value="1">
                            <input type="hidden" name="chosen_url" value="<?php echo htmlspecialchars($videoUrl); ?>">
                            <input type="hidden" name="textoBase" value="<?php echo htmlspecialchars($textoBase); ?>">
                            <input type="hidden" name="tema_escolhido" value="<?php echo htmlspecialchars($opcao); ?>">
                            <button type="submit" class="w-full text-left p-4 bg-white border border-stone-200 rounded-xl hover:border-[#5D2A18] hover:bg-amber-50 transition-all font-semibold text-stone-700 flex items-center gap-3">
                                <span class="bg-[#5D2A18] text-white rounded-full w-6 h-6 flex items-center justify-center text-sm"><?php echo $index + 1; ?></span>
                                <?php echo htmlspecialchars($opcao); ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Área de Exibição do Ebook Final Forjado -->
        <?php if ($ebook_html): ?>
            <div class="mt-8 p-8 border-t-4 border-[#5D2A18] bg-stone-50 rounded-xl prose max-w-none shadow-inner">
                <div class="mb-4 bg-green-100 text-green-800 p-3 rounded-lg text-center font-bold">✨ Ebook de 20 Capítulos Gerado com Sucesso!</div>
                <?php echo $ebook_html; ?>
            </div>
            <div class="mt-6">
                <button onclick="window.print()" class="w-full bg-white border-2 border-[#5D2A18] text-[#5D2A18] p-4 rounded-xl font-bold text-lg hover:bg-[#5D2A18] hover:text-white transition-all">
                    BAIXAR EM PDF / IMPRIMIR INFOPRODUTO
                </button>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="mt-4 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 font-medium"><?php echo $erro; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
