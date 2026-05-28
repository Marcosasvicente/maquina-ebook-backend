<?php
/**
 * DIAGRAMADOR DARK PRO V7 - IMPACTO TOTAL E IMAGENS FORÇADAS
 */

$titulo = $_POST['tema_escolhido'] ?? 'ESTRATÉGIA DOMINANTE';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Conteúdo não encontrado.</h1>';

function diagramarPremiumV7($html) {
    // 1. LIMPEZA TOTAL: Remove qualquer estilo de cor que a IA tenha injetado
    $html = preg_replace('/style="[^"]*"/i', '', $html);

    // 2. TÍTULOS DE CAPÍTULOS: Vermelho Sangue, Gigantes
    $html = preg_replace('/<h2>(.*?)<\/h2>/i', '<h2 class="chapter-title">$1</h2>', $html);

    // 3. SUBTÍTULOS: BRANCO PURO, NEGRITO E O DOBRO DO TAMANHO DO TEXTO
    $html = preg_replace('/<h3>(.*?)<\/h3>/i', '<h3 class="sub-title">$1</h3>', $html);

    // 4. MECANISMO DE IMAGENS: Nova estratégia com carregamento forçado
    $contador = 0;
    $callbackParagrafo = function($matches) use (&$contador) {
        $texto = trim($matches[1]);
        if (strlen(strip_tags($texto)) < 100) return "<p class='book-paragraph'>$texto</p>";
        
        $contador++;
        $seed = rand(1, 99999);
        // Prompt refinado para Psicologia Dark e Conceitos Masculinos
        $keywords = urlencode("cinematic photography, dark moody noir, masculine power, " . mb_strimwidth(strip_tags($texto), 0, 100));
        
        // Endpoint que funciona melhor com tags <img> diretas
        $imgUrl = "https://image.pollinations.ai/prompt/{$keywords}?width=1080&height=600&nologo=true&seed={$seed}&model=flux";

        return "
            <div class='content-block'>
                <p class='book-paragraph'>{$texto}</p>
                <div class='premium-frame'>
                    <img src='{$imgUrl}' class='main-img' alt='Visual Analysis' loading='eager'>
                    <div class='vignette'></div>
                </div>
            </div>";
    };

    $html = preg_replace_callback('/<p>(.*?)<\/p>/i', $callbackParagrafo, $html);

    // 5. FRASES DE IMPACTO (Negritos)
    $html = str_ireplace(['<b>', '<strong>'], '<blockquote class="bold-impact">', $html);
    $html = str_ireplace(['</b>', '</strong>'], '</blockquote>', $html);

    return $html;
}

$conteudoFinal = diagramarPremiumV7($conteudoBruto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&family=Lora:wght@400;700&family=Inter:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #000000;
            --red: #FF0000;
            --white: #FFFFFF;
            --gray: #A0A0A0;
            --font-size-text: 1.3rem; /* Tamanho base do texto */
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--black);
            color: var(--gray);
            font-family: 'Lora', serif;
            line-height: 1.8;
            overflow-x: hidden;
        }

        .book-wrapper { max-width: 800px; margin: 0 auto; padding: 100px 20px; }

        /* Título Principal */
        .cover-title { text-align: center; margin-bottom: 150px; }
        .cover-title h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 5rem;
            color: var(--red);
            text-transform: uppercase;
            font-weight: 900;
            line-height: 0.9;
        }

        /* Capítulos */
        .chapter-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 3.5rem;
            color: var(--red);
            text-transform: uppercase;
            margin: 120px 0 60px 0;
            border-left: 15px solid var(--red);
            padding-left: 25px;
        }

        /* SUBTÍTULOS: BRANCO PURO E O DOBRO DO TAMANHO DO TEXTO */
        .sub-title {
            font-family: 'Inter', sans-serif;
            font-size: 2.8rem; /* MAIS QUE O DOBRO DO TEXTO BASE */
            color: var(--white) !important;
            font-weight: 900 !important;
            text-transform: uppercase;
            margin: 80px 0 40px 0;
            line-height: 1.2;
            display: block;
        }

        /* Texto Comum */
        .book-paragraph {
            font-size: var(--font-size-text);
            margin-bottom: 35px;
            text-align: justify;
        }

        /* Moldura de Imagem Cinematográfica */
        .premium-frame {
            margin: 50px 0 100px 0;
            background: #050505;
            border: 1px solid #111;
            position: relative;
            box-shadow: 0 40px 80px rgba(0,0,0,0.9);
        }
        .main-img {
            width: 100%;
            display: block;
            filter: contrast(1.1) brightness(0.8);
            border-bottom: 4px solid var(--red);
        }
        .vignette {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            box-shadow: inset 0 0 100px rgba(0,0,0,1);
            pointer-events: none;
        }

        /* Citações de Impacto */
        .bold-impact {
            font-family: 'Inter', sans-serif;
            font-size: 1.8rem;
            color: var(--white);
            font-weight: 900;
            font-style: italic;
            margin: 60px 0;
            padding: 40px;
            background: rgba(255,0,0,0.05);
            border-right: 8px solid var(--red);
            text-align: right;
        }

        .btn-print {
            position: fixed;
            bottom: 30px; right: 30px;
            background: var(--red);
            color: white;
            padding: 20px 40px;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            border: none;
            cursor: pointer;
            z-index: 1000;
            border-radius: 5px;
        }

        @media print {
            .btn-print { display: none; }
            .chapter-title { page-break-before: always; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">EXPORTAR PDF FINAL</button>

    <div class="book-wrapper">
        <header class="cover-title">
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p style="color: #333; letter-spacing: 15px; margin-top: 15px;">TOP SECRET DOCUMENT</p>
        </header>

        <article>
            <?= $conteudoFinal ?>
        </article>
    </div>

</body>
</html>
