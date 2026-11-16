/**
 * ColourSwatches — the colour dots on a variable product's page.
 *
 * The dots (inc/Swatches.php) stand in for WooCommerce's colour dropdown, which
 * stays in the markup because WooCommerce's variation script reads it: it finds
 * the variation, swaps the gallery photograph and price. This keeps the two in
 * step — a dot picked sets the dropdown and tells WooCommerce it changed, and a
 * change WooCommerce makes itself moves the dot. Until it runs, the dropdown
 * is what the page shows.
 */
export class ColourSwatches {
	/**
	 * @param {HTMLElement} root The [data-colour-swatches] element; its value is the select's id.
	 */
	constructor(root) {
		this.root = root;
		this.select = document.getElementById(root.dataset.colourSwatches);
		if (!(this.select instanceof HTMLSelectElement)) return;

		root.classList.add('is-enhanced');
		root.addEventListener('change', (event) => this.onPick(event));

		// WooCommerce triggers its own change through jQuery, which a native
		// listener does not see.
		const $ = window.jQuery;
		if ($) {
			$(this.select).on('change', () => this.sync());
		} else {
			this.select.addEventListener('change', () => this.sync());
		}

		this.sync();
	}

	/**
	 * A dot was picked: set the dropdown and tell whoever is listening.
	 *
	 * @param {Event} event
	 */
	onPick(event) {
		const input = event.target;
		if (!(input instanceof HTMLInputElement) || input.type !== 'radio') return;

		this.select.value = input.value;

		const $ = window.jQuery;
		if ($) {
			$(this.select).trigger('change');
		} else {
			this.select.dispatchEvent(new Event('change', { bubbles: true }));
		}
	}

	/**
	 * Check the dot that matches the dropdown.
	 */
	sync() {
		this.root.querySelectorAll('input[type="radio"]').forEach((input) => {
			input.checked = input.value === this.select.value;
		});
	}
}
