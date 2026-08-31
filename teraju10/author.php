<?php
/**
 * Template halaman profil penulis.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$author_id = get_queried_object_id();
?>

<main class="wrap">
	<div class="page-header">
		<div class="narrow author-box">
			<div class="avatar">
				<?php
				if ( get_avatar( $author_id ) ) {
					echo get_avatar( $author_id, 56 );
				} else {
					echo esc_html( teraju10_initials( get_the_author_meta( 'display_name', $author_id ) ) );
				}
				?>
			</div>
			<div>
				<h1><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></h1>
				<?php $bio = get_the_author_meta( 'description', $author_id ); ?>
				<?php if ( $bio ) : ?>
					<p><?php echo esc_html( $bio ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<section class="rubric">
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card', array( 'show_meta' => true ) );
				endwhile;
				?>
			</div>
		</section>
		<?php the_posts_pagination( array( 'prev_text' => __( '&larr; Sebelumnya', 'teraju10' ), 'next_text' => __( 'Berikutnya &rarr;', 'teraju10' ) ) ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</main>

<?php
get_footer();
