<?php
/**
 * Template part: kartu artikel (gambar + judul).
 * Dipakai di homepage, arsip, hasil pencarian, dan "Baca juga".
 *
 * Terima $args:
 * - image_size (string) ukuran gambar, default 'teraju10-card'.
 * - show_meta  (bool)   tampilkan kategori & tanggal, default false.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image_size = isset( $args['image_size'] ) ? $args['image_size'] : 'teraju10-card';
$show_meta  = isset( $args['show_meta'] ) ? (bool) $args['show_meta'] : false;
?>
<article <?php post_class( 'card' ); ?>>
	<a class="thumb <?php echo has_post_thumbnail() ? '' : esc_attr( teraju10_placeholder_class() ); ?>" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( $image_size, array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
		<?php endif; ?>
	</a>
	<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	<?php if ( $show_meta ) : ?>
		<div class="meta">
			<?php
			$cats = get_the_category();
			if ( ! empty( $cats ) ) {
				echo esc_html( $cats[0]->name ) . ' &middot; ';
			}
			echo esc_html( get_the_date() );
			?>
		</div>
	<?php endif; ?>
</article>
