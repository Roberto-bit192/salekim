<?php
/**
 * Lista de artigos (Conteúdos), arquivos de categoria e busca.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require SALEKIM_DIR . 'templates/cabecalho.php';

if ( is_search() ) {
	$sk_titulo = 'Resultados para “' . get_search_query() . '”';
} elseif ( is_archive() ) {
	$sk_titulo = wp_strip_all_tags( get_the_archive_title() );
} else {
	$sk_titulo = 'Conteúdos';
}
?>
<div class="envoltorio secao">
	<div class="cabeca-secao">
		<p class="rotulo">Conteúdos</p>
		<h1><?php echo esc_html( $sk_titulo ); ?></h1>
		<?php if ( ! is_search() && ! is_archive() ) : ?>
			<p class="discreto">Artigos e vídeos sobre liderança, grupos, cultura organizacional e desenvolvimento humano.</p>
		<?php endif; ?>
	</div>
	<?php if ( have_posts() ) : ?>
		<div class="cartoes">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'cartao' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="cartao-imagem" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large' ); ?></a>
					<?php endif; ?>
					<p class="rotulo"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="discreto"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
					<a class="link-seta" href="<?php the_permalink(); ?>">Ler<span class="screen-reader-text"> <?php the_title(); ?></span></a>
				</article>
			<?php endwhile; ?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'prev_text' => 'Anteriores',
				'next_text' => 'Próximos',
			)
		);
		?>
	<?php else : ?>
		<p>Em breve, artigos e vídeos por aqui. Enquanto isso, conheça os <a href="<?php echo esc_url( salekim_link( 'programas' ) ); ?>">programas</a> ou siga a Genira no <a target="_blank" rel="noopener" href="<?php echo esc_url( salekim_instagram_url() ); ?>">Instagram</a>.</p>
	<?php endif; ?>
</div>
<?php
require SALEKIM_DIR . 'templates/rodape.php';
