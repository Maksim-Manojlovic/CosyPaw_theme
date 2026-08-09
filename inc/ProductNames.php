<?php
/**
 * ProductNames — per-product English/Russian name overrides.
 *
 * The Serbian name of a product is its post_title, edited in the normal title
 * field. English and Russian names normally come from the .po files, keyed on
 * that title as the msgid — which means renaming anything, or adding a product
 * the translation files have never heard of, silently leaves it Serbian in the
 * other two languages.
 *
 * This adds a "CosyPaw — names" box on the product edit screen so those two
 * names can be typed in directly. An override wins over gettext; leaving a field
 * empty falls back to the .po translation, so the seeded catalog keeps working
 * untouched.
 *
 * @package CosyPaw
 */

declare(strict_types=1);

namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProductNames.
 */
final class ProductNames {

	/**
	 * Post meta key prefix; the locale is appended (_cosypaw_name_en_US).
	 *
	 * @var string
	 */
	public const META_PREFIX = '_cosypaw_name_';

	/**
	 * Nonce action/field name.
	 *
	 * @var string
	 */
	private const NONCE = 'cosypaw_product_names';

	/**
	 * Overridable locales => native label.
	 *
	 * Serbian is deliberately absent: it is the post_title.
	 *
	 * @var array<string,string>
	 */
	private const LOCALES = array(
		'en_US' => 'English',
		'ru_RU' => 'Русский',
	);

	/**
	 * Constructor — registers the meta box and its save handler.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes_product', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Read the stored override for a product in a given locale.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $locale     Locale, or '' for the current one.
	 * @return string Empty string when no override is set.
	 */
	public function get( int $product_id, string $locale = '' ): string {
		if ( ! $product_id ) {
			return '';
		}

		$locale = '' !== $locale ? $locale : determine_locale();

		if ( ! isset( self::LOCALES[ $locale ] ) ) {
			return '';
		}

		return trim( (string) get_post_meta( $product_id, self::META_PREFIX . $locale, true ) );
	}

	/**
	 * Register the meta box on the product edit screen.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'cosypaw-product-names',
			__( 'CosyPaw — names', 'cosypaw' ),
			array( $this, 'render' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param \WP_Post $post Product being edited.
	 * @return void
	 */
	public function render( $post ): void {
		$product_id = (int) $post->ID;
		$source     = (string) $post->post_title;

		wp_nonce_field( self::NONCE, self::NONCE );
		?>
		<p class="description">
			<?php esc_html_e( 'The title field above is the Serbian name. Fill these in to set the name shown to English and Russian visitors.', 'cosypaw' ); ?>
		</p>

		<?php foreach ( self::LOCALES as $locale => $label ) : ?>
			<?php
			$value = $this->get( $product_id, $locale );
			// What the front end would show if this field stays empty.
			$fallback = $this->translate_in( $source, $locale );
			?>
			<p>
				<label for="cosypaw-name-<?php echo esc_attr( $locale ); ?>">
					<strong><?php echo esc_html( $label ); ?></strong>
				</label><br />
				<input
					type="text"
					class="widefat"
					id="cosypaw-name-<?php echo esc_attr( $locale ); ?>"
					name="cosypaw_name[<?php echo esc_attr( $locale ); ?>]"
					value="<?php echo esc_attr( $value ); ?>"
					placeholder="<?php echo esc_attr( $fallback ); ?>"
				/>
			</p>
		<?php endforeach; ?>

		<p class="description">
			<?php esc_html_e( 'Leave empty to use the translation from the theme language files (shown greyed out).', 'cosypaw' ); ?>
		</p>
		<?php
	}

	/**
	 * Translate a string in a specific locale, whatever the current one is.
	 *
	 * Used only to show the admin what the fallback would be if the field is
	 * left empty. The box needs two locales inside one request, which neither
	 * switch_to_locale() nor load_textdomain() delivers reliably: WordPress
	 * resolves a translation against the locale that is current at lookup time,
	 * so once the Russian catalogue is in play the English lookups start missing
	 * and silently return the Serbian source. Reading the .mo directly keeps the
	 * two catalogues independent and touches no global i18n state.
	 *
	 * @param string $text   Source string.
	 * @param string $locale Target locale.
	 * @return string
	 */
	private function translate_in( string $text, string $locale ): string {
		if ( '' === $text ) {
			return '';
		}

		static $catalogs = array();

		if ( ! array_key_exists( $locale, $catalogs ) ) {
			require_once ABSPATH . WPINC . '/pomo/mo.php';

			$mofile = get_template_directory() . '/languages/' . $locale . '.mo';
			$mo     = new \MO();

			$catalogs[ $locale ] = ( is_readable( $mofile ) && $mo->import_from_file( $mofile ) ) ? $mo : null;
		}

		if ( ! $catalogs[ $locale ] instanceof \MO ) {
			return $text;
		}

		return (string) $catalogs[ $locale ]->translate( $text );
	}

	/**
	 * Persist the submitted overrides.
	 *
	 * An empty field deletes the meta rather than storing '', so resolve() falls
	 * cleanly back to gettext.
	 *
	 * @param int      $post_id Product ID.
	 * @param \WP_Post $post    Product.
	 * @return void
	 */
	public function save( $post_id, $post ): void {
		$post_id = (int) $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || ( $post && 'product' !== $post->post_type ) ) {
			return;
		}

		// Absent nonce means this save did not come from the product editor
		// (bulk edit, REST, the seeder) — leave any existing overrides alone.
		if ( ! isset( $_POST[ self::NONCE ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$submitted = isset( $_POST['cosypaw_name'] ) ? (array) wp_unslash( $_POST['cosypaw_name'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		foreach ( array_keys( self::LOCALES ) as $locale ) {
			$key   = self::META_PREFIX . $locale;
			$value = isset( $submitted[ $locale ] ) ? sanitize_text_field( (string) $submitted[ $locale ] ) : '';

			if ( '' === trim( $value ) ) {
				delete_post_meta( $post_id, $key );
				continue;
			}

			update_post_meta( $post_id, $key, $value );
		}
	}
}
