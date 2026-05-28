<?php
/**
 * DIAGRAMADOR DARK PRO V5 - ULTRA-CONTRASTE & IMAGENS DINÂMICAS
 */

$titulo = $_POST['tema_escolhido'] ?? 'O PODER DO SILÊNCIO';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Conteúdo não encontrado.</h1>';

function diagramarPremiumV5($html) {
    // 1. Títulos de Capítulos: Vermelho Sangue, Gigantes, Negrito
    $html = preg_replace('/<h2>(.*?)<\/h2>/i', '<h2 class="chapter-title">$1</h2>', $html);

    // 2. SUBTÍTULOS: Branco Puro, Negrito, Fonte Inter (Corrigido para máxima visibilidade)
    $html = preg_replace('/<h3>(.*?)<\/h3>/i', '<h3 class="sub-title">$1</h3>', $html);

    // 3. Mecanismo de Imagens: Nova Estratégia de Injeção Direta
    $contador = 0;
    $callbackParagrafo = function($matches) use (&$contador) {
        $texto = trim($matches[1]);
        if (strlen(strip_tags($texto)) < 60) return "<p class='book-paragraph'>$texto</p>";
        
        $contador++;
        // Tradução interna implícita para conceitos cinematográficos
        $seed = rand(1, 9999);
        $resumoPrompt = urlencode("dark cinematic noir, moody lighting, " . mb_strimwidth(strip_tags($texto), 0, 80));
        
        $imgUrl = "https://pollinations.ai/p/{$resumoPrompt}?width=1080&height=600&seed={$seed}&model=flux&nologo=true";

        return "
            <div class='paragraph-group'>
                <p class='book-paragraph'>{$texto}</p>
                <div class='image-wrapper'>
                    <img src='{$imgUrl}' class='cinematic-img' alt='Visual Insight' loading='lazy'>
                    <div class='img-overlay'></div>
                </div>
            </div>";
    };

    $html = preg_replace_callback('/<p>(.*?)<\/p>/i', $callbackParagrafo, $html);

    // 4. Frases de Impacto: Estilo Citação de Poder
    $html = str_ireplace(['<b>', '<strong>'], '<blockquote class="power-quote">', $html);
    $html = str_ireplace(['</b>', '</strong>'], '</blockquote>', $html);

    return $html;
}

$conteudoFinal = diagramarPremiumV5($conteudoBruto);
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
            --white: #FFFFFF;
            --text-dim: #BBBBBB;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--pure-black);
            color: var(--text-dim);
            font-family: 'Lora', serif;
            line-height: 1.9;
        }

        .container { max-width: 800px; margin: 0 auto; padding: 80px 20px; }

        /* Capa */
        .header-main { text-align: center; margin-bottom: 120px; }
        .header-main h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 5rem;
            color: var(--blood-red);
            text-transform: uppercase;
            font-weight: 900;
            line-height: 0.85;
            letter-spacing: -3px;
        }

        /* Títulos (Vermelhos e Maiores) */
        .chapter-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 3.5rem;
            color: var(--blood-red);
            text-transform: uppercase;
            font-weight: 900;
            margin: 150px 0 40px 0;
            line-height: 1;
        }

        /* Subtítulos (BRANCO PURO E NEGRITO) */
        .sub-title {
            font-family: 'Inter', sans-serif;
            font-size: 2.2rem;
            color: var(--white) !important; /* Força o branco total */
            font-weight: 900 !important;
            text-transform: uppercase;
            margin: 80px 0 30px 0;
            border-left: 12px solid var(--blood-red);
            padding-left: 20px;
        }

        .book-paragraph {
            font-size: 1.4rem;
            margin-bottom: 35px;
            text-align: justify;
        }

        /* Estilo das Imagens Cinematográficas */
        .image-wrapper {
            margin: 50px 0 80px 0;
            border: 2px solid #1a1a1a;
            position: relative;
            background: #0a0a0a;
        }
        .cinematic-img {
            width: 100%;
            display: block;
            filter: contrast(1.1) brightness(0.7);
        }
        .img-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            box-shadow: inset 0 0 100px rgba(0,0,0,0.9);
        }

        /* Citações */
        .power-quote {
            font-family: 'Inter', sans-serif;
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--white);
            font-style: italic;
            margin: 60px 0;
            padding: 40px;
            background: rgba(255,0,0,0.05);
            border-top: 1px solid var(--blood-red);
            border-bottom: 1px solid var(--blood-red);
        }

        .btn-save {
            position: fixed;
            top: 25px; right: 25px;
            background: var(--blood-red);
            color: white;
            padding: 20px 40px;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(255,0,0,0.3);
        }

        @media print {
            .btn-save { display: none; }
            .chapter-title { page-break-before: always; }
        }
    </style>
</head>
<body>

    <button class="btn-save" onclick="window.print()">SALVAR ARQUIVO FINAL</button>

    <div class="container">
        <div class="header-main">
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p style="color: #444; letter-spacing: 12px; margin-top: 15px;">TOP SECRET</p>
        </div>

        <article>
            <?= $conteudoFinal ?>
        </article>
    </div>

</body>
</html>
