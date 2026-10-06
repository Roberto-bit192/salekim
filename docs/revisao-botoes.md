# Revisão dos botões do site atual

Conferido em 6 de outubro de 2026, nas páginas Início, Sobre, Para Empresas, Programas, Clientes, Contato e Política de Privacidade.

- **Quebrado:** o botão não funciona.
- **Ajuste:** funciona, mas leva ao lugar errado, confunde ou fere uma boa prática.
- **Ok:** funciona.

## Quebrados (corrigir primeiro)

| Página | Botão | Problema | Correção |
|---|---|---|---|
| Programas | Quero saber mais (Método VIDA) | Aponta para `#salekim-form-pessoas`, mas esse formulário não existe na página Programas. O clique não faz nada. | Incluir o formulário de pessoas no fim da página Programas, com `id="salekim-form-pessoas"`, e escolher o programa automaticamente. |
| Programas | Entrar na lista de espera (Programa InterAgir) | Mesmo destino inexistente. | Idem. |
| Programas | Entrar na lista de espera (DNA) | Mesmo destino inexistente. | Idem. |

Correção mínima no WordPress: na página Programas, adicione um bloco com o shortcode ou widget do formulário de pessoas (o mesmo da página Contato) e dê a ele a âncora `salekim-form-pessoas`.

## Ajustes

| Página | Botão | Hoje | Na proposta |
|---|---|---|---|
| Programas | Quero saber mais (Formação em desenvolvimento de grupos) | Abre o WhatsApp sem mensagem. O mesmo texto de botão faz coisas diferentes na mesma página. | "Quero saber mais" sempre abre o formulário. "Conversar pelo WhatsApp" abre a conversa já com o nome do programa. |
| Programas | Falar com a Genira (Supervisão, Acompanhamento individual) | Abre o WhatsApp sem mensagem; a Genira não sabe de qual programa se trata. | Mensagem pronta com o nome do programa, mais a opção de formulário. |
| Início | Pedir proposta | Vai para o topo de Para Empresas; o formulário fica no fim da página. | Vai direto ao formulário de proposta. |
| Início | Quero saber mais | Vai para Programas, onde estavam os botões quebrados. | "Saber mais sobre os programas" leva ao formulário de pessoas. |
| Início | Conhecer → (Para Empresas / Para Pessoas) | Funcionam (ok). | Texto mais claro: "Conhecer as soluções" e "Ver os programas". |
| Para Empresas | Serviços | Os quatro serviços não têm botão. | Cada serviço tem "Pedir proposta", que abre o formulário com o assunto já escolhido. |
| Clientes | Formulário "Pedir proposta" | Repete o formulário de Para Empresas e de Contato: são três cópias para manter. | Um botão que leva ao único formulário de proposta. |
| Contato | Lista "Programa de interesse" | Nomes diferentes dos da página Programas ("DNA — Mentoria", "Coaching (Executive / Life)"). | Os mesmos nomes da página Programas. |
| Contato | Dois formulários empilhados | O visitante precisa descobrir qual preencher. | Escolha entre "Sou de uma empresa" e "Quero me desenvolver"; aparece só o formulário certo. |
| Todas | Aviso de envio do formulário | Some sozinho depois de 6 segundos (`front.js`); quem lê devagar perde a confirmação. | O aviso fica na tela e é anunciado pelo leitor de tela. |
| Todas | Campo de telefone | O exemplo mostra "(11) 90000-0000", mas o campo apaga parênteses e hífen ao sair dele. | O número é formatado como no exemplo enquanto se digita. |
| Todas | Aviso de cookies: Aceito / Só os essenciais | Funcionam, mas não há como mudar a escolha depois. A LGPD exige que o consentimento possa ser revogado. | "Aceitar todos" e "Só os essenciais", mais o link "Preferências de cookies" no rodapé, que reabre o aviso. |
| Política de Privacidade | Título | "Política de Privacidade" aparece duas vezes seguidas. | Um título só. |
| Sobre | Foto da Genira | Sem texto alternativo; o leitor de tela não descreve a imagem. | Texto alternativo com o nome dela. |
| Sobre | Fim da página | Sem chamada para ação. | "Pedir proposta para empresa" e "Ver os programas para pessoas". |
| Rodapé | Links | Só tem a política de privacidade. | Atalhos para todas as páginas, contatos e preferências de cookies. |

## Funcionando (mantidos)

Menu, logotipo, e-mail, WhatsApp (11) 99914-3277, Instagram @genirarosa, botão flutuante do WhatsApp (já tem rótulo para leitor de tela) e envio dos formulários com campo isca contra spam e consentimento obrigatório.

## Como foi testado o protótipo

Navegador Chromium automatizado, em computador (1280 px) e celular (390 px), temas claro e escuro:

- todos os itens do menu, inclusive o menu do celular;
- "Pedir proposta" da página inicial, de cada serviço e da página Clientes: abrem o formulário com o assunto certo e o cursor no campo Nome;
- os seis botões da página Programas: abrem o formulário com o programa certo; os links de WhatsApp levam a mensagem com o nome do programa;
- telefone formatado como (13) 99999-8888 durante a digitação;
- envio bloqueado sem o consentimento; aviso de confirmação continua visível depois de 6 segundos;
- aviso de cookies some após a escolha e reabre pelo rodapé;
- nenhum link interno quebrado, nenhum erro de JavaScript e nenhuma rolagem lateral no celular.
