<?php
/**
 * Site header — opening document, announcement marquee, sticky nav.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

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

<a class="skip-link" href="#primary"><?php esc_html_e( 'Pređi na sadržaj', 'cosypaw' ); ?></a>

<!-- Announcement marquee -->
<div class="announce" data-marquee>
	<?php
	$announcements = array(
		__( 'Ručni rad sa puno ljubavi', 'cosypaw' ),
		__( 'Besplatna dostava na Trio paket', 'cosypaw' ),
		__( 'Mekani svet peškirića', 'cosypaw' ),
	);
	// One group is rendered twice; the track animates -50% (one full group),
	// so the second group is in place exactly when the first scrolls out =
	// seamless, gap-free loop. Phrases are repeated inside each group so a
	// single group is wider than the viewport.
	//
	// The track is aria-hidden because it repeats every phrase four times; the
	// list below carries the same announcements once, so assistive tech gets
	// the free-shipping offer instead of the whole bar being hidden from it.
	?>
	<div class="announce__viewport" aria-hidden="true">
		<div class="announce__track">
			<?php for ( $cosypaw_g = 0; $cosypaw_g < 2; $cosypaw_g++ ) : ?>
				<div class="announce__group">
					<?php foreach ( array_merge( $announcements, $announcements ) as $line ) : ?>
						<span><?php echo esc_html( $line ); ?></span><span class="announce__dot">•</span>
					<?php endforeach; ?>
				</div>
			<?php endfor; ?>
		</div>
	</div>

	<ul class="screen-reader-text">
		<?php foreach ( $announcements as $line ) : ?>
			<li><?php echo esc_html( $line ); ?></li>
		<?php endforeach; ?>
	</ul>

	<?php
	// WCAG 2.2.2 — the bar loops indefinitely, so it needs a pause control.
	// Ships hidden; Marquee.js unhides it, so no-JS never shows a dead button.
	?>
	<button
		type="button"
		class="announce__pause"
		data-marquee-toggle
		aria-pressed="false"
		aria-label="<?php esc_attr_e( 'Pauziraj najave', 'cosypaw' ); ?>"
		hidden
	>
		<svg class="announce__pause-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
		<svg class="announce__play-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>
	</button>
</div>

<header class="site-header" data-site-nav>
	<nav class="nav" aria-label="<?php esc_attr_e( 'Glavna navigacija', 'cosypaw' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__brand">
			<?php \Theme\Setup::brand_logo(); ?>
		</a>

		<div class="nav__links" id="cosypaw-nav-menu" data-nav-panel>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'nav__menu',
						'depth'          => 1,
						'fallback_cb'    => false,
						'link_before'    => '<span class="nav__link">',
						'link_after'     => '</span>',
					)
				);
			} else {
				// Default in-page anchors (resolve from any page back to the homepage sections).
				$cosypaw_home = home_url( '/' );
				// Listed in scroll order — nav that disagrees with the page it
				// links into makes the anchors feel arbitrary.
				$cosypaw_anchors = array(
					'#galerija' => __( 'Peškirići', 'cosypaw' ),
					'#paketi'   => __( 'Paketi', 'cosypaw' ),
					'#zasto'    => __( 'Zašto CosyPaw', 'cosypaw' ),
				);
				foreach ( $cosypaw_anchors as $anchor => $label ) {
					printf(
						'<a href="%1$s" class="nav__link">%2$s</a>',
						esc_url( ( is_front_page() ? '' : $cosypaw_home ) . $anchor ),
						esc_html( $label )
					);
				}
			}
			?>

			<?php
			// Inside the panel rather than the action bar: three 44px flag
			// targets do not fit alongside the brand, cart and toggle at 375px,
			// and the panel gives them the room. On desktop the panel is inline,
			// so the switcher sits beside the menu exactly as before.
			if ( function_exists( 'cosypaw_language' ) ) {
				cosypaw_language()->switcher();
			}
			?>
		</div>

		<?php
		// Cart and the nav toggle stay outside the collapsing panel so they are
		// reachable at every width.
		?>
		<div class="nav__actions">
			<?php
			$cosypaw_wc    = function_exists( 'WC' ) && function_exists( 'wc_get_cart_url' );

			// WooCommerce being active is not the same as the shop being live:
			// until the seeder has run, nothing in the catalog is a real product
			// and the demo cart is still the one holding the customer's picks.
			$cosypaw_live = $cosypaw_wc && class_exists( '\Theme\WooCommerce' ) && (
				(array) get_option( \Theme\WooCommerce::PRODUCT_MAP_OPTION, array() )
				|| (array) get_option( \Theme\WooCommerce::PACKAGE_MAP_OPTION, array() )
			);
			$cosypaw_count = ( $cosypaw_wc && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
			$cosypaw_cart_svg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h13l-1.2 8.4a2 2 0 0 1-2 1.7H9.2a2 2 0 0 1-2-1.7L6 4H3"/><circle cx="9.5" cy="20" r="1.2"/><circle cx="16.5" cy="20" r="1.2"/></svg>';
			// data-cart-owner marks the badge as the live shop's. The demo cart
			// in CartDrawer.js writes to [data-cart-count] on boot from its own
			// sessionStorage, which off the front page is the last word on the
			// element — there is no cart-fragments script out here to correct it
			// afterwards, so a live cart of two would render as the demo cart's
			// nothing. The marker tells the demo cart to leave it alone.
			$cosypaw_badge = sprintf(
				'<span class="cart-btn__badge" data-cart-count%1$s%2$s>%3$s</span>',
				$cosypaw_live ? ' data-cart-owner="wc"' : '',
				$cosypaw_count < 1 ? ' hidden' : '',
				esc_html( (string) $cosypaw_count )
			);
			$cosypaw_svg_allowed = array(
				'svg'    => array( 'width' => array(), 'height' => array(), 'viewbox' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array(), 'stroke-linecap' => array(), 'stroke-linejoin' => array(), 'aria-hidden' => array() ),
				'path'   => array( 'd' => array() ),
				'circle' => array( 'cx' => array(), 'cy' => array(), 'r' => array() ),
				'span'   => array( 'class' => array(), 'data-cart-count' => array(), 'data-cart-owner' => array(), 'hidden' => array() ),
			);
			if ( $cosypaw_wc ) :
				?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-btn" aria-label="<?php esc_attr_e( 'Pogledaj korpu', 'cosypaw' ); ?>">
					<?php echo wp_kses( $cosypaw_cart_svg . $cosypaw_badge, $cosypaw_svg_allowed ); ?>
				</a>
			<?php else : ?>
				<button type="button" class="cart-btn" data-cart-toggle aria-label="<?php esc_attr_e( 'Otvori korpu', 'cosypaw' ); ?>">
					<?php echo wp_kses( $cosypaw_cart_svg . $cosypaw_badge, $cosypaw_svg_allowed ); ?>
				</button>
			<?php endif; ?>

			<?php
			// Ships hidden; SiteNav.js unhides it once the panel is collapsible,
			// so a failed script leaves a plain visible nav instead of a dead
			// button.
			?>
			<button
				type="button"
				class="nav__toggle"
				data-nav-toggle
				aria-controls="cosypaw-nav-menu"
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Meni', 'cosypaw' ); ?>"
				hidden
			>
				<svg class="nav__toggle-open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
				<svg class="nav__toggle-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
			</button>
		</div>
	</nav>
</header>
