<?php
/**
 * Kaiko — Mix-and-match wholesale tier pricing
 *
 * Applies the configured wholesale tiers based on the TOTAL quantity of a
 * parent product in the cart (summed across all its variations), not the
 * per-line quantity. Trade customers routinely mix colours + sizes — e.g.
 * 3× Rainbow-M + 3× Reptile-Green-M of the same parent should trigger the
 * tier-2 (6-11 band) discount, with every unit priced at that tier rate.
 *
 * Replaces the earlier kaiko_apply_tier_pricing_to_cart hook in functions.php
 * which used per-line qty and therefore never combined variations.
 *
 * ---------------------------------------------------------------------------
 * DISCOVERY NOTES (source of truth for tier data)
 * ---------------------------------------------------------------------------
 * - kaiko_get_product_tiers( $product_id )    — functions.php:752-807
 *     Returns the normalised tier array. Honours ACF repeater
 *     `kaiko_wholesale_tiers` per product (absolute `unit_price` per row),
 *     falling back to `kaiko_get_default_tier_schedule()` (the filterable
 *     1-5 / 6-11 / 12-23 / 24+ at 0/12/22/30% schedule) applied to the
 *     product's base price. Single source of truth — we do NOT read raw
 *     post_meta here.
 * - kaiko_find_tier_for_qty( $tiers, $qty )   — functions.php:812-820
 *     Picks the tier whose min/max band contains $qty. max_qty === 0 means
 *     no upper bound.
 *
 * Tier application per row:
 * - `is_default` tiers apply discount_pct to each variation's own base
 *   price (preserves per-variation price differentials).
 * - ACF tiers apply the absolute `unit_price` (tiers are parent-level, so
 *   every variation in the mix-and-match group gets the same unit price).
 *
 * Existing per-line display system (unchanged structure, data source only):
 * - inc/cart-layout.php::kaiko_cart_line_tier_data() feeds the shared
 *   chip+nudge partial at template-parts/kaiko-cart-line-tier.php. After
 *   this change it is called with a `lookup_qty` = parent-total so the
 *   "Tier N applied — saved £X" chip reflects the applied tier rather than
 *   the line quantity.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;


/* ============================================================
   1. HELPERS
   ============================================================ */

/**
 * Sum of cart quantities for a given parent product id, across every line
 * (including variations). Cached per-cart via a static keyed by the cart
 * object hash so the filter on cart_item_name doesn't re-walk the cart
 * for every item.
 *
 * @param int           $parent_id  Parent product ID.
 * @param WC_Cart|null  $cart       Optional cart; defaults to WC()->cart.
 * @return int
 */
function kaiko_cart_parent_total_qty( $parent_id, $cart = null ) {
	static $cache = array();

	$parent_id = (int) $parent_id;
	if ( $parent_id <= 0 ) {
		return 0;
	}

	if ( ! $cart ) {
		$cart = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart : null;
	}
	if ( ! $cart ) {
		return 0;
	}

	$cache_key = spl_object_hash( $cart );
	if ( ! isset( $cache[ $cache_key ] ) ) {
		$sums = array();
		foreach ( $cart->get_cart() as $item ) {
			if ( ! empty( $item['bundled_by'] ) ) {
				continue; // bundled children don't count towards the parent's tier group
			}
			$pid = (int) ( $item['product_id'] ?? 0 );
			if ( $pid <= 0 ) continue;
			$sums[ $pid ] = ( $sums[ $pid ] ?? 0 ) + (int) ( $item['quantity'] ?? 0 );
		}
		$cache[ $cache_key ] = $sums;
	}

	return (int) ( $cache[ $cache_key ][ $parent_id ] ?? 0 );
}

/**
 * Unit price for a given (parent product, qty) with optional variation
 * base for per-variation default-schedule pricing.
 *
 * Returns null when the product has no tiers configured OR no tier
 * matches $qty — callers should leave the price untouched in that case.
 *
 * @param int        $product_id
 * @param int        $qty
 * @param float|null $variation_base  Current per-unit price of the cart
 *                                    item's underlying product/variation;
 *                                    required for default-schedule tiers
 *                                    so each variation keeps its own base.
 * @return float|null
 */
