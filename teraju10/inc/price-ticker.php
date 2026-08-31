<?php
/**
 * Ticker harga emas & USD/IDR — diperbarui otomatis via WP-Cron dari sumber
 * yang bisa diverifikasi publik, lalu di-cache di wp_options supaya
 * pengunjung situs tidak pernah menunggu request ke luar saat membuka
 * halaman.
 *
 * Sumber data:
 * - Kurs USD/IDR: Frankfurter.app, yang menyajikan ulang kurs referensi
 *   harian resmi Bank Sentral Eropa (ECB) — bukan agregator pihak ketiga
 *   yang tidak jelas asalnya.
 * - Emas dunia (spot XAU/USD): endpoint publik goldprice.org, acuan yang
 *   umum dipakai media finansial untuk harga emas dunia, lalu dikonversi ke
 *   Rupiah per gram. Ini HARGA EMAS DUNIA (spot), BUKAN harga jual resmi
 *   Antam (yang punya premi/ongkos cetak lokal) — dilabeli apa adanya di
 *   ticker supaya tidak menyesatkan pembaca.
 *
 * Kalau salah satu atau kedua sumber gagal diakses, ticker mempertahankan
 * angka terakhir yang berhasil diambil, dan kalau sudah terlalu lama gagal
 * (lebih dari 4 hari), otomatis jatuh kembali ke angka manual di Customizer
 * — ticker tidak pernah kosong atau rusak hanya karena satu API sedang down.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TERAJU10_TICKER_OPTION', 'teraju10_ticker_data' );
define( 'TERAJU10_TICKER_CRON_HOOK', 'teraju10_update_ticker_prices' );
define( 'TERAJU10_TROY_OUNCE_GRAMS', 31.1034768 );

/**
 * Jadwalkan cron 2x sehari saat tema diaktifkan, plus satu kali fetch cepat
 * supaya ticker tidak menunggu kosong sampai jadwal cron berikutnya.
 */
function teraju10_ticker_schedule() {
	if ( ! wp_next_scheduled( TERAJU10_TICKER_CRON_HOOK ) ) {
		wp_schedule_event( time(), 'twicedaily', TERAJU10_TICKER_CRON_HOOK );
	}

	if ( ! get_option( TERAJU10_TICKER_OPTION ) ) {
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, TERAJU10_TICKER_CRON_HOOK );
	}
}
add_action( 'after_switch_theme', 'teraju10_ticker_schedule' );

/**
 * Bersihkan jadwal cron saat tema diganti, supaya tidak menyisakan job hantu.
 */
function teraju10_ticker_unschedule() {
	$timestamp = wp_next_scheduled( TERAJU10_TICKER_CRON_HOOK );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, TERAJU10_TICKER_CRON_HOOK );
	}
}
add_action( 'switch_theme', 'teraju10_ticker_unschedule' );

/**
 * Jaring pengaman: kalau WP-Cron situs jarang jalan (trafik sepi atau
 * dimatikan hosting) dan data ticker sudah lebih dari 2 hari, minta satu
 * kali fetch tambahan saat ada pengunjung membuka homepage — dijadwalkan
 * lewat WP-Cron juga, jadi tidak membuat pengunjung itu menunggu.
 */
function teraju10_ticker_self_heal() {
	if ( ! is_front_page() || wp_doing_ajax() ) {
		return;
	}

	$data       = get_option( TERAJU10_TICKER_OPTION, array() );
	$last_fetch = max( (int) ( $data['gold_fetched_at'] ?? 0 ), (int) ( $data['usd_fetched_at'] ?? 0 ) );

	if ( ( time() - $last_fetch ) > 2 * DAY_IN_SECONDS && ! wp_next_scheduled( TERAJU10_TICKER_CRON_HOOK ) ) {
		wp_schedule_single_event( time() + 10, TERAJU10_TICKER_CRON_HOOK );
	}
}
add_action( 'wp', 'teraju10_ticker_self_heal' );

/**
 * Ambil kurs USD/IDR dari Frankfurter.app (data resmi ECB).
 *
 * @return float|WP_Error
 */
function teraju10_fetch_usd_idr() {
	$response = wp_remote_get(
		'https://api.frankfurter.app/latest?from=USD&to=IDR',
		array( 'timeout' => 8 )
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['rates']['IDR'] ) ) {
		return new WP_Error( 'teraju10_ticker', 'Kurs USD/IDR tidak ditemukan di respons Frankfurter.' );
	}

	return (float) $body['rates']['IDR'];
}

/**
 * Ambil harga emas dunia (spot XAU/USD per troy ounce) dari goldprice.org.
 *
 * @return float|WP_Error
 */
function teraju10_fetch_gold_spot_usd() {
	$response = wp_remote_get(
		'https://data-asg.goldprice.org/dbXRates/USD',
		array( 'timeout' => 8 )
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['items'][0]['xauPrice'] ) ) {
		return new WP_Error( 'teraju10_ticker', 'Harga emas spot tidak ditemukan di respons goldprice.org.' );
	}

	return (float) $body['items'][0]['xauPrice'];
}

/**
 * Hitung persentase perubahan dari nilai lama ke nilai baru.
 *
 * @param float $old Nilai lama.
 * @param float $new Nilai baru.
 * @return float
 */
