<?php
/**
 * Form pencarian.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="teraju10-search"><?php esc_html_e( 'Cari', 'teraju10' ); ?></label>
	<input
		type="search"
		id="teraju10-search"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Cari artikel...', 'teraju10' ); ?>"
	>
	<button type="submit"><?php esc_html_e( 'Cari', 'teraju10' ); ?></button>
</form>
