<?php
/**
 * Kaiko — Cart Page body (left column of template-cart.php)
 *
 * Renders the lines card + actions row + cross-sell upsell.
 * template-cart.php owns the hero + 2-column grid + summary sidebar.
 *
 * Preserves every WC core hook so bundles / subscriptions /
 * extensions keep working. Verified against
 *   plugins/woocommerce/templates/cart/cart.php
 *
 * @package KaikoChild
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<form class="woocommerce-cart-form kaiko-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" enctype="multipart/form-data">

	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<?php
	// Lines card — rendered via helper so the same markup is used for AJAX fragments.
	echo kaiko_render_cart_lines();
	?>

	<?php do_action( 'woocommerce_before_cart_contents' ); ?>
	<?php do_action( 'woocommerce_cart_contents' ); ?>
	<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>

	<div class="kaiko-cart-actions">
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="kaiko-cart-actions__continue">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
			<?php esc_html_e( 'Continue shopping', 'kaiko-child' ); ?>
		</a>

		<?php if ( wc_coupons_enabled() ) : ?>
			<div class="kaiko-cart-actions__promo coupon">
				<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'kaiko-child' ); ?></label>
				<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Promo code', 'kaiko-child' ); ?>">
				<button type="submit" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'kaiko-child' ); ?>"><?php esc_html_e( 'Apply', 'kaiko-child' ); ?></button>
				<?php do_action( 'woocommerce_cart_coupon' ); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php do_action( 'woocommerce_cart_actions' ); ?>

	<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<?php
// Cross-sell upsell (template-override at woocommerce/cart/cross-sells.php).
// Render only — template-cart.php supplies our own summary sidebar in the
// right column; we don't call woocommerce_cart_collaterals here because
// that would also print WC's default Cart Totals widget which would
// duplicate our sticky summary.
woocommerce_cross_sell_display( 3, 3 );
?>

<?php do_action( 'woocommerce_after_cart' ); ?>
