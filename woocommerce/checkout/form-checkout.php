<?php
/**
 * Kaiko — Checkout form (override)
 *
 * Rewrites WC's default form-checkout.php into a 4-card left column
 * (coupon / billing / notes / payment) + sticky order-review right
 * column, to match the approved preview at
 * /Users/thomasmay/Desktop/Larry/kaiko-checkout-preview.html.
 *
 * Two companion overrides co-operate:
 *  - woocommerce/checkout/form-shipping.php  — shipping fields only,
 *    notes block removed (we render notes in our own card below).
 *  - woocommerce/checkout/review-order.php   — grouped-by-size lines,
 *    custom totals, bank-transfer-only payment handling.
 *
 * The default "Have a coupon?" toggle + form that WC injects on
 * `woocommerce_before_checkout_form` is unhooked in
 * inc/checkout-layout.php so we can render a single always-visible
 * Coupon card at the top of the left column instead.
 *
 * @package KaikoChild
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

$kaiko_coupons_enabled = function_exists( 'wc_coupons_enabled' ) && wc_coupons_enabled();
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout kaiko-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<div class="kaiko-checkout-columns">

		<div class="kaiko-checkout-fields">

			<?php if ( $kaiko_coupons_enabled ) : ?>
				<?php
				// Card 1 — Coupon. Not a nested <form>; the apply button is
				// type="button" and assets/js/kaiko-checkout.js fires the
				// standard wc-ajax=apply_coupon request, then triggers
				// update_checkout. Keeps the markup inside the main checkout
				// form (HTML5 disallows nested forms) without losing WC's
				// coupon validation flow.
				?>
				<div class="kaiko-co-card kaiko-co-card--coupon">
					<div class="kaiko-co-coupon__prompt">
						<strong><?php esc_html_e( 'Have a coupon code?', 'kaiko-child' ); ?></strong>
						<?php esc_html_e( 'Enter it to apply your discount.', 'kaiko-child' ); ?>
					</div>
					<div class="kaiko-co-coupon__group">
						<label for="kaiko_coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
						<input type="text" id="kaiko_coupon_code" class="kaiko-co-coupon__input" placeholder="<?php esc_attr_e( 'Enter code', 'kaiko-child' ); ?>" autocomplete="off" />
						<button type="button" class="kaiko-co-coupon__btn" data-kaiko-apply-coupon>
							<?php esc_html_e( 'Apply', 'kaiko-child' ); ?>
						</button>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div class="kaiko-co-card kaiko-co-card--billing">
					<div class="kaiko-co-card__head">
						<h3><?php esc_html_e( 'Billing details', 'kaiko-child' ); ?></h3>
						<span class="kaiko-co-step"><?php esc_html_e( 'Step 1 of 3', 'kaiko-child' ); ?></span>
					</div>
					<div id="customer_details">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<?php
				// Card 3 — Order notes.
				//
				// WC's default form-shipping.php renders the notes block
				// inside the shipping card. Our override at
				// woocommerce/checkout/form-shipping.php drops it so we can
				// render notes here as their own card. `get_checkout_fields('order')`
				// is still wired through WC's save/validate pipeline — we
				// just move the render location.
				if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) :
					$order_fields = $checkout->get_checkout_fields( 'order' );
					if ( ! empty( $order_fields ) ) : ?>
						<div class="kaiko-co-card kaiko-co-card--notes">
							<div class="kaiko-co-card__head">
								<h3><?php esc_html_e( 'Order notes', 'kaiko-child' ); ?></h3>
								<span class="kaiko-co-step"><?php esc_html_e( 'Step 2 of 3', 'kaiko-child' ); ?></span>
							</div>
							<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>
							<div class="woocommerce-additional-fields__field-wrapper">
								<?php foreach ( $order_fields as $key => $field ) : ?>
									<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
								<?php endforeach; ?>
							</div>
							<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
						</div>
					<?php endif;
				endif;
				?>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>

			<div class="kaiko-co-card kaiko-co-card--payment">
				<div class="kaiko-co-card__head">
					<h3><?php esc_html_e( 'Payment', 'kaiko-child' ); ?></h3>
					<span class="kaiko-co-step"><?php esc_html_e( 'Step 3 of 3', 'kaiko-child' ); ?></span>
				</div>
				<?php
				// Kaiko is bank-transfer only (BACS). The user-facing notice
				// below replaces the gateway list — the actual hidden radio
				// input for `payment_method=bacs` is emitted by our
				// review-order.php override so WC still has a selected
				// gateway on submit.
				echo wp_kses_post( kaiko_render_bank_transfer_notice() );
				?>
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

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
