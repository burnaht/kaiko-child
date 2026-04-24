# Kaiko — Post-deploy fix-ups (PR #15 is live)

Three issues Tom reported after the mix-and-match accumulator went live.
Treat as a single PR off `main`. Branch name: `fix/cart-grouping-and-checkout`.

This is NOT the Silkworm Store codebase — it's kaiko-child (Woodmart parent,
Woo 9.x, PHP 8.x). Do **not** apply Silkworm's CLAUDE.md conventions here.
Trade-gated B2B store.

---

## Issue 1 — Cart lines should group by size

After the batch ATC, each (colour, size, qty) row is its own cart line —
correct. But they're displayed in **insertion order**, so a customer who
picks Tree Stump Brown Standard + Tree Stump Brown Large + Mossy Green
Standard ends up with three interleaved rows.

**Desired:** all Standard rows of a given parent sit next to each other,
then all Large, etc. Keeps the cart scannable and makes the per-size tier
grouping visually obvious (which matches how cart-side tier pricing now
works per (parent, size)).

**Where:** cart page + checkout review — both iterate `WC()->cart->get_cart()`.

**Approach:** filter `woocommerce_get_cart_contents` and sort by
`(product_id, size_attr_value, cart_item_key)`. The size helper already
exists: `kaiko_cart_size_attr_value( $cart_item )` in
`inc/mix-and-match-pricing.php`.

Add to `inc/mix-and-match-pricing.php` (keep the tier/display logic
co-located — a cart item's visual order is a direct consequence of the
per-(parent, size) tier grouping, so it belongs here):

```php
add_filter( 'woocommerce_get_cart_contents', 'kaiko_sort_cart_by_parent_and_size', 20 );

function kaiko_sort_cart_by_parent_and_size( $contents ) {
    if ( ! is_array( $contents ) || count( $contents ) < 2 ) {
        return $contents;
    }
    // Snapshot insertion order so we can use it as a stable tiebreaker.
    $order = array();
    $i = 0;
    foreach ( $contents as $key => $item ) {
        $order[ $key ] = $i++;
    }
    uasort( $contents, function( $a, $b ) use ( $order ) {
        $pa = isset( $a['product_id'] ) ? (int) $a['product_id'] : 0;
        $pb = isset( $b['product_id'] ) ? (int) $b['product_id'] : 0;
        if ( $pa !== $pb ) {
            return $pa - $pb;
        }
        $sa = function_exists( 'kaiko_cart_size_attr_value' ) ? kaiko_cart_size_attr_value( $a ) : '';
        $sb = function_exists( 'kaiko_cart_size_attr_value' ) ? kaiko_cart_size_attr_value( $b ) : '';
        if ( $sa !== $sb ) {
            return strcmp( $sa, $sb );
        }
        // Stable tiebreaker so the order inside a (parent, size) bucket
        // matches the order the customer added them.
        $ka = isset( $a['key'] ) ? (string) $a['key'] : '';
        $kb = isset( $b['key'] ) ? (string) $b['key'] : '';
        if ( isset( $order[ $ka ], $order[ $kb ] ) ) {
            return $order[ $ka ] - $order[ $kb ];
        }
        return 0;
    } );
    return $contents;
}
```

Notes:
- `woocommerce_get_cart_contents` returns the cart's contents property
  AFTER WC has loaded them from session; filtering here doesn't mutate
  the stored session order, just the display order.
- Filter priority 20 so it runs after `wc_load_cart_from_session` has
  settled but before most theme rendering.
- Do NOT change the session storage — if WC decides to reload from
  session mid-request we still want the original insertion order in
  `cart_contents`. This filter is read-only on the array it's handed.
- Test: add 2 rows of Standard, then 1 row of Large, then 1 more row
  of Standard. Cart should show Standard, Standard, Standard, Large —
  not Standard, Standard, Large, Standard.

---

## Issue 2 — Checkout template visually broken on trade-approved account

Tom shared a screenshot of `/checkout/` on a trade-approved account. The
hero banner (`CHECKOUT / Complete Your Order / Secure checkout powered
by Kaiko.`) renders correctly. Below that:

- **BILLING DETAILS** column is unstyled — plain text, no card
  treatment, form inputs look stock-WC (thin-outline pills).
- **YOUR ORDER** column renders with the off-white rounded card — correct.
- A small green/pink crescent icon sits to the left of "Have a coupon?
  Click here to enter your code." — looks like a half-rendered info
  pseudo-element.
- The two columns look roughly side-by-side but the proportions are off.

Screenshot location for reference — ask Tom to re-share or check the
thread. Repro on live at `https://kaikoproducts.com/checkout/` with a
trade-approved user and 2+ line items in cart.

### Diagnosis steps

1. Open `/checkout/` on live as an approved trade user.
2. DevTools → Elements. Check whether `.kaiko-checkout-columns` exists
   as a direct child of `form.checkout` and whether the CSS grid is
   actually applying (Computed styles).
