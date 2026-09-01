<?php
/**
 * Teraju10 theme functions and definitions.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'TERAJU10_VERSION' ) ) {
	define( 'TERAJU10_VERSION', '1.11.0' );
}

/**
 * Theme setup.
 */
function teraju10_setup() {
	load_theme_textdomain( 'teraju10', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 46,
			'width'       => 154,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	set_post_thumbnail_size( 900, 560, true );
	add_image_size( 'teraju10-card', 480, 300, true );
	add_image_size( 'teraju10-square', 300, 300, true );

	register_nav_menus(
		array(
			'primary' => __( 'Menu Utama', 'teraju10' ),
		)
	);
}
add_action( 'after_setup_theme', 'teraju10_setup' );

/**
 * Setel tagline default situs SEKALI SAJA saat tema versi ini pertama kali
 * dimuat (bukan tiap kunjungan, dan tidak akan menimpa lagi kalau nanti
 * diubah manual lewat Appearance > Customize > Site Identity). Ini dipakai
 * supaya tagline langsung berubah begitu tema ini di-upload/aktif, tanpa
 * redaksi perlu buka Customizer dulu — tapi setelah itu, kolom tagline
 * kembali jadi pengaturan biasa yang bebas diubah kapan saja.
 */
function teraju10_maybe_set_default_tagline() {
	if ( get_option( 'teraju10_tagline_v2' ) ) {
		return;
	}
	update_option( 'blogdescription', 'Solusi, alih-alih sensasi' );
	update_option( 'teraju10_tagline_v2', '1' );
}
add_action( 'after_setup_theme', 'teraju10_maybe_set_default_tagline' );

/**
 * Bersihkan <head> dari elemen bawaan WordPress yang sudah jarang relevan
 * (RSD/WLW untuk software blog lama, shortlink, versi WP) dan matikan
 * script+style emoji bawaan (murni polyfill untuk browser sangat lama).
 * Hemat beberapa request/inline-script kecil di SETIAP halaman tanpa
 * mengubah fungsi apa pun — situs tetap ringan.
 */
function teraju10_head_cleanup() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'teraju10_head_cleanup' );

/**
 * Register widget areas (sidebar artikel + kolom footer).
 * Semua bisa diisi lewat Appearance > Widgets tanpa kode.
 */
function teraju10_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar Artikel', 'teraju10' ),
			'id'            => 'post-sidebar',
			'description'   => __( 'Tampil di sisi kanan halaman artikel. Tambahkan widget "Teraju: Postingan Terpopuler" atau "Teraju: Slot Iklan/Gambar" di sini.', 'teraju10' ),
			'before_widget' => '<div class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	$footer_columns = array(
		'footer-1' => __( 'Footer - Kanal', 'teraju10' ),
		'footer-2' => __( 'Footer - Tentang', 'teraju10' ),
		'footer-3' => __( 'Footer - Ikuti Kami', 'teraju10' ),
	);

	foreach ( $footer_columns as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'description'   => __( 'Kolom footer, bisa diisi widget Menu Kustom atau Teks.', 'teraju10' ),
				'before_widget' => '<div class="footer-col %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h5>',
				'after_title'   => '</h5>',
			)
		);
	}
}
add_action( 'widgets_init', 'teraju10_widgets_init' );

/**
 * Enqueue styles and scripts.
 */
function teraju10_scripts() {
	wp_enqueue_style(
		'teraju10-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Work+Sans:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'teraju10-style', get_stylesheet_uri(), array(), TERAJU10_VERSION );
	wp_enqueue_script( 'teraju10-main', get_template_directory_uri() . '/assets/js/main.js', array(), TERAJU10_VERSION, true );

	if ( is_singular( 'post' ) ) {
		// Font serif untuk isi artikel (gaya The Guardian) — cuma dimuat di halaman
		// artikel, bukan di semua halaman, supaya homepage/arsip tetap seringan mungkin.
		wp_enqueue_style(
			'teraju10-fonts-article',
			'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400&display=swap',
			array(),
			null
		);

		wp_enqueue_script( 'teraju10-quote-share', get_template_directory_uri() . '/assets/js/quote-share.js', array(), TERAJU10_VERSION, true );
		wp_localize_script(
			'teraju10-quote-share',
			'teraju10QuoteShare',
			array(
				'labels' => array(
					'copy'     => __( 'Salin kutipan & tautan', 'teraju10' ),
					'copied'   => __( 'Tersalin!', 'teraju10' ),
					'whatsapp' => __( 'Bagikan kutipan ke WhatsApp', 'teraju10' ),
					'twitter'  => __( 'Bagikan kutipan ke X', 'teraju10' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'teraju10_scripts' );

/**
 * Preconnect lebih awal ke domain Google Fonts, supaya koneksi (DNS + TLS)
 * sudah siap SEBELUM tag <link> stylesheet fonts dibaca browser — beberapa
 * ratus milidetik lebih cepat pada koneksi lambat, tanpa perlu meng-host
 * sendiri file font.
 *
 * @param array  $urls URL yang mau di-hint.
 * @param string $relation_type Jenis hint ('preconnect', 'dns-prefetch', dst).
 * @return array
 */
function teraju10_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
		$urls[] = 'https://fonts.googleapis.com';
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'teraju10_resource_hints', 10, 2 );

/**
 * Excerpt length & "read more" string.
 */
function teraju10_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'teraju10_excerpt_length' );

function teraju10_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'teraju10_excerpt_more' );

/**
 * Fallback menu jika belum ada menu yang diatur di Appearance > Menus.
 */
function teraju10_fallback_menu() {
	echo '<ul id="primary-menu" class="menu">';
	wp_list_categories(
		array(
			'title_li' => '',
			'depth'    => 1,
			'number'   => 8,
		)
	);
	echo '</ul>';
}

/**
 * Includes.
 */
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/meta-boxes.php';
require get_template_directory() . '/inc/schema-markup.php';
require get_template_directory() . '/inc/view-counter.php';
require get_template_directory() . '/inc/widgets.php';
require get_template_directory() . '/inc/price-ticker.php';
require get_template_directory() . '/inc/seo-meta.php';
require get_template_directory() . '/inc/adsense.php';
