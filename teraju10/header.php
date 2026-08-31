<?php
/**
 * Template header, dipakai di semua halaman.
 *
 * @package Teraju10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Lompat ke konten', 'teraju10' ); ?></a>

<?php if ( is_singular( 'post' ) ) : ?>
	<div class="progress-bar" id="progressBar"></div>
<?php endif; ?>

<?php
$redaksi_page = get_page_by_path( 'redaksi' );
$has_english  = teraju10_category_exists( 'english-version' );
$wa_number    = preg_replace( '/[^0-9]/', '', teraju10_get_option( 'wa_number' ) );
$wa_message   = teraju10_get_option( 'wa_message' );

if ( $redaksi_page || $has_english || $wa_number ) :
	?>
	<div class="utility-bar">
		<div class="wrap">
			<div class="links">
				<?php if ( $redaksi_page ) : ?>
					<a href="<?php echo esc_url( get_permalink( $redaksi_page ) ); ?>"><?php esc_html_e( 'Redaksi', 'teraju10' ); ?></a>
				<?php endif; ?>

				<?php if ( $has_english ) : ?>
					<?php $english_cat = get_category_by_slug( 'english-version' ); ?>
					<a href="<?php echo esc_url( get_category_link( $english_cat ) ); ?>"><?php esc_html_e( 'English version', 'teraju10' ); ?></a>
				<?php endif; ?>

				<?php if ( $wa_number ) : ?>
					<a href="https://wa.me/<?php echo esc_attr( $wa_number ); ?>?text=<?php echo rawurlencode( $wa_message ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Kirim Kabar Kalbar', 'teraju10' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
endif;
?>

<header class="site-header">
	<div class="wrap header-row">
		<div>
			<?php if ( has_custom_logo() ) : ?>
				<div class="site-logo"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a class="brand-mark" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php
					$site_name = get_bloginfo( 'name' );
					if ( false !== strpos( $site_name, '.' ) ) {
						list( $main, $suffix ) = explode( '.', $site_name, 2 );
						echo esc_html( $main ) . '<span>.' . esc_html( $suffix ) . '</span>';
					} else {
						echo esc_html( $site_name );
					}
					?>
				</a>
			<?php endif; ?>
			<?php $description = get_bloginfo( 'description' ); ?>
			<?php if ( $description ) : ?>
				<p class="brand-tagline"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
		<a class="search-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>?s=" aria-label="<?php esc_attr_e( 'Cari', 'teraju10' ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
		</a>
	</div>
</header>

<nav class="main-nav" aria-label="<?php esc_attr_e( 'Menu utama', 'teraju10' ); ?>">
	<div class="wrap">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				)
			);
		} else {
			teraju10_fallback_menu();
		}
		?>
	</div>
</nav>

<?php if ( is_front_page() ) : ?>
	<div class="ticker">
		<div class="wrap">
			<div class="item">
				<span class="label"><?php esc_html_e( 'Harga emas / gram', 'teraju10' ); ?></span>
				<strong><?php echo esc_html( teraju10_get_ticker_value( 'gold_price' ) ); ?></strong>
				<?php $gold_change = teraju10_get_ticker_value( 'gold_change' ); ?>
				<span class="<?php echo ( strpos( $gold_change, '-' ) === 0 ) ? 'down' : 'up'; ?>">
					<?php echo ( strpos( $gold_change, '-' ) === 0 ) ? '&#9660;' : '&#9650;'; ?> <?php echo esc_html( ltrim( $gold_change, '+-' ) ); ?>
				</span>
			</div>
			<div class="item">
				<span class="label">USD/IDR</span>
				<strong><?php echo esc_html( teraju10_get_ticker_value( 'usd_idr' ) ); ?></strong>
				<?php $usd_change = teraju10_get_ticker_value( 'usd_change' ); ?>
				<span class="<?php echo ( strpos( $usd_change, '-' ) === 0 ) ? 'down' : 'up'; ?>">
					<?php echo ( strpos( $usd_change, '-' ) === 0 ) ? '&#9660;' : '&#9650;'; ?> <?php echo esc_html( ltrim( $usd_change, '+-' ) ); ?>
				</span>
			</div>
			<div class="updated">
				<?php echo esc_html( teraju10_get_ticker_value( 'updated' ) ); ?>
				<?php if ( '1' === teraju10_get_option( 'ticker_auto_update' ) ) : ?>
					<span class="ticker-source"><?php esc_html_e( '· Kurs: ECB, Emas: GoldPrice.org', 'teraju10' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<div id="content">
