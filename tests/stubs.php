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
		 * Product ID.
		 *
		 * @var int
		 */
		private int $id;

		/**
		 * Approved reviews on this product.
		 *
		 * @var int
		 */
		private int $review_count = 0;

		/**
		 * Constructor.
		 *
		 * @param string $name        Product name.
		 * @param string $price       Selling price.
		 * @param bool   $purchasable Purchasable flag.
		 * @param int    $id          Product ID.
		 */
		public function __construct( string $name = 'Žirafa', string $price = '790', bool $purchasable = true, int $id = 0 ) {
			$this->name        = $name;
			$this->price       = $price;
			$this->purchasable = $purchasable;
			$this->id          = $id;
		}

		/**
		 * Product ID.
		 *
		 * @return int
		 */
		public function get_id(): int {
			return $this->id;
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

		/**
		 * How many approved reviews the product carries.
		 *
		 * @return int
		 */
		public function get_review_count(): int {
			return $this->review_count;
		}

		/**
		 * Set the review count for a test that turns on it.
		 *
		 * @param int $count Reviews.
		 * @return void
		 */
		public function set_review_count( int $count ): void {
			$this->review_count = $count;
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

		/**
		 * Total quantity across the cart lines.
		 *
		 * @return int
		 */
		public function get_cart_contents_count(): int {
			$count = 0;
			foreach ( $this->contents as $item ) {
				$count += (int) ( $item['quantity'] ?? 0 );
			}

			return $count;
		}

		/**
		 * Sum of the line prices, fees excluded.
		 *
		 * Fees excluded on purpose: that is what WooCommerce's own
		 * get_cart_contents_total() means, and a stub that quietly folded them in
		 * is exactly what let the cart pill ship quoting the pre-discount price.
		 *
		 * @return float
		 */
		public function get_cart_contents_total(): float {
			$total = 0.0;
			foreach ( $this->contents as $item ) {
				$product = $item['data'] ?? null;
				if ( $product instanceof \WC_Product ) {
					$total += (float) $product->get_price() * (int) ( $item['quantity'] ?? 0 );
				}
			}

			return $total;
		}

		/**
		 * Sum of the fees booked on the cart, negative for a discount.
		 *
		 * @return float
		 */
		public function get_fee_total(): float {
			$total = 0.0;
			foreach ( $this->fees as $fee ) {
				$total += $fee['amount'];
			}

			return $total;
		}

		/**
		 * WooCommerce's cart *contents* total, formatted. Despite the name it
		 * excludes fees — mirrored here so a test cannot pass on a total the real
		 * cart would never produce.
		 *
		 * @return string
		 */
		public function get_cart_total(): string {
			return wc_price( $this->get_cart_contents_total() );
		}
	}
}

if ( ! function_exists( 'wc_price' ) ) {
	/**
	 * Stand-in for WooCommerce's price formatter, in the shop's own format:
	 * de-DE grouping and an RSD suffix, matching Theme\Catalog::format_price().
	 *
	 * @param float $amount Amount.
	 * @return string
	 */
	function wc_price( float $amount ): string {
		return '<span class="woocommerce-Price-amount">' . number_format( $amount, 0, ',', '.' ) . ' RSD</span>';
	}
}

if ( ! class_exists( 'WC_Mock_Order_Item' ) ) {
	/**
	 * One order line. A bundle carries its motif ids in `cosypaw_motifs`; a
	 * single towel carries none and is identified by its product id alone.
	 */
	class WC_Mock_Order_Item {

		/**
		 * Product ID.
		 *
		 * @var int
		 */
		private int $product_id;

		/**
		 * Line-item meta.
		 *
		 * @var array<string,string>
		 */
		private array $meta;

		/**
		 * Constructor.
		 *
		 * @param int                  $product_id Product ID.
		 * @param array<string,string> $meta       Line-item meta.
		 */
		public function __construct( int $product_id, array $meta = array() ) {
			$this->product_id = $product_id;
			$this->meta       = $meta;
		}

		/**
		 * Product ID.
		 *
		 * @return int
		 */
		public function get_product_id(): int {
			return $this->product_id;
		}

		/**
		 * Read one meta value.
		 *
		 * @param string $key Meta key.
		 * @return string
		 */
		public function get_meta( string $key ): string {
			return $this->meta[ $key ] ?? '';
		}
	}
}

