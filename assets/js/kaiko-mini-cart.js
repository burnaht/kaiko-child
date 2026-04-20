/**
 * Kaiko Mini-Cart
 *
 * Scene 1 behaviour from kaiko-cart-concept.html, wired to WooCommerce:
 *   - added_to_cart → toast + badge pulse + drawer (180ms delay chain)
 *   - Header cart button → open drawer
 *   - Esc / backdrop / close button → close drawer
 *   - Qty stepper in drawer → admin-ajax update + re-apply fragments
 *   - Non-AJAX POST adds → session flag → drawer on next page load
 */
(function ($) {
	'use strict';

	var cfg = window.kaikoMiniCart || {};

	var doc      = document;
	var drawer   = doc.getElementById('kaiko-drawer');
	var backdrop = doc.getElementById('kaiko-drawer-backdrop');
	var toast    = doc.getElementById('kaiko-toast');

	if (!drawer || !backdrop) return;

	var toastTimer;
	var lastItemCount = currentItemCount();

	function currentItemCount() {
		var badge = doc.querySelector('.kaiko-nav-cart-count');
		if (!badge) return 0;
		var n = parseInt(badge.textContent, 10);
		return isNaN(n) ? 0 : n;
	}


	/* ---------- Drawer open / close ---------- */

	function openDrawer() {
		drawer.classList.add('is-open');
		backdrop.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		backdrop.setAttribute('aria-hidden', 'false');
		doc.body.classList.add('kaiko-cart-open');
		// Move focus to the close button for keyboard users
		var close = doc.getElementById('kaiko-drawer-close');
		if (close) { try { close.focus({ preventScroll: true }); } catch (e) { close.focus(); } }
	}

	function closeDrawer() {
		drawer.classList.remove('is-open');
		backdrop.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		backdrop.setAttribute('aria-hidden', 'true');
		doc.body.classList.remove('kaiko-cart-open');
	}


	/* ---------- Toast + badge pulse ---------- */

	function flashToast(msg) {
		if (!toast) return;
		var msgEl = toast.querySelector('.kaiko-toast__msg');
		if (msgEl && msg) msgEl.innerHTML = msg;
		toast.classList.add('is-visible');
		toast.setAttribute('aria-hidden', 'false');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () {
			toast.classList.remove('is-visible');
			toast.setAttribute('aria-hidden', 'true');
		}, 1800);
	}

	function bumpBadge() {
		var badge = doc.querySelector('.kaiko-nav-cart-count');
		if (!badge) return;
		badge.classList.remove('just-added');
		// Force reflow so the animation restarts even on rapid adds.
		// eslint-disable-next-line no-unused-expressions
		void badge.offsetWidth;
		badge.classList.add('just-added');
	}

	function flashJustAddedItem() {
		var items = drawer.querySelectorAll('.kaiko-drawer__item');
		if (!items.length) return;
		var last = items[items.length - 1];
		last.classList.remove('just-added');
		void last.offsetWidth;
		last.classList.add('just-added');
	}


	/* ---------- The add-to-cart celebration chain ---------- */

	function runAddChain(toastMsg) {
		flashToast(toastMsg);
		bumpBadge();
		flashJustAddedItem();
		setTimeout(openDrawer, 180);
	}


	/* ---------- WooCommerce fragments listener ---------- */
	// WC dispatches `added_to_cart` on <body> after a successful AJAX add.
	// The fragments we returned from PHP are already applied by WC when this
	// fires, so the drawer body reflects the new cart state.
	$(doc.body).on('added_to_cart', function (e, fragments, cart_hash, $button) {
		var msg = 'Added to cart';
		try {
			// Derive the most-recent product name from the ATC button for the toast.
			if ($button && $button.length) {
				var name = $button.data('kaiko-name')
					|| $button.attr('aria-label')
					|| ($button.closest('.product').find('.woocommerce-loop-product__title').first().text() || '').trim()
					|| ($button.closest('li.product, .kaiko-product-card').find('.kaiko-product-card-title').first().text() || '').trim();
				if (name) {
					msg = 'Added to cart — <strong>' + escapeHtml(name) + '</strong>';
				}
			}
		} catch (err) { /* non-fatal */ }

		runAddChain(msg);
		lastItemCount = currentItemCount();
	});

	// Also react to other cart-change events so the drawer stays current
	// without opening unasked. (fragments are already applied by WC)
	$(doc.body).on('wc_fragments_refreshed wc_fragments_loaded removed_from_cart updated_cart_totals', function () {
		lastItemCount = currentItemCount();
	});


	/* ---------- Clicks: open / close ---------- */

	doc.addEventListener('click', function (e) {
		var t = e.target;
		if (!t) return;
		var opener = t.closest && t.closest('[data-kaiko-open-cart]');
		if (opener) { e.preventDefault(); openDrawer(); return; }

		if (t.closest && t.closest('#kaiko-drawer-close')) { e.preventDefault(); closeDrawer(); return; }
	});

	backdrop.addEventListener('click', closeDrawer);

	doc.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && drawer.classList.contains('is-open')) closeDrawer();
	});


	/* ---------- Qty stepper inside drawer ---------- */

	drawer.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.kaiko-drawer__qtymini button') : null;
		if (!btn) return;
		e.preventDefault();

		var wrap = btn.closest('.kaiko-drawer__qtymini');
		var key  = wrap && wrap.getAttribute('data-cart-item-key');
		var qEl  = wrap && wrap.querySelector('.q');
		if (!key || !qEl) return;

		var current = parseInt(qEl.textContent, 10) || 0;
		var action  = btn.getAttribute('data-action');
		var next    = action === 'dec' ? Math.max(0, current - 1) : current + 1;
		if (next === current) return;

		updateQty(wrap, key, next);
	});

	function updateQty(wrap, key, qty) {
		if (!cfg.ajaxUrl || !cfg.nonce) return;
		wrap.classList.add('is-busy');

		var body = new URLSearchParams();
		body.append('action', 'kaiko_update_cart_qty');
		body.append('nonce', cfg.nonce);
		body.append('cart_item_key', key);
		body.append('qty', String(qty));

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
		.then(function (r) { return r.json(); })
		.then(function (res) {
			if (!res || !res.success) return;
			applyFragments(res.data && res.data.fragments);
			// Let listeners know (price displays, recent-view modules etc.)
			$(doc.body).trigger('updated_cart_totals');
			$(doc.body).trigger('wc_fragments_refreshed');
		})
		.catch(function () { /* swallow */ })
		.finally(function () {
			wrap.classList.remove('is-busy');
		});
	}

	function applyFragments(fragments) {
		if (!fragments || typeof fragments !== 'object') return;
		Object.keys(fragments).forEach(function (selector) {
			var html = fragments[selector];
			doc.querySelectorAll(selector).forEach(function (node) {
				var temp = doc.createElement('template');
				temp.innerHTML = html;
				var fresh = temp.content.firstElementChild;
				if (fresh) node.replaceWith(fresh);
			});
		});
	}


	/* ---------- Non-AJAX POST fallback ---------- */

	function fireDelayedAddChain() {
		// Wait a tick so fragments are rendered by the browser.
		setTimeout(function () { runAddChain('Added to cart'); }, 120);
	}

	// Server-side session flag (set by woocommerce_add_to_cart on POST).
	if (cfg.justAdded) {
		if (doc.readyState === 'loading') {
			doc.addEventListener('DOMContentLoaded', fireDelayedAddChain);
		} else {
			fireDelayedAddChain();
		}
	}

	// URL param belt-and-braces (some integrations append ?added_to_cart=ID).
	try {
		var params = new URLSearchParams(window.location.search);
		if (params.has('added_to_cart')) {
			if (doc.readyState === 'loading') {
				doc.addEventListener('DOMContentLoaded', fireDelayedAddChain);
			} else {
				fireDelayedAddChain();
			}
			params.delete('added_to_cart');
			params.delete('_wpnonce');
			var qs = params.toString();
			var clean = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
			if (window.history && window.history.replaceState) {
				window.history.replaceState({}, '', clean);
			}
		}
	} catch (err) { /* non-fatal */ }


	/* ---------- Utils ---------- */

	function escapeHtml(str) {
		return String(str).replace(/[&<>"']/g, function (c) {
			return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
		});
	}
})(window.jQuery);
