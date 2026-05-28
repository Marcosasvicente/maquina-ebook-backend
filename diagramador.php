<?php
/**
 * DIAGRAMADOR PRO - MÓDULO DE IDENTIDADE VISUAL
 * Este arquivo recebe o conteúdo bruto e aplica a diagramação de alto impacto.
 */

// Recebe os dados do EbookForge
$titulo = $_POST['tema_escolhido'] ?? 'Título do Infoproduto';
$conteudo = $_POST['ebook_html'] ?? '<h1>Aguardando conteúdo...</h1>';

// Função para limpar e preparar o conteúdo para o estilo Dark
function prepararConteudo($html) {
    // Transforma títulos de capítulos em destaque vermelho
    $html = str_ireplace('<h2>', '<h2 class="capitulo-titulo">', $html);
    // Garante que negritos sejam destacados em branco puro
    $html = str_ireplace(['<b>', '<strong>'], '<strong class="impacto">', $html);
    return $html;
}

$conteudo_diagramado = prepararConteudo($conteudo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador - <?php echo htmlspecialchars($titulo); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <style>
        /* CONFIGURAÇÃO DE CORES DAS IMAGENS ANALISADAS */
        :root {
            --preto-profundo: #000000;
            --vermelho-sangue: #BC0000;
            --cinza-texto: #B3B3B3;
            --branco-puro: #FFFFFF;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--preto-profundo);
            color: var(--cinza-texto);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
        }

        /* Container que simula a largura de leitura das imagens */
        .viewport {
            max-width: 500px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: var(--preto-profundo);
            min-height: 100vh;
        }

        /* Título de Capa de Alto Impacto */
        .capa-header {
            border-bottom: 5px solid var(--vermelho-sangue);
            padding-bottom: 20px;
            margin-bottom: 50px;
        }

        h1.main-title {
            font-family: 'Oswald', sans-serif;
            font-size: 3.5rem;
            color: var(--vermelho-sangue);
            text-transform: uppercase;
            line-height: 0.9;
            font-weight: 900;
        }

        /* Estilo dos Capítulos (Baseado na Imagem 01) */
        .capitulo-titulo {
            font-family: 'Oswald', sans-serif;
            color: var(--vermelho-sangue);
            font-size: 2.2rem;
            text-transform: uppercase;
            margin-top: 60px;
            margin-bottom: 25px;
            line-height: 1.1;
        }

        /* Parágrafos e Leitura */
        p {
            font-size: 1.15rem;
            margin-bottom: 20px;
            text-align: left;
        }

        /* Frases de Impacto (Baseado na Imagem 02/03) */
        .impacto {
            color: var(--branco-puro);
            font-size: 1.3rem;
            display: block;
            margin: 15px 0;
            font-weight: 700;
            border-left: 3px solid var(--vermelho-sangue);
            padding-left: 10px;
        }

        /* Botão Flutuante para PDF */
        .btn-pdf {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--vermelho-sangue);
            color: #fff;
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(188, 0, 0, 0.4);
            z-index: 1000;
        }

        @media print {
            .btn-pdf { display: none; }
            .viewport { max-width: 100%; padding: 0; }
            .capitulo-titulo { page-break-before: always; }
        }
    </style>
</head>
<body>

    <a href="javascript:window.print()" class="btn-pdf">EXPORTAR PDF PREMIUM</a>

    <div class="viewport">
        <header class="capa-header">
            <h1 class="main-title"><?php echo htmlspecialchars($titulo); ?></h1>
        </header>

        <article class="conteudo-main">
            <?php echo $conteudo_diagramado; ?>
        </article>
    </div>

</body>
</html>
