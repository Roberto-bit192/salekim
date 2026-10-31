<?php
/**
 * Plugin Name:       SALEKIM Pré-projeto
 * Plugin URI:        https://www.salekim.com.br
 * Description:       Troca o bloco "Método VIDA" das listas de programas (Portfólio e Programas) pelo "Pré-projeto", com texto novo, e troca a chamada final da página Sobre por um título "Conheça nossos programas" que leva às ofertas do Portfólio. Acrescenta o bloco "Programa Vida após a aposentadoria", com a página de vendas em /vida-apos-aposentadoria/. Funciona junto com o plugin SALEKIM Site, sem substituí-lo. Para voltar ao bloco antigo, basta desativar este plugin.
 * Version:           1.4.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            SALEKIM Treinamento e Desenvolvimento Ltda.
 * License:           GPL-2.0-or-later
 * Text Domain:       salekim-pre-projeto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SALEKIM_PP_VERSAO', '1.4.0' );

/** Textos do bloco. Para mudar, edite aqui. */
function salekim_pp_textos() {
	return array(
		'titulo' => 'Pré-projeto',
		'texto'  => array(
			'O Pré-Projeto Vida Após Aposentadoria ajuda você a organizar uma primeira visão da sua vida após a aposentadoria.',
			'Destina-se a pessoas aposentadas, próximas à aposentadoria ou a quem quer se preparar com antecedência.',
		),
	);
}

/**
 * Os blocos de programas vêm de shortcodes do plugin SALEKIM Site ([salekim_portfolio], [salekim_programas]).
 * Aqui só o trecho do Método VIDA é reescrito, depois que o shortcode monta a página.
 */
add_filter( 'do_shortcode_tag', 'salekim_pp_filtrar', 20, 2 );

function salekim_pp_filtrar( $saida, $tag ) {
	if ( ! in_array( $tag, array( 'salekim_portfolio', 'salekim_programas' ), true ) || ! is_string( $saida ) ) {
		return $saida;
	}
	$saida = salekim_pp_trocar( $saida );
	if ( 'salekim_portfolio' === $tag ) {
		$saida = salekim_pp_novo_bloco( $saida );
	}
	return $saida;
}

