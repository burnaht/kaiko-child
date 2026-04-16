<?php
/**
 * Kaiko — Page Shell (Opening)
 *
 * Wraps WooCommerce pages in the Kaiko design shell.
 * Outputs <!DOCTYPE html>, <head>, body open, Kaiko nav.
 * Pair with kaiko-page-close.php.
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

// Determine body class additions
$kaiko_page_class = 'kaiko-page';
if ( is_shop() || is_product_category() || is_product_tag() ) {
    $kaiko_page_class .= ' kaiko-shop-page';
} elseif ( is_product() ) {
    $kaiko_page_class .= ' kaiko-product-page';
} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
    $kaiko_page_class .= ' kaiko-cart-page';
} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
    $kaiko_page_class .= ' kaiko-checkout-page';
} elseif ( function_exists( 'is_account_page' ) && is_account_page() ) {
    $kaiko_page_class .= ' kaiko-account-page';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class( $kaiko_page_class ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'template-parts/kaiko-header' ); ?>

<main class="kaiko-main" id="kaiko-main">
