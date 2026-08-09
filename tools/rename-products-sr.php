<?php
/**
 * One-off: restore Serbian names on the seeded CosyPaw WooCommerce products.
 *
 * ProductSeeder builds product names from Catalog, whose strings pass through
 * __(). When the seeder runs while the admin locale is en_US (or ru_RU), the
 * translated name is frozen into post_title, and re-running the seeder will not
 * fix it — the seeder skips ids that are already mapped. This script rewrites
 * the titles from a hardcoded sr table, so it does not depend on the current
 * locale or on the .mo files being loaded.
 *
 * Products are resolved through the seeder's own id maps
 * (cosypaw_product_map / cosypaw_package_map), never by guessing titles.
 *
 * Usage (dry run — prints what would change, writes nothing):
 *   php tools/rename-products-sr.php --wp=C:/xamppp/htdocs/cosypaw
 *
 * Apply:
 *   php tools/rename-products-sr.php --wp=C:/xamppp/htdocs/cosypaw --apply
 *
 * Options:
 *   --wp=PATH   WordPress root holding wp-load.php. Defaults to $WP_ROOT, then
 *               to walking up from this file (works when the theme sits inside
 *               the install).
 *   --apply     Actually write. Without it the script only reports.
 *   --slugs     Also rewrite post_name from the Serbian title (zirafa, tresnja,
 *               …). Changes product permalinks — leave off on a live shop.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	exit( 'CLI only.' );
}

/**
 * Serbian product names, keyed by the Catalog motif / package id.
 * Mirrors the msgids in inc/Catalog.php.
 */
const CP_MOTIF_NAMES = array(
	'zirafa'   => 'Žirafa',
	'koala'    => 'Koala',
	'pingvin'  => 'Pingvin',
	'sova'     => 'Sova',
	'panda'    => 'Panda',
	'meda'     => 'Meda',
	'kapibara' => 'Kapibara',
	'maca'     => 'Maca',
	'kucence'  => 'Kucence',
	'zeka'     => 'Zeka',
	'avokado'  => 'Avokado',
	'ananas'   => 'Ananas',
	'tresnja'  => 'Trešnja',
	'sir'      => 'Sir',
	'krofna'   => 'Krofna',
	'biskvit'  => 'Biskvit',
	'keks'     => 'Čokoladni keks',
	'tost'     => 'Tost',
	'lala'     => 'Lala',
	'list'     => 'Javorov list',
);

const CP_PACKAGE_NAMES = array(
	'solo' => 'Pojedinačno',
	'duo'  => 'Duo paket',
	'trio' => 'Trio paket',
);

/**
 * Read a --key=value / --flag argument.
 *
 * @param string $name    Option name without the leading dashes.
 * @param bool   $is_flag True for a valueless flag.
 * @return string|bool
 */
function cp_arg( string $name, bool $is_flag = false ) {
	foreach ( array_slice( $GLOBALS['argv'], 1 ) as $arg ) {
		if ( $is_flag && "--{$name}" === $arg ) {
			return true;
		}
		if ( ! $is_flag && 0 === strpos( $arg, "--{$name}=" ) ) {
			return substr( $arg, strlen( $name ) + 3 );
		}
	}

	return $is_flag ? false : '';
}

/**
 * Locate wp-load.php.
 *
 * @return string Absolute path, or '' when not found.
 */
function cp_find_wp_load(): string {
	$candidates = array();

	$explicit = (string) cp_arg( 'wp' );
	if ( '' !== $explicit ) {
		$candidates[] = rtrim( str_replace( '\\', '/', $explicit ), '/' ) . '/wp-load.php';
	}

	$env = (string) getenv( 'WP_ROOT' );
	if ( '' !== $env ) {
		$candidates[] = rtrim( str_replace( '\\', '/', $env ), '/' ) . '/wp-load.php';
	}

	$dir = str_replace( '\\', '/', __DIR__ );
	for ( $i = 0; $i < 8; $i++ ) {
		$candidates[] = $dir . '/wp-load.php';
		$parent       = dirname( $dir );
		if ( $parent === $dir ) {
			break;
		}
		$dir = $parent;
	}

	foreach ( $candidates as $path ) {
		if ( is_readable( $path ) ) {
			return $path;
		}
	}

	return '';
}

$wp_load = cp_find_wp_load();
if ( '' === $wp_load ) {
	fwrite( STDERR, "Could not find wp-load.php. Pass --wp=/path/to/wordpress\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_load;

if ( ! function_exists( 'wc_get_product' ) ) {
	fwrite( STDERR, "WooCommerce is not active on this install.\n" );
	exit( 1 );
}

$apply = (bool) cp_arg( 'apply', true );
$slugs = (bool) cp_arg( 'slugs', true );

echo 'WordPress: ' . dirname( $wp_load ) . "\n";
echo 'Mode:      ' . ( $apply ? 'APPLY' : 'dry run (pass --apply to write)' ) . "\n";
echo 'Slugs:     ' . ( $slugs ? 'rewritten' : 'left as-is' ) . "\n\n";

$groups = array(
	array( 'label' => 'motif',   'map' => (array) get_option( 'cosypaw_product_map', array() ), 'names' => CP_MOTIF_NAMES ),
	array( 'label' => 'package', 'map' => (array) get_option( 'cosypaw_package_map', array() ), 'names' => CP_PACKAGE_NAMES ),
);

$changed = 0;
$ok      = 0;
$missing = array();

foreach ( $groups as $group ) {
	foreach ( $group['names'] as $id => $name ) {
		if ( ! isset( $group['map'][ $id ] ) ) {
			$missing[] = "{$group['label']}:{$id} (not in map — run the seeder)";
			continue;
		}

		$product_id = (int) $group['map'][ $id ];
		$product    = wc_get_product( $product_id );
		if ( ! $product ) {
			$missing[] = "{$group['label']}:{$id} (mapped to #{$product_id}, which no longer exists)";
			continue;
		}

		$current   = $product->get_name();
		$new_slug  = sanitize_title( $name );
		$slug_diff = $slugs && $product->get_slug() !== $new_slug;

		if ( $current === $name && ! $slug_diff ) {
			++$ok;
			continue;
		}

		printf(
			"#%d %-14s %-18s -> %s%s\n",
			$product_id,
			$id,
			$current,
			$name,
			$slug_diff ? "  [slug: {$product->get_slug()} -> {$new_slug}]" : ''
		);
		++$changed;

		if ( ! $apply ) {
			continue;
		}

		$product->set_name( $name );
		if ( $slugs ) {
			$product->set_slug( $new_slug );
		}
		$product->save();

		// The seeder stamps the featured image's alt + title with the product
		// name, so those carry the same wrong-locale text.
		$image_id = (int) $product->get_image_id();
		if ( $image_id ) {
			update_post_meta( $image_id, '_wp_attachment_image_alt', $name );
			wp_update_post(
				array(
					'ID'         => $image_id,
					'post_title' => $name,
				)
			);
		}
	}
}

foreach ( $missing as $note ) {
	echo "SKIP {$note}\n";
}

echo "\n";
printf(
	"%d already correct, %d %s, %d skipped.\n",
	$ok,
	$changed,
	$apply ? 'renamed' : 'would be renamed',
	count( $missing )
);

if ( $apply && $changed > 0 ) {
	wc_delete_product_transients();
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
	echo "Product transients cleared.\n";
}
