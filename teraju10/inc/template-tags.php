<?php
/**
 * Custom template tags for this theme.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kembalikan inisial 1-2 huruf dari sebuah nama, untuk avatar bulat.
 *
 * @param string $name Nama lengkap.
 * @return string
 */
function teraju10_initials( $name ) {
	$name  = trim( (string) $name );
	if ( '' === $name ) {
		return '?';
	}
	$parts = preg_split( '/\s+/', $name );
	$parts = array_filter( $parts );
	$parts = array_values( $parts );

	if ( count( $parts ) === 1 ) {
		return strtoupper( mb_substr( $parts[0], 0, 2 ) );
	}

	$first = mb_substr( $parts[0], 0, 1 );
	$last  = mb_substr( $parts[ count( $parts ) - 1 ], 0, 1 );
	return strtoupper( $first . $last );
}

/**
 * Estimasi waktu baca berdasarkan jumlah kata konten.
 *
 * @param int $post_id ID artikel.
 * @return string
 */
function teraju10_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$content = wp_strip_all_tags( strip_shortcodes( $content ) );
	$words   = str_word_count( $content );
	$minutes = max( 1, (int) ceil( $words / 200 ) );

	/* translators: %d: estimated reading time in minutes. */
	return sprintf( _n( '%d menit membaca', '%d menit membaca', $minutes, 'teraju10' ), $minutes );
}

/**
 * Susun item breadcrumb sebagai array asosiatif ( 'label' => ..., 'url' => ... ).
 * Dipakai bersama untuk tampilan visual dan schema BreadcrumbList, supaya selalu sinkron.
 *
 * @return array
 */
function teraju10_get_breadcrumb_items() {
	$items   = array();
	$items[] = array(
		'label' => __( 'Beranda', 'teraju10' ),
		'url'   => home_url( '/' ),
	);

	if ( is_singular( 'post' ) ) {
		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			$primary    = $categories[0];
			$items[]    = array(
				'label' => $primary->name,
				'url'   => get_category_link( $primary ),
			);
		}
		$items[] = array(
			'label' => get_the_title(),
			'url'   => get_permalink(),
		);
	} elseif ( is_category() ) {
		$items[] = array(
			'label' => single_cat_title( '', false ),
			'url'   => get_category_link( get_queried_object_id() ),
		);
	} elseif ( is_tag() ) {
		$items[] = array(
			'label' => single_tag_title( '', false ),
			'url'   => get_tag_link( get_queried_object_id() ),
		);
	} elseif ( is_author() ) {
		$items[] = array(
			'label' => get_the_author(),
			'url'   => get_author_posts_url( get_queried_object_id() ),
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'label' => sprintf( __( 'Hasil pencarian: %s', 'teraju10' ), get_search_query() ),
			'url'   => '',
		);
	} elseif ( is_page() ) {
		$items[] = array(
			'label' => get_the_title(),
			'url'   => get_permalink(),
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'label' => __( 'Halaman tidak ditemukan', 'teraju10' ),
			'url'   => '',
		);
	}

	return $items;
}

/**
 * Tampilkan breadcrumb visual.
 */
function teraju10_breadcrumbs() {
	$items = teraju10_get_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}

	echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'Rute halaman', 'teraju10' ) . '">';
	$last_index = count( $items ) - 1;
	foreach ( $items as $index => $item ) {
		if ( $index > 0 ) {
			echo '<span class="sep">/</span>';
		}
		if ( $index === $last_index || empty( $item['url'] ) ) {
			echo '<span>' . esc_html( $item['label'] ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		}
	}
	echo '</nav>';
}

/**
 * Ambil "Ringkasan Cepat" (untuk AEO) dari meta artikel, dengan fallback ke excerpt.
 *
 * @param int $post_id ID artikel.
 * @return string
 */
function teraju10_get_summary( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$summary = get_post_meta( $post_id, '_teraju_summary', true );

	if ( empty( $summary ) ) {
		$summary = get_the_excerpt( $post_id );
	}

	return $summary;
}

/**
 * Parse teks "Fakta Cepat" (format satu baris: Label|Nilai) menjadi array.
 *
 * @param int $post_id ID artikel.
 * @return array
 */
function teraju10_get_facts( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = get_post_meta( $post_id, '_teraju_facts', true );
	$facts   = array();

	if ( empty( $raw ) ) {
		return $facts;
	}

	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( false === strpos( $line, '|' ) ) {
			continue;
		}
		list( $label, $value ) = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' === $label || '' === $value ) {
			continue;
		}
		$facts[] = array(
			'num' => $value,
			'cap' => $label,
		);
	}

	return $facts;
}

/**
 * Kembalikan kelas placeholder yang berganti-ganti (netral/emas/gelap)
 * supaya kartu tanpa featured image tetap terlihat bervariasi, bukan monoton.
 *
 * @return string
 */
function teraju10_placeholder_class() {
	static $variants = array( 'placeholder', 'placeholder gold', 'placeholder ink' );
	static $i = 0;
	$class = $variants[ $i % count( $variants ) ];
	++$i;
	return $class;
}

/**
 * Cek apakah sebuah kategori (berdasarkan slug) memang ada, supaya query rubrik
 * di homepage tidak pernah pecah walau slug-nya diketik salah di Customizer.
 *
 * @param string $slug Slug kategori.
 * @return bool
 */
function teraju10_category_exists( $slug ) {
	if ( empty( $slug ) ) {
		return false;
	}
	$term = get_category_by_slug( $slug );
	return ( $term instanceof WP_Term );
}
