<?php
/**
 * Kaiko — Coupon form
 *
 * Override of WooCommerce cart/form-coupon.php. Our cart.php renders
 * its own inline promo input, so this override is intentionally empty
 * to stop WC's default form from appearing twice when the
 * `woocommerce_cart_coupon` action fires inside our promo wrap.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;
