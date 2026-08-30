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
	 * @param {Event}  event    jQuery event (unused).
	 * @param {object} xhr      The completed request.
	 * @param {object} settings Its settings, which carry the URL.
	 */
	_onAjax(event, xhr, settings) {
		const url = (settings && settings.url) || '';

		// Only the cart endpoints answer with a page; the fragment refresh and
		// the checkout's order review answer with JSON that has none of this.
		if (url.indexOf('wc-ajax=') === -1) return;

		const html = xhr && xhr.responseText;
		if (!html || html.indexOf('data-upsell-panel') === -1) return;

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
