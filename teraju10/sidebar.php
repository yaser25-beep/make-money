<?php
/**
 * Sidebar artikel — isinya diatur sepenuhnya lewat Appearance > Widgets
 * (widget "Teraju: Postingan Terpopuler" dan "Teraju: Slot Iklan/Gambar").
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'post-sidebar' ) ) {
	return;
}
?>
<aside class="post-sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'teraju10' ); ?>">
	<?php dynamic_sidebar( 'post-sidebar' ); ?>
</aside>