function salekim_pp_trocar( $html ) {
	// Título do bloco: o primeiro elemento cujo texto é só "Método VIDA" (de preferência um título <h2>…<h6>).
	$nome = '\s*M(?:é|&eacute;|&#233;)todo\s+VIDA\s*';
	if ( ! preg_match( '~<(h[2-6])([^>]*)>' . $nome . '</\1>~iu', $html, $m, PREG_OFFSET_CAPTURE )
		&& ! preg_match( '~<(strong|b|p|div|span|a)([^>]*)>' . $nome . '</\1>~iu', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}
	$inicio = $m[0][1];
	$nivel  = $m[1][0];
	$t      = salekim_pp_textos();

	// O bloco vai até o título do programa seguinte (mesma etiqueta e mesmos atributos).
	$depois = $inicio + strlen( $m[0][0] );
	$abre   = strpos( $html, '<' . $nivel . $m[2][0] . '>', $depois );
	$fim    = false !== $abre ? $abre : strlen( $html );
	$bloco  = substr( $html, $depois, $fim - $depois );

	// A linha "Para quem está se aposentando…" sai: o texto novo já diz para quem é.
	$publico = 'Para quem est(?:á|&aacute;|&#225;) se aposentando ou j(?:á|&aacute;|&#225;) se aposentou\.?';
	$sem     = preg_replace( '~<(\w+)[^>]*>\s*(?:<(\w+)[^>]*>\s*)?' . $publico . '\s*(?:</\2>\s*)?</\1>~u', '', $bloco, 1, $quantos );
	$bloco   = $quantos ? $sem : preg_replace( '~' . $publico . '~u', '', $bloco, 1 );
	$bloco = preg_replace(
		'#Quem se aposenta sem um plano.*?como\s+apoio\.?#su',
		implode( '</p><p>', array_map( 'esc_html', $t['texto'] ) ),
		$bloco,
		1
	);

	$titulo = '<' . $nivel . $m[2][0] . '>' . esc_html( $t['titulo'] ) . '</' . $nivel . '>';

	return substr( $html, 0, $inicio ) . $titulo . $bloco . substr( $html, $fim );
}

/**
 * Página Sobre: a chamada final ("Faça contato conosco" e dois botões) vira só o título
 * "Conheça nossos programas", que leva às ofertas do Portfólio.
 * A troca é feita na página já pronta, porque esse bloco pode vir do editor, do Elementor ou de um shortcode.
 */
add_action( 'template_redirect', 'salekim_pp_sobre' );

function salekim_pp_sobre() {
	if ( is_admin() || ! is_page( array( 'sobre', 'Sobre' ) ) ) {
		return;
	}
	ob_start( 'salekim_pp_sobre_trocar' );
}

function salekim_pp_sobre_trocar( $html ) {
	if ( ! preg_match( '~(<h[1-6][^>]*>)\s*Fa(?:ç|&ccedil;|&#231;)a contato conosco\s*(</h[1-6]>)~iu', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}
	$link   = home_url( '/portfolio/#ofertas' );
	$titulo = $m[1][0] . '<a class="salekim-pp-link" href="' . esc_url( $link ) . '">Conheça nossos programas</a>' . $m[2][0];
	$inicio = $m[0][1];
	$depois = $inicio + strlen( $m[0][0] );

	// Os botões ficam logo depois do título, ainda dentro do quadro: o trecho vai até o próximo título ou o rodapé.
	$fim = strlen( $html );
	if ( preg_match( '~<h[1-6][\s>]|<footer[\s>]~i', $html, $p, PREG_OFFSET_CAPTURE, $depois ) ) {
		$fim = $p[0][1];
	}
	$trecho = substr( $html, $depois, $fim - $depois );
	$trecho = preg_replace( '~<a\b[^>]*>\s*(?:<[^>]+>\s*)*(?:Pedir proposta para empresa|Ver os programas para pessoas)\s*(?:</[^>]+>\s*)*?</a>~iu', '', $trecho );
	// Tira as caixas que ficaram vazias (onde estavam os botões).
	do {
		$trecho = preg_replace( '~<(div|p|span|li|ul)\b[^>]*class="[^"]*(?:button|botao|acoes)[^"]*"[^>]*>\s*</\1>|<p>\s*</p>~i', '', $trecho, -1, $quantos );
	} while ( $quantos );

	$estilo = '<style>.salekim-pp-link{color:inherit!important;text-decoration:none}.salekim-pp-link::after{content:" →"}.salekim-pp-link:hover{text-decoration:underline;text-underline-offset:.15em}</style>';
	return substr( $html, 0, $inicio ) . $titulo . $trecho . $estilo . substr( $html, $fim );
}

/* ---------- Programa Vida após a aposentadoria ---------- */

/** Endereço da página de vendas, servida pelo próprio plugin. */
function salekim_pp_vida_url() {
	return home_url( '/vida-apos-aposentadoria/' );
}

function salekim_pp_vida_textos() {
	return array(
		'titulo'  => 'Programa Vida após a aposentadoria',
		'publico' => 'Para quem se aposentou e quer um plano para os próximos anos.',
		'texto'   => 'Um processo guiado, em grupo pequeno e com acompanhamento individual, para transformar a falta de direção do início da aposentadoria em um plano real para os próximos anos. São 45 dias, conduzidos pelo Método V.I.D.A.',
	);
}

/**
 * Cria o bloco do Programa Vida no Portfólio copiando o bloco "Formação em desenvolvimento de grupos"
 * (mesmo formato), com título e texto próprios e "Saiba mais" no lugar de "Em breve".
 * Entra logo antes da Formação, depois do Pré-projeto.
 */
function salekim_pp_novo_bloco( $html ) {
	$t = salekim_pp_vida_textos();
	if ( false !== strpos( $html, $t['titulo'] ) || ! class_exists( 'DOMDocument' ) ) {
		return $html;
	}
	$modelo = 'Formação em desenvolvimento de grupos';

	$doc   = new DOMDocument( '1.0', 'UTF-8' );
	$antes = libxml_use_internal_errors( true );
	$ok    = $doc->loadHTML( '<?xml encoding="UTF-8"><div id="salekim-pp-raiz">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $antes );
	if ( ! $ok ) {
		return $html;
	}
	$raiz  = $doc->getElementById( 'salekim-pp-raiz' );
	$xpath = new DOMXPath( $doc );

	// Título da Formação.
	$titulo = null;
	foreach ( $xpath->query( '//h2|//h3|//h4|//h5|//h6' ) as $h ) {
		if ( salekim_pp_texto( $h ) === $modelo ) {
			$titulo = $h;
			break;
		}
	}
	if ( ! $titulo || ! $raiz ) {
		return $html;
	}

	// O bloco é o maior elemento em volta do título que não contém outro título de programa.
	$bloco = $titulo;
	while ( $bloco->parentNode && ! $bloco->parentNode->isSameNode( $raiz ) && 1 === $xpath->query( './/' . $titulo->nodeName, $bloco->parentNode )->length ) {
		$bloco = $bloco->parentNode;
	}
	if ( $bloco->isSameNode( $titulo ) ) {
		return $html;
	}

	// Botão "Saiba mais" do Pré-projeto: serve de modelo para o novo botão.
	$modelo_botao = null;
	foreach ( $xpath->query( '//a' ) as $a ) {
		if ( 'Saiba mais' === salekim_pp_texto( $a ) ) {
			$modelo_botao = $a;
			break;
		}
	}

	$novo = $bloco->cloneNode( true );
	$xn   = new DOMXPath( $doc );
	foreach ( $xn->query( './/*[@id]', $novo ) as $el ) {
		$el->removeAttribute( 'id' );
	}
	if ( $novo instanceof DOMElement && $novo->hasAttribute( 'id' ) ) {
		$novo->setAttribute( 'id', 'programa-vida-apos-aposentadoria' );
	}

	// Título.
	foreach ( $xn->query( './/' . $titulo->nodeName, $novo ) as $h ) {
		salekim_pp_por_texto( $h, $t['titulo'] );
	}

	// Público e descrição: os parágrafos de texto do bloco (fora dos botões).
	$paragrafos = array();
	foreach ( $xn->query( './/p[not(ancestor::a) and not(.//a) and not(.//button)]', $novo ) as $p ) {
		$texto = salekim_pp_texto( $p );
		if ( '' !== $texto && 'Em breve' !== $texto ) {
			$paragrafos[] = $p;
		}
	}
	foreach ( $paragrafos as $i => $p ) {
		if ( 0 === $i ) {
			salekim_pp_por_texto( $p, $t['publico'] );
		} elseif ( 1 === $i ) {
			salekim_pp_por_texto( $p, $t['texto'] );
		} else {
			$p->parentNode->removeChild( $p );
		}
	}

	// "Em breve" vira o botão "Saiba mais" com o link da página de vendas.
	$em_breve = null;
	foreach ( $xn->query( './/*', $novo ) as $el ) {
		if ( 'Em breve' === salekim_pp_texto( $el ) ) {
			$em_breve = $el; // O último encontrado é o mais interno.
		}
	}
	while ( $em_breve && $em_breve->parentNode && ! $em_breve->parentNode->isSameNode( $novo ) && 'Em breve' === salekim_pp_texto( $em_breve->parentNode ) && 1 === $em_breve->parentNode->childNodes->length ) {
		$em_breve = $em_breve->parentNode;
	}
	if ( $modelo_botao ) {
		$botao = $modelo_botao->cloneNode( true );
	} else {
		$botao = $doc->createElement( 'a', 'Saiba mais' );
		$botao->setAttribute( 'class', 'botao laranja' );
	}
	$botao->setAttribute( 'href', salekim_pp_vida_url() );
	$botao->removeAttribute( 'target' );
	$botao->removeAttribute( 'id' );
	if ( $em_breve ) {
		$em_breve->parentNode->replaceChild( $botao, $em_breve );
	} else {
		$whats = $xn->query( './/a[contains(@href, "wa.me")]', $novo )->item( 0 );
		if ( $whats ) {
			$whats->parentNode->insertBefore( $botao, $whats );
		}
	}

	// Mensagem pronta do WhatsApp com o nome do programa novo.
	foreach ( $xn->query( './/a[contains(@href, "wa.me")]', $novo ) as $a ) {
		$a->setAttribute( 'href', str_replace( array( rawurlencode( $modelo ), urlencode( $modelo ) ), rawurlencode( $t['titulo'] ), $a->getAttribute( 'href' ) ) );
	}

	$bloco->parentNode->insertBefore( $novo, $bloco );

	$saida = '';
	foreach ( $raiz->childNodes as $no ) {
		$saida .= $doc->saveHTML( $no );
	}
	return $saida;
}

function salekim_pp_texto( $no ) {
	return trim( preg_replace( '/\s+/u', ' ', $no->textContent ) );
}

/** Troca o texto de um elemento, mantendo a etiqueta interna única (ex.: <strong>), se houver. */
function salekim_pp_por_texto( $el, $texto ) {
	$alvo = $el;
	while ( 1 === $alvo->childNodes->length && $alvo->firstChild instanceof DOMElement ) {
		$alvo = $alvo->firstChild;
	}
	while ( $alvo->firstChild ) {
		$alvo->removeChild( $alvo->firstChild );
	}
	$alvo->appendChild( $alvo->ownerDocument->createTextNode( $texto ) );
}

/** Página de vendas em /vida-apos-aposentadoria/, enquanto não houver uma página do WordPress nesse endereço. */
add_action( 'template_redirect', 'salekim_pp_pagina_vendas', 0 );

function salekim_pp_pagina_vendas() {
	if ( ! is_404() ) {
		return;
	}
	$caminho = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$base    = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( '' !== $base && 0 === strpos( $caminho, $base . '/' ) ) {
		$caminho = substr( $caminho, strlen( $base ) + 1 );
	}
	if ( 'vida-apos-aposentadoria' !== $caminho ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	readfile( plugin_dir_path( __FILE__ ) . 'paginas/vida-apos-aposentadoria.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
