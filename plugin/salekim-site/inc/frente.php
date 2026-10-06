<?php
/**
 * Layout do site: o plugin desenha cabeçalho, páginas, artigos e rodapé,
 * qualquer que seja o tema ativo. Os estilos do tema não são carregados.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Permite desligar o layout por código, se um dia a SALEKIM adotar um tema próprio. */
function salekim_layout_ativo() {
	return (bool) apply_filters( 'salekim_usar_layout', true );
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		register_nav_menus( array( 'salekim-principal' => 'Menu principal do site SALEKIM' ) );
	},
	20
);

add_filter(
	'document_title_separator',
	function () {
		return '·';
	}
);

add_filter( 'template_include', 'salekim_modelo', 99 );

function salekim_modelo( $modelo ) {
	if ( ! salekim_layout_ativo() || is_feed() || is_embed() || is_robots() ) {
		return $modelo;
	}
	if ( is_404() ) {
		return SALEKIM_DIR . 'templates/404.php';
	}
	if ( is_page() ) {
		return SALEKIM_DIR . 'templates/pagina.php';
	}
	if ( is_singular() ) {
		return SALEKIM_DIR . 'templates/artigo.php';
	}
	return SALEKIM_DIR . 'templates/blog.php';
}

/** Página montada pelo plugin (layout em seções) ou página comum (texto corrido). */
function salekim_e_pagina_do_site( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();
	return (bool) get_post_meta( $post_id, '_sk_pagina', true );
}

add_action( 'wp_enqueue_scripts', 'salekim_recursos', 20 );

