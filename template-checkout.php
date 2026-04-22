<?php
/**
 * Template Name: Kaiko Checkout
 * Template Post Type: page
 *
 * Standalone full-width page template for the WooCommerce checkout.
 * The theme owns this page end-to-end — no Woodmart sidebar, no
 * fallback widgets, no double page title. The [woocommerce_checkout]
 * shortcode lives in the page content; this template renders it inside
 * .kaiko-checkout-section so woocommerce/checkout/form-checkout.php's
 * .kaiko-checkout-columns grid (fields + sticky review) picks up the
 * existing shell styles.
 *
 * Auto-assigned to the Checkout page by inc/checkout-layout.php, with
 * a template_include safety net for installs where page meta is missing.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

    <section class="kaiko-checkout-hero">
        <div class="kaiko-checkout-hero__eyebrow"><?php esc_html_e( 'Checkout', 'kaiko-child' ); ?></div>
        <h1 class="kaiko-checkout-hero__title"><?php esc_html_e( 'Complete Your Order', 'kaiko-child' ); ?></h1>
        <p class="kaiko-checkout-hero__subtitle"><?php esc_html_e( 'Secure checkout powered by Kaiko.', 'kaiko-child' ); ?></p>
    </section>

    <section class="kaiko-checkout-section">
        <?php
        // Render the Checkout page's content — [woocommerce_checkout]
        // runs here and in turn uses our woocommerce/checkout/form-checkout.php
        // override for the .kaiko-checkout-columns markup.
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </section>

<?php
get_footer();
