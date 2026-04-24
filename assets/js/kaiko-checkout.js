/**
 * Kaiko — Checkout coupon apply / remove
 *
 * The Coupon card inside woocommerce/checkout/form-checkout.php renders
 * its Apply button as <button type="button">, NOT as a submit inside a
 * nested <form class="checkout_coupon">. That's so the whole thing can
 * sit inside the main <form class="checkout"> without HTML5 auto-closing
 * the parent on a nested form (which would break every subsequent
 * card's submit).
 *
 * Apply is therefore wired up here: POST to wc-ajax=apply_coupon,
 * trigger update_checkout on success so WC re-renders the order review
 * with the new discount, then surface any error inline on the coupon
 * input. The "remove" action is handled the same way for the "[Remove]"
 * link WC renders inside each coupon row of the totals section.
 *
 * wc_checkout_params is localised by WC on the checkout page and
 * exposes:
 *   - wc_ajax_url:       template URL with %%endpoint%% placeholder
 *   - apply_coupon_nonce
 *   - remove_coupon_nonce
 *   - i18n_checkout_error, i18n_coupon_applied etc.
 *
 * We depend on 'wc-checkout' in the enqueue so WC's own checkout.js has
 * initialised and wc_checkout_params is in scope before we bind.
 */

( function ( $ ) {
	'use strict';

	if ( ! window.jQuery ) {
		return;
	}

	function ajaxUrl( endpoint ) {
		if ( window.wc_checkout_params && window.wc_checkout_params.wc_ajax_url ) {
			return window.wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', endpoint );
		}
		// Fallback — builds the canonical WC AJAX URL. Used if
		// wc_checkout_params isn't localised (unusual on /checkout/).
		var base = ( window.wc_cart_fragments_params && window.wc_cart_fragments_params.wc_ajax_url )
			|| '/?wc-ajax=%%endpoint%%';
		return base.toString().replace( '%%endpoint%%', endpoint );
	}

	function nonce( name ) {
		return ( window.wc_checkout_params && window.wc_checkout_params[ name ] ) || '';
	}

	function setButtonBusy( $btn, busy ) {
		if ( ! $btn || ! $btn.length ) return;
		if ( busy ) {
			$btn.attr( 'aria-busy', 'true' ).prop( 'disabled', true );
			if ( ! $btn.data( 'kaiko-original-text' ) ) {
				$btn.data( 'kaiko-original-text', $btn.text() );
			}
			$btn.text( 'Applying…' );
		} else {
			$btn.removeAttr( 'aria-busy' ).prop( 'disabled', false );
			var original = $btn.data( 'kaiko-original-text' );
			if ( original ) {
				$btn.text( original );
			}
		}
	}

	function showCouponError( $card, message ) {
		var $existing = $card.find( '.kaiko-co-coupon__error' );
		if ( ! message ) {
			$existing.remove();
			return;
		}
		if ( $existing.length ) {
			$existing.text( message );
			return;
		}
		$card.append( $( '<div class="kaiko-co-coupon__error" role="alert"></div>' ).text( message ) );
	}

	function applyCoupon( $card ) {
		var $input = $card.find( '#kaiko_coupon_code' );
		var $btn   = $card.find( '[data-kaiko-apply-coupon]' );
		var code   = $.trim( $input.val() || '' );

		showCouponError( $card, '' );

		if ( ! code ) {
			$input.trigger( 'focus' );
			return;
		}

		setButtonBusy( $btn, true );

		$.ajax( {
			type: 'POST',
			url:  ajaxUrl( 'apply_coupon' ),
			data: {
				security:    nonce( 'apply_coupon_nonce' ),
				coupon_code: code
			},
			dataType: 'html'
		} ).done( function ( response ) {
			// WC returns HTML notices; strip tags for the inline error
			// surface, and trigger update_checkout so the review redraws
			// with the applied discount (or without, on failure).
			$( '.woocommerce-error, .woocommerce-message, .is-error, .is-success' ).remove();
			if ( response ) {
				var $wrap = $( '<div>' ).html( response );
				var $err  = $wrap.find( '.woocommerce-error' );
				if ( $err.length ) {
					showCouponError( $card, $.trim( $err.text() ) );
				} else {
					$input.val( '' );
				}
			}
			$( document.body ).trigger( 'applied_coupon_in_checkout', [ code ] );
			$( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );
		} ).fail( function () {
			showCouponError( $card, 'Could not apply coupon — please try again.' );
		} ).always( function () {
			setButtonBusy( $btn, false );
		} );
	}

	function removeCoupon( code ) {
		if ( ! code ) return;
		$.ajax( {
			type: 'POST',
			url:  ajaxUrl( 'remove_coupon' ),
			data: {
				security: nonce( 'remove_coupon_nonce' ),
				coupon:   code
			},
			dataType: 'html'
		} ).done( function () {
			$( document.body ).trigger( 'removed_coupon_in_checkout', [ code ] );
			$( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );
		} );
	}

	$( function () {
		// Apply button click
		$( document.body ).on( 'click', '[data-kaiko-apply-coupon]', function ( e ) {
			e.preventDefault();
			var $card = $( this ).closest( '.kaiko-co-card--coupon' );
			applyCoupon( $card );
		} );

		// Enter in the coupon input
		$( document.body ).on( 'keydown', '#kaiko_coupon_code', function ( e ) {
			if ( e.key === 'Enter' || e.keyCode === 13 ) {
				e.preventDefault();
				var $card = $( this ).closest( '.kaiko-co-card--coupon' );
				applyCoupon( $card );
			}
		} );

		// [Remove] link inside coupon row of the order totals
		$( document.body ).on( 'click', '.woocommerce-remove-coupon', function ( e ) {
			e.preventDefault();
			var code = $( this ).data( 'coupon' );
			removeCoupon( code );
		} );
	} );

} )( window.jQuery );
