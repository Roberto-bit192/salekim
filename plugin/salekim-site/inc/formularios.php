<?php
/**
 * Os dois formulários do site: "Pedir proposta" (empresas) e "Quero saber mais / lista de espera" (pessoas).
 * Cada envio fica guardado em SALEKIM > Mensagens e é avisado por e-mail.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'salekim_formulario', 'salekim_sc_formulario' );
add_action( 'admin_post_nopriv_salekim_form', 'salekim_receber_formulario' );
add_action( 'admin_post_salekim_form', 'salekim_receber_formulario' );

function salekim_lista_assuntos() {
	return array_map(
		function ( $l ) {
			return $l[0];
		},
		salekim_linhas( salekim_opcao( 'assuntos' ) )
	);
}

function salekim_lista_programas() {
	return wp_list_pluck( salekim_itens( 'sk_programa' ), 'post_title' );
}

/**
 * Campos de cada formulário: nome => [rótulo, tipo, obrigatório, autocomplete].
 */
function salekim_campos_form( $tipo ) {
	if ( 'empresa' === $tipo ) {
		return array(
			'nome'     => array( 'Nome', 'text', true, 'name' ),
			'empresa'  => array( 'Empresa', 'text', true, 'organization' ),
			'cargo'    => array( 'Cargo', 'text', false, 'organization-title' ),
			'email'    => array( 'E-mail', 'email', true, 'email' ),
			'telefone' => array( 'WhatsApp ou telefone', 'tel', true, 'tel' ),
			'assunto'  => array( 'Assunto', 'select', false, '' ),
			'mensagem' => array( 'O que a sua empresa precisa hoje?', 'textarea', false, '' ),
		);
	}
	return array(
		'nome'     => array( 'Nome', 'text', true, 'name' ),
		'email'    => array( 'E-mail', 'email', true, 'email' ),
		'telefone' => array( 'WhatsApp ou telefone', 'tel', true, 'tel' ),
		'programa' => array( 'Programa de interesse', 'select', false, '' ),
		'mensagem' => array( 'Mensagem', 'textarea', false, '' ),
	);
}

function salekim_mensagens_retorno() {
	return array(
		'campos'   => 'Preencha os campos marcados com *.',
		'email'    => 'Confira o e-mail: parece que falta alguma parte.',
		'telefone' => 'Confira o telefone: use o DDD e o número, por exemplo (11) 99914-3277.',
		'lgpd'     => 'Para enviar, marque a autorização de uso dos dados.',
		'muitas'   => 'Recebemos várias mensagens seguidas deste aparelho. Tente de novo em alguns minutos ou fale pelo WhatsApp.',
		'falha'    => 'Não foi possível enviar agora. Tente de novo ou fale pelo WhatsApp.',
	);
}

