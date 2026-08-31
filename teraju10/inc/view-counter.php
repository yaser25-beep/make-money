<?php
/**
 * Penghitung tayang (views) 7 hari terakhir, dipakai widget "Postingan
 * Terpopuler" mode otomatis — menggantikan jumlah komentar yang sudah
 * tidak relevan lagi sebagai ukuran "populer" (banyak situs berita zaman
 * sekarang komentarnya sepi/dimatikan, padahal pembacanya tetap ramai).
 *
 * Cara kerja, sengaja dibuat ramah CACHE HALAMAN:
 * - Hitungan TIDAK ditambah saat halaman artikel dirender di server (itu
 *   akan mustahil dihitung benar begitu situs dipasangi plugin cache,
 *   karena PHP tidak jalan lagi untuk kunjungan yang disajikan dari cache).
 * - Sebagai gantinya, browser pembaca yang lapor lewat satu request kecil
 *   terpisah (admin-ajax.php, endpoint yang MEMANG selalu dijalankan
 *   dinamis, tidak pernah ikut di-cache) setelah halaman selesai dimuat.
 *   Jadi halaman artikelnya sendiri tetap 100% bisa di-cache utuh.
 * - Satu pembaca cuma dihitung sekali per 30 menit per artikel (dicek di
 *   browser lewat localStorage), supaya reload berkali-kali tidak
 *   menggelembungkan angka.
 *
 * Data disimpan per-hari (maks. 8 hari ke belakang) di post meta, lalu
 * dijumlah jadi satu angka "7 hari terakhir" yang gampang dipakai untuk
 * mengurutkan (meta_value_num). Angka itu dihitung ulang tiap kali ada
 * tayangan baru, DAN oleh satu cron harian — supaya artikel yang sudah
 * berhenti dibaca ikut "meluruh" dari daftar populer, bukan nyangkut
 * selamanya dengan angka lama.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TERAJU10_VIEWS_DAILY_META', '_teraju_views_daily' );
define( 'TERAJU10_VIEWS_WEEKLY_META', '_teraju_views_7d' );
define( 'TERAJU10_VIEWS_WINDOW_DAYS', 7 );
define( 'TERAJU10_VIEWS_RECOMPUTE_CRON_HOOK', 'teraju10_recompute_weekly_views' );

/**
 * Muat script pelapor tayangan, hanya di halaman artikel tunggal.
 */
