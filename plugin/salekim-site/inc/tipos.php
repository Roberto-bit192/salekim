<?php
/**
 * Cadastros editáveis pelo painel: programas, serviços, depoimentos e mensagens recebidas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'salekim_registrar_tipos' );

function salekim_registrar_tipos() {
	$base = array(
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => 'salekim',
		'show_in_rest'       => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'has_archive'        => false,
		'rewrite'            => false,
	);

	register_post_type(
		'sk_programa',
		$base + array(
			'labels'   => array(
				'name'          => 'Programas',
				'singular_name' => 'Programa',
				'add_new'       => 'Novo programa',
				'add_new_item'  => 'Novo programa',
				'edit_item'     => 'Editar programa',
				'all_items'     => 'Programas',
				'menu_name'     => 'Programas',
			),
			'supports' => array( 'title', 'editor', 'page-attributes' ),
		)
	);

	register_post_type(
		'sk_servico',
		$base + array(
			'labels'   => array(
				'name'          => 'Serviços para empresas',
				'singular_name' => 'Serviço',
				'add_new'       => 'Novo serviço',
				'add_new_item'  => 'Novo serviço',
				'edit_item'     => 'Editar serviço',
				'all_items'     => 'Serviços para empresas',
				'menu_name'     => 'Serviços',
			),
			'supports' => array( 'title', 'editor', 'page-attributes' ),
		)
	);

	register_post_type(
		'sk_depoimento',
		$base + array(
			'labels'   => array(
				'name'          => 'Depoimentos',
				'singular_name' => 'Depoimento',
				'add_new'       => 'Novo depoimento',
				'add_new_item'  => 'Novo depoimento',
				'edit_item'     => 'Editar depoimento',
				'all_items'     => 'Depoimentos',
				'menu_name'     => 'Depoimentos',
			),
			'supports' => array( 'title', 'editor', 'page-attributes' ),
		)
	);

	register_post_type(
		'sk_mensagem',
		$base + array(
			'labels'       => array(
				'name'          => 'Mensagens recebidas',
				'singular_name' => 'Mensagem',
				'edit_item'     => 'Mensagem recebida',
				'all_items'     => 'Mensagens recebidas',
				'menu_name'     => 'Mensagens',
				'not_found'     => 'Nenhuma mensagem ainda.',
			),
			'supports'     => array( 'title' ),
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		)
	);
}

/* ---------- Campos extras ---------- */

/** Campos de cada tipo: chave => [rótulo, tipo, ajuda]. */
function salekim_campos_tipo( $tipo ) {
	$campos = array(
		'sk_programa'   => array(
			'publico'  => array( 'Para quem é', 'texto', 'Aparece em itálico abaixo do nome. Ex.: Para quem está se aposentando ou já se aposentou.' ),
			'etiqueta' => array( 'Etiqueta', 'escolha', 'Destaque pequeno acima do nome.' ),
			'botao'    => array( 'Texto do botão principal', 'texto', 'Ex.: Quero saber mais, Entrar na lista de espera, Quero participar.' ),
			'link'     => array( 'Link externo do botão (opcional)', 'url', 'Ex.: página de vendas do VIDA na Hotmart. Em branco, o botão abre o formulário de programas com este programa já escolhido.' ),
			'whatsapp' => array( 'Mostrar botão "Conversar pelo WhatsApp"', 'marcar', 'A conversa já abre com o nome do programa.' ),
		),
		'sk_servico'    => array(
			'assunto' => array( 'Assunto no formulário de proposta', 'texto', 'Em branco, usa o nome do serviço. Precisa ser igual a um dos assuntos em SALEKIM > Configurações.' ),
		),
		'sk_depoimento' => array(
			'cargo'   => array( 'Cargo', 'texto', '' ),
			'empresa' => array( 'Empresa', 'texto', '' ),
		),
	);
	return isset( $campos[ $tipo ] ) ? $campos[ $tipo ] : array();
}

function salekim_etiquetas() {
	return array( '', 'Inscrições abertas', 'Lista de espera', 'Em breve', 'Novo' );
}

add_action( 'add_meta_boxes', 'salekim_caixas' );

function salekim_caixas() {
	foreach ( array( 'sk_programa', 'sk_servico', 'sk_depoimento' ) as $tipo ) {
		add_meta_box( 'salekim_campos', 'Detalhes', 'salekim_caixa_campos', $tipo, 'normal', 'high' );
	}
	add_meta_box( 'salekim_mensagem', 'Mensagem', 'salekim_caixa_mensagem', 'sk_mensagem', 'normal', 'high' );
	remove_meta_box( 'submitdiv', 'sk_mensagem', 'side' );
}

