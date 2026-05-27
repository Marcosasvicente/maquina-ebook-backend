<?php
// --- CONFIGURAÇÃO DE SEGURANÇA E CHAVE API ---
// A chave abaixo é a que você forneceu. O sistema a usará como prioridade.
$apiKey = 'AIzaSyDiv97dj9UpB17FShxSMvF0npuzfLE0c-k';

$ebook_html = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['videoUrl'])) {
    $videoUrl = $_POST['videoUrl'];
    
    // Extração do ID do Vídeo (Suporta youtube.com e youtu.be)
    preg_match("/(?:v=|\/)([a-zA-Z0-9_-]{11})/", $videoUrl, $matches);
    $videoId = $matches[1] ?? null;

    $textoBase = "";
    if ($videoId) {
        // API auxiliar para capturar a legenda do YouTube
        $transcriptData = @file_get_contents("https://subtitles-youtube.vercel.app/api/transcript?videoId=" . $videoId);
        $transcript = json_decode($transcriptData, true);
        if ($transcript && is_array($transcript)) {
            foreach ($transcript as $line) { 
                $textoBase .= $line['text'] . " "; 
            }
        }
    }

    // Prompt Estratégico em Psicologia Dark (Público Masculino 35-65 anos)
    $promptTexto = "Aja como Especialista em Psicologia Dark e Copywriting de Alta Retenção. Crie um EBOOK COMPLETO (formatado em HTML) para homens de 35 a 65 anos baseado neste conteúdo: $videoUrl. Use linguagem madura, direta e autoritária. Aplique a cor #5D2A18 (Terracota) nos títulos h1 e h2. Transcrição do vídeo: $textoBase";
    
    $payload = ["contents" => [["parts" => [["text" => $promptTexto]]]]];
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    // Configuração da chamada externa para o Google Gemini
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    $ebookFinal = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($httpCode === 200 && $ebookFinal) {
        // Limpa marcações de markdown do HTML
        $ebook_html = str_replace(['```html', '```'], '', $ebookFinal);
    } else {
        $erro = "Erro na API do Gemini (Código HTTP: $httpCode). Verifique se a chave está ativa no Google Cloud.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EbookForge - Painel de Controle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #fdfaf7; color: #331a11; }
        .sanguine-bg { background-color: #5D2A18; }
        .sanguine-text { color: #5D2A18; }
        .prose h1, .prose h2 { color: #5D2A18; font-weight: bold; margin-top: 1.5em; }
        .prose p { margin-bottom: 1em; line-height: 1.7; }
    </style>
</head>
<body class="p-4 md:p-10">
    <div class="max-w-4xl mx-auto">
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold sanguine-text mb-2">EbookForge</h1>
            <p class="text-gray-600">Transforme vídeos em ebooks de alta conversão.</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8 border border-gray-100">
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Link do Vídeo do YouTube:</label>
                    <input type="url" name="videoUrl" required placeholder="https://www.youtube.com/watch?v=..." 
                           class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[#5D2A18] outline-none bg-gray-50">
                </div>
                <button type="submit" class="w-full sanguine-bg text-white font-bold py-3 rounded-lg hover:opacity-95 transition shadow-md">
                    FORJAR EBOOK AGORA
                </button>
            </form>
        </div>

        <?php if ($ebook_html): ?>
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8 prose max-w-none border border-gray-100 shadow-inner">
                <?php echo $ebook_html; ?>
            </div>
            <button onclick="window.print()" class="w-full border-2 border-[#5D2A18] sanguine-text font-bold py-3 rounded-lg hover:bg-[#5D2A18] hover:text-white transition shadow-sm mb-10">
                GERAR PDF / IMPRIMIR
            </button>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-lg text-center font-bold border border-red-200 shadow-sm">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
    </div>
</body>
</html>
