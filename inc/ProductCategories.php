<?php
/**
 * ProductCategories — the towel category tree.
 *
 * The shop began with one flat category: every towel sat in "Peškirići" and a
 * shopper looking for an animal scrolled past the pastries to find one. This
 * creates the subcategories under it. Which towel goes where is a decision the
 * shop makes by hand on the product screen — nothing here files anything.
 *
 * The parent term is never removed from a product. WooCommerce::register_towel()
 * decides that a product is a towel by has_term( TOWEL_CATEGORY ), which does
 * not look at child terms — a towel moved down into a subcategory alone would
 * stop being part of the catalogue, and would vanish from the landing page, the
 * bundle builder and the pricing. Tick the subcategory in wp-admin, and leave
 * "Peškirići" ticked with it.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProductCategories.
 */
final class ProductCategories {

	/**
	 * Taxonomy the tree lives in.
	 *
	 * @var string
	 */
	private const TAXONOMY = 'product_cat';

	/**
	 * Subcategory slug => name, all children of WooCommerce::TOWEL_CATEGORY.
	 *
	 * The names are Serbian on purpose. A term name is a database value, not a
	 * msgid: the shop's existing categories ("Peškirići", "Paketi") are stored
	 * in the source language and WooCommerce prints them as they are, so these
	 * match rather than inventing a second convention.
	 *
	 * @var array<string,string>
	 */
	public const SUBCATEGORIES = array(
		'peskirici-u-torbi' => 'Peškirići u torbi',
		'zivotinjice'       => 'Životinjice',
		'zalogajcici'       => 'Zalogajčići',
		'cvetici-i-listici' => 'Cvetići i listići',
	);

	/**
	 * Create any missing subcategory, and the parent they hang from.
	 *
	 * Idempotent: a term that already exists is left exactly as it is, name,
	 * description, slug and all. Re-running only fills in what is missing —
	 * including a single subcategory somebody deleted by accident.
	 *
	 * @return int Number of terms created this run.
	 */
	public function ensure_terms(): int {
		$parent = $this->parent_term_id();
		if ( $parent < 1 ) {
			return 0;
		}

		$created = 0;
		foreach ( self::SUBCATEGORIES as $slug => $name ) {
			if ( get_term_by( 'slug', $slug, self::TAXONOMY ) ) {
				continue;
			}

			$term = wp_insert_term(
				$name,
				self::TAXONOMY,
				array(
					'slug'   => $slug,
					'parent' => $parent,
				)
			);

			if ( ! is_wp_error( $term ) ) {
				++$created;
			}
		}

		return $created;
	}

	/**
	 * Which subcategories exist right now, as slug => term id.
	 *
	 * @return array<string,int>
	 */
	public function existing_terms(): array {
		$out = array();
		foreach ( array_keys( self::SUBCATEGORIES ) as $slug ) {
			$term = get_term_by( 'slug', $slug, self::TAXONOMY );
			if ( $term ) {
				$out[ $slug ] = (int) $term->term_id;
			}
		}

		return $out;
	}

	/**
	 * Term id of the towel category, creating it when the shop has none.
	 *
	 * A shop without it is a shop where no product can be a towel, so the tree
	 * has nothing to hang from and there is no sense in creating the children
	 * as orphans.
	 *
	 * @return int
	 */
	private function parent_term_id(): int {
		$term = get_term_by( 'slug', WooCommerce::TOWEL_CATEGORY, self::TAXONOMY );
		if ( $term ) {
			return (int) $term->term_id;
		}

		$created = wp_insert_term( 'Peškirići', self::TAXONOMY, array( 'slug' => WooCommerce::TOWEL_CATEGORY ) );

		return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
	}
}
