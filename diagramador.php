<?php
$titulo = $_POST['tema_escolhido'] ?? 'O PODER DO SILÊNCIO';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Conteúdo não encontrado.</h1>';

function diagramarComImagens($html) {
    // 1. Estiliza os capítulos e insere a imagem automática baseada no título
    // Usamos um contador para dar variedade aos prompts
    $callback = function($matches) {
        $capituloTexto = strip_tags($matches[0]);
        // Criamos um prompt focado em Psicologia Dark/Cinematográfico
        $prompt = urlencode("cinematic dark style, moody lighting, male perspective, " . $capituloTexto);
        $imgUrl = "https://image.pollinations.ai/prompt/{$prompt}?width=1080&height=600&nologo=true&seed=" . rand(1, 1000);
        
        return "
            <div class='chapter-wrapper'>
                <img src='{$imgUrl}' alt='Chapter Image' class='chapter-img' loading='lazy'>
                <h2 class='chapter-title'>{$matches[1]}</h2>
            </div>";
    };

    // Procura por todos os <h2> e aplica a função acima
    $html = preg_replace_callback('/<h2>(.*?)<\/h2>/i', $callback, $html);
    
    // 2. Transforma parágrafos e negritos
    $html = str_ireplace('<p>', '<p class="book-paragraph">', $html);
    $html = str_ireplace(['<b>', '<strong>'], '<blockquote class="impact-quote">', $html);
    $html = str_ireplace(['</b>', '</strong>'], '</blockquote>', $html);
    
    return $html;
}

$conteudoFinal = diagramarComImagens($conteudoBruto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,700;1,400&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --dark-bg: #050505; --accent-red: #BC0000; --text-color: #d1d1d1; }
        body { 
            background-color: var(--dark-bg); 
            color: var(--text-color); 
            font-family: 'Crimson Pro', serif; 
            line-height: 1.8; 
            background-image: linear-gradient(rgba(5,5,5,0.9), rgba(5,5,5,0.9)), url('https://www.transparenttextures.com/patterns/dark-matter.png');
        }
        .book-page { max-width: 700px; margin: 0 auto; padding: 40px; }
        
        /* Estilo das Imagens Geradas */
        .chapter-img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 5px;
            margin-top: 60px;
            filter: grayscale(40%) contrast(120%) brightness(80%);
            border-bottom: 3px solid var(--accent-red);
            box-shadow: 0 15px 35px rgba(0,0,0,0.8);
        }

        .chapter-title {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            color: #fff;
            text-transform: uppercase;
            margin: 30px 0 50px 0;
            text-align: center;
        }

        .book-paragraph { font-size: 1.3rem; margin-bottom: 25px; text-align: justify; }
        .impact-quote { 
            color: #fff; font-weight: bold; font-style: italic; font-size: 1.5rem; 
            margin: 40px 0; padding: 20px; border-left: 5px solid var(--accent-red); 
            background: rgba(188,0,0,0.05);
        }
        
        .no-print-btn { position: fixed; top: 20px; right: 20px; background: var(--accent-red); color: white; padding: 15px; border: none; cursor: pointer; font-weight: bold; z-index: 100; }

        @media print { .no-print-btn { display: none; } .chapter-img { page-break-before: always; } }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print-btn">SALVAR EBOOK COM IMAGENS</button>
    <div class="book-page">
        <h1 style="font-family:'Oswald'; color:var(--accent-red); font-size:4rem; text-align:center; margin-bottom:100px; text-transform:uppercase; border-bottom: 2px solid var(--accent-red); padding-bottom:20px;">
            <?= htmlspecialchars($titulo) ?>
        </h1>
        <article><?= $conteudoFinal ?></article>
    </div>
</body>
</html>

