<?php
/**
 * INDEX.PHP - PREMIUM V10 (ENVIO SEGURO DIRETO)
 * Corrige o loop e garante o envio dos dados POST para o diagramador.php
 */

ini_set('max_execution_time', 600);
set_time_limit(600);

$apiKey = trim(getenv('GROQ_API_KEY') ?: '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');

$sugestoes = null;
$ebook_html = null;
$tema = $_POST['tema_escolhido'] ?? '';
$videoUrl = $_POST['videoUrl'] ?? $_POST['chosen_url'] ?? '';
$textoBase = $_POST['textoBase'] ?? '';

// Captura de Transcrição do YouTube
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

// PASSO 1: Gerar Sugestões de Títulos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_ideias'])) {
    $promptIdeias = "Crie 5 títulos de Ebooks Premium sobre: '$textoBase'. Use Mecanismo Único e Promessa Inédita. Um por linha, sem números.";
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

// PASSO 2: Geração do Conteúdo Premium
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ebook'])) {
    $promptEbook = "Aja como um Especialista em Psicologia Dark. Escreva um EBOOK PREMIUM em HTML sobre o tema: '$tema'. Use obrigatoriamente tags <h2> para capítulos e tags <h3> para subtítulos. Certifique-se de incluir histórias (storytelling), ganchos de retenção, um plano de ação prático e scripts prontos ao final de cada capítulo. Não use marcações Markdown como '**', use apenas HTML puro.";
    
    $payload = [
        "model" => "llama-3.3-70b-versatile", 
        "messages" => [["role" => "user", "content" => $promptEbook]], 
        "temperature" => 0.7, 
        "max_tokens" => 8000
    ];
    
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
<body class="bg-[#000000] text-white p-6 font-sans">
    <div class="max-w-3xl mx-auto mt-10">
        <h1 class="text-4xl font-black text-red-600 mb-8 tracking-tighter" style="font-family: 'Montserrat';">EBOOKFORGE <span class="text-white">PREMIUM</span></h1>

        <!-- Formulário de Entrada principal -->
        <form method="POST" class="mb-8 flex gap-2">
            <input type="hidden" name="buscar_ideias" value="1">
            <input type="url" name="videoUrl" required class="flex-1 bg-zinc-900 p-4 rounded-xl border border-zinc-800 text-white outline-none focus:border-red-600" placeholder="Insira o Link do Vídeo Base" value="<?= htmlspecialchars($videoUrl) ?>">
            <button class="bg-red-600 hover:bg-red-700 px-6 py-4 rounded-xl font-bold transition">ANALISAR</button>
        </form>

        <!-- Lista de Títulos Gerados -->
        <?php if ($sugestoes): ?>
            <div class="space-y-3">
                <h3 class="text-zinc-500 font-bold uppercase text-xs tracking-widest mb-2">Selecione o Conceito do Ebook:</h3>
                <?php foreach ($sugestoes as $s): if(empty(trim($s))) continue; ?>
                <form method="POST">
                    <input type="hidden" name="criar_ebook" value="1">
                    <input type="hidden" name="tema_escolhido" value="<?= htmlspecialchars(trim($s)) ?>">
                    <input type="hidden" name="textoBase" value="<?= htmlspecialchars($textoBase) ?>">
                    <input type="hidden" name="chosen_url" value="<?= htmlspecialchars($videoUrl) ?>">
                    <button class="w-full text-left p-5 bg-zinc-900 border border-zinc-800 rounded-xl hover:border-red-600 transition font-bold text-gray-200"><?= htmlspecialchars(trim($s)) ?></button>
                </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- SEÇÃO DE DISPARO DA DIAGRAMAÇÃO DIRECT-HIT -->
        <?php if (!empty($ebook_html)): ?>
            <div class="mt-8 p-8 border border-red-950 bg-zinc-950 rounded-2xl text-center shadow-2xl">
                <div class="w-12 h-12 border-4 border-t-red-600 border-zinc-800 rounded-full animate-spin mx-auto mb-4"></div>
                <h2 class="text-xl font-black text-white uppercase tracking-wider mb-2">Conteúdo Processado com Sucesso!</h2>
                <p class="text-zinc-400 text-sm mb-6">A estrutura premium foi copiada e os dados estão estáveis para a renderização dark.</p>
                
                <!-- Formulário com dados embutidos diretamente no HTML nativo -->
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
