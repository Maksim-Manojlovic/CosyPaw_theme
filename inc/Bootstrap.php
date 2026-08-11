<?php
/**
 * Core bootstrap class — wires the theme modules together via Dependency Injection.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap.
 *
 * Instantiated exactly once from functions.php with a plain `new` call.
 * This class deliberately does NOT implement the Singleton pattern: no
 * private static $instance, no static factory, no getInstance(). All
 * sub-modules are created in the constructor and receive their dependencies
 * through constructor injection.
 */
final class Bootstrap {

	/**
	 * Theme text domain, injected from functions.php.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Theme setup module.
	 *
	 * @var Setup
	 */
	private Setup $setup;

	/**
	 * Asset (Vite) pipeline module.
	 *
	 * @var Assets
	 */
	private Assets $assets;

	/**
	 * WooCommerce integration module. Null when WooCommerce is inactive.
	 *
	 * @var WooCommerce|null
	 */
	private ?WooCommerce $woocommerce = null;

	/**
	 * SEO meta / structured data module.
	 *
	 * @var Seo
	 */
	private Seo $seo;

	/**
	 * Constructor — builds and injects every sub-module.
	 *
	 * @param string $text_domain Theme text domain.
	 */
	public function __construct( string $text_domain ) {
		$this->text_domain = $text_domain;

		// Core modules (always present).
		$this->setup  = new Setup( $this->text_domain );
		$this->assets = new Assets( $this->text_domain, get_template_directory(), get_template_directory_uri() );
		$this->seo    = new Seo( new Catalog() );

		// Conditional WooCommerce module.
		if ( class_exists( 'WooCommerce' ) ) {
			$this->woocommerce = new WooCommerce( $this->text_domain, new Catalog() );
		} else {
			$this->register_missing_woocommerce_notice();
		}
	}

	/**
	 * Register an admin notice warning that WooCommerce is required but inactive.
	 *
	 * Prevents fatal errors on installs without WooCommerce while still nudging
	 * the administrator to install it.
	 *
	 * @return void
	 */
	private function register_missing_woocommerce_notice(): void {
		add_action(
			'admin_notices',
			function (): void {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				printf(
					'<div class="notice notice-warning"><p>%s</p></div>',
					esc_html__( 'CosyPaw recommends the WooCommerce plugin. Shop features are disabled until it is installed and active.', 'cosypaw' )
				);
			}
		);
	}
}
