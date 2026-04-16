<?php
/**
 * Kaiko — Product Card (Shop Loop)
 *
 * Override of WooCommerce content-product.php
 * Adds: kaiko-reveal animations, species tags, custom card layout
 *
 * @package KaikoChild
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$species    = get_field( 'compatible_species', $product->get_id() );
?>

<li <?php wc_product_class( 'kaiko-product-card kaiko-reveal', $product ); ?>>

    <?php if ( $product->is_on_sale() ) : ?>
        <span class="kaiko-product-card-badge">Sale</span>
    <?php endif; ?>

    <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="kaiko-product-card-link">

        <div class="kaiko-product-card-image">
            <?php echo $product->get_image( 'kaiko-product-card' ); ?>
        </div>

        <div class="kaiko-product-card-body">

            <h3 class="kaiko-product-card-title">
                <?php echo get_the_title(); ?>
            </h3>

            <?php if ( ! empty( $species ) && is_array( $species ) ) : ?>
                <div class="kaiko-product-card-species">
                    <?php
                    $names = wp_list_pluck( $species, 'species_name' );
                    $shown = array_slice( $names, 0, 3 );
                    echo esc_html( implode( ' · ', $shown ) );
                    if ( count( $names ) > 3 ) {
                        echo ' <span class="kaiko-product-card-species-more">+' . ( count( $names ) - 3 ) . '</span>';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="kaiko-product-card-footer">
                <div class="kaiko-product-card-price">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <?php if ( kaiko_user_can_see_prices() ) : ?>
                    <span class="kaiko-product-card-cart-icon" aria-label="<?php esc_attr_e( 'Add to cart', 'kaiko-child' ); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>

        </div>

    </a>

</li>
