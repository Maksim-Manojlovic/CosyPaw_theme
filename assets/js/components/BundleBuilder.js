/**
 * BundleBuilder
 *
 * Interactive "Napravi svoj paket" section: pick a package tier (solo/duo/trio),
 * fill its motif slots from the gallery, then add the assembled bundle to the
 * cart. Mirrors the design prototype's UX (slots, used-count badges, "Iznenadi
 * me", "Očisti", CTA states, warn toasts).
 *
 * Cart integration (single source of truth — no separate in-section cart):
 *   - WC mode  (selected tier carries data-product-id): AJAX-adds the package
 *     product with the chosen motifs as `cosypaw_motifs` cart-item data, then
 *     fires WooCommerce's `added_to_cart` (updates the nav badge + toast).
 *   - Demo mode (no mapped product): dispatches `cosypaw:add` to the cart drawer.
 *
 * Markup contract: see the Paketi section in front-page.php.
 */
export class BundleBuilder {
	/**
	 * @param {HTMLElement} root  The [data-bundle-builder] element.
	 * @param {object}      [opts] { cart, l10n }.
	 */
	constructor(root, opts = {}) {
		if (!(root instanceof HTMLElement)) {
			throw new TypeError('BundleBuilder requires its root element.');
		}
		this.root = root;
		this.cart = opts.cart || null;
		this.l10n = Object.assign(
			{
				slotLabel: 'Motiv %d',
				addToCartPrice: 'Dodaj u korpu • %s',
				chooseMore: 'Izaberi još %d',
				motifOne: 'motiv',
				motifMany: 'motiva',
				bundleFull: 'Paket je pun — ukloni motiv da dodaš drugi',
				notFull: 'Izaberi još %d — paket nije popunjen',
				removeMotif: 'Ukloni motiv',
				currency: 'RSD',
				locale: 'de-DE',
			},
			opts.l10n || {}
		);

		this.tiers = Array.from(root.querySelectorAll('[data-tiers] [data-package]'));
		this.motifs = Array.from(root.querySelectorAll('[data-gallery] [data-motif-id]'));
		this.step2El = root.querySelector('[data-builder-step2]');
		this.slotsEl = root.querySelector('[data-slots]');
		this.countEl = root.querySelector('[data-count]');
		this.qtyLabelEl = root.querySelector('[data-qty-label]');
		this.clearBtn = root.querySelector('[data-clear]');
		this.randomBtn = root.querySelector('[data-random]');
		this.ctaBtn = root.querySelector('[data-add-bundle]');
		this.ctaLabelEl = root.querySelector('[data-cta-label]');
		this.selPriceEl = root.querySelector('[data-sel-price]');
		this.selOldEl = root.querySelector('[data-sel-old]');
		this.selNameEl = root.querySelector('[data-sel-name]');
		this.selPerEl = root.querySelector('[data-sel-per]');

		this.picks = [];
		// No tier is pre-selected: Step 2 stays hidden until the user picks one.
		this.selected = this.tiers.find((t) => t.getAttribute('aria-pressed') === 'true') || null;

		this._onClick = this._onClick.bind(this);
		root.addEventListener('click', this._onClick);

		this._render();
	}

	/* ---------- helpers ---------- */

	_qty() {
		return this.selected ? parseInt(this.selected.dataset.qty || '1', 10) : 1;
	}

	_motifById(id) {
		return this.motifs.find((m) => m.dataset.motifId === id) || null;
	}

	_motifName(id) {
		const el = this._motifById(id);
		return el ? el.dataset.name : '';
	}

	/* ---------- events ---------- */

	_onClick(e) {
		const tier = e.target.closest('[data-package]');
		if (tier && this.root.contains(tier)) {
			this.selectTier(tier);
			return;
		}

		const motif = e.target.closest('[data-motif-id]');
		if (motif && this.root.contains(motif)) {
			e.preventDefault();
			this.addMotif(motif.dataset.motifId);
			return;
		}

		const remove = e.target.closest('[data-slot-remove]');
		if (remove) {
			e.preventDefault();
			this.removeSlot(parseInt(remove.dataset.slotRemove, 10));
			return;
		}

		if (e.target.closest('[data-random]')) {
			e.preventDefault();
			this.randomFill();
		} else if (e.target.closest('[data-clear]')) {
			e.preventDefault();
			this.clear();
		} else if (e.target.closest('[data-add-bundle]')) {
			e.preventDefault();
			this.addBundle();
		}
	}

