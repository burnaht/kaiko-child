# Kaiko — Full checkout redesign

Replace the current half-styled checkout with a proper Kaiko-branded checkout
that matches the approved preview and the existing cart page design language.

**NOT Silkworm Store.** This is kaiko-child (Woodmart parent, Woo 9.x, PHP 8.x,
trade-gated B2B). Ignore `/Users/thomasmay/Desktop/Larry/CLAUDE.md` — it
describes a different project. Kaiko has **no caching** (no WP Rocket, no
Redis, no CDN) so changes go live the moment 20i fast-forwards `main`.

- Branch: `feature/checkout-redesign`
- Single PR off `main`.
- Scope: `/checkout/` page only.
  **Out of scope:** the site header, the cart page, the thank-you page
  (`/checkout/order-received/`), the cart-to-checkout flow, the trade-approval
  gate. Leave all of those alone.

---

## Source of truth

1. **Approved preview — the visual target:**
   `/Users/thomasmay/Desktop/Larry/kaiko-checkout-preview.html`
   (open in a browser; the implementation should render the live checkout
   indistinguishable from this, bar the real WC form markup and live product
   data.)

2. **Sibling reference — cart page design language we're matching:**
   `/Users/thomasmay/Desktop/Larry/kaiko-previews/cart.html`

3. **Existing cart styles already in the theme (match these patterns):**
   `assets/css/kaiko-cart.css` — use the same card / shadow / radius / spacing
   tokens so checkout reads as the cart page's sibling.

Design tokens used in the preview (if equivalents don't already exist in
`assets/css/kaiko-shell.css` / `kaiko-cart.css`, add them in a scoped file):

```
bg page:       #fafafa
hero bg:       #fef5f0
card bg:       #ffffff
teal primary:  #1a5c52   (CTA, chips, focus ring, links)
teal dark:     #134840   (CTA hover)
teal soft:     #e6f0ec   (chip bg)
teal tint:     #f4f9f6   (bank notice bg)
teal border:   #d6e8df   (bank notice border)
ink:           #1a1a1a
text muted:    #666
border:        #e0e0e0 / #f0f0f0 (divider)
radius card:   8px
radius input:  4px
shadow card:   0 1px 3px rgba(0,0,0,0.05)
font:          Inter (already loaded via preview; use existing
               --kaiko-font-body if it resolves to Inter, otherwise
               load Inter 400/500/600/700 from Google Fonts)
grid:          2fr 1fr at 1200px max-width, 2rem gap
```

---

## Payment method — READ CAREFULLY

Kaiko is **bank transfer only**. There is **no Mollie, no Klarna, no card**.
Customers receive an email with bank details and a reference after placing
the order; the order sits as on-hold until the transfer lands.

The current theme already has BACS as the intended method (see the
order-received template at `woocommerce/checkout/thankyou.php` and the CSS
block referenced in `functions.php` line 104).

**Do not add** any payment-gateway selection UI. The preview shows a single
"Pay by bank transfer" notice panel instead of a gateway list. That's the
target.

If WC is currently rendering a gateway list on `/checkout/`, it's because
BACS is the only enabled gateway and WC still shows it as a single radio
option. Our template override will replace that gateway area with the
branded notice panel.

---

## What the page should contain

Match the preview exactly. Summarised:

**Hero** (pink-peach band, full-width, above the columns)
- Eyebrow: "Checkout"
- H1: "Complete your order"
- Sub: "Secure checkout — your cart is saved until you place the order."

The theme already renders this hero in `template-checkout.php` via the
`.kaiko-checkout-hero` markup. Keep that template — it works. The hero CSS
already exists at `assets/css/kaiko-shell.css` lines ~689–724. Leave the
hero alone unless a spacing tweak is needed to match the preview.

**Grid** — `2fr 1fr` at `max-width: 1200px`, `gap: 2rem`, stacks at ≤1024px.

**Left column — fields**
1. Coupon card (white, rounded, horizontal prompt + input + Apply button).
   Styled like the cart's coupon row.
2. Billing details card — standard WC billing fields but laid out in a
   clean 2-column grid (first/last name side-by-side, company/country/
   address/etc as labelled inputs with uppercase 12px grey labels, red asterisk
   for required, small "(optional)" tag in grey). Includes the "Ship to a
   different address?" checkbox at the bottom of the card.
