<?php
/**
 * Template arsip: kategori, tag, dan arsip tanggal.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="wrap">
	<div class="page-header">
		<div class="wrap">
			<h1><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<p>', '</p>' ); ?>
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
