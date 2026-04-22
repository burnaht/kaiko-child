/**
 * Kaiko — Shared Header JS
 *
 * Owns behavior for the shared nav partial (template-parts/kaiko-header.php):
 *   - Mobile hamburger open/close (overlay + slide-out panel)
 *   - Scroll-shrink shadow on the fixed nav
 *   - Homepage-only WoodMart style override (see note below)
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

		// Homepage-only WoodMart override. Ported from the inline block that
		// used to live in template-homepage.php — the WoodMart parent theme
		// was leaking styles into .kaiko-nav on the front page. Keep gated on
		// the body class until we can prove on live that kaiko-shell.css
		// holds on its own; if so, delete this whole branch in a follow-up.
		if ( nav && document.body.classList.contains( 'kaiko-homepage' ) ) {
			var isMobile = window.innerWidth <= 768;
			var navPad   = isMobile ? '0 1.5rem' : '0 6rem';
			var navH     = isMobile ? '64px' : '72px';

			nav.style.cssText =
				'position:fixed!important;top:0!important;left:0!important;right:0!important;' +
				'z-index:1000!important;background:rgba(255,255,255,0.97)!important;' +
				'backdrop-filter:blur(20px)!important;-webkit-backdrop-filter:blur(20px)!important;' +
				'border-bottom:1px solid #e4e2de!important;padding:' + navPad + '!important;' +
				'height:' + navH + '!important;display:flex!important;align-items:center!important;' +
				'justify-content:space-between!important;';

			var navLogo = document.querySelector( '.kaiko-nav-logo' );
			if ( navLogo ) {
				navLogo.style.cssText =
					"font-family:'Gotham','Gotham Bold',-apple-system,sans-serif!important;" +
					'font-size:1.75rem!important;font-weight:700!important;' +
					'letter-spacing:-0.03em!important;color:#1a1a1a!important;text-decoration:none!important;';
			}

			var navLinks = document.querySelector( '.kaiko-nav-links' );
			if ( navLinks && ! isMobile ) {
				navLinks.style.cssText = 'display:flex!important;gap:6rem!important;align-items:center!important;';

				var links = navLinks.querySelectorAll( 'a:not(.kaiko-nav-cta)' );
				links.forEach( function ( a ) {
					a.style.cssText =
						"font-family:'Inter',-apple-system,sans-serif!important;" +
						'font-size:0.9rem!important;font-weight:500!important;color:#6b6963!important;' +
						'text-decoration:none!important;letter-spacing:0.025em!important;transition:color 0.2s!important;';
				} );

				var cta = navLinks.querySelector( '.kaiko-nav-cta' );
				if ( cta ) {
					cta.style.cssText =
						'background:#1a5c52!important;color:#fff!important;padding:8px 20px!important;' +
						'border-radius:6px!important;font-size:0.85rem!important;' +
						'text-transform:uppercase!important;letter-spacing:0.05em!important;' +
						'text-decoration:none!important;font-weight:600!important;';
				}
			}
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
