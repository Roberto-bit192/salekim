<?php
/**
 * Plugin Name:       SALEKIM Pré-projeto
 * Plugin URI:        https://www.salekim.com.br
 * Description:       Troca o bloco "Método VIDA" das listas de programas (Portfólio e Programas) pelo "Pré-projeto", com texto novo, e troca a chamada final da página Sobre por um título "Conheça nossos programas" que leva às ofertas do Portfólio. Funciona junto com o plugin SALEKIM Site, sem substituí-lo. Para voltar ao bloco antigo, basta desativar este plugin.
 * Version:           1.3.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            SALEKIM Treinamento e Desenvolvimento Ltda.
 * License:           GPL-2.0-or-later
 * Text Domain:       salekim-pre-projeto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SALEKIM_PP_VERSAO', '1.3.0' );

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
	return salekim_pp_trocar( $saida );
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
