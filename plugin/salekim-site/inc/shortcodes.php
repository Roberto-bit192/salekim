<?php
/**
 * Partes do site que vêm dos cadastros e das configurações.
 * Podem ser usadas em qualquer página, dentro de um bloco "Shortcode".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'salekim_roda', 'salekim_sc_roda' );
add_shortcode( 'salekim_foto', 'salekim_sc_foto' );
add_shortcode( 'salekim_clientes', 'salekim_sc_clientes' );
add_shortcode( 'salekim_servicos', 'salekim_sc_servicos' );
add_shortcode( 'salekim_programas', 'salekim_sc_programas' );
add_shortcode( 'salekim_depoimentos', 'salekim_sc_depoimentos' );
add_shortcode( 'salekim_contatos', 'salekim_sc_contatos' );
add_shortcode( 'salekim_whatsapp', 'salekim_sc_whatsapp' );
add_shortcode( 'salekim_dado', 'salekim_sc_dado' );
add_shortcode( 'salekim_preferencias_cookies', 'salekim_sc_preferencias_cookies' );

/** A roda: indivíduo, grupo e organização, com a Genira ao centro. */
function salekim_sc_roda() {
	$foto = esc_url( salekim_foto_url() );
	return '<figure class="roda">
<svg viewBox="-10 -10 460 460" role="img" aria-labelledby="roda-titulo">
<title id="roda-titulo">Três círculos concêntricos: organização, grupo e indivíduo, com Genira Rosa dos Santos ao centro.</title>
<defs>
<path id="roda-org" d="M220,220 m-196,0 a196,196 0 1,1 392,0 a196,196 0 1,1 -392,0"/>
<path id="roda-grupo" d="M220,220 m-142,0 a142,142 0 1,1 284,0 a142,142 0 1,1 -284,0"/>
<clipPath id="roda-recorte"><circle cx="220" cy="220" r="92"/></clipPath>
</defs>
<circle class="anel" cx="220" cy="220" r="210"/>
<circle class="anel meio" cx="220" cy="220" r="156"/>
<g class="giro">
<text><textPath href="#roda-org" startOffset="3%">organização · cultura · resultado</textPath></text>
<text><textPath href="#roda-grupo" startOffset="8%">grupo · vínculo · tarefa</textPath></text>
<circle class="ponto" cx="220" cy="10" r="5"/>
<circle class="ponto d" cx="376" cy="220" r="5"/>
<circle class="ponto" cx="64" cy="220" r="5"/>
<circle class="ponto d" cx="220" cy="376" r="5"/>
</g>
<circle class="nucleo" cx="220" cy="220" r="94"/>
<image href="' . $foto . '" x="150" y="138" width="140" height="205" clip-path="url(#roda-recorte)" preserveAspectRatio="xMidYMin meet"/>
</svg>
<figcaption>Indivíduo, grupo e organização, olhados ao mesmo tempo.</figcaption>
</figure>';
}

function salekim_sc_foto() {
	$medidas = salekim_opcao( 'foto_url' ) ? '' : ' width="257" height="376"';
	return '<div class="retrato"><img src="' . esc_url( salekim_foto_url() ) . '" alt="Genira Rosa dos Santos, sócia e consultora da SALEKIM"' . $medidas . ' loading="lazy" decoding="async"></div>';
}

/** [salekim_clientes formato="faixa|lista"] */
function salekim_sc_clientes( $atts ) {
	$atts = shortcode_atts( array( 'formato' => 'faixa' ), $atts, 'salekim_clientes' );
	if ( 'lista' === $atts['formato'] ) {
		$out = '<ul class="lista-clientes">';
		foreach ( salekim_linhas( salekim_opcao( 'clientes_lista' ) ) as $c ) {
			$out .= '<li>' . esc_html( $c[0] ) . ( $c[1] ? '<small>' . esc_html( $c[1] ) . '</small>' : '' ) . '</li>';
		}
		return $out . '<li>E outras organizações<small>de médio e grande porte</small></li></ul>';
	}
	$out = '<div class="clientes-faixa"><div class="envoltorio"><span class="rotulo">Empresas que confiaram no nosso trabalho</span><ul>';
	foreach ( salekim_linhas( salekim_opcao( 'clientes_faixa' ) ) as $c ) {
		$out .= '<li>' . esc_html( $c[0] ) . '</li>';
	}
	return $out . '</ul><a class="link-seta" href="' . esc_url( salekim_link( 'clientes' ) ) . '">Ver todos</a></div></div>';
}

