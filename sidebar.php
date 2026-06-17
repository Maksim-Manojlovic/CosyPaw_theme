<?php
/**
 * Primary sidebar.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'primary' ) ) {
	return;
}
?>
<aside class="sidebar" role="complementary">
	<?php dynamic_sidebar( 'primary' ); ?>
</aside>
