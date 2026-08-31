<?php
/**
 * Meta description, Open Graph, dan Twitter Card — supaya tautan artikel
 * tampil rapi & akurat saat dibagikan (WhatsApp/Facebook/X) dan lebih
 * mudah "dibaca" mesin pencari maupun AI search (ChatGPT, Perplexity,
 * Google AI Overview, dsb. — sebagian besar masih memakai og:title/
 * og:description sebagai salah satu sinyal ringkasan halaman).
 *
 * Dilewati otomatis kalau ada plugin SEO populer aktif (Yoast, RankMath,
 * All in One SEO, SEOPress), supaya tag-nya tidak dobel dengan yang sudah
 * dihasilkan plugin tersebut — plugin SEO selalu diutamakan.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cek apakah ada plugin SEO populer yang sudah aktif.
 *
 * @return bool
 */
function teraju10_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' )
		|| class_exists( 'RankMath' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' );
}

/**
 * Ambil deskripsi terbaik untuk halaman yang sedang dibuka: Ringkasan
 * Cepat (gaya Smart Brevity) untuk artikel, deskripsi taksonomi untuk
 * arsip kategori/tag, tagline situs untuk beranda.
 *
 * @return string
 */
function teraju10_page_description() {
	if ( is_singular( 'post' ) ) {
		return teraju10_get_summary( get_the_ID() );
	}
	if ( is_category() || is_tag() ) {
		return term_description();
	}
	if ( is_front_page() || is_home() ) {
		return get_bloginfo( 'description' );
	}
	return '';
}

/**
 * Ambil gambar terbaik untuk og:image: thumbnail artikel, atau logo situs
 * sebagai cadangan.
 *
 * @return string
 */
function teraju10_page_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( $image_src ) {
			return $image_src[0];
		}
	}
	if ( has_custom_logo() ) {
		$logo_src = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
		if ( $logo_src ) {
			return $logo_src[0];
		}
	}
	return '';
}

/**
 * Output <meta name="description">, Open Graph, dan Twitter Card di
 * <head>. Ringan: hanya beberapa tag teks, tanpa request/aset tambahan.
 */
function teraju10_output_head_meta() {
	if ( teraju10_has_seo_plugin() ) {
		return;
	}

	$description = trim( wp_strip_all_tags( teraju10_page_description() ) );
	$description = $description ? wp_trim_words( $description, 30, '…' ) : '';

	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}

	$title = is_singular() ? wp_strip_all_tags( get_the_title() ) : wp_strip_all_tags( get_bloginfo( 'name' ) );
	$image = teraju10_page_image();

	if ( is_singular() ) {
		$url = get_permalink();
	} elseif ( is_category() || is_tag() ) {
		$url = get_term_link( get_queried_object() );
	} else {
		$url = home_url( '/' );
	}

	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";

	if ( $description ) {
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	if ( ! is_wp_error( $url ) && $url ) {
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	}
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	echo '<meta property="og:locale" content="id_ID">' . "\n";
	echo '<meta name="twitter:card" content="' . esc_attr( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";

	if ( is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'teraju10_output_head_meta', 1 );