function teraju10_view_tracker_scripts() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_script(
		'teraju10-view-tracker',
		get_template_directory_uri() . '/assets/js/view-tracker.js',
		array(),
		TERAJU10_VERSION,
		true
	);
	wp_localize_script(
		'teraju10-view-tracker',
		'teraju10ViewTracker',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postId'  => get_the_ID(),
			'nonce'   => wp_create_nonce( 'teraju10_track_view' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'teraju10_view_tracker_scripts' );

/**
 * Endpoint AJAX yang dipanggil browser pembaca untuk lapor satu tayangan.
 * Dibuka untuk pengunjung biasa (nopriv) maupun yang login.
 */
function teraju10_handle_track_view() {
	check_ajax_referer( 'teraju10_track_view', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

	if ( ! $post_id || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		wp_send_json_error( null, 400 );
	}

	teraju10_record_view( $post_id );
	wp_send_json_success();
}
add_action( 'wp_ajax_teraju10_track_view', 'teraju10_handle_track_view' );
add_action( 'wp_ajax_nopriv_teraju10_track_view', 'teraju10_handle_track_view' );

/**
 * Catat satu tayangan untuk sebuah artikel: tambah bucket hari ini, buang
 * bucket yang sudah lewat dari jendela pengamatan, lalu hitung ulang total
 * 7 hari terakhir.
 *
 * @param int $post_id ID artikel.
 */
function teraju10_record_view( $post_id ) {
	$daily = get_post_meta( $post_id, TERAJU10_VIEWS_DAILY_META, true );
	$daily = is_array( $daily ) ? $daily : array();

	$today = current_time( 'Y-m-d' );
	$daily[ $today ] = isset( $daily[ $today ] ) ? $daily[ $today ] + 1 : 1;

	$daily = teraju10_trim_daily_views( $daily );

	update_post_meta( $post_id, TERAJU10_VIEWS_DAILY_META, $daily );
	update_post_meta( $post_id, TERAJU10_VIEWS_WEEKLY_META, teraju10_sum_daily_views( $daily ) );
}

/**
 * Buang entri harian yang lebih tua dari jendela pengamatan (dilebihkan
 * sedikit dari 7 hari, sebagai buffer zona waktu).
 *
 * @param array $daily Peta 'Y-m-d' => jumlah.
 * @return array
 */
function teraju10_trim_daily_views( $daily ) {
	$cutoff = strtotime( '-' . ( TERAJU10_VIEWS_WINDOW_DAYS + 1 ) . ' days', current_time( 'timestamp' ) );

	foreach ( array_keys( $daily ) as $date ) {
		if ( strtotime( $date ) < $cutoff ) {
			unset( $daily[ $date ] );
		}
	}

	return $daily;
}

/**
 * Jumlahkan entri harian yang masih berada dalam jendela 7 hari terakhir.
 *
 * @param array $daily Peta 'Y-m-d' => jumlah.
 * @return int
 */
function teraju10_sum_daily_views( $daily ) {
	$cutoff = strtotime( '-' . TERAJU10_VIEWS_WINDOW_DAYS . ' days', current_time( 'timestamp' ) );
	$total  = 0;

	foreach ( $daily as $date => $count ) {
		if ( strtotime( $date ) >= $cutoff ) {
			$total += (int) $count;
		}
	}

	return $total;
}

/**
 * Jadwalkan cron harian yang menghitung ulang total 7-hari SEMUA artikel
 * yang pernah tercatat tayangannya — supaya artikel yang sudah tidak
 * dibaca lagi ikut "meluruh" dari daftar populer meski tidak ada
 * tayangan baru yang memicu perhitungan ulang.
 */
function teraju10_views_schedule_cron() {
	if ( ! wp_next_scheduled( TERAJU10_VIEWS_RECOMPUTE_CRON_HOOK ) ) {
		wp_schedule_event( time(), 'daily', TERAJU10_VIEWS_RECOMPUTE_CRON_HOOK );
	}
}
add_action( 'after_switch_theme', 'teraju10_views_schedule_cron' );

/**
 * Bersihkan jadwal cron saat tema diganti.
 */
function teraju10_views_unschedule_cron() {
	$timestamp = wp_next_scheduled( TERAJU10_VIEWS_RECOMPUTE_CRON_HOOK );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, TERAJU10_VIEWS_RECOMPUTE_CRON_HOOK );
	}
}
add_action( 'switch_theme', 'teraju10_views_unschedule_cron' );

/**
 * Callback cron: hitung ulang total 7-hari semua artikel yang punya data
 * tayangan. Dibatasi jumlahnya per jalan supaya tetap ringan di situs
 * dengan sangat banyak artikel — diurutkan dari yang paling relevan
 * (total 7-hari tertinggi) dulu.
 */
function teraju10_recompute_all_weekly_views() {
	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 500,
			'orderby'                => 'meta_value_num',
			'meta_key'               => TERAJU10_VIEWS_WEEKLY_META,
			'order'                  => 'DESC',
			'meta_query'             => array(
				array(
					'key'     => TERAJU10_VIEWS_DAILY_META,
					'compare' => 'EXISTS',
				),
			),
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $query->posts as $post_id ) {
		$daily = get_post_meta( $post_id, TERAJU10_VIEWS_DAILY_META, true );
		$daily = is_array( $daily ) ? teraju10_trim_daily_views( $daily ) : array();

		update_post_meta( $post_id, TERAJU10_VIEWS_DAILY_META, $daily );
		update_post_meta( $post_id, TERAJU10_VIEWS_WEEKLY_META, teraju10_sum_daily_views( $daily ) );
	}
}
add_action( TERAJU10_VIEWS_RECOMPUTE_CRON_HOOK, 'teraju10_recompute_all_weekly_views' );