function salekim_caixa_campos( $post ) {
	wp_nonce_field( 'salekim_campos', 'salekim_campos_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( salekim_campos_tipo( $post->post_type ) as $chave => $c ) {
		$valor = get_post_meta( $post->ID, '_sk_' . $chave, true );
		$id    = 'sk_' . $chave;
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $c[0] ) . '</label></th><td>';
		if ( 'escolha' === $c[1] ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
			foreach ( salekim_etiquetas() as $et ) {
				echo '<option value="' . esc_attr( $et ) . '"' . selected( $valor, $et, false ) . '>' . esc_html( $et ? $et : 'Nenhuma' ) . '</option>';
			}
			echo '</select>';
		} elseif ( 'marcar' === $c[1] ) {
			echo '<label><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="1"' . checked( $valor, '1', false ) . '> Sim</label>';
		} else {
			$type = 'url' === $c[1] ? 'url' : 'text';
			echo '<input class="large-text" type="' . $type . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $valor ) . '">';
		}
		if ( $c[2] ) {
			echo '<p class="description">' . esc_html( $c[2] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	if ( 'sk_depoimento' === $post->post_type ) {
		echo '<p class="description">Escreva o depoimento no campo de texto acima e o nome da pessoa no título. Publique só depoimentos autorizados por escrito.</p>';
	}
	if ( 'sk_programa' === $post->post_type ) {
		echo '<p class="description">A ordem dos programas na página segue o campo "Ordem" (caixa Atributos, ao lado).</p>';
	}
}

add_action( 'save_post', 'salekim_salvar_campos', 10, 2 );

function salekim_salvar_campos( $post_id, $post ) {
	if ( ! isset( $_POST['salekim_campos_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['salekim_campos_nonce'] ), 'salekim_campos' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( salekim_campos_tipo( $post->post_type ) as $chave => $c ) {
		$nome = 'sk_' . $chave;
		if ( 'marcar' === $c[1] ) {
			update_post_meta( $post_id, '_sk_' . $chave, isset( $_POST[ $nome ] ) ? '1' : '0' );
			continue;
		}
		$valor = isset( $_POST[ $nome ] ) ? wp_unslash( $_POST[ $nome ] ) : '';
		$valor = 'url' === $c[1] ? esc_url_raw( $valor ) : sanitize_text_field( $valor );
		update_post_meta( $post_id, '_sk_' . $chave, $valor );
	}
}

/* ---------- Mensagens recebidas (só leitura) ---------- */

function salekim_caixa_mensagem( $post ) {
	$dados = get_post_meta( $post->ID, '_sk_dados', true );
	if ( ! is_array( $dados ) ) {
		$dados = array();
	}
	echo '<table class="widefat striped"><tbody>';
	foreach ( $dados as $rotulo => $valor ) {
		echo '<tr><th style="width:12rem">' . esc_html( $rotulo ) . '</th><td>' . nl2br( esc_html( $valor ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
	$email = isset( $dados['E-mail'] ) ? $dados['E-mail'] : '';
	if ( $email ) {
		echo '<p><a class="button button-primary" href="mailto:' . esc_attr( $email ) . '">Responder por e-mail</a></p>';
	}
	$envio = get_post_meta( $post->ID, '_sk_email_enviado', true );
	echo '<p class="description">' . ( '1' === $envio ? 'Aviso enviado por e-mail.' : 'O aviso por e-mail não foi enviado. Confira em SALEKIM > Montar o site > Testar e-mail.' ) . '</p>';
	echo '<p><a class="button" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">Mover para a lixeira</a></p>';
}

add_filter( 'manage_sk_mensagem_posts_columns', 'salekim_colunas_mensagem' );

function salekim_colunas_mensagem( $colunas ) {
	return array(
		'cb'       => $colunas['cb'],
		'title'    => 'Quem enviou',
		'sk_tipo'  => 'Formulário',
		'sk_sobre' => 'Assunto ou programa',
		'date'     => 'Data',
	);
}

add_action( 'manage_sk_mensagem_posts_custom_column', 'salekim_coluna_mensagem', 10, 2 );

function salekim_coluna_mensagem( $coluna, $post_id ) {
	if ( 'sk_tipo' === $coluna ) {
		echo esc_html( 'empresa' === get_post_meta( $post_id, '_sk_tipo', true ) ? 'Pedido de proposta' : 'Programas' );
	}
	if ( 'sk_sobre' === $coluna ) {
		echo esc_html( get_post_meta( $post_id, '_sk_sobre', true ) );
	}
}

/** Ordem do painel igual à do site. */
add_action( 'pre_get_posts', 'salekim_ordem_painel' );

function salekim_ordem_painel( $query ) {
	if ( is_admin() && $query->is_main_query() && in_array( $query->get( 'post_type' ), array( 'sk_programa', 'sk_servico', 'sk_depoimento' ), true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
	}
}
