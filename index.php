<?php
$apiKey = trim(getenv('GROQ_API_KEY') ?: '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');

$ebook_html = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['videoUrl'])) {
    $videoUrl = $_POST['videoUrl'];
    
    preg_match("/(?:v=|\/)([a-zA-Z0-9_-]{11})/", $videoUrl, $matches);
    $videoId = $matches[1] ?? null;

    $textoBase = "";
    if ($videoId) {
        $transcriptData = @file_get_contents("https://subtitles-youtube.vercel.app/api/transcript?videoId=" . $videoId);
        $transcript = json_decode($transcriptData, true);
        if ($transcript && is_array($transcript)) {
            foreach ($transcript as $line) { $textoBase .= $line['text'] . " "; }
        }
    }

    $promptTexto = "Crie um ebook em HTML baseado neste conteúdo: " . ($textoBase ?: $videoUrl) . ". Use tons de marrom (#5D2A18) nos títulos. Foco: Homens 35-65 anos. Seja direto.";
    
    $payload = [
        "model" => "llama-3.1-70b-versatile", // MODELO ATUALIZADO AQUI
        "messages" => [["role" => "user", "content" => $promptTexto]],
        "temperature" => 0.7
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    
    if ($httpCode === 200) {
        $ebook_html = str_replace(['```html', '```'], '', $result['choices'][0]['message']['content']);
    } else {
        $msg_erro = $result['error']['message'] ?? 'Erro desconhecido';
        $erro = "Erro Groq ($httpCode): $msg_erro";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>EbookForge - Groq</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fdfaf7] p-10">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-md">
        <h1 class="text-3xl font-bold text-[#5D2A18] mb-6">EbookForge</h1>
        <form method="POST" class="space-y-4">
            <input type="url" name="videoUrl" required placeholder="URL do YouTube" class="w-full p-3 border rounded-lg">
            <button type="submit" class="w-full bg-[#5D2A18] text-white p-3 rounded-lg font-bold">GERAR EBOOK AGORA</button>
        </form>

        <?php if ($ebook_html): ?>
            <div class="mt-8 p-6 border rounded-lg prose"><?php echo $ebook_html; ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg"><?php echo $erro; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
