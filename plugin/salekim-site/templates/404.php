<?php
/**
 * Página não encontrada.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require SALEKIM_DIR . 'templates/cabecalho.php';
?>
<div class="envoltorio secao">
	<div class="cabeca-secao">
		<p class="rotulo">Página não encontrada</p>
		<h1>Este endereço não existe mais.</h1>
		<p class="discreto">O site da SALEKIM foi renovado e algumas páginas mudaram de lugar. Escolha por onde seguir:</p>
	</div>
	<div class="wp-block-buttons">
		<div class="wp-block-button azul"><a class="wp-block-button__link" href="<?php echo esc_url( salekim_link( 'para-empresas' ) ); ?>">Soluções para empresas</a></div>
		<div class="wp-block-button laranja"><a class="wp-block-button__link" href="<?php echo esc_url( salekim_link( 'programas' ) ); ?>">Programas para pessoas</a></div>
		<div class="wp-block-button contorno"><a class="wp-block-button__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">Página inicial</a></div>
	</div>
</div>
<?php
require SALEKIM_DIR . 'templates/rodape.php';
