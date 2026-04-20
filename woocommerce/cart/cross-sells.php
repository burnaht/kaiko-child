<?php
/**
 * Kaiko — Cross-sell upsell strip
 *
 * Override of WooCommerce cart/cross-sells.php. 3-up cards matching
 * the concept. Hides entirely when no cross-sells are configured.
 *
 * Variables available (from woocommerce_cross_sell_display):
 *   $cross_sells (array of WC_Product) · $columns · $orderby · $limit · $order
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $cross_sells ) ) {
	return;
}
?>

<div class="kaiko-cart-upsell cross-sells">
	<div class="kaiko-cart-upsell__head"><?php esc_html_e( 'Often paired with your order', 'kaiko-child' ); ?></div>
	<div class="kaiko-cart-upsell__grid">
		<?php foreach ( $cross_sells as $cross_sell ) :
			$post_object = get_post( $cross_sell->get_id() );
			setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$thumb = $cross_sell->get_image( array( 96, 96 ) );
			$add_url = esc_url( add_query_arg( array( 'add-to-cart' => $cross_sell->get_id() ), wc_get_cart_url() ) );
			?>
			<a href="<?php echo esc_url( $cross_sell->get_permalink() ); ?>" class="kaiko-cart-upsell__item" data-product-id="<?php echo esc_attr( $cross_sell->get_id() ); ?>">
				<div class="kaiko-cart-upsell__item__icon"><?php echo $thumb; ?></div>
				<div class="kaiko-cart-upsell__item__body">
					<div class="kaiko-cart-upsell__item__title"><?php echo esc_html( $cross_sell->get_name() ); ?></div>
					<div class="kaiko-cart-upsell__item__price"><?php echo wp_kses_post( $cross_sell->get_price_html() ); ?></div>
				</div>
				<button type="button" class="kaiko-cart-upsell__item__add" data-add-url="<?php echo $add_url; ?>" data-product-id="<?php echo esc_attr( $cross_sell->get_id() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to cart', 'kaiko-child' ), $cross_sell->get_name() ) ); ?>">+</button>
			</a>
		<?php endforeach; wp_reset_postdata(); ?>
	</div>
</div>
