<?php
/**
 * ProductSeeder — creates WooCommerce products from the Catalog data.
 *
 * Adds a Tools → "CosyPaw Seeder" admin page. On submit (nonce + capability
 * guarded) it creates a simple product for each towel motif and package that
 * does not already exist, sideloads the matching image from /assets, and stores
 * the motif/package id → product id maps in options. Idempotent.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProductSeeder.
 */
final class ProductSeeder {

	private const NONCE_ACTION = 'cosypaw_seed_products';

	/**
	 * Catalog data source.
	 *
	 * @var Catalog
	 */
	private Catalog $catalog;

	/**
	 * Constructor — registers the admin page and form handler.
	 *
	 * @param Catalog $catalog Catalog data source.
	 */
	public function __construct( Catalog $catalog ) {
		$this->catalog = $catalog;

		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_post_' . self::NONCE_ACTION, array( $this, 'handle' ) );

		// Headless: `wp cosypaw seed`.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command(
				'cosypaw seed',
				function (): void {
					if ( ! $this->avif_supported() ) {
						\WP_CLI::warning( 'AVIF is not supported here — product images will be missing or thumbnail-less. Needs WP 6.5+ and GD/Imagick with AVIF.' );
					}
					$created = $this->seed();
					\WP_CLI::success( sprintf( 'CosyPaw: %d product(s) created.', $created ) );
				}
			);
		}
	}

	/**
	 * Register the Tools submenu page.
	 *
	 * @return void
	 */
	public function register_page(): void {
		add_management_page(
			__( 'CosyPaw Seeder', 'cosypaw' ),
			__( 'CosyPaw Seeder', 'cosypaw' ),
			'manage_options',
			'cosypaw-seeder',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the seeder admin page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$product_map = (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() );
		$package_map = (array) get_option( WooCommerce::PACKAGE_MAP_OPTION, array() );
		$seeded      = isset( $_GET['seeded'] ) ? absint( wp_unslash( $_GET['seeded'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'CosyPaw Seeder', 'cosypaw' ); ?></h1>

			<?php if ( null !== $seeded ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php
						printf(
							/* translators: %d: number of products created. */
							esc_html__( 'Done — %d new product(s) created.', 'cosypaw' ),
							(int) $seeded
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( ! $this->avif_supported() ) : ?>
				<div class="notice notice-warning">
					<p>
						<?php
						esc_html_e(
							'This site cannot process AVIF images, and the CosyPaw motif photography ships in that format. Seeding will create the products but their images will be missing or thumbnail-less. AVIF needs WordPress 6.5 or newer plus GD/Imagick built with AVIF support — ask your host to enable it, then run the seeder again.',
							'cosypaw'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<p>
				<?php
				printf(
					/* translators: 1: motif products mapped, 2: package products mapped. */
					esc_html__( 'Currently mapped: %1$d motif products, %2$d package products.', 'cosypaw' ),
					count( $product_map ),
					count( $package_map )
				);
				?>
			</p>

			<p>
				<?php
				$cod  = (array) get_option( 'woocommerce_cod_settings', array() );
				$zone = (int) get_option( CheckoutSetup::ZONE_OPTION, 0 );

				esc_html_e( 'Checkout:', 'cosypaw' );
				echo ' ';
				echo empty( $cod['enabled'] ) || 'yes' !== $cod['enabled']
					? esc_html__( 'cash on delivery is OFF', 'cosypaw' )
					: esc_html__( 'cash on delivery is on', 'cosypaw' );
				echo ', ';
				echo $zone > 0
					? esc_html__( 'the Serbia shipping zone exists', 'cosypaw' )
					: esc_html__( 'there is NO shipping zone', 'cosypaw' );
				echo '.';
				?>
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::NONCE_ACTION ); ?>" />
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<p>
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Create / update CosyPaw products', 'cosypaw' ); ?>
					</button>
				</p>
				<p class="description">
					<?php esc_html_e( 'Safe to run repeatedly — existing products are skipped.', 'cosypaw' ); ?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle the seed form submission.
	 *
	 * @return void
	 */
	public function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'cosypaw' ) );
		}
		check_admin_referer( self::NONCE_ACTION );

		if ( ! class_exists( '\WC_Product_Simple' ) ) {
			wp_die( esc_html__( 'WooCommerce is not active.', 'cosypaw' ) );
		}

		$created = $this->seed();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'   => 'cosypaw-seeder',
					'seeded' => $created,
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	/**
	 * Create every missing motif + package product. Idempotent.
	 *
	 * @return int Number of products created this run.
	 */
	public function seed(): int {
		if ( ! class_exists( '\WC_Product_Simple' ) ) {
			return 0;
		}

		$passthrough = $this->suppress_theme_translations();

		try {
			$created = 0;

			// Motifs.
			$product_map = (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() );
			foreach ( $this->catalog->products() as $motif ) {
				if ( isset( $product_map[ $motif['id'] ] ) && get_post( (int) $product_map[ $motif['id'] ] ) ) {
					// Already created — re-attach the image if it went missing,
					// and make sure it carries its alt text either way.
					$this->backfill_image_meta( (int) $product_map[ $motif['id'] ], $motif['name'], $motif['image'] );
					continue;
				}
				$pid = $this->create_product( $motif['name'], Catalog::UNIT_PRICE, $motif['image'] );
				if ( $pid ) {
					$product_map[ $motif['id'] ] = $pid;
					++$created;
				}
			}
			update_option( WooCommerce::PRODUCT_MAP_OPTION, $product_map );

			// Packages.
			$package_map = (array) get_option( WooCommerce::PACKAGE_MAP_OPTION, array() );
			foreach ( $this->catalog->packages() as $pkg ) {
				if ( isset( $package_map[ $pkg['id'] ] ) && get_post( (int) $package_map[ $pkg['id'] ] ) ) {
					continue;
				}
				$pid = $this->create_product( $pkg['name'], (int) $pkg['price'], '' );
				if ( $pid ) {
					$package_map[ $pkg['id'] ] = $pid;
					++$created;
				}
			}
			update_option( WooCommerce::PACKAGE_MAP_OPTION, $package_map );

			// Products nobody can pay for are not a live shop. WooCommerce
			// starts with every gateway off and no shipping zone, so going
			// live means configuring both — see CheckoutSetup. Idempotent.
			CheckoutSetup::configure();

			$this->seed_site_logo();
		} finally {
			remove_filter( 'gettext', $passthrough, 99 );
		}

		return $created;
	}

	/**
	 * Make Catalog strings resolve to their untranslated msgid for the duration
	 * of a seed run.
	 *
	 * A seeded product's post_title doubles as the msgid that
	 * WooCommerce::translate_product_title() feeds back through __() on every
	 * request, so the title must be stored in the source (Serbian) language. The
	 * Catalog names are already gettext output, which means seeding from an
	 * English or Russian admin would otherwise freeze the *translation* into the
	 * database — where it matches no msgid, and the product then shows that one
	 * language to every visitor. Suppressing the theme's own translations while
	 * the names are read makes the stored title locale-independent.
	 *
	 * Only the theme text domain is affected, and only until the filter is
	 * removed in seed()'s finally block.
	 *
	 * @return callable The registered filter, for removal.
	 */
	private function suppress_theme_translations(): callable {
		$passthrough = static function ( $translation, $text, $domain ) {
			return COSYPAW_TEXT_DOMAIN === $domain ? $text : $translation;
		};

		add_filter( 'gettext', $passthrough, 99, 3 );

		return $passthrough;
	}

	/**
	 * Create a simple, published WooCommerce product.
	 *
	 * @param string $name      Product name.
	 * @param int    $price     Regular price (RSD).
	 * @param string $image_url Source image URL (mapped to a local /assets path).
	 * @return int Product ID, or 0 on failure.
	 */
	private function create_product( string $name, int $price, string $image_url ): int {
		$product = new \WC_Product_Simple();
		$product->set_name( $name );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_regular_price( (string) $price );
		$product->set_sold_individually( false );
		$product_id = (int) $product->save();

		if ( ! $product_id ) {
			return 0;
		}

		if ( '' !== $image_url ) {
			$attachment_id = $this->sideload_local_image( $image_url, $product_id, $name );
			if ( $attachment_id ) {
				$product->set_image_id( $attachment_id );
				$product->save();
			}
		}

		return $product_id;
	}

	/**
	 * Set the image's alt text (accessibility) and admin title.
	 *
	 * Caption (post_excerpt) and description (post_content) are intentionally
	 * left blank — caption can render visibly on the front end, and duplicating
	 * the product name there adds nothing.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $text          Descriptive text (the product name).
	 * @return void
	 */
	private function set_image_meta( int $attachment_id, string $text ): void {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $text );
		wp_update_post(
			array(
				'ID'         => $attachment_id,
				'post_title' => $text,
			)
		);
	}

	/**
	 * Ensure an already-created product still has its featured image, and that
	 * the image carries its meta.
	 *
	 * A product whose image went missing — deleted from the media library, or
	 * lost in a migration — used to fall straight through this method, because
	 * it only had a branch for the case where an image was already there. The
	 * product itself is in the map, so seed() skips creating it, which left the
	 * shop grid showing WooCommerce's grey placeholder with no way to repair it
	 * short of re-attaching every image by hand. Re-sideloading here is what
	 * makes the seeder able to fix that, and it is still idempotent: an intact
	 * image is left exactly where it is.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $name       Product name.
	 * @param string $image_url  Source image under the theme directory.
	 * @return void
	 */
	private function backfill_image_meta( int $product_id, string $name, string $image_url = '' ): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		if ( ! $product ) {
			return;
		}

		$image_id = (int) $product->get_image_id();

		if ( $this->attachment_is_intact( $image_id ) ) {
			$this->set_image_meta( $image_id, $name );

			return;
		}

		if ( '' === $image_url ) {
			return;
		}

		$attachment_id = $this->sideload_local_image( $image_url, $product_id, $name );
		if ( $attachment_id ) {
			$product->set_image_id( $attachment_id );
			$product->save();
		}
	}

	/**
	 * Whether an attachment id still resolves to a file on disk.
	 *
	 * The id alone is not enough: WooCommerce keeps _thumbnail_id whether or not
	 * the attachment behind it survived, so a deleted image leaves a product
	 * pointing at a post that is gone, or at a post whose file is.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private function attachment_is_intact( int $attachment_id ): bool {
		if ( $attachment_id < 1 || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}

		$file = get_attached_file( $attachment_id );

		return is_string( $file ) && '' !== $file && file_exists( $file );
	}

	/**
	 * Whether this install can actually ingest the AVIF motif images.
	 *
	 * The motif photography ships as AVIF. WordPress only allows AVIF uploads
	 * from 6.5 on, and only generates the intermediate thumbnail sizes when the
	 * image editor (GD or Imagick) was compiled with AVIF support — which many
	 * shared hosts still lack. Without it a sideload either fails outright or
	 * lands a full-size original with no thumbnails, which reads as "the shop
	 * images are broken". Checked up front so the seeder can say so plainly
	 * instead of silently producing image-less products.
	 *
	 * @return bool
	 */
	public function avif_supported(): bool {
		if ( ! function_exists( 'wp_image_editor_supports' ) ) {
			return false;
		}

		$allowed = wp_get_mime_types();

		return in_array( 'image/avif', (array) $allowed, true )
			&& wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) );
	}

	/**
	 * Resolve a theme-hosted image URL to its path on disk.
	 *
	 * Motif images live in subdirectories (assets/motifs/), so the URL's path
	 * relative to the theme root is preserved rather than flattened. Because the
	 * URL can originate from the `cosypaw_catalog_products` filter, the resolved
	 * path is confirmed to stay inside the theme directory before it is used.
	 *
	 * @param string $image_url Image URL under the theme directory.
	 * @return string Absolute readable path, or '' if it does not resolve.
	 */
	private function resolve_theme_path( string $image_url ): string {
		$theme_uri = get_template_directory_uri();
		$theme_dir = get_template_directory();

		if ( 0 === strpos( $image_url, $theme_uri ) ) {
			$relative = ltrim( substr( $image_url, strlen( $theme_uri ) ), '/' );
		} else {
			// Not a theme URL — fall back to a bare filename under /assets.
			$relative = 'assets/' . wp_basename( $image_url );
		}

		$path = realpath( $theme_dir . '/' . $relative );
		$root = realpath( $theme_dir );

		if ( false === $path || false === $root || 0 !== strpos( $path, $root . DIRECTORY_SEPARATOR ) ) {
			return '';
		}

		return is_readable( $path ) ? $path : '';
	}

	/**
	 * Put the brand badge into the media library and point the Customizer's
	 * logo and the site icon at it. Idempotent.
	 *
	 * The header renders the badge straight from /assets, so this is not what
	 * makes the logo appear on the page. It is what gives the rest of
	 * WordPress a logo to use: the browser-tab icon, the Organization schema in
	 * Theme\Seo, and anything else reading get_theme_mod('custom_logo').
	 *
	 * The PNG is sideloaded rather than the AVIF the header uses — the site
	 * icon is resized by WordPress into favicon dimensions, and an install
	 * without AVIF support would silently produce no icon at all.
	 *
	 * @return void
	 */
	private function seed_site_logo(): void {
		if ( (int) get_theme_mod( 'custom_logo' ) > 0 || (int) get_option( 'site_icon' ) > 0 ) {
			return;
		}

		$id = $this->sideload_local_image(
			get_template_directory_uri() . '/assets/logo-512.png',
			0,
			get_bloginfo( 'name' )
		);

		if ( $id < 1 ) {
			return;
		}

		set_theme_mod( 'custom_logo', $id );
		update_option( 'site_icon', $id );
		$this->generate_site_icon_sizes( $id );
	}

	/**
	 * Add the favicon-sized crops to an attachment being used as the site icon.
	 *
	 * WordPress only produces the 32/180/192/270/512 site_icon-* sizes inside
	 * the Customizer's icon control, which is a cropper this seeder never goes
	 * through. Without them get_site_icon_url() falls back to the source image,
	 * so a browser tab would pull the full 512px PNG for a 32px favicon.
	 *
	 * @param int $attachment_id Attachment used as the site icon.
	 * @return void
	 */
	private function generate_site_icon_sizes( int $attachment_id ): void {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-site-icon.php';

		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! class_exists( '\WP_Site_Icon' ) ) {
			return;
		}

		$site_icon = new \WP_Site_Icon();
		add_filter( 'intermediate_image_sizes_advanced', array( $site_icon, 'additional_sizes' ) );

		try {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );
		} finally {
			remove_filter( 'intermediate_image_sizes_advanced', array( $site_icon, 'additional_sizes' ) );
		}
	}

	/**
	 * Sideload an image that lives in the theme directory.
	 *
	 * @param string $image_url Image URL under the theme directory.
	 * @param int    $parent_id Product post ID to attach to.
	 * @param string $alt       Alt/title/caption/description text.
	 * @return int Attachment ID, or 0 on failure.
	 */
	private function sideload_local_image( string $image_url, int $parent_id, string $alt = '' ): int {
		$path = $this->resolve_theme_path( $image_url );
		if ( '' === $path ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = wp_tempnam( $path );
		if ( ! $tmp || ! copy( $path, $tmp ) ) {
			return 0;
		}

		$file_array = array(
			'name'     => wp_basename( $path ),
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload( $file_array, $parent_id );

		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			return 0;
		}

		if ( '' !== $alt ) {
			$this->set_image_meta( (int) $attachment_id, $alt );
		}

		return (int) $attachment_id;
	}
}
