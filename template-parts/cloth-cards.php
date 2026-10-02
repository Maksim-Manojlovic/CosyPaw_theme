<?php
/**
 * Products of a line as cards: photo, name, price and an AJAX add-to-cart.
 *
 * Made for the magične krpice — front page section and /magicne-krpe/ — and
 * reused for the long towels. Neither is a towel in the package sense, so
 * there is no package to drop them in; the cloths' own deal is the set of both
 * colours, a wide card under the two.
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type array      $cloths \Theme\Cloths::products() rows.
 *     @type array|null $set    \Theme\Cloths::set() row, or null.
 *     @type string     $layout Optional grid modifier, e.g. "four" for a
 *                              four-across row.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_cloths = (array) ( $args['cloths'] ?? array() );
$cosypaw_set    = is_array( $args['set'] ?? null ) ? $args['set'] : null;
$cosypaw_layout = sanitize_html_class( (string) ( $args['layout'] ?? '' ) );

if ( ! $cosypaw_cloths ) {
	return;
}
?>
<div class="cloths<?php echo '' !== $cosypaw_layout ? ' cloths--' . esc_attr( $cosypaw_layout ) : ''; ?>">
	<?php foreach ( $cosypaw_cloths as $cosypaw_c ) : ?>
		<div class="cloth-card">
			<?php
			// aria-hidden + tabindex="-1": the name below is the same link with
			// the accessible text; the photo would be a second, unlabelled stop.
			?>
			<a class="cloth-card__media" href="<?php echo esc_url( $cosypaw_c['permalink'] ); ?>" aria-hidden="true" tabindex="-1">
				<?php
				if ( $cosypaw_c['image_id'] > 0 ) {
					// The attachment carries its own descriptive alt, set when the
					// product was created; WordPress supplies the srcset.
					echo wp_get_attachment_image(
						(int) $cosypaw_c['image_id'],
						'woocommerce_single',
						false,
						array(
							'class'    => 'cloth-card__img',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width: 560px) calc(100vw - 44px), 360px',
						)
					);
				}
				?>
			</a>
			<div class="cloth-card__body">
				<a class="cloth-card__name" href="<?php echo esc_url( $cosypaw_c['permalink'] ); ?>"><?php echo esc_html( $cosypaw_c['name'] ); ?></a>
				<span class="cloth-card__price"><?php echo esc_html( \Theme\Catalog::format_price( (int) $cosypaw_c['price'] ) ); ?></span>
				<a
					href="<?php echo esc_url( $cosypaw_c['add_to_cart_url'] ); ?>"
					class="btn btn--primary cloth-card__buy add_to_cart_button ajax_add_to_cart"
					data-product_id="<?php echo esc_attr( (string) (int) $cosypaw_c['id'] ); ?>"
					data-quantity="1"
					rel="nofollow"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Dodaj %s u korpu', 'cosypaw' ), $cosypaw_c['name'] ) ); ?>"
				><?php esc_html_e( 'Dodaj u korpu', 'cosypaw' ); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<?php if ( $cosypaw_set ) : ?>
	<div class="cloth-set">
		<a class="cloth-set__media" href="<?php echo esc_url( $cosypaw_set['permalink'] ); ?>" aria-hidden="true" tabindex="-1">
			<?php
			// Both photographs side by side: the set is the two cloths above,
			// and showing one of them would read as a single cloth.
			foreach ( array_slice( array_filter( array_merge( array( (int) $cosypaw_set['image_id'] ), $cosypaw_set['gallery_ids'] ) ), 0, 2 ) as $cosypaw_img ) {
				echo wp_get_attachment_image(
					(int) $cosypaw_img,
					'woocommerce_thumbnail',
					false,
					array(
						'class'    => 'cloth-set__img',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width: 560px) calc(50vw - 40px), 170px',
					)
				);
			}
			?>
		</a>
		<div class="cloth-set__body">
			<span class="cloth-set__badge">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: formatted saving, e.g. "299 RSD". */
						__( 'Uštedi %s', 'cosypaw' ),
						\Theme\Catalog::format_price( (int) $cosypaw_set['saving'] )
					)
				);
				?>
			</span>
			<a class="cloth-card__name" href="<?php echo esc_url( $cosypaw_set['permalink'] ); ?>"><?php echo esc_html( $cosypaw_set['name'] ); ?></a>
			<span class="cloth-set__prices">
				<span class="cloth-card__price"><?php echo esc_html( \Theme\Catalog::format_price( (int) $cosypaw_set['price'] ) ); ?></span>
				<del class="cloth-set__old">
					<span class="screen-reader-text"><?php esc_html_e( 'Pojedinačno:', 'cosypaw' ); ?></span>
					<?php echo esc_html( \Theme\Catalog::format_price( (int) $cosypaw_set['separately'] ) ); ?>
				</del>
			</span>
			<a
				href="<?php echo esc_url( $cosypaw_set['add_to_cart_url'] ); ?>"
				class="btn btn--primary cloth-card__buy add_to_cart_button ajax_add_to_cart"
				data-product_id="<?php echo esc_attr( (string) (int) $cosypaw_set['id'] ); ?>"
				data-quantity="1"
				rel="nofollow"
				aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Dodaj %s u korpu', 'cosypaw' ), $cosypaw_set['name'] ) ); ?>"
			><?php esc_html_e( 'Dodaj set u korpu', 'cosypaw' ); ?></a>
		</div>
	</div>
<?php endif; ?>
