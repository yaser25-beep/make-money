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
 * Ambil "Ringkasan Cepat" sebagai poin-poin inti, gaya "Smart Brevity" ala
 * Axios/Semafor: satu baris di kotak Ringkasan Cepat = satu poin/fakta
 * terpenting. Urutan prioritas sumbernya:
 *
 * 1. Kotak "Ringkasan Cepat" kalau diisi redaksi — satu baris = satu poin.
 * 2. Excerpt MANUAL (kotak "Excerpt" bawaan WordPress) kalau diisi — berarti
 *    redaksi memang sengaja menulis ringkasan sendiri di sana.
 * 3. Ekstraksi otomatis dari ISI artikel (teraju10_auto_summary_points()):
 *    beberapa kalimat paling representatif dipilih berdasar kata-kata yang
 *    paling sering muncul di seluruh artikel — bukan sekadar memotong
 *    paragraf pertama, supaya lebih dekat ke "inti berita" yang sebenarnya.
 * 4. Excerpt OTOMATIS WordPress (potongan awal konten) sebagai jaring
 *    pengaman terakhir, kalau artikel terlalu pendek untuk poin (3).
 *
 * Kalau hasil akhirnya cuma satu poin, dikembalikan sebagai satu poin saja
 * supaya tampil sebagai kalimat biasa, bukan daftar ber-bullet yang aneh
 * untuk satu kalimat. Dibatasi maksimal 4 poin biar tetap "cepat".
 *
 * @param int $post_id ID artikel.
 * @return array
 */
function teraju10_get_summary_points( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = get_post_meta( $post_id, '_teraju_summary', true );

	if ( ! empty( $raw ) ) {
		$lines  = preg_split( '/\r\n|\r|\n/', (string) $raw );
		$points = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$points[] = $line;
			}
		}
		if ( ! empty( $points ) ) {
			return array_slice( $points, 0, 4 );
		}
	}

	$manual_excerpt = trim( wp_strip_all_tags( (string) get_post_field( 'post_excerpt', $post_id ) ) );
	if ( '' !== $manual_excerpt ) {
		return array( $manual_excerpt );
	}

	$auto_points = teraju10_auto_summary_points( $post_id, 3 );
	if ( ! empty( $auto_points ) ) {
		return $auto_points;
	}

	$fallback = get_the_excerpt( $post_id );
	return $fallback ? array( $fallback ) : array();
}

/**
 * Ekstraksi otomatis beberapa kalimat paling representatif dari ISI
 * artikel (bukan cuma paragraf pertama) — ringkasan ekstraktif berbasis
 * frekuensi kata, versi ringan dari teknik yang sama dasarnya dengan
 * TextRank. Ini HANYA dipakai kalau redaksi tidak mengisi Ringkasan Cepat
 * maupun Excerpt manual, jadi ini jaring pengaman, bukan pengganti editor
 * manusia — tapi jauh lebih mendekati "inti artikel" dibanding sekadar
 * memotong kata-kata pertama.
 *
 * Cara kerja singkat:
 * 1. Pecah isi artikel jadi kalimat, skor tiap kalimat dari frekuensi
 *    kata-katanya (minus stopword) di seluruh artikel — MURNI dari kata,
 *    tanpa bobot posisi, supaya kalimat penting di tengah/akhir artikel
 *    punya peluang sama besar terpilih seperti kalimat pembuka.
 * 2. Kalimat dibagi jadi $max_points "seksi" berurutan (kalau diminta 3
 *    poin: sepertiga awal/tengah/akhir artikel), lalu dari TIAP seksi
 *    diambil satu kalimat berskor tertinggi. Ini jaminan struktural supaya
 *    hasilnya benar-benar menyebar di sepanjang artikel, bukan cuma dari
 *    paragraf pembuka — itu keluhan utama yang mau diperbaiki di sini.
 * 3. Kalimat yang tereliminasi karena terlalu mirip kalimat lain yang
 *    sudah terpilih (dedup overlap) digantikan kandidat berikutnya.
 * 4. Urutkan lagi sesuai posisi asli di artikel, biar mengalir alami.
 *
 * @param int $post_id ID artikel.
 * @param int $max_points Jumlah maksimal poin.
 * @return array
 */
