# Mix-and-Match PDP Accumulator — Implementation Brief for Claude Code

## Context

You are working in `/Users/thomasmay/Desktop/Larry/kaiko-child` — the Kaiko Products child theme for kaikoproducts.com (Woodmart parent). This is NOT the Silkworm Store project. The repo's `CLAUDE.md` one level up belongs to Silkworm; ignore it. The theme has its own conventions you can read in the `inc/` headers and existing code.

Tom approved the staged UX design on 2026-04-22 after reviewing a clickable preview at `/Users/thomasmay/Desktop/Larry/kaiko-pdp-mix-match-preview.html`. Open that file in a browser before you start coding — it is the canonical reference for visual + interaction behaviour. Match it.

Deployment is via 20i's "Deploy Repository" panel — push to a feature branch, open a PR, merge to `main`, and tell Tom to click Deploy. Do not merge directly to `main`.

## Goal

On every variable product that has wholesale tiers configured AND has a `pa_colour` (or `pa_color`) attribute, replace the native WooCommerce variations form with a multi-colour accumulator. Customers build up a list of `(colour, size, qty)` rows, see live tier pricing **per size**, and commit everything on a single Add-to-Cart click. Each row becomes its own line item. Products that don't meet the gate keep their current behaviour and the existing `assets/js/kaiko-variation-swap.js`.

## Critical behaviour — sizes are INDEPENDENT tier pools

**Each size builds its own tier qualification pool.** Small + Large qty does NOT combine for tier purposes. 3× Small + 3× Large is NOT 6 units for tier-2 — it's 3 Small at tier-1 AND 3 Large at tier-1. Only 6+ units *of the same size* unlocks tier-2 for that size. This is a deliberate departure from the prior behaviour documented in `inc/mix-and-match-pricing.php`.

This means BOTH ends of the system need updating:

1. **PDP JS** calculates tier per size group (not against the global sum).
2. **Cart-side** `inc/mix-and-match-pricing.php` must also change — it currently keys `$qty_by_parent` by `product_id` only, needs to key by `(product_id, size_attribute_value)`. Otherwise customers see one price on the PDP and a different total at the cart. See the "Cart-side change" section below.

## Architecture

Three new files and two edits. Keep each file focused.

### New files

**`inc/mix-match-accumulator.php`** — server-side gate, asset enqueue, AJAX handler.
- `kaiko_pdp_should_use_accumulator( $product )` → bool. True when product is variable, has tiers (`kaiko_get_product_tiers()` non-empty), and exposes a `pa_colour` (or `pa_color`) attribute with ≥2 terms. Apply filter `kaiko_pdp_accumulator_enabled` so we can kill-switch per product.
- `kaiko_pdp_stack_attribute_slug( $product )` → string. Returns `'pa_colour'` or `'pa_color'`. Filter `kaiko_pdp_stack_attribute`.
- `kaiko_pdp_size_attribute_slug( $product )` → string|null. Returns the size-like attribute slug (first non-colour attribute). Filter `kaiko_pdp_size_attribute`.
- `kaiko_pdp_variation_map( $product )` → array shaped for JS: `[ variation_id => [ 'colour' => slug, 'size' => slug|null, 'price' => float, 'in_stock' => bool, 'image_url' => url ] ]`. Serialize with `wp_json_encode` + `esc_attr` onto the accumulator root.
- `kaiko_pdp_colour_terms( $product )` → ordered `[ slug, name, hex ]` for swatches. Hex from ACF `kaiko_colour_hex` on term, fallback `#CEC6B3`.
- `kaiko_pdp_size_terms( $product )` → ordered `[ slug, name ]` for the size chip tabs. Returns empty array if no size attribute.
- AJAX endpoint `wp_ajax_kaiko_batch_add_to_cart` + `wp_ajax_nopriv_*`. Accepts `rows: [{ variation_id, product_id, quantity }, ...]` + nonce `kaiko_batch_atc`. Loops calling `WC()->cart->add_to_cart()`. Returns `{ ok, added, cart_hash, cart_contents_count, error? }`.
- Conditional enqueue: when gate passes, enqueue `kaiko-mix-match.js` (deps `['jquery', 'wc-add-to-cart-variation']`) and `kaiko-mix-match.css`. Localise nonce + AJAX URL.
- Guard with `defined('ABSPATH') || exit;` and escape everything.

