<?php
/**
 * Plugin Name:       SALEKIM Site
 * Plugin URI:        https://www.salekim.com.br
 * Description:       Monta o site da SALEKIM do começo ao fim: páginas, programas, serviços, formulários de proposta e de programas, WhatsApp, LGPD, SEO, Google Analytics e Meta Pixel. Tudo editável pelo painel.
 * Version:           2.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            SALEKIM Treinamento e Desenvolvimento Ltda.
 * Author URI:        https://www.salekim.com.br
 * License:           GPL-2.0-or-later
 * Text Domain:       salekim-site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SALEKIM_VERSAO', '2.0.0' );
define( 'SALEKIM_ARQUIVO', __FILE__ );
define( 'SALEKIM_DIR', plugin_dir_path( __FILE__ ) );
define( 'SALEKIM_URL', plugin_dir_url( __FILE__ ) );

require_once SALEKIM_DIR . 'inc/opcoes.php';
require_once SALEKIM_DIR . 'inc/conteudo.php';
require_once SALEKIM_DIR . 'inc/tipos.php';
require_once SALEKIM_DIR . 'inc/shortcodes.php';
require_once SALEKIM_DIR . 'inc/formularios.php';
require_once SALEKIM_DIR . 'inc/frente.php';
require_once SALEKIM_DIR . 'inc/seo.php';
require_once SALEKIM_DIR . 'inc/instalador.php';

if ( is_admin() ) {
	require_once SALEKIM_DIR . 'inc/painel.php';
}

register_activation_hook( __FILE__, 'salekim_ativar' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Na ativação, registra os tipos de conteúdo e monta o site.
 * Nada é apagado: páginas antigas com o mesmo endereço ganham uma revisão
 * antes de receber o novo texto, e o conteúdo de demonstração vai para a lixeira.
 */
function salekim_ativar() {
	salekim_registrar_tipos();
	$relatorio = salekim_montar_site( array( 'limpar_demo' => true ) );
	set_transient( 'salekim_relatorio', $relatorio, HOUR_IN_SECONDS );
	flush_rewrite_rules();
}
