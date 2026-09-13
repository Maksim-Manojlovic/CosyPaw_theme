/**
 * CartQuantity
 *
 * Submits the cart form when a quantity changes, so "Ažuriranje korpe" does not
 * have to be pressed — the button is hidden by woocommerce.css and clicked from
 * here instead.
 *
 * The button was the whole failure mode: a shopper types 3, reads a total that
 * still says 1, and either learns the rule or checks out with the wrong basket.
 * WooCommerce ships it because the quantity field cannot reach the server on
 * its own; that is a constraint of the form, not something a buyer should have
 * to know.
 *
 * A full submit, not an AJAX patch. WooCommerce's own cart.js only re-renders
 * the form and `.cart_totals` on AJAX, which would leave the package offer
 * beside them saying whatever it said before the change — and the offer is the
 * reason this cart page exists. A reload redraws every claim at once.
 */
export class CartQuantity {
	/**
	 * @param {HTMLFormElement} form The cart form.
	 */
	constructor(form) {
		this.form = form;
		this.timer = 0;

		// Delegated: WooCommerce replaces the table wholesale on its own AJAX
		// updates, and a listener bound to today's inputs would not survive it.
		this.form.addEventListener('change', (event) => this.onChange(event));
	}

	/**
	 * @param {Event} event Change event from anywhere in the form.
	 */
	onChange(event) {
		const input = event.target;
		if (!(input instanceof HTMLInputElement) || !input.matches('.qty')) return;

		// A quantity typed to nothing is a half-finished edit, not a request to
		// empty the line — submitting it would be answering a question the
		// shopper has not finished asking.
		if (input.value === '') return;

		// Spinner arrows fire one change per click, so a shopper stepping from
		// 1 to 4 would post three carts and watch three reloads.
		window.clearTimeout(this.timer);
		this.timer = window.setTimeout(() => this.submit(), 500);
	}

	/**
	 * Post the form the way the hidden button would.
	 */
	submit() {
		const button = this.form.querySelector('[name="update_cart"]');
		if (!button) return;

		// WooCommerce disables it until something changes, and a disabled
		// submitter cannot submit anything.
		button.disabled = false;

		// requestSubmit(submitter) carries the button's own name/value into the
		// post, which is what WC_Form_Handler::update_cart_action() looks for.
		if (typeof this.form.requestSubmit === 'function') {
			this.form.requestSubmit(button);
			return;
		}

		button.click();
	}
}
