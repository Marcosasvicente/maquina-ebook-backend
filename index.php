<?php
// --- LÓGICA DO BACKEND (PROCESSAMENTO) ---
$apiKey = getenv('GEMINI_API_KEY');
$ebook_html = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['videoUrl'])) {
    $videoUrl = $_POST['videoUrl'];
    
    // Extração do ID do Vídeo
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

    $promptTexto = "Aja como Especialista em Psicologia Dark e Copywriting. Crie um EBOOK COMPLETO (HTML) para homens de 35-65 anos baseado neste conteúdo: $textoBase. Use títulos em cor #5D2A18.";
    
    $payload = ["contents" => [["parts" => [["text" => $promptTexto]]]]];
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    $ebook_html = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if ($ebook_html) {
        $ebook_html = str_replace(['```html', '```'], '', $ebook_html);
    } else {
        $erro = "Não foi possível gerar o conteúdo. Verifique sua chave API.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EbookForge - Gerador de Infoprodutos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #fdfaf7; color: #331a11; }
        .sanguine-bg { background-color: #5D2A18; }
        .sanguine-text { color: #5D2A18; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold sanguine-text mb-2">EbookForge</h1>
            <p class="text-gray-600">Transforme vídeos em ebooks de alta conversão em segundos.</p>
        </div>

        <div class="card p-6 mb-8">
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block font-semibold mb-1">Link do Vídeo do YouTube:</label>
                    <input type="url" name="videoUrl" required placeholder="https://www.youtube.com/watch?v=..." 
                           class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#5D2A18]">
                </div>
                <button type="submit" class="w-full sanguine-bg text-white font-bold py-3 rounded-lg hover:opacity-90 transition">
                    Forjar Ebook Agora
                </button>
            </form>
        </div>

        <?php if ($ebook_html): ?>
            <div class="card p-8 mb-8 prose max-w-none" id="ebook-content">
                <?php echo $ebook_html; ?>
            </div>
            <button onclick="window.print()" class="w-full border-2 border-[#5D2A18] sanguine-text font-bold py-3 rounded-lg hover:bg-[#5D2A18] hover:text-white transition">
                Salvar como PDF
            </button>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-lg text-center font-bold">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
