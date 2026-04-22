/**
 * Kaiko — Shared Header JS
 *
 * Owns behavior for the shared nav partial (template-parts/kaiko-header.php):
 *   - Mobile hamburger open/close (overlay + slide-out panel)
 *   - Scroll-shrink shadow on the fixed nav
 *
 * Idempotent: safe to load on every page. Every branch no-ops silently if
 * the partial's markup isn't on the page.
 *
 * @package KaikoChild
 */
(function () {
	'use strict';

	function init() {
		var hamburger  = document.getElementById( 'kaiko-nav-hamburger' );
		var mobileMenu = document.getElementById( 'kaiko-mobile-menu' );
		var overlay    = document.getElementById( 'kaiko-mobile-overlay' );
		var closeBtn   = document.getElementById( 'kaiko-mobile-close' );
		var nav        = document.getElementById( 'kaiko-nav' );

		if ( hamburger && mobileMenu ) {
			function openMenu() {
				hamburger.classList.add( 'open' );
				hamburger.setAttribute( 'aria-expanded', 'true' );
				mobileMenu.classList.add( 'open' );
				mobileMenu.setAttribute( 'aria-hidden', 'false' );
				if ( overlay ) overlay.classList.add( 'active' );
				document.body.style.overflow = 'hidden';
			}

			function closeMenu() {
				hamburger.classList.remove( 'open' );
				hamburger.setAttribute( 'aria-expanded', 'false' );
				mobileMenu.classList.remove( 'open' );
				mobileMenu.setAttribute( 'aria-hidden', 'true' );
				if ( overlay ) overlay.classList.remove( 'active' );
				document.body.style.overflow = '';
			}

			hamburger.addEventListener( 'click', function () {
				if ( mobileMenu.classList.contains( 'open' ) ) closeMenu();
				else openMenu();
			} );

			if ( closeBtn ) closeBtn.addEventListener( 'click', closeMenu );
			if ( overlay ) overlay.addEventListener( 'click', closeMenu );

			document.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' && mobileMenu.classList.contains( 'open' ) ) closeMenu();
			} );
		}

		if ( nav ) {
			window.addEventListener( 'scroll', function () {
				nav.classList.toggle( 'scrolled', window.scrollY > 10 );
			}, { passive: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
