<?php
/**
 * DIAGRAMADOR DARK PRO V6 - FOCO EM BRANCO PURO E IMAGENS FLUX
 */

$titulo = $_POST['tema_escolhido'] ?? 'ESTRATÉGIA DOMINANTE';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Conteúdo não encontrado.</h1>';

function diagramarPremiumV6($html) {
    // 1. LIMPEZA DE ESTILOS INJETADOS: Remove qualquer 'color:' que a IA tenha colocado nos subtítulos
    $html = preg_replace('/style="[^"]*color:[^"]*"/i', '', $html);

    // 2. TÍTULOS DE CAPÍTULOS: Vermelho Sangue, Gigantes e Negrito
    $html = preg_replace('/<h2>(.*?)<\/h2>/i', '<h2 class="chapter-title">$1</h2>', $html);

    // 3. SUBTÍTULOS: Branco Puro (#FFFFFF) e Negrito Absoluto
    // Forçamos a limpeza da tag e aplicamos a classe que garante o branco
    $html = preg_replace('/<h3>(.*?)<\/h3>/i', '<h3 class="sub-title">$1</h3>', $html);

    // 4. MECANISMO DE IMAGENS: Geradas após cada parágrafo denso
    $contador = 0;
    $callbackParagrafo = function($matches) use (&$contador) {
        $texto = trim($matches[1]);
        // Ignora parágrafos muito curtos para não poluir
        if (strlen(strip_tags($texto)) < 80) return "<p class='book-paragraph'>$texto</p>";
        
        $contador++;
        $seed = rand(1, 99999);
        // Prompt otimizado para o nicho Dark Psychology
        $resumoPrompt = urlencode("cinematic dark noir photography, moody lighting, high contrast, masculine atmosphere, " . mb_strimwidth(strip_tags($texto), 0, 100));
        
        $imgUrl = "https://pollinations.ai/p/{$resumoPrompt}?width=1080&height=600&seed={$seed}&model=flux&nologo=true";

        return "
            <div class='paragraph-group'>
                <p class='book-paragraph'>{$texto}</p>
                <div class='image-wrapper'>
                    <img src='{$imgUrl}' class='cinematic-img' alt='Insight Visual' loading='lazy' onerror='this.parentElement.style.display=\"none\"'>
                    <div class='img-overlay'></div>
                </div>
            </div>";
    };

    $html = preg_replace_callback('/<p>(.*?)<\/p>/i', $callbackParagrafo, $html);

    // 5. FRASES DE IMPACTO: Citações em Branco e Negrito com fundo escuro
    $html = str_ireplace(['<b>', '<strong>'], '<blockquote class="power-quote">', $html);
    $html = str_ireplace(['</b>', '</strong>'], '</blockquote>', $html);

    return $html;
}

$conteudoFinal = diagramarPremiumV6($conteudoBruto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&family=Lora:ital,wght@0,400;0,700;1,400&family=Inter:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --pure-black: #000000;
            --blood-red: #FF0000;
            --pure-white: #FFFFFF;
            --text-gray: #CCCCCC;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--pure-black);
            color: var(--text-gray);
            font-family: 'Lora', serif;
            line-height: 1.9;
        }

        .container { max-width: 850px; margin: 0 auto; padding: 80px 25px; }

        /* Capa */
        .header-main { text-align: center; margin-bottom: 120px; }
        .header-main h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 5.5rem;
            color: var(--blood-red);
            text-transform: uppercase;
            font-weight: 900;
            line-height: 0.85;
            letter-spacing: -4px;
        }

        /* Títulos (Vermelhos) */
        .chapter-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 3.8rem;
            color: var(--blood-red);
            text-transform: uppercase;
            font-weight: 900;
            margin: 140px 0 50px 0;
            line-height: 1;
            border-bottom: 3px solid var(--blood-red);
            padding-bottom: 10px;
        }

        /* SUBTÍTULOS: BRANCO ABSOLUTO E NEGRITO */
        .sub-title {
            font-family: 'Inter', sans-serif;
            font-size: 2.4rem;
            color: var(--pure-white) !important;
            font-weight: 900 !important;
            text-transform: uppercase;
            margin: 90px 0 35px 0;
            display: block;
            background: linear-gradient(90deg, rgba(255,0,0,0.2) 0%, transparent 100%);
            padding: 15px;
            border-left: 15px solid var(--blood-red);
        }

        .book-paragraph {
            font-size: 1.45rem;
            margin-bottom: 40px;
            text-align: justify;
            color: var(--text-gray);
        }

        /* Imagens Cinematográficas */
        .image-wrapper {
            margin: 60px 0 100px 0;
            border: 1px solid #222;
            position: relative;
            background: #080808;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
        }
        .cinematic-img {
            width: 100%;
            display: block;
            filter: contrast(1.1) brightness(0.8);
        }
        .img-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            box-shadow: inset 0 0 120px rgba(0,0,0,1);
        }

        /* Citações de Impacto */
        .power-quote {
            font-family: 'Inter', sans-serif;
            font-size: 1.9rem;
            font-weight: 900;
            color: var(--pure-white);
            font-style: italic;
            margin: 70px 0;
            padding: 50px;
            background: #0a0a0a;
            border-right: 8px solid var(--blood-red);
            text-align: right;
        }

        .btn-save {
            position: fixed;
            top: 30px; right: 30px;
            background: var(--blood-red);
            color: white;
            padding: 22px 45px;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            border: none;
            cursor: pointer;
            z-index: 1000;
            text-transform: uppercase;
            font-size: 1.1rem;
            box-shadow: 0 10px 30px rgba(255,0,0,0.4);
        }

        @media print {
            .btn-save { display: none; }
            .chapter-title { page-break-before: always; }
        }
    </style>
</head>
<body>

    <button class="btn-save" onclick="window.print()">SALVAR EBOOK PREMIUM</button>

    <div class="container">
        <header class="header-main">
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p style="color: var(--blood-red); letter-spacing: 15px; margin-top: 20px; font-weight: bold;">HIGH IMPACT DOCUMENT</p>
        </header>

        <article>
            <?= $conteudoFinal ?>
        </article>
    </div>

</body>
</html>
