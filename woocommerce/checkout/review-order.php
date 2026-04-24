<?php
/**
 * Kaiko — Checkout order-review (override)
 *
 * Rebuilds WC's default review-order table into:
 *
 *   - a grouped-by-size stack of product rows (image w/ teal qty badge,
 *     product name + tier chip, line total)
 *   - a totals block (Subtotal, Wholesale savings, Shipping, VAT…)
 *   - a grand total with "inc. VAT" subtext
 *   - the Place Order button + form nonces
 *   - a trust strap + single "UK BANK TRANSFER" payment chip
 *
 * Size grouping relies on PR #16's woocommerce_get_cart_contents filter
 * (kaiko_sort_cart_by_parent_and_size) so we can detect size changes by
 * walking the cart in order.
 *
 * Payment gateways: Kaiko is bank-transfer only, with the branded
 * notice rendered in the left column's Payment card. The gateway list
 * WC expects (so the payment_method radio input is posted) is still
 * emitted inside #payment but visually hidden via CSS — this keeps
 * WC's update_checkout AJAX + form validation working without showing
 * a single-option radio list to the user.
 *
 * @package KaikoChild
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$cart            = WC()->cart;
$current_size    = null;
$group_index     = 0;
$colour_terms    = array();
$size_terms_cache = array();

/**
 * Resolve a WC attribute value (a slug) to its human-readable term name,
 * falling back to a title-cased slug if the taxonomy/term is missing.
 */
if ( ! function_exists( 'kaiko_checkout_attr_term_name' ) ) {
	function kaiko_checkout_attr_term_name( $taxonomy, $slug ) {
		if ( '' === $slug ) {
			return '';
		}
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term->name;
		}
		return ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
	}
}
?>

