<?php
/**
 * Template part: ditampilkan saat tidak ada artikel yang cocok.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="no-results">
	<?php if ( is_search() ) : ?>
		<h2><?php esc_html_e( 'Tidak ada hasil ditemukan', 'teraju10' ); ?></h2>
		<p><?php esc_html_e( 'Coba kata kunci lain, atau jelajahi rubrik lewat menu di atas.', 'teraju10' ); ?></p>
	<?php else : ?>
		<h2><?php esc_html_e( 'Belum ada artikel di sini', 'teraju10' ); ?></h2>
		<p><?php esc_html_e( 'Silakan cek kembali nanti.', 'teraju10' ); ?></p>
	<?php endif; ?>
	<?php get_search_form(); ?>
</div>
