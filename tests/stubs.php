<?php
/**
 * Global-namespace class stubs for the unit tests.
 *
 * Brain\Monkey covers WordPress *functions*; the theme also type-checks against
 * a couple of WooCommerce classes, which do not exist without a WooCommerce
 * install. Only the members the theme actually calls are defined here.
 *
 * Lives outside *Test.php so PHPUnit does not treat it as a test case.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

if ( ! class_exists( 'WC_Product' ) ) {
	/**
	 * Minimal stand-in for WooCommerce's product class.
	 */
	class WC_Product {

		/**
		 * Product name.
		 *
		 * @var string
		 */
		private string $name;

		/**
		 * Selling price.
		 *
		 * @var string
		 */
		private string $price;

		/**
		 * Whether the product can be bought.
		 *
		 * @var bool
		 */
		private bool $purchasable;

		/**
		 * Constructor.
		 *
		 * @param string $name        Product name.
		 * @param string $price       Selling price.
		 * @param bool   $purchasable Purchasable flag.
		 */
		public function __construct( string $name = 'Žirafa', string $price = '790', bool $purchasable = true ) {
			$this->name        = $name;
			$this->price       = $price;
			$this->purchasable = $purchasable;
		}

		/**
		 * Product name.
		 *
		 * @return string
		 */
		public function get_name(): string {
			return $this->name;
		}

		/**
		 * Selling price.
		 *
		 * @return string
		 */
		public function get_price(): string {
			return $this->price;
		}

		/**
		 * Whether the product can be bought.
		 *
		 * @return bool
		 */
		public function is_purchasable(): bool {
			return $this->purchasable;
		}
	}
}

if ( ! class_exists( 'WC_Cart' ) ) {
	/**
	 * Minimal stand-in for WooCommerce's cart, recording the fees added to it.
	 */
	class WC_Cart {

		/**
		 * Cart lines, in WooCommerce's shape.
		 *
		 * @var array<int,array<string,mixed>>
		 */
		private array $contents;

		/**
		 * Fees booked by add_fee(), newest last.
		 *
		 * @var array<int,array{name:string,amount:float,taxable:bool}>
		 */
		public array $fees = array();

		/**
		 * Constructor.
		 *
		 * @param array<int,array<string,mixed>> $contents Cart lines.
		 */
		public function __construct( array $contents = array() ) {
			$this->contents = $contents;
		}

		/**
		 * Cart lines.
		 *
		 * @return array<int,array<string,mixed>>
		 */
		public function get_cart(): array {
			return $this->contents;
		}

		/**
		 * Record a fee.
		 *
		 * @param string $name    Fee label.
		 * @param float  $amount  Fee amount (negative for a discount).
		 * @param bool   $taxable Whether the fee is taxed.
		 * @return void
		 */
		public function add_fee( string $name, float $amount, bool $taxable = false ): void {
			$this->fees[] = array(
				'name'    => $name,
				'amount'  => (float) $amount,
				'taxable' => $taxable,
			);
		}
	}
}
