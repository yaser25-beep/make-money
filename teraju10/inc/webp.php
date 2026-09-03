<?php
/**
 * Konversi otomatis JPG/PNG ke WebP untuk setiap gambar yang diunggah, dan
 * menyajikannya lewat <picture> dengan fallback otomatis ke format asli
 * kalau browser pembaca tidak mendukung WebP atau file WebP-nya belum ada.
 *
 * Sengaja tidak menyentuh AVIF: dukungan generate AVIF di GD/Imagick masih
 * sangat tidak merata antar hosting, jadi berisiko diam-diam gagal di
 * banyak server. WebP sudah didukung hampir semua browser modern dan hasil
 * kompresinya sudah jauh lebih kecil dari JPG/PNG asli.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cek sekali per request apakah server ini bisa generate WebP (GD/Imagick).
 * Kalau tidak bisa, seluruh fitur di file ini otomatis tidak aktif — tidak
 * ada error, situs tetap jalan normal pakai JPG/PNG asli seperti biasa.
 *
 * @return bool
 */
function teraju10_webp_supported() {
	static $supported = null;
	if ( null === $supported ) {
		$supported = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
	}
	return $supported;
}

/**
 * Bikin file .webp untuk full-size + semua ukuran (thumbnail, medium,
 * large, dst) setiap kali gambar JPG/PNG selesai diunggah/diproses
 * WordPress. Gambar yang gagal dikonversi di satu ukuran dilewati saja —
 * tidak menghentikan upload atau memunculkan error ke user.
 *
 * @param array $metadata      Metadata attachment.
 * @param int   $attachment_id ID attachment.
 * @return array
 */
function teraju10_generate_webp_versions( $metadata, $attachment_id ) {
	if ( ! teraju10_webp_supported() ) {
		return $metadata;
	}

	$mime = get_post_mime_type( $attachment_id );
	if ( ! in_array( $mime, array( 'image/jpeg', 'image/png' ), true ) ) {
		return $metadata;
	}

	$file = get_attached_file( $attachment_id );
	if ( ! $file || ! file_exists( $file ) ) {
		return $metadata;
	}

	$upload_dir = dirname( $file );
	$paths      = array( $file );

	if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
		foreach ( $metadata['sizes'] as $size ) {
			if ( ! empty( $size['file'] ) ) {
				$paths[] = $upload_dir . '/' . $size['file'];
			}
		}
	}

	foreach ( array_unique( $paths ) as $path ) {
		teraju10_convert_to_webp( $path );
	}

	update_post_meta( $attachment_id, '_teraju_webp_generated', 1 );

	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'teraju10_generate_webp_versions', 10, 2 );

/**
 * Konversi satu file gambar ke .webp di lokasi yang sama (nama sama, cuma
 * ganti ekstensi). Diam-diam gagal (return false) kalau file sumbernya
 * sudah tidak ada atau editornya error — sengaja tidak melempar exception,
 * supaya proses upload/regenerate tidak pernah terhenti gara-gara satu
 * gambar bermasalah.
 *
 * @param string $source_path Path file JPG/PNG asli.
 * @return bool
 */
function teraju10_convert_to_webp( $source_path ) {
	if ( ! file_exists( $source_path ) ) {
		return false;
	}

	$webp_path = teraju10_webp_path( $source_path );
	if ( file_exists( $webp_path ) && filemtime( $webp_path ) >= filemtime( $source_path ) ) {
		return true; // Sudah ada dan masih baru, tidak perlu diulang.
	}

	$editor = wp_get_image_editor( $source_path );
	if ( is_wp_error( $editor ) ) {
		return false;
	}

	$saved = $editor->save( $webp_path, 'image/webp' );
	return ! is_wp_error( $saved );
}

/**
 * Ganti ekstensi .jpg/.jpeg/.png jadi .webp pada sebuah path/URL.
 *
 * @param string $path Path atau URL asli.
 * @return string
 */
function teraju10_webp_path( $path ) {
	return preg_replace( '/\.(jpe?g|png)$/i', '.webp', $path );
}

/**
 * Hapus semua file .webp turunan saat attachment aslinya dihapus dari
 * Media Library, supaya tidak ada file "sampah" tertinggal di server.
 *
 * @param int $attachment_id ID attachment yang dihapus.
 */
function teraju10_delete_webp_versions( $attachment_id ) {
	$file = get_attached_file( $attachment_id );
	if ( ! $file ) {
		return;
	}

	$webp = teraju10_webp_path( $file );
	if ( file_exists( $webp ) ) {
		wp_delete_file( $webp );
	}

	$metadata = wp_get_attachment_metadata( $attachment_id );
	if ( empty( $metadata['sizes'] ) ) {
		return;
	}

	$upload_dir = dirname( $file );
	foreach ( $metadata['sizes'] as $size ) {
		if ( empty( $size['file'] ) ) {
			continue;
		}
		$size_webp = teraju10_webp_path( $upload_dir . '/' . $size['file'] );
		if ( file_exists( $size_webp ) ) {
			wp_delete_file( $size_webp );
		}
	}
}
add_action( 'delete_attachment', 'teraju10_delete_webp_versions' );

