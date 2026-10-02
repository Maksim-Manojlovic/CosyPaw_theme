<?php
/**
 * Unit tests for the header menu.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\NavMenu;
use Theme\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/inc/Pages.php';
require_once dirname( __DIR__ ) . '/inc/NavMenu.php';

final class NavMenuTest extends TestCase {

	/**
	 * Pages the fake database holds, slug => (object) post.
	 *
	 * @var array<string,object>
	 */
	private array $pages = array();

	/**
	 * Menu items added, in order.
	 *
	 * @var list<array<string,mixed>>
	 */
	private array $items = array();

	/**
	 * Theme mods, name => value.
	 *
	 * @var array<string,mixed>
	 */
	private array $mods = array();

	/**
	 * Menus that exist, name => term id.
	 *
	 * @var array<string,int>
	 */
	private array $menus = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->pages = array();
		$this->items = array();
		$this->mods  = array();
		$this->menus = array();

		Functions\stubs(
			array(
				'add_action'    => true,
				'add_filter'    => true,
				'is_wp_error'   => false,
				'is_front_page' => false,
				'is_admin'      => false,
				'home_url'      => static fn( string $path = '' ) => 'https://cosypaw.rs' . $path,
				'__'            => static fn( string $text ) => 'en:' . $text,
			)
		);

		Functions\when( 'get_page_by_path' )->alias( fn( string $slug ) => $this->pages[ $slug ] ?? null );
		Functions\when( 'get_permalink' )->alias( static fn( $page ) => 'https://cosypaw.rs/' . $page->post_name . '/' );
		Functions\when( 'wp_get_nav_menu_object' )->alias(
			function ( $menu ) {
				$id = is_int( $menu ) ? ( in_array( $menu, $this->menus, true ) ? $menu : 0 ) : ( $this->menus[ $menu ] ?? 0 );

				return $id > 0 ? (object) array( 'term_id' => $id ) : false;
			}
		);
		Functions\when( 'wp_get_nav_menu_items' )->alias(
			fn( int $menu_id ) => array_values( array_filter( $this->items, static fn( array $item ) => $item['menu'] === $menu_id ) )
		);
		Functions\when( 'wp_create_nav_menu' )->alias(
			function ( string $name ): int {
				$this->menus[ $name ] = 42;

				return 42;
			}
		);
		Functions\when( 'wp_update_nav_menu_item' )->alias(
			function ( int $menu_id, int $item_id, array $data ): int {
				$this->items[] = array( 'menu' => $menu_id ) + $data;

				return count( $this->items );
			}
		);
		Functions\when( 'get_theme_mod' )->alias( fn( string $name, $fallback = false ) => $this->mods[ $name ] ?? $fallback );
		Functions\when( 'set_theme_mod' )->alias(
			function ( string $name, $value ): void {
				$this->mods[ $name ] = $value;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Publish every registered page in the fake database.
	 *
	 * @return void
	 */
	private function publish_pages(): void {
		$id = 10;
		foreach ( Pages::REGISTRY as $page ) {
			$this->pages[ $page['slug'] ] = (object) array(
				'ID'          => $id++,
				'post_name'   => $page['slug'],
				'post_status' => 'publish',
			);
		}
	}

	/**
	 * With every page published, the menu holds ITEMS in order — pages as
	 * page items, sections as links to the homepage — and is assigned to the
	 * header.
	 *
	 * @return void
	 */
	public function test_seed_builds_and_assigns_the_menu(): void {
		$this->publish_pages();

		$added = ( new NavMenu() )->seed();

		$this->assertSame( count( NavMenu::ITEMS ), $added );
		$this->assertSame( array_column( NavMenu::ITEMS, 'label' ), array_column( $this->items, 'menu-item-title' ) );
		$this->assertSame( 42, $this->mods['nav_menu_locations'][ NavMenu::LOCATION ] );

		$this->assertSame( 'post_type', $this->items[0]['menu-item-type'] );
		$this->assertSame( $this->pages[ Pages::REGISTRY['motivi']['slug'] ]->ID, $this->items[0]['menu-item-object-id'] );
		$this->assertSame( 'custom', $this->items[6]['menu-item-type'] );
		$this->assertSame( 'https://cosypaw.rs/#zasto', $this->items[6]['menu-item-url'] );
	}

	/**
	 * A missing page drops out of the menu, except the towels, which link
	 * to the homepage gallery instead.
	 *
	 * @return void
	 */
	public function test_seed_without_pages_keeps_only_anchors(): void {
		( new NavMenu() )->seed();

		$this->assertSame( array( 'Cosy peškiri', 'Paketi', 'Zašto CosyPaw' ), array_column( $this->items, 'menu-item-title' ) );
		$this->assertSame( 'https://cosypaw.rs/#galerija', $this->items[0]['menu-item-url'] );
	}

	/**
	 * A header menu the shop has filled keeps its items and its place.
	 *
	 * @return void
	 */
	public function test_seed_leaves_a_filled_menu_alone(): void {
		$this->menus['Moj meni']          = 7;
		$this->items[]                    = array( 'menu' => 7, 'menu-item-title' => 'Moj link' );
		$this->mods['nav_menu_locations'] = array( NavMenu::LOCATION => 7 );

		$this->assertSame( 0, ( new NavMenu() )->seed() );
		$this->assertCount( 1, $this->items );
		$this->assertSame( 7, $this->mods['nav_menu_locations'][ NavMenu::LOCATION ] );
	}

	/**
	 * A header menu left empty is filled where it is, not replaced.
	 *
	 * @return void
	 */
	public function test_seed_fills_an_empty_assigned_menu(): void {
		$this->menus['Moj meni']          = 7;
		$this->mods['nav_menu_locations'] = array( NavMenu::LOCATION => 7 );

		$this->assertSame( 3, ( new NavMenu() )->seed() );
		$this->assertSame( array( 7, 7, 7 ), array_column( $this->items, 'menu' ) );
		$this->assertSame( 7, $this->mods['nav_menu_locations'][ NavMenu::LOCATION ] );
	}

	/**
	 * A header pointing at a deleted menu gets "Glavni meni" instead.
	 *
	 * @return void
	 */
	public function test_seed_replaces_a_deleted_menu(): void {
		$this->mods['nav_menu_locations'] = array( NavMenu::LOCATION => 99 );

		$this->assertSame( 3, ( new NavMenu() )->seed() );
		$this->assertSame( 42, $this->mods['nav_menu_locations'][ NavMenu::LOCATION ] );
	}

	/**
	 * An existing, filled "Glavni meni" is assigned as it is, not filled twice.
	 *
	 * @return void
	 */
	public function test_seed_assigns_an_existing_menu_without_adding(): void {
		$this->menus[ NavMenu::MENU_NAME ] = 9;
		$this->items[]                     = array( 'menu' => 9, 'menu-item-title' => 'Peškiri' );

		$this->assertSame( 0, ( new NavMenu() )->seed() );
		$this->assertCount( 1, $this->items );
		$this->assertSame( 9, $this->mods['nav_menu_locations'][ NavMenu::LOCATION ] );
	}

	/**
	 * Header titles follow the visitor's language; other menus' do not.
	 *
	 * @return void
	 */
	public function test_translate_title_only_in_the_header(): void {
		$menu = new NavMenu();

		$this->assertSame( 'en:Cosy peškiri', $menu->translate_title( 'Cosy peškiri', new \stdClass(), (object) array( 'theme_location' => 'primary' ) ) );
		$this->assertSame( 'Cosy peškiri', $menu->translate_title( 'Cosy peškiri', new \stdClass(), (object) array( 'theme_location' => 'footer' ) ) );
	}

	/**
	 * The fallback links match ITEMS, translated, sections resolved to the
	 * homepage from any other page.
	 *
	 * @return void
	 */
	public function test_default_links(): void {
		Functions\when( 'get_page_by_path' )->alias( static fn() => null );

		$this->assertSame(
			array(
				array( 'https://cosypaw.rs/#galerija', 'en:Cosy peškiri' ),
				array( 'https://cosypaw.rs/#paketi', 'en:Paketi' ),
				array( 'https://cosypaw.rs/#zasto', 'en:Zašto CosyPaw' ),
			),
			NavMenu::default_links()
		);
	}
}
