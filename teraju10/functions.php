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
	define( 'TERAJU10_VERSION', '1.4.0' );
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
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
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

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'teraju10_scripts' );

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
require get_template_directory() . '/inc/widgets.php';
require get_template_directory() . '/inc/price-ticker.php';