/**
 * Ubah URL upload jadi path file lokal, buat pengecekan file_exists().
 *
 * @param string $url URL gambar.
 * @return string|false
 */
function teraju10_url_to_path( $url ) {
	$upload_dir = wp_get_upload_dir();
	if ( empty( $upload_dir['baseurl'] ) || 0 !== strpos( $url, $upload_dir['baseurl'] ) ) {
		return false;
	}
	return $upload_dir['basedir'] . substr( $url, strlen( $upload_dir['baseurl'] ) );
}

/**
 * Konversi tiap URL dalam atribut srcset dari .jpg/.png ke .webp, tapi
 * cuma untuk entri yang file .webp-nya benar-benar ada di server — entri
 * lain dibuang dari srcset (tidak masalah, <img> aslinya tetap fallback).
 *
 * @param string $srcset Nilai atribut srcset asli.
 * @return string
 */
function teraju10_webp_srcset( $srcset ) {
	$entries = array_map( 'trim', explode( ',', $srcset ) );
	$out     = array();

	foreach ( $entries as $entry ) {
		if ( ! preg_match( '/^(\S+)(\s+\S+)?$/', $entry, $m ) ) {
			continue;
		}
		$path = teraju10_url_to_path( $m[1] );
		if ( $path && file_exists( teraju10_webp_path( $path ) ) ) {
			$out[] = teraju10_webp_path( $m[1] ) . ( isset( $m[2] ) ? $m[2] : '' );
		}
	}

	return implode( ', ', $out );
}

/**
 * Bungkus satu tag <img ...> jadi <picture><source webp>...<img></picture>
 * KALAU file .webp untuk src-nya benar-benar ada di server. Kalau tidak
 * (mis. gambar lama yang belum sempat dikonversi), tag <img> dikembalikan
 * apa adanya tanpa <picture> — tidak pernah ada link gambar rusak.
 *
 * @param string $img_html Markup <img ...> utuh.
 * @return string
 */
function teraju10_wrap_img_in_picture( $img_html ) {
	if ( ! preg_match( '/\ssrc=["\']([^"\']+\.(?:jpe?g|png))["\']/i', $img_html, $src_match ) ) {
		return $img_html;
	}

	$src      = $src_match[1];
	$src_path = teraju10_url_to_path( $src );
	if ( ! $src_path || ! file_exists( teraju10_webp_path( $src_path ) ) ) {
		return $img_html;
	}

	$srcset = '';
	if ( preg_match( '/\ssrcset=["\']([^"\']+)["\']/i', $img_html, $srcset_match ) ) {
		$webp_srcset = teraju10_webp_srcset( $srcset_match[1] );
		if ( $webp_srcset ) {
			$srcset = ' srcset="' . esc_attr( $webp_srcset ) . '"';
		}
	}

	if ( '' === $srcset ) {
		$srcset = ' srcset="' . esc_attr( teraju10_webp_path( $src ) ) . '"';
	}

	$sizes = '';
	if ( preg_match( '/\ssizes=["\']([^"\']+)["\']/i', $img_html, $sizes_match ) ) {
		$sizes = ' sizes="' . esc_attr( $sizes_match[1] ) . '"';
	}

	return '<picture>'
		. '<source type="image/webp"' . $srcset . $sizes . '>'
		. $img_html
		. '</picture>';
}

/**
 * Terapkan pembungkusan <picture> ke semua gambar di isi artikel.
 *
 * @param string $content Konten artikel (setelah blocks di-render).
 * @return string
 */
function teraju10_content_images_to_webp( $content ) {
	if ( is_feed() || is_admin() || ! teraju10_webp_supported() || '' === $content ) {
		return $content;
	}

	return preg_replace_callback(
		'/<img\b[^>]*>/i',
		function ( $matches ) {
			return teraju10_wrap_img_in_picture( $matches[0] );
		},
		$content
	);
}
add_filter( 'the_content', 'teraju10_content_images_to_webp', 20 );

/**
 * Terapkan pembungkusan <picture> juga ke gambar yang dirender lewat
 * wp_get_attachment_image()/the_post_thumbnail() — dipakai untuk hero
 * image, kartu artikel, dan widget di seluruh tema.
 *
 * @param string $html Markup <img> yang sudah dirender WordPress.
 * @return string
 */
function teraju10_attachment_image_to_webp( $html ) {
	if ( is_feed() || is_admin() || ! teraju10_webp_supported() || '' === $html ) {
		return $html;
	}
	return teraju10_wrap_img_in_picture( $html );
}
add_filter( 'wp_get_attachment_image_html', 'teraju10_attachment_image_to_webp' );

/**
 * Hitung berapa gambar JPG/PNG lama yang belum punya versi WebP — dipakai
 * di halaman Tools > Regenerate WebP.
 *
 * @return int
 */
