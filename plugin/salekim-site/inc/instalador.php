<?php
/**
 * Monta o site: páginas, programas, serviços, menu, página inicial, endereços amigáveis
 * e limpeza do conteúdo de demonstração. Pode rodar quantas vezes for preciso:
 * só cria o que falta e nunca apaga nada (o que sai vai para a lixeira).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $args restaurar: regrava o texto original das páginas do site; limpar_demo: manda a demonstração para a lixeira.
 * @return string[] relatório do que foi feito.
 */
function salekim_montar_site( $args = array() ) {
	$args      = wp_parse_args(
		$args,
		array(
			'restaurar'   => false,
			'limpar_demo' => false,
		)
	);
	$relatorio = array();

	if ( false === get_option( 'salekim_opcoes' ) ) {
		add_option( 'salekim_opcoes', salekim_padroes() );
		$relatorio[] = 'Configurações iniciais gravadas (e-mail, WhatsApp, Instagram, clientes).';
	}

	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		$relatorio[] = 'Endereços amigáveis ativados (ex.: /sobre/).';
	}

	/* Páginas */
	$ids   = get_option( 'salekim_paginas', array() );
	$ordem = 0;
	foreach ( salekim_paginas_padrao() as $chave => $p ) {
		$ordem++;
		$id       = ! empty( $ids[ $chave ] ) ? (int) $ids[ $chave ] : 0;
		$existente = $id ? get_post( $id ) : null;
		if ( ! $existente || 'trash' === $existente->post_status || 'page' !== $existente->post_type ) {
			$existente = get_page_by_path( $chave, OBJECT, 'page' );
			if ( $existente && 'trash' === $existente->post_status ) {
				$existente = null;
			}
		}

		$dados = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p['titulo'],
			'post_name'    => $chave,
			'post_content' => $p['conteudo'],
			'menu_order'   => $ordem,
		);

		if ( ! $existente ) {
			$id          = wp_insert_post( wp_slash( $dados ), true );
			$relatorio[] = is_wp_error( $id ) ? 'Erro ao criar a página ' . $p['titulo'] . ': ' . $id->get_error_message() : 'Página criada: ' . $p['titulo'] . '.';
		} elseif ( ! get_post_meta( $existente->ID, '_sk_pagina', true ) || $args['restaurar'] ) {
			// Página antiga com o mesmo endereço (site anterior) ou pedido de restauração:
			// guarda a versão atual nas revisões antes de gravar o texto novo.
			$id = $existente->ID;
			wp_save_post_revision( $id );
			$modo = get_post_meta( $id, '_elementor_edit_mode', true );
			if ( $modo ) {
				update_post_meta( $id, '_sk_elementor_edit_mode', $modo );
				delete_post_meta( $id, '_elementor_edit_mode' );
			}
			update_post_meta( $id, '_wp_page_template', 'default' );
			$dados['ID'] = $id;
			wp_update_post( wp_slash( $dados ) );
			$relatorio[] = ( $args['restaurar'] && get_post_meta( $id, '_sk_pagina', true ) ? 'Texto original restaurado: ' : 'Página do site anterior substituída (a versão antiga ficou nas revisões): ' ) . $p['titulo'] . '.';
		} else {
			$id = $existente->ID;
			if ( 'publish' !== $existente->post_status ) {
				wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => 'publish',
					)
				);
				$relatorio[] = 'Página publicada: ' . $p['titulo'] . '.';
			}
		}

		if ( $id && ! is_wp_error( $id ) ) {
			$ids[ $chave ] = (int) $id;
			update_post_meta( $id, '_sk_pagina', $chave );
			if ( ! get_post_meta( $id, '_sk_descricao', true ) || $args['restaurar'] ) {
				update_post_meta( $id, '_sk_descricao', $p['descricao'] );
			}
		}
	}
	update_option( 'salekim_paginas', $ids );

	/* Leitura: página inicial, artigos e privacidade */
	if ( ! empty( $ids['inicio'] ) && ( 'page' !== get_option( 'show_on_front' ) || (int) get_option( 'page_on_front' ) !== $ids['inicio'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['inicio'] );
		$relatorio[] = 'Página inicial definida: Início.';
	}
	if ( ! empty( $ids['conteudos'] ) && (int) get_option( 'page_for_posts' ) !== $ids['conteudos'] ) {
		update_option( 'page_for_posts', $ids['conteudos'] );
		$relatorio[] = 'Artigos passam a aparecer em Conteúdos.';
	}
	if ( ! empty( $ids['politica-de-privacidade'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['politica-de-privacidade'] );
	}

	/* Programas e serviços */
	$relatorio = array_merge( $relatorio, salekim_criar_itens( 'sk_programa', salekim_programas_padrao() ) );
	$relatorio = array_merge( $relatorio, salekim_criar_itens( 'sk_servico', salekim_servicos_padrao() ) );

	/* Menu */
	$relatorio = array_merge( $relatorio, salekim_criar_menu( $ids ) );

	/* Dados gerais do site */
	$frases_padrao = array( '', 'Just another WordPress site', 'Só mais um site WordPress', 'Mais um site WordPress' );
	if ( in_array( get_option( 'blogdescription' ), $frases_padrao, true ) ) {
		update_option( 'blogdescription', 'Desenvolvimento humano e organizacional' );
	}
	if ( in_array( get_option( 'blogname' ), array( '', 'Meu site', 'My WordPress Website', 'My Blog' ), true ) ) {
		update_option( 'blogname', 'SALEKIM' );
	}
	if ( ! get_option( 'timezone_string' ) && ! (float) get_option( 'gmt_offset' ) ) {
		update_option( 'timezone_string', 'America/Sao_Paulo' );
	}
	if ( 'open' === get_option( 'default_comment_status' ) ) {
		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );
		$relatorio[] = 'Comentários desligados para novos artigos (podem ser ligados em Configurações > Discussão).';
	}

	if ( $args['limpar_demo'] ) {
		$relatorio = array_merge( $relatorio, salekim_limpar_demo() );
	}

	update_option( 'salekim_versao_montada', SALEKIM_VERSAO );
	flush_rewrite_rules();

	if ( ! $relatorio ) {
		$relatorio[] = 'Tudo já estava montado. Nada foi alterado.';
	}
	return $relatorio;
}

