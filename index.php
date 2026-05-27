<?php
// Configurações de CORS para o Lovable conseguir acessar este backend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

// 1. Configurações
$apiKey = getenv('GEMINI_API_KEY'); // Vamos configurar isso no Railway

// 2. Recebe os dados do Lovable
$input = json_decode(file_get_contents("php://input"), true);
$videoUrl = $input['videoUrl'] ?? '';
$tom = $input['tomDeVoz'] ?? 'Direto e Maduro';

if (!$videoUrl) {
    echo json_encode(["error" => "URL do vídeo não fornecida."]);
    exit;
}

// 3. Extração simplificada do ID do Vídeo
preg_match("/(?:v=|\/)([a-zA-Z0-9_-]{11})/", $videoUrl, $matches);
$videoId = $matches[1] ?? null;

if (!$videoId) {
    echo json_encode(["error" => "ID do vídeo inválido."]);
    exit;
}

// 4. Captura da Legenda (Usando API de terceiro gratuita/pública)
$transcriptData = @file_get_contents("https://subtitles-youtube.vercel.app/api/transcript?videoId=" . $videoId);
$transcript = json_decode($transcriptData, true);

$textoBase = "";
if ($transcript && is_array($transcript)) {
    foreach ($transcript as $line) {
        $textoBase .= $line['text'] . " ";
    }
} else {
    $textoBase = "Não foi possível extrair a legenda automaticamente. O vídeo pode estar sem legendas ou ser privado.";
}

// 5. Prompt Especialista para o Gemini
$prompt = [
    "contents" => [[
        "parts" => [[
            "text" => "Aja como um Especialista em Psicologia Dark e Copywriting de Retenção. 
            Transforme a transcrição abaixo em um EBOOK de ALTA QUALIDADE.
            Público-alvo: Homens de 35 a 65 anos. Linguagem: Direta, madura, sem gírias.
            
            ESTRUTURA DO EBOOK:
            1. Título Impactante (Headline de vendas).
            2. Introdução provocativa.
            3. 5 Capítulos com ensinamentos práticos e profundos baseados no vídeo.
            4. Conclusão com CTA (Chamada para ação).

            ESTILO VISUAL (HTML): Use tags HTML (h1, h2, p) com cores do tema 'Sanguine' (Terracota #8B4513 para títulos).
            
            TRANSCRIÇÃO: $textoBase"
        ]]
    ]]
];

// 6. Chamada para a API do Gemini
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($prompt));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

$ebookTexto = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Erro ao gerar conteúdo.";

// 7. Retorno para o Lovable
echo json_encode([
    "status" => "sucesso",
    "ebook_html" => $ebookTexto
]);
