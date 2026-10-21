<?php
/**
 * Storefront — the catalogue data every selling page renders from.
 *
 * The front page used to work all of this out inline, at the top of its own
 * template. The motif collection page sells the same towels through the same
 * cards and the same package builder, and two copies of "which package does the
 * builder open on" or "what is the cheapest per-piece price" would drift apart
 * the first time either was touched. Both templates ask here instead.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Storefront.
 */
final class Storefront {

	/**
	 * Everything the motif cards and the package builder need.
	 *
	 * @param Catalog $catalog Catalogue data object.
	 * @return array{
	 *     products: array<int,array<string,mixed>>,
	 *     from_price: int,
	 *     packages: array<int,array<string,mixed>>,
	 *     default_pkg: string,
	 *     selected: array<string,mixed>,
	 *     ladder: array<string,mixed>|null,
	 *     free_min: int
	 * }
	 */
	public static function context( Catalog $catalog ): array {
		/*
		 * Motifs whose product has been retired (trashed or unpublished) keep
		 * their row in the Catalog so past orders can still resolve their
		 * name, but they must not be offered for sale. Rows carry no
		 * 'available' key at all when WooCommerce is inactive or the motif is
		 * unmapped — the demo catalog stays fully browsable.
		 */
		$products = array_values( array_filter( $catalog->products(), array( self::class, 'in_stock' ) ) );

		/*
		 * A sold-out motif is still shown, marked "Rasprodato", but after
		 * everything that can be bought: the grid opens on towels a click can
		 * put in the cart, and the collapsed front-page grid hides the rest.
		 * The sort is stable, so each half keeps the catalogue's order.
		 */
		$on_sale  = array_values( array_filter( $products, array( self::class, 'sellable' ) ) );
		$products = array_merge(
			$on_sale,
			array_values( array_filter( $products, static fn( array $row ): bool => ! self::sellable( $row ) ) )
		);

		// Motifs can be priced individually in wp-admin, so the headline price
		// is the cheapest one actually on sale rather than a catalog-wide figure.
		$from_price = $on_sale
			? (int) min( array_column( $on_sale, 'price' ) )
			: Catalog::UNIT_PRICE;

		$packages    = $catalog->packages();
		$default_pkg = $catalog->default_package();

		/*
		 * The cheapest per-piece price any package reaches, printed beside the
		 * single price on every motif card. The comparison is the argument, so
		 * it is made where the price is read. Derived, like every other offer
		 * claim: null while no package undercuts the singles, and the ladder
		 * then does not print at all.
		 */
		$ladder = null;
		foreach ( $packages as $pkg ) {
			if ( (int) ( $pkg['qty'] ?? 0 ) < 2 || (int) ( $pkg['per'] ?? 0 ) < 1 ) {
				continue;
			}

			if ( null === $ladder || (int) $pkg['per'] < (int) $ladder['per'] ) {
				$ladder = $pkg;
			}
		}

		// The package the builder opens on; every offer claim is read off it.
		$selected = $packages[0];
		foreach ( $packages as $pkg ) {
			if ( $pkg['id'] === $default_pkg ) {
				$selected = $pkg;
				break;
			}
		}

		return array(
			'products'    => $products,
			'from_price'  => $from_price,
			'packages'    => $packages,
			'default_pkg' => $default_pkg,
			'selected'    => $selected,
			'ladder'      => $ladder,
			// Read once per page: the hero, the builder and the gift banner all
			// quote it, and they have to agree.
			'free_min'    => Catalog::free_shipping_min(),
		);
	}

	/**
	 * Whether a catalogue row is on sale.
	 *
	 * @param array<string,mixed> $row Catalogue row.
	 * @return bool
	 */
	public static function in_stock( array $row ): bool {
		return (bool) ( $row['available'] ?? true );
	}

	/**
	 * Whether a catalogue row can be put in the cart or the builder: on sale
	 * and not sold out. A sold-out row is still shown, marked "Rasprodato".
	 *
	 * @param array<string,mixed> $row Catalogue row.
	 * @return bool
	 */
	public static function sellable( array $row ): bool {
		return self::in_stock( $row ) && empty( $row['sold_out'] );
	}

	/**
	 * Alt text for a motif photo that stands on its own.
	 *
	 * A bare "Zeka" told a search engine nothing about what the picture is;
	 * this names the product and the motif. Only for images whose name is not
	 * printed beside them — gallery and builder tiles keep alt="" because
	 * their caption already says it.
	 *
	 * @param array<string,mixed> $row Catalogue row.
	 * @return string
	 */
	public static function motif_alt( array $row ): string {
		return sprintf(
			/* translators: %s: motif name, e.g. "Zeka". */
			__( 'Dečiji peškir od mikrofibera, oblik %s', 'cosypaw' ),
			(string) ( $row['name'] ?? '' )
		);
	}

	/**
	 * Sort motifs into the towel subcategories.
	 *
	 * A motif filed under more than one subcategory is shown in the first only:
	 * the same towel twice on one page reads as a mistake. Motifs in none —
	 * every towel, until the shop files them in wp-admin, and any motif with no
	 * WooCommerce product behind it — are returned under the empty key, so the
	 * page can still show them rather than lose them. That group comes last,
	 * unless $order names the empty key itself and so says where it goes.
	 *
	 * @param array<int,array<string,mixed>> $products Catalogue rows.
	 * @param string[]                       $order    Subcategory slugs in display order, '' for the unfiled group; defaults to ProductCategories order.
	 * @return array<string,array<int,array<string,mixed>>> Subcategory slug (or '') => rows. Empty groups omitted.
	 */
	public static function by_subcategory( array $products, array $order = array() ): array {
		$slugs  = $order ? array_values( $order ) : array_keys( ProductCategories::SUBCATEGORIES );
		$groups = array_fill_keys( array_merge( $slugs, array( '' ) ), array() );

		// The empty key is a place in the order, not a term to look for:
		// has_term() with no term asks "is it filed under anything at all".
		$slugs = array_values( array_diff( $slugs, array( '' ) ) );

		foreach ( $products as $row ) {
			$home = '';
			$id   = (int) ( $row['product_id'] ?? 0 );

			if ( $id > 0 ) {
				foreach ( $slugs as $slug ) {
					if ( has_term( $slug, 'product_cat', $id ) ) {
						$home = $slug;
						break;
					}
				}
			}

			$groups[ $home ][] = $row;
		}

		return array_filter( $groups );
	}
}
