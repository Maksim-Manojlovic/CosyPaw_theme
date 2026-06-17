<?php
/**
 * One-off: switch the WooCommerce Cart + Checkout pages from the block markup to
 * the classic shortcodes, so the theme's classic cart styling, the bundle
 * composite thumbnail (woocommerce_cart_item_thumbnail), and the .mo
 * translations all apply.
 *
 * Connects straight to the Local site's MySQL (bypasses the broken `localhost`
 * wp-cli path). Run with: php tools/switch-wc-classic.php
 */

declare(strict_types=1);

$host   = '127.0.0.1';
$port   = 10096;          // Local site MySQL port (cozypaw).
$db     = 'local';
$user   = 'root';
$pass   = 'root';
$prefix = 'wp_';

$pages = array(
	'woocommerce_cart_page_id'     => '[woocommerce_cart]',
	'woocommerce_checkout_page_id' => '[woocommerce_checkout]',
);

$mysqli = new mysqli( $host, $user, $pass, $db, $port );
if ( $mysqli->connect_errno ) {
	fwrite( STDERR, 'DB connect failed: ' . $mysqli->connect_error . "\n" );
	exit( 1 );
}
$mysqli->set_charset( 'utf8mb4' );

foreach ( $pages as $option => $shortcode ) {
	$res = $mysqli->query( "SELECT option_value FROM {$prefix}options WHERE option_name='{$option}' LIMIT 1" );
	$row = $res ? $res->fetch_assoc() : null;
	$pid = $row ? (int) $row['option_value'] : 0;
	if ( ! $pid ) {
		echo "{$option}: no page id\n";
		continue;
	}

	$cur = $mysqli->query( "SELECT post_content FROM {$prefix}posts WHERE ID={$pid} LIMIT 1" )->fetch_assoc();
	$before = $cur ? $cur['post_content'] : '';

	$stmt = $mysqli->prepare( "UPDATE {$prefix}posts SET post_content=? WHERE ID=?" );
	$stmt->bind_param( 'si', $shortcode, $pid );
	$stmt->execute();
	$stmt->close();

	echo "Page {$pid} ({$option}) updated -> {$shortcode}\n";
	echo '  was: ' . str_replace( "\n", ' ', substr( $before, 0, 90 ) ) . "\n";
}

$mysqli->close();
echo "Done.\n";
