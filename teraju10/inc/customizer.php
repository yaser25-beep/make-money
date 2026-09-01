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
				'warisan_pinned_id'  => array(
					__( 'ID artikel "Warisan Kalbar" yang di-pin (opsional)', 'teraju10' ),
					'',
					'text',
					__( 'Kosongkan untuk perilaku lama (otomatis tampilkan artikel TERBARU dari kategori di atas). Isi dengan ID artikel (lihat di URL wp-admin saat mengedit artikel, mis. post.php?post=1234) untuk MENGUNCI satu artikel tertentu di slot ini — dia tidak akan tergeser walau ada artikel baru lain di kategori yang sama. Cocok untuk artikel liputan mendalam/pilar yang ingin terus tampil. Kalau ID yang diisi tidak valid/sudah dihapus, otomatis kembali ke perilaku lama.', 'teraju10' ),
				),
			),
		),
		'teraju10_adsense'  => array(
			'title'  => __( 'AdSense / Iklan', 'teraju10' ),
			'fields' => array(
				'adsense_enable'          => array(
					__( 'Aktifkan iklan AdSense', 'teraju10' ),
					'0',
					'checkbox',
					__( 'Centang setelah dua kode di bawah sudah diisi dan situs sudah disetujui Google AdSense. Bisa dimatikan lagi kapan pun tanpa perlu menghapus kodenya.', 'teraju10' ),
				),
				'adsense_head_code'       => array(
					__( 'Kode AdSense global (di <head>)', 'teraju10' ),
					'',
					'textarea_code',
					__( 'Tempel kode "Auto ads" dari dashboard AdSense (Ads > By site > kode <script async src="...adsbygoogle.js?client=ca-pub-XXXXXXXXXXXXXXXX"...>) atau kode verifikasi situs. Tampil di <head> setiap halaman.', 'teraju10' ),
				),
				'adsense_in_article_code' => array(
					__( 'Kode unit iklan in-article', 'teraju10' ),
					'',
					'textarea_code',
					__( 'Tempel kode unit iklan "In-article" dari AdSense (Ads > By ad unit > In-article ad, biasanya berupa tag <ins class="adsbygoogle">...). Otomatis disisipkan setelah paragraf ke-3 pada halaman artikel — posisi umum yang dipakai media besar karena tak mengganggu awal bacaan tapi tetap terlihat saat scroll. Iklan hanya muncul kalau artikel cukup panjang (>6 paragraf).', 'teraju10' ),
				),
			),
		),
		'teraju10_karhutla' => array(
			'title'  => __( 'Efek Kesadaran Karhutla', 'teraju10' ),
			'fields' => array(
				'karhutla_smoke_effect' => array(
					__( 'Tampilkan efek kabut asap', 'teraju10' ),
					'1',
					'checkbox',
					__( 'Efek visual kabut asap tipis + pesan kesadaran di halaman artikel, untuk momen karhutla musim ini. Bersifat SEMENTARA — matikan centang ini kapan pun (mis. setelah musim hujan/karhutla mereda) untuk menghilangkannya sepenuhnya, tanpa perlu ubah kode.', 'teraju10' ),
				),
				'karhutla_message'      => array(
					__( 'Pesan kesadaran', 'teraju10' ),
					__( 'Kabut asap karhutla masih terjadi di sejumlah wilayah Kalimantan Barat musim ini — salah satu yang terparah dalam beberapa tahun terakhir.', 'teraju10' )
				),
				'karhutla_category'     => array( __( 'Slug kategori/tag liputan Karhutla (opsional)', 'teraju10' ), 'karhutla' ),
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

			if ( 'checkbox' === $type ) {
				$sanitize_callback = 'teraju10_sanitize_checkbox';
			} elseif ( 'textarea_code' === $type ) {
				// Field khusus kode iklan/tracking (mis. AdSense) yang boleh berisi tag <script>.
				// Hanya user dengan izin edit_theme_options yang bisa mengisi Customizer, jadi
				// disimpan apa adanya — sanitize_text_field akan merusak tag <script>/<ins>.
				$sanitize_callback = 'teraju10_sanitize_code_snippet';
			} else {
				$sanitize_callback = 'sanitize_text_field';
			}

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => $default,
					'sanitize_callback' => $sanitize_callback,
					'transport'         => 'refresh',
				)
			);

			$control_args = array(
				'label'   => $label,
				'section' => $section_id,
				'type'    => 'textarea_code' === $type ? 'textarea' : $type,
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

/**
 * "Sanitasi" untuk field kode iklan/tracking (mis. AdSense) — cuma di-trim,
 * TIDAK di-strip tag-nya, supaya <script>/<ins> dari AdSense tidak rusak.
 * Aman karena field ini cuma bisa diisi user dengan izin edit_theme_options.
 *
 * @param mixed $value Nilai mentah dari form.
 * @return string
 */
function teraju10_sanitize_code_snippet( $value ) {
	return is_string( $value ) ? trim( $value ) : '';
}