function teraju10_auto_summary_points( $post_id, $max_points = 3 ) {
	$content = get_post_field( 'post_content', $post_id );
	/* Pastikan ada batas spasi di antara elemen blok (paragraf, heading,
	   dst) SEBELUM tag-nya dibuang — beberapa editor menyimpan HTML tanpa
	   spasi di antar tag, yang tanpa ini bisa membuat kalimat terakhir satu
	   paragraf "menempel" ke kalimat pertama paragraf berikutnya. */
	$content = preg_replace( '/<\/(p|div|li|h[1-6]|blockquote|br)[^>]*>/i', "$0\n\n", $content );
	$content = wp_strip_all_tags( strip_shortcodes( $content ) );
	$content = trim( preg_replace( '/\s+/u', ' ', $content ) );

	if ( '' === $content ) {
		return array();
	}

	$sentences = preg_split( '/(?<=[.!?])\s+(?=[A-Z0-9"\'\x{2018}\x{201C}])/u', $content );
	$sentences = is_array( $sentences ) ? $sentences : array( $content );

	$candidates = array();
	foreach ( $sentences as $index => $sentence ) {
		$sentence = trim( $sentence );
		if ( '' === $sentence ) {
			continue;
		}
		$word_count = str_word_count( $sentence );
		/* Buang kalimat yang kependekan (biasanya sub-judul/label yang
		   kepotong) atau kepanjangan (kurang padat untuk sebuah "poin"). */
		if ( $word_count < 6 || $word_count > 40 ) {
			continue;
		}
		$candidates[] = array(
			'text'  => $sentence,
			'index' => $index,
		);
	}

	if ( empty( $candidates ) ) {
		return array();
	}

	if ( count( $candidates ) <= $max_points ) {
		return wp_list_pluck( $candidates, 'text' );
	}

	$stopwords = teraju10_id_stopwords();
	$word_freq = array();
	foreach ( $candidates as $c ) {
		foreach ( teraju10_tokenize( $c['text'] ) as $word ) {
			if ( mb_strlen( $word ) < 3 || isset( $stopwords[ $word ] ) ) {
				continue;
			}
			$word_freq[ $word ] = isset( $word_freq[ $word ] ) ? $word_freq[ $word ] + 1 : 1;
		}
	}

	foreach ( $candidates as $i => $c ) {
		$score = 0.0;
		$scored_word = 0;
		foreach ( teraju10_tokenize( $c['text'] ) as $word ) {
			if ( isset( $word_freq[ $word ] ) ) {
				$score += $word_freq[ $word ];
				++$scored_word;
			}
		}
		$candidates[ $i ]['score'] = $scored_word ? ( $score / $scored_word ) : 0;
	}

	/* Bagi kandidat (yang urutannya masih sesuai posisi asli di artikel)
	   jadi $max_points seksi berurutan, lalu ambil satu kalimat berskor
	   tertinggi dari TIAP seksi — jaminan sebaran, bukan cuma soal siapa
	   duluan/terdepan di artikel. */
	$total        = count( $candidates );
	$section_size = max( 1, (int) ceil( $total / $max_points ) );
	$sections     = array_chunk( $candidates, $section_size );

	$picked = array();
	foreach ( $sections as $section ) {
		usort(
			$section,
			function ( $a, $b ) {
				return $b['score'] <=> $a['score'];
			}
		);

		foreach ( $section as $c ) {
			$is_redundant = false;
			foreach ( $picked as $already ) {
				if ( teraju10_sentence_overlap( $c['text'], $already['text'] ) > 0.55 ) {
					$is_redundant = true;
					break;
				}
			}
			if ( ! $is_redundant ) {
				$picked[] = $c;
				break;
			}
		}
	}

	/* Kalau ada seksi yang gagal menyumbang poin (semua kandidatnya
	   ternyata redundan dengan poin lain), isi kekurangannya dari sisa
	   kandidat berskor tertinggi supaya jumlah poin tetap maksimal. */
	if ( count( $picked ) < min( $max_points, $total ) ) {
		$picked_texts = wp_list_pluck( $picked, 'text' );
		$remaining    = array_filter(
			$candidates,
			function ( $c ) use ( $picked_texts ) {
				return ! in_array( $c['text'], $picked_texts, true );
			}
		);
		usort(
			$remaining,
			function ( $a, $b ) {
				return $b['score'] <=> $a['score'];
			}
		);
		foreach ( $remaining as $c ) {
			if ( count( $picked ) >= $max_points ) {
				break;
			}
			$picked[] = $c;
		}
	}

	usort(
		$picked,
		function ( $a, $b ) {
			return $a['index'] <=> $b['index'];
		}
	);

	return wp_list_pluck( $picked, 'text' );
}

