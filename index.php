<?php
ini_set('max_execution_time', 600); 
set_time_limit(600);

$apiKey = trim(getenv('GROQ_API_KEY') ?: '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');

$sugestoes = null;
$ebook_html = null;
$erro = null;

$videoUrl = $_POST['videoUrl'] ?? $_POST['chosen_url'] ?? '';
$textoBase = $_POST['textoBase'] ?? '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias']) && !empty($videoUrl)) {
    $promptIdeias = "Com base no vídeo, crie 5 títulos de Ebooks inéditos e magnéticos. Evite clichês. Escreva apenas os títulos, um por linha. Conteúdo: " . ($textoBase ?: $videoUrl);
    $payload = ["model" => "llama-3.3-70b-versatile", "messages" => [["role" => "user", "content" => $promptIdeias]], "temperature" => 0.9];
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
    $sugestoes = array_slice($sugestoes ?? [], 0, 5);
    curl_close($ch);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook']) && !empty($_POST['tema_escolhido'])) {
    $tema = $_POST['tema_escolhido'];
    $promptEbook = "Aja como um Ghostwriter de Elite. Escreva um EBOOK EXTENSO (meta de 6000 palavras) em HTML sobre: '$tema'. CONTEXTO DO VÍDEO: $textoBase. DIRETRIZES: 20 CAPÍTULOS detalhados + Conclusão. FORMATO: h2 para capítulos, p para texto. Use b para frases de impacto.";
    $payload = ["model" => "llama-3.3-70b-versatile", "messages" => [["role" => "user", "content" => $promptEbook]], "temperature" => 0.8, "max_tokens" => 8000];
    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 500);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $ebook_html = str_replace(['```html', '```', '**'], ['', '', '<b>'], $result['choices'][0]['message']['content']);
    } else { $erro = "Erro ao forjar conteúdo denso."; }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>EbookForge V4 - Deep Content</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .prose h2 { color: #5D2A18; font-weight: bold; margin-top: 40px; font-size: 1.8rem; border-bottom: 2px solid #5D2A18; padding-bottom: 10px; }
        .prose p { margin-bottom: 20px; line-height: 1.8; font-size: 1.1rem; color: #333; text-align: justify; }
    </style>
</head>
<body class="bg-[#fdfaf7] py-10 px-4">
    <div class="max-w-4xl mx-auto bg-white p-10 rounded-2xl shadow-2xl border border-stone-200">
        <h1 class="text-4xl font-black text-[#5D2A18] mb-8 text-center uppercase">EbookForge PRO</h1>
        
        <form method="POST" class="space-y-4 mb-12">
            <input type="hidden" name="buscar_ideias" value="1">
            <input type="url" name="videoUrl" required value="<?= htmlspecialchars($videoUrl) ?>" placeholder="Link do YouTube" class="w-full p-5 border-2 rounded-2xl outline-none focus:border-amber-600 shadow-inner">
            <button type="submit" class="w-full bg-[#5D2A18] text-white p-5 rounded-2xl font-bold text-xl">GERAR ESTRATÉGIAS</button>
        </form>

        <?php if ($sugestoes): ?>
            <div class="space-y-4">
                <?php foreach ($sugestoes as $opcao): ?>
                <form method="POST">
                    <input type="hidden" name="criar_ebook" value="1">
                    <input type="hidden" name="chosen_url" value="<?= htmlspecialchars($videoUrl) ?>">
                    <input type="hidden" name="textoBase" value="<?= htmlspecialchars($textoBase) ?>">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($opcao) ?>">
                    <button type="submit" class="w-full text-left p-5 bg-stone-50 border-2 rounded-2xl font-bold"><?= htmlspecialchars($opcao) ?></button>
                </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($ebook_html): ?>
            <div class="mt-12 p-10 border-t-8 border-[#5D2A18] bg-[#fffefc] rounded-xl prose max-w-none shadow-2xl">
                <?= $ebook_html ?>
            </div>

            <form action="diagramador.php" method="POST" target="_blank" class="mt-8">
                <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($tema) ?>">
                <input type="hidden" name="ebook_html" value="<?= htmlspecialchars($ebook_html) ?>">
                <button type="submit" class="w-full bg-[#BC0000] text-white p-6 rounded-2xl font-bold text-2xl shadow-xl hover:bg-red-700 transition-all">
                    DIAGRAMAÇÃO PSICOLOGIA DARK →
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