3. Check whether `body` has both `kaiko-page` AND `kaiko-checkout-page`
   classes — those are what the CSS in `assets/css/kaiko-shell.css`
   hooks on (see sections `10. CHECKOUT COLUMNS LAYOUT` and
   `body.kaiko-checkout-page` rules).
4. Check the coupon row — inspect the crescent element. Likely culprits:
   - `.kaiko-page .woocommerce-info` has `border-radius + border-left: 4px`
     (lines ~1358, 1380 in `assets/css/kaiko-shell.css`). If the box has
     collapsed to ~0 width because of a grid rule, only the rounded
     left border paints, producing a crescent.
   - OR Woodmart's own info-icon font didn't load on this page.

### Likely fixes (pick what matches the repro)

- If `kaiko-checkout-page` body class is missing → `inc/checkout-layout.php`
  adds it in `kaiko_checkout_body_class()`. Check whether `is_checkout()`
  is returning false inside that filter (edge case: custom checkout
  endpoints or checkout blocks plugin).
- If `.kaiko-checkout-columns` grid isn't applying → check specificity;
  Woodmart may be overriding `display: flex` or `display: block` on
  `.woocommerce-checkout`. Rule in shell.css is unscoped — bump it to
  `body.kaiko-checkout-page .kaiko-checkout-columns`.
- Billing column needs the same card-treatment as `.kaiko-checkout-review`
  for visual parity. Add a matching background/padding/border-radius
  rule on `.kaiko-checkout-fields` inside the checkout-page scope. Match
  `--kaiko-off-white` + `--kaiko-radius-2xl` + `--kaiko-space-2xl`
  padding so the two columns read as paired cards.
- If the crescent is a collapsed `.woocommerce-info` → make sure the
  info box has `display: block; width: 100%` on checkout, or remove the
  rounded-corner rule specifically for checkout's coupon notice.

### Acceptance

- Billing fields sit in a card that visually mirrors the Your Order card.
- "Have a coupon?" row renders as a single clean WC info bar, no orphan
  crescent.
- Layout is two columns at ≥1024px, stacks at <1024px.
- Works for the trade-approved account Tom was testing on
  (`kaiko_user_can_see_prices()` returns true for that user).

---

## Issue 3 — VAT line missing at checkout

The cart / checkout review tables show **Subtotal** and **Total** rows
only. No VAT breakdown. Product pages + cart imply "VAT applied at
checkout" elsewhere on the site, so seeing no VAT line at checkout
looks like an error to the customer and could block trade customers
from completing the order (they need the VAT number on the invoice).

### First, verify WC settings — DO NOT skip this

In `wp-admin` → WooCommerce → Settings → Tax:
- **Prices entered with tax:** whatever the store owner wants (likely
  "ex-VAT" for B2B).
- **Display prices during cart and checkout:** must be either
  "Excluding tax" (then WC emits a `tax-total` tfoot row) or
  "Including tax" (then no separate line — which is probably why
  Tom sees no VAT).
- **Display tax totals:** "As a single total" is what trade
  customers expect ("VAT £x.xx").

If the setting is wrong, that's the entire fix — no code. Confirm with
Tom before writing code.

### If the setting is right but the row is still missing

Then the theme/template is hiding it. Check:

1. `woocommerce/cart/cart-totals.php` or `woocommerce/checkout/review-order.php`
   overrides — none should exist in kaiko-child per a glob I ran, but
   verify. If any, they need to include `<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>` rows.
2. `assets/css/kaiko-shell.css` — search for rules that could hide
   `.tax-total`, `.tax-rate`, `.includes_tax`, or tfoot rows in
   `.woocommerce-checkout-review-order-table` / `.cart_totals`.
3. Any `inc/*.php` that removes the `woocommerce_cart_totals_before_order_total`
   / `woocommerce_cart_totals_order_total_html` hooks.

### Acceptance

- Checkout review order table shows a row labelled "VAT" (or equivalent
  tax label) between subtotal and total.
- Tax amount is correct for the cart contents.
- Trade-approved users see this on `/checkout/`.

---

## PR / Deploy notes

1. Single PR with all three fixes off `main`.
2. No changes to the PDP accumulator, the AJAX batch handler, or the
   cart-side (parent, size) grouping — those are working as intended.
3. When merged, Tom clicks Deploy Repository on 20i so the new `main`
   lands on the live server.

## How to run

- Branch: `fix/cart-grouping-and-checkout`
- Each fix as its own commit so they can be reverted independently if
  one misbehaves on live.
- Smoke-test checklist for Tom at the end of the PR description:
  1. Cart shows Standard rows grouped before Large rows.
  2. Checkout billing + order summary look like paired cards.
  3. Checkout shows a VAT line.
