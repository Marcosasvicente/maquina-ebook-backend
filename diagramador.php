<?php
/**
 * DIAGRAMADOR DARK PRO V3 - CINEMATIC EDITION
 * Lógica: Imagens por parágrafo e tipografia agressiva.
 */

$titulo = $_POST['tema_escolhido'] ?? 'ESTRATÉGIA DOMINANTE';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Conteúdo não encontrado.</h1>';

function diagramarImersivo($html) {
    // 1. Tratamento de Capítulos (Títulos Vermelhos e Grandes)
    $html = preg_replace('/<h2>(.*?)<\/h2>/i', '<h2 class="chapter-title">$1</h2>', $html);

    // 2. Tratamento de Subtítulos (Brancos e Negrito)
    // Caso a IA gere h3 ou h4, tratamos como subtítulos brancos
    $html = preg_replace('/<h3 style=".*?">(.*?)<\/h3>/i', '<h3 class="sub-title">$1</h3>', $html);
    $html = preg_replace('/<h3>(.*?)<\/h3>/i', '<h3 class="sub-title">$1</h3>', $html);

    // 3. Mecanismo Estratégico: Imagem Cinematográfica por Parágrafo
    $callbackParagrafo = function($matches) {
        $textoOriginal = $matches[1];
        if (strlen(trim($textoOriginal)) < 30) return "<p class='book-paragraph'>$textoOriginal</p>";

        // Limpa o texto para criar o prompt da imagem
        $resumoParaPrompt = urlencode(mb_strimwidth(strip_tags($textoOriginal), 0, 80, ""));
        $seed = rand(1, 99999);
        
        // Prompt otimizado para qualidade cinematográfica e nicho masculino
        $promptFinal = "ultra-realistic, cinematic lighting, dramatic shadows, dark aesthetic, masculine atmosphere, " . $resumoParaPrompt;
        $imgUrl = "https://image.pollinations.ai/prompt/{$promptFinal}?width=800&height=400&nologo=true&model=flux&seed={$seed}";

        return "
            <div class='content-block'>
                <p class='book-paragraph'>$textoOriginal</p>
                <div class='img-container'>
                    <img src='{$imgUrl}' alt='Insight Visual' class='context-img' loading='lazy'>
                </div>
            </div>";
    };

    // Aplica imagem em cada parágrafo
    $html = preg_replace_callback('/<p>(.*?)<\/p>/i', $callbackParagrafo, $html);

    // 4. Frases de Impacto (Blockquotes)
    $html = str_ireplace(['<b>', '<strong>'], '<blockquote class="impact-quote">', $html);
    $html = str_ireplace(['</b>', '</strong>'], '</blockquote>', $html);

    return $html;
}

$conteudoFinal = diagramarImersivo($conteudoBruto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;700&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --dark-bg: #030303; 
            --accent-red: #D20000; 
            --text-gray: #cecece;
            --sub-white: #ffffff;
        }

        body { 
            background-color: var(--dark-bg); 
            color: var(--text-gray); 
            font-family: 'Crimson Pro', serif; 
            line-height: 1.8;
            background-image: radial-gradient(circle at center, #111 0%, #030303 100%);
        }

        .book-page { max-width: 750px; margin: 0 auto; padding: 60px 20px; }

        /* Título Principal */
        .main-header {
            text-align: center;
            margin-bottom: 120px;
            border-bottom: 4px solid var(--accent-red);
            padding-bottom: 30px;
        }

        .main-header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 5rem;
            color: var(--accent-red);
            text-transform: uppercase;
            line-height: 0.9;
            font-weight: 900;
        }

        /* Capítulos: Vermelhos, Negrito e Maiores */
        .chapter-title {
            font-family: 'Oswald', sans-serif;
            font-size: 3.2rem;
            color: var(--accent-red);
            text-transform: uppercase;
            font-weight: 900;
            margin: 100px 0 40px 0;
            text-align: center;
            line-height: 1.1;
        }

        /* Subtítulos: Brancos, Negrito e Médios */
        .sub-title {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            color: var(--sub-white);
            text-transform: uppercase;
            font-weight: 700;
            margin: 60px 0 20px 0;
            border-left: 10px solid var(--accent-red);
            padding-left: 20px;
        }

        .book-paragraph {
            font-size: 1.4rem;
            text-align: justify;
            margin-bottom: 30px;
        }

        /* Container de Imagem por Contexto */
        .img-container {
            margin: 40px 0;
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.9);
        }

        .context-img {
            width: 100%;
            display: block;
            filter: brightness(0.7) contrast(1.2) grayscale(0.2);
            transition: 0.5s;
        }

        /* Frase de Impacto */
        .impact-quote {
            color: var(--sub-white);
            font-weight: 700;
            font-style: italic;
            font-size: 1.7rem;
            margin: 50px 0;
            padding: 30px;
            background: rgba(210, 0, 0, 0.08);
            border-right: 5px solid var(--accent-red);
            text-align: right;
        }

        .no-print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--accent-red);
            color: white;
            padding: 20px 40px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(210, 0, 0, 0.5);
        }

        @media print {
            .no-print-btn { display: none; }
            .chapter-title { page-break-before: always; }
            body { background: black; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">FINALIZAR INFOPRODUTO</button>

    <div class="book-page">
        <header class="main-header">
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p style="letter-spacing: 8px; color: #555; margin-top: 10px;">ARQUIVO CONFIDENCIAL</p>
        </header>

        <article>
            <?= $conteudoFinal ?>
        </article>
    </div>

</body>
</html>
