<?php
/**
 * Template halaman 404.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="narrow">
	<div class="error-404">
		<h1>404</h1>
		<p><?php esc_html_e( 'Halaman yang kamu cari tidak ditemukan atau sudah dipindahkan.', 'teraju10' ); ?></p>
		<?php get_search_form(); ?>
	</div>

	<div class="widget" style="max-width:400px;margin:0 auto 60px;">
		<?php the_widget( 'Teraju10_Popular_Posts_Widget', array( 'title' => __( 'Mungkin kamu suka', 'teraju10' ) ) ); ?>
	</div>
</main>

<?php
get_footer();