	/* ---------- actions ---------- */

	selectTier(el) {
		const wasHidden = this.step2El ? this.step2El.hidden : false;
		this.selected = el;
		this.tiers.forEach((t) => t.setAttribute('aria-pressed', t === el ? 'true' : 'false'));
		this.picks = this.picks.slice(0, this._qty());
		this._render();
		// Guide the eye to the now-revealed motif step (only on first reveal).
		if (wasHidden) {
			this._scrollTo(this.step2El);
		}
	}

	addMotif(id) {
		const qty = this._qty();
		if (this.picks.length >= qty) {
			this._toast(this.l10n.bundleFull, 'warn');
			return;
		}
		this.picks.push(id);
		this._render();
		// When the bundle just filled up, bring the CTA into view.
		if (this.picks.length === qty) {
			this._scrollTo(this.ctaBtn);
		}
	}

	removeSlot(index) {
		if (index >= 0 && index < this.picks.length) {
			this.picks.splice(index, 1);
			this._render();
		}
	}

	clear() {
		this.picks = [];
		this._render();
	}

	randomFill() {
		const qty = this._qty();
		while (this.picks.length < qty && this.motifs.length) {
			const m = this.motifs[Math.floor(Math.random() * this.motifs.length)];
			this.picks.push(m.dataset.motifId);
		}
		this._render();
		this._scrollTo(this.ctaBtn);
	}

	/**
	 * Smooth-scroll an element into view, clearing the sticky header (+ admin bar).
	 * Honors prefers-reduced-motion.
	 * @private
	 */
	_scrollTo(el) {
		if (!el) return;
		const reduce =
			window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		requestAnimationFrame(() => {
			const header = document.querySelector('.site-header');
			let offset = header ? header.getBoundingClientRect().height : 0;
			if (document.body.classList.contains('admin-bar')) offset += 32;
			const top = el.getBoundingClientRect().top + window.pageYOffset - offset - 16;
			window.scrollTo({ top: Math.max(0, top), behavior: reduce ? 'auto' : 'smooth' });
		});
	}

	addBundle() {
		const qty = this._qty();
		const remaining = qty - this.picks.length;
		if (remaining > 0) {
			this._toast(this.l10n.notFull.replace('%d', String(remaining)), 'warn');
			return;
		}

		const ids = this.picks.slice();
		const names = ids.map((id) => this._motifName(id));
		const productId = this.selected.dataset.productId;

		if (productId) {
			this._wcAdd(productId, ids);
		} else {
			// Demo cart: one line per bundle, motifs in the name.
			const label = `${this.selected.dataset.name} (${names.join(', ')})`;
			this.root.dispatchEvent(
				new CustomEvent('cosypaw:add', {
					bubbles: true,
					detail: { name: label, price: parseInt(this.selected.dataset.price || '0', 10) || 0 },
				})
			);
		}

		this.picks = [];
		this._render();
	}

	/**
	 * Add the package product to the WooCommerce cart with the motif selection.
	 * Sends motif IDs; the server resolves names + composite thumbnail.
	 * @private
	 */
	_wcAdd(productId, ids) {
		const params = window.wc_add_to_cart_params;
		const $ = window.jQuery;
		if (!params || !$) {
			return;
		}

		const url = params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart');
		const body = new FormData();
		body.append('product_id', productId);
		body.append('quantity', '1');
		body.append('cosypaw_motifs', ids.join(','));

		fetch(url, { method: 'POST', body, credentials: 'same-origin' })
			.then((r) => r.json())
			.then((res) => {
				if (!res || res.error) {
					return;
				}
				// Let WooCommerce update fragments (badge) + our listener show the toast.
				$(document.body).trigger('added_to_cart', [
					res.fragments,
					res.cart_hash,
					$(this.ctaBtn),
				]);
			})
			.catch(() => {});
	}

