<?php
// --- TRAVA DE SEGURANÇA: Aumenta o tempo de execução para textos longos ---
ini_set('max_execution_time', 300); // 5 minutos
set_time_limit(300);

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

// PASSO 1: Gerar Sugestões
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias']) && !empty($videoUrl)) {
    $promptIdeias = "Crie 5 títulos de Ebooks virais baseados neste vídeo, um por linha, numerados. Apenas os títulos. Conteúdo: " . ($textoBase ?: $videoUrl);
    $payload = ["model" => "llama-3.3-70b-versatile", "messages" => [["role" => "user", "content" => $promptIdeias]], "temperature" => 0.7];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Aguarda 1 minuto para sugestões
    
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

// PASSO 2: Gerar Ebook (Onde estava dando Erro 0)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook']) && !empty($_POST['tema_escolhido'])) {
    $tema = $_POST['tema_escolhido'];
    $promptEbook = "Aja como Editor Senior. Escreva um EBOOK COMPLETO em HTML sobre: '$tema'. Use o contexto: $textoBase. REQUISITO: Introdução + 20 CAPÍTULOS DETALHADOS + Conclusão. Use <h2> para títulos e <p> para texto. Não use crases de código.";

    $payload = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [["role" => "user", "content" => $promptEbook]],
        "temperature" => 0.5,
        "max_tokens" => 6000 // Espaço de sobra
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    
    // --- O PULO DO GATO: Aumenta o tempo de resposta do cURL ---
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 240); // Aguarda até 4 minutos a IA escrever tudo
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $ebook_html = str_replace(['```html', '```', '**'], ['', '', '<b>'], $result['choices'][0]['message']['content']);
    } else {
        $erro = "Erro ($httpCode): " . ($curlError ?: "A IA demorou muito para responder. Tente novamente.");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>EbookForge V3 - Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>.prose h2 { color: #5D2A18; font-weight: bold; margin-top: 25px; font-size: 1.5rem; } .prose p { margin-bottom: 15px; line-height: 1.7; }</style>
</head>
<body class="bg-[#fdfaf7] py-10 px-4">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-xl border border-stone-200">
        <h1 class="text-3xl font-extrabold text-[#5D2A18] mb-8 text-center">EbookForge V3</h1>
        
        <form method="POST" class="space-y-4 mb-8">
            <input type="hidden" name="buscar_ideias" value="1">
            <input type="url" name="videoUrl" required value="<?php echo htmlspecialchars($videoUrl); ?>" placeholder="Link do YouTube" class="w-full p-4 border-2 rounded-xl outline-none focus:border-[#5D2A18]">
            <button type="submit" class="w-full bg-[#5D2A18] text-white p-4 rounded-xl font-bold">ANALISAR VÍDEO</button>
        </form>

        <?php if ($sugestoes): ?>
            <div class="grid gap-3">
                <?php foreach ($sugestoes as $opcao): ?>
                <form method="POST">
                    <input type="hidden" name="criar_ebook" value="1">
                    <input type="hidden" name="chosen_url" value="<?php echo htmlspecialchars($videoUrl); ?>">
                    <input type="hidden" name="textoBase" value="<?php echo htmlspecialchars($textoBase); ?>">
                    <input type="hidden" name="tema_escolhido" value="<?php echo htmlspecialchars($opcao); ?>">
                    <button type="submit" class="w-full text-left p-4 bg-stone-50 border rounded-xl hover:bg-amber-50 font-semibold"><?php echo htmlspecialchars($opcao); ?></button>
                </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($ebook_html): ?>
            <div class="mt-8 p-8 border-t-4 border-[#5D2A18] bg-stone-50 rounded-xl prose max-w-none"><?php echo $ebook_html; ?></div>
            <button onclick="window.print()" class="w-full mt-4 bg-[#5D2A18] text-white p-4 rounded-xl font-bold">IMPRIMIR PDF</button>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="mt-4 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200"><?php echo $erro; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
