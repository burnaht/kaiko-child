<?php
/**
 * Template Name: Kaiko Cart
 * Template Post Type: page
 *
 * Standalone full-width page template for the WooCommerce cart.
 * The theme owns this page end-to-end: hero lives here (not in a
 * woocommerce_before_cart hook) so nothing double-renders.
 *
 * Auto-assigned to the Cart page by inc/cart-layout.php, with a
 * template_include safety net for installs where page meta is missing.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$kaiko_cart = class_exists( 'WooCommerce' ) && WC()->cart ? WC()->cart : null;

// Mirror WC_Shortcode_Cart::output() pre-render checks so stock /
// coupon / shipping notices surface before we paint the page.
if ( $kaiko_cart ) {
    $kaiko_cart->calculate_totals();
    if ( method_exists( $kaiko_cart, 'check_cart_items' ) ) {
        $kaiko_cart->check_cart_items();
    }
}

$kaiko_empty   = ! $kaiko_cart || $kaiko_cart->is_empty();
$kaiko_subline = $kaiko_empty
    ? __( 'Your cart is currently empty — start by picking from our trade catalogue below.', 'kaiko-child' )
    : __( 'Review your items before proceeding to checkout.', 'kaiko-child' );

get_header();
?>

    <section class="kaiko-cart-hero">
        <div class="kaiko-cart-hero__eyebrow"><?php esc_html_e( 'Your Cart', 'kaiko-child' ); ?></div>
        <h1 class="kaiko-cart-hero__title"><?php esc_html_e( 'Shopping Cart', 'kaiko-child' ); ?></h1>
        <p class="kaiko-cart-hero__subtitle"><?php echo esc_html( $kaiko_subline ); ?></p>
    </section>

    <?php if ( $kaiko_empty ) : ?>

        <div class="kaiko-cart-wrap kaiko-cart-wrap--single">
            <?php wc_get_template( 'cart/cart-empty.php' ); ?>
        </div>

    <?php else : ?>

        <div class="kaiko-cart-wrap">
            <div class="kaiko-cart-main">
                <?php
                // Notices (coupon applied, stock issues, etc.) render inside the main column.
                echo '<div class="kaiko-cart-notices">';
                wc_print_notices();
                echo '</div>';

                // The populated cart body — lines card + actions + upsell.
                wc_get_template( 'cart/cart.php' );
                ?>
            </div>

            <aside class="kaiko-cart-summary">
                <?php kaiko_render_cart_summary(); ?>
            </aside>
        </div>

    <?php endif; ?>

<?php
get_footer();
