<?php
/**
 * One motif card: photo, name, price, the package ladder and the two ways to
 * buy it (into a package, or one on its own).
 *
 * Shared by the front-page gallery and the motif collection page, so a card
 * reads and buys the same wherever it is met.
 *
 * @package CosyPaw
 *
 * @var array $args {
 *     @type array      $motif  Catalogue row (see \Theme\Catalog::products()).
 *     @type array|null $ladder Cheapest per-piece package, or null.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p              = (array) ( $args['motif'] ?? array() );
$cosypaw_ladder = $args['ladder'] ?? null;

if ( ! $p ) {
	return;
}

/* translators: %s: motif name. */
$item_label = sprintf( __( '%s • 1 kom', 'cosypaw' ), $p['name'] );

// Only a seeded motif has a product page behind it. Without one
// the card stays exactly what it was: a picture and a buy button.
$cosypaw_link = isset( $p['permalink'] ) ? (string) $p['permalink'] : '';
?>
<div class="motif-card">
	<?php
	if ( '' !== $cosypaw_link ) :
		// aria-hidden + tabindex="-1": the name below links to the
		// same place and carries the accessible text, so the image
		// would only be a second, unlabelled stop on the way there.
		?>
		<a class="motif-card__link" href="<?php echo esc_url( $cosypaw_link ); ?>" aria-hidden="true" tabindex="-1">
	<?php endif; ?>
	<?php
	// alt="" — the motif name is printed as text directly below.
	// The srcset omits image_sm on purpose: it is a 1:1 crop while
	// the others are 3:4, and srcset candidates have to be the same
	// picture at different sizes or the crop shifts with the
	// viewport.
	?>
	<img
		class="motif-card__img"
		src="<?php echo esc_url( $p['image_md'] ); ?>"
		srcset="<?php echo esc_attr( \Theme\Assets::motif_srcset( $p ) ); ?>"
		sizes="<?php echo esc_attr( \Theme\Assets::GRID_SIZES ); ?>"
		width="600"
		height="800"
		alt=""
		loading="lazy"
		decoding="async"
	>
	<?php if ( '' !== $cosypaw_link ) : ?>
		</a>
	<?php endif; ?>
	<div class="motif-card__row">
		<div class="motif-card__meta">
			<div class="motif-name">
				<?php if ( '' !== $cosypaw_link ) : ?>
					<a class="motif-name__link" href="<?php echo esc_url( $cosypaw_link ); ?>"><?php echo esc_html( $p['name'] ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $p['name'] ); ?>
				<?php endif; ?>
			</div>
			<div class="motif-price">
				<span><?php echo esc_html( \Theme\Catalog::format_price( (int) $p['price'] ) ); ?></span>
				<?php
				// The same motif, priced as part of the best package.
				// Only where that price actually undercuts this
				// card's own — motifs are priced individually in
				// wp-admin, so the comparison is made per card and
				// not once for the grid.
				if ( null !== $cosypaw_ladder && (int) $cosypaw_ladder['per'] < (int) $p['price'] ) :
					?>
					<button
						type="button"
						class="motif-ladder"
						data-add-to-bundle
						data-motif-id="<?php echo esc_attr( $p['id'] ); ?>"
						data-package="<?php echo esc_attr( (string) $cosypaw_ladder['id'] ); ?>"
						aria-label="<?php
						echo esc_attr(
							sprintf(
								/* translators: 1: towels in the package, 2: formatted per-piece price, 3: motif name. */
								__( 'Napravi paket od %1$d peškirića po %2$s, počni sa motivom %3$s', 'cosypaw' ),
								(int) $cosypaw_ladder['qty'],
								\Theme\Catalog::format_price( (int) $cosypaw_ladder['per'] ),
								$p['name']
							)
						);
						?>"
					>
						<?php
						printf(
							/* translators: 1: towels in the package, 2: formatted per-piece price. */
							esc_html__( '%1$d kom · %2$s / kom', 'cosypaw' ),
							(int) $cosypaw_ladder['qty'],
							esc_html( \Theme\Catalog::format_price( (int) $cosypaw_ladder['per'] ) )
						);
						?>
					</button>
				<?php endif; ?>
			</div>
			<?php
			// Buying one is still here, demoted to a text link. The
			// card's main action now sends the motif to the builder:
			// a single towel is the cheapest thing the shop sells and
			// the only one that pays for its own delivery, so it is a
			// poor default for a click made at peak enthusiasm.
			if ( ! empty( $p['product_id'] ) ) :
				?>
				<a
					href="<?php echo esc_url( $p['add_to_cart_url'] ); ?>"
					class="motif-single add_to_cart_button ajax_add_to_cart"
					data-product_id="<?php echo esc_attr( (string) (int) $p['product_id'] ); ?>"
					data-quantity="1"
					rel="nofollow"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: motif name. */ __( 'Kupi %s, 1 kom', 'cosypaw' ), $p['name'] ) ); ?>"
				><?php esc_html_e( 'Kupi 1 kom', 'cosypaw' ); ?></a>
			<?php else : ?>
				<button
					type="button"
					class="motif-single"
					data-cart-add
					data-name="<?php echo esc_attr( $item_label ); ?>"
					data-price="<?php echo esc_attr( (string) (int) $p['price'] ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: motif name. */ __( 'Kupi %s, 1 kom', 'cosypaw' ), $p['name'] ) ); ?>"
				><?php esc_html_e( 'Kupi 1 kom', 'cosypaw' ); ?></button>
			<?php endif; ?>
		</div>
		<?php
		// Drops the motif straight into a builder slot further down
		// the page — see BundleBuilder.addMotifFromGallery().
		?>
		<button
			type="button"
			class="motif-add"
			data-add-to-bundle
			data-motif-id="<?php echo esc_attr( $p['id'] ); ?>"
			aria-label="<?php echo esc_attr( sprintf( /* translators: %s: motif name. */ __( 'Dodaj %s u paket', 'cosypaw' ), $p['name'] ) ); ?>"
		>
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
			<?php esc_html_e( 'U paket', 'cosypaw' ); ?>
		</button>
	</div>
</div>
