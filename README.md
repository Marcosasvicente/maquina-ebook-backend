# 📘 EbookForge - Máquina Geradora de Ebooks

O **EbookForge** é uma aplicação inteligente que transforma links de vídeos do YouTube em ebooks completos, diagramados e prontos para venda. A ferramenta utiliza IA para extrair o conhecimento de vídeos e reescrevê-los com técnicas de **Psicologia Dark e Copywriting de Retenção**.

## 🚀 Tecnologias Utilizadas

* **Frontend:** [Lovable.app](https://lovable.app) (Interface inspirada no Gamma.app)
* **Backend:** PHP rodando no [Railway](https://railway.app)
* **IA:** Google Gemini Pro 1.5 (Processamento de linguagem natural)
* **Hospedagem:** GitHub + Railway

## 🧠 Funcionalidades

* **Extração Automática:** Captura legendas e transcrições de vídeos do YouTube.
* **Escrita Especializada:** Conteúdo focado em homens de 35 a 65 anos (linguagem madura e direta).
* **Diagramação "Sanguine":** Saída em HTML estilizado com cores terracota e marrom escuro.
* **Exportação:** Interface preparada para conversão em PDF.

## 🛠️ Configuração do Ambiente (Backend)

Para rodar este backend, você precisará configurar as seguintes Variáveis de Ambiente no seu projeto do Railway:

1.  `GEMINI_API_KEY`: Sua chave privada obtida no [Google AI Studio](https://aistudio.google.com/).
2.  `ALLOWED_ORIGIN`: A URL do seu frontend no Lovable (para evitar erros de CORS).

## 📂 Estrutura de Arquivos

* `index.php`: O "motor" principal que processa as requisições e comunica com o Gemini.
* `Procfile`: Arquivo de configuração para o deploy no Railway.
* `.htaccess`: (Opcional) Para redirecionamento de rotas.

## 📝 Como usar

1.  Cole a URL de um vídeo do YouTube na interface do **EbookForge**.
2.  Escolha o Tom de Voz (ex: Direto e Maduro).
3.  Clique em **"Forjar Ebook"**.
4.  Aguarde o processamento e visualize o conteúdo diagramado na tela.
5.  Clique em **"Exportar para PDF"** para salvar seu produto.

---
*Projeto desenvolvido para automação de funis de vendas invisíveis e criação de infoprodutos de alta qualidade.*
