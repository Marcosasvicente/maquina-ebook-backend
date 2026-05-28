<?php
/**
 * DIAGRAMADOR DARK PRO V4 - PREMIUM VISUAL EXPERIENCE
 * Foco: Estética de Cinema, Contraste Extremo e Imagens Funcionais.
 */

$titulo = $_POST['tema_escolhido'] ?? 'ESTRATÉGIA DOMINANTE';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Conteúdo não encontrado.</h1>';

function diagramarPremium($html) {
    // 1. Títulos de Capítulos (Vermelhos, Gigantes e Negrito)
    $html = preg_replace('/<h2>(.*?)<\/h2>/i', '<h2 class="chapter-title">$1</h2>', $html);

    // 2. Subtítulos (Brancos, Negrito e com Marcador)
    // Converte h3 ou textos que a IA costuma mandar como subtítulos
    $html = preg_replace('/<h3>(.*?)<\/h3>/i', '<h3 class="sub-title">$1</h3>', $html);

    // 3. Estratégia de Imagens Contextuais (Nova Abordagem: Div com Background ou Imagem Direta com Fallback)
    $contador = 0;
    $callbackParagrafo = function($matches) use (&$contador) {
        $texto = trim($matches[1]);
        if (strlen(strip_tags($texto)) < 50) return "<p class='book-paragraph'>$texto</p>";
        
        $contador++;
        // Extrai palavras-chave do parágrafo para o prompt
        $keywords = urlencode(mb_strimwidth(strip_tags($texto), 0, 100));
        $seed = rand(1, 100000);
        
        // Prompt otimizado para não falhar e ser cinematográfico
        $imgUrl = "https://image.pollinations.ai/prompt/cinematic-photography-dark-moody-masculine-noir-style-{$keywords}?width=1080&height=540&nologo=true&seed={$seed}";

        // Retorna o parágrafo com a imagem logo abaixo, estilizada com moldura
        return "
            <div class='content-section'>
                <p class='book-paragraph'>{$texto}</p>
                <div class='premium-img-frame'>
                    <img src='{$imgUrl}' class='premium-img' alt='Visual Insight' loading='lazy' onerror='this.style.display=\"none\"'>
                    <div class='img-shadow'></div>
                </div>
            </div>";
    };

    // Aplica a lógica nos parágrafos
    $html = preg_replace_callback('/<p>(.*?)<\/p>/i', $callbackParagrafo, $html);

    // 4. Frases de Impacto (Blockquotes Estilizados)
    $html = str_ireplace(['<b>', '<strong>'], '<blockquote class="impact-quote">', $html);
    $html = str_ireplace(['</b>', '</strong>'], '</blockquote>', $html);

    return $html;
}

$conteudoFinal = diagramarPremium($conteudoBruto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-black: #050505;
            --accent-red: #E60000;
            --sub-white: #FFFFFF;
            --text-gray: #B0B0B0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg-black);
            color: var(--text-gray);
            font-family: 'Playfair Display', serif; /* Fonte de livro clássico */
            line-height: 1.8;
            -webkit-font-smoothing: antialiased;
        }

        /* Textura de fundo sutil */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            opacity: 0.1;
            pointer-events: none;
            z-index: -1;
        }

        .book-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 100px 25px;
        }

        /* Capa / Título Principal */
        .main-header {
            text-align: center;
            margin-bottom: 150px;
        }

        .main-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 4.5rem;
            color: var(--accent-red);
            text-transform: uppercase;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -2px;
            margin-bottom: 20px;
        }

        /* Títulos de Capítulos (Vermelho, Negrito, Grande) */
        .chapter-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 3rem;
            color: var(--accent-red);
            text-transform: uppercase;
            font-weight: 900;
            margin: 120px 0 50px 0;
            border-bottom: 2px solid var(--accent-red);
            padding-bottom: 10px;
            text-align: left;
        }

        /* Subtítulos (Branco, Negrito) */
        .sub-title {
            font-family: 'Inter', sans-serif;
            font-size: 1.8rem;
            color: var(--sub-white);
            font-weight: 700;
            text-transform: uppercase;
            margin: 60px 0 30px 0;
            letter-spacing: 1px;
        }

        .book-paragraph {
            font-size: 1.35rem;
            margin-bottom: 25px;
            text-align: justify;
            color: var(--text-gray);
        }

        /* Frame de Imagem Premium */
        .premium-img-frame {
            margin: 45px 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            background: #111;
            position: relative;
        }

        .premium-img {
            width: 100%;
            height: auto;
            display: block;
            filter: contrast(1.1) brightness(0.8) grayscale(0.3);
            transition: all 0.5s ease;
        }

        .premium-img:hover {
            filter: contrast(1.2) brightness(1);
        }

        /* Citação de Impacto */
        .impact-quote {
            font-family: 'Inter', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            font-style: italic;
            color: var(--sub-white);
            margin: 60px 0;
            padding: 40px;
            background: linear-gradient(90deg, #111 0%, #050505 100%);
            border-left: 6px solid var(--accent-red);
            box-shadow: 20px 20px 60px rgba(0,0,0,0.5);
        }

        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--accent-red);
            color: #fff;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            text-transform: uppercase;
            cursor: pointer;
            z-index: 9999;
            box-shadow: 0 10px 30px rgba(230, 0, 0, 0.4);
        }

        @media print {
            .btn-print { display: none; }
            body { background: #000; }
            .chapter-title { page-break-before: always; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">SALVAR ARQUIVO FINAL</button>

    <div class="book-container">
        <header class="main-header">
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p style="font-family: 'Inter'; letter-spacing: 10px; font-size: 0.8rem; color: var(--accent-red);">DOCUMENTO DE ALTO IMPACTO</p>
        </header>

        <article>
            <?= $conteudoFinal ?>
        </article>

        <footer style="margin-top: 150px; text-align: center; font-family: 'Inter'; font-size: 0.7rem; opacity: 0.4;">
            PROPRIEDADE EXCLUSIVA - TODOS OS DIREITOS RESERVADOS
        </footer>
    </div>

</body>
</html>
