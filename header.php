<?php
/**
 * Kaiko — Site Header
 *
 * Single source of truth for the opening DOCTYPE → <body> → nav markup.
 * Called by WordPress core's get_header() dispatch, which means every
 * route — including fallthroughs (search, 404, default page.php, WC
 * overrides) — renders the Kaiko shell instead of Woodmart's.
 *
 * Pair with footer.php.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

// Context-specific body class. Centralised here so every route gets the
// same hook — previously each template hand-rolled its own body_class
// filter, which is why the `kaiko-page` class was missing on some routes
// and Woodmart chrome leaked through.
$kaiko_body_context = 'kaiko-page';
if ( is_front_page() || is_page_template( 'template-homepage.php' ) ) {
    $kaiko_body_context .= ' kaiko-homepage';
} elseif ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
    $kaiko_body_context .= ' kaiko-shop-page';
} elseif ( function_exists( 'is_product' ) && is_product() ) {
    $kaiko_body_context .= ' kaiko-product-page';
} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
    $kaiko_body_context .= ' kaiko-cart-page';
} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
    $kaiko_body_context .= ' kaiko-checkout-page';
} elseif ( function_exists( 'is_account_page' ) && is_account_page() ) {
    $kaiko_body_context .= ' kaiko-account-page kaiko-myaccount-page';
} elseif ( is_page_template( 'page-about.php' ) ) {
    $kaiko_body_context .= ' kaiko-about-page';
} elseif ( is_page_template( 'page-products.php' ) ) {
    $kaiko_body_context .= ' kaiko-products-page';
} elseif ( is_page_template( 'template-contact.php' ) ) {
    $kaiko_body_context .= ' kaiko-contact';
} elseif ( is_search() ) {
    $kaiko_body_context .= ' kaiko-search-page';
} elseif ( is_404() ) {
    $kaiko_body_context .= ' kaiko-404-page';
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class( $kaiko_body_context ); ?>>
<?php wp_body_open(); ?>
<?php do_action( 'woodmart_after_body_open' ); ?>

<?php
// Elementor Theme Builder short-circuit. If a user has built a header
// in Elementor Pro, let it render instead of our partial. Returns false
// (or the function doesn't exist) in every other case, so we fall
// through to the Kaiko partial.
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) :
    get_template_part( 'template-parts/kaiko-header' );
endif;
?>

<main class="kaiko-main" id="kaiko-main" role="main">
