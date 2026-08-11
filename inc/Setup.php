<?php
/**
 * Theme setup — registers theme supports and nav menus.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup.
 *
 * Hooks its registration onto after_setup_theme. Instantiated by Bootstrap.
 */
final class Setup {

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Constructor.
	 *
	 * @param string $text_domain Theme text domain.
	 */
	public function __construct( string $text_domain ) {
		$this->text_domain = $text_domain;

		// Bootstrap already runs on after_setup_theme; register supports immediately.
		$this->load_textdomain();
		$this->register_theme_support();
		$this->register_menus();

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Load theme translations from /languages (e.g. en_US.mo, ru_RU.mo).
	 *
	 * @return void
	 */
	private function load_textdomain(): void {
		load_theme_textdomain( 'cosypaw', get_template_directory() . '/languages' );
	}

	/**
	 * Register widget areas.
	 *
	 * @return void
	 */
	public function register_widgets(): void {
		register_sidebar(
			array(
				'name'          => __( 'Primary Sidebar', 'cosypaw' ),
				'id'            => 'primary',
				'description'   => __( 'Appears beside posts and archives.', 'cosypaw' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}

	/**
	 * Register required and recommended theme supports.
	 *
	 * @return void
	 */
	private function register_theme_support(): void {
		// Required by the architecture spec.
		add_theme_support( 'woocommerce' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		// Recommended additions.
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'automatic-feed-links' );

		// WooCommerce gallery features.
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	/**
	 * Echo the brand lockup image used by the header and the footer.
	 *
	 * The logo is a circular badge with the wordmark drawn inside it, so it
	 * replaces the whole lockup rather than sitting beside a text wordmark —
	 * the word would otherwise appear twice. It also means the image carries
	 * the site name on its own, which is why the alt text is the name and not
	 * an empty decorative string.
	 *
	 * A logo set in the Customizer wins. It is emitted as a bare <img> rather
	 * than through the_custom_logo(): that helper wraps its own anchor around
	 * the image, and both call sites already sit inside one.
	 *
	 * @param string $class Extra class for the <img>.
	 * @param bool   $lazy  Whether the image may load lazily.
	 * @return void
	 */
	public static function brand_logo( string $class = '', bool $lazy = false ): void {
		$classes = trim( 'brand-logo ' . $class );
		$alt     = get_bloginfo( 'name' );
		$loading = $lazy ? ' loading="lazy"' : '';
		$custom  = (int) get_theme_mod( 'custom_logo' );
		$url     = $custom > 0 ? wp_get_attachment_image_url( $custom, 'full' ) : '';

		if ( $url ) {
			printf(
				'<img class="%1$s" src="%2$s" width="76" height="76" alt="%3$s" decoding="async"%4$s>',
				esc_attr( $classes ),
				esc_url( $url ),
				esc_attr( $alt ),
				$loading // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- literal.
			);

			return;
		}

		$base = get_template_directory_uri() . '/assets/';
		printf(
			'<img class="%1$s" src="%2$slogo-76.avif" srcset="%2$slogo-152.avif 2x, %2$slogo-228.avif 3x" width="76" height="76" alt="%3$s" decoding="async"%4$s>',
			esc_attr( $classes ),
			esc_url( $base ),
			esc_attr( $alt ),
			$loading // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- literal.
		);
	}

	/**
	 * Register navigation menu locations.
	 *
	 * @return void
	 */
	private function register_menus(): void {
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'cosypaw' ),
				'footer'  => __( 'Footer Menu', 'cosypaw' ),
			)
		);
	}
}
