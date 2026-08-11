/**
 * CartDrawer
 *
 * Client-side demo cart for the CosyPaw landing page. The prototype is a
 * demo shop (cash-on-delivery, no live orders), so the cart lives in the
 * browser and persists to sessionStorage.
 *
 * Wires:
 *  - [data-cart-toggle] / [data-cart-close]  open & close the drawer
 *  - [data-cart-add] (name/price data-attrs) add an item on click
 *  - custom event "cosypaw:add" {detail:{name, price}} add programmatically
 *  - [data-cart-checkout] demo checkout (toast only)
 *
 * Renders into the markup in footer.php (items, count badge, total, empty state).
 */
export class CartDrawer {
	/**
	 * @param {Document|HTMLElement} [root=document]
	 * @param {object} [l10n] Localized strings (window.CosyPawL10n).
	 */
	constructor(root = document, l10n = {}) {
		this.root = root;
		this.l10n = Object.assign(
			{
				addedSuffix: 'dodat u korpu',
				demoMsg: 'Demo prodavnice — porudžbina nije aktivna',
				removeLabel: 'Ukloni',
				currency: 'RSD',
				locale: 'de-DE',
			},
			l10n || {}
		);

		this.drawer = root.querySelector('[data-cart-drawer]');
		this.panel = root.querySelector('[data-cart-panel]');
		this.itemsEl = root.querySelector('[data-cart-items]');
		this.emptyEl = root.querySelector('[data-cart-empty]');
		this.footEl = root.querySelector('[data-cart-foot]');
		this.totalEl = root.querySelector('[data-cart-total]');
		this.countEl = root.querySelector('[data-cart-count]');
		this.toastEl = root.querySelector('[data-toast]');
		this.toastMsgEl = root.querySelector('[data-toast-msg]');

		this.items = this._load();
		this._toastTimer = null;
		// Element focus returns to when the drawer closes.
		this._lastFocused = null;
		// Elements outside the drawer that were made inert while it is open.
		this._inerted = [];

		// Bound handlers for clean teardown.
		this._onClick = this._onClick.bind(this);
		this._onAddEvent = this._onAddEvent.bind(this);
		this._onKeydown = this._onKeydown.bind(this);

		root.addEventListener('click', this._onClick);
		root.addEventListener('cosypaw:add', this._onAddEvent);
		document.addEventListener('keydown', this._onKeydown);

		this._render();
	}

	/* ---------- persistence ---------- */

	_load() {
		try {
			const raw = sessionStorage.getItem('cosypaw_cart');
			const parsed = raw ? JSON.parse(raw) : [];
			return Array.isArray(parsed) ? parsed : [];
		} catch {
			return [];
		}
	}

	_save() {
		try {
			sessionStorage.setItem('cosypaw_cart', JSON.stringify(this.items));
		} catch {
			/* ignore quota / disabled storage */
		}
	}

	/* ---------- formatting ---------- */

	_fmt(amount) {
		return Number(amount).toLocaleString(this.l10n.locale) + ' ' + this.l10n.currency;
	}

	/* ---------- events ---------- */

	_onClick(e) {
		const addBtn = e.target.closest('[data-cart-add]');
		if (addBtn && this.root.contains(addBtn)) {
			e.preventDefault();
			this.add({
				name: addBtn.dataset.name || 'Artikal',
				price: parseInt(addBtn.dataset.price || '0', 10) || 0,
			});
			return;
		}

		if (e.target.closest('[data-cart-toggle]')) {
			e.preventDefault();
			this.toggle();
			return;
		}
		if (e.target.closest('[data-cart-close]')) {
			e.preventDefault();
			this.close();
			return;
		}
		if (e.target.closest('[data-cart-checkout]')) {
			e.preventDefault();
			this.close();
			this._showToast(this.l10n.demoMsg);
			return;
		}

		const removeBtn = e.target.closest('[data-cart-remove]');
		if (removeBtn) {
			e.preventDefault();
			this.remove(parseInt(removeBtn.dataset.cartRemove, 10));
		}
	}

	_onAddEvent(e) {
		if (e.detail && e.detail.name) {
			this.add({ name: e.detail.name, price: Number(e.detail.price) || 0 });
		}
	}

	_onKeydown(e) {
		if (!this.drawer || this.drawer.hidden) return;

		if (e.key === 'Escape') {
			this.close();
			return;
		}

		if (e.key === 'Tab') {
			this._trapTab(e);
		}
	}

	/* ---------- focus management ---------- */

	/**
	 * Focusable elements inside the panel, in tab order.
	 * @private
	 * @return {HTMLElement[]}
	 */
	_focusable() {
		if (!this.panel) return [];
		const sel =
			'a[href], button:not([disabled]), input:not([disabled]), ' +
			'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
		return Array.from(this.panel.querySelectorAll(sel)).filter(
			(el) => !el.hidden && el.offsetParent !== null
		);
	}

