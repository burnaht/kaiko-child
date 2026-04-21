/**
 * Kaiko — Cart page interactions
 *
 * Shares the ajax_url + nonce localised by the mini-cart PR
 * (window.kaikoMiniCart) so endpoints and fragments stay consistent.
 *
 * Remove × + undo-toast logic is handled in kaiko-mini-cart.js so the
 * drawer's × button works on every page (this file only loads on /cart/).
 *
 * Binds:
 *   - Qty steppers (and qty input)            → kaiko_update_cart_qty
 *   - Tier nudge "Add N more"                  → kaiko_update_cart_qty (to next tier min)
 *   - Cross-sell +                             → add-to-cart URL (native WC form)
 *   - Promo code Apply                         → kaiko_apply_coupon
 *   - Coupon chip ×                            → kaiko_remove_coupon
 */
(function () {
	'use strict';

	var cfg = window.kaikoMiniCart || {};
	if (!cfg.ajaxUrl || !cfg.nonce) return;

	var QTY_DEBOUNCE_MS = 350;
	var doc = document;

	var qtyTimers = Object.create(null); // per-cart-item-key debounce


	/* ---------- Fragment application ---------- */

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
		syncSavingsRow();
	}

	// Toggle the savings row based on the fragment's amount text.
	function syncSavingsRow() {
		var amt = doc.querySelector('.kaiko-cart-summary__savings__amt');
		if (!amt) return;
		var row = amt.closest('.kaiko-cart-summary__row');
		if (!row) return;
		var hasSaving = /[1-9]/.test(amt.textContent || '');
		if (hasSaving) {
			row.removeAttribute('hidden');
		} else {
			row.setAttribute('hidden', '');
		}
	}


	/* ---------- AJAX helpers ---------- */

	function post(action, extras) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', cfg.nonce);
		Object.keys(extras || {}).forEach(function (k) {
			if (extras[k] !== undefined && extras[k] !== null) body.append(k, String(extras[k]));
		});
		return fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (r) { return r.json(); });
	}


	/* ---------- Qty stepper ---------- */

	function bindStepperClicks(e) {
		var btn = e.target.closest ? e.target.closest('.kaiko-qty-stepper button') : null;
		if (!btn) return;
		e.preventDefault();

		var wrap = btn.closest('.kaiko-qty-stepper');
		var input = wrap && wrap.querySelector('input');
		var key = wrap && wrap.getAttribute('data-cart-item-key');
		if (!wrap || !input || !key) return;

		var current = parseInt(input.value, 10) || 0;
		var action = btn.getAttribute('data-action');
		var next = action === 'dec' ? Math.max(0, current - 1) : current + 1;
		if (next === current) return;

		input.value = String(next);
		scheduleQtyUpdate(wrap, key, next);
	}

	function bindStepperInput(e) {
		var input = e.target;
		if (!input || !input.matches || !input.matches('.kaiko-qty-stepper input')) return;
		var wrap = input.closest('.kaiko-qty-stepper');
		var key = wrap && wrap.getAttribute('data-cart-item-key');
		if (!wrap || !key) return;
		var v = Math.max(0, parseInt(input.value, 10) || 0);
		scheduleQtyUpdate(wrap, key, v);
	}

	function scheduleQtyUpdate(wrap, key, qty) {
		clearTimeout(qtyTimers[key]);
		qtyTimers[key] = setTimeout(function () {
			doQtyUpdate(wrap, key, qty);
		}, QTY_DEBOUNCE_MS);
	}

	function doQtyUpdate(wrap, key, qty) {
		if (!wrap) return;
		wrap.classList.add('is-busy');
		post('kaiko_update_cart_qty', { cart_item_key: key, qty: qty })
			.then(function (res) {
				if (!res || !res.success) return;
				applyFragments(res.data && res.data.fragments);
				if (res.data && res.data.count === 0) maybeSwapEmpty();
			})
			.catch(function () {})
			.finally(function () { wrap.classList.remove('is-busy'); });
	}


	/* ---------- Tier nudge ---------- */

	function bindTierNudge(e) {
		var nudge = e.target.closest ? e.target.closest('.kaiko-tier-nudge') : null;
		if (!nudge) return;
		e.preventDefault();
		var key = nudge.getAttribute('data-cart-item-key');
		var nextQty = parseInt(nudge.getAttribute('data-next-qty'), 10);
		if (!key || !nextQty) return;
		// Optimistic UI: bump the stepper input immediately.
		var wrap = doc.querySelector('.kaiko-qty-stepper[data-cart-item-key="' + CSS.escape(key) + '"]');
		if (wrap) {
			var input = wrap.querySelector('input');
			if (input) input.value = String(nextQty);
			doQtyUpdate(wrap, key, nextQty);
		}
	}


	/* ---------- Empty-state swap ---------- */
	// Used by the qty-stepper path when a decrement lands the cart at zero.
	// Remove × flow lives in kaiko-mini-cart.js and calls its own variant.

	function maybeSwapEmpty() {
		var wrap = doc.querySelector('.kaiko-cart-wrap');
		if (!wrap) return;
		wrap.style.transition = 'opacity 320ms ease';
		wrap.style.opacity = '0';
		setTimeout(function () { window.location.reload(); }, 340);
	}


	/* ---------- Cross-sell +  ---------- */

	function bindUpsellAdd(e) {
		var btn = e.target.closest ? e.target.closest('.kaiko-cart-upsell__item__add') : null;
		if (!btn) return;
		e.preventDefault();
		e.stopPropagation();
		var pid = parseInt(btn.getAttribute('data-product-id'), 10);
		if (!pid) return;
		btn.disabled = true;
		// Use WC's built-in AJAX add-to-cart (hooked to wc-add-to-cart).
		var body = new URLSearchParams();
		body.append('product_id', String(pid));
		body.append('quantity', '1');
		fetch(cfg.ajaxUrl + '?action=woocommerce_add_to_cart', {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
		.then(function (r) { return r.json(); })
		.then(function (res) {
			btn.disabled = false;
			if (!res || res.error) return;
			if (res.fragments) applyFragments(res.fragments);
			// WC dispatches added_to_cart on body — mini-cart JS handles toast + drawer
			if (window.jQuery) window.jQuery(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash, window.jQuery(btn)]);
		})
		.catch(function () { btn.disabled = false; });
	}


	/* ---------- Coupons ---------- */

	function bindCouponApply(e) {
		var btn = e.target.closest ? e.target.closest('.kaiko-cart-actions__promo button') : null;
		if (!btn) return;
		var form = btn.closest('form');
		var input = form && form.querySelector('input[name="coupon_code"]');
		if (!input) return;
		e.preventDefault();
		var code = (input.value || '').trim();
		if (!code) return;

		btn.disabled = true;
		post('kaiko_apply_coupon', { code: code })
			.then(function (res) {
				btn.disabled = false;
				if (!res || !res.success) {
					showNotice('error', (res && res.data && res.data.message) || 'Coupon could not be applied');
					return;
				}
				input.value = '';
				applyFragments(res.data && res.data.fragments);
				showNotice('success', 'Coupon applied');
			})
			.catch(function () { btn.disabled = false; });
	}

	function bindCouponRemove(e) {
		var btn = e.target.closest ? e.target.closest('.kaiko-coupon-remove') : null;
		if (!btn) return;
		e.preventDefault();
		var code = btn.getAttribute('data-coupon');
		if (!code) return;
		btn.disabled = true;
		post('kaiko_remove_coupon', { code: code })
			.then(function (res) {
				if (res && res.success) applyFragments(res.data && res.data.fragments);
			})
			.catch(function () {});
	}


	/* ---------- Notices (inline, ephemeral) ---------- */

	function showNotice(kind, message) {
		var wrap = doc.querySelector('.kaiko-cart-notices');
		if (!wrap) return;
		var cls = kind === 'error' ? 'woocommerce-error' : kind === 'info' ? 'woocommerce-info' : 'woocommerce-message';
		var el = doc.createElement('div');
		el.className = cls;
		el.textContent = message;
		wrap.innerHTML = '';
		wrap.appendChild(el);
		setTimeout(function () {
			if (el.parentNode === wrap) wrap.removeChild(el);
		}, 4000);
	}


	/* ---------- Init ---------- */

	doc.addEventListener('click', function (e) {
		bindStepperClicks(e);
		bindTierNudge(e);
		bindUpsellAdd(e);
		bindCouponApply(e);
		bindCouponRemove(e);
	});

	doc.addEventListener('change', bindStepperInput);
	doc.addEventListener('input', bindStepperInput);

	// Run the savings-row sync on load.
	syncSavingsRow();
})();
