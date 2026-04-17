/**
 * Kaiko Shop Archive — filters + drawer
 *
 * Debounced AJAX filter on input change (with graceful fallback to native
 * form-submit if kaikoData isn't available). Also handles the mobile
 * filter drawer and the "Clear filters" action.
 */
(function () {
	'use strict';

	var form   = document.getElementById('kaiko-shop-filters-form');
	var grid   = document.getElementById('kaiko-product-grid');
	var count  = document.getElementById('kaiko-product-count');
	var count2 = document.getElementById('kaiko-product-count-inline');
	var reset  = document.getElementById('kaiko-filter-reset');

	var sidebar  = document.getElementById('kaiko-shop-filters');
	var backdrop = document.getElementById('kaiko-shop-sidebar-backdrop');
	var toggle   = document.getElementById('kaiko-filter-toggle');
	var close    = document.getElementById('kaiko-filter-close');

	if (!form || !grid) return;

	// --- Drawer (mobile) --------------------------------------------------
	function openDrawer() {
		if (!sidebar) return;
		sidebar.classList.add('is-open');
		if (backdrop) backdrop.classList.add('is-open');
		toggle && toggle.setAttribute('aria-expanded', 'true');
		document.body.style.overflow = 'hidden';
	}

	function closeDrawer() {
		if (!sidebar) return;
		sidebar.classList.remove('is-open');
		if (backdrop) backdrop.classList.remove('is-open');
		toggle && toggle.setAttribute('aria-expanded', 'false');
		document.body.style.overflow = '';
	}

	if (toggle)   toggle.addEventListener('click', openDrawer);
	if (close)    close.addEventListener('click', closeDrawer);
	if (backdrop) backdrop.addEventListener('click', closeDrawer);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
			closeDrawer();
		}
	});

	// --- State helpers ----------------------------------------------------
	function getState() {
		var fd = new FormData(form);
		return {
			category:   (fd.get('category')   || '').toString().trim(),
			species:    (fd.get('species')    || '').toString().trim(),
			difficulty: (fd.get('difficulty') || '').toString().trim(),
			min_price:  (fd.get('min_price')  || '').toString().trim(),
			max_price:  (fd.get('max_price')  || '').toString().trim(),
			orderby:    (fd.get('orderby')    || 'date').toString().trim()
		};
	}

	function updateUrl(state) {
		if (!window.history || !window.history.replaceState) return;
		var url = new URL(window.location.href);
		Object.keys(state).forEach(function (key) {
			if (state[key] && !(key === 'orderby' && state[key] === 'date')) {
				url.searchParams.set(key, state[key]);
			} else {
				url.searchParams.delete(key);
			}
		});
		url.searchParams.delete('paged');
		window.history.replaceState({}, '', url.toString());
	}

	function setCount(n) {
		var txt = String(n);
		if (count)  count.textContent  = txt;
		if (count2) count2.textContent = txt;
	}

	// --- AJAX fetch -------------------------------------------------------
	var ajaxAvailable = !!(window.kaikoData && window.kaikoData.ajaxUrl && window.kaikoData.nonce);
	var requestToken = 0;
	var debounceTimer;

	function runFilter(options) {
		if (!ajaxAvailable) {
			// No REST creds — let the form submit drive page reload.
			form.submit();
			return;
		}

		var state = getState();
		updateUrl(state);

		var body = new URLSearchParams();
		body.append('action', 'kaiko_filter_products');
		body.append('nonce', window.kaikoData.nonce);
		Object.keys(state).forEach(function (key) {
			if (state[key]) body.append(key, state[key]);
		});
		body.append('paged', '1');
		body.append('per_page', '12');

		var token = ++requestToken;
		grid.classList.add('is-loading');

		fetch(window.kaikoData.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
		.then(function (r) { return r.json(); })
		.then(function (res) {
			if (token !== requestToken) return; // stale response
			if (!res || !res.success) return;

			grid.innerHTML = res.data.html || '';
			setCount(res.data.found || 0);
		})
		.catch(function () { /* swallow — form submit is fallback */ })
		.finally(function () {
			if (token === requestToken) grid.classList.remove('is-loading');
			if (options && options.closeDrawerAfter) closeDrawer();
		});
	}

	function schedule(options) {
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(function () { runFilter(options || {}); }, 220);
	}

	// --- Bindings ---------------------------------------------------------
	form.addEventListener('change', function (e) {
		if (e.target && e.target.matches('input, select')) {
			schedule();
		}
	});

	form.addEventListener('input', function (e) {
		// Price inputs: debounce only on typing
		if (e.target && e.target.matches('input[type="number"]')) {
			schedule();
		}
	});

	form.addEventListener('submit', function (e) {
		if (ajaxAvailable) {
			e.preventDefault();
			runFilter({ closeDrawerAfter: true });
		}
		// else: native submit navigates with URL params
	});

	if (reset) {
		reset.addEventListener('click', function () {
			// Reset form fields
			form.reset();
			Array.prototype.forEach.call(
				form.querySelectorAll('input[type="radio"][value=""]'),
				function (r) { r.checked = true; }
			);
			Array.prototype.forEach.call(
				form.querySelectorAll('input[type="number"]'),
				function (n) { n.value = ''; }
			);
			var sort = form.querySelector('[name="orderby"]');
			if (sort) sort.value = 'date';
			runFilter();
		});
	}
})();
