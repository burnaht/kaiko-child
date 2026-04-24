<?php
/**
 * Kaiko — PDP Mix-and-Match Accumulator (template partial)
 *
 * Rendered in place of WooCommerce's native variations_form on products
 * that pass kaiko_pdp_should_use_accumulator(). Displays size tabs (one
 * tier pool per size), a colour swatch grid that adds rows to the
 * active size's group, a per-size grouped selection panel, grand total,
 * and a single batch ATC button.
 *
 * The native variations_form is rendered hidden inside the accumulator
 * so any third-party JS depending on `product_variations` JSON keeps
 * working, and so WC recognises this PDP as a variable-product page.
 *
 * Expected $args:
 *   - product  WC_Product  The variable product being rendered.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$product = isset( $args['product'] ) && $args['product'] instanceof WC_Product ? $args['product'] : null;
if ( ! $product ) {
	global $product;
}
if ( ! $product instanceof WC_Product ) {
	return;
}

// The parent template already renders pending / logged-out notices and
// only includes this partial inside the $can_purchase branch — but
// defence in depth: re-check the gate so a direct get_template_part()
// call never leaks the form to a non-approved user.
if ( function_exists( 'kaiko_user_can_see_prices' ) && ! kaiko_user_can_see_prices() ) {
	return;
}

$variation_map = kaiko_pdp_variation_map( $product );
$colours       = kaiko_pdp_colour_terms( $product );
$sizes         = kaiko_pdp_size_terms( $product );
$tiers         = function_exists( 'kaiko_get_product_tiers' ) ? kaiko_get_product_tiers( $product->get_id() ) : array();

$default_size = ! empty( $sizes ) ? $sizes[0]['slug'] : '';

// Normalise tier payload for the JS — only fields the front-end needs.
$tier_payload = array();
foreach ( $tiers as $t ) {
	$tier_payload[] = array(
		'min_qty'      => (int) ( $t['min_qty'] ?? 0 ),
		'max_qty'      => (int) ( $t['max_qty'] ?? 0 ),
		'unit_price'   => (float) ( $t['unit_price'] ?? 0 ),
		'discount_pct' => (float) ( $t['discount_pct'] ?? 0 ),
		'is_default'   => ! empty( $t['is_default'] ),
	);
}
?>
<div class="kaiko-mm"
	data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
	data-variation-map="<?php echo esc_attr( wp_json_encode( $variation_map ) ); ?>"
	data-tiers="<?php echo esc_attr( wp_json_encode( $tier_payload ) ); ?>"
	data-default-size="<?php echo esc_attr( $default_size ); ?>"
	data-has-sizes="<?php echo ! empty( $sizes ) ? '1' : '0'; ?>">

	<?php
	// Hidden native variations form — rendered so `product_variations`
	// JSON is reachable for any WC JS that expects it. Not interacted
	// with by the user.
	?>
	<div class="kaiko-mm__native" aria-hidden="true">
		<?php woocommerce_variable_add_to_cart(); ?>
	</div>

	<?php if ( ! empty( $sizes ) ) : ?>
		<div class="kaiko-mm__field">
			<div class="kaiko-mm__field-label">
				<span><?php esc_html_e( 'Currently adding to size', 'kaiko-child' ); ?></span>
				<span class="kaiko-mm__field-hint"><?php esc_html_e( 'Switch anytime — your other selections stay', 'kaiko-child' ); ?></span>
			</div>
			<div class="kaiko-mm__size-tabs" role="tablist" data-kaiko-mm-size-tabs>
				<?php foreach ( $sizes as $i => $s ) :
					$is_active = ( 0 === $i );
					?>
					<button type="button"
						class="kaiko-mm__size-tab<?php echo $is_active ? ' is-active' : ''; ?>"
						data-size="<?php echo esc_attr( $s['slug'] ); ?>"
						role="tab"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
						<span class="kaiko-mm__size-tab-label"><?php echo esc_html( $s['name'] ); ?></span>
						<span class="kaiko-mm__size-tab-count" hidden>0</span>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="kaiko-mm__size-context" data-kaiko-mm-size-context aria-live="polite"></div>
		</div>
	<?php endif; ?>

	<div class="kaiko-mm__field">
		<div class="kaiko-mm__field-label">
			<span><?php esc_html_e( 'Colour', 'kaiko-child' ); ?></span>
			<span class="kaiko-mm__field-hint"><?php esc_html_e( 'Tap to add to the active size', 'kaiko-child' ); ?></span>
		</div>
		<div class="kaiko-mm__colour-grid" data-kaiko-mm-colour-grid>
			<?php foreach ( $colours as $c ) : ?>
				<button type="button" class="kaiko-mm__swatch"
					data-colour="<?php echo esc_attr( $c['slug'] ); ?>"
					data-colour-name="<?php echo esc_attr( $c['name'] ); ?>"
					data-hex="<?php echo esc_attr( $c['hex'] ); ?>">
					<span class="kaiko-mm__swatch-dot" style="background: <?php echo esc_attr( $c['hex'] ); ?>;"></span>
					<span class="kaiko-mm__swatch-name"><?php echo esc_html( $c['name'] ); ?></span>
					<span class="kaiko-mm__swatch-tick" aria-hidden="true">&#10003;</span>
				</button>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="kaiko-mm__selection" data-kaiko-mm-selection>
		<div class="kaiko-mm__selection-header">
			<span class="kaiko-mm__selection-title"><?php esc_html_e( 'Your selection', 'kaiko-child' ); ?></span>
			<span class="kaiko-mm__selection-meta" data-kaiko-mm-selection-meta><?php esc_html_e( '0 sizes · 0 units', 'kaiko-child' ); ?></span>
		</div>
		<div class="kaiko-mm__selection-empty" data-kaiko-mm-selection-empty>
			<strong><?php esc_html_e( 'Start with a size, then tap colours', 'kaiko-child' ); ?></strong>
			<span><?php esc_html_e( 'Each size gets its own tier discount.', 'kaiko-child' ); ?></span>
		</div>
		<div class="kaiko-mm__groups" data-kaiko-mm-groups></div>
	</div>

	<div class="kaiko-mm__atc-zone">
		<div class="kaiko-mm__total">
			<div class="kaiko-mm__total-label">
				<strong><?php esc_html_e( 'Grand total', 'kaiko-child' ); ?></strong>
				<span data-kaiko-mm-total-breakdown><?php esc_html_e( 'Across 0 sizes', 'kaiko-child' ); ?></span>
			</div>
			<div class="kaiko-mm__total-value" data-kaiko-mm-total-value><?php echo wp_kses_post( wc_price( 0 ) ); ?></div>
		</div>
		<button type="button" class="kaiko-mm__atc" data-kaiko-mm-atc disabled>
			<span data-kaiko-mm-atc-label><?php esc_html_e( 'Pick a colour to start', 'kaiko-child' ); ?></span>
			<span class="kaiko-mm__atc-count" data-kaiko-mm-atc-count><?php esc_html_e( '0 items', 'kaiko-child' ); ?></span>
		</button>
		<div class="kaiko-mm__hint" data-kaiko-mm-hint></div>
		<div class="kaiko-mm__error" data-kaiko-mm-error role="alert" hidden></div>
	</div>
</div>