function salekim_itens( $tipo ) {
	return get_posts(
		array(
			'post_type'      => $tipo,
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		)
	);
}

function salekim_texto_item( $post ) {
	return wpautop( wp_kses_post( $post->post_content ) );
}

function salekim_sc_servicos() {
	$out = '<div class="servicos">';
	foreach ( salekim_itens( 'sk_servico' ) as $s ) {
		$assunto = get_post_meta( $s->ID, '_sk_assunto', true );
		$assunto = $assunto ? $assunto : $s->post_title;
		$href    = add_query_arg( 'assunto', rawurlencode( $assunto ), salekim_link( 'para-empresas' ) ) . '#pedir-proposta';
		$out    .= '<article class="servico" id="servico-' . esc_attr( $s->post_name ) . '"><h3>' . esc_html( $s->post_title ) . '</h3>'
			. '<div class="descricao">' . salekim_texto_item( $s ) . '</div>'
			. '<p><a class="botao azul pequeno" href="' . esc_url( $href ) . '" data-assunto="' . esc_attr( $assunto ) . '">Pedir proposta</a></p></article>';
	}
	return $out . '</div>';
}

function salekim_sc_programas() {
	$out = '<div class="programas">';
	foreach ( salekim_itens( 'sk_programa' ) as $p ) {
		$titulo   = $p->post_title;
		$publico  = get_post_meta( $p->ID, '_sk_publico', true );
		$etiqueta = get_post_meta( $p->ID, '_sk_etiqueta', true );
		$botao    = get_post_meta( $p->ID, '_sk_botao', true );
		$link     = get_post_meta( $p->ID, '_sk_link', true );
		$whats    = '1' === get_post_meta( $p->ID, '_sk_whatsapp', true );
		$botao    = $botao ? $botao : 'Quero saber mais';

		$out .= '<article class="programa" id="' . esc_attr( $p->post_name ) . '"><div class="programa-texto">';
		if ( $etiqueta ) {
			$out .= '<span class="etiqueta">' . esc_html( $etiqueta ) . '</span>';
		}
		$out .= '<h3>' . esc_html( $titulo ) . '</h3>';
		if ( $publico ) {
			$out .= '<p class="publico">' . esc_html( $publico ) . '</p>';
		}
		$out .= '<div class="descricao">' . salekim_texto_item( $p ) . '</div></div><div class="programa-acoes">';
		if ( $link ) {
			$out .= '<a class="botao laranja" href="' . esc_url( $link ) . '" target="_blank" rel="noopener" data-sk-evento="programa_externo" data-programa="' . esc_attr( $titulo ) . '">' . esc_html( $botao ) . '</a>';
		} else {
			$href = add_query_arg( 'programa', rawurlencode( $titulo ), salekim_link( 'programas' ) ) . '#saber-mais';
			$out .= '<a class="botao laranja" href="' . esc_url( $href ) . '" data-programa="' . esc_attr( $titulo ) . '">' . esc_html( $botao ) . '</a>';
		}
		if ( $whats ) {
			$out .= '<a class="botao contorno pequeno" href="' . esc_url( salekim_whatsapp_url( 'Olá, Genira! Quero saber mais sobre: ' . $titulo . '.' ) ) . '" target="_blank" rel="noopener">Conversar pelo WhatsApp</a>';
		}
		$out .= '</div></article>';
	}
	return $out . '</div>';
}

