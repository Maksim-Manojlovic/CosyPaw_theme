<?php
/**
 * One-off: retire the Trio package from a shop seeded before the 2+1 reprice.
 *
 * The catalogue now sells two things — a Single at 790 and a 2+1 package at
 * 1.490 for three towels — and the old Trio (three towels, dearer) has no
 * reason to exist beside it. Catalog::packages() has already dropped it, which
 * leaves a live shop with a product nobody links to but anybody can still reach
 * by URL, still mapped under cosypaw_package_map['trio'].
 *
 * This unpublishes that product and removes the map entry. Draft, never
 * deleted: past orders point at the post, and an order whose line item resolves
 * to nothing is a support ticket months from now. Drafting takes it off sale
 * and leaves the record intact — and is undone with one click in wp-admin.
 *
 * The package the shop keeps selling is renamed by the sibling script, which
 * owns the Serbian names:
 *   php tools/rename-products-sr.php --wp=PATH --apply
 *
 * Usage (dry run — prints what would change, writes nothing):
 *   php tools/retire-trio-package.php --wp=C:/path/to/wordpress
 *
 * Apply:
 *   php tools/retire-trio-package.php --wp=C:/path/to/wordpress --apply
 *
 * Options:
 *   --wp=PATH   WordPress root holding wp-load.php. Defaults to $WP_ROOT, then
 *               to walking up from this file.
 *   --apply     Actually write. Without it the script only reports.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( 'cli' !== PHP_SAPI ) {
	exit( 'CLI only.' );
}

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

echo 'WordPress: ' . dirname( $wp_load ) . "\n";
echo 'Mode:      ' . ( $apply ? 'APPLY' : 'dry run (pass --apply to write)' ) . "\n\n";

$map = (array) get_option( 'cosypaw_package_map', array() );

// The package that stays. Reported, never touched: its price is the shop's to
// set in wp-admin and its name belongs to rename-products-sr.php.
if ( isset( $map['duo'] ) ) {
	$pack = wc_get_product( (int) $map['duo'] );

	if ( $pack ) {
		printf(
			"keep  #%d  %s — %s RSD\n",
			(int) $map['duo'],
			$pack->get_name(),
			(string) $pack->get_price()
		);

		if ( '2+1 paket' !== $pack->get_name() ) {
			echo "      name is not \"2+1 paket\" — run tools/rename-products-sr.php --apply\n";
		}

		if ( (int) $pack->get_price() >= 2 * 790 ) {
			echo "      price is at or above two singles, so \"2+1 GRATIS\" will not render\n";
		}
	} else {
		printf( "keep  #%d — mapped but the product no longer exists\n", (int) $map['duo'] );
	}
} else {
	echo "keep  duo package is not mapped — run the seeder\n";
}

if ( ! isset( $map['trio'] ) ) {
	echo "\nNo Trio package mapped. Nothing to retire.\n";
	exit( 0 );
}

$trio_id = (int) $map['trio'];
$trio    = wc_get_product( $trio_id );
$status  = $trio ? get_post_status( $trio_id ) : '';

printf(
	"\nretire #%d  %s — %s (%s)\n",
	$trio_id,
	$trio ? $trio->get_name() : '(missing)',
	$trio ? (string) $trio->get_price() . ' RSD' : 'no product',
	'' === $status ? 'gone' : $status
);

if ( ! $apply ) {
	echo "\nWould draft the product and drop cosypaw_package_map['trio'].\n";
	exit( 0 );
}

if ( $trio && 'draft' !== $status ) {
	$trio->set_status( 'draft' );
	$trio->save();
	echo "Drafted.\n";
}

unset( $map['trio'] );
update_option( 'cosypaw_package_map', $map );
echo "Removed from cosypaw_package_map.\n";

wc_delete_product_transients();
if ( function_exists( 'wp_cache_flush' ) ) {
	wp_cache_flush();
}
echo "Product transients cleared.\n";