function teraju10_count_pending_webp() {
	$query = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => array( 'image/jpeg', 'image/png' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_teraju_webp_generated',
					'compare' => 'NOT EXISTS',
				),
			),
			'no_found_rows'  => false,
		)
	);
	return (int) $query->found_posts;
}

/**
 * Halaman Tools > Regenerate WebP — buat mengonversi gambar-gambar LAMA
 * (yang sudah diunggah sebelum fitur ini aktif) secara bertahap, tanpa
 * bikin server timeout, dengan tombol yang bisa dijalankan kapan saja.
 */
function teraju10_register_webp_admin_page() {
	add_management_page(
		__( 'Regenerate WebP', 'teraju10' ),
		__( 'Regenerate WebP', 'teraju10' ),
		'manage_options',
		'teraju10-regenerate-webp',
		'teraju10_render_webp_admin_page'
	);
}
add_action( 'admin_menu', 'teraju10_register_webp_admin_page' );

/**
 * Render halaman Tools > Regenerate WebP.
 */
function teraju10_render_webp_admin_page() {
	echo '<div class="wrap"><h1>' . esc_html__( 'Regenerate WebP', 'teraju10' ) . '</h1>';

	if ( ! teraju10_webp_supported() ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Server ini belum bisa membuat file WebP (dukungan GD/Imagick di hosting belum ada). Hubungi penyedia hosting untuk mengaktifkan dukungan WebP, atau upgrade versi PHP/Imagick-nya. Situs tetap berjalan normal dengan JPG/PNG asli selama itu.', 'teraju10' ) . '</p></div></div>';
		return;
	}

	$remaining = teraju10_count_pending_webp();
	?>
	<p><?php esc_html_e( 'Konversi gambar JPG/PNG LAMA (yang diunggah sebelum fitur WebP aktif) jadi WebP, supaya artikel-artikel lama ikut lebih ringan. Gambar yang diunggah SETELAH fitur ini aktif otomatis dikonversi sendiri — tombol ini cuma untuk membereskan arsip lama.', 'teraju10' ); ?></p>
	<p><strong id="teraju10-webp-remaining"><?php echo esc_html( sprintf( /* translators: %d: jumlah gambar */ __( '%d gambar belum dikonversi.', 'teraju10' ), $remaining ) ); ?></strong></p>
	<button type="button" class="button button-primary" id="teraju10-webp-start" <?php disabled( 0 === $remaining ); ?>><?php esc_html_e( 'Mulai konversi', 'teraju10' ); ?></button>
	<p id="teraju10-webp-status"></p>
	<script>
	( function () {
		var btn         = document.getElementById( 'teraju10-webp-start' );
		var statusEl    = document.getElementById( 'teraju10-webp-status' );
		var remainingEl = document.getElementById( 'teraju10-webp-remaining' );
		if ( ! btn ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			btn.disabled = true;
			runBatch();
		} );

		function runBatch() {
			statusEl.textContent = <?php echo wp_json_encode( __( 'Memproses, jangan tutup halaman ini…', 'teraju10' ) ); ?>;

			fetch( ajaxurl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				credentials: 'same-origin',
				body: 'action=teraju10_regenerate_webp_batch&nonce=<?php echo esc_js( wp_create_nonce( 'teraju10_webp_batch' ) ); ?>',
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( data ) {
					if ( ! data.success ) {
						statusEl.textContent = <?php echo wp_json_encode( __( 'Terjadi kesalahan, coba klik lagi.', 'teraju10' ) ); ?>;
						btn.disabled = false;
						return;
					}

					remainingEl.textContent = data.data.remaining + <?php echo wp_json_encode( ' ' . __( 'gambar belum dikonversi.', 'teraju10' ) ); ?>;

					if ( data.data.remaining > 0 && data.data.processed > 0 ) {
						runBatch();
					} else {
						statusEl.textContent = <?php echo wp_json_encode( __( 'Selesai.', 'teraju10' ) ); ?>;
						btn.disabled = true;
					}
				} )
				[ 'catch' ]( function () {
					statusEl.textContent = <?php echo wp_json_encode( __( 'Terjadi kesalahan, coba klik lagi.', 'teraju10' ) ); ?>;
					btn.disabled = false;
				} );
		}
	}() );
	</script>
	</div>
	<?php
}

/**
 * AJAX handler: proses 10 gambar lama berikutnya jadi WebP.
 */
function teraju10_ajax_regenerate_webp_batch() {
	check_ajax_referer( 'teraju10_webp_batch', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	$batch = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => array( 'image/jpeg', 'image/png' ),
			'posts_per_page' => 10,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_teraju_webp_generated',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	$processed = 0;
	foreach ( $batch->posts as $attachment_id ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );
		teraju10_generate_webp_versions( is_array( $metadata ) ? $metadata : array(), $attachment_id );
		++$processed;
	}

	wp_send_json_success(
		array(
			'processed' => $processed,
			'remaining' => teraju10_count_pending_webp(),
		)
	);
}
add_action( 'wp_ajax_teraju10_regenerate_webp_batch', 'teraju10_ajax_regenerate_webp_batch' );
