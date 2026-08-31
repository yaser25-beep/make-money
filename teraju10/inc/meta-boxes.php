<?php
/**
 * Meta box "Ringkasan Cepat" dan "Fakta Cepat" untuk halaman edit artikel.
 * Ini yang mengisi kotak AEO/fact-box di template single.php.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function teraju10_add_meta_boxes() {
	add_meta_box(
		'teraju10_summary',
		__( 'Ringkasan Cepat (untuk AI & pencarian)', 'teraju10' ),
		'teraju10_render_summary_box',
		'post',
		'normal',
		'high'
	);

	add_meta_box(
		'teraju10_facts',
		__( 'Fakta Cepat', 'teraju10' ),
		'teraju10_render_facts_box',
		'post',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'teraju10_add_meta_boxes' );

/**
 * Tampilan kotak Ringkasan Cepat.
 *
 * @param WP_Post $post Objek post.
 */
function teraju10_render_summary_box( $post ) {
	wp_nonce_field( 'teraju10_save_meta', 'teraju10_meta_nonce' );
	$value = get_post_meta( $post->ID, '_teraju_summary', true );
	?>
	<p>
		<strong><?php esc_html_e( 'Satu poin inti per baris', 'teraju10' ); ?></strong>
		&mdash;
		<?php esc_html_e( 'tekan Enter untuk baris baru. Tulis 1-4 kalimat pendek yang MASING-MASING berdiri sendiri sebagai fakta/inti terpenting berita ini — gaya "Smart Brevity" yang dipakai Axios/Semafor, BUKAN satu paragraf panjang. Ini tampil sebagai ringkasan ber-bullet di atas artikel, dan paling sering dikutip AI Overview/Perplexity/ChatGPT. Kosongkan untuk memakai excerpt otomatis (kalau begitu, tampil sebagai satu kalimat biasa, bukan bullet).', 'teraju10' ); ?>
	</p>
	<textarea
		name="teraju10_summary"
		rows="4"
		style="width:100%;"
		maxlength="600"
		placeholder="<?php esc_attr_e( "Contoh:\nHarga rumah di Pontianak naik rata-rata 11% dalam setahun terakhir\nKenaikan paling tajam terjadi di kawasan Pontianak Selatan\nPemicunya: proyek jalan lingkar baru dan lonjakan pendatang", 'teraju10' ); ?>"
	><?php echo esc_textarea( $value ); ?></textarea>
	<?php
}

/**
 * Tampilan kotak Fakta Cepat.
 *
 * @param WP_Post $post Objek post.
 */
function teraju10_render_facts_box( $post ) {
	$value = get_post_meta( $post->ID, '_teraju_facts', true );
	?>
	<p>
		<?php esc_html_e( 'Satu baris satu fakta, format: Label | Nilai. Kosongkan jika artikel ini tidak butuh kotak fakta.', 'teraju10' ); ?>
	</p>
	<textarea
		name="teraju10_facts"
		rows="5"
		style="width:100%;font-family:monospace;"
		placeholder="Kenaikan harga rata-rata dalam setahun|+11%&#10;Kawasan dengan kenaikan tertinggi|Pontianak Selatan"
	><?php echo esc_textarea( $value ); ?></textarea>
	<?php
}

/**
 * Simpan data meta box dengan aman: cek nonce, cek autosave, cek hak akses.
 *
 * @param int $post_id ID post yang sedang disimpan.
 */
function teraju10_save_meta_boxes( $post_id ) {
	if ( ! isset( $_POST['teraju10_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['teraju10_meta_nonce'] ) ), 'teraju10_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['teraju10_summary'] ) ) {
		update_post_meta( $post_id, '_teraju_summary', sanitize_textarea_field( wp_unslash( $_POST['teraju10_summary'] ) ) );
	}

	if ( isset( $_POST['teraju10_facts'] ) ) {
		update_post_meta( $post_id, '_teraju_facts', sanitize_textarea_field( wp_unslash( $_POST['teraju10_facts'] ) ) );
	}
}
add_action( 'save_post', 'teraju10_save_meta_boxes' );
