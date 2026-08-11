/**
 * SiteNav
 *
 * Collapses the header navigation into a toggle-driven panel on narrow
 * viewports. Replaces the previous `display:none` rule, which hid the nav links
 * below 560px with nothing to open in their place — the site's sections were
 * simply unreachable on a phone.
 *
 * Progressive enhancement: the toggle ships `hidden` and the panel is only
 * collapsed once this script marks the header `data-nav-enhanced`, so a failed
 * script leaves a plain, fully visible nav rather than a dead button.
 *
 * Markup contract (header.php):
 *   <header class="site-header" data-site-nav>
 *     <nav class="nav">
 *       ...
 *       <div class="nav__links" id="cosypaw-nav-menu" data-nav-panel>…</div>
 *       <button data-nav-toggle aria-controls="cosypaw-nav-menu" aria-expanded="false" hidden>
 *     </nav>
 *   </header>
 */
export class SiteNav {
	/** Viewport at or below which the nav collapses. Matches the theme's
	 *  single-column breakpoint in landing.css / content.css. */
	static BREAKPOINT = 880;

	/**
	 * @param {HTMLElement} root The [data-site-nav] header element.
	 */
	constructor(root) {
		if (!(root instanceof HTMLElement)) {
			throw new TypeError('SiteNav requires its root element.');
		}

		this.root = root;
		this.panel = root.querySelector('[data-nav-panel]');
		this.toggle = root.querySelector('[data-nav-toggle]');

		if (!this.panel || !this.toggle) {
			return;
		}

		this.mq = window.matchMedia(`(max-width: ${SiteNav.BREAKPOINT}px)`);

		this._onToggle = this._onToggle.bind(this);
		this._onKeydown = this._onKeydown.bind(this);
		this._onDocClick = this._onDocClick.bind(this);
		this._onPanelClick = this._onPanelClick.bind(this);
		this._onBreakpoint = this._onBreakpoint.bind(this);

		this.toggle.hidden = false;
		this.root.setAttribute('data-nav-enhanced', '');

		this.toggle.addEventListener('click', this._onToggle);
		this.panel.addEventListener('click', this._onPanelClick);
		document.addEventListener('keydown', this._onKeydown);
		document.addEventListener('click', this._onDocClick);
		this.mq.addEventListener('change', this._onBreakpoint);

		this.close();
	}

	/* ---------- state ---------- */

	get isOpen() {
		return this.toggle.getAttribute('aria-expanded') === 'true';
	}

	open() {
		this.toggle.setAttribute('aria-expanded', 'true');
		this.root.setAttribute('data-nav-open', '');
	}

	/**
	 * @param {boolean} [restoreFocus=false] Return focus to the toggle — used
	 *        when the user dismisses deliberately (Escape), not on passive
	 *        closes like an outside click or a resize past the breakpoint.
	 */
	close(restoreFocus = false) {
		this.toggle.setAttribute('aria-expanded', 'false');
		this.root.removeAttribute('data-nav-open');
		if (restoreFocus) this.toggle.focus();
	}

	/* ---------- events ---------- */

	_onToggle(e) {
		e.preventDefault();
		if (this.isOpen) {
			this.close();
		} else {
			this.open();
		}
	}

	_onKeydown(e) {
		if (e.key === 'Escape' && this.isOpen) {
			this.close(true);
		}
	}

	_onDocClick(e) {
		if (this.isOpen && !this.root.contains(e.target)) {
			this.close();
		}
	}

	/** Following a link inside the panel should dismiss it (same-page anchors
	 *  navigate without a reload, so the panel would otherwise stay open). */
	_onPanelClick(e) {
		if (this.isOpen && e.target.closest('a')) {
			this.close();
		}
	}

	/** Widening past the breakpoint restores the inline nav; drop the open flag
	 *  so it is not left set when the panel is no longer collapsed. */
	_onBreakpoint(e) {
		if (!e.matches) this.close();
	}

	destroy() {
		this.toggle.removeEventListener('click', this._onToggle);
		this.panel.removeEventListener('click', this._onPanelClick);
		document.removeEventListener('keydown', this._onKeydown);
		document.removeEventListener('click', this._onDocClick);
		this.mq.removeEventListener('change', this._onBreakpoint);
		this.root.removeAttribute('data-nav-enhanced');
		this.root.removeAttribute('data-nav-open');
		this.toggle.hidden = true;
	}
}

export default SiteNav;