**`template-parts/kaiko-pdp-accumulator.php`** — the rendered markup.
- Accept `$product` and `$args` via `get_template_part()` — follow the pattern used by existing `template-parts/kaiko-*.php` files.
- Render outer `<div class="kaiko-mm" data-variation-map="...">`.
- Hidden native `<form class="variations_form" style="display:none">` — call `woocommerce_variable_add_to_cart()` inside so `product_variations` JSON is reachable for any WC JS that expects it.
- **Size tabs** (if a size attribute exists): rounded pill-group, one tab per size term. Active tab has a badge showing qty selected in that size (hidden when 0). First size is active by default. If there is no size attribute, skip the tabs entirely and render a single implicit group.
- **Active-size context line** — teal info row under the tabs showing either "Tap a colour to add to the **Large** pool" OR "You have **3 units in Large** · tier 1 at £10.49/unit".
- **Colour swatch grid** — one button per colour term. Ticks light up for colours present in the active size only (not across all sizes).
- **Selection panel grouped by size** — one `.kaiko-mm__group` per size that has at least one row. Each group header shows: `Size: Large`, a per-group tier chip (`Tier 2 · £9.23/unit · saving £7.56`), a per-group subtotal. Rows list under the header with ±/qty/✕.
- **Grand total row** (dark background) + ATC button. ATC label: "Add to cart — £86.85" with a count pill "7 items".
- The existing `.kaiko-pp-tiers` card stays above — informational only now. Its note reads "Each size qualifies for tiers independently". No pill-click behaviour in v2 (ambiguous with multiple size pools).