/** [salekim_formulario tipo="empresa|pessoa|escolha"] */
function salekim_sc_formulario( $atts ) {
	$atts = shortcode_atts( array( 'tipo' => 'pessoa' ), $atts, 'salekim_formulario' );
	if ( 'escolha' !== $atts['tipo'] ) {
		return salekim_formulario_html( 'empresa' === $atts['tipo'] ? 'empresa' : 'pessoa' );
	}
	// Página de contato: o visitante escolhe e vê só o formulário certo.
	$pessoa = ( isset( $_GET['sk_tipo'] ) && 'pessoa' === $_GET['sk_tipo'] ) || isset( $_GET['programa'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$out    = '<div class="escolha-form">';
	$out   .= '<div class="seletor" role="group" aria-label="Tipo de contato">';
	$out   .= '<button type="button" data-sk-mostrar="sk-escolha-empresa" aria-pressed="' . ( $pessoa ? 'false' : 'true' ) . '">Sou de uma empresa</button>';
	$out   .= '<button type="button" data-sk-mostrar="sk-escolha-pessoa" aria-pressed="' . ( $pessoa ? 'true' : 'false' ) . '">Quero me desenvolver</button>';
	$out   .= '</div>';
	$out   .= '<div class="escolha-painel" id="sk-escolha-empresa"' . ( $pessoa ? ' hidden' : '' ) . '>' . salekim_formulario_html( 'empresa', 'c-' ) . '</div>';
	$out   .= '<div class="escolha-painel" id="sk-escolha-pessoa"' . ( $pessoa ? '' : ' hidden' ) . '>' . salekim_formulario_html( 'pessoa', 'c-' ) . '</div>';
	return $out . '</div>';
}

function salekim_formulario_html( $tipo, $prefixo = '' ) {
	// phpcs:disable WordPress.Security.NonceVerification
	$id_form  = 'sk-form-' . $prefixo . $tipo;
	$valores  = array();
	$retorno  = '';
	$deste    = isset( $_GET['sk_tipo'] ) && $tipo === $_GET['sk_tipo'] && isset( $_GET['sk_form'] ) && $id_form === $_GET['sk_form'];
	if ( $deste && isset( $_GET['sk_enviado'] ) ) {
		$retorno = '<p class="aviso ok" role="status" tabindex="-1">Recebemos a sua mensagem. A Genira vai responder em breve pelo e-mail ou WhatsApp que você informou.</p>';
	} elseif ( $deste && isset( $_GET['sk_erro'] ) ) {
		$erros   = salekim_mensagens_retorno();
		$codigo  = sanitize_key( $_GET['sk_erro'] );
		$retorno = '<p class="aviso erro" role="alert" tabindex="-1">' . esc_html( isset( $erros[ $codigo ] ) ? $erros[ $codigo ] : $erros['falha'] ) . '</p>';
		if ( isset( $_GET['sk_t'] ) ) {
			$guardados = get_transient( 'salekim_form_' . sanitize_key( $_GET['sk_t'] ) );
			$valores   = is_array( $guardados ) ? $guardados : array();
		}
	}
	if ( empty( $valores['programa'] ) && isset( $_GET['programa'] ) ) {
		$valores['programa'] = sanitize_text_field( wp_unslash( $_GET['programa'] ) );
	}
	if ( empty( $valores['assunto'] ) && isset( $_GET['assunto'] ) ) {
		$valores['assunto'] = sanitize_text_field( wp_unslash( $_GET['assunto'] ) );
	}
	// phpcs:enable

	$i     = function ( $nome ) use ( $prefixo, $tipo ) {
		return 'sk-' . $prefixo . $tipo . '-' . $nome;
	};
	$rotulo_form = 'empresa' === $tipo ? 'Pedir proposta' : 'Saber mais sobre os programas';
	$out         = '<form class="formulario" id="' . esc_attr( $id_form ) . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" aria-label="' . esc_attr( $rotulo_form ) . '" data-sk-tipo="' . esc_attr( $tipo ) . '">';
	$out        .= $retorno;
	$out        .= '<input type="hidden" name="action" value="salekim_form">';
	$out        .= '<input type="hidden" name="sk_tipo" value="' . esc_attr( $tipo ) . '">';
	$out        .= '<input type="hidden" name="sk_form" value="' . esc_attr( $id_form ) . '">';
	$origem      = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( '/' );
	$out        .= '<input type="hidden" name="sk_origem" value="' . esc_url( $origem ) . '">';
	$out        .= '<input type="hidden" name="sk_inicio" value="' . esc_attr( time() ) . '">';
	$out        .= '<div class="isca" aria-hidden="true"><label for="' . esc_attr( $i( 'site' ) ) . '">Deixe este campo vazio</label><input id="' . esc_attr( $i( 'site' ) ) . '" name="sk_site" type="text" tabindex="-1" autocomplete="off"></div>';
	$out        .= '<div class="campos">';

	foreach ( salekim_campos_form( $tipo ) as $nome => $c ) {
		list( $rotulo, $tipo_campo, $obrigatorio, $auto ) = $c;
		$id      = $i( $nome );
		$valor   = isset( $valores[ $nome ] ) ? $valores[ $nome ] : '';
		$inteiro = in_array( $tipo_campo, array( 'textarea' ), true ) || 'programa' === $nome;
		$req     = $obrigatorio ? ' required aria-required="true"' : '';
		$out    .= '<div class="campo' . ( $inteiro ? ' inteiro' : '' ) . '"><label for="' . esc_attr( $id ) . '">' . esc_html( $rotulo ) . ( $obrigatorio ? ' *' : '' ) . '</label>';
		if ( 'select' === $tipo_campo ) {
			$opcoes = 'assunto' === $nome ? salekim_lista_assuntos() : salekim_lista_programas();
			$vazio  = 'assunto' === $nome ? 'Escolha, se quiser' : 'Ainda não sei';
			$out   .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $nome ) . '"><option value="">' . esc_html( $vazio ) . '</option>';
			foreach ( $opcoes as $op ) {
				$out .= '<option value="' . esc_attr( $op ) . '"' . selected( $valor, $op, false ) . '>' . esc_html( $op ) . '</option>';
			}
			$out .= '</select>';
		} elseif ( 'textarea' === $tipo_campo ) {
			$dica = 'empresa' === $tipo ? 'Conte o contexto, o tamanho da equipe e o prazo, se já souber.' : 'Conte um pouco sobre o seu momento.';
			$out .= '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $nome ) . '" rows="5" aria-describedby="' . esc_attr( $id ) . '-ajuda">' . esc_textarea( $valor ) . '</textarea><span class="ajuda" id="' . esc_attr( $id ) . '-ajuda">' . esc_html( $dica ) . '</span>';
		} else {
			$extra = 'tel' === $tipo_campo ? ' inputmode="tel" placeholder="(11) 99999-9999"' : '';
			$out  .= '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $nome ) . '" type="' . esc_attr( $tipo_campo ) . '" value="' . esc_attr( $valor ) . '"' . ( $auto ? ' autocomplete="' . esc_attr( $auto ) . '"' : '' ) . $extra . $req . '>';
		}
		$out .= '</div>';
	}
	$out .= '</div>';
	$out .= '<label class="consentimento" for="' . esc_attr( $i( 'lgpd' ) ) . '"><input id="' . esc_attr( $i( 'lgpd' ) ) . '" name="lgpd" type="checkbox" value="1" required aria-required="true"><span>Autorizo a SALEKIM a usar os meus dados para responder a este contato, conforme a <a href="' . esc_url( salekim_link( 'politica-de-privacidade' ) ) . '">política de privacidade</a>. *</span></label>';
	$out .= '<p class="aviso erro" role="alert" data-sk-erro hidden></p>';
	$out .= '<div class="acoes"><button class="botao' . ( 'empresa' === $tipo ? ' azul' : ' laranja' ) . '" type="submit">' . ( 'empresa' === $tipo ? 'Pedir proposta' : 'Enviar' ) . '</button><span class="discreto">* campos obrigatórios</span></div>';
	return $out . '</form>';
}

/** Recebe o envio, guarda, avisa por e-mail e volta para a página com o resultado. */
function salekim_receber_formulario() {
	// Formulário público: a proteção vem do campo isca, do tempo mínimo de preenchimento e do limite por aparelho.
	// phpcs:disable WordPress.Security.NonceVerification
	$tipo    = isset( $_POST['sk_tipo'] ) && 'empresa' === $_POST['sk_tipo'] ? 'empresa' : 'pessoa';
	$id_form = isset( $_POST['sk_form'] ) ? sanitize_key( $_POST['sk_form'] ) : 'sk-form-' . $tipo;
	$origem  = isset( $_POST['sk_origem'] ) ? esc_url_raw( wp_unslash( $_POST['sk_origem'] ) ) : '';
	if ( ! $origem ) {
		$origem = wp_get_referer();
	}
	$origem = wp_validate_redirect( $origem, home_url( '/' ) );
	$origem = remove_query_arg( array( 'sk_enviado', 'sk_erro', 'sk_t', 'sk_tipo', 'sk_form' ), strtok( $origem, '#' ) );

	$voltar = function ( $args ) use ( $origem, $tipo, $id_form ) {
		$args['sk_tipo'] = $tipo;
		$args['sk_form'] = $id_form;
		wp_safe_redirect( add_query_arg( $args, $origem ) . '#' . $id_form, 303 );
		exit;
	};

	// Robôs: preenchem o campo isca ou enviam rápido demais. Recebem "ok" e nada é enviado.
	$inicio = isset( $_POST['sk_inicio'] ) ? (int) $_POST['sk_inicio'] : 0;
	if ( ! empty( $_POST['sk_site'] ) || ( $inicio && time() - $inicio < 3 ) ) {
		$voltar( array( 'sk_enviado' => 1 ) );
	}

	$dados = array();
	foreach ( salekim_campos_form( $tipo ) as $nome => $c ) {
		$bruto          = isset( $_POST[ $nome ] ) ? wp_unslash( $_POST[ $nome ] ) : '';
		$dados[ $nome ] = 'textarea' === $c[1] ? sanitize_textarea_field( $bruto ) : sanitize_text_field( $bruto );
	}
	$lgpd = ! empty( $_POST['lgpd'] );
	// phpcs:enable

	$erro = '';
	foreach ( salekim_campos_form( $tipo ) as $nome => $c ) {
		if ( $c[2] && '' === $dados[ $nome ] ) {
			$erro = 'campos';
		}
	}
	$digitos = preg_replace( '/\D/', '', $dados['telefone'] );
	if ( ! $erro && ! is_email( $dados['email'] ) ) {
		$erro = 'email';
	} elseif ( ! $erro && ( strlen( $digitos ) < 10 || strlen( $digitos ) > 13 ) ) {
		$erro = 'telefone';
	} elseif ( ! $erro && ! $lgpd ) {
		$erro = 'lgpd';
	}

	$ip_chave = 'salekim_limite_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$envios   = (int) get_transient( $ip_chave );
	if ( ! $erro && $envios >= 5 ) {
		$erro = 'muitas';
	}

	if ( $erro ) {
		$token = wp_generate_password( 12, false );
		set_transient( 'salekim_form_' . strtolower( $token ), $dados, 10 * MINUTE_IN_SECONDS );
		$voltar(
			array(
				'sk_erro' => $erro,
				'sk_t'    => strtolower( $token ),
			)
		);
	}
	set_transient( $ip_chave, $envios + 1, 15 * MINUTE_IN_SECONDS );

	$rotulos = array();
	foreach ( salekim_campos_form( $tipo ) as $nome => $c ) {
		$rotulos[ 'mensagem' === $nome ? 'Mensagem' : $c[0] ] = $dados[ $nome ];
	}
	$sobre = 'empresa' === $tipo ? $dados['assunto'] : $dados['programa'];
	$rotulos['Formulário']   = 'empresa' === $tipo ? 'Pedir proposta (empresas)' : 'Programas (pessoas)';
	$rotulos['Enviado de']   = $origem;
	$rotulos['Consentimento'] = 'Autorizou o uso dos dados (LGPD) em ' . wp_date( 'd/m/Y H:i' );

	$titulo = $dados['nome'] . ( 'empresa' === $tipo && $dados['empresa'] ? ' · ' . $dados['empresa'] : '' );
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'sk_mensagem',
			'post_status' => 'private',
			'post_title'  => $titulo,
		)
	);
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_sk_dados', $rotulos );
		update_post_meta( $post_id, '_sk_tipo', $tipo );
		update_post_meta( $post_id, '_sk_sobre', $sobre );
	}

	$assunto = 'empresa' === $tipo
		? '[Site SALEKIM] Pedido de proposta: ' . ( $dados['empresa'] ? $dados['empresa'] : $dados['nome'] )
		: '[Site SALEKIM] ' . ( $dados['programa'] ? $dados['programa'] : 'Programas' ) . ': ' . $dados['nome'];
	$corpo = '';
	foreach ( $rotulos as $rotulo => $valor ) {
		if ( '' !== $valor ) {
			$corpo .= $rotulo . ': ' . $valor . "\n";
		}
	}
	$corpo .= "\nResponda direto a este e-mail para falar com " . $dados['nome'] . ".\n";
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		$corpo .= 'Mensagem guardada no painel: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";
	}
	$nome_limpo = str_replace( array( "\r", "\n", '"', '<', '>' ), '', $dados['nome'] );
	$enviado    = wp_mail(
		salekim_opcao( 'email_destino' ),
		$assunto,
		$corpo,
		array( 'Reply-To: "' . $nome_limpo . '" <' . $dados['email'] . '>' )
	);
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_sk_email_enviado', $enviado ? '1' : '0' );
	}

	// A mensagem ficou guardada mesmo se o e-mail falhar; para o visitante, o envio deu certo.
	if ( ! $post_id || is_wp_error( $post_id ) ) {
		if ( ! $enviado ) {
			$voltar( array( 'sk_erro' => 'falha' ) );
		}
	}
	$voltar( array( 'sk_enviado' => 1 ) );
}
