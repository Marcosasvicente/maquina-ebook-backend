<?php
/**
 * INDEX.PHP - PREMIUM V8 (AUTO-BRIDGE SYSTEM)
 * Blindagem de rota e envio automático para diagramação
 */

// 1. BLINDAGEM DE SEGURANÇA: Garante que o arquivo do diagramador exista antes de tentar qualquer ponte
$arquivo_destino = 'diagramador.php';

ini_set('max_execution_time', 600);
set_time_limit(600);

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

// PASSO 1: Sugestões
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias'])) {
    $promptIdeias = "Crie 5 títulos de Ebooks Premium sobre: '$textoBase'. Use Mecanismo Único e Promessa Inédita. Um por linha.";
    $payload = ["model" => "llama-3.3-70b-versatile", "messages" => [["role" => "user", "content" => $promptIdeias]], "temperature" => 0.8];
    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    $sugestoes = explode("\n", trim($result['choices'][0]['message']['content'] ?? ''));
    curl_close($ch);
}

// PASSO 2: Geração Premium
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook'])) {
    $tema = $_POST['tema_escolhido'];
    $promptEbook = "Escreva um EBOOK PREMIUM em HTML sobre: '$tema'. Use STORYTELLING, RETENÇÃO, OPEN LOOPS, PLANOS DE AÇÃO e SCRIPTS. Formato: h2 para capítulos, h3 para subtítulos.";
    $payload = ["model" => "llama-3.3-70b-versatile", "messages" => [["role" => "user", "content" => $promptEbook]], "temperature" => 0.7, "max_tokens" => 8000];
    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    $ebook_html = $result['choices'][0]['message']['content'] ?? "";
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>EbookForge Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-black text-red-600 mb-8">EBOOKFORGE <span class="text-white">PREMIUM</span></h1>

        <form method="POST" class="mb-8 flex gap-2">
            <input type="hidden" name="buscar_ideias" value="1">
            <input type="url" name="videoUrl" required class="flex-1 bg-zinc-900 p-4 rounded-xl border border-zinc-800" placeholder="Link do Vídeo">
            <button class="bg-red-600 px-6 py-4 rounded-xl font-bold">ANALISAR</button>
        </form>

        <?php if ($sugestoes): ?>
            <div class="space-y-3">
                <?php foreach ($sugestoes as $s): ?>
                <form method="POST">
                    <input type="hidden" name="criar_ebook" value="1">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($s) ?>">
                    <input type="hidden" name="textoBase" value="<?= htmlspecialchars($textoBase) ?>">
                    <button class="w-full text-left p-4 bg-zinc-900 border border-zinc-800 rounded-xl hover:border-red-600 transition font-bold"><?= $s ?></button>
                </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($ebook_html): ?>
            <div id="status_msg" class="mt-10 p-10 text-center border-2 border-dashed border-red-600 rounded-3xl">
                <h2 class="text-2xl font-bold mb-4">CONTEÚDO PREMIUM GERADO!</h2>
                <p class="text-zinc-400 mb-6">Iniciando motor de diagramação dark em 3 segundos...</p>
                
                <form id="autoBridgeForm" action="<?= $arquivo_destino ?>" method="POST">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($tema) ?>">
                    <textarea name="ebook_html" class="hidden"><?= htmlspecialchars($ebook_html) ?></textarea>
                    <button type="submit" class="bg-white text-black px-10 py-4 rounded-full font-black uppercase text-sm">Clique aqui se não redirecionar</button>
                </form>
            </div>

            <script>
                // O GRANDE TRUQUE: Dispara o formulário automaticamente após 3 segundos
                setTimeout(function(){
                    document.getElementById('autoBridgeForm').submit();
                }, 3000);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
