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

		$created = 0;

		// Motifs.
		$product_map = (array) get_option( WooCommerce::PRODUCT_MAP_OPTION, array() );
		foreach ( $this->catalog->products() as $motif ) {
			if ( isset( $product_map[ $motif['id'] ] ) && get_post( (int) $product_map[ $motif['id'] ] ) ) {
				// Already created — ensure its image has alt/caption/description.
				$this->backfill_image_meta( (int) $product_map[ $motif['id'] ], $motif['name'] );
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

		return $created;
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
	 * Ensure an already-created product's featured image carries image meta.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $name       Product name.
	 * @return void
	 */
	private function backfill_image_meta( int $product_id, string $name ): void {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		if ( ! $product ) {
			return;
		}
		$image_id = (int) $product->get_image_id();
		if ( $image_id ) {
			$this->set_image_meta( $image_id, $name );
		}
	}

	/**
	 * Sideload an image that lives in the theme's /assets directory.
	 *
	 * @param string $image_url Image URL under the theme directory.
	 * @param int    $parent_id Product post ID to attach to.
	 * @param string $alt       Alt/title/caption/description text.
	 * @return int Attachment ID, or 0 on failure.
	 */
	private function sideload_local_image( string $image_url, int $parent_id, string $alt = '' ): int {
		$path = get_template_directory() . '/assets/' . wp_basename( $image_url );
		if ( ! is_readable( $path ) ) {
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
