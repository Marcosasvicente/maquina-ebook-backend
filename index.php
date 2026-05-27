<?php
// 1. Configurações de Acesso (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 2. Chave da API (Puxa das variáveis de ambiente do Railway)
$apiKey = getenv('GEMINI_API_KEY');

// 3. Recebe e decodifica os dados
$input = json_decode(file_get_contents("php://input"), true);
$videoUrl = $input['videoUrl'] ?? '';
$tom = $input['tomDeVoz'] ?? 'Direto e Maduro';

if (!$videoUrl) {
    echo json_encode(["status" => "erro", "message" => "URL ausente"]);
    exit;
}

// 4. Extração do ID do Vídeo
preg_match("/(?:v=|\/)([a-zA-Z0-9_-]{11})/", $videoUrl, $matches);
$videoId = $matches[1] ?? null;

// 5. Tentativa de captura de legenda (Transcript)
$textoBase = "";
if ($videoId) {
    $transcriptData = @file_get_contents("https://subtitles-youtube.vercel.app/api/transcript?videoId=" . $videoId);
    $transcript = json_decode($transcriptData, true);
    
    if ($transcript && is_array($transcript)) {
        foreach ($transcript as $line) {
            $textoBase .= $line['text'] . " ";
        }
    }
}

// 6. Montagem do Prompt Estratégico (Psicologia e Copywriting)
$promptTexto = "Aja como um Especialista em Psicologia Dark e Copywriting de Alta Retenção. 
Crie um EBOOK COMPLETO baseado neste link de vídeo: $videoUrl.

CONTEÚDO BASE (TRANSCRIÇÃO): 
$textoBase

INSTRUÇÕES CRÍTICAS:
1. PÚBLICO: Homens de 35 a 65 anos. Use tom de autoridade, direto, maduro e sem gírias.
2. ESTRUTURA: Título Magnético, Introdução Impactante, 5 Capítulos Profundos e uma Conclusão com CTA.
3. FORMATO DE SAÍDA: Responda APENAS com o código HTML interno (sem a tag <html> ou <body>). Use <h1> para títulos, <h2> para capítulos e <p> para o texto.
4. CORES: Use o estilo 'Sanguine' (Títulos em cor Terracota/Marrom escuro #5D2A18).
5. Se a transcrição estiver vazia, use o seu conhecimento sobre o tema do vídeo para criar o conteúdo.

NÃO inclua explicações antes ou depois do código. Entregue apenas o conteúdo do ebook.";

$payload = [
    "contents" => [["parts" => [["text" => $promptTexto]]]]
];

// 7. Chamada para a API do Gemini
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);
$ebookFinal = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

// 8. Limpeza de possíveis marcações de markdown (```html ...)
if ($ebookFinal) {
    $ebookFinal = str_replace(['```html', '```'], '', $ebookFinal);
}

// 9. Resposta Final
if ($httpCode === 200 && $ebookFinal) {
    echo json_encode([
        "status" => "sucesso",
        "ebook_html" => trim($ebookFinal)
    ]);
} else {
    echo json_encode([
        "status" => "erro",
        "message" => "O Gemini não conseguiu gerar o texto. Verifique sua API Key no Railway.",
        "debug" => $result
    ]);
}
