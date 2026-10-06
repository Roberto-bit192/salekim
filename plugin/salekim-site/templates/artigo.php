<?php
/**
 * Um artigo da área Conteúdos.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require SALEKIM_DIR . 'templates/cabecalho.php';

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'envoltorio secao prosa artigo' ); ?>>
		<p class="rotulo"><a href="<?php echo esc_url( salekim_link( 'conteudos' ) ); ?>">Conteúdos</a> · <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		<h1><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="capa"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>
		<?php the_content(); ?>
	</article>
	<div class="chamada">
		<h2>Quer levar esse tema para a sua equipe?</h2>
		<div class="wp-block-buttons">
			<div class="wp-block-button"><a class="wp-block-button__link" href="<?php echo esc_url( salekim_link( 'para-empresas', 'pedir-proposta' ) ); ?>">Pedir proposta para empresa</a></div>
			<div class="wp-block-button contorno"><a class="wp-block-button__link" href="<?php echo esc_url( salekim_link( 'programas' ) ); ?>">Ver os programas para pessoas</a></div>
		</div>
	</div>
	<?php
endwhile;

require SALEKIM_DIR . 'templates/rodape.php';
