<?php
/**
 * Kaiko — Shared cart-line tier chip + nudge partial
 *
 * Rendered by BOTH the cart page (woocommerce/cart/cart.php via
 * kaiko_render_cart_line_row) AND the mini-cart drawer
 * (inc/mini-cart.php::kaiko_render_drawer_item). Single source of truth
 * so the "Tier N applied — saved £X.XX" chip + "Add N more to unlock
 * Tier Y — £Z each" nudge render identically on both surfaces.
 *
 * Expected $args (passed through get_template_part's fourth argument):
 *   - tier          array|null   Output of kaiko_cart_line_tier_data()
 *   - cart_item_key string       The WC cart item key
 *   - qty           int          Line quantity
 *   - applied_unit  float        Per-unit price currently applied (after tier)
 *   - compact       bool         true in the drawer — renders
 *                                .kaiko-tier-nudge--compact so padding/font
 *                                shrink to fit the narrower column.
 *
 * Emits chip + nudge inline. No wrapping container — the caller decides
 * where each piece lands in its own layout.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$tier          = isset( $args['tier'] ) ? $args['tier'] : null;
$cart_item_key = isset( $args['cart_item_key'] ) ? (string) $args['cart_item_key'] : '';
$qty           = isset( $args['qty'] ) ? (int) $args['qty'] : 0;
$applied_unit  = isset( $args['applied_unit'] ) ? (float) $args['applied_unit'] : 0.0;
$compact       = ! empty( $args['compact'] );

if ( ! is_array( $tier ) ) {
	return;
}

// Chip: show when a real tier (index ≥ 2) is active AND we saved something.
if ( ! empty( $tier['active_index'] ) && (int) $tier['active_index'] > 1 && ! empty( $tier['saved_total'] ) && (float) $tier['saved_total'] > 0 ) : ?>
	<span class="kaiko-tier-applied">
		<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
		<?php
		/* translators: 1: tier index, 2: money saved */
		printf(
			esc_html__( 'Tier %1$d applied — saved %2$s', 'kaiko-child' ),
			(int) $tier['active_index'],
			wp_kses_post( wc_price( (float) $tier['saved_total'] ) )
		);
		?>
	</span>
<?php endif;

// Nudge: next tier is within 3 qty AND its unit price is better.
if ( ! empty( $tier['next'] ) && isset( $tier['next_unit'] ) && $tier['next_unit'] !== null && (float) $tier['next_unit'] < (float) $applied_unit ) :
	$delta = (int) $tier['next']['min_qty'] - (int) $qty;
	if ( $delta > 0 && $delta <= 3 ) :
		$nudge_class  = 'kaiko-tier-nudge';
		if ( $compact ) {
			$nudge_class .= ' kaiko-tier-nudge--compact';
		}
		?>
		<button
			type="button"
			class="<?php echo esc_attr( $nudge_class ); ?>"
			data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
			data-next-qty="<?php echo esc_attr( (int) $tier['next']['min_qty'] ); ?>"
		>
			<?php
			/* translators: 1: qty to add, 2: tier index, 3: per-unit price */
			printf(
				esc_html__( 'Add %1$d more to unlock Tier %2$d — %3$s each', 'kaiko-child' ),
				(int) $delta,
				(int) $tier['next_index'],
				wp_kses_post( wc_price( (float) $tier['next_unit'] ) )
			);
			?>
		</button>
		<?php
	endif;
endif;
