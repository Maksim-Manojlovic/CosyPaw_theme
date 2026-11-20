/**
 * GiftPopup — the "Crni petak" gift picker (Theme\BlackFriday::popup()).
 *
 * A native <dialog>: it brings its own focus trap, Esc to close and a
 * backdrop. Anything carrying [data-gift-open] opens it — the launcher pill,
 * the ladder in the cart, the "Promeni poklon" link under a gift line — and it
 * opens itself once per promotion, a few seconds into the first visit, except
 * on the cart and the checkout, where the shopper is already busy.
 *
 * The pick is posted to a wc-ajax endpoint that stores it in the WooCommerce
 * session; the cart reads it from there. It is also remembered in the browser,
 * because the popup's markup is the same for every visitor (page caching) and
 * the chosen card has to be marked from here.
 */
const SEEN_KEY = 'cosypaw-gift-seen';
const CHOICE_KEY = 'cosypaw-gift-choice';
const AUTO_OPEN_MS = 5000;

const store = {
	get(key) {
		try {
			return window.localStorage.getItem(key);
		} catch {
			return null;
		}
	},
	set(key, value) {
		try {
			window.localStorage.setItem(key, value);
		} catch {
			// Private mode or blocked storage: the popup still works, it only
			// forgets.
		}
	},
};

export class GiftPopup {
	/**
	 * @param {HTMLDialogElement} dialog The [data-gift-popup] element.
	 */
	constructor(dialog) {
		this.dialog = dialog;
		this.promo = dialog.dataset.giftPromo || '';
		this.endpoint = dialog.dataset.giftEndpoint || '';
		this.status = dialog.querySelector('[data-gift-status]');
		this.fab = document.querySelector('[data-gift-fab]');
		this.busy = false;

		document.addEventListener('click', (event) => this.onDocumentClick(event));
		dialog.addEventListener('click', (event) => this.onDialogClick(event));
		dialog.addEventListener('change', (event) => this.onChange(event));
		dialog.addEventListener('close', () => this.onClose());

		this.restore();

		if (dialog.dataset.giftAuto === '1' && store.get(SEEN_KEY) !== this.promo) {
			this.timer = window.setTimeout(() => {
				// Not over another dialog the visitor has open.
				if (!document.querySelector('dialog[open]')) this.open();
			}, AUTO_OPEN_MS);
		}
	}

	open() {
		window.clearTimeout(this.timer);
		store.set(SEEN_KEY, this.promo);

		if (this.dialog.open) return;

		if (typeof this.dialog.showModal === 'function') {
			this.dialog.showModal();
		} else {
			this.dialog.setAttribute('open', '');
		}

		document.documentElement.classList.add('has-gift-popup');
		if (this.fab) this.fab.hidden = true;
	}

	close() {
		if (typeof this.dialog.close === 'function') {
			this.dialog.close();
		} else {
			this.dialog.removeAttribute('open');
			this.onClose();
		}
	}

	onClose() {
		document.documentElement.classList.remove('has-gift-popup');
		if (this.fab) this.fab.hidden = false;
	}

	/**
	 * @param {MouseEvent} event
	 */
	onDocumentClick(event) {
		if (!(event.target instanceof Element)) return;

		const opener = event.target.closest('[data-gift-open]');
		if (!opener) return;

		event.preventDefault();
		this.open();
	}

	/**
	 * A pick button, or a click on the backdrop (the dialog itself has no
	 * padding, so only the backdrop reports the dialog as its target).
	 *
	 * @param {MouseEvent} event
	 */
	onDialogClick(event) {
		if (event.target === this.dialog) {
			this.close();
			return;
		}

		if (!(event.target instanceof Element)) return;

		const button = event.target.closest('[data-gift-pick]');
		if (button) this.choose(button.dataset.giftPick || '');
	}

	/**
	 * A colour dot: the photograph follows it.
	 *
	 * @param {Event} event
	 */
	onChange(event) {
		const input = event.target;
		if (!(input instanceof HTMLInputElement) || input.name !== 'gift-colour') return;

		const card = input.closest('[data-gift-card]');
		const image = card ? card.querySelector('[data-gift-image]') : null;
		if (image && input.dataset.image) {
			image.src = input.dataset.image;
			image.alt = input.dataset.alt || '';
		}
	}

	/**
	 * @param {string} pick "towel" or "cloths".
	 */
	async choose(pick) {
		if (this.busy || !this.endpoint) return;
		this.busy = true;

		const colourInput = this.dialog.querySelector('input[name="gift-colour"]:checked');
		const body = new FormData();
		body.append('pick', pick);
		if (colourInput) body.append('colour', colourInput.value);

		this.say('');

		try {
			const response = await fetch(this.endpoint, {
				method: 'POST',
				body,
				credentials: 'same-origin',
			});
			const json = await response.json();

			if (!response.ok || !json || !json.success) {
				throw new Error('rejected');
			}

			const choice = { pick: json.data.pick, colour: json.data.colour };
			store.set(CHOICE_KEY, JSON.stringify({ promo: this.promo, ...choice }));
			this.mark(choice);
			this.say(json.data.message || '');
			this.refreshTill();
		} catch {
			this.say(this.dialog.dataset.giftError || '');
		} finally {
			this.busy = false;
		}
	}

	/**
	 * The cart and the checkout show the gift as a line: bring them up to date.
	 */
	refreshTill() {
		const body = document.body;

		if (body.classList.contains('woocommerce-cart')) {
			window.setTimeout(() => window.location.reload(), 900);
			return;
		}

		if (body.classList.contains('woocommerce-checkout') && window.jQuery) {
			window.jQuery(document.body).trigger('update_checkout');
		}
	}

	/**
	 * Mark the chosen card, and set the colour dots to the chosen colour.
	 *
	 * @param {{pick: string, colour: string}} choice
	 */
	mark(choice) {
		const chosenLabel = this.dialog.dataset.giftChosen || '';

		this.dialog.querySelectorAll('[data-gift-card]').forEach((card) => {
			const chosen = card.dataset.giftCard === choice.pick;
			card.classList.toggle('is-chosen', chosen);

			const button = card.querySelector('[data-gift-pick]');
			if (button) {
				button.textContent = chosen && chosenLabel ? chosenLabel : button.dataset.label || '';
				button.setAttribute('aria-pressed', chosen ? 'true' : 'false');
			}
		});

		if (choice.pick === 'towel' && choice.colour) {
			const input = this.dialog.querySelector(`input[name="gift-colour"][value="${CSS.escape(choice.colour)}"]`);
			if (input && !input.disabled && !input.checked) {
				input.checked = true;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
		}
	}

	/**
	 * Mark what this browser picked earlier in this promotion.
	 */
	restore() {
		const raw = store.get(CHOICE_KEY);
		if (!raw) return;

		try {
			const saved = JSON.parse(raw);
			if (saved && saved.promo === this.promo) this.mark(saved);
		} catch {
			// A value from some older script: ignore it.
		}
	}

	/**
	 * @param {string} message
	 */
	say(message) {
		if (this.status) this.status.textContent = message;
	}
}
