<?php
/**
 * Integrasi Google AdSense — kode diisi lewat Appearance > Customize >
 * AdSense / Iklan, tanpa perlu sentuh file tema.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cetak kode AdSense global (Auto ads / verifikasi situs) di <head>.
 */
function teraju10_adsense_head() {
	if ( '1' !== teraju10_get_option( 'adsense_enable' ) ) {
		return;
	}

	$code = teraju10_get_option( 'adsense_head_code' );
	if ( '' === trim( $code ) ) {
		return;
	}

	echo "\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- kode iklan AdSense, hanya diisi lewat Customizer oleh admin (edit_theme_options).
}
add_action( 'wp_head', 'teraju10_adsense_head' );

/**
 * Sisipkan unit iklan in-article setelah paragraf ke-3 pada halaman artikel
 * tunggal, kalau artikelnya cukup panjang. Tidak menyentuh tampilan
 * archive/homepage karena the_content() cuma dipakai di halaman single.
 *
 * @param string $content Konten artikel.
 * @return string
 */
function teraju10_adsense_in_article( $content ) {
	if ( is_admin() || ! is_single() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( '1' !== teraju10_get_option( 'adsense_enable' ) ) {
		return $content;
	}

	$ad_code = trim( teraju10_get_option( 'adsense_in_article_code' ) );
	if ( '' === $ad_code ) {
		return $content;
	}

	$closing_tag = '</p>';
	$paragraphs  = explode( $closing_tag, $content );
	$last_index  = count( $paragraphs ) - 1;

	// Butuh setidaknya ~6 paragraf asli supaya iklan tidak nempel di ujung artikel pendek.
	if ( $last_index < 6 ) {
		return $content;
	}

	$ad_markup     = '<div class="in-article-ad">' . $ad_code . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- kode iklan AdSense, hanya diisi lewat Customizer oleh admin.
	$insert_after  = 3;
	$paragraph_num = 0;
	$output        = '';

	foreach ( $paragraphs as $index => $paragraph ) {
		if ( $index === $last_index ) {
			$output .= $paragraph;
			break;
		}

		if ( '' === trim( $paragraph ) ) {
			continue;
		}

		$output .= $paragraph . $closing_tag;
		++$paragraph_num;

		if ( $insert_after === $paragraph_num ) {
			$output .= $ad_markup;
		}
	}

	return $output;
}
add_filter( 'the_content', 'teraju10_adsense_in_article' );
