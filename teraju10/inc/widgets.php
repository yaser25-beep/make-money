<?php
/**
 * Widget kustom:
 * 1. Teraju10_Popular_Posts_Widget — daftar bernomor, otomatis atau manual.
 * 2. Teraju10_Ad_Slot_Widget — slot gambar promosi atau kode iklan.
 * Keduanya didaftarkan supaya bisa diseret ke "Sidebar Artikel" lewat
 * Appearance > Widgets, tanpa perlu Elementor atau plugin tambahan.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget: Postingan Terpopuler.
 */
class Teraju10_Popular_Posts_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'teraju10_popular_posts',
			__( 'Teraju: Postingan Terpopuler', 'teraju10' ),
			array(
				'description' => __( 'Daftar bernomor artikel terpopuler. Mode otomatis (berdasar jumlah tayangan 7 hari terakhir) atau masukkan ID artikel manual.', 'teraju10' ),
			)
		);
	}

	public function widget( $args, $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Terpopuler minggu ini', 'teraju10' );
		$count      = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 10;
		$manual_ids = ! empty( $instance['manual_ids'] ) ? $instance['manual_ids'] : '';

		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		if ( $manual_ids ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $manual_ids ) ) );
			$query_args = array(
				'post_type'           => 'post',
				'post__in'            => ! empty( $ids ) ? $ids : array( 0 ),
				'orderby'             => 'post__in',
				'posts_per_page'      => count( $ids ),
				'ignore_sticky_posts' => true,
			);
		} else {
			$query_args = array(
				'post_type'           => 'post',
				'posts_per_page'      => $count,
				/* Urut dari tayangan 7 hari terakhir tertinggi; kalau nilainya
				   sama (mis. situs baru & belum ada data tayangan sama sekali,
				   semuanya 0), jatuh ke artikel terbaru dulu — supaya widget
				   tidak pernah tampil kosong/acak sebelum data terkumpul. */
				'orderby'             => array(
					'meta_value_num' => 'DESC',
					'date'           => 'DESC',
				),
				'meta_key'            => TERAJU10_VIEWS_WEEKLY_META,
				/* Sertakan juga artikel yang belum pernah tercatat tayangannya
				   (meta belum ada sama sekali) — dianggap 0, tetap ikut diurut,
				   bukan malah tersingkir dari daftar. */
				'meta_query'          => array(
					'relation' => 'OR',
					array(
						'key'     => TERAJU10_VIEWS_WEEKLY_META,
						'compare' => 'EXISTS',
					),
					array(
						'key'     => TERAJU10_VIEWS_WEEKLY_META,
						'compare' => 'NOT EXISTS',
					),
				),
				'ignore_sticky_posts' => true,
			);
		}

		$query = new WP_Query( $query_args );

		if ( $query->have_posts() ) {
			echo '<ol class="pop-list-compact">';
			$i = 1;
			while ( $query->have_posts() ) {
				$query->the_post();
				echo '<li><span class="pop-num-sm">' . esc_html( $i ) . '</span><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
				++$i;
			}
			echo '</ol>';
		}
		wp_reset_postdata();

		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : __( 'Terpopuler minggu ini', 'teraju10' );
		$count      = isset( $instance['count'] ) ? absint( $instance['count'] ) : 10;
		$manual_ids = isset( $instance['manual_ids'] ) ? $instance['manual_ids'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Judul:', 'teraju10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Jumlah artikel (mode otomatis):', 'teraju10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'manual_ids' ) ); ?>"><?php esc_html_e( 'ID artikel manual (opsional, pisahkan koma):', 'teraju10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'manual_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'manual_ids' ) ); ?>" type="text" value="<?php echo esc_attr( $manual_ids ); ?>" placeholder="123, 456, 789">
			<small><?php esc_html_e( 'Jika diisi, daftar manual ini dipakai dan mode otomatis diabaikan.', 'teraju10' ); ?></small>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance               = array();
		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['count']      = absint( $new_instance['count'] );
		$instance['manual_ids'] = sanitize_text_field( $new_instance['manual_ids'] );
		return $instance;
	}
}

/**
 * Widget: Slot Iklan / Gambar.
 */
