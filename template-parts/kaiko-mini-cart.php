<?php
/**
 * Kaiko — Mini-cart drawer partial
 *
 * Printed once per page by kaiko_mini_cart_print_drawer() on wp_footer.
 * Structure matches Scene 1 of kaiko-cart-concept.html.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
	return;
}
?>

<!-- Kaiko toast (fired by JS after add_to_cart) -->
<div class="kaiko-toast" id="kaiko-toast" role="status" aria-live="polite" aria-hidden="true">
	<span class="kaiko-toast__icon" aria-hidden="true">
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
	</span>
	<span class="kaiko-toast__msg"><?php esc_html_e( 'Added to cart', 'kaiko-child' ); ?></span>
</div>

<!-- Backdrop -->
<div class="drawer-backdrop" id="kaiko-drawer-backdrop" aria-hidden="true"></div>

<!-- Drawer -->
<aside class="kaiko-drawer" id="kaiko-drawer" aria-hidden="true" aria-label="<?php esc_attr_e( 'Your cart', 'kaiko-child' ); ?>" role="dialog">
	<div class="kaiko-drawer__head">
		<div class="kaiko-drawer__head__badge" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
		</div>
		<div class="kaiko-drawer__head__title">
			<?php esc_html_e( 'Your cart', 'kaiko-child' ); ?>
			<?php echo kaiko_render_drawer_head_meta(); ?>
		</div>
		<button type="button" class="kaiko-drawer__close" id="kaiko-drawer-close" aria-label="<?php esc_attr_e( 'Close cart', 'kaiko-child' ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
	</div>

	<?php echo kaiko_render_drawer_body(); ?>

	<div class="kaiko-drawer__foot">
		<?php echo kaiko_render_drawer_subtotal(); ?>
		<div class="kaiko-drawer__tax"><?php esc_html_e( 'ex. VAT · calculated at checkout', 'kaiko-child' ); ?></div>
		<div class="kaiko-drawer__ctas">
			<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn--primary">
				<?php esc_html_e( 'Checkout', 'kaiko-child' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
			</a>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn--ghost"><?php esc_html_e( 'View full cart', 'kaiko-child' ); ?></a>
		</div>
		<div class="kaiko-drawer__reassure">
			<span>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
				<?php esc_html_e( 'Secure checkout via Mollie', 'kaiko-child' ); ?>
			</span>
		</div>
	</div>
</aside>
