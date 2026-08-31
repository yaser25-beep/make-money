<?php
/**
 * Customizer settings — semua bisa diatur dari Appearance > Customize,
 * tanpa perlu sentuh kode atau Elementor.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Daftar field Customizer, dikelompokkan per bagian.
 * Menambah field baru tinggal menambah baris di sini.
 */
function teraju10_customizer_fields() {
	return array(
		'teraju10_ticker'   => array(
			'title'  => __( 'Ticker Harga (Emas & Kurs)', 'teraju10' ),
			'fields' => array(
				'ticker_auto_update' => array(
					__( 'Update otomatis setiap hari', 'teraju10' ),
					'1',
					'checkbox',
					__( 'Kalau aktif, kurs USD/IDR diambil dari data resmi Bank Sentral Eropa (Frankfurter.app) dan harga emas dunia dari GoldPrice.org, diperbarui otomatis lewat WP-Cron. Kalau kedua sumber gagal diakses lebih dari 4 hari, ticker otomatis kembali memakai angka manual di bawah ini.', 'teraju10' ),
				),
				'ticker_gold_price'  => array( __( 'Harga emas / gram (manual / cadangan)', 'teraju10' ), 'Rp 2.751.000' ),
				'ticker_gold_change' => array( __( 'Perubahan harga emas (manual / cadangan)', 'teraju10' ), '+0,04%' ),
				'ticker_usd_idr'     => array( __( 'Kurs USD/IDR (manual / cadangan)', 'teraju10' ), 'Rp 17.679' ),
				'ticker_usd_change'  => array( __( 'Perubahan kurs USD/IDR (manual / cadangan)', 'teraju10' ), '-0,03%' ),
				'ticker_updated'     => array( __( 'Keterangan waktu update (manual / cadangan)', 'teraju10' ), __( 'Diperbarui otomatis', 'teraju10' ) ),
			),
		),
		'teraju10_kabar'    => array(
			'title'  => __( 'Kirim Kabar Kalbar (WhatsApp)', 'teraju10' ),
			'fields' => array(
				'wa_number'  => array( __( 'Nomor WhatsApp (format 62...)', 'teraju10' ), '6285966616195' ),
				'wa_message' => array( __( 'Pesan WA default', 'teraju10' ), __( 'Halo teraju, saya mau kirim Kabar Kalbar', 'teraju10' ) ),
			),
		),
		'teraju10_english'  => array(
			'title'  => __( 'Teaser English Version', 'teraju10' ),
			'fields' => array(
				'english_headline'   => array( __( 'Judul teaser', 'teraju10' ), __( 'West Kalimantan, explained — for investors, NGOs, and the diaspora', 'teraju10' ) ),
				'english_desc'       => array( __( 'Deskripsi teaser', 'teraju10' ), __( 'Cross-border trade, blue economy, and the province\'s quiet economic shifts — written for readers outside Indonesia.', 'teraju10' ) ),
				'english_url'        => array( __( 'Tautan tujuan', 'teraju10' ), '' ),
			),
		),
		'teraju10_homepage' => array(
			'title'  => __( 'Rubrik Homepage', 'teraju10' ),
			'fields' => array(
				'kabar_kalbar_tag'   => array( __( 'Slug tag untuk "Kabar Kalbar"', 'teraju10' ), 'komunitas' ),
				'rubric_1_category'  => array( __( 'Slug kategori Rubrik 1', 'teraju10' ), 'otomotif' ),
				'rubric_2_category'  => array( __( 'Slug kategori Rubrik 2', 'teraju10' ), 'politik' ),
				'warisan_category'   => array( __( 'Slug kategori "Warisan Kalbar"', 'teraju10' ), 'kultur' ),
			),
		),
	);
}

/**
 * Daftarkan section dan field ke Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Instance customizer.
 */
function teraju10_customize_register( $wp_customize ) {
	foreach ( teraju10_customizer_fields() as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'    => $section['title'],
				'priority' => 160,
			)
		);

		foreach ( $section['fields'] as $setting_id => $field ) {
			$label       = $field[0];
			$default     = $field[1];
			$type        = isset( $field[2] ) ? $field[2] : 'text';
			$description = isset( $field[3] ) ? $field[3] : '';

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => $default,
					'sanitize_callback' => 'checkbox' === $type ? 'teraju10_sanitize_checkbox' : 'sanitize_text_field',
					'transport'         => 'refresh',
				)
			);

			$control_args = array(
				'label'   => $label,
				'section' => $section_id,
				'type'    => $type,
			);
			if ( $description ) {
				$control_args['description'] = $description;
			}

			$wp_customize->add_control( $setting_id, $control_args );
		}
	}
}
add_action( 'customize_register', 'teraju10_customize_register' );

/**
 * Ambil nilai setting Customizer dengan fallback default, tanpa perlu
 * menulis ulang default value di banyak tempat.
 *
 * @param string $setting_id ID setting.
 * @return string
 */
function teraju10_get_option( $setting_id ) {
	foreach ( teraju10_customizer_fields() as $section ) {
		if ( isset( $section['fields'][ $setting_id ] ) ) {
			$default = $section['fields'][ $setting_id ][1];
			return get_theme_mod( $setting_id, $default );
		}
	}
	return get_theme_mod( $setting_id, '' );
}

/**
 * Sanitasi field checkbox Customizer jadi string '1' atau '0'.
 *
 * @param mixed $value Nilai mentah dari form.
 * @return string
 */
function teraju10_sanitize_checkbox( $value ) {
	return ( true === $value || 1 === $value || '1' === $value ) ? '1' : '0';
}
