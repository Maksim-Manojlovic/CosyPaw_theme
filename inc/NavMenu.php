<?php
/**
 * NavMenu — the header's links, and the wp-admin menu that holds them.
 *
 * The header prints whatever menu is assigned to the "primary" location
 * (Izgled → Meniji), and falls back to ITEMS while none is. So the shop can
 * edit the header by hand, this builds a "Glavni meni" from the same ITEMS
 * once, the first time an administrator opens wp-admin, and assigns it. A
 * location that already has a menu is never touched: that menu is the shop's.
 *
 * A menu item's title is a database value in one language, while the header
 * speaks three. Titles are therefore run through the theme's translations
 * on the way out: one that matches a msgid ("Cosy peškiri") follows the
 * visitor's language, one the shop typed itself prints as typed.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NavMenu.
 */
final class NavMenu {

	/**
	 * Menu location the header prints.
	 */
	public const LOCATION = 'primary';

	/**
	 * Name of the menu built for it.
	 */
	public const MENU_NAME = 'Glavni meni';

	/**
	 * Bump to build the menu again on installs where the location is empty.
	 */
	public const VERSION = 1;

	/**
	 * Option recording which version this install has been brought up to.
	 */
	private const OPTION = 'cosypaw_nav_menu_version';

	/**
	 * The header's links, in order.
	 *
	 * 'label' is a Serbian msgid. 'page' is a Pages::REGISTRY key; 'anchor' a
	 * front-page section id. With both, the anchor stands in while the page
	 * does not exist yet — the towels fall back to the homepage gallery.
	 *
	 * @var list<array{label:string,page?:string,anchor?:string}>
	 */
	public const ITEMS = array(
		array(
			'label'  => 'Cosy peškiri',
			'page'   => 'motivi',
			'anchor' => 'galerija',
		),
		array(
			'label' => 'Peškiri',
			'page'  => 'dugi',
		),
		array(
			'label' => 'Jastuci',
			'page'  => 'jastuci',
		),
		array(
			'label' => 'Magične krpe',
			'page'  => 'krpe',
		),
		array(
			'label' => 'Pokloni',
			'page'  => 'paketi',
		),
		array(
			'label'  => 'Paketi',
			'anchor' => 'paketi',
		),
		array(
			'label'  => 'Zašto CosyPaw',
			'anchor' => 'zasto',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		// After Pages (priority 10) has created the pages the items point at.
		add_action( 'admin_init', array( $this, 'maybe_seed' ), 20 );
		add_filter( 'nav_menu_item_title', array( $this, 'translate_title' ), 10, 3 );
	}

	/**
	 * The header's links while no menu is assigned: list of [ href, label ].
	 *
	 * A page that does not exist yet drops out, unless it has an anchor to
	 * fall back to. Anchors resolve from any page back to the homepage.
	 *
	 * @return list<array{0:string,1:string}>
	 */
	public static function default_links(): array {
		$home  = is_front_page() ? '' : home_url( '/' );
		$links = array();

		foreach ( self::ITEMS as $item ) {
			$href = isset( $item['page'] ) ? Pages::url( $item['page'] ) : '';
			if ( '' === $href && isset( $item['anchor'] ) ) {
				$href = $home . '#' . $item['anchor'];
			}
			if ( '' === $href ) {
				continue;
			}

			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- ITEMS labels are msgids.
			$links[] = array( $href, __( $item['label'], 'cosypaw' ) );
		}

		return $links;
	}

	/**
	 * Run seed() unless this install is already at the current version.
	 *
	 * @return void
	 */
	public function maybe_seed(): void {
		if ( (int) get_option( self::OPTION, 0 ) >= self::VERSION || ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$this->seed();
		update_option( self::OPTION, self::VERSION );
	}

	/**
	 * Build "Glavni meni" from ITEMS and assign it to the header, unless the
	 * header already has a menu. A "Glavni meni" that already exists is
	 * assigned as it is.
	 *
	 * @return int Number of menu items added.
	 */
	public function seed(): int {
		if ( has_nav_menu( self::LOCATION ) ) {
			return 0;
		}

		$added = 0;
		$menu  = wp_get_nav_menu_object( self::MENU_NAME );

		if ( is_object( $menu ) && ! empty( $menu->term_id ) ) {
			$menu_id = (int) $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( self::MENU_NAME );
			if ( is_wp_error( $menu_id ) || (int) $menu_id < 1 ) {
				return 0;
			}
			$menu_id = (int) $menu_id;
			$added   = $this->add_items( $menu_id );
		}

		$locations                   = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations[ self::LOCATION ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );

		return $added;
	}

	/**
	 * Translate a header menu item's title into the visitor's language.
	 *
	 * @param string $title     Menu item title.
	 * @param object $menu_item Menu item.
	 * @param object $args      wp_nav_menu() arguments.
	 * @return string
	 */
	public function translate_title( $title, $menu_item, $args ) {
		if ( ! is_string( $title ) || '' === $title || is_admin() ) {
			return $title;
		}
		if ( self::LOCATION !== ( is_object( $args ) ? ( $args->theme_location ?? '' ) : '' ) ) {
			return $title;
		}

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- a title typed to match a msgid.
		return __( $title, 'cosypaw' );
	}

	/**
	 * Add ITEMS to a menu: pages as page items, so they follow a changed
	 * slug, anchors as custom links to the homepage.
	 *
	 * @param int $menu_id Menu term id.
	 * @return int Number of items added.
	 */
	private function add_items( int $menu_id ): int {
		$added = 0;

		foreach ( self::ITEMS as $item ) {
			$page_id = isset( $item['page'] ) ? self::page_id( $item['page'] ) : 0;

			if ( $page_id > 0 ) {
				$data = array(
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_id,
				);
			} elseif ( isset( $item['anchor'] ) ) {
				$data = array(
					'menu-item-type' => 'custom',
					'menu-item-url'  => home_url( '/#' . $item['anchor'] ),
				);
			} else {
				continue;
			}

			$id = wp_update_nav_menu_item(
				$menu_id,
				0,
				$data + array(
					'menu-item-title'  => $item['label'],
					'menu-item-status' => 'publish',
				)
			);

			if ( ! is_wp_error( $id ) && (int) $id > 0 ) {
				++$added;
			}
		}

		return $added;
	}

	/**
	 * ID of a registered page, or 0 while it does not exist or is not published.
	 *
	 * @param string $key Pages::REGISTRY key.
	 * @return int
	 */
	private static function page_id( string $key ): int {
		$slug = Pages::REGISTRY[ $key ]['slug'] ?? '';
		$page = '' !== $slug ? get_page_by_path( $slug ) : null;

		if ( ! is_object( $page ) || empty( $page->ID ) || 'publish' !== ( $page->post_status ?? '' ) ) {
			return 0;
		}

		return (int) $page->ID;
	}
}
