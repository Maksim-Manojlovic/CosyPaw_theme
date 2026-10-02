/**
 * BundleBuilder
 *
 * Interactive "Napravi svoj paket" section: pick a package tier (solo/duo),
 * fill its motif slots from the gallery, then add the assembled bundle to the
 * cart. Mirrors the design prototype's UX (slots, used-count badges, "Iznenadi
 * me", "Očisti", CTA states, warn toasts).
 *
 * The tier is where the bundle starts, not where it ends. Once its slots are
 * full the next one unlocks, so a fourth towel is a single on top of the
 * package and a sixth completes a second one. Whatever the count, the price
 * is plan() — the same cheapest-combination rule \Theme\BundlePricing bills
 * the cart with — so the total shown here is the total the cart will charge.
 *
 * Cart integration (single source of truth — no separate in-section cart):
 *   - WC mode  (selected tier carries data-product-id): AJAX-adds the largest
 *     package for every full set of its size, and each towel left over as the
 *     single, with the chosen motifs as `cosypaw_motifs` cart-item data. The
 *     cart's package pricing then prices them exactly as plan() did. Fires
 *     WooCommerce's `added_to_cart` (updates the nav badge + toast).
 *   - Demo mode (no mapped product): dispatches `cosypaw:add` to the cart drawer.
 *
 * Markup contract: see template-parts/packages.php.
 */

/**
 * Most towels one bundle takes. Only a guard against a slot row that grows
 * without end; four full packages is far past any real order.
 */