<div class="kaiko-co-review-inner">

	<div class="kaiko-co-lines">
		<?php
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
			$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			if ( ! $product || ! $product->exists() || $cart_item['quantity'] <= 0 ) {
				continue;
			}
			if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}

			$product_id     = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			$parent_product = wc_get_product( $product_id );
			$qty            = (int) $cart_item['quantity'];
			$applied_unit   = (float) $product->get_price();
			$line_total     = $applied_unit * $qty;

			// Size heading — emit when the (parent, size) bucket changes.
			$size_value = function_exists( 'kaiko_cart_size_attr_value' ) ? kaiko_cart_size_attr_value( $cart_item ) : '';
			$size_slug  = function_exists( 'kaiko_pdp_size_attribute_slug' ) && $parent_product ? kaiko_pdp_size_attribute_slug( $parent_product ) : '';

			$size_key = $product_id . '|' . $size_value;
			if ( $size_key !== $current_size ) {
				$current_size = $size_key;
				$group_index++;

				if ( $size_value ) {
					if ( ! isset( $size_terms_cache[ $size_slug . '|' . $size_value ] ) ) {
						$size_terms_cache[ $size_slug . '|' . $size_value ] = $size_slug
							? kaiko_checkout_attr_term_name( $size_slug, $size_value )
							: ucwords( str_replace( array( '-', '_' ), ' ', $size_value ) );
					}
					$size_label = $size_terms_cache[ $size_slug . '|' . $size_value ];
					?>
					<div class="kaiko-co-size-heading"><?php echo esc_html( $size_label ); ?></div>
					<?php
				} elseif ( $group_index > 1 ) {
					// Different parent product with no size attribute —
					// still emit a thin divider so the grouping reads.
					?>
					<div class="kaiko-co-size-heading"><?php echo esc_html( $parent_product ? $parent_product->get_title() : '' ); ?></div>
					<?php
				}
			}

			// Colour attribute (pa_colour / pa_color).
			$colour_slug = function_exists( 'kaiko_pdp_stack_attribute_slug' ) && $parent_product ? kaiko_pdp_stack_attribute_slug( $parent_product ) : null;
			$colour_name = '';
			if ( $colour_slug && ! empty( $cart_item['variation'] ) && isset( $cart_item['variation'][ 'attribute_' . $colour_slug ] ) ) {
				$colour_slug_value = (string) $cart_item['variation'][ 'attribute_' . $colour_slug ];
				$cache_key         = $colour_slug . '|' . $colour_slug_value;
				if ( ! isset( $colour_terms[ $cache_key ] ) ) {
					$colour_terms[ $cache_key ] = kaiko_checkout_attr_term_name( $colour_slug, $colour_slug_value );
				}
				$colour_name = $colour_terms[ $cache_key ];
			}

			// Tier chip — "TIER N" when the cart line sits in a banded tier.
			$lookup_qty = function_exists( 'kaiko_cart_group_total_qty' )
				? (int) kaiko_cart_group_total_qty( (int) $product_id, $size_value )
				: $qty;
			$tier = function_exists( 'kaiko_cart_line_tier_data' )
				? kaiko_cart_line_tier_data( $product_id, $qty, $applied_unit, $lookup_qty )
				: null;
			$tier_label = '';
			if ( is_array( $tier ) && ! empty( $tier['active_index'] ) ) {
				/* translators: %d: tier index (1 = base, 2+ = wholesale bands) */
				$tier_label = sprintf( __( 'Tier %d', 'kaiko-child' ), (int) $tier['active_index'] );
			}

			$thumb = $product->get_image( array( 56, 56 ) );
			$title = $parent_product ? $parent_product->get_title() : $product->get_title();
			?>
			<div class="kaiko-co-line">
				<div class="kaiko-co-line__image">
					<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — WC_Product::get_image returns sanitised HTML. ?>
					<span class="kaiko-co-line__qty"><?php echo esc_html( $qty ); ?></span>
				</div>
				<div class="kaiko-co-line__meta">
					<div class="kaiko-co-line__name"><?php echo esc_html( $title ); ?></div>
					<div class="kaiko-co-line__variant">
						<?php if ( '' !== $colour_name ) : ?>
							<?php echo esc_html( $colour_name ); ?>
						<?php endif; ?>
						<?php if ( '' !== $tier_label ) : ?>
							<span class="kaiko-co-chip"><?php echo esc_html( $tier_label ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="kaiko-co-line__total"><?php echo wp_kses_post( wc_price( $line_total ) ); ?></div>
			</div>
			<?php
		endforeach;
		?>
	</div>

	<div class="kaiko-co-totals">
		<div class="row">
			<label><?php esc_html_e( 'Subtotal', 'kaiko-child' ); ?></label>
			<span class="value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php
		$savings = function_exists( 'kaiko_cart_total_savings' ) ? (float) kaiko_cart_total_savings() : 0.0;
		if ( $savings > 0 ) : ?>
			<div class="row savings">
				<label><?php esc_html_e( 'Wholesale savings', 'kaiko-child' ); ?></label>
				<span class="value">&minus;<?php echo wp_kses_post( wc_price( $savings ) ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>
			<div class="row">
				<label><?php esc_html_e( 'Shipping', 'kaiko-child' ); ?></label>
				<span class="value"><?php wc_cart_totals_shipping_html(); ?></span>
			</div>
		<?php elseif ( $cart->needs_shipping() ) : ?>
			<div class="row">
				<label><?php esc_html_e( 'Shipping', 'kaiko-child' ); ?></label>
				<span class="value"><?php esc_html_e( 'Calculated at checkout', 'kaiko-child' ); ?></span>
			</div>
		<?php endif; ?>

		<?php
		if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) :
			$tax_totals = $cart->get_tax_totals();
			if ( ! empty( $tax_totals ) ) :
				foreach ( $tax_totals as $tax ) : ?>
					<div class="row">
						<label><?php echo esc_html( $tax->label ); ?></label>
						<span class="value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach;
			endif;
		endif;
		?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="row">
				<label><?php echo esc_html( $fee->name ); ?></label>
				<span class="value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="row">
				<label><?php echo esc_html( wc_cart_totals_coupon_label( $coupon ) ); ?></label>
				<span class="value"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="kaiko-co-grand">
		<label><?php esc_html_e( 'Total', 'kaiko-child' ); ?></label>
		<div class="kaiko-co-grand__amount">
			<span class="value"><?php wc_cart_totals_order_total_html(); ?></span>
			<?php if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) : ?>
				<span class="inc-tax"><?php esc_html_e( 'inc. VAT', 'kaiko-child' ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<div id="payment" class="woocommerce-checkout-payment">
		<?php if ( $cart->needs_payment() ) : ?>
			<div class="kaiko-co-payment-hidden" aria-hidden="true">
				<ul class="wc_payment_methods payment_methods methods">
					<?php
					$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
					WC()->payment_gateways->set_current_gateway( $available_gateways );

					if ( ! empty( $available_gateways ) ) {
						foreach ( $available_gateways as $gateway ) {
							wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
						}
					} else {
						echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . wp_kses_post( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) ) . '</li>';
					}
					?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="form-row place-order">
			<noscript>
				<?php
				/* translators: $1 and $2 opening and closing emphasis tags respectively */
				printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ), '<em>', '</em>' );
				?>
				<br /><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
			</noscript>

			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

			<?php
			$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
			?>
			<button
				type="submit"
				class="button alt kaiko-co-place<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"
				name="woocommerce_checkout_place_order"
				id="place_order"
				value="<?php echo esc_attr( $order_button_text ); ?>"
				data-value="<?php echo esc_attr( $order_button_text ); ?>"
			>
				<?php echo wp_kses_post( $order_button_text ); ?>
			</button>

			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>
	</div>

	<div class="kaiko-co-trust">
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
			<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
		</svg>
		<?php echo esc_html( function_exists( 'kaiko_checkout_trust_line' ) ? kaiko_checkout_trust_line() : __( 'Secure SSL checkout', 'kaiko-child' ) ); ?>
	</div>

	<div class="kaiko-co-paychips">
		<span class="kaiko-co-paychip"><?php esc_html_e( 'UK Bank Transfer', 'kaiko-child' ); ?></span>
	</div>

</div>