**`assets/js/kaiko-mix-match.js`** — state machine + per-size tier calc + AJAX batch.
- Self-executing, no globals. jQuery OK for event delegation.
- Parse `data-variation-map` once on init.
- State: `{ activeSize: slug|null, sizes: [slug...], rows: [{ size, colour, qty, variationId, hex, name }] }`.
- Derivations: `qtyForSize(size)`, `tierForQty(q)` (reads `.kaiko-pp-tier` data attrs), `grandTotal()`, `grandSavings()`, `sizesInUse()`, `rowsBySize(size)`, `findRow(size, colour)`.
- Mutations: `toggleColour(colour, hex)` (adds to activeSize or removes if already present), `bumpRow(idx, delta)`, `setRowQty(idx, qty)`, `removeRow(idx)`, `switchActiveSize(size)` (NO confirm dialog — switching just pivots the UI, nothing is destroyed).
- `render()` updates: size tabs incl. per-size qty badges, active-size context line, colour swatch ticks (reflecting active size only), grouped-by-size selection panel, per-group tier chips + subtotals, grand total, ATC label + count + enabled state, hint text.
- **Per-group tier:** each rendered group shows the tier matching its OWN qty. Group tier chip includes saving amount vs base price.
- **No global tier-pill highlight in v2** — tiers are per-size, so a single global highlight would be misleading. Tier card is informational only.
- **`resolveVariation(size, colour)`** — walks variation map to find the matching `variation_id`. Returns null if combo unavailable (swatch gets `.is-oos` state and can't be added).
- `submitBatch()` → POST to `kaikoData.ajaxUrl` with `kaiko_batch_add_to_cart`, nonce, rows. On success trigger `$(document.body).trigger('wc_fragment_refresh')` + open Woodmart mini-cart (check the theme for actual event — likely `$('body').trigger('wd-side-cart-open')`; fall back to clicking mini-cart toggle). On partial/full failure show inline error, keep selection intact so the user can retry.

**`assets/css/kaiko-mix-match.css`** — lift from the preview HTML's `<style>` block, scoped with `.kaiko-mm` prefix and `body.kaiko-product-page` ancestor where relevant. Drop the preview-banner and wrap/gallery styles — those belong to the existing PDP template. Keep `.pp-size-tabs`, `.pp-size-context`, `.pp-swatch`, `.pp-selection`, `.pp-group`, `.pp-row`, `.pp-atc` — rename under `.kaiko-mm` namespace (e.g. `.kaiko-mm__size-tabs`, `.kaiko-mm__group`, `.kaiko-mm__row`, etc.).

### Edits to existing files

**`woocommerce/single-product.php`** — where it currently calls `woocommerce_template_single_add_to_cart()` (search for that exact string), wrap in a gate:
```php
if ( kaiko_pdp_should_use_accumulator( $product ) ) {
    get_template_part( 'template-parts/kaiko-pdp-accumulator', null, array( 'product' => $product ) );
} elseif ( $product->is_purchasable() && $product->is_in_stock() ) {
    woocommerce_template_single_add_to_cart();
} else {
    echo '<p class="kaiko-pp-oos">' . esc_html__( 'Currently out of stock — contact info@kaikoproducts.com for availability.', 'kaiko-child' ) . '</p>';
}
```

**`functions.php`** — add `require_once KAIKO_DIR . '/inc/mix-match-accumulator.php';` in the phase 5 includes block alongside `mix-and-match-pricing.php`. Do NOT enqueue the new assets from `functions.php` — keep enqueue logic inside the new inc file.

## Cart-side change — `inc/mix-and-match-pricing.php`

The existing file sums `$qty_by_parent` by `product_id` only. It needs to sum by `(product_id, size_attribute_value)` so tiers qualify per-size, matching the PDP UI. Concrete edits:

1. Add a helper `kaiko_cart_size_attr_value( $cart_item )` that pulls the size attribute off the cart item's `variation` array. Use `kaiko_pdp_size_attribute_slug()` to know which key. Return `''` if the product has no size attribute (falls back to parent-only pooling — i.e. products that only have colours, no sizes, still pool).
2. Change `kaiko_cart_parent_total_qty()` signature to `kaiko_cart_group_total_qty( $parent_id, $size_value, $cart = null )`. Update cache key to `$parent_id . '|' . $size_value`.
3. In `kaiko_apply_mix_and_match_tiers()`, replace `$qty_by_parent[$pid]` with `$qty_by_group[ $pid . '|' . $size_value ]`. Apply the matching tier using that group's total, not the parent-wide total.
4. Update the file header docblock to reflect the new behaviour: "Tiers qualify per `(parent, size)` group. Mixing colours within a size pools; mixing sizes does NOT." Remove the contradictory existing example about "3× Rainbow-M + 3× Reptile-Green-M triggers tier-2" — or rewrite it correctly (that example IS valid because both are M, so it pools).
5. Write a short regression test note at the bottom listing the scenarios you verified manually.

**Important:** any existing callers of `kaiko_cart_parent_total_qty()` need updating. Grep for usages. The `template-parts/kaiko-cart-line-tier.php` lookup that feeds the cart-line tier chip is the most likely caller — update it to pass the size value.

## Acceptance criteria

Tom will verify in this order:

1. On a product with tiers + colours (e.g. "Ecosystem Rock") while logged in as an approved trade user, the accumulator renders instead of the native form.
2. Picking the active size tab to **Large** then tapping a colour adds a row under a "Size: Large" group. Tapping a second colour adds another row inside the same Large group.
3. Bumping qty via +/- or typing updates that size group's subtotal, tier chip, and the grand total. Other size groups are unaffected.
4. Switching the active size tab to **Standard** does NOT clear anything — the Standard group starts empty and the Large group is preserved. Tapping colours now builds the Standard group alongside.
5. Each size group shows its OWN tier. 6× Large and 3× Standard = Large at tier-2 (£9.23/unit), Standard at tier-1 (£10.49/unit). Grand total sums the tiered subtotals.
6. Clicking ATC sends one AJAX request; all rows across all sizes commit as separate line items.
7. **Cart verification:** 3× Small + 3× Large of the same parent keeps each at tier-1 (no pooling across sizes). 6× Large alone upgrades all Large lines to tier-2. Per-line tier chip in the cart reflects the per-size pool.
8. On a product with tiers but NO colour attribute, the native variations form still renders (no regression).
9. On a simple product with no tiers, nothing changes.
10. Out-of-stock (colour × active size) combos render swatches as `.is-oos` and can't be added.
11. No JS errors in console on any PDP or the cart page.

## Branch + PR

- Branch name: `feature/pdp-mix-match-accumulator`
- Commit granularity: one commit per file is fine; atomic per logical unit is better. First commit should be the backend scaffold (inc file + gate wiring), then template partial, then JS, then CSS.
- PR title: `PDP: mix-and-match colour accumulator for tiered variable products`
- PR body: link to `MIX_MATCH_CC_BRIEF.md` + the preview file path. List the acceptance criteria as a checklist. Flag any deviations from this brief with reasoning.
- Do NOT merge yourself. Wait for Tom to merge + click Deploy on 20i.

## Constraints and gotchas

- Kaiko uses a blocklist-style role check `kaiko_user_can_see_prices()`. The accumulator template must respect it — for non-approved users, show the existing pending/apply-for-access CTAs instead. Mirror what the template currently does around `$can_purchase`.
- `kaiko_get_product_tiers()` returns either an ACF-defined schedule (absolute unit prices) or the default schedule (discount percentages applied to base). Variation price in the JS must be per-variation — pull `display_price` from the variation map, not the tier unit-price, when computing base.
- The existing `kaiko-variation-swap.js` must NOT load on accumulator PDPs (they are mutually exclusive). Add the opposite condition to its enqueue: `is_product() && ! kaiko_pdp_should_use_accumulator( $product )`.
- WooCommerce Blocks cart is not used on Kaiko — this is classic cart. `woocommerce_add_to_cart_fragments` still works, so the Woodmart mini-cart fragment refresh will update after the AJAX batch.
- If any row's `add_to_cart()` call returns false in the AJAX handler, do not rollback earlier successful adds — just return `{ ok: false, added: <partial count>, error: '...' }` and let the JS show a non-fatal warning. Tom would rather see 3 of 4 lines in the cart than zero.
- Escape everything. `esc_attr` on data attrs, `esc_html` on text, `wp_kses_post` only on known-safe WC price HTML. No raw `echo` of user-reachable data.

## Open calls for Tom

1. **Products with >1 non-colour attribute** (e.g. `pa_size` AND `pa_finish`). Current brief assumes the FIRST non-colour attribute is the size-tab axis and any further attributes are ignored. If Kaiko has any products shaped like that, Tom needs to flag them so we can decide on a matrix UX (would be a v2 of this work).
2. **Sub-lookbook tier cap.** If a size group has 0 units, it's excluded from everything — but what if a trade customer wants to top up one size to unlock a tier AND has a partial order on another size? The current brief treats them independently per Tom's instruction. If cross-size qualification is ever wanted as a bonus path (e.g. "unlock tier-2 on ALL sizes if grand total ≥ 24"), that's a future filter hook — not in v1.