/**
 * Pecah teks jadi array kata lowercase (huruf/angka saja), buat kebutuhan
 * penghitungan frekuensi & overlap kalimat.
 *
 * @param string $text Teks masukan.
 * @return array
 */
function teraju10_tokenize( $text ) {
	$text = mb_strtolower( $text, 'UTF-8' );
	preg_match_all( '/[a-z0-9]+/u', $text, $matches );
	return $matches[0];
}

/**
 * Rasio kemiripan dua kalimat (Jaccard sederhana atas kata unik), dipakai
 * untuk membuang poin yang isinya terlalu mirip dengan poin lain yang
 * sudah terpilih.
 *
 * @param string $a Kalimat pertama.
 * @param string $b Kalimat kedua.
 * @return float 0..1
 */
function teraju10_sentence_overlap( $a, $b ) {
	$words_a = array_unique( teraju10_tokenize( $a ) );
	$words_b = array_unique( teraju10_tokenize( $b ) );

	if ( empty( $words_a ) || empty( $words_b ) ) {
		return 0.0;
	}

	$common = array_intersect( $words_a, $words_b );
	$union  = array_unique( array_merge( $words_a, $words_b ) );

	return count( $union ) ? ( count( $common ) / count( $union ) ) : 0.0;
}

/**
 * Daftar stopword Bahasa Indonesia (kata umum yang dibuang dari
 * penghitungan frekuensi, karena tidak menandakan topik apa pun).
 * Sengaja disimpan statis di dalam fungsi (bukan file terpisah) supaya
 * ringkasan ini tetap "ringan" — tidak ada file/aset tambahan.
 *
 * @return array Kata sebagai key, untuk pengecekan isset() yang cepat.
 */
function teraju10_id_stopwords() {
	static $stopwords = null;

	if ( null === $stopwords ) {
		$list = array(
			'yang', 'dan', 'di', 'ke', 'dari', 'ini', 'itu', 'untuk', 'dengan', 'pada',
			'adalah', 'akan', 'atau', 'juga', 'tidak', 'dalam', 'dapat', 'oleh', 'sebagai',
			'karena', 'yaitu', 'namun', 'tersebut', 'tetapi', 'sudah', 'belum', 'masih',
			'saat', 'ketika', 'jika', 'kalau', 'tapi', 'tak', 'ada', 'bisa', 'harus',
			'tentang', 'para', 'antara', 'setelah', 'sebelum', 'saja', 'lebih', 'sangat',
			'begitu', 'hingga', 'sampai', 'agar', 'supaya', 'maka', 'sehingga', 'yakni',
			'serta', 'bagi', 'terhadap', 'kata', 'ujar', 'ungkap', 'jelas', 'tutur',
			'menurut', 'bahwa', 'satu', 'dua', 'tiga', 'kami', 'kita', 'mereka', 'dia',
			'ia', 'nya', 'anda', 'saya', 'kamu', 'kalian', 'apa', 'siapa', 'mengapa',
			'bagaimana', 'dimana', 'kapan', 'seperti', 'banyak', 'sedikit', 'semua',
			'beberapa', 'setiap', 'tiap', 'pun', 'lah', 'kah', 'pula', 'memang', 'justru',
			'bahkan', 'apabila', 'sementara', 'selama', 'sejak', 'sekitar', 'kembali',
			'terus', 'langsung', 'secara', 'melalui', 'tanpa', 'selain', 'baik', 'maupun',
			'yg', 'dgn', 'dr', 'utk', 'krn', 'tsb', 'the', 'a', 'an', 'of', 'in', 'to', 'and',
		);
		$stopwords = array_fill_keys( $list, true );
	}

	return $stopwords;
}

