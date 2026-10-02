<?php
/**
 * One card per design, with a colour swatch per product: photo, name, price
 * and an AJAX add-to-cart for whichever colour is picked.
 *
 * Every colour is printed — its own photo, its own product link and its own
 * add-to-cart — and the swatches, plain radio buttons, choose which one shows.
 * The switch is CSS (`:has()` in landing.css), so it works without script and
 * the button can never add a colour other than the one on screen. Where
 * `:has()` is not supported the colours simply stack, each fully usable.
 *
 * Shared by the front page sections and the product line pages.
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type array  $groups ProductLine::groups() output.
 *     @type string $id     Prefix for the radio group names, unique on the page.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cosypaw_groups = (array) ( $args['groups'] ?? array() );
$cosypaw_prefix = sanitize_key( (string) ( $args['id'] ?? 'boja' ) );

if ( ! $cosypaw_groups ) {
	return;
}
?>
<div class="swatch-cards<?php echo 1 === count( $cosypaw_groups ) ? ' swatch-cards--single' : ''; ?>">
	<?php foreach ( $cosypaw_groups as $cosypaw_group ) : ?>
		<?php
		$cosypaw_variants = (array) $cosypaw_group['variants'];
		$cosypaw_name     = $cosypaw_prefix . '-' . sanitize_key( (string) $cosypaw_group['key'] );
		?>
		<div class="swatch-card">
			<?php if ( count( $cosypaw_variants ) > 1 ) : ?>
				<fieldset class="swatch-card__swatches">
					<legend class="swatch-card__legend"><?php esc_html_e( 'Izaberi boju', 'cosypaw' ); ?></legend>
					<?php foreach ( $cosypaw_variants as $cosypaw_i => $cosypaw_v ) : ?>
						<label class="swatch" title="<?php echo esc_attr( $cosypaw_v['color'] ); ?>">
							<input
								type="radio"
								class="swatch__input"
								name="<?php echo esc_attr( $cosypaw_name ); ?>"
								value="<?php echo esc_attr( $cosypaw_v['key'] ); ?>"
								<?php checked( 0, $cosypaw_i ); ?>
							>
							<span class="swatch__dot" style="--swatch: <?php echo esc_attr( $cosypaw_v['swatch'] ); ?>" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php echo esc_html( $cosypaw_v['color'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
			<?php endif; ?>

			<?php foreach ( $cosypaw_variants as $cosypaw_v ) : ?>
				<div class="swatch-card__variant">
					<?php
					// aria-hidden + tabindex="-1": the name below is the same link
					// with the accessible text; the photo would be a second,
					// unlabelled stop.
					?>
					<a class="cloth-card__media swatch-card__media" href="<?php echo esc_url( $cosypaw_v['permalink'] ); ?>" aria-hidden="true" tabindex="-1">
						<?php
						if ( $cosypaw_v['image_id'] > 0 ) {
							// The attachment carries its own descriptive alt, set
							// when the product was created; WordPress supplies the
							// srcset.
							echo wp_get_attachment_image(
								(int) $cosypaw_v['image_id'],
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
					<a class="cloth-card__name swatch-card__name" href="<?php echo esc_url( $cosypaw_v['permalink'] ); ?>"><?php echo esc_html( $cosypaw_v['name'] ); ?></a>
					<span class="cloth-card__price swatch-card__price"><?php echo esc_html( \Theme\Catalog::format_price( (int) $cosypaw_v['price'] ) ); ?></span>
					<a
						href="<?php echo esc_url( $cosypaw_v['add_to_cart_url'] ); ?>"
						class="btn btn--primary cloth-card__buy swatch-card__buy add_to_cart_button ajax_add_to_cart"
						data-product_id="<?php echo esc_attr( (string) (int) $cosypaw_v['id'] ); ?>"
						data-quantity="1"
						rel="nofollow"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Dodaj %s u korpu', 'cosypaw' ), $cosypaw_v['name'] ) ); ?>"
					><?php esc_html_e( 'Dodaj u korpu', 'cosypaw' ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</div>