function salekim_recursos() {
	if ( ! salekim_layout_ativo() ) {
		return;
	}
	$css = SALEKIM_DIR . 'assets/css/salekim.css';
	$js  = SALEKIM_DIR . 'assets/js/salekim.js';
	wp_enqueue_style( 'salekim', SALEKIM_URL . 'assets/css/salekim.css', array(), SALEKIM_VERSAO . '.' . filemtime( $css ) );
	wp_enqueue_script( 'salekim', SALEKIM_URL . 'assets/js/salekim.js', array(), SALEKIM_VERSAO . '.' . filemtime( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script(
		'salekim',
		'salekimConfig',
		array(
			'ga4'   => preg_replace( '/[^A-Za-z0-9-]/', '', salekim_opcao( 'ga4_id' ) ),
			'pixel' => preg_replace( '/\D/', '', salekim_opcao( 'pixel_id' ) ),
		)
	);
}

add_action( 'wp_enqueue_scripts', 'salekim_remover_tema', 999 );
add_action( 'wp_print_styles', 'salekim_remover_tema', 999 );

/** Tira os estilos e scripts do tema ativo (e dos estilos globais dele), que brigariam com o layout. */
function salekim_remover_tema() {
	if ( ! salekim_layout_ativo() || is_admin() ) {
		return;
	}
	$pastas = array_unique( array( get_template_directory_uri(), get_stylesheet_directory_uri() ) );
	foreach ( array( wp_styles(), wp_scripts() ) as $fila ) {
		foreach ( (array) $fila->queue as $handle ) {
			$src = isset( $fila->registered[ $handle ] ) ? (string) $fila->registered[ $handle ]->src : '';
			foreach ( $pastas as $pasta ) {
				if ( $src && 0 === strpos( $src, $pasta ) ) {
					$fila->dequeue( $handle );
				}
			}
		}
	}
	foreach ( array( 'global-styles', 'classic-theme-styles', 'wp-block-library-theme' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}

add_filter( 'body_class', 'salekim_classes_corpo', 99 );

function salekim_classes_corpo( $classes ) {
	if ( ! salekim_layout_ativo() ) {
		return $classes;
	}
	// Classes de construtores de página (Elementor) trazem cores e fontes de outro layout.
	$classes   = array_filter(
		$classes,
		function ( $c ) {
			return 0 !== strpos( $c, 'elementor-kit' );
		}
	);
	$classes[] = 'salekim';
	return $classes;
}

add_action( 'wp_head', 'salekim_cabeca', 2 );

function salekim_cabeca() {
	if ( ! salekim_layout_ativo() ) {
		return;
	}
	$fontes = SALEKIM_URL . 'assets/fonts/';
	echo '<link rel="preload" href="' . esc_url( $fontes . 'spectral-300.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<link rel="preload" href="' . esc_url( $fontes . 'hanken-grotesk.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<meta name="theme-color" content="#003C24">' . "\n";
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( SALEKIM_URL . 'assets/img/folhas.svg' ) . '" type="image/svg+xml">' . "\n";
	}
}

/* ---------- Partes do layout ---------- */

function salekim_menu() {
	if ( has_nav_menu( 'salekim-principal' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'salekim-principal',
				'container'      => false,
				'menu_class'     => 'menu-lista',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		return;
	}
	echo '<ul class="menu-lista">';
	$atual = get_queried_object_id();
	$ids   = get_option( 'salekim_paginas', array() );
	foreach ( salekim_paginas_padrao() as $chave => $p ) {
		if ( empty( $p['menu'] ) ) {
			continue;
		}
		$id   = isset( $ids[ $chave ] ) ? (int) $ids[ $chave ] : 0;
		$aria = $id && $id === $atual ? ' aria-current="page"' : '';
		echo '<li><a href="' . esc_url( salekim_link( $chave ) ) . '"' . $aria . '>' . esc_html( $p['titulo'] ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</ul>';
}

/** Itens do rodapé: os mesmos do menu, mais Conteúdos quando houver artigos. */
function salekim_links_rodape() {
	$itens = array();
	if ( has_nav_menu( 'salekim-principal' ) ) {
		$locais = get_nav_menu_locations();
		foreach ( (array) wp_get_nav_menu_items( $locais['salekim-principal'] ) as $item ) {
			if ( ! $item->menu_item_parent ) {
				$itens[ $item->url ] = $item->title;
			}
		}
	} else {
		foreach ( salekim_paginas_padrao() as $chave => $p ) {
			if ( ! empty( $p['menu'] ) ) {
				$itens[ salekim_link( $chave ) ] = $p['titulo'];
			}
		}
	}
	if ( wp_count_posts()->publish > 0 ) {
		$itens[ salekim_link( 'conteudos' ) ] = 'Conteúdos';
	}
	return $itens;
}

add_action( 'wp_footer', 'salekim_extras_rodape', 5 );

/** Aviso de cookies e botão flutuante do WhatsApp. */
function salekim_extras_rodape() {
	if ( ! salekim_layout_ativo() ) {
		return;
	}
	if ( salekim_tem_medicao() ) {
		?>
<div class="cookies" id="sk-cookies" role="region" aria-label="Aviso de cookies" hidden>
	<div class="caixa">
		<p>Usamos cookies essenciais e, com o seu consentimento, cookies de medição (Google Analytics e Meta Pixel) para entender o uso do site. Saiba mais na <a href="<?php echo esc_url( salekim_link( 'politica-de-privacidade' ) ); ?>">política de privacidade</a>.</p>
		<div class="acoes">
			<button class="botao pequeno" type="button" id="sk-ck-aceitar">Aceitar todos</button>
			<button class="botao contorno pequeno" type="button" id="sk-ck-essenciais">Só os essenciais</button>
		</div>
	</div>
</div>
		<?php
	}
	?>
<a class="whatsapp" target="_blank" rel="noopener" href="<?php echo esc_url( salekim_whatsapp_url() ); ?>" aria-label="Conversar com a SALEKIM pelo WhatsApp" data-sk-evento="whatsapp">
	<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.4-.3z"/></svg>
	<span>WhatsApp</span>
</a>
	<?php
}

function salekim_tem_medicao() {
	return '' !== salekim_opcao( 'ga4_id' ) || '' !== salekim_opcao( 'pixel_id' );
}
