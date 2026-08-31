<?php
/**
 * Template artikel tunggal.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id      = get_the_ID();
	$has_sidebar  = is_active_sidebar( 'post-sidebar' );
	$layout_class = $has_sidebar ? 'post-layout' : 'post-layout no-sidebar';
	$facts          = teraju10_get_facts( $post_id );
	$summary_points = teraju10_get_summary_points( $post_id );
	$categories     = get_the_category();
	?>

	<main>
		<div class="<?php echo esc_attr( $layout_class ); ?>">
			<article <?php post_class( 'post-main' ); ?>>

				<?php teraju10_breadcrumbs(); ?>

				<?php if ( ! empty( $categories ) ) : ?>
					<a class="article-tag" href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a>
				<?php endif; ?>

				<h1 class="headline"><?php the_title(); ?></h1>

				<div class="byline-row">
					<div class="avatar">
						<?php
						$author_id = get_the_author_meta( 'ID' );
						if ( get_avatar( $author_id ) ) {
							echo get_avatar( $author_id, 40 );
						} else {
							echo esc_html( teraju10_initials( get_the_author() ) );
						}
						?>
					</div>
					<div class="byline-meta">
						<a class="author" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php the_author(); ?></a>
						<div class="dates">
							<span><?php echo esc_html( sprintf( __( 'Diterbitkan %s', 'teraju10' ), get_the_date( 'j F Y, H.i' ) ) ); ?></span>
							<?php if ( get_the_modified_date( 'U' ) > get_the_date( 'U' ) ) : ?>
								<span class="rule"></span>
								<span><?php echo esc_html( sprintf( __( 'Diperbarui %s', 'teraju10' ), get_the_modified_date( 'j F Y, H.i' ) ) ); ?></span>
							<?php endif; ?>
							<span class="rule"></span>
							<span><?php echo esc_html( teraju10_reading_time( $post_id ) ); ?></span>
						</div>
					</div>
					<div class="share-row">
						<button type="button" class="icon-btn" id="saveBtn" aria-label="<?php esc_attr_e( 'Simpan artikel', 'teraju10' ); ?>" title="<?php esc_attr_e( 'Simpan artikel', 'teraju10' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4V3z"></path></svg>
						</button>
						<button type="button" class="icon-btn" data-share="whatsapp" aria-label="<?php esc_attr_e( 'Bagikan ke WhatsApp', 'teraju10' ); ?>" title="<?php esc_attr_e( 'Bagikan ke WhatsApp', 'teraju10' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.36 7.56L4 20l1.06-4.48A8.5 8.5 0 1 1 21 11.5z"></path></svg>
						</button>
						<button type="button" class="icon-btn" data-share="copy" aria-label="<?php esc_attr_e( 'Salin tautan', 'teraju10' ); ?>" title="<?php esc_attr_e( 'Salin tautan', 'teraju10' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1.5 1.5"></path><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1.5-1.5"></path></svg>
						</button>
					</div>
				</div>

				<?php if ( ! empty( $summary_points ) ) : ?>
					<div class="summary-box">
						<div class="label"><?php esc_html_e( 'Ringkasan cepat', 'teraju10' ); ?></div>
						<?php if ( count( $summary_points ) > 1 ) : ?>
							<ul>
								<?php foreach ( $summary_points as $point ) : ?>
									<li><?php echo esc_html( $point ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p><?php echo esc_html( $summary_points[0] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="hero-image">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
					<?php
					$caption = wp_get_attachment_caption( get_post_thumbnail_id() );
					if ( $caption ) :
						?>
						<div class="caption"><?php echo esc_html( $caption ); ?></div>
						<?php
					endif;
				endif;
				?>

				<div class="article-body">
					<?php the_content(); ?>
				</div>

				<?php if ( ! empty( $facts ) ) : ?>
					<div class="fact-box">
						<div class="label"><?php esc_html_e( 'Fakta cepat', 'teraju10' ); ?></div>
						<ul>
							<?php foreach ( $facts as $fact ) : ?>
								<li><span class="num"><?php echo esc_html( $fact['num'] ); ?></span><span class="cap"><?php echo esc_html( $fact['cap'] ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php
				$post_tags = get_the_tags();
				if ( $post_tags ) :
					?>
					<ul class="tags">
						<?php foreach ( $post_tags as $tag ) : ?>
							<li><a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>"><?php echo esc_html( $tag->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<?php
				endif;
				?>

				<?php $author_bio = get_the_author_meta( 'description' ); ?>
				<?php if ( $author_bio ) : ?>
					<div class="author-card">
						<div class="avatar">
							<?php
							if ( get_avatar( $author_id ) ) {
								echo get_avatar( $author_id, 52 );
							} else {
								echo esc_html( teraju10_initials( get_the_author() ) );
							}
							?>
						</div>
						<div>
							<h4><?php the_author(); ?></h4>
							<p><?php echo esc_html( $author_bio ); ?></p>
							<a class="author-link" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php esc_html_e( 'Lihat semua tulisan', 'teraju10' ); ?></a>
						</div>
					</div>
				<?php endif; ?>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</article>

			<?php if ( $has_sidebar ) : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>
		</div>

		<?php
		$related_query = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'post__not_in'        => array( $post_id ),
				'category__in'        => wp_list_pluck( $categories, 'term_id' ),
				'ignore_sticky_posts' => true,
			)
		);
		if ( $related_query->have_posts() ) :
			?>
			<section class="related">
				<div class="narrow">
					<h2><?php esc_html_e( 'Baca juga', 'teraju10' ); ?></h2>
					<div class="rubric-grid">
						<?php
						while ( $related_query->have_posts() ) :
							$related_query->the_post();
							get_template_part( 'template-parts/content', 'card' );
						endwhile;
						?>
					</div>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
		?>
	</main>

	<?php
endwhile;

get_footer();
