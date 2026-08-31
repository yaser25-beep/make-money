<?php
/**
 * Template halaman statis (Redaksi, Pedoman Media Siber, Privasi, dll).
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main class="narrow" style="padding-bottom:60px;">
		<?php teraju10_breadcrumbs(); ?>
		<h1 class="headline"><?php the_title(); ?></h1>
		<div class="article-body">
			<?php the_content(); ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
