<?php
/**
 * Páginas. As montadas pelo plugin usam o layout em seções; as demais, texto corrido.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require SALEKIM_DIR . 'templates/cabecalho.php';

while ( have_posts() ) :
	the_post();
	if ( salekim_e_pagina_do_site() ) :
		the_content();
	else :
		?>
		<article <?php post_class( 'envoltorio secao prosa' ); ?>>
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</article>
		<?php
	endif;
endwhile;

require SALEKIM_DIR . 'templates/rodape.php';
