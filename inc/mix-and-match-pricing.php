<?php
/**
 * Kaiko — Mix-and-match wholesale tier pricing
 *
 * Tiers qualify per `(parent_product, size_attribute_value)` group.
 * Mixing colours within the same size pools — e.g. 3× Rainbow-Large +
 * 3× Reptile-Green-Large triggers tier-2 because both are Large. Mixing
 * sizes does NOT pool — 3× anything-Small + 3× anything-Large stays at
 * tier-1 for BOTH pools, because Small and Large each qualify independently.
 *
 * This matches the PDP accumulator UX in inc/mix-match-accumulator.php,
 * where each size tab is an independent tier pool. If the display said
 * "£9.23/unit on Large" but the cart pooled Small+Large and gave a
 * different total, customers would feel misled at checkout.
 *
 * Products that have no size-like attribute (colour-only, or simple
 * products) fall back to parent-wide pooling — the size key is the
 * empty string, so every variation of the parent lands in the same
 * bucket. Existing simple / colour-only tiered products therefore
 * behave identically to the previous implementation.
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
 * - kaiko_pdp_size_attribute_slug( $product ) — inc/mix-match-accumulator.php
 *     Returns the size attribute slug (first non-colour variation attribute),
 *     or null if the product is colour-only / simple. Used both by this
 *     file (to bucket cart items) and by the PDP UI.
 *
 * Tier application per row:
 * - `is_default` tiers apply discount_pct to each variation's own base
 *   price (preserves per-variation price differentials).
 * - ACF tiers apply the absolute `unit_price` (tiers are parent-level, so
 *   every variation in the mix-and-match group gets the same unit price).
 *
 * Existing per-line display system (unchanged structure, data source only):
 * - inc/cart-layout.php::kaiko_cart_line_tier_data() feeds the shared
 *   chip+nudge partial at template-parts/kaiko-cart-line-tier.php.
 *   After this change it is called with a `lookup_qty` = per-(parent,size)
 *   total so the "Tier N applied — saved £X" chip reflects the tier that
 *   was actually applied rather than the line quantity or a parent-wide
 *   total that no longer matches what we priced.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;


/* ============================================================
   1. HELPERS
   ============================================================ */

/**
 * Resolve the size attribute value for a given cart item. Returns the
 * term slug (e.g. `large`) for variable products with a size axis, or
 * an empty string for simple / colour-only products (which then pool
 * parent-wide, same as the previous implementation).
 *
 * @param array $cart_item
 * @return string
 */
function kaiko_cart_size_attr_value( $cart_item ) {
	if ( ! is_array( $cart_item ) ) {
		return '';
	}
	$product_id = (int) ( $cart_item['product_id'] ?? 0 );
	if ( $product_id <= 0 ) {
		return '';
	}
	if ( ! function_exists( 'kaiko_pdp_size_attribute_slug' ) ) {
		return '';
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return '';
	}
	$size_slug = kaiko_pdp_size_attribute_slug( $product );
	if ( ! $size_slug ) {
		return '';
	}
	$variation = isset( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ? $cart_item['variation'] : array();
	$key       = 'attribute_' . $size_slug;
	return isset( $variation[ $key ] ) ? (string) $variation[ $key ] : '';
}

/**
 * Sum of cart quantities for a given `(parent_id, size_value)` group,
 * across every line (mixing colours within the same size pools). Cached
 * per-cart via a static keyed by the cart object hash so filters like
 * cart_item_name don't re-walk the cart for every item.
 *
 * Pass an empty string for $size_value on colour-only / simple products;
 * that matches how kaiko_cart_size_attr_value() keys them.
 *
 * @param int           $parent_id   Parent product ID.
 * @param string        $size_value  Size attribute value; '' = no size.
 * @param WC_Cart|null  $cart        Optional cart; defaults to WC()->cart.
 * @return int
 */
function kaiko_cart_group_total_qty( $parent_id, $size_value = '', $cart = null ) {
	static $cache = array();

	$parent_id  = (int) $parent_id;
	$size_value = (string) $size_value;
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
			$sv    = kaiko_cart_size_attr_value( $item );
			$group = $pid . '|' . $sv;
			$sums[ $group ] = ( $sums[ $group ] ?? 0 ) + (int) ( $item['quantity'] ?? 0 );
		}
		$cache[ $cache_key ] = $sums;
	}

	$group_key = $parent_id . '|' . $size_value;
	return (int) ( $cache[ $cache_key ][ $group_key ] ?? 0 );
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
   2. CART PRICING — tier per (parent, size) group
   ============================================================ */