3. Order notes card — single textarea for order notes. Optional.
4. Payment card — **bank-transfer notice only**. Icon + title
   ("Pay by bank transfer") + copy explaining bank details are emailed
   after placing the order. Below it, the terms-and-conditions checkbox.

All four cards share the same shadow/radius/padding treatment. The billing
card has a tiny "Step 1 of 3" / "Step 2 of 3" / "Step 3 of 3" label in the
card head (top-right, small uppercase teal). Keep these — Tom approved them.

**Right column — sticky order summary**
- H2: "Your order"
- Order lines grouped by size (Small / Large / XL headings). Each line
  shows: 56×56 product image with a teal qty badge top-right, product name,
  variant / colour line with a tier chip (e.g. `TIER 1`, `TIER 2`), line
  total on the right.
- Totals block: Subtotal, Wholesale savings (in teal, negative), Shipping,
  VAT (20%).
- Grand total row with `inc. VAT` subtext.
- "Place order & receive bank details" primary button (teal, full width).
- Trust row: small padlock icon + "Secure SSL checkout — bank details sent
  after order".
- Payment chips row: single chip "UK BANK TRANSFER" (replace the current
  VISA/MC/AMEX/KLARNA row).

At ≤1024px the summary loses `position: sticky` and stacks under the fields.

---

## Files to change

### 1. `woocommerce/checkout/form-checkout.php` (rewrite)

Current file is a bare two-column scaffold. Restructure so the left column
renders as **four separate cards** instead of one big `.kaiko-checkout-fields`
wrapper.

Proposed structure (pseudo-markup — keep the real WC hooks intact):

```php
<form name="checkout" method="post" class="checkout woocommerce-checkout kaiko-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <div class="kaiko-checkout-columns">

        <div class="kaiko-checkout-fields">

            <!-- Card 1: Coupon -->
            <div class="kaiko-co-card kaiko-co-card--coupon">
                <?php wc_get_template( 'checkout/form-coupon.php', array( 'checkout' => $checkout ) ); ?>
            </div>

            <?php if ( $checkout->get_checkout_fields() ) : ?>

                <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                <!-- Card 2: Billing + shipping toggle -->
                <div class="kaiko-co-card kaiko-co-card--billing">
                    <div class="kaiko-co-card__head">
                        <h3><?php esc_html_e( 'Billing details', 'kaiko-child' ); ?></h3>
                        <span class="kaiko-co-step">Step 1 of 3</span>
                    </div>
                    <div id="customer_details">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                        <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                    </div>
                </div>

                <!-- Card 3: Order notes -->
                <div class="kaiko-co-card kaiko-co-card--notes">
                    <div class="kaiko-co-card__head">
                        <h3><?php esc_html_e( 'Order notes', 'kaiko-child' ); ?></h3>
                        <span class="kaiko-co-step">Step 2 of 3</span>
                    </div>
                    <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
                </div>

            <?php endif; ?>

            <!-- Card 4: Payment / terms -->
            <div class="kaiko-co-card kaiko-co-card--payment">
                <div class="kaiko-co-card__head">
                    <h3><?php esc_html_e( 'Payment', 'kaiko-child' ); ?></h3>
                    <span class="kaiko-co-step">Step 3 of 3</span>
                </div>
                <?php // Rendered by our review-order.php override (see below) — ?>
                <?php // specifically the bank-transfer notice + terms. The ?>
                <?php // normal WC gateway list is suppressed. ?>
                <?php echo kaiko_render_bank_transfer_notice(); ?>
                <?php wc_get_template( 'checkout/terms.php' ); ?>
            </div>

        </div>

        <div class="kaiko-checkout-review">
            <h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

            <div id="order_review" class="woocommerce-checkout-review-order">
                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
            </div>

            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
        </div>

    </div>

</form>
```

Notes:
- Leave the surrounding `do_action( 'woocommerce_before_checkout_form', $checkout )`
  and `do_action( 'woocommerce_after_checkout_form', $checkout )` calls
  intact — other plugins / WC core hook into them.
- `woocommerce_checkout_before_customer_details` and
  `woocommerce_checkout_after_customer_details` still fire in this
  arrangement, just around the billing card rather than around a wrapping
  `#customer_details`.