function kaiko_get_tier_price_for_qty( $product_id, $qty, $variation_base = null ) {
	$tiers = function_exists( 'kaiko_get_product_tiers' ) ? kaiko_get_product_tiers( (int) $product_id ) : array();
	if ( empty( $tiers ) ) {
		return null;
	}

	$tier = function_exists( 'kaiko_find_tier_for_qty' ) ? kaiko_find_tier_for_qty( $tiers, (int) $qty ) : null;
	if ( ! $tier ) {
		return null;
	}

	if ( ! empty( $tier['is_default'] ) ) {
		// Default schedule — apply discount_pct to the variation's current base.
		if ( $variation_base === null ) {
			return null;
		}
		$pct  = (float) $tier['discount_pct'];
		$base = (float) $variation_base;
		if ( $base <= 0 ) {
			return null;
		}
		return round( $base * ( 1 - $pct / 100 ), 2 );
	}

	// ACF tier — absolute unit price, uniform across variations.
	$unit = (float) ( $tier['unit_price'] ?? 0 );
	return $unit > 0 ? $unit : null;
}


/* ============================================================
   2. CART PRICING — combine tiers across variations
   ============================================================ */

add_action( 'woocommerce_before_calculate_totals', 'kaiko_apply_mix_and_match_tiers', 20, 1 );

/**
 * Group cart items by parent product_id, look up the tier matching each
 * group's total qty, and apply it to every line in the group.
 *
 * Runs on every cart recalculation. Priority 20 is deliberately after
 * Woo's default 10 so we override any default pricing the core set first.
 */
function kaiko_apply_mix_and_match_tiers( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	// Trade-gated like the rest of Kaiko: non-approved users don't see prices
	// and can't check out, so no point recalculating.
	if ( ! function_exists( 'kaiko_user_can_see_prices' ) || ! kaiko_user_can_see_prices() ) {
		return;
	}
	// Don't loop forever — WC fires this hook many times per request.
	if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
		return;
	}

	// Pass 1 — sum qty per parent product. Skip bundled children (their
	// parent bundle manages pricing) and renewal rows (Subscriptions sets
	// the renewal price itself; we don't second-guess).
	$qty_by_parent = array();
	foreach ( $cart->get_cart() as $cart_item ) {
		if ( ! empty( $cart_item['bundled_by'] ) ) continue;
		if ( ! empty( $cart_item['subscription_renewal'] ) || ! empty( $cart_item['subscription_resubscribe'] ) ) continue;

		$pid = (int) ( $cart_item['product_id'] ?? 0 );
		if ( $pid <= 0 ) continue;
		$qty_by_parent[ $pid ] = ( $qty_by_parent[ $pid ] ?? 0 ) + (int) ( $cart_item['quantity'] ?? 0 );
	}

	// Pass 2 — apply the tier that matches each group's total.
	foreach ( $cart->get_cart() as $cart_item ) {
		if ( ! empty( $cart_item['bundled_by'] ) ) continue;
		if ( ! empty( $cart_item['subscription_renewal'] ) || ! empty( $cart_item['subscription_resubscribe'] ) ) continue;

		$pid = (int) ( $cart_item['product_id'] ?? 0 );
		if ( $pid <= 0 ) continue;

		$total_qty = $qty_by_parent[ $pid ] ?? 0;
		if ( $total_qty <= 0 ) continue;

		$variation_base = ( isset( $cart_item['data'] ) && is_object( $cart_item['data'] ) )
			? (float) $cart_item['data']->get_price()
			: null;

		$unit = kaiko_get_tier_price_for_qty( $pid, $total_qty, $variation_base );
		if ( null === $unit ) {
			continue; // no tiers / no matching band → leave the existing price alone.
		}

		$cart_item['data']->set_price( (float) $unit );
	}
}


/* ============================================================
   3. PDP — "mix and match across variations" note
   ============================================================ */

/**
 * Render the mix-and-match note. Called directly from the single-product
 * template after the .kaiko-pp-tiers card, only when the product has both
 * variations and configured tiers — simple products with tiers don't need
 * this reassurance (no variations to mix).
 */
function kaiko_render_pdp_mix_and_match_note( $product = null ) {
	if ( ! $product ) {
		global $product;
	}
	if ( ! $product || ! is_object( $product ) ) return;
	if ( ! method_exists( $product, 'is_type' ) || ! $product->is_type( 'variable' ) ) return;

	$tiers = function_exists( 'kaiko_get_product_tiers' ) ? kaiko_get_product_tiers( $product->get_id() ) : array();
	if ( empty( $tiers ) ) return;

	echo '<p class="kaiko-pdp__mix-and-match-note">' . esc_html__(
		'Mix and match colours and sizes — the quantity discount applies to the total across all variations of this product.',
		'kaiko-child'
	) . '</p>';
}