function teraju10_percent_change( $old, $new ) {
	if ( empty( $old ) ) {
		return 0.0;
	}
	return ( ( $new - $old ) / $old ) * 100;
}

/**
 * Callback cron: ambil data terbaru dari kedua sumber secara independen.
 * Kalau satu sumber gagal, field itu saja yang tidak diperbarui — field
 * lain tetap jalan seperti biasa.
 */
function teraju10_ticker_cron_update() {
	$data = wp_parse_args(
		get_option( TERAJU10_TICKER_OPTION, array() ),
		array(
			'gold_price'      => 0,
			'gold_change'     => 0,
			'gold_fetched_at' => 0,
			'usd_idr'         => 0,
			'usd_change'      => 0,
			'usd_fetched_at'  => 0,
		)
	);

	$usd_idr = teraju10_fetch_usd_idr();
	if ( ! is_wp_error( $usd_idr ) && $usd_idr > 0 ) {
		$data['usd_change']     = teraju10_percent_change( $data['usd_idr'], $usd_idr );
		$data['usd_idr']        = $usd_idr;
		$data['usd_fetched_at'] = time();
	}

	if ( $data['usd_idr'] > 0 ) {
		$gold_spot = teraju10_fetch_gold_spot_usd();
		if ( ! is_wp_error( $gold_spot ) && $gold_spot > 0 ) {
			$gold_idr_per_gram        = ( $gold_spot / TERAJU10_TROY_OUNCE_GRAMS ) * $data['usd_idr'];
			$data['gold_change']      = teraju10_percent_change( $data['gold_price'], $gold_idr_per_gram );
			$data['gold_price']       = $gold_idr_per_gram;
			$data['gold_fetched_at']  = time();
		}
	}

	update_option( TERAJU10_TICKER_OPTION, $data, false );
}
add_action( TERAJU10_TICKER_CRON_HOOK, 'teraju10_ticker_cron_update' );

/**
 * Format angka Rupiah ala Indonesia: "Rp 2.751.000".
 *
 * @param float $value Nilai.
 * @return string
 */
function teraju10_format_rupiah( $value ) {
	return 'Rp ' . number_format( (float) $value, 0, ',', '.' );
}

/**
 * Format persentase perubahan dengan tanda +/-, ala "+0,04%".
 *
 * @param float $value Nilai persen.
 * @return string
 */
function teraju10_format_percent( $value ) {
	$sign = $value < 0 ? '-' : '+';
	return $sign . number_format( abs( (float) $value ), 2, ',', '.' ) . '%';
}

/**
 * Nilai ticker yang ditampilkan di header: pakai data otomatis kalau
 * fiturnya aktif dan datanya masih segar (<4 hari), kalau tidak jatuh
 * kembali ke isian manual Customizer — perilaku lama, tidak pernah berubah.
 *
 * @param string $key 'gold_price' | 'gold_change' | 'usd_idr' | 'usd_change' | 'updated'.
 * @return string
 */
function teraju10_get_ticker_value( $key ) {
	$auto_enabled = '1' === teraju10_get_option( 'ticker_auto_update' );
	$data         = get_option( TERAJU10_TICKER_OPTION, array() );
	$max_age      = 4 * DAY_IN_SECONDS;

	$is_gold_fresh = $auto_enabled && ! empty( $data['gold_fetched_at'] ) && ( time() - $data['gold_fetched_at'] ) < $max_age;
	$is_usd_fresh  = $auto_enabled && ! empty( $data['usd_fetched_at'] ) && ( time() - $data['usd_fetched_at'] ) < $max_age;

	switch ( $key ) {
		case 'gold_price':
		case 'gold_change':
			if ( $is_gold_fresh ) {
				$value = ( 'gold_price' === $key )
					? teraju10_format_rupiah( $data['gold_price'] )
					: teraju10_format_percent( $data['gold_change'] );
				return apply_filters( 'teraju10_ticker_value', $value, $key, true );
			}
			break;

		case 'usd_idr':
		case 'usd_change':
			if ( $is_usd_fresh ) {
				$value = ( 'usd_idr' === $key )
					? teraju10_format_rupiah( $data['usd_idr'] )
					: teraju10_format_percent( $data['usd_change'] );
				return apply_filters( 'teraju10_ticker_value', $value, $key, true );
			}
			break;

		case 'updated':
			if ( $is_gold_fresh || $is_usd_fresh ) {
				$latest = max( (int) $data['gold_fetched_at'], (int) $data['usd_fetched_at'] );
				/* translators: %s: waktu relatif, misal "2 jam". */
				$value = sprintf( __( 'Diperbarui %s yang lalu', 'teraju10' ), human_time_diff( $latest, time() ) );
				return apply_filters( 'teraju10_ticker_value', $value, $key, true );
			}
			break;
	}

	$manual_map = array(
		'gold_price'  => 'ticker_gold_price',
		'gold_change' => 'ticker_gold_change',
		'usd_idr'     => 'ticker_usd_idr',
		'usd_change'  => 'ticker_usd_change',
		'updated'     => 'ticker_updated',
	);

	$value = teraju10_get_option( $manual_map[ $key ] );
	return apply_filters( 'teraju10_ticker_value', $value, $key, false );
}
