<?php
/**
 * Kaiko — Product Card (Shop Loop)
 *
 * Override of WooCommerce content-product.php.
 * Matches the card in kaiko-previews/shop.html: pill-tagged image,
 * title, one-line intro, price (gated), and a "View Details" CTA.
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$kaiko_cat_terms = get_the_terms( $product->get_id(), 'product_cat' );
$kaiko_cat_name  = '';
if ( ! empty( $kaiko_cat_terms ) && ! is_wp_error( $kaiko_cat_terms ) ) {
	$kaiko_cat_name = $kaiko_cat_terms[0]->name;
}

$kaiko_intro = $product->get_short_description();
if ( '' === $kaiko_intro ) {
	$kaiko_intro = wp_trim_words( wp_strip_all_tags( $product->get_description() ), 16, '…' );
}
$kaiko_intro = wp_strip_all_tags( $kaiko_intro );
?>

<li <?php wc_product_class( 'kaiko-product-card kaiko-reveal', $product ); ?>>

	<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="kaiko-product-card-link">

		<div class="kaiko-product-card-image">
			<?php echo $product->get_image( 'kaiko-product-card' ); ?>

			<?php if ( $kaiko_cat_name ) : ?>
				<span class="kaiko-product-card-pill"><?php echo esc_html( $kaiko_cat_name ); ?></span>
			<?php endif; ?>

			<?php if ( $product->is_on_sale() ) : ?>
				<span class="kaiko-product-card-badge kaiko-product-card-badge--sale"><?php esc_html_e( 'Sale', 'kaiko-child' ); ?></span>
			<?php elseif ( ( time() - get_post_time( 'U', true, $product->get_id() ) ) < MONTH_IN_SECONDS ) : ?>
				<span class="kaiko-product-card-badge kaiko-product-card-badge--new"><?php esc_html_e( 'New', 'kaiko-child' ); ?></span>
			<?php endif; ?>
		</div>

		<div class="kaiko-product-card-body">

			<h3 class="kaiko-product-card-title"><?php echo esc_html( get_the_title() ); ?></h3>

			<?php if ( $kaiko_intro ) : ?>
				<p class="kaiko-product-card-intro"><?php echo esc_html( $kaiko_intro ); ?></p>
			<?php endif; ?>

			<div class="kaiko-product-card-footer">
				<div class="kaiko-product-card-price">
					<?php echo $product->get_price_html(); ?>
				</div>
				<span class="kaiko-product-card-cta" aria-hidden="true">
					<?php esc_html_e( 'View details', 'kaiko-child' ); ?>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
				</span>
			</div>

		</div>

	</a>

</li>
