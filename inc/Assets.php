<?php
/**
 * Asset pipeline — Vite integration for dev (HMR) and production (manifest),
 * with per-context code splitting.
 *
 * Entries (see vite.config.js):
 *   - assets/css/main.css        global tokens/base/glass — loaded everywhere
 *   - assets/js/landing.js       hero carousel + cart + packages — front page only
 *   - assets/css/content.css     blog/single/archive/404 — content pages only
 *   - assets/css/woocommerce.css shop/cart/checkout/account — WC pages only
 *   - assets/js/dev.js           dev-only aggregate (imports all of the above)
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets.
 *
 * Dev mode  : `hot` file in the theme root → load @vite/client + dev.js (which
 *             imports every stylesheet + component) from the dev server.
 * Production: read dist/.vite/manifest.json and enqueue only the entries the
 *             current view needs.
 */
final class Assets {

	private const ENTRY_APP     = 'assets/css/main.css';
	private const ENTRY_LANDING = 'assets/js/landing.js';
	private const ENTRY_CONTENT = 'assets/css/content.css';
	private const ENTRY_WC      = 'assets/css/woocommerce.css';
	private const ENTRY_DEV     = 'assets/js/dev.js';

	/**
	 * Theme text domain.
	 *
	 * @var string
	 */
	private string $text_domain;

	/**
	 * Absolute theme directory path (no trailing slash).
	 *
	 * @var string
	 */
	private string $theme_dir;

	/**
	 * Theme directory URI (no trailing slash).
	 *
	 * @var string
	 */
	private string $theme_uri;

	/**
	 * Script handles that must be emitted as ES modules.
	 *
	 * @var string[]
	 */
	private array $module_handles = array( 'vite-client' );

