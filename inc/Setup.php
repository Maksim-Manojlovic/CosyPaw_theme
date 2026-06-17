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
