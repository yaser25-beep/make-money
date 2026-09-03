<?php
/**
 * Penghitung "sudah dibagikan berapa kali" — social proof JUJUR (angka
 * asli dari klik tombol bagikan, bukan rekaan) yang tampil di kotak
 * bagikan akhir artikel begitu jumlahnya cukup meyakinkan untuk
 * ditampilkan. Arsitekturnya sengaja disamakan dengan inc/view-counter.php:
 * dicatat lewat request admin-ajax.php terpisah dari render halaman,
 * supaya halaman artikel tetap 100% ramah plugin cache.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TERAJU10_SHARE_CLICKS_META', '_teraju_share_clicks' );
define( 'TERAJU10_SHARE_COUNT_MIN_DISPLAY', 5 );

/**
 * Kirim data yang dibutuhkan JS (nonce + post ID) ke script utama, hanya
 * di halaman artikel tunggal.
 */
function teraju10_share_tracker_scripts() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_localize_script(
		'teraju10-main',
		'teraju10ShareTracker',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postId'  => get_the_ID(),
			'nonce'   => wp_create_nonce( 'teraju10_track_share' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'teraju10_share_tracker_scripts', 20 );

/**
 * Endpoint AJAX yang dipanggil browser pembaca setiap klik tombol bagikan
 * (WhatsApp/X/salin tautan) — dibuka untuk pengunjung biasa maupun login.
 */
function teraju10_handle_track_share() {
	check_ajax_referer( 'teraju10_track_share', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

	if ( ! $post_id || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		wp_send_json_error( null, 400 );
	}

	$count = (int) get_post_meta( $post_id, TERAJU10_SHARE_CLICKS_META, true );
	update_post_meta( $post_id, TERAJU10_SHARE_CLICKS_META, $count + 1 );

	wp_send_json_success();
}
add_action( 'wp_ajax_teraju10_track_share', 'teraju10_handle_track_share' );
add_action( 'wp_ajax_nopriv_teraju10_track_share', 'teraju10_handle_track_share' );

/**
 * Jumlah share yang layak ditampilkan ke pembaca, atau 0 kalau belum
 * cukup meyakinkan (di bawah TERAJU10_SHARE_COUNT_MIN_DISPLAY) — supaya
 * artikel yang baru dibagikan 1-2 kali tidak malah terkesan sepi.
 *
 * @param int $post_id ID artikel.
 * @return int
 */
function teraju10_get_display_share_count( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$count   = (int) get_post_meta( $post_id, TERAJU10_SHARE_CLICKS_META, true );

	if ( $count < TERAJU10_SHARE_COUNT_MIN_DISPLAY ) {
		return 0;
	}

	return $count;
}
