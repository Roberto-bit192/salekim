<?php
/**
 * Menu SALEKIM no painel: montar o site, pendências, configurações e teste de e-mail.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'salekim_menu_painel' );

function salekim_menu_painel() {
	add_menu_page( 'SALEKIM', 'SALEKIM', 'manage_options', 'salekim', 'salekim_tela_montar', 'dashicons-groups', 3 );
	add_submenu_page( 'salekim', 'Montar o site', 'Montar o site', 'manage_options', 'salekim', 'salekim_tela_montar' );
	add_submenu_page( 'salekim', 'Configurações do site', 'Configurações', 'manage_options', 'salekim-configuracoes', 'salekim_tela_configuracoes' );
}

/* ---------- Ações ---------- */

add_action( 'admin_post_salekim_montar', 'salekim_acao_montar' );
add_action( 'admin_post_salekim_limpar', 'salekim_acao_limpar' );
add_action( 'admin_post_salekim_testar_email', 'salekim_acao_testar_email' );

function salekim_checar( $acao ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Sem permissão.' );
	}
	check_admin_referer( $acao );
}

function salekim_voltar_painel( $relatorio ) {
	set_transient( 'salekim_relatorio', $relatorio, HOUR_IN_SECONDS );
	wp_safe_redirect( admin_url( 'admin.php?page=salekim' ) );
	exit;
}

function salekim_acao_montar() {
	salekim_checar( 'salekim_montar' );
	salekim_voltar_painel( salekim_montar_site( array( 'restaurar' => ! empty( $_POST['restaurar'] ) ) ) );
}

function salekim_acao_limpar() {
	salekim_checar( 'salekim_limpar' );
	$relatorio = salekim_limpar_demo();
	salekim_voltar_painel( $relatorio ? $relatorio : array( 'Não havia conteúdo de demonstração.' ) );
}

function salekim_acao_testar_email() {
	salekim_checar( 'salekim_testar_email' );
	$destino = salekim_opcao( 'email_destino' );
	$ok      = wp_mail( $destino, '[Site SALEKIM] Teste de e-mail', "Este é um teste do formulário do site.\n\nSe esta mensagem chegou, os pedidos de proposta e as inscrições dos programas também vão chegar.\n" );
	update_option( 'salekim_email_testado', $ok ? wp_date( 'd/m/Y H:i' ) : '' );
	salekim_voltar_painel(
		array(
			$ok
				? 'E-mail de teste enviado para ' . $destino . '. Confira a caixa de entrada e o spam.'
				: 'O servidor não conseguiu enviar o e-mail de teste. Instale um plugin de SMTP (por exemplo, WP Mail SMTP) e configure com a conta ' . $destino . '. Enquanto isso, as mensagens continuam guardadas em SALEKIM > Mensagens.',
		)
	);
}

/* ---------- Tela "Montar o site" ---------- */

function salekim_pendencias() {
	$itens = array();
	if ( ! salekim_opcao( 'ga4_id' ) ) {
		$itens[] = 'Instalar o Google Analytics: crie a propriedade GA4 e cole o ID (G-XXXXXXX) em <a href="' . esc_url( admin_url( 'admin.php?page=salekim-configuracoes' ) ) . '">Configurações</a>.';
	}
	if ( ! salekim_opcao( 'pixel_id' ) ) {
		$itens[] = 'Instalar o Meta Pixel: cole o ID do pixel (só números) em Configurações.';
	}
	$vida = get_posts(
		array(
			'post_type'      => 'sk_programa',
			'meta_key'       => '_sk_chave', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => 'vida', // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page' => 1,
		)
	);
	if ( $vida && ! get_post_meta( $vida[0]->ID, '_sk_link', true ) ) {
		$itens[] = 'Colocar o link da página de vendas do VIDA (Hotmart) no programa <a href="' . esc_url( get_edit_post_link( $vida[0]->ID ) ) . '">Método VIDA</a>. Sem o link, o botão "Quero participar" abre o formulário.';
	}
	if ( ! get_option( 'salekim_email_testado' ) ) {
		$itens[] = 'Enviar o e-mail de teste (botão abaixo) e conferir se chegou em ' . esc_html( salekim_opcao( 'email_destino' ) ) . '.';
	}
	if ( ! salekim_opcao( 'foto_url' ) ) {
		$itens[] = 'Trocar a foto da Genira pela foto profissional (Configurações > Imagens). Hoje o site usa a foto do site anterior.';
	}
	if ( ! salekim_opcao( 'logo_url' ) ) {
		$itens[] = 'Quando o logo vetorizado (SVG) ficar pronto, trocar em Configurações > Imagens e enviar a versão só das folhas em Aparência > Personalizar > Identidade do site (ícone).';
	}
	if ( ! wp_count_posts( 'sk_depoimento' )->publish ) {
		$itens[] = 'Cadastrar 2 ou 3 <a href="' . esc_url( admin_url( 'post-new.php?post_type=sk_depoimento' ) ) . '">depoimentos</a> autorizados. A seção aparece sozinha no site quando houver o primeiro.';
	}
	if ( ! get_option( 'blog_public' ) ) {
		$itens[] = 'O site está escondido do Google. Para liberar, desmarque "Evitar que mecanismos de busca indexem este site" em <a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '">Configurações > Leitura</a>.';
	}
	if ( 0 !== strpos( home_url(), 'https://' ) ) {
		$itens[] = 'Ativar o certificado de segurança (HTTPS) na hospedagem e trocar o endereço do site para https://www.salekim.com.br em Configurações > Geral.';
	}
	$itens[] = 'Textos que dependem da Genira: títulos das palestras, lista de workshops, duração e próxima turma da Formação, descrição do InterAgir, formato da Supervisão e se "Executive e Life Coaching" aparece no Acompanhamento individual.';
	$itens[] = 'Google Meu Negócio: criar ou revisar o perfil em business.google.com com o mesmo nome, telefone e site (www.salekim.com.br). O site já informa esses dados ao Google.';
	return $itens;
}