class Teraju10_Ad_Slot_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'teraju10_ad_slot',
			__( 'Teraju: Slot Iklan / Gambar', 'teraju10' ),
			array(
				'description' => __( 'Slot fleksibel: unggah gambar promosi, atau tempel kode iklan (mis. AdSense/GAM).', 'teraju10' ),
			)
		);
	}

	public function widget( $args, $instance ) {
		$label    = ! empty( $instance['label'] ) ? $instance['label'] : __( 'Iklan', 'teraju10' );
		$image_id = ! empty( $instance['image_id'] ) ? absint( $instance['image_id'] ) : 0;
		$link     = ! empty( $instance['link'] ) ? $instance['link'] : '';
		$ad_code  = ! empty( $instance['ad_code'] ) ? $instance['ad_code'] : '';

		echo wp_kses_post( $args['before_widget'] );
		echo '<span class="ad-label">' . esc_html( $label ) . '</span>';

		if ( $ad_code ) {
			echo '<div class="ad-slot-code">' . $ad_code . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- kode iklan pihak ketiga, sudah difilter saat disimpan (lihat update()).
		} elseif ( $image_id ) {
			$image_html = wp_get_attachment_image(
				$image_id,
				'medium',
				false,
				array( 'style' => 'width:100%;height:auto;border-radius:8px;display:block;' )
			);
			if ( $link ) {
				echo '<a href="' . esc_url( $link ) . '">' . wp_kses_post( $image_html ) . '</a>';
			} else {
				echo wp_kses_post( $image_html );
			}
		} else {
			echo '<div class="ad-slot placeholder"><span>' . esc_html__( 'Slot iklan / gambar', 'teraju10' ) . '<br>300 &times; 250</span></div>';
		}

		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$label     = isset( $instance['label'] ) ? $instance['label'] : __( 'Iklan', 'teraju10' );
		$image_id  = isset( $instance['image_id'] ) ? absint( $instance['image_id'] ) : 0;
		$link      = isset( $instance['link'] ) ? $instance['link'] : '';
		$ad_code   = isset( $instance['ad_code'] ) ? $instance['ad_code'] : '';
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'label' ) ); ?>"><?php esc_html_e( 'Label kecil:', 'teraju10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'label' ) ); ?>" type="text" value="<?php echo esc_attr( $label ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Gambar promosi (opsional):', 'teraju10' ); ?></label><br>
			<img class="teraju10-ad-preview" src="<?php echo esc_url( $image_url ); ?>" style="max-width:100%;margin-bottom:8px;<?php echo $image_url ? '' : 'display:none;'; ?>">
			<input type="hidden" class="teraju10-ad-image-id" name="<?php echo esc_attr( $this->get_field_name( 'image_id' ) ); ?>" value="<?php echo esc_attr( $image_id ); ?>">
			<br>
			<button type="button" class="button teraju10-ad-upload"><?php esc_html_e( 'Pilih gambar', 'teraju10' ); ?></button>
			<button type="button" class="button teraju10-ad-remove"><?php esc_html_e( 'Hapus', 'teraju10' ); ?></button>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php esc_html_e( 'Tautan gambar (opsional):', 'teraju10' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="text" value="<?php echo esc_attr( $link ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_code' ) ); ?>"><?php esc_html_e( 'Atau tempel kode iklan (mis. AdSense):', 'teraju10' ); ?></label>
			<textarea class="widefat" rows="4" id="<?php echo esc_attr( $this->get_field_id( 'ad_code' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_code' ) ); ?>"><?php echo esc_textarea( $ad_code ); ?></textarea>
			<small><?php esc_html_e( 'Jika diisi, ini dipakai dan gambar di atas diabaikan. Butuh akun dengan izin "unfiltered_html" agar tag <script> tidak terpotong.', 'teraju10' ); ?></small>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['label']    = sanitize_text_field( $new_instance['label'] );
		$instance['image_id'] = absint( $new_instance['image_id'] );
		$instance['link']     = esc_url_raw( $new_instance['link'] );
		$instance['ad_code']  = current_user_can( 'unfiltered_html' )
			? $new_instance['ad_code']
			: wp_kses_post( $new_instance['ad_code'] );
		return $instance;
	}
}

/**
 * Daftarkan kedua widget.
 */
function teraju10_register_widgets() {
	register_widget( 'Teraju10_Popular_Posts_Widget' );
	register_widget( 'Teraju10_Ad_Slot_Widget' );
}
add_action( 'widgets_init', 'teraju10_register_widgets' );

/**
 * Muat pustaka media WordPress dan skrip admin kecil untuk tombol
 * "Pilih gambar" di widget Slot Iklan, khusus di layar Widgets & Customizer.
 *
 * @param string $hook Nama layar admin saat ini.
 */
function teraju10_admin_widget_assets( $hook ) {
	if ( 'widgets.php' !== $hook && 'customize.php' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'teraju10-admin-widgets',
		get_template_directory_uri() . '/assets/js/admin-widgets.js',
		array( 'jquery' ),
		TERAJU10_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'teraju10_admin_widget_assets' );
