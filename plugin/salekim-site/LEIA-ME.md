# Plugin SALEKIM Site 2.0

Monta o site www.salekim.com.br do começo ao fim, a partir do briefing de setembro de 2026 e do rascunho de textos de 25/09/2026.

## Instalar

1. Faça um backup do site na hospedagem (a maioria tem um botão "Backup" no painel).
2. No WordPress: **Plugins > Adicionar novo > Enviar plugin**, escolha `salekim-site.zip` e clique em **Instalar agora**.
   Se aparecer que o plugin já existe, escolha **Substituir a atual pela enviada**.
3. Clique em **Ativar**. O site é montado na hora.
4. Abra **SALEKIM > Montar o site** e confira o que foi feito e a lista "O que falta".

O plugin funciona com qualquer tema: ele desenha o próprio cabeçalho, rodapé e páginas. Se o Elementor estiver instalado, pode ser desativado depois de conferir o site (as páginas antigas ficam guardadas nas revisões).

## O que o plugin faz ao ativar

| Item | Resultado |
|---|---|
| Páginas | Início, Sobre, Para empresas, Programas, Clientes, Contato, Conteúdos e Política de privacidade, com os textos do briefing. Páginas antigas com o mesmo endereço recebem o texto novo, e a versão antiga fica nas revisões. |
| Programas | Método VIDA, Formação em desenvolvimento de grupos, Programa InterAgir, DNA, Supervisão e Acompanhamento individual. |
| Serviços | Transformação organizacional, Desenvolvimento de lideranças e equipes, Palestras e Workshops. |
| Menu e leitura | Menu principal, página inicial = Início, artigos em Conteúdos, endereços amigáveis (/sobre/), comentários desligados. |
| Demonstração | "Olá, mundo!", "Página de exemplo", textos "Lorem ipsum" e o comentário de exemplo vão para a lixeira (podem ser recuperados por 30 dias). |

## Onde mudar cada coisa

| O que | Onde |
|---|---|
| Textos das páginas | **Páginas**. Clique no texto e escreva por cima. Os blocos com colchetes, como `[salekim_programas]`, puxam os cadastros: não apague. |
| Programas | **SALEKIM > Programas**: nome, para quem é, descrição, etiqueta (Inscrições abertas, Lista de espera), texto do botão, link externo (Hotmart) e botão de WhatsApp. A ordem segue o campo "Ordem". |
| Serviços para empresas | **SALEKIM > Serviços** |
| Depoimentos | **SALEKIM > Depoimentos**. A seção aparece sozinha no site quando houver o primeiro publicado. |
| Clientes, contatos, CNPJ, logo, foto, Analytics, Pixel, Search Console | **SALEKIM > Configurações** |
| Mensagens dos formulários | Chegam no e-mail e ficam em **SALEKIM > Mensagens** |
| Descrição no Google | Caixa "Descrição para o Google", ao lado do editor de cada página |
| Artigos | **Posts > Adicionar novo**. Depois do primeiro, inclua "Conteúdos" em **Aparência > Menus**. |

## Formulários

* **Pedir proposta** (empresas): nome, empresa, cargo, e-mail, WhatsApp, assunto e "o que a sua empresa precisa hoje".
* **Quero saber mais / lista de espera** (pessoas): nome, e-mail, WhatsApp, programa e mensagem.
* Os botões dos programas e dos serviços já escolhem o programa ou o assunto.
* Proteção contra spam sem captcha: campo invisível, tempo mínimo de preenchimento e limite de 5 envios a cada 15 minutos por aparelho.
* Consentimento LGPD obrigatório, registrado com data e hora em cada mensagem.

Se o e-mail de teste não chegar, instale o plugin **WP Mail SMTP** e configure com a conta genira@salekim.com.br. As mensagens continuam guardadas no painel mesmo quando o e-mail falha.

## LGPD, Analytics e Pixel

Com o ID do Google Analytics 4 ou do Meta Pixel preenchido em Configurações, o site mostra o aviso de cookies. Os dois só carregam depois de "Aceitar todos". O link "Preferências de cookies" no rodapé reabre o aviso. Envios de formulário contam como conversão (`generate_lead` no Analytics, `Lead` no Pixel) e cliques no WhatsApp como `Contact`.

## SEO

Título e descrição de cada página, Open Graph para compartilhamento no WhatsApp e redes, dados estruturados da empresa (nome, CNPJ, telefone, cidade, Instagram) e o mapa do site do WordPress em `/wp-sitemap.xml`. Se um plugin de SEO (Yoast, Rank Math) for instalado, ele assume essa parte.

## Ferramentas (SALEKIM > Montar o site)

* **Montar ou completar o site**: cria o que estiver faltando. Não muda textos já editados.
* **Restaurar o texto original**: regrava as páginas com o texto do briefing (a versão atual fica nas revisões).
* **Enviar e-mail de teste**.
* **Mover a demonstração para a lixeira**.

## Pendências de conteúdo (dependem da Genira)

Títulos das palestras, lista de workshops, duração e próxima turma da Formação, descrição do InterAgir após a reformulação, formato da Supervisão, se "Executive e Life Coaching" aparece no Acompanhamento individual, depoimentos autorizados, fotos profissionais, logo vetorizado e link da página de vendas do VIDA na Hotmart.