	/**
	 * Constructor.
	 *
	 * @param string $text_domain Theme text domain.
	 * @param string $theme_dir   Absolute path to the theme directory.
	 * @param string $theme_uri   URI to the theme directory.
	 */
	public function __construct( string $text_domain, string $theme_dir, string $theme_uri ) {
		$this->text_domain = $text_domain;
		$this->theme_dir   = untrailingslashit( $theme_dir );
		$this->theme_uri   = untrailingslashit( $theme_uri );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fonts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'wp_resource_hints', array( $this, 'resource_hints' ), 10, 2 );
		add_filter( 'script_loader_tag', array( $this, 'maybe_module_type' ), 10, 3 );
	}

	/**
	 * Enqueue the brand web fonts (Baloo 2 + Nunito) from Google Fonts.
	 *
	 * @return void
	 */
	public function enqueue_fonts(): void {
		wp_enqueue_style(
			'cosypaw-fonts',
			'https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Nunito:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap&subset=latin-ext',
			array(),
			null
		);
	}

	/**
	 * Preconnect to the Google Fonts hosts.
	 *
	 * @param string[] $urls          URLs to resource-hint.
	 * @param string   $relation_type The relation type.
	 * @return string[]
	 */
	public function resource_hints( array $urls, string $relation_type ): array {
		if ( 'preconnect' === $relation_type ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}

		return $urls;
	}

	/**
	 * Enqueue assets for the current view (dev or production strategy).
	 *
	 * @return void
	 */
	public function enqueue(): void {
		if ( $this->is_dev_server() ) {
			$this->enqueue_dev();
			return;
		}

		// Global styles everywhere.
		$this->enqueue_entry( 'cosypaw-app', self::ENTRY_APP );

		if ( is_front_page() ) {
			$this->enqueue_entry( 'cosypaw-landing', self::ENTRY_LANDING, array( 'cosypaw-app' ) );
			$this->localize( 'cosypaw-landing' );
		} elseif ( $this->is_wc_page() ) {
			$this->enqueue_entry( 'cosypaw-wc', self::ENTRY_WC, array( 'cosypaw-app' ) );
		} else {
			$this->enqueue_entry( 'cosypaw-content', self::ENTRY_CONTENT, array( 'cosypaw-app' ) );
		}
	}

	/**
	 * Whether the current request is a WooCommerce page.
	 *
	 * @return bool
	 */
	private function is_wc_page(): bool {
		return function_exists( 'is_woocommerce' )
			&& ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() );
	}

	/**
	 * Resolve one Vite manifest entry and enqueue its file(s).
	 *
	 * CSS-only entries (input is a .css file) enqueue a stylesheet; JS entries
	 * enqueue a module script plus any CSS Vite extracted for it.
	 *
	 * @param string   $handle Base handle.
	 * @param string   $key    Manifest key (entry source path).
	 * @param string[] $deps   Style/script dependencies.
	 * @return void
	 */
	private function enqueue_entry( string $handle, string $key, array $deps = array() ): void {
		$manifest = $this->read_manifest();
		if ( null === $manifest || empty( $manifest[ $key ]['file'] ) ) {
			return;
		}

		$entry    = $manifest[ $key ];
		$dist     = $this->theme_uri . '/dist/';
		$version  = wp_get_theme()->get( 'Version' );
		$file     = ltrim( (string) $entry['file'], '/' );

		if ( str_ends_with( $file, '.css' ) ) {
			wp_enqueue_style( $handle, $dist . $file, $deps, $version );
			return;
		}

		// JS entry: module script + extracted CSS.
		wp_enqueue_script( $handle, $dist . $file, array(), $version, true );
		$this->module_handles[] = $handle;

		if ( ! empty( $entry['css'] ) && is_array( $entry['css'] ) ) {
			foreach ( $entry['css'] as $i => $css_file ) {
				wp_enqueue_style( $handle . '-' . $i, $dist . ltrim( (string) $css_file, '/' ), $deps, $version );
			}
		}
	}

	/**
	 * Pass translated UI strings + formatting config to a front-end JS handle.
	 *
	 * @param string $handle Script handle to attach the data to.
	 * @return void
	 */
	private function localize( string $handle ): void {
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}

		wp_localize_script(
			$handle,
			'CosyPawL10n',
			array(
				'addedSuffix' => __( 'dodat u korpu', 'cosypaw' ),
				'addedToCart' => __( 'Dodato u korpu', 'cosypaw' ),
				'addedShort'  => __( 'Dodato', 'cosypaw' ),
				'demoMsg'     => __( 'Demo prodavnice — porudžbina nije aktivna', 'cosypaw' ),
				'removeLabel' => __( 'Ukloni', 'cosypaw' ),
				'currency'    => __( 'RSD', 'cosypaw' ),
				'locale'      => 'de-DE',
				// Bundle builder.
				'slotLabel'      => __( 'Motiv %d', 'cosypaw' ),
				'addToCartPrice' => __( 'Dodaj u korpu • %s', 'cosypaw' ),
				'chooseMore'     => __( 'Izaberi još %d', 'cosypaw' ),
				'motifOne'       => __( 'motiv', 'cosypaw' ),
				'motifMany'      => __( 'motiva', 'cosypaw' ),
				'bundleFull'     => __( 'Paket je pun — ukloni motiv da dodaš drugi', 'cosypaw' ),
				'notFull'        => __( 'Izaberi još %d — paket nije popunjen', 'cosypaw' ),
				'removeMotif'    => __( 'Ukloni motiv', 'cosypaw' ),
			)
		);
	}

	/**
	 * Whether the Vite dev server is running (hot file present).
	 *
	 * @return bool
	 */
	private function is_dev_server(): bool {
		return is_readable( $this->theme_dir . '/hot' );
	}

	/**
	 * Read the Vite dev server origin from the hot file.
	 *
	 * @return string Origin (no trailing slash).
	 */
	private function dev_server_url(): string {
		$url = trim( (string) file_get_contents( $this->theme_dir . '/hot' ) );

		return '' !== $url ? untrailingslashit( $url ) : 'http://localhost:5173';
	}

	/**
	 * Dev strategy — @vite/client + the aggregate dev entry (loads everything).
	 *
	 * @return void
	 */
	private function enqueue_dev(): void {
		$server = $this->dev_server_url();

		wp_enqueue_script( 'vite-client', $server . '/@vite/client', array(), null, false );
		wp_enqueue_script( 'cosypaw-dev', $server . '/' . self::ENTRY_DEV, array(), null, true );
		$this->module_handles[] = 'cosypaw-dev';

		$this->localize( 'cosypaw-dev' );
	}

	/**
	 * Read and decode the production Vite manifest.
	 *
	 * @return array<string,mixed>|null Decoded manifest or null on failure.
	 */
	private function read_manifest(): ?array {
		$path = $this->theme_dir . '/dist/.vite/manifest.json';
		if ( ! is_readable( $path ) ) {
			return null;
		}

		$decoded = json_decode( (string) file_get_contents( $path ), true );

		return is_array( $decoded ) ? $decoded : null;
	}

	/**
	 * Emit Vite-managed scripts as ES modules.
	 *
	 * @param string $tag    The full script tag.
	 * @param string $handle The script handle.
	 * @param string $src    The script source URL.
	 * @return string
	 */
	public function maybe_module_type( string $tag, string $handle, string $src ): string {
		if ( ! in_array( $handle, $this->module_handles, true ) ) {
			return $tag;
		}

		return sprintf(
			'<script type="module" src="%s" id="%s-js"></script>' . "\n",
			esc_url( $src ),
			esc_attr( $handle )
		);
	}
}
