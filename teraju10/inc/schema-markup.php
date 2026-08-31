<?php
/**
 * Structured data (JSON-LD): NewsArticle + BreadcrumbList.
 * Dipasang di wp_head supaya Google News, AI Overview, dan mesin pencari lain
 * bisa membaca artikel secara mesin-terbaca, sesuai spesifikasi di brief awal.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ambil URL logo publisher untuk keperluan schema.
 *
 * @return string
 */
function teraju10_schema_publisher_logo() {
	if ( has_custom_logo() ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $logo_src ) {
			return $logo_src[0];
		}
	}
	return '';
}

/**
 * Output JSON-LD NewsArticle pada halaman single artikel.
 */
function teraju10_output_news_article_schema() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$post_id       = get_the_ID();
	$author_id     = (int) get_the_author_meta( 'ID' );
	$publisher_logo = teraju10_schema_publisher_logo();

	$schema = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'NewsArticle',
		'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
		'datePublished'    => get_the_date( 'c', $post_id ),
		'dateModified'     => get_the_modified_date( 'c', $post_id ),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post_id ),
		),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $author_id ),
			'url'   => get_author_posts_url( $author_id ),
		),
		'publisher'        => array(
			'@type' => 'NewsMediaOrganization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( $publisher_logo ) {
		$schema['publisher']['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $publisher_logo,
		);
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$image_id  = get_post_thumbnail_id( $post_id );
		$image_src = wp_get_attachment_image_src( $image_id, 'full' );
		if ( $image_src ) {
			$schema['image'] = array( $image_src[0] );
		}
	}

	$summary = teraju10_get_summary( $post_id );
	if ( ! empty( $summary ) ) {
		$schema['description'] = wp_strip_all_tags( $summary );

		/*
		 * SpeakableSpecification: menandai kotak "Ringkasan Cepat" sebagai
		 * bagian yang aman dibacakan asisten suara (Google Assistant, dsb).
		 * Dipopulerkan Washington Post, masih jarang dipakai portal daerah.
		 */
		$schema['speakable'] = array(
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => array( '.summary-box p' ),
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'teraju10_output_news_article_schema' );

/**
 * Output JSON-LD BreadcrumbList, memakai sumber data yang sama dengan
 * breadcrumb visual (teraju10_get_breadcrumb_items) supaya tidak pernah beda.
 */
function teraju10_output_breadcrumb_schema() {
	$items = teraju10_get_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}

	$list_items = array();
	foreach ( $items as $index => $item ) {
		$entry = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => wp_strip_all_tags( $item['label'] ),
		);
		if ( ! empty( $item['url'] ) ) {
			$entry['item'] = $item['url'];
		}
		$list_items[] = $entry;
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list_items,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'teraju10_output_breadcrumb_schema' );
