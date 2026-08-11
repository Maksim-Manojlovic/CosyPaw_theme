/**
 * Marquee
 *
 * Pause/resume control for the announcement bar.
 *
 * WCAG 2.2.2 requires a pause, stop or hide mechanism for any motion that
 * starts automatically, lasts more than five seconds and runs alongside other
 * content. The bar scrolls on a 40s infinite loop, so honouring
 * prefers-reduced-motion is not sufficient on its own — that setting only
 * reaches users who have found and enabled it at the OS level.
 *
 * The preference is remembered for the session, since a user who stops the
 * motion once should not have to stop it again on the next page.
 *
 * Markup contract (header.php):
 *   <div class="announce" data-marquee>
 *     <div class="announce__track" aria-hidden="true">…</div>
 *     <button data-marquee-toggle aria-pressed="false">
 */
export class Marquee {
	static STORAGE_KEY = 'cosypaw_marquee_paused';

	/**
	 * @param {HTMLElement} root The [data-marquee] element.
	 * @param {object} [l10n] Localized strings (window.CosyPawL10n).
	 */
	constructor(root, l10n = {}) {
		if (!(root instanceof HTMLElement)) {
			throw new TypeError('Marquee requires its root element.');
		}

		this.root = root;
		this.toggle = root.querySelector('[data-marquee-toggle]');
		if (!this.toggle) return;

		this.l10n = Object.assign(
			{ marqueePause: 'Pauziraj najave', marqueePlay: 'Pusti najave' },
			l10n || {}
		);

		this._onToggle = this._onToggle.bind(this);
		this.toggle.addEventListener('click', this._onToggle);

		this.toggle.hidden = false;
		this._apply(this._stored());
	}

	/* ---------- persistence ---------- */

	_stored() {
		try {
			return sessionStorage.getItem(Marquee.STORAGE_KEY) === '1';
		} catch {
			return false;
		}
	}

	_store(paused) {
		try {
			sessionStorage.setItem(Marquee.STORAGE_KEY, paused ? '1' : '0');
		} catch {
			/* ignore quota / disabled storage */
		}
	}

	/* ---------- state ---------- */

	get isPaused() {
		return this.root.hasAttribute('data-marquee-paused');
	}

	_apply(paused) {
		this.root.toggleAttribute('data-marquee-paused', paused);
		this.toggle.setAttribute('aria-pressed', paused ? 'true' : 'false');
		this.toggle.setAttribute(
			'aria-label',
			paused ? this.l10n.marqueePlay : this.l10n.marqueePause
		);
	}

	pause() {
		this._apply(true);
		this._store(true);
	}

	play() {
		this._apply(false);
		this._store(false);
	}

	_onToggle(e) {
		e.preventDefault();
		if (this.isPaused) {
			this.play();
		} else {
			this.pause();
		}
	}

	destroy() {
		this.toggle.removeEventListener('click', this._onToggle);
	}
}

export default Marquee;