- Keep `#customer_details`, `#order_review`, and
  `#order_review_heading` IDs — WC's JS hooks into them.
- `.col-1` / `.col-2` wrappers have been dropped; the billing and shipping
  hooks now both render inside the same billing card. WC Blocks is **not**
  enabled here (classic shortcode checkout), so this is safe.

### 2. `woocommerce/checkout/review-order.php` (NEW override)

Default WC table is bottom-less — no thumbnails, no per-size grouping,
standard WC styling. Replace with a template that:

- Iterates `WC()->cart->get_cart()` (already sorted by our PR #16
  `kaiko_sort_cart_by_parent_and_size` filter into `product_id` → size groups
  via `woocommerce_get_cart_contents`).
- Tracks a running `$current_size` while iterating; when it changes, emit a
  `<div class="kaiko-co-size-heading">Small|Large|XL</div>`.
- For each cart item, render:
  ```html
  <div class="kaiko-co-line">
    <div class="kaiko-co-line__image">
      <?php echo $product->get_image( array( 56, 56 ) ); ?>
      <span class="kaiko-co-line__qty"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
    </div>
    <div class="kaiko-co-line__meta">
      <div class="kaiko-co-line__name"><?php echo esc_html( $product->get_title() ); ?></div>
      <div class="kaiko-co-line__variant">
        <?php echo esc_html( $colour_attr ); ?>
        <?php if ( $tier ) : ?><span class="kaiko-co-chip"><?php echo esc_html( $tier_label ); ?></span><?php endif; ?>
      </div>
    </div>
    <div class="kaiko-co-line__total"><?php echo wc_price( $line_total ); ?></div>
  </div>
  ```
- Use `kaiko_cart_size_attr_value( $cart_item )` from
  `inc/mix-and-match-pricing.php` for the size header. For the colour line,
  pull the colour attribute from the variation (`pa_colour` or whatever the
  attribute slug is — grep the variation attrs). For the tier chip, use the
  existing `kaiko_get_cart_item_tier_label()` / `kaiko_get_product_tiers()`
  helpers if they exist; otherwise leave the chip out for v1 and open a
  follow-up task.
- After the lines, emit the totals block:
  - Subtotal → `WC()->cart->get_cart_subtotal()` (or
    `wc_cart_totals_subtotal_html()` — use whichever the existing checkout
    row uses)
  - Wholesale savings — only render if
    `kaiko_get_cart_wholesale_savings()` or equivalent exists and returns
    a positive value. Chase the implementation from `inc/mix-and-match-pricing.php`
    or wherever tier discounts are computed. If no helper exists, omit the
    row for v1 and log a TODO.
  - Shipping → `wc_cart_totals_shipping_html()`
  - VAT (20%) → loop `WC()->cart->get_tax_totals()` and render each.
    Fallback label: "VAT (20%)".
  - Grand total → `WC()->cart->get_total()` with "inc. VAT" subtext.
- Render `<?php do_action( 'woocommerce_review_order_before_submit' ); ?>` /
  `after_submit` around the Place Order button so other integrations can
  still inject.
