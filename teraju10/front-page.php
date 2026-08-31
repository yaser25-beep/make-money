<?php
/**
 * Template homepage.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="wrap">

	<?php
	$hero_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 5,
			'ignore_sticky_posts' => false,
		)
	);

	if ( $hero_query->have_posts() ) :
		$hero_query->the_post();
		?>
		<section class="hero">
			<div class="hero-main">
				<a class="thumb <?php echo has_post_thumbnail() ? '' : esc_attr( teraju10_placeholder_class() ); ?>" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large' ); ?>
					<?php endif; ?>
				</a>
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
			</div>

			<?php if ( $hero_query->post_count > 1 ) : ?>
				<ul class="hero-list">
					<?php
					while ( $hero_query->have_posts() ) :
						$hero_query->the_post();
						?>
						<li class="item">
							<div>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							</div>
							<a class="thumb <?php echo has_post_thumbnail() ? '' : esc_attr( teraju10_placeholder_class() ); ?>" href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'teraju10-square' ); ?>
								<?php endif; ?>
							</a>
						</li>
						<?php
					endwhile;
					?>
				</ul>
			<?php endif; ?>
		</section>
		<?php
	endif;
	wp_reset_postdata();
	?>

	<?php
	$kabar_tag = teraju10_get_option( 'kabar_kalbar_tag' );
	if ( $kabar_tag ) :
		$kabar_query = new WP_Query(
			array(
				'post_type'      => 'post',
				'tag'            => $kabar_tag,
				'posts_per_page' => 3,
			)
		);
		if ( $kabar_query->have_posts() ) :
			?>
			<section class="kabar-kalbar">
				<div class="kk-head">
					<div>
						<h2><?php esc_html_e( 'Kabar Kalbar', 'teraju10' ); ?></h2>
						<p><?php esc_html_e( 'Cerita positif dari warga Kalbar, dikirim langsung lewat WhatsApp dan diverifikasi tim redaksi sebelum tayang.', 'teraju10' ); ?></p>
					</div>
					<?php
					$wa_number  = preg_replace( '/[^0-9]/', '', teraju10_get_option( 'wa_number' ) );
					$wa_message = teraju10_get_option( 'wa_message' );
					?>
					<a class="kk-cta" href="https://wa.me/<?php echo esc_attr( $wa_number ); ?>?text=<?php echo rawurlencode( $wa_message ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Punya kabar baik? Kirim ke sini', 'teraju10' ); ?>
					</a>
				</div>
				<div class="kk-grid">
					<?php
					while ( $kabar_query->have_posts() ) :
						$kabar_query->the_post();
						?>
						<div class="kk-item">
							<span class="badge"><?php esc_html_e( 'Kiriman warga', 'teraju10' ); ?></span>
							<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
						</div>
						<?php
					endwhile;
					?>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
	endif;
	?>

	<?php
	$rubric_slugs = array(
		teraju10_get_option( 'rubric_1_category' ),
		teraju10_get_option( 'rubric_2_category' ),
	);

	foreach ( $rubric_slugs as $slug ) :
		if ( ! teraju10_category_exists( $slug ) ) {
			continue;
		}
		$category    = get_category_by_slug( $slug );
		$rubric_query = new WP_Query(
			array(
				'post_type'      => 'post',
				'category_name'  => $slug,
				'posts_per_page' => 3,
			)
		);
		if ( ! $rubric_query->have_posts() ) {
			wp_reset_postdata();
			continue;
		}
		?>
		<section class="rubric">
			<div class="section-head">
				<h2><?php echo esc_html( $category->name ); ?></h2>
				<a class="see-all" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php esc_html_e( 'Lihat semua', 'teraju10' ); ?></a>
			</div>
			<div class="rubric-grid">
				<?php
				while ( $rubric_query->have_posts() ) :
					$rubric_query->the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	endforeach;
	?>

	<?php
	$warisan_slug = teraju10_get_option( 'warisan_category' );
	if ( teraju10_category_exists( $warisan_slug ) ) :
		$warisan_query = new WP_Query(
			array(
				'post_type'      => 'post',
				'category_name'  => $warisan_slug,
				'posts_per_page' => 1,
			)
		);
		if ( $warisan_query->have_posts() ) :
			$warisan_query->the_post();
			?>
			<section class="feature-warisan">
				<a class="fw-media <?php echo has_post_thumbnail() ? '' : esc_attr( teraju10_placeholder_class() ); ?>" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large' ); ?>
					<?php endif; ?>
				</a>
				<div class="fw-text">
					<div class="eyebrow"><?php esc_html_e( 'Warisan Kalbar', 'teraju10' ); ?></div>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<div class="fw-byline"><?php echo esc_html( sprintf( __( 'Oleh %s', 'teraju10' ), get_the_author() ) ); ?></div>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
	endif;
	?>

	<?php
	$english_headline = teraju10_get_option( 'english_headline' );
	$english_url      = teraju10_get_option( 'english_url' );
	if ( empty( $english_url ) && teraju10_category_exists( 'english-version' ) ) {
		$english_cat = get_category_by_slug( 'english-version' );
		$english_url = get_category_link( $english_cat );
	}
	if ( $english_headline && $english_url ) :
		?>
		<section class="english-teaser">
			<div class="et-text">
				<div class="eyebrow"><?php esc_html_e( 'English version', 'teraju10' ); ?></div>
				<h2><?php echo esc_html( $english_headline ); ?></h2>
				<p><?php echo esc_html( teraju10_get_option( 'english_desc' ) ); ?></p>
			</div>
			<a class="et-cta" href="<?php echo esc_url( $english_url ); ?>"><?php esc_html_e( 'Read in English', 'teraju10' ); ?></a>
		</section>
		<?php
	endif;
	?>

</main>

<?php
get_footer();
