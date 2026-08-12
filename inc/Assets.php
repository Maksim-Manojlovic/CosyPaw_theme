<?php
/**
 * Asset pipeline — Vite integration for dev (HMR) and production (manifest),
 * with per-context code splitting.
 *
 * Entries (see vite.config.js):
 *   - assets/js/app.js           global tokens/base/glass + site chrome (nav,
 *                                cart drawer) — loaded everywhere
 *   - assets/js/landing.js       hero carousel + package builder — front page only
 *   - assets/css/content.css     blog/single/archive/404 — content pages only
 *   - assets/css/woocommerce.css shop/cart/checkout/account — WC pages only
 *   - assets/js/dev.js           dev-only aggregate (imports all of the above)
 *
 * The brand webfonts are no longer enqueued here — they are self-hosted and
 * declared in assets/css/fonts.css, which main.css imports, so they ride along
 * with the app entry instead of costing a cross-origin request of their own.
 *
 * This class also owns the front page's critical path: it preloads the LCP
 * image and pulls WooCommerce's stylesheets and jQuery out of the way.
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

	private const ENTRY_APP     = 'assets/js/app.js';
	private const ENTRY_LANDING = 'assets/js/landing.js';
	private const ENTRY_CONTENT = 'assets/css/content.css';
	private const ENTRY_WC      = 'assets/css/woocommerce.css';
	private const ENTRY_DEV     = 'assets/js/dev.js';

	/**
	 * `sizes` for the hero carousel slides, shared with front-page.php.
	 *
	 * The slide is `min(380px, 100vw - 44px) - 32px`: .hero contributes 22px of
	 * side padding, .hero__card another 16px, and the card caps at 380px. The
	 * breakpoint is where those meet — 100vw - 44px = 380px, so 424px.
	 *
	 * This used to claim `calc(100vw - 44px)`, which forgot the card padding and
	 * overstated the slide by 32px. That was enough to push a 412px phone over
	 * the 600w candidate and onto the 900w one for the LCP image.
	 *
	 * @var string
	 */
	public const HERO_SIZES = '(max-width: 424px) calc(100vw - 76px), 348px';

	/**
	 * `sizes` for the motif grid cards, shared with front-page.php.
	 *
	 * @var string
	 */
	public const GRID_SIZES = '(max-width: 560px) calc(100vw - 72px), (max-width: 880px) calc(50vw - 47px), 329px';

	/**
	 * WooCommerce stylesheets that do nothing on the front page.
	 *
	 * The landing page's "Kupi" buttons are AJAX add-to-cart links, so the
	 * WooCommerce *scripts* stay; it is only these four stylesheets that are
	 * dead weight, and they cost four render-blocking requests before first
	 * paint. The theme styles every WooCommerce-flavoured element the landing
	 * page shows, down to `.added_to_cart` in landing.css.
	 *
	 * @var string[]
	 */
	private const WC_STYLE_HANDLES = array(
		'woocommerce-general',
		'woocommerce-layout',
		'woocommerce-smallscreen',
		'wc-blocks-style',
	);

	/**
	 * Script handles to push out of the critical path with `defer`.
	 *
	 * jQuery is printed in the head and blocks the parser for ~1s on a throttled
	 * mobile connection; nothing on the front end needs it before DOM ready.
	 * WordPress only honours a defer strategy when every dependent script is
	 * also deferrable, so jQuery's WooCommerce dependents are listed too — miss
	 * one and core silently keeps the whole chain blocking. Handles that are not
	 * registered on the current view are skipped.
	 *
	 * @var string[]
	 */
	private const DEFER_HANDLES = array(
		'jquery-core',
		'jquery-migrate',
		'jquery',
		'wc-blockui',
		'wc-jquery-blockui',
		'wc-js-cookie',
		'woocommerce',
		'wc-add-to-cart',
		'wc-cart-fragments',
		'sourcebuster-js',
		'wc-order-attribution',
		// Site Kit hangs its WooCommerce event provider off the same chain; left
		// blocking it would drag every handle above it back to blocking too.
		'googlesitekit-events-provider-woocommerce',
	);

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

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		// Priority 99: WooCommerce registers its own front-end assets on the
		// default priority, so its handles only exist to trim once it has run.
		add_action( 'wp_enqueue_scripts', array( $this, 'trim_woocommerce_assets' ), 99 );
		add_filter( 'style_loader_tag', array( $this, 'suppress_woocommerce_styles' ), 10, 2 );
		add_action( 'wp_head', array( $this, 'preload_lcp_image' ), 2 );
		add_filter( 'script_loader_tag', array( $this, 'maybe_module_type' ), 10, 3 );
	}

	/**
	 * Preload the first hero slide — the front page's LCP element.
	 *
	 * Without this the browser cannot start the fetch until it has parsed the
	 * stylesheets ahead of the markup, which on mobile costs most of a second.
	 * The three attributes below have to stay character-identical to the `<img>`
	 * in front-page.php, or the preload picks a different candidate and the
	 * page downloads the motif twice.
	 *
	 * @return void
	 */
	public function preload_lcp_image(): void {
		if ( ! is_front_page() ) {
			return;
		}

		$featured = ( new Catalog() )->featured();
		if ( empty( $featured[0] ) ) {
			return;
		}

		printf(
			'<link rel="preload" as="image" href="%1$s" imagesrcset="%2$s" imagesizes="%3$s" fetchpriority="high">' . "\n",
			esc_url( $featured[0]['image_md'] ),
			esc_attr( self::motif_srcset( $featured[0] ) ),
			esc_attr( self::HERO_SIZES )
		);
	}

	/**
	 * The responsive candidate list shared by the hero and the motif grid.
	 *
	 * Stops at 900w on purpose. Neither placement is ever wider than ~370 CSS
	 * px, so the 1086w original could only ever be picked by a 3x phone, where
	 * it bought nothing and cost 90 KB — that single mis-pick was the front
	 * page's largest transfer.
	 *
	 * @param array{image_md:string,image_lg:string} $motif Motif record from Catalog.
	 * @return string
	 */
	public static function motif_srcset( array $motif ): string {
		return $motif['image_md'] . ' 600w, ' . $motif['image_lg'] . ' 900w';
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

		// Global styles + site chrome everywhere. The strings go on this handle
		// rather than the landing one because the cart drawer is site-wide.
		$app_styles = $this->enqueue_entry( 'cosypaw-app', self::ENTRY_APP );
		$this->localize( 'cosypaw-app' );

		if ( is_front_page() ) {
			$this->enqueue_entry( 'cosypaw-landing', self::ENTRY_LANDING, $app_styles, array( 'cosypaw-app' ) );
		} elseif ( $this->is_wc_page() ) {
			$this->enqueue_entry( 'cosypaw-wc', self::ENTRY_WC, $app_styles );
		} else {
			$this->enqueue_entry( 'cosypaw-content', self::ENTRY_CONTENT, $app_styles );
		}
	}

	/**
	 * Take WooCommerce's front-end assets off the critical path.
	 *
	 * Defers jQuery and the WooCommerce scripts everywhere. They are all
	 * `jQuery(function(){…})` consumers, so running after parse is not a
	 * behaviour change — but it does mean nothing in the head waits on them.
	 * The stylesheets are handled separately in trim_woocommerce_styles().
	 *
	 * @return void
	 */
	public function trim_woocommerce_assets(): void {
		if ( is_admin() || $this->is_dev_server() ) {
			return;
		}

		foreach ( self::DEFER_HANDLES as $handle ) {
			if ( wp_script_is( $handle, 'registered' ) ) {
				wp_script_add_data( $handle, 'strategy', 'defer' );
			}
		}
	}

	/**
	 * Drop WooCommerce's stylesheet tags from the front page.
	 *
	 * Suppressing the printed tag rather than dequeuing the handle. A dequeue
	 * does work, but only from a late enough hook: `wc-blocks-style` is
	 * re-enqueued from `wp_head` by WooCommerce's notices service, carries
	 * `wp_add_inline_style` content, and is declared a dependency by several
	 * block types, so anything at `wp_enqueue_scripts` priority 99 misses it and
	 * only `wp_print_styles` catches it. Filtering the tag is downstream of all
	 * of that and does not depend on winning an ordering race.
	 *
	 * Deliberately the front page and nowhere else: `is_wc_page()` cannot see a
	 * `[products]` shortcode or a WooCommerce block dropped into an ordinary
	 * page, and those would lose their styling. The front page is a hand-built
	 * template, so there is nothing there to break.
	 *
	 * @param string $tag    The full `<link>` tag.
	 * @param string $handle The stylesheet handle.
	 * @return string The tag, or an empty string to drop it.
	 */
	public function suppress_woocommerce_styles( string $tag, string $handle ): string {
		if ( is_admin() || $this->is_dev_server() || ! is_front_page() ) {
			return $tag;
		}

		return in_array( $handle, self::WC_STYLE_HANDLES, true ) ? '' : $tag;
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
	 * @param string   $handle      Base handle.
	 * @param string   $key         Manifest key (entry source path).
	 * @param string[] $style_deps  Stylesheet dependencies (cascade order).
	 * @param string[] $script_deps Script dependencies (execution order).
	 * @return string[] Handles of the stylesheets this entry enqueued, so a
	 *                  dependent entry can order its own CSS after them.
	 */
	private function enqueue_entry( string $handle, string $key, array $style_deps = array(), array $script_deps = array() ): array {
		$manifest = $this->read_manifest();
		if ( null === $manifest || empty( $manifest[ $key ]['file'] ) ) {
			return array();
		}

		$entry    = $manifest[ $key ];
		$dist     = $this->theme_uri . '/dist/';
		$version  = wp_get_theme()->get( 'Version' );
		$file     = ltrim( (string) $entry['file'], '/' );

		if ( str_ends_with( $file, '.css' ) ) {
			wp_enqueue_style( $handle, $dist . $file, $style_deps, $version );
			return array( $handle );
		}

		// JS entry: module script + extracted CSS.
		wp_enqueue_script( $handle, $dist . $file, $script_deps, $version, true );
		$this->module_handles[] = $handle;

		$style_handles = array();
		if ( ! empty( $entry['css'] ) && is_array( $entry['css'] ) ) {
			foreach ( $entry['css'] as $i => $css_file ) {
				$style_handle = $handle . '-' . $i;
				wp_enqueue_style( $style_handle, $dist . ltrim( (string) $css_file, '/' ), $style_deps, $version );
				$style_handles[] = $style_handle;
			}
		}

		return $style_handles;
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
				// Announcement bar pause control (WCAG 2.2.2).
				'marqueePause' => __( 'Pauziraj najave', 'cosypaw' ),
				'marqueePlay'  => __( 'Pusti najave', 'cosypaw' ),
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
				'adding'         => __( 'Dodajem…', 'cosypaw' ),
				'addFailed'      => __( 'Dodavanje nije uspelo — pokušaj ponovo', 'cosypaw' ),
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
