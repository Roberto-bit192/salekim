# SALEKIM: proposta de novo layout

Proposta de layout para [salekim.com.br](https://salekim.com.br), feita a partir do perfil publicado em [salekim.com.br/sobre](https://salekim.com.br/sobre/) (lido em 6 de outubro de 2026).

## Plugin que monta o site (novo)

`dist/salekim-site.zip` é o plugin WordPress **SALEKIM Site 2.0**. Instalado e ativado em www.salekim.com.br, ele monta o site inteiro: páginas com os textos do briefing, programas, serviços, menu, formulários de proposta e de programas (com envio para genira@salekim.com.br), WhatsApp, aviso de cookies (LGPD), Google Analytics e Meta Pixel com consentimento e SEO básico. Instruções em [`plugin/salekim-site/LEIA-ME.md`](plugin/salekim-site/LEIA-ME.md).

Para gerar o zip de novo depois de mudar o código: `bash plugin/montar-zip.sh`.

## O que tem aqui

| Arquivo | O que é |
|---|---|
| `plugin/salekim-site/` | Código do plugin WordPress que monta o site. |
| `dist/salekim-site.zip` | Plugin pronto para instalar (Plugins > Adicionar novo > Enviar plugin). |
| `prototipo/index.html` | Protótipo navegável com todas as páginas do site. Abra direto no navegador. |
| `prototipo/fonte.html` | Fonte do protótipo (textos, estilos e comportamento dos botões). |
| `prototipo/montar.py` | Gera `index.html` e `artefato.html` (versão com imagens embutidas, para compartilhar como página única). |
| `prototipo/imagens/` | Logotipo com fundo transparente e foto da Genira, tirados do site atual. |
| `docs/revisao-botoes.md` | Revisão de todos os botões do site atual e o que muda na proposta. |
| `docs/revisao-texto.md` | Regra de maiúsculas e lista das mudanças de texto. |

Depois de editar `fonte.html`, rode:

```bash
python3 prototipo/montar.py
```

## A ideia do layout

- **A roda.** A abertura mostra três círculos concêntricos (indivíduo, grupo, organização) com a Genira ao centro. É como o próprio perfil descreve a prática: a organização vista como um sistema, em três níveis ao mesmo tempo. Também lembra o formato em roda dos grupos que ela conduz como didata da SBDG.
- **Do processo às pessoas.** A página Sobre virou uma linha do tempo, da siderurgia à fundação da SALEKIM em 1996, porque essa transição de carreira é o centro da história dela.
- **Tarefa e emoção.** A seção "Como trabalhamos" reúne os três níveis, as três etapas (diagnóstico cuidadoso, intervenção vivencial, acompanhamento próximo) e a frase sobre tarefa e emoção.
- **Profundidade e leveza.** Títulos em serifa leve, bastante espaço em branco e uma só animação, lenta, na roda. A animação é desligada para quem configurou o sistema para reduzir movimento.
- **Dois públicos, duas folhas.** As folhas azul e laranja do logotipo marcam os dois caminhos: azul para empresas, laranja para pessoas. Os botões de cada caminho usam a mesma cor.
- **Clientes logo abaixo da abertura.** A área de depoimentos vazia saiu da página inicial e volta quando houver depoimentos autorizados.
- **Tema escuro.** O protótipo também funciona no modo escuro do celular ou do computador.

## Para colocar no ar

O site atual é WordPress com Elementor e um plugin próprio (`salekim-site`), cujo código não está neste repositório. Sugestão de ordem:

1. **Agora:** corrigir os três botões quebrados da página Programas (ver `docs/revisao-botoes.md`).
2. Aplicar a revisão de maiúsculas e de texto.
3. Levar o novo layout para o tema ou para o Elementor, usando o protótipo como referência.
