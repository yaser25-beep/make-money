<?php
/**
 * Template footer, dipakai di semua halaman.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</div><!-- #content -->

<footer class="site-footer">
	<div class="wrap">
		<div class="footer-grid">
			<div>
				<a class="brand-mark" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php
					$site_name = get_bloginfo( 'name' );
					if ( false !== strpos( $site_name, '.' ) ) {
						list( $main, $suffix ) = explode( '.', $site_name, 2 );
						echo esc_html( $main ) . '<span>.' . esc_html( $suffix ) . '</span>';
					} else {
						echo esc_html( $site_name );
					}
					?>
				</a>
				<p class="footer-blurb"><?php bloginfo( 'description' ); ?></p>
			</div>

			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<?php dynamic_sidebar( 'footer-1' ); ?>
			<?php else : ?>
				<div class="footer-col">
					<h5><?php esc_html_e( 'Kanal', 'teraju10' ); ?></h5>
					<p style="font-size:12px;color:var(--muted);"><?php esc_html_e( 'Tambahkan widget "Menu Kustom" di sini lewat Appearance > Widgets.', 'teraju10' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
				<?php dynamic_sidebar( 'footer-2' ); ?>
			<?php else : ?>
				<div class="footer-col">
					<h5><?php esc_html_e( 'Tentang', 'teraju10' ); ?></h5>
				</div>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
				<?php dynamic_sidebar( 'footer-3' ); ?>
			<?php else : ?>
				<div class="footer-col">
					<h5><?php esc_html_e( 'Ikuti Kami', 'teraju10' ); ?></h5>
				</div>
			<?php endif; ?>
		</div>

		<div class="footer-bottom">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> &mdash; <?php esc_html_e( 'Hak cipta dilindungi', 'teraju10' ); ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