/** Some quando não há depoimentos publicados. */
function salekim_sc_depoimentos( $atts ) {
	$atts  = shortcode_atts( array( 'titulo' => 'O que dizem sobre o trabalho' ), $atts, 'salekim_depoimentos' );
	$itens = salekim_itens( 'sk_depoimento' );
	if ( ! $itens ) {
		return '';
	}
	$out = '<section class="secao depoimentos" aria-labelledby="depoimentos-titulo"><p class="rotulo">Depoimentos</p><h2 id="depoimentos-titulo">' . esc_html( $atts['titulo'] ) . '</h2><div class="depoimentos-lista">';
	foreach ( $itens as $d ) {
		$quem  = array_filter( array( get_post_meta( $d->ID, '_sk_cargo', true ), get_post_meta( $d->ID, '_sk_empresa', true ) ) );
		$out  .= '<figure class="depoimento"><blockquote>' . salekim_texto_item( $d ) . '</blockquote><figcaption><strong>' . esc_html( $d->post_title ) . '</strong>';
		$out  .= $quem ? '<span>' . esc_html( implode( ', ', $quem ) ) . '</span>' : '';
		$out  .= '</figcaption></figure>';
	}
	return $out . '</div></section>';
}

function salekim_sc_contatos() {
	$email = salekim_opcao( 'email_publico' );
	$whats = salekim_opcao( 'whatsapp_texto' );
	$insta = '@' . ltrim( salekim_opcao( 'instagram' ), '@' );

	$out  = '<div class="contatos">';
	$out .= '<div class="contato-item"><span class="rotulo">E-mail</span><span class="valor" id="sk-v-email">' . esc_html( $email ) . '</span><div class="acoes"><a class="link-seta" href="mailto:' . esc_attr( $email ) . '">Escrever</a><button class="copiar" type="button" data-copiar="sk-v-email">Copiar</button></div></div>';
	$out .= '<div class="contato-item"><span class="rotulo">WhatsApp</span><span class="valor" id="sk-v-whats">' . esc_html( $whats ) . '</span><div class="acoes"><a class="link-seta" target="_blank" rel="noopener" href="' . esc_url( salekim_whatsapp_url() ) . '">Abrir conversa</a><button class="copiar" type="button" data-copiar="sk-v-whats">Copiar</button></div></div>';
	$out .= '<div class="contato-item"><span class="rotulo">Instagram</span><span class="valor">' . esc_html( $insta ) . '</span><div class="acoes"><a class="link-seta" target="_blank" rel="noopener" href="' . esc_url( salekim_instagram_url() ) . '">Abrir perfil</a></div></div>';

	$endereco = salekim_opcao( 'endereco' );
	$mapa     = salekim_opcao( 'mapa_url' );
	if ( ! $mapa && $endereco ) {
		$mapa = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $endereco );
	}
	$out .= '<div class="contato-item"><span class="rotulo">Onde estamos</span><span class="valor">' . esc_html( $endereco ? $endereco : salekim_opcao( 'cidade' ) ) . '</span>';
	$out .= $mapa ? '<div class="acoes"><a class="link-seta" target="_blank" rel="noopener" href="' . esc_url( $mapa ) . '">Ver no mapa</a></div>' : '<span class="discreto">Atendimento presencial e online.</span>';
	$out .= '</div></div>';
	return $out;
}

/** [salekim_whatsapp mensagem="..."]texto do link[/salekim_whatsapp] */
function salekim_sc_whatsapp( $atts, $conteudo = '' ) {
	$atts     = shortcode_atts( array( 'mensagem' => '' ), $atts, 'salekim_whatsapp' );
	$conteudo = $conteudo ? $conteudo : 'WhatsApp';
	return '<a href="' . esc_url( salekim_whatsapp_url( $atts['mensagem'] ) ) . '" target="_blank" rel="noopener">' . wp_kses_post( $conteudo ) . '</a>';
}

/** [salekim_dado campo="cnpj"]: mostra uma configuração. */
function salekim_sc_dado( $atts ) {
	$atts      = shortcode_atts( array( 'campo' => '' ), $atts, 'salekim_dado' );
	$liberados = array( 'razao_social', 'cnpj', 'cidade', 'endereco', 'email_publico', 'whatsapp_texto', 'instagram', 'lema' );
	return in_array( $atts['campo'], $liberados, true ) ? esc_html( salekim_opcao( $atts['campo'] ) ) : '';
}

function salekim_sc_preferencias_cookies() {
	return '<button class="link" type="button" data-sk-cookies>preferências de cookies</button>';
}
