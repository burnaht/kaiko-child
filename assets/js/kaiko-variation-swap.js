/**
 * Kaiko — Variation swap fix (v3).
 *
 * Ported from Code Snippet #29. Enqueued only on single-product pages in
 * functions.php so it doesn't load everywhere.
 *
 * Responsibilities:
 *   - Force-inits WooCommerce's variations_form (some templates bind it
 *     too early and the first selection is a no-op).
 *   - Runs a manual variation matcher that handles the prefix bug where
 *     attribute keys occasionally come through unprefixed.
 *   - Self-contained qty → tier highlight → Add-to-Cart label sync, so
 *     the tier pill matching the current qty lights up and the ATC
 *     button reflects the live total (e.g. "ADD TO CART — £42.00").
 */

( function () {
    if ( ! window.jQuery ) {
        return;
    }
    var $ = window.jQuery;

    function init() {
        var $form = $( '.kaiko-pp-summary form.variations_form' );
        if ( ! $form.length ) {
            $form = $( 'form.variations_form' );
        }
        if ( ! $form.length ) {
            return;
        }

        if ( typeof $form.wc_variation_form === 'function' ) {
            $form.wc_variation_form();
        }

        var variations = $form.data( 'product_variations' );

        // ---- Variation matcher ----
        function readSelections() {
            var sel = {};
            $form.find( '.variations select, .variations_table select' ).each( function () {
                var name = $( this ).attr( 'name' );
                if ( name ) {
                    sel[ name ] = this.value;
                }
            } );
            return sel;
        }

        function matchVariation() {
            if ( ! variations || ! variations.length ) {
                return null;
            }
            var selections = readSelections();
            for ( var i = 0; i < variations.length; i++ ) {
                var v = variations[ i ];
                if ( ! v.attributes ) {
                    continue;
                }
                var ok = true;
                for ( var key in v.attributes ) {
                    var attrVal = v.attributes[ key ];
                    var selVal  = selections[ key ];
                    if ( typeof selVal === 'undefined' ) {
                        selVal = selections[ 'attribute_' + key ];
                    }
                    if ( attrVal !== '' && attrVal !== selVal ) {
                        ok = false;
                        break;
                    }
                }
                if ( ok ) {
                    return v;
                }
            }
            return null;
        }

        // ---- Tier pill rewrite ----
        function rewriteTiers( baseUnit ) {
            if ( ! isFinite( baseUnit ) || baseUnit <= 0 ) {
                return;
            }
            document.querySelectorAll( '.kaiko-pp-tier' ).forEach( function ( cell ) {
                var pct  = parseFloat( cell.getAttribute( 'data-discount-pct' ) ) || 0;
                var unit = Math.round( baseUnit * ( 1 - pct / 100 ) * 100 ) / 100;
                cell.dataset.price = unit.toFixed( 2 );
                var priceEl = cell.querySelector( '.kaiko-pp-tier__price' );
                if ( priceEl ) {
                    priceEl.innerHTML = '<bdi>£' + unit.toFixed( 2 ) + '</bdi>';
                }
            } );
        }

        // ---- Qty → tier highlight + ATC label ----
        function findQtyInput() {
            return document.querySelector( 'form.cart input.qty' );
        }

        function applyQty() {
            var qInput = findQtyInput();
            if ( ! qInput ) {
                return;
            }
            var qty = Math.max( 1, parseInt( qInput.value, 10 ) || 1 );

            var cells = document.querySelectorAll( '.kaiko-pp-tier' );
            if ( ! cells.length ) {
                return;
            }

            var activeCell = null;
            cells.forEach( function ( c ) {
                var min     = parseInt( c.getAttribute( 'data-min' ), 10 ) || 1;
                var max     = parseInt( c.getAttribute( 'data-max' ), 10 ) || 0;
                var inRange = ( max === 0 ) ? ( qty >= min ) : ( qty >= min && qty <= max );
                if ( inRange ) {
                    activeCell = c;
                }
            } );

            cells.forEach( function ( c ) { c.classList.toggle( 'is-active', c === activeCell ); } );

            if ( ! activeCell ) {
                return;
            }

            var unit = parseFloat( activeCell.dataset.price );
            if ( ! isFinite( unit ) || unit <= 0 ) {
                return;
            }
            var total = qty * unit;

            var totalEl = document.querySelector( '[data-kaiko-total]' );
            if ( totalEl ) {
                totalEl.textContent = '£' + total.toFixed( 2 );
            }

            var atc = document.querySelector(
                'form.cart button[type="submit"].single_add_to_cart_button, form.cart button.single_add_to_cart_button, form.cart .single_add_to_cart_button'
            );
            if ( atc ) {
                var baseLabel = atc.getAttribute( 'data-kaiko-atc-base' );
                if ( ! baseLabel ) {
                    baseLabel = ( atc.textContent || 'Add to cart' ).replace( /\s*—.*$/, '' ).trim();
                    atc.setAttribute( 'data-kaiko-atc-base', baseLabel );
                }
                atc.textContent = baseLabel.toUpperCase() + ' — £' + total.toFixed( 2 );
            }
        }

        // ---- Variation apply ----
        function applyVariation( v ) {
            if ( ! v ) {
                return;
            }

            rewriteTiers( parseFloat( v.display_price ) );

            if ( typeof window.syncMainImageFromVariation === 'function' && v.image ) {
                window.syncMainImageFromVariation( v.image );
            }
            $( 'body' ).trigger( 'found_variation', [ $form, v ] );

            applyQty();
        }

        // ---- Event wiring ----
        $form.on( 'change', '.variations select, .variations_table select', function () {
            setTimeout( function () { applyVariation( matchVariation() ); }, 0 );
        } );

        $( document ).on( 'input change keyup', 'form.cart input.qty', applyQty );
        $( document ).on(
            'click',
            'form.cart .plus, form.cart .minus, form.cart .wd-plus, form.cart .wd-minus, form.cart .quantity button',
            function () {
                setTimeout( applyQty, 30 );
                setTimeout( applyQty, 100 );
            }
        );

        // Pill click — set qty to the tier's minimum, then apply.
        $( document ).on( 'click', '.kaiko-pp-tier', function () {
            var min    = parseInt( this.getAttribute( 'data-min' ), 10 ) || 1;
            var qInput = findQtyInput();
            if ( qInput ) {
                qInput.value = min;
                qInput.dispatchEvent( new Event( 'input',  { bubbles: true } ) );
                qInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
                $( qInput ).trigger( 'change' );
            }
            setTimeout( applyQty, 30 );
        } );

        // Polling safety net — some stepper implementations mutate the
        // qty input programmatically without firing a standard event.
        var lastVal = null;
        setInterval( function () {
            var q = findQtyInput();
            if ( ! q ) {
                return;
            }
            if ( q.value !== lastVal ) {
                lastVal = q.value;
                applyQty();
            }
        }, 250 );

        // Initial pass.
        setTimeout( function () {
            applyVariation( matchVariation() );
            applyQty();
        }, 100 );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
    $( window ).on( 'load', init );
} )();
