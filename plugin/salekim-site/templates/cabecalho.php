<?php
/**
 * Cabeçalho comum a todas as páginas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>
<header class="topo">
	<div class="envoltorio">
		<a class="marca" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="SALEKIM, página inicial">
			<img src="<?php echo esc_url( salekim_logo_url() ); ?>" alt="Salekim, desenvolvimento humano e organizacional" width="440" height="222">
		</a>
		<button class="botao contorno pequeno botao-menu" id="sk-botao-menu" type="button" aria-expanded="false" aria-controls="sk-menu">Menu</button>
		<nav class="menu" id="sk-menu" aria-label="Principal">
			<?php salekim_menu(); ?>
		</nav>
		<a class="botao topo-cta" href="<?php echo esc_url( salekim_link( 'contato' ) ); ?>">Fale com a Genira</a>
	</div>
</header>
<main id="conteudo" class="sk-conteudo" tabindex="-1">
