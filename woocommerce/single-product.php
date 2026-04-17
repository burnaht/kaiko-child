<?php
/**
 * Kaiko — Single Product Page
 *
 * Override of WooCommerce single-product.php
 * Uses Kaiko shell (nav + footer) for visual consistency with homepage.
 * Adds: scroll reveal animations, sticky gallery, species/specs sections
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/kaiko-page-open' );
?>

<?php while ( have_posts() ) : the_post(); ?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'kaiko-single-product', $product ); ?>>

    <!-- Breadcrumbs -->
    <div class="kaiko-breadcrumb-bar kaiko-reveal">
        <?php woocommerce_breadcrumb(); ?>
    </div>

    <!-- Main Product Section -->
    <div class="kaiko-single-product__main">

        <!-- Gallery -->
        <div class="kaiko-single-product__gallery kaiko-reveal-left">
            <?php do_action( 'woocommerce_before_single_product_summary' ); ?>
        </div>

        <!-- Summary -->
        <div class="kaiko-single-product__summary kaiko-reveal-right">
            <?php
            /**
             * @hooked woocommerce_template_single_title - 5
             * @hooked woocommerce_template_single_rating - 10
             * @hooked woocommerce_template_single_price - 10
             * @hooked woocommerce_template_single_excerpt - 20
             * @hooked woocommerce_template_single_add_to_cart - 30
             * @hooked woocommerce_template_single_meta - 40
             * @hooked woocommerce_template_single_sharing - 50
             * @hooked kaiko_restricted_purchase_message - 30 (replaces add_to_cart when not approved)
             */
            do_action( 'woocommerce_single_product_summary' );
            ?>
        </div>

    </div>

    <!-- Extended product info (species, specs, care) -->
    <div class="kaiko-single-product__details kaiko-reveal">
        <?php
        /**
         * @hooked kaiko_display_species_compatibility - 15
         * @hooked kaiko_display_hardware_specs - 16
         * @hooked woocommerce_output_product_data_tabs - 10
         * @hooked woocommerce_upsell_display - 15
         * @hooked woocommerce_output_related_products - 20
         */
        do_action( 'woocommerce_after_single_product_summary' );
        ?>
    </div>

</div>

<?php endwhile; ?>

<?php
get_template_part( 'template-parts/kaiko-page-close' );
