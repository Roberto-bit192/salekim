<?php
/**
 * SEO básico: descrição de cada página, Open Graph, dados estruturados para o Google
 * (empresa, endereço e contato, que ajudam o Google Meu Negócio) e verificação do Search Console.
 * Se um plugin de SEO (Yoast, Rank Math, AIOSEO, SEOPress) estiver ativo, ele assume.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function salekim_outro_seo() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

add_action( 'wp_head', 'salekim_seo', 3 );

function salekim_seo() {
	$gsc = salekim_opcao( 'gsc_verificacao' );
	if ( $gsc ) {
		// Aceita o código sozinho ou a tag inteira copiada do Search Console.
		if ( preg_match( '/content=["\']([^"\']+)/', $gsc, $m ) ) {
			$gsc = $m[1];
		}
		echo '<meta name="google-site-verification" content="' . esc_attr( $gsc ) . '">' . "\n";
	}
	if ( salekim_outro_seo() ) {
		return;
	}

	$descricao = salekim_descricao_atual();
	$titulo    = wp_get_document_title();
	$url       = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : '' );
	$imagem    = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : salekim_foto_url();

	if ( $descricao ) {
		echo '<meta name="description" content="' . esc_attr( $descricao ) . '">' . "\n";
	}
	echo '<meta property="og:locale" content="pt_BR">' . "\n";
	echo '<meta property="og:site_name" content="SALEKIM">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $titulo ) . '">' . "\n";
	if ( $descricao ) {
		echo '<meta property="og:description" content="' . esc_attr( $descricao ) . '">' . "\n";
	}
	if ( $url ) {
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	}
	echo '<meta property="og:image" content="' . esc_url( $imagem ) . '">' . "\n";
	echo '<meta name="twitter:card" content="summary">' . "\n";

	if ( is_front_page() ) {
		echo '<script type="application/ld+json">' . wp_json_encode( salekim_dados_estruturados(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}

function salekim_descricao_atual() {
	if ( is_front_page() ) {
		$id = (int) get_option( 'page_on_front' );
	} elseif ( is_home() ) {
		$id = (int) get_option( 'page_for_posts' );
	} elseif ( is_singular() ) {
		$id = get_queried_object_id();
	} else {
		return get_bloginfo( 'description' );
	}
	$descricao = $id ? get_post_meta( $id, '_sk_descricao', true ) : '';
	if ( ! $descricao && $id && is_singular() ) {
		$descricao = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ), 30, '…' );
	}
	return $descricao;
}

function salekim_dados_estruturados() {
	$telefone = '+' . preg_replace( '/\D/', '', salekim_opcao( 'whatsapp' ) );
	$endereco = array(
		'@type'           => 'PostalAddress',
		'addressLocality' => 'São Vicente',
		'addressRegion'   => 'SP',
		'addressCountry'  => 'BR',
	);
	if ( salekim_opcao( 'endereco' ) ) {
		$endereco['streetAddress'] = salekim_opcao( 'endereco' );
	}
	return array(
		'@context'     => 'https://schema.org',
		'@type'        => 'ProfessionalService',
		'@id'          => home_url( '/#empresa' ),
		'name'         => 'SALEKIM',
		'legalName'    => salekim_opcao( 'razao_social' ),
		'taxID'        => salekim_opcao( 'cnpj' ),
		'url'          => home_url( '/' ),
		'logo'         => salekim_logo_url(),
		'image'        => salekim_foto_url(),
		'description'  => 'Desenvolvimento humano e organizacional: transformação organizacional, lideranças e equipes, palestras, workshops e programas para pessoas.',
		'slogan'       => salekim_opcao( 'lema' ),
		'email'        => salekim_opcao( 'email_publico' ),
		'telephone'    => $telefone,
		'foundingDate' => '1996',
		'address'      => $endereco,
		'areaServed'   => 'BR',
		'founder'      => array(
			'@type'    => 'Person',
			'name'     => 'Genira Rosa dos Santos',
			'jobTitle' => 'Sócia e consultora',
		),
		'sameAs'       => array( salekim_instagram_url() ),
	);
}

/* ---------- Campo "Descrição para o Google" nas páginas e artigos ---------- */

add_action(
	'add_meta_boxes',
	function () {
		if ( salekim_outro_seo() ) {
			return;
		}
		foreach ( array( 'page', 'post' ) as $tipo ) {
			add_meta_box( 'salekim_seo', 'Descrição para o Google', 'salekim_caixa_seo', $tipo, 'side', 'default' );
		}
	}
);

function salekim_caixa_seo( $post ) {
	wp_nonce_field( 'salekim_seo', 'salekim_seo_nonce' );
	$valor = get_post_meta( $post->ID, '_sk_descricao', true );
	echo '<label class="screen-reader-text" for="sk_descricao">Descrição para o Google</label>';
	echo '<textarea id="sk_descricao" name="sk_descricao" rows="5" style="width:100%" maxlength="300">' . esc_textarea( $valor ) . '</textarea>';
	echo '<p class="description">Uma ou duas frases, até 160 caracteres. Aparece no Google abaixo do título da página.</p>';
}

add_action(
	'save_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['salekim_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['salekim_seo_nonce'] ), 'salekim_seo' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$valor = isset( $_POST['sk_descricao'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sk_descricao'] ) ) : '';
		update_post_meta( $post_id, '_sk_descricao', $valor );
	}
);