	/* ---------- rendering ---------- */

	_fmt(amount) {
		return Number(amount).toLocaleString(this.l10n.locale) + ' ' + this.l10n.currency;
	}

	_render() {
		// Step 2 is revealed only once a tier is chosen.
		if (this.step2El) this.step2El.hidden = !this.selected;
		if (!this.selected) return;

		const qty = this._qty();

		this._renderSlots(qty);
		this._renderGalleryBadges();

		if (this.countEl) this.countEl.textContent = String(this.picks.length);
		if (this.qtyLabelEl) this.qtyLabelEl.textContent = String(qty);
		if (this.clearBtn) this.clearBtn.hidden = this.picks.length === 0;

		this._renderSelection();
		this._renderCta(qty);
	}

	_renderSlots(qty) {
		if (!this.slotsEl) return;
		this.slotsEl.textContent = '';

		for (let i = 0; i < qty; i++) {
			const id = this.picks[i];
			const slot = document.createElement('div');
			slot.className = 'builder-slot';

			if (id) {
				const name = this._motifName(id);
				const img = this._motifById(id) ? this._motifById(id).dataset.image : '';
				slot.classList.add('is-filled');
				slot.innerHTML =
					`<div class="builder-slot__img" role="img" aria-label="${this._esc(name)}" style="background-image:url('${this._esc(img)}')">` +
					`<button type="button" class="builder-slot__remove" data-slot-remove="${i}" aria-label="${this._esc(this.l10n.removeMotif)}">` +
					'<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>' +
					'</button></div>' +
					`<span class="builder-slot__name">${this._esc(name)}</span>`;
			} else {
				slot.classList.add('is-empty');
				slot.innerHTML =
					'<div class="builder-slot__placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></div>' +
					`<span class="builder-slot__name builder-slot__name--muted">${this._esc(this.l10n.slotLabel.replace('%d', String(i + 1)))}</span>`;
			}
			this.slotsEl.appendChild(slot);
		}
	}

	_renderGalleryBadges() {
		const counts = {};
		this.picks.forEach((id) => {
			counts[id] = (counts[id] || 0) + 1;
		});
		this.motifs.forEach((el) => {
			const badge = el.querySelector('[data-used]');
			if (!badge) return;
			const n = counts[el.dataset.motifId] || 0;
			badge.textContent = String(n);
			badge.hidden = n === 0;
		});
	}

	_renderSelection() {
		if (!this.selected) return;
		const d = this.selected.dataset;
		if (this.selNameEl) this.selNameEl.textContent = d.name || '';
		if (this.selPriceEl) this.selPriceEl.textContent = d.priceFmt || this._fmt(d.price || 0);
		if (this.selPerEl) this.selPerEl.textContent = d.perFmt || '';
		if (this.selOldEl) {
			this.selOldEl.textContent = d.oldFmt || '';
			this.selOldEl.hidden = !d.oldFmt;
		}
	}

	_renderCta(qty) {
		if (!this.ctaBtn || !this.ctaLabelEl) return;
		const remaining = qty - this.picks.length;
		const full = remaining <= 0;
		const priceFmt = this.selected ? this.selected.dataset.priceFmt || this._fmt(this.selected.dataset.price) : '';

		// Stays clickable when incomplete so the click yields a "not full" toast.
		// Named for what it is rather than "disabled": it is an active control,
		// so it carries neither the disabled styling nor the contrast exemption.
		this.ctaBtn.classList.toggle('is-incomplete', !full);
		this.ctaLabelEl.textContent = full
			? this.l10n.addToCartPrice.replace('%s', priceFmt)
			: this.l10n.chooseMore.replace('%d', String(remaining)) +
			  ' ' +
			  (remaining === 1 ? this.l10n.motifOne : this.l10n.motifMany);
	}

	_toast(msg, kind) {
		if (this.cart && typeof this.cart.toast === 'function') {
			this.cart.toast(msg, kind);
		}
	}

	_esc(s) {
		return String(s).replace(/&/g, '&amp;').replace(/'/g, '&#39;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	}

	destroy() {
		this.root.removeEventListener('click', this._onClick);
	}
}

export default BundleBuilder;
