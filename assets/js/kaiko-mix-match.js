/**
 * Kaiko — PDP Mix-and-Match Accumulator (front-end state machine).
 *
 * Mounts on .kaiko-mm roots rendered by template-parts/kaiko-pdp-accumulator.php.
 * Reads the variation map + tier schedule from data attributes, maintains a
 * local list of (size, colour, qty) rows, and on ATC commits every row as a
 * separate cart line via a single AJAX batch call.
 *
 * Tier qualification is per-size: each size builds its own tier pool. The
 * server-side in inc/mix-and-match-pricing.php mirrors this — tiers are
 * keyed by (parent_id, size_attribute_value).
 */

( function ( $ ) {
	'use strict';

	if ( ! window.jQuery ) {
		return;
	}

	function parseJSONAttr( el, attr ) {
		try {
			var raw = el.getAttribute( attr );
			if ( ! raw ) return null;
			return JSON.parse( raw );
		} catch ( e ) {
			return null;
		}
	}

	function fmtMoney( n ) {
		var v = ( +n || 0 );
		return '£' + v.toFixed( 2 );
	}

	function init( root ) {
		if ( root.__kaikoMmInit ) return;
		root.__kaikoMmInit = true;

		var variationMap = parseJSONAttr( root, 'data-variation-map' ) || {};
		var tiers        = parseJSONAttr( root, 'data-tiers' ) || [];
		var defaultSize  = root.getAttribute( 'data-default-size' ) || '';
		var hasSizes     = root.getAttribute( 'data-has-sizes' ) === '1';
		var productId    = parseInt( root.getAttribute( 'data-product-id' ), 10 ) || 0;

		// DOM references
		var sizeTabsEl        = root.querySelector( '[data-kaiko-mm-size-tabs]' );
		var sizeContextEl     = root.querySelector( '[data-kaiko-mm-size-context]' );
		var colourGridEl      = root.querySelector( '[data-kaiko-mm-colour-grid]' );
		var selectionEl       = root.querySelector( '[data-kaiko-mm-selection]' );
		var selectionEmptyEl  = root.querySelector( '[data-kaiko-mm-selection-empty]' );
		var selectionMetaEl   = root.querySelector( '[data-kaiko-mm-selection-meta]' );
		var groupsEl          = root.querySelector( '[data-kaiko-mm-groups]' );
		var totalValueEl      = root.querySelector( '[data-kaiko-mm-total-value]' );
		var totalBreakdownEl  = root.querySelector( '[data-kaiko-mm-total-breakdown]' );
		var atcBtn            = root.querySelector( '[data-kaiko-mm-atc]' );
		var atcLabelEl        = root.querySelector( '[data-kaiko-mm-atc-label]' );
		var atcCountEl        = root.querySelector( '[data-kaiko-mm-atc-count]' );
		var hintEl            = root.querySelector( '[data-kaiko-mm-hint]' );
		var errorEl           = root.querySelector( '[data-kaiko-mm-error]' );

		// Size ordering (from tab dataset) — keeps group render order stable.
		var sizes = [];
		if ( sizeTabsEl ) {
			Array.prototype.forEach.call( sizeTabsEl.querySelectorAll( '.kaiko-mm__size-tab' ), function ( t ) {
				sizes.push( t.getAttribute( 'data-size' ) );
			} );
		}

		// Quick lookup: size → display name (from tab label text).
		var sizeNames = {};
		if ( sizeTabsEl ) {
			Array.prototype.forEach.call( sizeTabsEl.querySelectorAll( '.kaiko-mm__size-tab' ), function ( t ) {
				var slug = t.getAttribute( 'data-size' );
				var lbl  = t.querySelector( '.kaiko-mm__size-tab-label' );
				sizeNames[ slug ] = lbl ? lbl.textContent.trim() : slug;
			} );
		}

		var state = {
			activeSize: hasSizes ? ( defaultSize || ( sizes[0] || '' ) ) : '',
			rows: []
			/* rows: { size, colour, colourName, hex, qty, variationId, displayPrice } */
		};

		/* ---------------- variation resolution ---------------- */

		function resolveVariation( size, colour ) {
			for ( var vid in variationMap ) {
				if ( ! Object.prototype.hasOwnProperty.call( variationMap, vid ) ) continue;
				var v = variationMap[ vid ];
				// size === null in the map means the product has no size axis.
				var sizeMatches = hasSizes
					? ( String( v.size || '' ) === String( size || '' ) || v.size === '' && size === '' )
					: true;
				var colourMatches = String( v.colour || '' ) === String( colour || '' );
				if ( sizeMatches && colourMatches ) {
					return {
						variationId:  parseInt( vid, 10 ),
						displayPrice: parseFloat( v.price ) || 0,
						inStock:      !! v.in_stock,
						imageUrl:     v.image_url || ''
					};
				}
			}
			return null;
		}

		/* ---------------- tier math ---------------- */

		function tierForQty( q ) {
			if ( ! tiers.length ) return null;
			for ( var i = 0; i < tiers.length; i++ ) {
				var t  = tiers[ i ];
				var ok = t.max_qty === 0
					? q >= t.min_qty
					: ( q >= t.min_qty && q <= t.max_qty );
				if ( ok ) return t;
			}
			// qty below first tier's min — fall back to first.
			return tiers[ 0 ];
		}

		function nextTierForQty( q ) {
			for ( var i = 0; i < tiers.length; i++ ) {
				if ( tiers[ i ].min_qty > q ) return tiers[ i ];
			}
			return null;
		}

		function tierIndex( tier ) {
			for ( var i = 0; i < tiers.length; i++ ) {
				if ( tiers[ i ] === tier ) return i;
			}
			return 0;
		}

		// Per-row unit price for a given applied tier.
		// - Default-schedule tiers: percentage off the variation's own display_price
		//   (preserves per-variation differentials).
		// - ACF tiers: absolute unit_price (parent-level, uniform across variations).
		function unitForRow( row, tier ) {
			if ( ! tier ) return row.displayPrice;
			if ( tier.is_default ) {
				return +( row.displayPrice * ( 1 - tier.discount_pct / 100 ) ).toFixed( 2 );
			}
			return +tier.unit_price;
		}

		// Row's pre-tier unit price — same logic as cart-layout.php's tier data.
		function baseForRow( row ) {
			if ( ! tiers.length ) return row.displayPrice;
			if ( tiers[ 0 ].is_default ) return row.displayPrice;
			return +tiers[ 0 ].unit_price;
		}

		/* ---------------- derivations ---------------- */

		function rowsBySize( size ) {
			return state.rows.filter( function ( r ) { return r.size === size; } );
		}
		function qtyForSize( size ) {
			return rowsBySize( size ).reduce( function ( s, r ) { return s + r.qty; }, 0 );
		}
		function sizesInUse() {
			if ( ! hasSizes ) {
				return qtyForSize( '' ) > 0 ? [ '' ] : [];
			}
			return sizes.filter( function ( s ) { return qtyForSize( s ) > 0; } );
		}
		function grandTotalQty() {
			return state.rows.reduce( function ( s, r ) { return s + r.qty; }, 0 );
		}
		function groupSubtotal( size ) {
			var q    = qtyForSize( size );
			var tier = tierForQty( q );
			return rowsBySize( size ).reduce( function ( acc, r ) {
				return acc + r.qty * unitForRow( r, tier );
			}, 0 );
		}
		function groupSavings( size ) {
			var q    = qtyForSize( size );
			var tier = tierForQty( q );
			return rowsBySize( size ).reduce( function ( acc, r ) {
				return acc + r.qty * ( baseForRow( r ) - unitForRow( r, tier ) );
			}, 0 );
		}
		function grandTotalValue() {
			return sizesInUse().reduce( function ( acc, s ) { return acc + groupSubtotal( s ); }, 0 );
		}
		function grandSavings() {
			return sizesInUse().reduce( function ( acc, s ) { return acc + groupSavings( s ); }, 0 );
		}
		function findRow( size, colour ) {
			for ( var i = 0; i < state.rows.length; i++ ) {
				var r = state.rows[ i ];
				if ( r.size === size && r.colour === colour ) return i;
			}
			return -1;
		}

		/* ---------------- mutations ---------------- */

		function toggleColour( colour, colourName, hex ) {
			var idx = findRow( state.activeSize, colour );
			if ( idx >= 0 ) {
				state.rows.splice( idx, 1 );
				render();
				return;
			}

			var res = resolveVariation( state.activeSize, colour );
			if ( ! res || ! res.inStock ) {
				// Can't add OOS / unavailable combinations.
				render();
				return;
			}

			state.rows.push( {
				size:         state.activeSize,
				colour:       colour,
				colourName:   colourName,
				hex:          hex,
				qty:          1,
				variationId:  res.variationId,
				displayPrice: res.displayPrice
			} );
			render();
		}

		function bumpRow( idx, delta ) {
			var r = state.rows[ idx ];
			if ( ! r ) return;
			r.qty = Math.max( 0, r.qty + delta );
			if ( r.qty === 0 ) state.rows.splice( idx, 1 );
			render();
		}
		function setRowQty( idx, qty ) {
			var r = state.rows[ idx ];
			if ( ! r ) return;
			r.qty = Math.max( 0, parseInt( qty, 10 ) || 0 );
			if ( r.qty === 0 ) state.rows.splice( idx, 1 );
			render();
		}
		function removeRow( idx ) {
			state.rows.splice( idx, 1 );
			render();
		}

		/* ---------------- render ---------------- */

		function render() {
			renderSizeTabs();
			renderSizeContext();
			renderSwatches();
			renderGroups();
			renderTotals();
			renderAtc();
			renderHint();
		}

		function renderSizeTabs() {
			if ( ! sizeTabsEl ) return;
			Array.prototype.forEach.call( sizeTabsEl.children, function ( tab ) {
				var size     = tab.getAttribute( 'data-size' );
				var isActive = size === state.activeSize;
				tab.classList.toggle( 'is-active', isActive );
				tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
				var count = tab.querySelector( '.kaiko-mm__size-tab-count' );
				var q     = qtyForSize( size );
				if ( count ) {
					if ( q > 0 ) {
						count.hidden = false;
						count.textContent = q;
					} else {
						count.hidden = true;
					}
				}
			} );
		}

		function renderSizeContext() {
			if ( ! sizeContextEl ) return;
			if ( ! hasSizes ) {
				sizeContextEl.innerHTML = '';
				sizeContextEl.hidden = true;
				return;
			}
			sizeContextEl.hidden = false;

			var activeName = sizeNames[ state.activeSize ] || state.activeSize;
			var q          = qtyForSize( state.activeSize );
			if ( q > 0 ) {
				var t    = tierForQty( q );
				var unit = t ? unitForRow(
					rowsBySize( state.activeSize )[ 0 ],
					t
				) : 0;
				sizeContextEl.innerHTML =
					'You have <strong>' + q + ' unit' + ( q === 1 ? '' : 's' ) +
					' in ' + escapeHtml( activeName ) + '</strong> · tier ' +
					( tierIndex( t ) + 1 ) + ' at ' + fmtMoney( unit ) + '/unit';
			} else {
				sizeContextEl.innerHTML =
					'Tap a colour below to add it to the <strong>' +
					escapeHtml( activeName ) + '</strong> pool.';
			}
		}

		function renderSwatches() {
			if ( ! colourGridEl ) return;
			var activeRows = rowsBySize( state.activeSize );
			Array.prototype.forEach.call( colourGridEl.children, function ( sw ) {
				var slug    = sw.getAttribute( 'data-colour' );
				var present = activeRows.some( function ( r ) { return r.colour === slug; } );
				sw.classList.toggle( 'is-active', present );

				var combo = resolveVariation( state.activeSize, slug );
				var oos   = ! combo || ! combo.inStock;
				sw.classList.toggle( 'is-oos', oos && ! present );
				if ( oos && ! present ) {
					sw.setAttribute( 'aria-disabled', 'true' );
				} else {
					sw.removeAttribute( 'aria-disabled' );
				}
			} );
		}

		function renderGroups() {
			if ( ! groupsEl || ! selectionEl || ! selectionEmptyEl || ! selectionMetaEl ) return;
			var used = sizesInUse();

			if ( used.length ) {
				selectionEl.classList.add( 'is-populated' );
				selectionEmptyEl.hidden = true;

				var html = used.map( function ( size ) {
					var rows     = rowsBySize( size );
					var q        = qtyForSize( size );
					var tier     = tierForQty( q );
					var isBase   = tier === tiers[ 0 ];
					var subtotal = groupSubtotal( size );
					var savings  = groupSavings( size );
					var unit     = tier ? unitForRow( rows[ 0 ], tier ) : 0;

					var chipLabel = 'Tier ' + ( tierIndex( tier ) + 1 ) + ' · ' + fmtMoney( unit ) + '/unit';
					if ( ! isBase && savings > 0.005 ) {
						chipLabel += ' · saving ' + fmtMoney( savings );
					}
					var chipClass = 'kaiko-mm__group-tier-chip' + ( isBase ? ' kaiko-mm__group-tier-chip--base' : '' );

					var next     = nextTierForQty( q );
					var nextNote = next
						? ( next.min_qty - q ) + ' more for ' + fmtMoney(
							next.is_default
								? +( rows[ 0 ].displayPrice * ( 1 - next.discount_pct / 100 ) ).toFixed( 2 )
								: +next.unit_price
						) + '/unit'
						: 'Best tier applied';

					var header = hasSizes
						? '<div class="kaiko-mm__group-size">' +
								'<span class="kaiko-mm__group-size-label">Size</span>' +
								'<span class="kaiko-mm__group-size-value">' + escapeHtml( sizeNames[ size ] || size ) + '</span>' +
								'<span class="' + chipClass + '">' + escapeHtml( chipLabel ) + '</span>' +
							'</div>'
						: '<div class="kaiko-mm__group-size">' +
								'<span class="' + chipClass + '">' + escapeHtml( chipLabel ) + '</span>' +
							'</div>';

					var list = rows.map( function ( r ) {
						var idx = state.rows.indexOf( r );
						return (
							'<div class="kaiko-mm__row" data-idx="' + idx + '">' +
								'<span class="kaiko-mm__row-dot" style="background: ' + escapeAttr( r.hex ) + ';"></span>' +
								'<span class="kaiko-mm__row-label">' + escapeHtml( r.colourName ) + '</span>' +
								'<span class="kaiko-mm__row-stepper">' +
									'<button type="button" data-act="dec" aria-label="Decrease">−</button>' +
									'<input type="number" min="0" value="' + r.qty + '" aria-label="Qty">' +
									'<button type="button" data-act="inc" aria-label="Increase">+</button>' +
								'</span>' +
								'<button type="button" class="kaiko-mm__row-remove" data-act="remove" aria-label="Remove">✕</button>' +
							'</div>'
						);
					} ).join( '' );

					return (
						'<div class="kaiko-mm__group" data-size="' + escapeAttr( size ) + '">' +
							'<div class="kaiko-mm__group-header">' +
								header +
								'<div class="kaiko-mm__group-subtotal">' +
									fmtMoney( subtotal ) +
									'<span class="kaiko-mm__group-subtotal-note"> · ' + escapeHtml( nextNote ) + '</span>' +
								'</div>' +
							'</div>' +
							'<div class="kaiko-mm__group-list">' + list + '</div>' +
						'</div>'
					);
				} ).join( '' );

				groupsEl.innerHTML = html;
			} else {
				selectionEl.classList.remove( 'is-populated' );
				selectionEmptyEl.hidden = false;
				groupsEl.innerHTML = '';
			}

			var totalQ = grandTotalQty();
			selectionMetaEl.innerHTML =
				used.length + ( hasSizes ? ( ' size' + ( used.length === 1 ? '' : 's' ) ) : ( ' selection' + ( used.length === 1 ? '' : 's' ) ) ) +
				' · <strong>' + totalQ + ' unit' + ( totalQ === 1 ? '' : 's' ) + '</strong>';
		}

		function renderTotals() {
			if ( ! totalValueEl || ! totalBreakdownEl ) return;
			var used    = sizesInUse();
			var totalV  = grandTotalValue();
			var savings = grandSavings();
			totalValueEl.textContent = fmtMoney( totalV );
			if ( hasSizes ) {
				totalBreakdownEl.textContent =
					'Across ' + used.length + ' size' + ( used.length === 1 ? '' : 's' ) +
					( savings > 0.005 ? ' · saving ' + fmtMoney( savings ) : '' );
			} else {
				totalBreakdownEl.textContent =
					grandTotalQty() + ' unit' + ( grandTotalQty() === 1 ? '' : 's' ) +
					( savings > 0.005 ? ' · saving ' + fmtMoney( savings ) : '' );
			}
		}

		function renderAtc() {
			if ( ! atcBtn ) return;
			var totalQ = grandTotalQty();
			var totalV = grandTotalValue();
			atcBtn.disabled = ( totalQ === 0 );
			if ( atcLabelEl ) atcLabelEl.textContent = totalQ ? ( 'Add to cart — ' + fmtMoney( totalV ) ) : 'Pick a colour to start';
			if ( atcCountEl ) atcCountEl.textContent = totalQ + ' item' + ( totalQ === 1 ? '' : 's' );
		}

		function renderHint() {
			if ( ! hintEl ) return;
			var totalQ = grandTotalQty();
			if ( totalQ === 0 ) {
				hintEl.textContent = hasSizes
					? 'Each size needs 6+ units to unlock the next tier on that size.'
					: 'Add 6+ units to unlock the next tier.';
				return;
			}
			if ( ! hasSizes ) {
				var q  = qtyForSize( '' );
				var nt = nextTierForQty( q );
				if ( nt ) {
					hintEl.textContent = ( nt.min_qty - q ) + ' more unit' + ( ( nt.min_qty - q ) === 1 ? '' : 's' ) + ' to reach the next tier.';
				} else {
					hintEl.textContent = 'Top tier applied — best price.';
				}
				return;
			}
			var aSize = state.activeSize;
			var aName = sizeNames[ aSize ] || aSize;
			var aq    = qtyForSize( aSize );
			if ( aq === 0 ) {
				hintEl.textContent = aName + ' is empty — tap a colour.';
				return;
			}
			var nt2 = nextTierForQty( aq );
			if ( nt2 ) {
				var delta = nt2.min_qty - aq;
				var sample = rowsBySize( aSize )[ 0 ];
				var nextUnit = nt2.is_default
					? +( sample.displayPrice * ( 1 - nt2.discount_pct / 100 ) ).toFixed( 2 )
					: +nt2.unit_price;
				hintEl.textContent = aName + ' needs ' + delta + ' more unit' + ( delta === 1 ? '' : 's' ) +
					' to reach ' + fmtMoney( nextUnit ) + '/unit on ' + aName + '.';
			} else {
				hintEl.textContent = aName + ' is at the top tier — best price.';
			}
		}

		function showError( msg ) {
			if ( ! errorEl ) return;
			if ( ! msg ) {
				errorEl.hidden = true;
				errorEl.textContent = '';
				return;
			}
			errorEl.hidden = false;
			errorEl.textContent = msg;
		}

		/* ---------------- events ---------------- */

		if ( sizeTabsEl ) {
			sizeTabsEl.addEventListener( 'click', function ( e ) {
				var tab = e.target.closest ? e.target.closest( '.kaiko-mm__size-tab' ) : null;
				if ( ! tab ) return;
				state.activeSize = tab.getAttribute( 'data-size' );
				render();
			} );
		}

		if ( colourGridEl ) {
			colourGridEl.addEventListener( 'click', function ( e ) {
				var sw = e.target.closest ? e.target.closest( '.kaiko-mm__swatch' ) : null;
				if ( ! sw ) return;
				if ( sw.classList.contains( 'is-oos' ) ) return;
				toggleColour(
					sw.getAttribute( 'data-colour' ),
					sw.getAttribute( 'data-colour-name' ),
					sw.getAttribute( 'data-hex' )
				);
			} );
		}

		if ( groupsEl ) {
			groupsEl.addEventListener( 'click', function ( e ) {
				var btn = e.target.closest ? e.target.closest( '[data-act]' ) : null;
				if ( ! btn ) return;
				var row = btn.closest( '.kaiko-mm__row' );
				if ( ! row ) return;
				var idx = parseInt( row.getAttribute( 'data-idx' ), 10 );
				var act = btn.getAttribute( 'data-act' );
				if ( act === 'inc'    ) bumpRow( idx, +1 );
				if ( act === 'dec'    ) bumpRow( idx, -1 );
				if ( act === 'remove' ) removeRow( idx );
			} );
			groupsEl.addEventListener( 'change', function ( e ) {
				var input = e.target.closest ? e.target.closest( 'input[type="number"]' ) : null;
				if ( ! input ) return;
				var row = input.closest( '.kaiko-mm__row' );
				if ( ! row ) return;
				setRowQty( parseInt( row.getAttribute( 'data-idx' ), 10 ), input.value );
			} );
		}

		if ( atcBtn ) {
			atcBtn.addEventListener( 'click', function () {
				if ( atcBtn.disabled ) return;
				submitBatch();
			} );
		}

		/* ---------------- submit ---------------- */

		function submitBatch() {
			showError( '' );
			if ( ! window.kaikoMixMatchData || ! window.kaikoMixMatchData.ajaxUrl || ! window.kaikoMixMatchData.nonce ) {
				showError( 'Cart endpoint unavailable — refresh the page and try again.' );
				return;
			}

			var payload = state.rows.map( function ( r ) {
				return {
					variation_id: r.variationId,
					product_id:   productId,
					quantity:     r.qty
				};
			} );

			atcBtn.disabled = true;
			if ( atcLabelEl ) atcLabelEl.textContent = 'Adding to cart…';

			$.ajax( {
				url:  window.kaikoMixMatchData.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'kaiko_batch_add_to_cart',
					nonce:  window.kaikoMixMatchData.nonce,
					rows:   payload
				}
			} ).done( function ( res ) {
				if ( ! res ) {
					showError( 'Empty response from cart endpoint.' );
					atcBtn.disabled = false;
					render();
					return;
				}
				if ( res.ok ) {
					state.rows = [];
					render();
				} else if ( res.added > 0 ) {
					// Partial success — drop only the rows that committed.
					state.rows.splice( 0, res.added );
					render();
					showError( res.error || ( 'Added ' + res.added + ' of ' + ( res.attempted || payload.length ) + ' — please retry the rest.' ) );
				} else {
					showError( res.error || 'Could not add anything to cart — try again.' );
					atcBtn.disabled = false;
					render();
					return;
				}

				// Refresh Woodmart/WC fragments and open the Kaiko mini-cart
				// drawer so the user sees the new line items.
				$( document.body ).trigger( 'wc_fragment_refresh' );
				$( document.body ).one( 'wc_fragments_refreshed', function () {
					openMiniCart();
				} );
				// Safety: open regardless after 800ms if the refresh event
				// never fires (e.g. no cart fragments registered).
				setTimeout( openMiniCart, 800 );
			} ).fail( function () {
				showError( 'Network error — check your connection and try again.' );
				atcBtn.disabled = false;
				render();
			} );
		}

		function openMiniCart() {
			if ( openMiniCart.__done ) return;
			openMiniCart.__done = true;
			var opener = document.querySelector( '[data-kaiko-open-cart]' );
			if ( opener ) {
				opener.click();
				return;
			}
			// Fallback — Woodmart side-cart or generic mini-cart toggles.
			$( document.body ).trigger( 'wd-side-cart-open' );
			var $toggle = $( '.wd-tools-cart a, .wd-cart-button, .cart-contents' ).first();
			if ( $toggle.length ) $toggle.trigger( 'click' );
		}

		/* ---------------- utilities ---------------- */

		function escapeHtml( str ) {
			return String( str == null ? '' : str )
				.replace( /&/g, '&amp;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' )
				.replace( /"/g, '&quot;' )
				.replace( /'/g, '&#39;' );
		}
		function escapeAttr( str ) { return escapeHtml( str ); }

		/* ---------------- first paint ---------------- */

		render();
	}

	function boot() {
		document.querySelectorAll( '.kaiko-mm' ).forEach( init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

} )( window.jQuery );
