/**
 * MiniCart — the cart as a panel from the right (Theme\MiniCart).
 *
 * The footer carries an empty <dialog>; its contents come from wc-ajax every
 * time it opens or changes, so a cached page never shows somebody else's
 * cart. The cart pill (a.cart-fab) and the header's cart icon (a.cart-btn)
 * open it instead of going to /cart/ — a middle click, or one with a modifier
 * key, still goes there. It also opens after an AJAX add to cart (the
 * `added_to_cart` jQuery event: the landing page's buttons and the package
 * builder), and on the first page after a product page's own add.
 *
 * Inside: quantity buttons and remove (with the nonce the contents carry),
 * "+ Dodaj" on the offered motifs, and the gift links, which open the gift
 * popup over the panel. A gift picked there refreshes the panel.
 */
export class MiniCart {
	/**
	 * @param {HTMLDialogElement} dialog The [data-mini-cart] element.
	 */
	constructor(dialog) {
		this.dialog = dialog;
		this.body = dialog.querySelector('[data-mini-cart-body]');
		this.countEl = dialog.querySelector('[data-mini-cart-count]');
		this.busy = false;
		this.nonce = '';

		document.addEventListener('click', (event) => this.onDocumentClick(event));
		dialog.addEventListener('click', (event) => this.onDialogClick(event));
		dialog.addEventListener('close', () => document.documentElement.classList.remove('has-mini-cart'));

		// A gift picked in the popup changes the gift line and the ladder.
		document.addEventListener('cosypaw:gift-chosen', () => {
			if (this.dialog.open) this.request(this.dialog.dataset.endpointView);
		});

		const $ = window.jQuery;
		if ($) {
			$(document.body).on('added_to_cart', () => this.open());
		} else {
			// jQuery is deferred; it may land after this boots.
			window.addEventListener('load', () => {
				if (window.jQuery) window.jQuery(document.body).on('added_to_cart', () => this.open());
			});
		}

		if (dialog.hasAttribute('data-open-on-load')) this.open();
	}

	async open() {
		if (!this.dialog.open) {
			if (typeof this.dialog.showModal === 'function') {
				this.dialog.showModal();
			} else {
				this.dialog.setAttribute('open', '');
			}
			document.documentElement.classList.add('has-mini-cart');
		}

		const loaded = await this.request(this.dialog.dataset.endpointView);

		// A panel that cannot load its contents must not stand between the
		// shopper and the cart: fall back to the cart page the icon links to.
		if (!loaded && !this.body.querySelector('.mini-cart__scroll') && this.dialog.dataset.cartUrl) {
			window.location.href = this.dialog.dataset.cartUrl;
		}
	}

	close() {
		if (typeof this.dialog.close === 'function') {
			this.dialog.close();
		} else {
			this.dialog.removeAttribute('open');
		}
		document.documentElement.classList.remove('has-mini-cart');
	}

	/**
	 * The pill and the header icon open the panel; a click meant for a new
	 * tab is left alone.
	 *
	 * @param {MouseEvent} event
	 */
	onDocumentClick(event) {
		if (!(event.target instanceof Element)) return;
		if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

		const link = event.target.closest('a.cart-fab, a.cart-btn, [data-mini-cart-open]');
		if (!link) return;

		event.preventDefault();
		this.open();
	}

	/**
	 * @param {MouseEvent} event
	 */
	onDialogClick(event) {
		// The panel fills the dialog's box except where the backdrop shows.
		if (event.target === this.dialog) {
			this.close();
			return;
		}

		if (!(event.target instanceof Element)) return;

		const inBody = (el) => (el && this.body.contains(el) ? el : null);
		const qty = inBody(event.target.closest('[data-qty]'));
		if (qty) {
			const line = qty.closest('[data-key]');
			if (line && !qty.disabled) {
				this.request(this.dialog.dataset.endpointQty, { key: line.dataset.key, qty: qty.dataset.qty, nonce: this.nonce });
			}
			return;
		}

		const add = inBody(event.target.closest('[data-add]'));
		if (add) {
			this.request(this.dialog.dataset.endpointAdd, { product_id: add.dataset.add });
		}
	}

	/**
	 * Post to an endpoint and render what comes back, success or not.
	 *
	 * @param {string} url
	 * @param {Record<string,string>} [fields]
	 * @returns {Promise<boolean>} Whether contents arrived (or are on the way).
	 */
	async request(url, fields = {}) {
		if (!url) return false;
		if (this.busy) return true;
		this.busy = true;
		let loaded = false;
		this.body.setAttribute('aria-busy', 'true');
		this.dialog.classList.add('is-busy');

		const body = new FormData();
		Object.entries(fields).forEach(([k, v]) => body.append(k, String(v)));

		try {
			const response = await fetch(url, { method: 'POST', body, credentials: 'same-origin' });
			const json = await response.json();
			if (json && json.data && typeof json.data.html === 'string') {
				this.render(json.data);
				loaded = true;
			}
		} catch {
			// The panel keeps what it showed; the next action retries.
		} finally {
			this.busy = false;
			this.body.setAttribute('aria-busy', 'false');
			this.dialog.classList.remove('is-busy');
		}

		return loaded;
	}

	/**
	 * @param {{html: string, count: number, fab: string}} data
	 */
	render(data) {
		const scrolled = this.body.querySelector('.mini-cart__scroll');
		const top = scrolled ? scrolled.scrollTop : 0;

		this.body.innerHTML = data.html;

		const scroll = this.body.querySelector('.mini-cart__scroll');
		if (scroll) {
			scroll.scrollTop = top;
			this.nonce = scroll.dataset.nonce || this.nonce;
		}

		const count = Number(data.count) || 0;
		if (this.countEl) this.countEl.textContent = count > 0 ? `(${count})` : '';

		// The header badge and the pill, which the page printed before the change.
		document.querySelectorAll('[data-cart-count]').forEach((badge) => {
			if (badge.dataset.cartOwner === 'wc' || !badge.dataset.cartOwner) {
				badge.textContent = String(count);
				badge.hidden = count < 1;
			}
		});

		const fab = document.querySelector('a.cart-fab');
		if (fab && data.fab) {
			const holder = document.createElement('div');
			holder.innerHTML = data.fab;
			const next = holder.firstElementChild;
			if (next) fab.replaceWith(next);
		}
	}
}