	/**
	 * Keep Tab inside the dialog. aria-modal alone does not constrain the
	 * browser's tab order, so without this the next Tab from the last control
	 * lands on the page behind the overlay.
	 * @private
	 */
	_trapTab(e) {
		const items = this._focusable();
		if (items.length === 0) {
			e.preventDefault();
			return;
		}

		const first = items[0];
		const last = items[items.length - 1];
		const active = document.activeElement;

		if (e.shiftKey && (active === first || !this.panel.contains(active))) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && active === last) {
			e.preventDefault();
			first.focus();
		}
	}

	/**
	 * Hide the rest of the document from assistive tech and the tab order while
	 * the drawer is open.
	 * @private
	 */
	_setBackgroundInert(on) {
		if (on) {
			// The toast is a body-level sibling too; inerting it would suppress
			// its live-region announcements while the drawer is open.
			this._inerted = Array.from(document.body.children).filter(
				(el) => el !== this.drawer && el !== this.toastEl && !el.hasAttribute('inert')
			);
			this._inerted.forEach((el) => el.setAttribute('inert', ''));
		} else {
			this._inerted.forEach((el) => el.removeAttribute('inert'));
			this._inerted = [];
		}
		// Stop the page behind the overlay from scrolling with the drawer.
		document.body.style.overflow = on ? 'hidden' : '';
	}

	/* ---------- public API ---------- */

	add(item) {
		this.items.push(item);
		this._save();
		this._render();
		this._showToast(item.name + ' ' + this.l10n.addedSuffix);
	}

	remove(index) {
		if (index >= 0 && index < this.items.length) {
			this.items.splice(index, 1);
			this._save();
			this._render();
		}
	}

	open() {
		if (!this.drawer || !this.drawer.hidden) return;

		this._lastFocused = document.activeElement;
		this.drawer.hidden = false;
		this._setBackgroundInert(true);

		// Move focus into the dialog; without this the drawer opens behind the
		// keyboard user, who stays parked on the trigger outside the overlay.
		const target = this._focusable()[0] || this.panel;
		if (target) target.focus();
	}

	close() {
		if (!this.drawer || this.drawer.hidden) return;

		this.drawer.hidden = true;
		this._setBackgroundInert(false);

		// Return focus where it came from. The trigger can be gone (e.g. a cart
		// row that was removed), so fall back to the header cart button.
		const restore =
			this._lastFocused && this._lastFocused.isConnected
				? this._lastFocused
				: this.root.querySelector('[data-cart-toggle]');
		if (restore && typeof restore.focus === 'function') restore.focus();
		this._lastFocused = null;
	}

	toggle() {
		if (!this.drawer) return;
		if (this.drawer.hidden) {
			this.open();
		} else {
			this.close();
		}
	}

	/** Public: show a transient toast. kind = 'ok' | 'warn'. */
	toast(message, kind = 'ok') {
		this._showToast(message, kind);
	}

	destroy() {
		this.root.removeEventListener('click', this._onClick);
		this.root.removeEventListener('cosypaw:add', this._onAddEvent);
		document.removeEventListener('keydown', this._onKeydown);
		if (this._toastTimer) clearTimeout(this._toastTimer);
		// Never leave the rest of the page inert or the body scroll-locked.
		this._setBackgroundInert(false);
	}

	/* ---------- rendering ---------- */

	_render() {
		const count = this.items.length;
		const total = this.items.reduce((sum, it) => sum + (Number(it.price) || 0), 0);

		// Count badge.
		if (this.countEl) {
			this.countEl.textContent = String(count);
			this.countEl.hidden = count === 0;
		}

		// Empty state vs. items.
		if (this.emptyEl) this.emptyEl.hidden = count > 0;
		if (this.footEl) this.footEl.hidden = count === 0;
		if (this.totalEl) this.totalEl.textContent = this._fmt(total);

		if (this.itemsEl) {
			this.itemsEl.textContent = '';
			this.items.forEach((it, i) => {
				this.itemsEl.appendChild(this._itemNode(it, i));
			});
		}
	}

	_itemNode(item, index) {
		const row = document.createElement('div');
		row.className = 'cart-item';

		const info = document.createElement('div');
		const name = document.createElement('div');
		name.className = 'cart-item__name';
		name.textContent = item.name;
		const price = document.createElement('div');
		price.className = 'cart-item__price';
		price.textContent = this._fmt(item.price);
		info.append(name, price);

		const remove = document.createElement('button');
		remove.type = 'button';
		remove.className = 'cart-item__remove';
		remove.setAttribute('data-cart-remove', String(index));
		remove.setAttribute('aria-label', this.l10n.removeLabel);
		remove.innerHTML =
			'<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';

		row.append(info, remove);
		return row;
	}

	_showToast(msg, kind = 'ok') {
		if (!this.toastEl) return;
		if (this.toastMsgEl) this.toastMsgEl.textContent = msg;
		this.toastEl.classList.toggle('toast--warn', kind === 'warn');
		this.toastEl.hidden = false;
		if (this._toastTimer) clearTimeout(this._toastTimer);
		this._toastTimer = setTimeout(() => {
			this.toastEl.hidden = true;
		}, 2600);
	}
}

export default CartDrawer;