/** Cria programas ou serviços que ainda não existem. Os que a SALEKIM apagou não voltam. */
function salekim_criar_itens( $tipo, $itens ) {
	$relatorio = array();
	$ordem     = 0;
	foreach ( $itens as $chave => $item ) {
		$ordem += 10;
		$ja     = get_posts(
			array(
				'post_type'      => $tipo,
				'post_status'    => 'any,trash',
				'meta_key'       => '_sk_chave', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $chave, // phpcs:ignore WordPress.DB.SlowDBQuery
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $ja ) {
			continue;
		}
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => $tipo,
					'post_status'  => 'publish',
					'post_title'   => $item['titulo'],
					'post_name'    => sanitize_title( 'sk_programa' === $tipo ? $item['titulo'] : $chave ),
					'post_content' => $item['texto'],
					'menu_order'   => $ordem,
				)
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_sk_chave', $chave );
		foreach ( $item as $campo => $valor ) {
			if ( in_array( $campo, array( 'titulo', 'texto' ), true ) ) {
				continue;
			}
			update_post_meta( $id, '_sk_' . $campo, (string) $valor );
		}
		$relatorio[] = ( 'sk_programa' === $tipo ? 'Programa criado: ' : 'Serviço criado: ' ) . $item['titulo'] . '.';
	}
	return $relatorio;
}

function salekim_criar_menu( $ids ) {
	$locais = get_nav_menu_locations();
	if ( ! empty( $locais['salekim-principal'] ) && wp_get_nav_menu_object( $locais['salekim-principal'] ) ) {
		return array();
	}
	$nome = 'Menu principal SALEKIM';
	$menu = wp_get_nav_menu_object( $nome );
	if ( $menu ) {
		$menu_id = $menu->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $nome );
		if ( is_wp_error( $menu_id ) ) {
			return array( 'Não foi possível criar o menu: ' . $menu_id->get_error_message() );
		}
		foreach ( salekim_paginas_padrao() as $chave => $p ) {
			if ( empty( $p['menu'] ) || empty( $ids[ $chave ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $p['titulo'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $ids[ $chave ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
	}
	$locais['salekim-principal'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locais );
	return array( 'Menu principal criado: Início, Sobre, Para empresas, Programas, Clientes e Contato.' );
}

/**
 * Conteúdo de demonstração: o artigo e a página de exemplo do WordPress,
 * textos "Lorem ipsum" do tema e o comentário de exemplo.
 *
 * @return WP_Post[]
 */
function salekim_conteudo_demo() {
	$achados = array();
	$todos   = get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => 500,
		)
	);
	$slugs   = array( 'hello-world', 'ola-mundo', 'sample-page', 'pagina-exemplo', 'pagina-de-exemplo' );
	foreach ( $todos as $post ) {
		if ( get_post_meta( $post->ID, '_sk_pagina', true ) ) {
			continue;
		}
		$rascunho_privacidade = 'draft' === $post->post_status && in_array( $post->post_name, array( 'privacy-policy', 'politica-de-privacidade' ), true );
		if ( $rascunho_privacidade || in_array( $post->post_name, $slugs, true ) || false !== stripos( $post->post_content . ' ' . $post->post_title, 'lorem ipsum' ) ) {
			$achados[] = $post;
		}
	}
	return $achados;
}

function salekim_limpar_demo() {
	$relatorio = array();
	foreach ( salekim_conteudo_demo() as $post ) {
		if ( wp_trash_post( $post->ID ) ) {
			$relatorio[] = 'Demonstração movida para a lixeira: ' . ( $post->post_title ? $post->post_title : '(sem título)' ) . '.';
		}
	}
	$comentarios = get_comments(
		array(
			'author_email' => 'wapuu@wordpress.example',
			'status'       => 'all',
		)
	);
	foreach ( $comentarios as $c ) {
		wp_trash_comment( $c->comment_ID );
		$relatorio[] = 'Comentário de exemplo movido para a lixeira.';
	}
	return $relatorio;
}
