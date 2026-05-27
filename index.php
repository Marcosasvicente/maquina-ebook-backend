<?php
// 1. Liberação Total de Acesso (CORS) - ESSENCIAL PARA O LOVABLE
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin, Accept");
header("Access-Control-Max-Age: 86400");

// Se for apenas uma sondagem (OPTIONS), encerra aqui com sucesso
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json; charset=utf-8");

// 2. Chave da API (Puxa das variáveis de ambiente do Railway)
$apiKey = getenv('GEMINI_API_KEY');

// 3. Recebe e decodifica os dados vindos do Lovable
$input = json_decode(file_get_contents("php://input"), true);
$videoUrl = $input['videoUrl'] ?? '';
$tom = $input['tomDeVoz'] ?? 'Direto e Maduro';

if (!$videoUrl) {
    echo json_encode(["status" => "erro", "message" => "URL do vídeo não fornecida."]);
    exit;
}

// 4. Extração do ID do Vídeo (Suporta youtube.com e youtu.be)
preg_match("/(?:v=|\/)([a-zA-Z0-9_-]{11})/", $videoUrl, $matches);
$videoId = $matches[1] ?? null;

// 5. Captura da Legenda (Transcript)
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

CONTEÚDO EXTRAÍDO DO VÍDEO: 
$textoBase

DIRETRIZES DO PRODUTO:
1. PÚBLICO-ALVO: Homens de 35 a 65 anos. Use linguagem direta, madura, autoritária e sem gírias.
2. ESTRUTURA DO EBOOK: Título Magnético, Introdução Provocativa, 5 Capítulos com conteúdo denso e prático, e uma Conclusão com CTA forte.
3. FORMATO DE SAÍDA: Responda APENAS com código HTML (use <h1>, <h2>, <p>). 
4. ESTILO VISUAL: Aplique a cor #5D2A18 (Terracota Sanguine) nos títulos <h1> e <h2>.
5. FALTA DE DADOS: Se a transcrição estiver vazia, use seu conhecimento vasto sobre o tema do vídeo para criar o melhor conteúdo possível.

NÃO escreva 'Aqui está o seu ebook' ou qualquer texto fora do HTML.";

$payload = [
    "contents" => [["parts" => [["text" => $promptTexto]]]]
];

// 7. Chamada para a API do Gemini
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

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

// 8. Limpeza de formatação Markdown indesejada
if ($ebookFinal) {
    $ebookFinal = str_replace(['```html', '```'], '', $ebookFinal);
}

// 9. Resposta Final para o Lovable
if ($httpCode === 200 && $ebookFinal) {
    echo json_encode([
        "status" => "sucesso",
        "ebook_html" => trim($ebookFinal)
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "message" => "Erro na comunicação com o Gemini. Verifique a API Key.",
        "debug_http_code" => $httpCode
    ]);
}