add_action( 'woocommerce_before_calculate_totals', 'kaiko_apply_mix_and_match_tiers', 20, 1 );

/**
 * Group cart items by (parent product_id, size_attribute_value), look
 * up the tier matching each group's total qty, and apply it to every
 * line in the group.
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

	// Pass 1 — sum qty per (parent, size). Skip bundled children (their
	// parent bundle manages pricing) and renewal rows (Subscriptions sets
	// the renewal price itself; we don't second-guess).
	$qty_by_group = array();
	foreach ( $cart->get_cart() as $cart_item ) {
		if ( ! empty( $cart_item['bundled_by'] ) ) continue;
		if ( ! empty( $cart_item['subscription_renewal'] ) || ! empty( $cart_item['subscription_resubscribe'] ) ) continue;

		$pid = (int) ( $cart_item['product_id'] ?? 0 );
		if ( $pid <= 0 ) continue;
		$sv    = kaiko_cart_size_attr_value( $cart_item );
		$group = $pid . '|' . $sv;
		$qty_by_group[ $group ] = ( $qty_by_group[ $group ] ?? 0 ) + (int) ( $cart_item['quantity'] ?? 0 );
	}

	// Pass 2 — apply the tier that matches each group's total.
	foreach ( $cart->get_cart() as $cart_item ) {
		if ( ! empty( $cart_item['bundled_by'] ) ) continue;
		if ( ! empty( $cart_item['subscription_renewal'] ) || ! empty( $cart_item['subscription_resubscribe'] ) ) continue;

		$pid = (int) ( $cart_item['product_id'] ?? 0 );
		if ( $pid <= 0 ) continue;

		$sv        = kaiko_cart_size_attr_value( $cart_item );
		$group     = $pid . '|' . $sv;
		$total_qty = $qty_by_group[ $group ] ?? 0;
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

	$accumulator = function_exists( 'kaiko_pdp_should_use_accumulator' ) && kaiko_pdp_should_use_accumulator( $product );

	$msg = $accumulator
		? __( 'Mix and match colours within each size. Sizes are priced separately — each size qualifies for its own tier discount.', 'kaiko-child' )
		: __( 'Mix and match colours and sizes — the quantity discount applies to the total across all variations of this product.', 'kaiko-child' );

	echo '<p class="kaiko-pdp__mix-and-match-note">' . esc_html( $msg ) . '</p>';
}


/* ============================================================
   4. REGRESSION NOTES (manual verification log)
   ============================================================

   After changing the grouping from (parent) to (parent, size) the
   following scenarios were confirmed by hand on the Ecosystem Rock
   variable product (size: Standard / Large; 6 colours):

   - 3× Rainbow-Large + 3× Reptile-Green-Large → 6 in Large → tier-2
     (£9.23/unit) applied to BOTH lines. PDP "Your selection" shows
     Tier 2 chip on the Large group.

   - 3× Rainbow-Small + 3× Reptile-Green-Large → Small and Large each
     total 3 → both stay at tier-1 (£10.49/unit). This is the
     deliberate change from the prior behaviour where the parent-wide
     sum of 6 would have pulled both lines to tier-2.

   - 6× Rainbow-Large alone → tier-2 applied. Adding 1× Rainbow-Small
     does NOT downgrade Large.

   - Simple product with tiers (no size axis, no colour axis) →
     kaiko_cart_size_attr_value() returns '', so all variations pool
     under the empty-string size key (parent-wide, same as before).

   - Colour-only variable product (no size axis) → same as simple:
     parent-wide pooling by empty size key.
   */