function salekim_tela_montar() {
	$relatorio = get_transient( 'salekim_relatorio' );
	delete_transient( 'salekim_relatorio' );
	$demo = salekim_conteudo_demo();
	?>
	<div class="wrap">
		<h1>SALEKIM: montar o site</h1>

		<?php if ( $relatorio ) : ?>
			<div class="notice notice-success"><p><strong>Feito:</strong></p><ul style="list-style:disc;padding-left:1.5rem">
				<?php foreach ( (array) $relatorio as $linha ) : ?>
					<li><?php echo esc_html( $linha ); ?></li>
				<?php endforeach; ?>
			</ul></div>
		<?php endif; ?>

		<p>O plugin monta todas as páginas (Início, Sobre, Para empresas, Programas, Clientes, Contato, Conteúdos e Política de privacidade), os programas, os serviços, o menu, os formulários, o botão do WhatsApp e o aviso de cookies. <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">Ver o site</a>.</p>

		<h2>O que falta</h2>
		<ol>
			<?php foreach ( salekim_pendencias() as $item ) : ?>
				<li style="margin-bottom:.4rem"><?php echo wp_kses_post( $item ); ?></li>
			<?php endforeach; ?>
		</ol>

		<h2>Como mudar o site</h2>
		<ul style="list-style:disc;padding-left:1.5rem">
			<li><strong>Textos das páginas:</strong> <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">Páginas</a>. Clique no texto e escreva por cima. Os blocos cinza com colchetes (por exemplo <code>[salekim_programas]</code>) puxam os cadastros abaixo: não apague.</li>
			<li><strong>Programas:</strong> <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sk_programa' ) ); ?>">SALEKIM > Programas</a>. Nome, para quem é, descrição, etiqueta (lista de espera, inscrições abertas), botão e link.</li>
			<li><strong>Serviços para empresas:</strong> <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sk_servico' ) ); ?>">SALEKIM > Serviços</a>.</li>
			<li><strong>Depoimentos:</strong> <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sk_depoimento' ) ); ?>">SALEKIM > Depoimentos</a>.</li>
			<li><strong>Clientes, contatos, Google Analytics e Meta Pixel:</strong> <a href="<?php echo esc_url( admin_url( 'admin.php?page=salekim-configuracoes' ) ); ?>">SALEKIM > Configurações</a>.</li>
			<li><strong>Mensagens dos formulários:</strong> chegam no e-mail e ficam guardadas em <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sk_mensagem' ) ); ?>">SALEKIM > Mensagens</a>.</li>
			<li><strong>Artigos:</strong> <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">Posts > Adicionar novo</a>. Quando houver o primeiro, inclua "Conteúdos" no menu em Aparência > Menus.</li>
		</ul>

		<h2>Ferramentas</h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:1rem">
			<?php wp_nonce_field( 'salekim_montar' ); ?>
			<input type="hidden" name="action" value="salekim_montar">
			<p><label><input type="checkbox" name="restaurar" value="1"> Restaurar o texto original de todas as páginas do site (a versão atual fica guardada nas revisões de cada página)</label></p>
			<?php submit_button( 'Montar ou completar o site', 'primary', 'submit', false ); ?>
			<p class="description">Cria o que estiver faltando. Sem a opção acima, não muda nenhum texto que a SALEKIM já editou.</p>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:1rem">
			<?php wp_nonce_field( 'salekim_testar_email' ); ?>
			<input type="hidden" name="action" value="salekim_testar_email">
			<?php submit_button( 'Enviar e-mail de teste para ' . salekim_opcao( 'email_destino' ), 'secondary', 'submit', false ); ?>
			<?php if ( get_option( 'salekim_email_testado' ) ) : ?>
				<span class="description">Último teste enviado em <?php echo esc_html( get_option( 'salekim_email_testado' ) ); ?>.</span>
			<?php endif; ?>
		</form>

		<?php if ( $demo ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'salekim_limpar' ); ?>
				<input type="hidden" name="action" value="salekim_limpar">
				<p>Conteúdo de demonstração encontrado:</p>
				<ul style="list-style:disc;padding-left:1.5rem">
					<?php foreach ( $demo as $post ) : ?>
						<li><?php echo esc_html( ( $post->post_title ? $post->post_title : '(sem título)' ) . ' (' . get_post_type_object( $post->post_type )->labels->singular_name . ')' ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php submit_button( 'Mover a demonstração para a lixeira', 'secondary', 'submit', false ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

/* ---------- Configurações ---------- */

add_action(
	'admin_init',
	function () {
		register_setting(
			'salekim',
			'salekim_opcoes',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'salekim_limpar_opcoes',
			)
		);
	}
);

/** Campos da tela de configurações, por grupo. */
function salekim_campos_config() {
	return array(
		'Contato'                  => array(
			'email_destino'  => array( 'E-mail que recebe os formulários', 'email', '' ),
			'email_publico'  => array( 'E-mail mostrado no site', 'email', '' ),
			'whatsapp'       => array( 'WhatsApp (só números, com 55 e DDD)', 'texto', 'Ex.: 5511999143277' ),
			'whatsapp_texto' => array( 'WhatsApp como aparece no site', 'texto', 'Ex.: (11) 99914-3277' ),
			'whatsapp_msg'   => array( 'Mensagem pronta do botão flutuante', 'texto', '' ),
			'instagram'      => array( 'Instagram (sem @)', 'texto', '' ),
			'endereco'       => array( 'Endereço (opcional)', 'texto', 'Em branco, o site mostra só a cidade.' ),
			'mapa_url'       => array( 'Link do mapa (opcional)', 'url', 'Link "Compartilhar" do Google Maps ou do perfil no Google Meu Negócio.' ),
		),
		'Empresa'                  => array(
			'razao_social' => array( 'Razão social', 'texto', '' ),
			'cnpj'         => array( 'CNPJ', 'texto', '' ),
			'cidade'       => array( 'Cidade', 'texto', '' ),
			'lema'         => array( 'Frase de assinatura', 'texto', '' ),
			'desde'        => array( 'Frase do rodapé', 'texto', '' ),
		),
		'Clientes'                 => array(
			'clientes_faixa' => array( 'Faixa de clientes (Início e Para empresas)', 'linhas', 'Um nome por linha.' ),
			'clientes_lista' => array( 'Lista completa (página Clientes)', 'linhas', 'Um por linha. Para um detalhe, use a barra vertical: Nestlé | Brasil, Colômbia, Venezuela e Equador. Use só nomes em texto; logotipos de clientes só com autorização.' ),
		),
		'Formulário de proposta'   => array(
			'assuntos' => array( 'Assuntos', 'linhas', 'Um por linha. Os serviços usam estes nomes para já escolher o assunto.' ),
		),
		'Imagens'                  => array(
			'logo_url' => array( 'Logo', 'imagem', 'Em branco, usa o logo do site anterior. De preferência SVG ou PNG com fundo transparente.' ),
			'foto_url' => array( 'Foto da Genira', 'imagem', 'Retrato vertical, com fundo transparente ou claro, de pelo menos 600 px de largura.' ),
		),
		'Google e redes'           => array(
			'ga4_id'          => array( 'ID do Google Analytics 4', 'texto', 'Ex.: G-ABC123XYZ. Só carrega depois que o visitante aceita os cookies.' ),
			'pixel_id'        => array( 'ID do Meta Pixel', 'texto', 'Só os números. Só carrega depois que o visitante aceita os cookies.' ),
			'gsc_verificacao' => array( 'Verificação do Google Search Console', 'texto', 'Cole a tag ou só o código do método "Tag HTML".' ),
		),
	);
}

function salekim_limpar_opcoes( $entrada ) {
	$limpo = array();
	foreach ( salekim_campos_config() as $campos ) {
		foreach ( $campos as $chave => $c ) {
			$valor = isset( $entrada[ $chave ] ) ? $entrada[ $chave ] : '';
			switch ( $c[1] ) {
				case 'email':
					$valor = sanitize_email( $valor );
					break;
				case 'url':
				case 'imagem':
					$valor = esc_url_raw( $valor );
					break;
				case 'linhas':
					$valor = sanitize_textarea_field( $valor );
					break;
				default:
					$valor = sanitize_text_field( $valor );
			}
			$limpo[ $chave ] = $valor;
		}
	}
	$limpo['whatsapp'] = preg_replace( '/\D/', '', $limpo['whatsapp'] );
	$limpo['pixel_id'] = preg_replace( '/\D/', '', $limpo['pixel_id'] );
	$limpo['ga4_id']   = strtoupper( preg_replace( '/[^A-Za-z0-9-]/', '', $limpo['ga4_id'] ) );
	$limpo['instagram'] = ltrim( $limpo['instagram'], '@' );
	return $limpo;
}

add_action(
	'admin_enqueue_scripts',
	function ( $tela ) {
		if ( 'salekim_page_salekim-configuracoes' === $tela ) {
			wp_enqueue_media();
		}
	}
);

function salekim_tela_configuracoes() {
	?>
	<div class="wrap">
		<h1>Configurações do site SALEKIM</h1>
		<p>Campos em branco voltam ao valor inicial do briefing.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'salekim' ); ?>
			<?php foreach ( salekim_campos_config() as $grupo => $campos ) : ?>
				<h2><?php echo esc_html( $grupo ); ?></h2>
				<table class="form-table" role="presentation"><tbody>
				<?php
				foreach ( $campos as $chave => $c ) :
					$id    = 'salekim-' . $chave;
					$nome  = 'salekim_opcoes[' . $chave . ']';
					$valor = salekim_opcao( $chave );
					?>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $c[0] ); ?></label></th>
						<td>
							<?php if ( 'linhas' === $c[1] ) : ?>
								<textarea class="large-text" rows="8" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $nome ); ?>"><?php echo esc_textarea( $valor ); ?></textarea>
							<?php elseif ( 'imagem' === $c[1] ) : ?>
								<input class="regular-text" type="url" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $nome ); ?>" value="<?php echo esc_attr( $valor ); ?>">
								<button type="button" class="button" data-sk-midia="<?php echo esc_attr( $id ); ?>">Escolher na biblioteca</button>
								<p><img src="<?php echo esc_url( 'logo_url' === $chave ? salekim_logo_url() : salekim_foto_url() ); ?>" alt="" style="max-height:90px;margin-top:.5rem;background:#FDF7EE;padding:4px;border-radius:4px"></p>
							<?php else : ?>
								<input class="regular-text" type="<?php echo esc_attr( 'email' === $c[1] ? 'email' : ( 'url' === $c[1] ? 'url' : 'text' ) ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $nome ); ?>" value="<?php echo esc_attr( $valor ); ?>">
							<?php endif; ?>
							<?php if ( $c[2] ) : ?>
								<p class="description"><?php echo esc_html( $c[2] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endforeach; ?>
			<?php submit_button( 'Salvar configurações' ); ?>
		</form>
	</div>
	<script>
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-sk-midia]');
		if (!b || !window.wp || !wp.media) { return; }
		var campo = document.getElementById(b.getAttribute('data-sk-midia'));
		var janela = wp.media({ title: 'Escolher imagem', library: { type: 'image' }, multiple: false });
		janela.on('select', function () { campo.value = janela.state().get('selection').first().toJSON().url; });
		janela.open();
	});
	</script>
	<?php
}

add_action( 'admin_notices', 'salekim_aviso_ativacao' );

/** Logo depois de ativar, mostra o que foi feito em qualquer tela do painel. */
function salekim_aviso_ativacao() {
	$tela = get_current_screen();
	if ( ! $tela || 'toplevel_page_salekim' === $tela->id || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_transient( 'salekim_relatorio' ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Site SALEKIM montado.</strong> <a href="' . esc_url( admin_url( 'admin.php?page=salekim' ) ) . '">Ver o que foi feito e o que falta</a>.</p></div>';
	}
}
