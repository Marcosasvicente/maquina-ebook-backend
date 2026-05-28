<?php
/**
 * DIAGRAMADOR DARK PRO V2 - EDIÇÃO IMERSIVA
 * Foco: Estética de Livro Físico, Texturas Sutis e Leitura Fluida.
 */

$titulo = $_POST['tema_escolhido'] ?? 'O PODER DO SILÊNCIO';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Nenhum conteúdo processado.</h1>';

function diagramarConteudo($html) {
    // 1. Capítulos com numeração e estilo clássico
    $html = str_ireplace('<h2>', '<h2 class="chapter-title">', $html);
    
    // 2. Frases de impacto com design de "Pull Quote" (Citação de destaque)
    $html = str_ireplace(['<b>', '<strong>'], '<blockquote class="impact-quote">', $html);
    $html = str_ireplace(['</b>', '</strong>'], '</blockquote>', $html);
    
    // 3. Parágrafos com recuo de primeira linha (estilo livro)
    $html = str_ireplace('<p>', '<p class="book-paragraph">', $html);
    
    return $html;
}

$conteudoFinal = diagramarConteudo($conteudoBruto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,700;1,400&family=Oswald:wght@700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --dark-bg: #050505;
            --accent-red: #BC0000;
            --text-color: #d1d1d1;
            --page-width: 650px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--dark-bg);
            /* Imagem sutil de fundo (Textura de papel escuro ou fumaça leve) */
            background-image: 
                linear-gradient(rgba(5, 5, 5, 0.92), rgba(5, 5, 5, 0.92)),
                url('https://www.transparenttextures.com/patterns/dark-matter.png');
            color: var(--text-color);
            font-family: 'Crimson Pro', serif; /* Fonte serifada para leitura de livro */
            line-height: 1.8;
        }

        /* Container do Livro */
        .book-page {
            max-width: var(--page-width);
            margin: 0 auto;
            padding: 80px 40px;
            background: rgba(10, 10, 10, 0.6);
            box-shadow: 0 0 100px rgba(0,0,0,0.5);
            min-height: 100vh;
        }

        /* Título Principal Estilo Capa */
        .book-header {
            text-align: center;
            margin-bottom: 100px;
            border-bottom: 1px solid rgba(188, 0, 0, 0.3);
            padding-bottom: 50px;
        }

        .main-title {
            font-family: 'Oswald', sans-serif;
            font-size: 3.5rem;
            color: var(--accent-red);
            text-transform: uppercase;
            letter-spacing: -1px;
            line-height: 1;
        }

        /* Estilo de Título de Capítulo */
        .chapter-title {
            font-family: 'Oswald', sans-serif;
            font-size: 2.2rem;
            color: #fff;
            margin: 80px 0 40px 0;
            text-transform: uppercase;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chapter-title::before, .chapter-title::after {
            content: "";
            height: 1px;
            width: 50px;
            background: var(--accent-red);
            margin: 0 20px;
        }

        /* Parágrafo Estilo Livro */
        .book-paragraph {
            font-size: 1.35rem;
            margin-bottom: 20px;
            text-align: justify;
            hyphens: auto;
        }

        /* Frase de Impacto Dominante */
        .impact-quote {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-style: italic;
            color: #fff;
            font-size: 1.5rem;
            margin: 45px 0;
            padding: 20px 30px;
            border-left: 4px solid var(--accent-red);
            background: rgba(188, 0, 0, 0.05);
            display: block;
        }

        /* Botão Salvar (Invisível na impressão) */
        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--accent-red);
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            border: none;
            z-index: 1000;
            opacity: 0.7;
            transition: 0.3s;
        }
        
        .no-print-btn:hover { opacity: 1; }

        @media print {
            .no-print-btn { display: none; }
            body { background: #000; }
            .book-page { padding: 0; background: none; box-shadow: none; }
            .chapter-title { page-break-before: always; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">SALVAR COMO PDF</button>

    <div class="book-page">
        <header class="book-header">
            <h1 class="main-title"><?= htmlspecialchars($titulo) ?></h1>
            <p style="text-transform: uppercase; letter-spacing: 5px; font-size: 0.9rem; margin-top: 15px; color: #666;">Manual de Estratégia</p>
        </header>

        <article>
            <?= $conteudoFinal ?>
        </article>

        <footer style="margin-top: 100px; text-align: center; opacity: 0.3; font-size: 0.8rem;">
            © <?= date('Y') ?> - Todos os direitos reservados.
        </footer>
    </div>

</body>
</html>
