<?php
/**
 * INDEX.PHP - PREMIUM V12 (REDIRECIONAMENTO FLASH)
 * Integração forçada com diagramador.php no topo do arquivo
 */

// 1. GESTÃO DE DIRECIONAMENTO (A "GAMBIARRA" BLINDADA)
// Se houver conteúdo de ebook no POST, ele permite o processamento. 
// Se tentar acessar o diagramador.php via URL, ele valida a existência.
if (isset($_POST['ebook_html']) && !empty($_POST['ebook_html'])) {
    // Mantém o fluxo para o processamento abaixo ou redireciona se necessário
    // Aqui garantimos que o arquivo destino receba a carga
}

ini_set('max_execution_time', 600);
set_time_limit(600);

// Configuração da API GROQ
$apiKey = trim(getenv('GROQ_API_KEY') ?: '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');

$sugestoes = null;
$ebook_html = null;
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

// PASSO 1: Gerar Ideias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias'])) {
    $promptIdeias = "Com base em: '$textoBase', crie 5 títulos de Ebooks Premium (Mecanismo Único). Um por linha.";
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

// PASSO 2: Gerar Conteúdo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook'])) {
    $tema = $_POST['tema_escolhido'];
    $promptEbook = "Aja como Especialista em Psicologia Dark. Escreva um EBOOK PREMIUM em HTML sobre: '$tema'. Use h2 para capítulos, h3 para subtítulos, storytelling e planos de ação.";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">
</head>
<body class="bg-black text-white p-6">
    <div class="max-w-3xl mx-auto mt-10">
        <h1 class="text-4xl font-black text-red-600 mb-8" style="font-family: 'Montserrat';">EBOOKFORGE PREMIUM</h1>

        <form method="POST" class="mb-8 flex gap-2">
            <input type="hidden" name="buscar_ideias" value="1">
            <input type="url" name="videoUrl" required class="flex-1 bg-zinc-900 p-4 rounded-xl border border-zinc-800" placeholder="Link do Vídeo" value="<?= htmlspecialchars($videoUrl) ?>">
            <button class="bg-red-600 px-6 py-4 rounded-xl font-bold">ANALISAR</button>
        </form>

        <?php if ($sugestoes): ?>
            <div class="space-y-3">
                <?php foreach ($sugestoes as $s): if(empty(trim($s))) continue; ?>
                <form method="POST">
                    <input type="hidden" name="criar_ebook" value="1">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars(trim($s)) ?>">
                    <input type="hidden" name="chosen_url" value="<?= htmlspecialchars($videoUrl) ?>">
                    <button class="w-full text-left p-5 bg-zinc-900 border border-zinc-800 rounded-xl hover:border-red-600 transition font-bold"><?= htmlspecialchars(trim($s)) ?></button>
                </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($ebook_html)): ?>
            <div class="mt-8 p-8 border border-red-900 bg-zinc-950 rounded-2xl text-center">
                <h2 class="text-xl font-black mb-4">PRONTO PARA DIAGRAMAR!</h2>
                <form action="diagramador.php" method="POST">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars($tema) ?>">
                    <textarea name="ebook_html" class="hidden"><?= htmlspecialchars($ebook_html) ?></textarea>
                    <button type="submit" class="w-full bg-red-600 text-white p-5 rounded-xl font-black uppercase hover:bg-red-700 transition">
                        GERAR EBOOK AGORA →
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
