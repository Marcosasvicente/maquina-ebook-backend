<?php
/**
 * DIAGRAMADOR DARK PRO - MÓDULO DE IDENTIDADE VISUAL
 * Foco: Alto Impacto, Retenção e Estética de Psicologia Dominante.
 */

// Captura os dados vindos do formulário do EbookForge
$titulo = $_POST['tema_escolhido'] ?? 'O PODER DO SILÊNCIO';
$conteudoBruto = $_POST['ebook_html'] ?? '<h1>Nenhum conteúdo processado.</h1>';

/**
 * Função para aplicar a diagramação "Dark" no HTML vindo da IA
 */
function diagramarConteudo($html) {
    // 1. Destaca os Capítulos com a cor vermelha e estilo Oswald
    $html = str_ireplace('<h2>', '<h2 class="chapter-title">', $html);
    
    // 2. Transforma negritos em frases de impacto brancas com borda lateral
    $html = str_ireplace(['<b>', '<strong>'], '<strong class="highlight-impact">', $html);
    
    // 3. Adiciona uma classe especial aos parágrafos para melhor leitura
    $html = str_ireplace('<p>', '<p class="dark-text">', $html);
    
    return $html;
}

$conteudoFinal = diagramarConteudo($conteudoBruto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador Dark - <?php echo htmlspecialchars($titulo); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    
    <style>
        /* CONFIGURAÇÃO VISUAL BASEADA NAS IMAGENS DE REFERÊNCIA */
        :root {
            --black: #000000;
            --deep-red: #BC0000;
            --text-gray: #B3B3B3;
            --pure-white: #FFFFFF;
            --dark-surface: #0A0A0A;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--black);
            color: var(--text-gray);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Container que simula o formato de leitura mobile/e-reader */
        .viewport {
            max-width: 550px;
            margin: 0 auto;
            padding: 60px 25px;
            background-color: var(--black);
            min-height: 100vh;
            border-left: 1px solid #1a1a1a;
            border-right: 1px solid #1a1a1a;
            box-shadow: 0 0 50px rgba(188, 0, 0, 0.1);
        }

        /* Capa de Impacto */
        header.book-header {
            border-bottom: 6px solid var(--deep-red);
            padding-bottom: 30px;
            margin-bottom: 60px;
        }

        h1.main-title {
            font-family: 'Oswald', sans-serif;
            font-size: 3.8rem;
            color: var(--deep-red);
            text-transform: uppercase;
            line-height: 0.85;
            font-weight: 900;
            letter-spacing: -2px;
        }

        /* Títulos de Capítulo Estilizados */
        .chapter-title {
            font-family: 'Oswald', sans-serif;
            color: var(--pure-white);
            font-size: 2.5rem;
            text-transform: uppercase;
            margin-top: 80px;
            margin-bottom: 30px;
            line-height: 1.1;
            display: block;
            border-top: 1px solid #333;
            padding-top: 20px;
        }

        /* Parágrafos de Leitura */
        p.dark-text {
            font-size: 1.25rem;
            margin-bottom: 25px;
            text-align: left;
            color: var(--text-gray);
            font-weight: 400;
        }

        /* Frases de Impacto (Baseadas na estrutura das imagens) */
        .highlight-impact {
            color: var(--pure-white);
            font-size: 1.4rem;
            display: block;
            margin: 30px 0;
            font-weight: 700;
            border-left: 5px solid var(--deep-red);
            padding-left: 20px;
            font-style: italic;
            background: linear-gradient(90deg, #1a0000 0%, transparent 100%);
            padding-top: 10px;
            padding-bottom: 10px;
        }

        /* Botão Flutuante para Gerar PDF */
        .no-print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--deep-red);
            color: #fff;
            padding: 18px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 900;
            font-size: 1rem;
            text-transform: uppercase;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            z-index: 9999;
            transition: transform 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .no-print-btn:hover {
            transform: scale(1.05);
            background-color: #ff0000;
        }

        /* Regras de Impressão */
        @media print {
            .no-print-btn { display: none !important; }
            body { background-color: #000 !important; }
            .viewport { 
                max-width: 100% !important; 
                border: none !important; 
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .chapter-title { page-break-before: always; }
            p, strong, h1, h2 { color: white !important; }
            h1, h2 { color: #ff0000 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">
        FINALIZAR E SALVAR PDF
    </button>

    <div class="viewport">
        <header class="book-header">
            <h1 class="main-title"><?php echo htmlspecialchars($titulo); ?></h1>
        </header>

        <article>
            <?php echo $conteudoFinal; ?>
        </article>
        
        <footer style="margin-top: 100px; text-align: center; color: #333; font-size: 0.8rem; text-transform: uppercase;">
            Conteúdo Protegido - Sistema de Diagramação Automática
        </footer>
    </div>

</body>
</html>
