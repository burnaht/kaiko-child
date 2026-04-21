/**
 * Kaiko — My Account interactions.
 *
 * Phase 2:
 *   - Orders list filter chips (client-side row hide/show, no reload)
 */
(function () {
	'use strict';

	window.kaikoAccount = window.kaikoAccount || {};

	var doc = document;

	/* ---- Orders filter chips ---- */

	var chipContainer = doc.querySelector('[data-kaiko-orders-filters]');
	var tbody         = doc.querySelector('[data-kaiko-orders-tbody]');
	if (!chipContainer || !tbody) return;

	var chips = chipContainer.querySelectorAll('.kaiko-chip');
	var rows  = tbody.querySelectorAll('tr[data-status]');

	chipContainer.addEventListener('click', function (e) {
		var chip = e.target.closest ? e.target.closest('.kaiko-chip') : null;
		if (!chip) return;
		var filter = chip.getAttribute('data-status-filter');
		if (!filter) return;

		chips.forEach(function (c) {
			var on = (c === chip);
			c.classList.toggle('active', on);
			c.setAttribute('aria-selected', on ? 'true' : 'false');
		});

		rows.forEach(function (row) {
			if (filter === 'all') {
				row.hidden = false;
				return;
			}
			row.hidden = (row.getAttribute('data-status') !== filter);
		});
	});
})();
