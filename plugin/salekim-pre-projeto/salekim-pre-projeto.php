<?php
/**
 * Plugin Name:       SALEKIM Pré-projeto
 * Plugin URI:        https://www.salekim.com.br
 * Description:       Troca o bloco "Método VIDA" das listas de programas (Portfólio e Programas) pelo "Pré-projeto", com texto novo e foto da Genira com fundo transparente. Funciona junto com o plugin SALEKIM Site, sem substituí-lo. Para voltar ao bloco antigo, basta desativar este plugin.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            SALEKIM Treinamento e Desenvolvimento Ltda.
 * License:           GPL-2.0-or-later
 * Text Domain:       salekim-pre-projeto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SALEKIM_PP_VERSAO', '1.0.0' );

/** Textos do bloco. Para mudar, edite aqui. */
function salekim_pp_textos() {
	return array(
		'titulo'  => 'Pré-projeto',
		'publico' => 'Destina-se a pessoas aposentadas, próximas à aposentadoria ou a quem quer se preparar com antecedência.',
		'texto'   => array(
			'Imagine chegar à aposentadoria com mais clareza sobre o que você quer preservar, experimentar e construir.',
			'O Pré-Projeto Vida Após Aposentadoria ajuda você a organizar uma primeira visão da sua vida após a aposentadoria.',
		),
		'foto'    => plugin_dir_url( __FILE__ ) . 'img/genira-pre-projeto.webp',
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

	$bloco = preg_replace(
		'~Para quem est(?:á|&aacute;|&#225;) se aposentando ou j(?:á|&aacute;|&#225;) se aposentou\.?~u',
		esc_html( $t['publico'] ),
		$bloco,
		1
	);
	$bloco = preg_replace(
		'#Quem se aposenta sem um plano.*?como\s+apoio\.?#su',
		implode( '</p><p>', array_map( 'esc_html', $t['texto'] ) ),
		$bloco,
		1
	);

	$foto   = '<img class="salekim-pp-foto" src="' . esc_url( $t['foto'] ) . '" alt="Genira Rosa dos Santos" width="480" height="466" loading="lazy" decoding="async">';
	$titulo = $foto . '<' . $nivel . $m[2][0] . '>' . esc_html( $t['titulo'] ) . '</' . $nivel . '>';

	return substr( $html, 0, $inicio ) . $titulo . $bloco . substr( $html, $fim );
}

add_action( 'wp_head', 'salekim_pp_estilo', 50 );

function salekim_pp_estilo() {
	echo '<style id="salekim-pre-projeto">'
		. '.salekim-pp-foto{float:right;width:min(34%,190px);height:auto;margin:0 0 .75rem 1.25rem;shape-outside:url(' . esc_url( salekim_pp_textos()['foto'] ) . ');shape-margin:.5rem}'
		. '@media (max-width:600px){.salekim-pp-foto{float:none;display:block;width:150px;margin:0 0 1rem}}'
		. '</style>' . "\n";
}
