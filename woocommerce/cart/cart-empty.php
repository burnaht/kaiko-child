<?php
/**
 * Kaiko — Empty cart state
 *
 * Override of WooCommerce cart/cart-empty.php. Rendered from
 * template-cart.php when WC()->cart->is_empty().
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );
?>

<div class="kaiko-empty-card">
	<div class="kaiko-empty-card__icon" aria-hidden="true">
		<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
			<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
		</svg>
	</div>
	<h2><?php esc_html_e( 'Nothing in your cart yet', 'kaiko-child' ); ?></h2>
	<p><?php esc_html_e( 'Browse our live food, habitat, and lighting collections — trade pricing is applied automatically at checkout.', 'kaiko-child' ); ?></p>
	<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="kaiko-empty-card__cta">
		<?php esc_html_e( 'Browse products', 'kaiko-child' ); ?>
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
	</a>
</div>