const MAX_TOWELS = 12;

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
				slotLabel: 'Peškir %d',
				addToCartPrice: 'Dodaj u korpu • %s',
				chooseMore: 'Izaberi još %d',
				motifOne: 'peškir',
				motifMany: 'peškira',
				bundleMax: 'Najviše %d peškira odjednom — dodaj ovaj paket u korpu, pa napravi sledeći',
				bundleAdded: 'Dodato u paket — izaberi još %d',
				bundleReady: 'Paket je pun — dodaj u korpu ili izaberi još',
				bundleExtra: 'Dodato — u paketu je %d peškira',
				slotNext: '+ %s',
				slotFree: 'Gratis',
				unitShort: 'kom',
				shipFree: 'Besplatna dostava',
				shipMissing: 'Još %1$s do besplatne dostave (preko %2$s)',
				notFull: 'Izaberi još %d — paket nije popunjen',
				removeMotif: 'Ukloni peškir',
				adding: 'Dodajem…',
				addFailed: 'Dodavanje nije uspelo — pokušaj ponovo',
				currency: 'RSD',
				locale: 'de-DE',
			},
			opts.l10n || {}
		);

		this.tiers = Array.from(root.querySelectorAll('[data-tiers] [data-package]'));
		this.motifs = Array.from(root.querySelectorAll('[data-gallery] [data-motif-id]'));
		this.step2El = root.querySelector('[data-builder-step2]');
		// Slots, price and CTA share this row, so it is what every "look at
		// your bundle" scroll aims at — the button alone would put the towels
		// off the top of the screen on a phone, where the row stacks.
		this.summaryEl = root.querySelector('[data-builder-summary]');
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
		this.shipEl = root.querySelector('[data-sel-ship]');
		// Free-delivery threshold, 0 while there is none.
		this.freeMin = parseInt(root.dataset.freeMin || '0', 10) || 0;

		this.picks = [];
		// The markup ships one tier already pressed (see front-page.php). The
		// fallbacks cover markup that does not: prefer data-default-package,
		// then the first tier, so the builder is never a dead grid.
		this.selected =
			this.tiers.find((t) => t.getAttribute('aria-pressed') === 'true') ||
			this.tiers.find((t) => t.dataset.package === root.dataset.defaultPackage) ||
			this.tiers[0] ||
			null;
		if (this.selected) {
			this.tiers.forEach((t) =>
				t.setAttribute('aria-pressed', t === this.selected ? 'true' : 'false')
			);
		}
		// True while an add-to-cart request is in flight.
		this.busy = false;

		this._onClick = this._onClick.bind(this);
		root.addEventListener('click', this._onClick);

		this._render();
		this._applyDeepLink();
	}

	/**
	 * Drop a motif carried in from a product page (?motif=id) into the bundle,
	 * on the size that page was arguing for (?package=duo) when it names one.
	 * A tier is already selected by then, so the towel just lands in slot 1.
	 */
	_applyDeepLink() {
		let wanted = '';
		let size = '';
		try {
			const params = new URLSearchParams(window.location.search);
			wanted = params.get('motif') || '';
			size = params.get('package') || '';
		} catch (e) {
			return;
		}

		if (size) {
			const tier = this.tiers.find((t) => t.dataset.package === size);
			if (tier) this.selectTier(tier);
		}

		if (!wanted || !this._motifById(wanted) || !this.selected) return;

		this.addMotif(wanted);
	}

	/* ---------- helpers ---------- */

	_qty() {
		return this.selected ? parseInt(this.selected.dataset.qty || '1', 10) : 1;
	}

	/**
	 * Towels the bundle is priced for: the chosen ones, or the selected
	 * package's size while it is still being filled.
	 */
	_count() {
		return Math.max(this._qty(), this.picks.length);
	}

	/** The tier buttons as plain data, largest package first. */
	_tierData() {
		return this.tiers
			.map((t) => ({
				el: t,
				id: t.dataset.package || '',
				name: t.dataset.name || '',
				qty: parseInt(t.dataset.qty || '0', 10) || 0,
				price: parseInt(t.dataset.price || '0', 10) || 0,
			}))
			.filter((t) => t.qty > 0 && t.price > 0)
			.sort((a, b) => b.qty - a.qty);
	}

	/** The one-towel tier, or null. */
	_single() {
		return this._tierData().find((t) => t.qty === 1) || null;
	}

	/**
	 * Cheapest way the shop sells this many towels.
	 *
	 * A line-for-line port of \Theme\BundlePricing::plan(), which is what the
	 * cart is billed with: exact over the tiers, a package may be bought
	 * part-filled (two towels already cost the 2+1 package, so the third is
	 * free), ties keep the plan found first. The two have to agree, or the
	 * builder quotes a total the cart then contradicts.
	 *
	 * @param {number} towels
	 * @returns {{total:number, lines:Object<string,number>}}
	 */
	_plan(towels) {
		const tiers = this._tierData();
		const empty = { total: 0, lines: {} };
		if (towels < 1 || !tiers.length) return empty;

		const best = new Array(towels + 1).fill(null);
		best[0] = empty;

		for (let n = 1; n <= towels; n++) {
			for (const tier of tiers) {
				const prev = best[n - Math.min(tier.qty, n)];
				if (!prev) continue;

				const total = prev.total + tier.price;
				if (best[n] && total >= best[n].total) continue;

				const lines = Object.assign({}, prev.lines);
				lines[tier.id] = (lines[tier.id] || 0) + 1;
				best[n] = { total, lines };
			}
		}

		return best[towels] || empty;
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
		// Pressing the size already chosen changes nothing — it used to cut a
		// bundle grown past that size back down to it.
		if (el === this.selected) return;

		this.selected = el;
		this.tiers.forEach((t) => t.setAttribute('aria-pressed', t === el ? 'true' : 'false'));
		this.picks = this.picks.slice(0, this._qty());
		this._render();
	}

	addMotif(id) {
		if (this.picks.length >= MAX_TOWELS) {
			this._toast(this.l10n.bundleMax.replace('%d', String(MAX_TOWELS)), 'warn');
			return;
		}
		this.picks.push(id);
		this._render();
		// When the package just filled up, bring the summary into view.
		if (this.picks.length === this._qty()) {
			this._scrollTo(this.summaryEl || this.ctaBtn);
		}
	}

	/**
	 * Add a motif picked outside the builder — the grid further up the page —
	 * and bring the builder into view.
	 *
	 * The grid is where enthusiasm peaks, but its buttons are nowhere near the
	 * slots, so a silent add reads as a dead button. Every path here ends in
	 * either a scroll or a toast, and usually both.
	 *
	 * @param {string} id Motif id, as printed on the grid button.
	 * @param {string} [pkg] Package id to switch to first, when the button was
	 *   the card's per-piece price and is therefore arguing for a size.
	 */
	addMotifFromGallery(id, pkg = '') {
		if (!this.selected || !this._motifById(id)) return;

		// Before the add: selectTier truncates picks to the new size, so
		// switching afterwards could drop the towel that was just added.
		if (pkg) {
			const tier = this.tiers.find((t) => t.dataset.package === pkg);
			if (tier && tier !== this.selected) this.selectTier(tier);
		}

		const before = this.picks.length;
		this.addMotif(id);

		// Nothing landed: the bundle is at its limit, and addMotif has said so.
		if (this.picks.length === before) {
			this._scrollTo(this.step2El);
			return;
		}

		const remaining = this._qty() - this.picks.length;
		if (remaining === 0) {
			// addMotif scrolled to the summary — this click filled the package.
			this._toast(this.l10n.bundleReady);
			return;
		}

		if (remaining < 0) {
			// Past the package: a slot it unlocked.
			this._toast(this.l10n.bundleExtra.replace('%d', String(this.picks.length)));
			this._scrollTo(this.summaryEl || this.ctaBtn);
			return;
		}

		this._toast(
			this.l10n.bundleAdded.replace('%d', String(remaining)) +
				' ' +
				(remaining === 1 ? this.l10n.motifOne : this.l10n.motifMany)
		);
		this._scrollTo(this.step2El);
	}

	removeSlot(index) {
		if (index < 0 || index >= this.picks.length) return;

		this.picks.splice(index, 1);
		this._render();

		// _render rebuilds the slot row, destroying the button that was focused
		// and dropping focus to <body>. Put it back on the nearest remaining
		// remove button, or on the gallery if the row is now empty.
		const removes = this.slotsEl
			? Array.from(this.slotsEl.querySelectorAll('[data-slot-remove]'))
			: [];
		const next = removes[Math.min(index, removes.length - 1)];
		if (next) {
			next.focus();
		} else if (this.motifs.length) {
			this.motifs[0].focus();
		}
	}

	clear() {
		this.picks = [];
		this._render();
	}

	/**
	 * Fill the remaining slots at random.
	 *
	 * Draws without replacement while distinct motifs remain, so "Iznenadi me"
	 * cannot hand back a package of three identical towels — the surprise is meant
	 * to be a variety pack. Falls back to repeats only if the catalogue is
	 * smaller than the package.
	 *
	 * Once the package is full it adds one more, into the slot that unlocked.
	 */
	randomFill() {
		const qty = Math.min(
			MAX_TOWELS,
			this.picks.length >= this._qty() ? this.picks.length + 1 : this._qty()
		);
		const pool = this.motifs
			.map((m) => m.dataset.motifId)
			.filter((id) => !this.picks.includes(id));

		while (this.picks.length < qty && this.motifs.length) {
			if (pool.length) {
				const i = Math.floor(Math.random() * pool.length);
				this.picks.push(pool.splice(i, 1)[0]);
			} else {
				const m = this.motifs[Math.floor(Math.random() * this.motifs.length)];
				this.picks.push(m.dataset.motifId);
			}
		}

		this._render();
		this._scrollTo(this.summaryEl || this.ctaBtn);
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
		if (this.busy) return;

		const qty = this._qty();
		const remaining = qty - this.picks.length;
		if (remaining > 0) {
			this._toast(this.l10n.notFull.replace('%d', String(remaining)), 'warn');
			return;
		}

		const ids = this.picks.slice();

		if (this.selected.dataset.productId) {
			const lines = this._cartLines(ids);
			if (!lines) {
				this._toast(this.l10n.addFailed, 'warn');
				return;
			}
			// The picks are cleared by _wcAdd once the server confirms, so a
			// failed request leaves the user's selection intact to retry.
			this._wcAdd(lines);
			return;
		}

		// Demo cart: one line per bundle, motifs in the name.
		const names = ids.map((id) => this._motifName(id));
		const label = `${this._planName(ids.length)} (${names.join(', ')})`;
		this.root.dispatchEvent(
			new CustomEvent('cosypaw:add', {
				bubbles: true,
				detail: { name: label, price: this._plan(ids.length).total },
			})
		);

		this.picks = [];
		this._render();
	}

	/**
	 * Split the picks into the cart lines that carry them.
	 *
	 * Up to the selected size it is that package, as it always was. Past it,
	 * every full set of the largest package's size is one such package and
	 * each towel left over is the single — never a part-filled package, which
	 * would put a towel on the order that nobody chose. The cart's package
	 * pricing counts the towels across all the lines and bills plan() for
	 * them, so the split changes what the order lists, not what it costs.
	 *
	 * @param {string[]} ids Motif ids.
	 * @returns {Array<{productId:string, motifs:string[]}>|null} Null when a
	 *   towel has no product to travel on.
	 * @private
	 */
	_cartLines(ids) {
		if (ids.length <= this._qty()) {
			return [{ productId: this.selected.dataset.productId, motifs: ids }];
		}

		const tiers = this._tierData();
		const pack = tiers.find((t) => t.qty > 1 && t.el.dataset.productId) || null;
		const single = tiers.find((t) => t.qty === 1 && t.el.dataset.productId) || null;
		const lines = [];
		let rest = ids.slice();

		while (pack && rest.length >= pack.qty) {
			lines.push({ productId: pack.el.dataset.productId, motifs: rest.slice(0, pack.qty) });
			rest = rest.slice(pack.qty);
		}

		for (const id of rest) {
			if (single) {
				lines.push({ productId: single.el.dataset.productId, motifs: [id] });
				continue;
			}
			// No single package: the motif's own product is the same towel.
			const motif = this._motifById(id);
			const own = motif ? motif.dataset.productId : '';
			if (!own) return null;
			lines.push({ productId: own, motifs: [] });
		}

		return lines;
	}

	/**
	 * Add the bundle's lines to the WooCommerce cart, one request each, in order.
	 * Sends motif IDs; the server resolves names + composite thumbnail.
	 *
	 * Should a request fail part-way, the lines already in the cart stay there
	 * and their towels leave the builder, so a retry adds only what is missing.
	 *
	 * @param {Array<{productId:string, motifs:string[]}>} lines
	 * @private
	 */
	_wcAdd(lines) {
		const params = window.wc_add_to_cart_params;
		const $ = window.jQuery;
		if (!params || !$) {
			this._toast(this.l10n.addFailed, 'warn');
			return;
		}

		const url = params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart');
		let added = 0;
		let last = null;

		const post = (line) => {
			const body = new FormData();
			body.append('product_id', line.productId);
			body.append('quantity', '1');
			if (line.motifs.length) body.append('cosypaw_motifs', line.motifs.join(','));

			return fetch(url, { method: 'POST', body, credentials: 'same-origin' })
				.then((r) => {
					if (!r.ok) throw new Error(`HTTP ${r.status}`);
					return r.json();
				})
				.then((res) => {
					if (!res || res.error) {
						throw new Error('cart rejected the request');
					}
					last = res;
					added += Math.max(1, line.motifs.length);
				});
		};

		this._setBusy(true);

		const announce = () => {
			if (!last) return;
			// Let WooCommerce update fragments (badge) + our listener show the toast.
			$(document.body).trigger('added_to_cart', [last.fragments, last.cart_hash, $(this.ctaBtn)]);
		};

		lines
			.reduce((chain, line) => chain.then(() => post(line)), Promise.resolve())
			.then(() => {
				announce();
				this.picks = [];
				this._setBusy(false);
				this._render();
			})
			.catch(() => {
				// Previously swallowed: a failed add left the button unchanged
				// and the user with no indication that nothing had happened.
				announce();
				this.picks = this.picks.slice(added);
				this._setBusy(false);
				this._render();
				this._toast(this.l10n.addFailed, 'warn');
			});
	}

	/**
	 * Pending state for the CTA. Without it the button stayed idle for the whole
	 * round trip, inviting a second click that would add the bundle twice.
	 * @private
	 */
	_setBusy(busy) {
		this.busy = busy;
		if (!this.ctaBtn) return;

		this.ctaBtn.disabled = busy;
		this.ctaBtn.setAttribute('aria-busy', busy ? 'true' : 'false');
		if (busy && this.ctaLabelEl) {
			this.ctaLabelEl.textContent = this.l10n.adding;
		}
	}

	/* ---------- rendering ---------- */

	_fmt(amount) {
		return Number(amount).toLocaleString(this.l10n.locale) + ' ' + this.l10n.currency;
	}

	_render() {
		// Step 2 only hides in the degenerate case of a builder with no tiers.
		if (this.step2El) this.step2El.hidden = !this.selected;
		if (!this.selected) return;

		const qty = this._qty();

		this._renderSlots(this._count());
		this._renderGalleryBadges();

		// The row carries the finished state, so the styling has one hook for it.
		if (this.summaryEl) {
			this.summaryEl.classList.toggle('is-full', this.picks.length >= qty);
		}

		if (this.countEl) this.countEl.textContent = String(this.picks.length);
		if (this.qtyLabelEl) this.qtyLabelEl.textContent = String(this._count());
		if (this.clearBtn) this.clearBtn.hidden = this.picks.length === 0;

		const total = this._plan(this._count()).total;
		this._renderSelection(total);
		this._renderShipping(total);
		this._renderCta(qty, total);
	}

	/**
	 * What the plan for this many towels is called: the selected package's own
	 * name while the bundle is that package, otherwise its parts — "2+1 paket
	 * + Single", "2× 2+1 paket".
	 * @private
	 */
	_planName(count) {
		if (count <= this._qty() && this.selected) return this.selected.dataset.name || '';

		const names = {};
		this._tierData().forEach((t) => {
			names[t.id] = t.name;
		});
		const lines = this._plan(count).lines;

		return this._tierData()
			.filter((t) => lines[t.id])
			.map((t) => (lines[t.id] > 1 ? `${lines[t.id]}× ${names[t.id]}` : names[t.id]))
			.join(' + ');
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

		// The package is full: the next slot is open, and says what the towel
		// in it would add to the total — often nothing, when it completes a
		// package that is already being paid for.
		const n = this.picks.length;
		if (n >= this._qty() && n < MAX_TOWELS) {
			const extra = this._plan(n + 1).total - this._plan(n).total;
			const label = extra <= 0 ? this.l10n.slotFree : this.l10n.slotNext.replace('%s', this._fmt(extra));
			const slot = document.createElement('div');
			slot.className = 'builder-slot is-empty is-extra' + (extra <= 0 ? ' is-free' : '');
			slot.innerHTML =
				'<div class="builder-slot__placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></div>' +
				`<span class="builder-slot__name builder-slot__name--muted">${this._esc(label)}</span>`;
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

	_renderSelection(total) {
		if (!this.selected) return;
		const d = this.selected.dataset;

		// The selected package as the server printed it, until the bundle
		// grows past it; then the plan's own numbers. (Also should the package
		// ever be priced above the singles, where the plan is not the package.)
		if (this.picks.length <= this._qty() && total === (parseInt(d.price || '0', 10) || 0)) {
			if (this.selNameEl) this.selNameEl.textContent = d.name || '';
			if (this.selPriceEl) this.selPriceEl.textContent = d.priceFmt || this._fmt(d.price || 0);
			if (this.selPerEl) this.selPerEl.textContent = d.perFmt || '';
			if (this.selOldEl) {
				this.selOldEl.textContent = d.oldFmt || '';
				this.selOldEl.hidden = !d.oldFmt;
			}
			return;
		}

		const count = this._count();
		const single = this._single();
		const old = single ? single.price * count : 0;

		if (this.selNameEl) this.selNameEl.textContent = this._planName(count);
		if (this.selPriceEl) this.selPriceEl.textContent = this._fmt(total);
		if (this.selPerEl) {
			this.selPerEl.textContent = `${this._fmt(Math.round(total / count))} / ${this.l10n.unitShort}`;
		}
		if (this.selOldEl) {
			// Struck through only while it is more than the total — the same
			// rule the package cards follow.
			this.selOldEl.textContent = old > total ? this._fmt(old) : '';
			this.selOldEl.hidden = !(old > total);
		}
	}

	/**
	 * How far this bundle is from free delivery, beside its price.
	 *
	 * Measured on the bundle alone: what is already in the cart may carry it
	 * over sooner, so this can only understate how close the shopper is, never
	 * promise delivery the cart would charge for.
	 * @private
	 */
	_renderShipping(total) {
		if (!this.shipEl) return;
		if (this.freeMin < 1) {
			this.shipEl.hidden = true;
			return;
		}

		const free = total >= this.freeMin;
		this.shipEl.hidden = false;
		this.shipEl.classList.toggle('is-free', free);

		const text = this.shipEl.querySelector('[data-ship-text]') || this.shipEl;
		text.textContent = free
			? this.l10n.shipFree
			: this.l10n.shipMissing
					.replace('%1$s', this._fmt(this.freeMin - total))
					.replace('%2$s', this._fmt(this.freeMin));
	}

	_renderCta(qty, total) {
		if (!this.ctaBtn || !this.ctaLabelEl) return;
		const remaining = qty - this.picks.length;
		const full = remaining <= 0;
		const priceFmt = this._fmt(total);

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
