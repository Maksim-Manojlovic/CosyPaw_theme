/**
 * MotifColours — the colour dots on a motif card (the penguin).
 *
 * The motif is one variable product. The card shows its first colour, and the
 * dots (template-parts/motif-card.php) carry what each other colour needs:
 * its photograph and the variation to buy. A dot picked swaps the photograph
 * and re-aims the "Kupi 1 kom" link at that variation. WooCommerce's AJAX
 * add-to-cart takes a variation id as the product id, so the link needs no
 * more than that and a plain `href` for when script does not run.
 */
export class MotifColours {
	/**
	 * @param {HTMLElement} card The [data-motif-colours] card.
	 */
	constructor(card) {
		this.card = card;
		this.img = card.querySelector('.motif-card__img');
		this.buy = card.querySelector('.motif-single[data-product_id]');

		card.addEventListener('change', (event) => this.onChange(event));
	}

	/**
	 * @param {Event} event
	 */
	onChange(event) {
		const input = event.target;
		if (!(input instanceof HTMLInputElement) || input.type !== 'radio' || !input.checked) return;

		if (this.img && input.dataset.image) {
			this.img.src = input.dataset.image;
			if (input.dataset.srcset) {
				this.img.srcset = input.dataset.srcset;
			} else {
				this.img.removeAttribute('srcset');
			}
		}

		if (this.buy) {
			// The button's own state from a previous add must not carry over.
			this.buy.classList.remove('added', 'loading', 'is-added');
			this.buy.dataset.product_id = input.dataset.variation || this.buy.dataset.product_id;
			if (input.dataset.url) this.buy.setAttribute('href', input.dataset.url);
		}
	}
}
