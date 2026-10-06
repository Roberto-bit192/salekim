"""Monta o protótipo a partir de fonte.html.

Gera:
  index.html   página completa, para abrir no navegador (imagens em imagens/)
  artefato.html conteúdo com as imagens embutidas, para publicar como página única

Uso: python3 prototipo/montar.py
"""
import base64
import pathlib

AQUI = pathlib.Path(__file__).resolve().parent

PROGRAMAS = [
    "Método VIDA",
    "Formação em desenvolvimento de grupos",
    "Programa InterAgir",
    "DNA: mentoria para consultores comportamentais",
    "Supervisão para facilitadores de grupo",
    "Acompanhamento individual",
]
ASSUNTOS = [
    "Transformação organizacional",
    "Desenvolvimento de lideranças e equipes",
    "Palestra",
    "Workshop",
    "Outro assunto",
]


def opcoes(itens, vazio):
    linhas = [f'<option value="">{vazio}</option>']
    linhas += [f'<option value="{i}">{i}</option>' for i in itens]
    return "".join(linhas)


def formulario(tipo, prefixo):
    """tipo: 'empresa' ou 'pessoa'. prefixo deixa os ids únicos na página."""
    i = lambda nome: f"{prefixo}-{nome}"
    if tipo == "empresa":
        extra = (
            f'<div class="campo"><label for="{i("empresa")}">Empresa</label>'
            f'<input id="{i("empresa")}" name="empresa" type="text" autocomplete="organization"></div>'
            f'<div class="campo"><label for="{i("assunto")}">Assunto</label>'
            f'<select id="{i("assunto")}" name="assunto">{opcoes(ASSUNTOS, "Escolha, se quiser")}</select></div>'
        )
        botao = "Pedir proposta"
        dica = "Conte o contexto, o tamanho da equipe e o prazo, se já souber."
    else:
        extra = (
            f'<div class="campo inteiro"><label for="{i("programa")}">Programa de interesse</label>'
            f'<select id="{i("programa")}" name="programa">{opcoes(PROGRAMAS, "Ainda não sei")}</select></div>'
        )
        botao = "Enviar"
        dica = "Conte um pouco sobre o seu momento."
    return f"""<form class="formulario" aria-label="{'Pedir proposta' if tipo == 'empresa' else 'Saber mais sobre os programas'}">
  <div class="isca" aria-hidden="true"><label for="{i('site')}">Deixe este campo vazio</label><input class="isca-campo" id="{i('site')}" name="site_web" type="text" tabindex="-1" autocomplete="off"></div>
  <div class="campos">
    <div class="campo"><label for="{i('nome')}">Nome *</label><input id="{i('nome')}" name="nome" type="text" required autocomplete="name"></div>
    <div class="campo"><label for="{i('email')}">E-mail *</label><input id="{i('email')}" name="email" type="email" required autocomplete="email"></div>
    <div class="campo"><label for="{i('telefone')}">Telefone ou WhatsApp *</label><input id="{i('telefone')}" name="telefone" type="tel" required inputmode="tel" autocomplete="tel" placeholder="(11) 99914-3277"></div>
    {extra}
    <div class="campo inteiro"><label for="{i('mensagem')}">Mensagem</label><textarea id="{i('mensagem')}" name="mensagem"></textarea><span class="ajuda">{dica}</span></div>
  </div>
  <label class="consentimento" for="{i('lgpd')}"><input id="{i('lgpd')}" name="lgpd" type="checkbox" required><span>Autorizo o uso dos meus dados para contato sobre esta solicitação, conforme a <a href="#privacidade">política de privacidade</a>. *</span></label>
  <div class="acoes"><button class="botao" type="submit">{botao}</button></div>
  <p class="aviso" role="status" aria-live="polite" hidden></p>
</form>"""


def dados(caminho, tipo):
    return f"data:{tipo};base64," + base64.b64encode(caminho.read_bytes()).decode()


def main():
    fonte = (AQUI / "fonte.html").read_text(encoding="utf-8")
    for marca, html in {
        "<!--FORM-EMPRESA-->": formulario("empresa", "empresa"),
        "<!--FORM-PESSOA-->": formulario("pessoa", "pessoa"),
        "<!--FORM-EMPRESA-C-->": formulario("empresa", "c-empresa"),
        "<!--FORM-PESSOA-C-->": formulario("pessoa", "c-pessoa"),
    }.items():
        fonte = fonte.replace(marca, html)

    artefato = fonte.replace("{{LOGO}}", dados(AQUI / "imagens/logo.webp", "image/webp"))
    artefato = artefato.replace("{{GENIRA}}", dados(AQUI / "imagens/genira.webp", "image/webp"))
    (AQUI / "artefato.html").write_text(artefato, encoding="utf-8")

    local = fonte.replace("{{LOGO}}", "imagens/logo.webp").replace("{{GENIRA}}", "imagens/genira.webp")
    titulo_fim = local.index("</title>") + len("</title>")
    pagina = (
        '<!doctype html>\n<html lang="pt-BR">\n<head>\n<meta charset="utf-8">\n'
        '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">\n'
        + local[:titulo_fim]
        + "\n<style>[hidden]{display:none!important}img{max-width:100%}</style>"
        + local[titulo_fim:].split("<div class=\"faixa\"", 1)[0]
        + "</head>\n<body>\n<div class=\"faixa\""
        + local[titulo_fim:].split("<div class=\"faixa\"", 1)[1]
        + "\n</body>\n</html>\n"
    )
    (AQUI / "index.html").write_text(pagina, encoding="utf-8")
    print("ok:", (AQUI / "index.html").stat().st_size, (AQUI / "artefato.html").stat().st_size)


if __name__ == "__main__":
    main()
