<?php
// 1. Verificação de Rota (Deve ser a primeira coisa no arquivo)
if (strpos($_SERVER['REQUEST_URI'], 'diagramador.php') !== false) {
    include 'diagramador.php';
    exit;
}

ini_set('max_execution_time', 600); 
set_time_limit(600);

// Configuração da API
$apiKey = trim(getenv('GROQ_API_KEY') ?: 'AIzaSyBseYvTfNEvlOMFrk6Khu3YnSp
CC0ZoA6Y');

$sugestoes = null;
$ebook_html = null;
$erro = null;

$videoUrl = $_POST['videoUrl'] ?? $_POST['chosen_url'] ?? '';
$textoBase = $_POST['textoBase'] ?? '';

// Captura da Transcrição do YouTube
if (!empty($videoUrl) && empty($textoBase)) {
    preg_match("/(?:v=|\/)([a-zA-Z0-9_-]{11})/", $videoUrl, $matches);
    $videoId = $matches[1] ?? null;
    if ($videoId) {
        $transcriptData = @file_get_contents("https://subtitles-youtube.vercel.app/api/transcript?videoId=" . $videoId);
        if ($transcriptData) {
            $transcript = json_decode($transcriptData, true);
            if ($transcript && is_array($transcript)) {
                $textoBase = ""; 
                foreach ($transcript as $line) { $textoBase .= $line['text'] . " "; }
            }
        }
    }
}

// PASSO 1: Gerar Sugestões Virais (Acionado ao clicar em GERAR ESTRATÉGIAS)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias']) && !empty($videoUrl)) {
    $promptIdeias = "Com base no conteúdo deste vídeo, crie 5 títulos de Ebooks inéditos e magnéticos para o nicho de masculinidade e comportamento. Evite clichês. Escreva apenas os títulos, um por linha. Conteúdo base: " . (mb_strimwidth($textoBase, 0, 5000, "..."));
    
    $payload = ["model" => "llama-3.3-70b-versatile", "messages" => [["role" => "user", "content" => $promptIdeias]], "temperature" => 0.9];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    $conteudoResposta = $result['choices'][0]['message']['content'] ?? '';
    $linhas = explode("\n", str_replace("\r", "", trim($conteudoResposta)));
    
    foreach ($linhas as $linha) {
        $linhaLimpa = preg_replace('/^[0-9]+[\.\)\s\-]+/', '', trim($linha));
        if (!empty($linhaLimpa) && strlen($linhaLimpa) > 5) {
            $sugestoes[] = $linhaLimpa;
        }
    }
    if ($sugestoes) $sugestoes = array_slice($sugestoes, 0, 5);
}

// PASSO 2: Gerar Conteúdo do Ebook
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook']) && !empty($_POST['tema_escolhido'])) {
    $tema = $_POST['tema_escolhido'];
    $promptEbook = "Aja como um Ghostwriter de Elite especialista em Psicologia Dark. Escreva um EBOOK EXTENSO em HTML sobre: '$tema'. CONTEXTO: $textoBase. DIRETRIZES: Introdução + 20 CAPÍTULOS + Conclusão. Use h2 para títulos, p para texto e b para frases de impacto.";

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
    } else { $erro = "Erro ao forjar conteúdo. Verifique sua chave de API."; }
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
        <h1 class="text-4xl font-black text-[#5D2A18] mb-8 text-center uppercase tracking-tighter">EbookForge <span class="text-amber-600">PRO</span></h1>
        
        <form method="POST" class="space-y-4 mb-12">
            <input type="hidden" name="buscar_ideias" value="1">
            <input type="url" name="videoUrl" required value="<?= htmlspecialchars($videoUrl) ?>" placeholder="Cole o link do YouTube aqui..." class="w-full p-5 border-2 rounded-2xl outline-none focus:border-amber-600 shadow-inner">
            <button type="submit" class="w-full bg-[#5D2A18] text-white p-5 rounded-2xl font-bold text-xl hover:scale-[1.01] transition-transform shadow-lg">GERAR ESTRATÉGIAS INÉDITAS</button>
        </form>

        <?php if ($sugestoes): ?>
            <div class="space-y-4 animate-fade-in">
                <h2 class="text-xl font-bold text-stone-800">🔥 Escolha um título para o seu Ebook:</h2>
                <?php foreach ($sugestoes as $opcao): ?>
                <form method="POST">
                    <input type="hidden" name="criar_ebook" value="1">
                    <input type="hidden" name="chosen_url" value="<?= htmlspecialchars($videoUrl) ?>">
                    <input type="hidden" name="textoBase" value="<?= htmlspecialchars($textoBase) ?>">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($opcao) ?>">
                    <button type="submit" class="w-full text-left p-5 bg-stone-50 border-2 border-stone-100 rounded-2xl hover:border-amber-600 hover:bg-white transition-all font-bold text-stone-700 shadow-sm flex justify-between items-center">
                        <?= htmlspecialchars($opcao) ?>
                        <span class="text-amber-600">GERAR AGORA →</span>
                    </button>
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
                <textarea name="ebook_html" style="display:none;"><?= htmlspecialchars($ebook_html) ?></textarea>
                <button type="submit" style="background-color: #BC0000; color: white; padding: 25px; width: 100%; border-radius: 15px; font-weight: bold; font-size: 1.5rem; cursor: pointer; box-shadow: 0 10px 20px rgba(0,0,0,0.3);">
                    ABRIR DIAGRAMAÇÃO DARK →
                </button>
            </form>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="mt-6 p-5 bg-red-50 text-red-700 rounded-2xl border-2 border-red-100 font-bold text-center"><?= $erro ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
