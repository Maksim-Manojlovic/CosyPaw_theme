<?php
/**
 * Global-namespace stand-ins for WooCommerce's variable products, shared by
 * the tests of the penguin (PenguinTest) and the Dugonja (DugonjaTest).
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

require_once __DIR__ . '/stubs.php';

if ( ! class_exists( 'WC_Product_Attribute' ) ) {
	/**
	 * Records what the attribute was given.
	 */
	class WC_Product_Attribute {
		/** @var array<string,mixed> */
		public array $props = array();

		public function __call( string $method, array $args ) {
			if ( 0 === strpos( $method, 'set_' ) ) {
				$this->props[ substr( $method, 4 ) ] = $args[0];
			}
			return null;
		}
	}
}

if ( ! class_exists( 'WC_Product_Variation' ) ) {
	/**
	 * Records a saved variation.
	 */
	class WC_Product_Variation extends WC_Product {
		/** @var array<int,WC_Product_Variation> Saved variations by id. */
		public static array $saved = array();

		/** @var array<string,mixed> */
		public array $props = array();

		public function __call( string $method, array $args ) {
			if ( 0 === strpos( $method, 'set_' ) ) {
				$this->props[ substr( $method, 4 ) ] = $args[0];
			}
			return null;
		}

		public function get_attributes(): array {
			return (array) ( $this->props['attributes'] ?? array() );
		}

		public function get_image_id(): int {
			return (int) ( $this->props['image_id'] ?? 0 );
		}

		public function save(): int {
			$id                 = 900 + count( self::$saved );
			self::$saved[ $id ] = $this;

			return $id;
		}
	}
}

if ( ! class_exists( 'WC_Product_Variable' ) ) {
	/**
	 * The parent once it has been made variable.
	 */
	class WC_Product_Variable extends WC_Product {
		/** @var array<int,WC_Product_Variable> Saved parents by id. */
		public static array $saved = array();

		/** @var array<int,int> Ids passed to sync(). */
		public static array $synced = array();

		/** @var array<string,mixed> */
		public array $props = array();

		public function __construct( int $id = 0 ) {
			parent::__construct( 'Pingvin', '790', true, $id );
			$this->props = self::$saved[ $id ]->props ?? array();
		}

		public function __call( string $method, array $args ) {
			if ( 0 === strpos( $method, 'set_' ) ) {
				$this->props[ substr( $method, 4 ) ] = $args[0];
			}
			return null;
		}

		public function is_type( $type ): bool {
			return 'variable' === $type;
		}

		public function get_attributes(): array {
			return (array) ( $this->props['attributes'] ?? array() );
		}

		public function get_image_id(): int {
			return 77;
		}

		public function get_gallery_image_ids(): array {
			return (array) ( $this->props['gallery_image_ids'] ?? array( 55 ) );
		}

		public function get_default_attributes(): array {
			return (array) ( $this->props['default_attributes'] ?? array() );
		}

		public function get_children(): array {
			return array_keys( WC_Product_Variation::$saved );
		}

		public function save(): int {
			self::$saved[ $this->get_id() ] = $this;

			return $this->get_id();
		}

		public static function sync( $id ): void {
			self::$synced[] = (int) $id;
		}
	}
}