if ( ! class_exists( 'WC_Mock_Order' ) ) {
	/**
	 * Minimal stand-in for WC_Order, recording meta writes and saves so a test
	 * can assert that an ask was marked sent exactly once.
	 */
	class WC_Mock_Order {

		/**
		 * Order status.
		 *
		 * @var string
		 */
		private string $status;

		/**
		 * Order meta.
		 *
		 * @var array<string,mixed>
		 */
		private array $meta;

		/**
		 * Line items.
		 *
		 * @var array<int,WC_Mock_Order_Item>
		 */
		private array $items;

		/**
		 * Billing email.
		 *
		 * @var string
		 */
		private string $email;

		/**
		 * Number of save() calls.
		 *
		 * @var int
		 */
		public int $saves = 0;

		/**
		 * Constructor.
		 *
		 * @param string                        $status Order status.
		 * @param array<int,WC_Mock_Order_Item> $items  Line items.
		 * @param array<string,mixed>           $meta   Order meta.
		 * @param string                        $email  Billing email.
		 */
		public function __construct( string $status = 'completed', array $items = array(), array $meta = array(), string $email = 'kupac@example.test' ) {
			$this->status = $status;
			$this->items  = $items;
			$this->meta   = $meta;
			$this->email  = $email;
		}

		/**
		 * Order status.
		 *
		 * @return string
		 */
		public function get_status(): string {
			return $this->status;
		}

		/**
		 * Read one meta value.
		 *
		 * @param string $key Meta key.
		 * @return mixed
		 */
		public function get_meta( string $key ) {
			return $this->meta[ $key ] ?? '';
		}

		/**
		 * Write one meta value.
		 *
		 * @param string $key   Meta key.
		 * @param mixed  $value Meta value.
		 * @return void
		 */
		public function update_meta_data( string $key, $value ): void {
			$this->meta[ $key ] = $value;
		}

		/**
		 * Line items.
		 *
		 * @return array<int,WC_Mock_Order_Item>
		 */
		public function get_items(): array {
			return $this->items;
		}

		/**
		 * Billing email.
		 *
		 * @return string
		 */
		public function get_billing_email(): string {
			return $this->email;
		}

		/**
		 * Billing first name.
		 *
		 * @return string
		 */
		public function get_billing_first_name(): string {
			return 'Ana';
		}

		/**
		 * Persist. Counted so a test can prove the sent marker was written.
		 *
		 * @return void
		 */
		public function save(): void {
			++$this->saves;
		}
	}
}

if ( ! class_exists( 'WC_Mock_Mailer' ) ) {
	/**
	 * Spy for WC_Emails: keeps every message instead of sending it.
	 */
	class WC_Mock_Mailer {

		/**
		 * Sent messages, each { to, subject, body }.
		 *
		 * @var array<int,array<string,string>>
		 */
		public array $sent = array();

		/**
		 * WooCommerce's branded email shell. The stub keeps the body only.
		 *
		 * @param string $heading Email heading.
		 * @param string $message Email body.
		 * @return string
		 */
		public function wrap_message( string $heading, string $message ): string {
			return '<h1>' . $heading . '</h1>' . $message;
		}

		/**
		 * Record a message. The last two arguments exist because WC_Emails::send
		 * takes them; the theme passes both and PHP would fatal on the extras.
		 *
		 * @param string        $to          Recipient.
		 * @param string        $subject     Subject.
		 * @param string        $body        Wrapped body.
		 * @param string        $headers     Extra headers.
		 * @param array<int,mixed> $attachments Attachments.
		 * @return void
		 */
		public function send( string $to, string $subject, string $body = '', string $headers = '', array $attachments = array() ): void {
			$this->sent[] = array(
				'to'      => $to,
				'subject' => $subject,
				'body'    => $body,
			);
		}
	}
}
