<?php
/**
 * Rodapé comum a todas as páginas.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$sk_email = salekim_opcao( 'email_publico' );
?>
</main>
<footer class="rodape">
	<div class="envoltorio">
		<div class="colunas">
			<div class="rodape-marca">
				<p class="lema"><?php echo esc_html( salekim_opcao( 'lema' ) ); ?></p>
				<p class="desde"><?php echo esc_html( salekim_opcao( 'desde' ) ); ?></p>
			</div>
			<div>
				<h2>Site</h2>
				<ul>
					<?php foreach ( salekim_links_rodape() as $sk_url => $sk_titulo ) : ?>
						<li><a href="<?php echo esc_url( $sk_url ); ?>"><?php echo esc_html( $sk_titulo ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div>
				<h2>Contato</h2>
				<ul>
					<li><a href="mailto:<?php echo esc_attr( $sk_email ); ?>"><?php echo esc_html( $sk_email ); ?></a></li>
					<li><a target="_blank" rel="noopener" href="<?php echo esc_url( salekim_whatsapp_url() ); ?>">WhatsApp <?php echo esc_html( salekim_opcao( 'whatsapp_texto' ) ); ?></a></li>
					<li><a target="_blank" rel="noopener" href="<?php echo esc_url( salekim_instagram_url() ); ?>">Instagram @<?php echo esc_html( ltrim( salekim_opcao( 'instagram' ), '@' ) ); ?></a></li>
					<li><?php echo esc_html( salekim_opcao( 'cidade' ) ); ?></li>
				</ul>
			</div>
		</div>
		<div class="base">
			<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( salekim_opcao( 'razao_social' ) ); ?> · CNPJ <?php echo esc_html( salekim_opcao( 'cnpj' ) ); ?></span>
			<span>
				<a href="<?php echo esc_url( salekim_link( 'politica-de-privacidade' ) ); ?>">Política de privacidade</a>
				<?php if ( salekim_tem_medicao() ) : ?>
					· <button class="link" type="button" data-sk-cookies>Preferências de cookies</button>
				<?php endif; ?>
			</span>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