/**
 * Versi satu-string dari teraju10_get_summary_points(), tiap poin diberi
 * titik penutup lalu digabung jadi satu paragraf. Dipakai untuk meta
 * description / JSON-LD, yang butuh teks datar, bukan daftar.
 *
 * @param int $post_id ID artikel.
 * @return string
 */
function teraju10_get_summary( $post_id = 0 ) {
	$points = teraju10_get_summary_points( $post_id );

	$points = array_map(
		function ( $point ) {
			return rtrim( $point, '.!?' ) . '.';
		},
		$points
	);

	return implode( ' ', $points );
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
 * Pesan ajakan bagikan di kotak akhir artikel, MENYESUAIKAN kategori/tag
 * artikelnya — bukan satu kalimat generik untuk semua artikel. Prioritas:
 * 1) pesan manual per-artikel (meta _teraju_share_message, lihat meta box
 *    "Pesan Ajakan Bagikan"), untuk artikel liputan mendalam/pilar yang
 *    ingin pesan spesifik; 2) kecocokan kategori/tag lewat peta di
 *    teraju10_end_share_message_map(); 3) pesan default umum.
 * Bisa disesuaikan lebih lanjut lewat filter 'teraju10_end_share_message'
 * (mis. oleh automasi yang tahu konteks lebih spesifik dari satu artikel).
 *
 * @param int $post_id ID artikel.
 * @return string
 */
function teraju10_end_share_message( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$manual = trim( (string) get_post_meta( $post_id, '_teraju_share_message', true ) );
	if ( '' !== $manual ) {
		return apply_filters( 'teraju10_end_share_message', $manual, $post_id );
	}

	$default = __( 'Artikel ini bermanfaat? Bagikan ke teman-teman Anda.', 'teraju10' );
	$map     = teraju10_end_share_message_map();

	$post_tags = get_the_tags( $post_id );
	$slugs     = array_merge(
		wp_list_pluck( get_the_category( $post_id ), 'slug' ),
		$post_tags ? wp_list_pluck( $post_tags, 'slug' ) : array()
	);

	foreach ( $map as $slug => $message ) {
		if ( '' !== $slug && in_array( $slug, $slugs, true ) ) {
			return apply_filters( 'teraju10_end_share_message', $message, $post_id );
		}
	}

	return apply_filters( 'teraju10_end_share_message', $default, $post_id );
}

/**
 * Peta slug kategori/tag -> pesan ajakan bagikan yang lebih relevan
 * daripada kalimat generik. Urutan array menentukan prioritas kalau satu
 * artikel cocok dengan lebih dari satu slug. Beberapa entri diambil
 * langsung dari slug yang sudah diatur di Customizer (Rubrik Homepage /
 * Efek Kesadaran Karhutla), supaya otomatis ikut benar kalau slug itu
 * diubah — bukan di-hardcode dua kali di dua tempat berbeda.
 *
 * @return array
 */
function teraju10_end_share_message_map() {
	$map = array(
		teraju10_get_option( 'karhutla_category' ) => __( 'Bagikan supaya lebih banyak orang tahu kondisi ini.', 'teraju10' ),
		teraju10_get_option( 'warisan_category' )  => __( 'Bantu sejarah dan warisan Kalimantan Barat ini dibaca lebih banyak orang.', 'teraju10' ),
		teraju10_get_option( 'rubric_2_category' ) => __( 'Ikut sebarkan supaya isu ini didengar yang berwenang.', 'teraju10' ),
		teraju10_get_option( 'rubric_1_category' ) => __( 'Bagikan ke sesama pecinta otomotif Kalbar.', 'teraju10' ),
		'ekonomi'                                   => __( 'Bagikan supaya lebih banyak warga Kalbar tahu dampaknya.', 'teraju10' ),
		'hukum'                                     => __( 'Bagikan supaya lebih banyak orang tahu duduk perkaranya.', 'teraju10' ),
		'opini'                                     => __( 'Setuju atau tidak, ikut sebarkan biar diskusinya lebih ramai.', 'teraju10' ),
	);

	return apply_filters( 'teraju10_end_share_message_map', $map );
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
