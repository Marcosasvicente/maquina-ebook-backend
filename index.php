<?php
// --- CONFIGURAÇÃO DE SEGURANÇA E CHAVE API DA GROQ ---
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

    // --- NOVAS INSTRUÇÕES ALINHADAS ---
    // 1. Identificação automática do assunto
    // 2. Público-alvo universal
    // 3. Estrutura de no mínimo 20 capítulos
    $promptTexto = "Aja como um Editor de Infoprodutos Senior. 
    PASSO 1: Analise o conteúdo deste vídeo e identifique o tema central: $videoUrl.
    PASSO 2: Com base na transcrição abaixo, crie um EBOOK COMPLETO formatado em HTML.
    
    REGRAS OBRIGATÓRIAS:
    - O público é UNIVERSAL (adapte a linguagem para ser acessível a qualquer pessoa).
    - ESTRUTURA: Introdução robusta + MÍNIMO DE 20 CAPITULOS DISTINTOS E DETALHADOS + Conclusão prática.
    - ESTILO: Use títulos h1 e h2 com a cor #5D2A18. 
    - Formate parágrafos com a tag <p>.
    
    Transcrição do vídeo: $textoBase";
    
    $payload = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [["role" => "user", "content" => $promptTexto]],
        "temperature" => 0.6, // Reduzido levemente para manter o foco em 20 capítulos sem viajar demais
        "max_tokens" => 4000  // Aumentado para suportar o texto longo de 20 capítulos
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
        $ebookFinal = $result['choices'][0]['message']['content'];
        // Limpeza de Markdown e formatação de negrito
        $ebook_html = str_replace(['```html', '```', '**'], ['', '', '<b>'], $ebookFinal);
    } else {
        $msg_erro = $result['error']['message'] ?? 'Erro desconhecido';
        $erro = "Erro na Geração: $msg_erro";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>EbookForge V2 - Gerador Universal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .prose h1, .prose h2 { color: #5D2A18; font-weight: bold; margin-top: 20px; font-size: 1.5rem; }
        .prose p { margin-bottom: 15px; line-height: 1.6; color: #444; }
    </style>
</head>
<body class="bg-[#fdfaf7] py-10 px-4">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-xl border border-stone-200">
        <h1 class="text-4xl font-extrabold text-[#5D2A18] mb-2 text-center">EbookForge V2</h1>
        <p class="text-center text-stone-500 mb-8">Identificação Automática | 20+ Capítulos | Público Universal</p>
        
        <form method="POST" class="space-y-4 mb-10">
            <input type="url" name="videoUrl" required placeholder="Cole o link do YouTube aqui..." 
                   class="w-full p-4 border-2 border-stone-200 rounded-xl focus:border-[#5D2A18] outline-none transition-all">
            <button type="submit" class="w-full bg-[#5D2A18] text-white p-4 rounded-xl font-bold text-lg hover:brightness-110 transition-all shadow-lg">
                FORJAR EBOOK COMPLETO
            </button>
        </form>

        <?php if ($ebook_html): ?>
            <div class="p-8 border-t-4 border-[#5D2A18] bg-stone-50 rounded-b-xl prose max-w-none">
                <?php echo $ebook_html; ?>
            </div>
            <div class="mt-6 flex gap-4">
                <button onclick="window.print()" class="flex-1 bg-white border-2 border-[#5D2A18] text-[#5D2A18] p-3 rounded-lg font-bold">PDF / IMPRIMIR</button>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="mt-4 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 font-medium"><?php echo $erro; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
