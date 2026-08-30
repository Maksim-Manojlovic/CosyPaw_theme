import { UpsellSlider } from './UpsellSlider.js';

/**
 * CartUpsell
 *
 * Keeps the cart's package offer (Theme\Upsell) honest across WooCommerce's
 * AJAX cart updates, and boots the motif strip inside it.
 *
 * Removing a line or changing a quantity on the cart page is an AJAX call
 * whose response is the whole re-rendered cart page — but WooCommerce's
 * cart.js takes only two nodes out of it, the cart form and `.cart_totals`
 * (update_wc_div). The offer sits beside the totals rather than inside them,
 * so it was left saying whatever it said before the change: a cart that went
 * up to a whole Trio, where there is deliberately nothing to offer, stayed
 * silent after a towel was taken back out of it.
 *
 * The fresh markup is already in that response, so this reads the offer out of
 * it rather than asking the server a second time.
 */
export class CartUpsell {
	/**
	 * @param {HTMLElement} slot Element carrying [data-upsell-panel].
	 */
	constructor(slot) {
		this.slot = slot;

		this._onAjax = this._onAjax.bind(this);

		this._mount();

		// WooCommerce's cart script is jQuery, and so is the only hook into the
		// response it throws away. Without jQuery there is no AJAX cart either,
		// so there is nothing to keep in step.
		const $ = window.jQuery;
		if ($) $(document).on('ajaxComplete', this._onAjax);
	}

	/**
	 * Wire the arrows on whatever strip is in the slot right now.
	 */
	_mount() {
		this.slot.querySelectorAll('[data-upsell-slider]').forEach((slider) => {
			new UpsellSlider(slider);
		});
	}

	/**
	 * Swap in the offer from a cart response.
	 *
	 * The response is matched on what it contains, not on where it came from.
	 * Removing a line requests the remove link's own href and updating the cart
	 * posts to the form's action — plain cart URLs, neither of them a `wc-ajax`
	 * endpoint (see item_remove_clicked and update_cart in WooCommerce's
	 * cart.js), so a check on the URL rejects the two calls that matter most.
	 * Carrying the slot marker is the honest test: only a rendered cart page
	 * has one, and the responses that do not — the fragment refresh, the
	 * checkout's order review, the totals-only refresh — fall through.
	 *
	 * @param {Event}  event    jQuery event (unused).
	 * @param {object} xhr      The completed request.
	 * @param {object} settings Its settings (unused).
	 */
	_onAjax(event, xhr, settings) {
		const html = xhr && xhr.responseText;

		if (typeof html !== 'string' || html.indexOf('data-upsell-panel') === -1) return;

		const fresh = new DOMParser()
			.parseFromString(html, 'text/html')
			.querySelector('[data-upsell-panel]');

		if (!fresh) return;

		// innerHTML rather than replacing the node: the slot is what the next
		// response comes back into, so it has to outlive every swap.
		this.slot.innerHTML = fresh.innerHTML;
		this._mount();
	}
}
