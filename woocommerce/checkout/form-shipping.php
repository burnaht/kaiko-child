<?php
/**
 * Kaiko — Checkout shipping fields (override)
 *
 * Identical to WC's default form-shipping.php except we drop the
 * .woocommerce-additional-fields block that renders the order-notes
 * textarea inside the shipping card. form-checkout.php renders those
 * notes as their own "Order notes" card instead, to match the
 * approved checkout preview.
 *
 * The notes fields are still part of `$checkout->get_checkout_fields('order')`
 * and flow through WC's normal validation + order-save pipeline.
 *
 * @package KaikoChild
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

	<div class="woocommerce-shipping-fields">
		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" />
				<span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
			</label>
		</h3>

		<div class="shipping_address">
			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$fields = $checkout->get_checkout_fields( 'shipping' );
				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>
		</div>
	</div>

<?php endif; ?>