- Place Order button label: "Place order & receive bank details"
  (override via `woocommerce_order_button_text` filter in
  `inc/checkout-layout.php` rather than hard-coding, so any WC upgrades
  that tweak the template don't lose the label).

Skip `<?php do_action( 'woocommerce_review_order_before_payment' ); ?>`
and the gateway list entirely in this override — the payment notice lives
in the left column's Payment card instead.

### 3. `inc/checkout-layout.php` — add helpers

Add at the bottom of the file (keep everything already there):

```php
/**
 * Branded bank-transfer notice — rendered in the Payment card.
 */
function kaiko_render_bank_transfer_notice() {
    ob_start();
    ?>
    <div class="kaiko-co-bank">
        <div class="kaiko-co-bank__icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10l9-6 9 6"/>
                <path d="M5 10v9"/><path d="M19 10v9"/><path d="M9 10v9"/><path d="M15 10v9"/>
                <path d="M3 19h18"/>
            </svg>
        </div>
        <div class="kaiko-co-bank__body">
            <div class="kaiko-co-bank__title"><?php esc_html_e( 'Pay by bank transfer', 'kaiko-child' ); ?></div>
            <p><?php esc_html_e( "Once you place your order, we'll email you our bank details along with a reference number. Your order is held until payment arrives, then dispatched from our UK warehouse.", 'kaiko-child' ); ?></p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Override the Place Order button label.
 */
add_filter( 'woocommerce_order_button_text', function() {
    return __( 'Place order &amp; receive bank details', 'kaiko-child' );
} );

/**
 * Trust line text filter — the theme may render an SSL strap in the
 * review template; keep a single source of truth.
 */
function kaiko_checkout_trust_line() {
    return esc_html__( 'Secure SSL checkout — bank details sent after order', 'kaiko-child' );
}
```

### 4. `assets/css/kaiko-shell.css` — cleanup + new card styles

Surgical change: the existing `.kaiko-checkout-section ...` block at lines
~732–799 assumes a flat form with no cards, and the
`.kaiko-checkout-columns` block at ~1128–1200 wraps the fields in a single
off-white card. Both fight the new per-section card layout.

- **Delete** the `.kaiko-checkout-fields { background / padding / border-radius }`
  rule at ~1140–1146 — the fields column no longer has a single card
  backdrop; each of its four children is its own card.
- **Delete** the `.kaiko-checkout-review { background / padding / border-radius / sticky }`
  rule at ~1148–1154 **only if** replacing with the new `.kaiko-co-review`
  card rule. The sticky behaviour must survive.
- Remove or scope-down the `.kaiko-checkout-section .form-row input` /
  `.woocommerce-billing-fields h3` etc. rules at ~738–779 — they style
  WC's default flat markup and no longer match the new card-scoped
  structure. Either delete or rescope under `.kaiko-co-card` so they
  only apply inside our new cards.
- The grid rule at ~1133–1138 (`body.kaiko-checkout-page .kaiko-checkout-columns`)
  stays but update the column ratio to `2fr 1fr` and `gap: 2rem` to match
  the preview.

Add a new section at the bottom of `assets/css/kaiko-shell.css` (or
better — create `assets/css/kaiko-checkout.css` and enqueue conditionally
on `is_checkout()` via `inc/checkout-layout.php`; cleaner and keeps the
shell slimmer). All rules scoped under `body.kaiko-checkout-page` for
safety:

```css
/* Page canvas */
body.kaiko-checkout-page { background: #fafafa; }

/* Grid */
body.kaiko-checkout-page .kaiko-checkout-columns {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  align-items: start;
}

@media (max-width: 1024px) {
  body.kaiko-checkout-page .kaiko-checkout-columns { grid-template-columns: 1fr; }
}

/* Generic card */
body.kaiko-checkout-page .kaiko-co-card {
  background: #fff;
  border-radius: 8px;
  padding: 1.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  margin-bottom: 1.5rem;
}

body.kaiko-checkout-page .kaiko-co-card__head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid #f0f0f0;
}
body.kaiko-checkout-page .kaiko-co-card__head h3 {
  font-size: 15px; font-weight: 600; color: #1a1a1a; letter-spacing: 0.3px;
  margin: 0; padding: 0; border: 0;
}
body.kaiko-checkout-page .kaiko-co-step {
  font-size: 11px; font-weight: 700; color: #1a5c52;
  text-transform: uppercase; letter-spacing: 1px;
}

/* Coupon card — compact */
body.kaiko-checkout-page .kaiko-co-card--coupon {
  padding: 1.25rem 1.75rem;
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
}

/* Billing form — inputs / labels to match preview */
body.kaiko-checkout-page .kaiko-co-card .form-row {
  display: flex; flex-direction: column; gap: 0.4rem; margin: 0 0 1rem 0;
}
body.kaiko-checkout-page .kaiko-co-card .form-row-first,
body.kaiko-checkout-page .kaiko-co-card .form-row-last {
  width: calc(50% - 0.5rem);
  float: none;
}
/* ... and so on — grid the wc form-rows into a 2-col layout. */

/* Bank notice */
body.kaiko-checkout-page .kaiko-co-bank {
  display: flex; gap: 0.9rem; align-items: flex-start;
  padding: 1.1rem 1.2rem;
  background: #f4f9f6; border: 1px solid #d6e8df; border-radius: 6px;
}
body.kaiko-checkout-page .kaiko-co-bank__icon {
  flex-shrink: 0; width: 38px; height: 38px; border-radius: 8px;
  background: #fff; color: #1a5c52;
  display: flex; align-items: center; justify-content: center;
}
body.kaiko-checkout-page .kaiko-co-bank__title {
  font-size: 14px; font-weight: 700; color: #1a5c52;
  margin-bottom: 0.35rem;
}
body.kaiko-checkout-page .kaiko-co-bank__body p {
  font-size: 13px; color: #555; line-height: 1.55;
}

/* Review card (sticky) */
body.kaiko-checkout-page .kaiko-checkout-review {
  background: #fff; border-radius: 8px; padding: 1.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  position: sticky; top: 90px;
}
body.kaiko-checkout-page .kaiko-checkout-review h3#order_review_heading {
  font-size: 18px; font-weight: 600; margin-bottom: 1.25rem;
  padding-bottom: 0.9rem; border-bottom: 1px solid #f0f0f0;
  /* Remove any inherited uppercase/teal/larger display font from the old
     .kaiko-checkout-section rule. */
}
@media (max-width: 1024px) {
  body.kaiko-checkout-page .kaiko-checkout-review { position: static; }
}

/* Order lines */
body.kaiko-checkout-page .kaiko-co-size-heading {
  font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1px; color: #999;
  padding-top: 0.75rem; margin-top: 0.25rem;
  border-top: 1px dashed #eee;
}
body.kaiko-checkout-page .kaiko-co-size-heading:first-child {
  border-top: none; padding-top: 0; margin-top: 0;
}
body.kaiko-checkout-page .kaiko-co-line {
  display: grid; grid-template-columns: 56px 1fr auto;
  gap: 0.8rem; align-items: center;
  padding: 0.55rem 0;
}
body.kaiko-checkout-page .kaiko-co-line__image {
  position: relative; width: 56px; height: 56px;
  background: #e8e8e8; border-radius: 6px; overflow: hidden;
}
body.kaiko-checkout-page .kaiko-co-line__image img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
body.kaiko-checkout-page .kaiko-co-line__qty {
  position: absolute; top: -6px; right: -6px;
  min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px;
  background: #0f3f38; color: #fff;
  font-size: 11px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
body.kaiko-checkout-page .kaiko-co-line__name {
  font-size: 13px; font-weight: 600; color: #1a1a1a; line-height: 1.35;
}
body.kaiko-checkout-page .kaiko-co-line__variant {
  font-size: 12px; color: #666;
}
body.kaiko-checkout-page .kaiko-co-chip {
  display: inline-block; font-size: 10px; font-weight: 700;
  color: #1a5c52; background: #e6f0ec;
  padding: 2px 7px; border-radius: 999px; margin-left: 6px;
  text-transform: uppercase; letter-spacing: 0.4px;
}
body.kaiko-checkout-page .kaiko-co-line__total {
  font-size: 13px; font-weight: 600; color: #1a1a1a;
}

/* Totals block */
body.kaiko-checkout-page .kaiko-co-totals {
  display: flex; flex-direction: column; gap: 0.7rem;
  padding: 1rem 0;
  border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}
body.kaiko-checkout-page .kaiko-co-totals .row {
  display: flex; justify-content: space-between;
}
body.kaiko-checkout-page .kaiko-co-totals .row.savings .value { color: #1a5c52; }
body.kaiko-checkout-page .kaiko-co-grand {
  display: flex; justify-content: space-between; align-items: baseline;
  padding: 1.1rem 0; border-bottom: 2px solid #f0f0f0;
  margin-bottom: 1.25rem;
}
body.kaiko-checkout-page .kaiko-co-grand .value {
  font-size: 22px; font-weight: 700;
}
body.kaiko-checkout-page .kaiko-co-grand .inc-tax {
  display: block; font-size: 11px; color: #999; text-align: right;
  margin-top: 2px;
}

/* Place order */
body.kaiko-checkout-page #place_order,
body.kaiko-checkout-page .kaiko-co-place {
  width: 100%;
  background: #1a5c52; color: #fff; border: none;
  padding: 15px; border-radius: 4px;
  font-size: 15px; font-weight: 700; letter-spacing: 0.3px;
  cursor: pointer; transition: background 0.2s;
  margin-bottom: 1rem; margin-top: 0;
}
body.kaiko-checkout-page #place_order:hover,
body.kaiko-checkout-page .kaiko-co-place:hover { background: #134840; }

/* Trust strap + payment chip */
body.kaiko-checkout-page .kaiko-co-trust {
  display: flex; align-items: center; justify-content: center;
  gap: 0.5rem; font-size: 12px; color: #666; margin-bottom: 0.9rem;
}
body.kaiko-checkout-page .kaiko-co-paychips {
  display: flex; align-items: center; justify-content: center;
  gap: 0.6rem; padding-top: 0.9rem; border-top: 1px solid #f0f0f0;
}
body.kaiko-checkout-page .kaiko-co-paychip {
  font-size: 10px; font-weight: 700; letter-spacing: 0.5px;
  color: #999; background: #f5f5f5; border: 1px solid #eaeaea;
  border-radius: 4px; padding: 4px 9px; text-transform: uppercase;
}
```

(Trim the preview's CSS down to what's actually rendered against real WC
markup — the preview uses bespoke classes, the live checkout will mostly
need our new `.kaiko-co-*` classes plus targeted overrides of WC's
`.form-row`, `#place_order`, `.woocommerce-form-coupon`, etc.)

---

## Gotchas / things to watch

1. **Do not touch `template-checkout.php`.** It already wires the hero and
   runs `the_content()` which fires the `[woocommerce_checkout]` shortcode.
   All our changes happen inside the shortcode's rendered output.
2. **Do not touch the order-received (thank-you) template.** Branded BACS
   screen already works.
3. **Do not touch PR #16's cart sort filter** — it's what makes the review
   template's "grouped by size" rendering trivial.
4. **Trade-gated checkout.** Test as a trade-approved user
   (`kaiko_user_can_see_prices()` returns true). Non-approved users get
   redirected away via `kaiko_redirect_pending_from_checkout` and shouldn't
   see this page at all.
5. **VAT row.** Tom enabled WC taxes in the last deploy; the review template
   must iterate `WC()->cart->get_tax_totals()` so the VAT row renders. If
   `get_tax_totals()` is empty on the staging cart it means either taxes
   got disabled again or the customer's address puts them outside the UK
   tax zone — check both before declaring a bug.
6. **No Blocks checkout.** Kaiko uses classic shortcode. Don't add
   `@wordpress/...` dependencies or WC Blocks extensions.
7. **Don't enqueue Inter twice** — check whether `kaiko_enqueue_styles()`
   already loads Inter; if not, add it once. The Google Fonts URL from the
   preview is fine.

## Acceptance (Tom's smoke test on live after deploy)

1. `/checkout/` on an incognito window as a trade-approved account shows:
   a. Pink-peach hero with "Checkout / Complete your order" (unchanged).
   b. Two columns: wide left (fields) + narrower right (order summary).
   c. Four separate white cards on the left: Coupon, Billing, Order notes,
      Payment. No single grey wrapper around them.
   d. Billing form fields sit in a clean 2-col grid, labels are small
      uppercase grey, required fields have a red asterisk.
   e. Payment card shows a single teal-tinted "Pay by bank transfer" panel
      with icon and the "we'll email bank details" copy. No gateway radio
      list. Terms checkbox below.
   f. Right column shows product thumbnails with a teal qty badge, grouped
      under Size headings (Small / Large / XL), with tier chips where the
      item is in a tier.
   g. Totals block shows Subtotal, Wholesale savings (teal, negative),
      Shipping, VAT (20%), Grand total with "inc. VAT" subtext.
   h. CTA reads "Place order & receive bank details".
   i. Trust strap reads "Secure SSL checkout — bank details sent after order".
   j. Payment-chips row shows a single "UK BANK TRANSFER" chip.
2. At ≤1024px the grid stacks and the summary loses sticky.
3. At ≤600px the billing grid collapses to 1 column.
4. Submitting a real test order with items in the cart still completes and
   lands on the order-received page. The order sits as on-hold pending BACS.

## How to run

- Branch: `feature/checkout-redesign`
- Commit split:
  1. PHP: form-checkout.php rewrite + review-order.php + inc/checkout-layout.php additions
  2. CSS: remove old rules + add new `.kaiko-co-*` block (or new file +
     conditional enqueue)
  3. Any small JS needed (none expected)
- Push branch → open PR → Tom reviews diff against this brief + the
  preview → merge → 20i Deploy.
