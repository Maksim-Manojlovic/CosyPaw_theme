<?php
/**
 * CosyPaw theme bootstrap / entry point.
 *
 * Responsibilities (and ONLY these):
 *  1. Define the theme text domain constant.
 *  2. Register a native, dependency-free PSR-4-style autoloader for the Theme\ namespace.
 *  3. Instantiate Theme\Bootstrap exactly once (no Singleton).
 *
 * All business logic lives in classes under /inc. This file must stay thin.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme text domain. Must match the "Text Domain" header in style.css.
 *
 * @var string
 */
define( 'COSYPAW_TEXT_DOMAIN', 'cosypaw' );

/**
 * Native runtime autoloader for the Theme\ namespace.
 *
 * Maps Theme\Foo\Bar to /inc/Foo/Bar.php (PSR-4 logic) with no external
 * dependencies. This is the RUNTIME autoloader and is fully independent from
 * Composer — Composer is used only for development/testing (see composer.json).
 * The two mechanisms must never overlap.
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix   = 'Theme\\';
		$base_dir = __DIR__ . '/inc/';

		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class, $len ) ) {
			return; // Not our namespace; let other autoloaders handle it.
		}

		$relative = substr( $class, $len );
		$file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

/**
 * Front-end language switcher. Instantiated here (not in Bootstrap) so its
 * `locale` filter is registered as early as possible — before any text domain
 * (theme on after_setup_theme, WooCommerce on init) is loaded.
 *
 * @var \Theme\Language
 */
$GLOBALS['cosypaw_language'] = new \Theme\Language();

/**
 * Accessor for the Language instance (template tag).
 *
 * @return \Theme\Language
 */
function cosypaw_language(): \Theme\Language {
	return $GLOBALS['cosypaw_language'];
}

/**
 * Boot the theme. Bootstrap is instantiated exactly once here via a plain
 * `new` — NO Singleton, no static factory, no static $instance.
 */
add_action(
	'after_setup_theme',
	static function (): void {
		new \Theme\Bootstrap( COSYPAW_TEXT_DOMAIN );
	}
);
