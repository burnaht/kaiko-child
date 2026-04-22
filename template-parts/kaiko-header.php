<?php
/**
 * Kaiko — Shared Header Partial
 *
 * Renders the frosted-glass navigation bar used across ALL pages.
 * Replaces WoodMart's default header for visual consistency.
 *
 * Usage: get_template_part( 'template-parts/kaiko-header' );
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

$home_url     = esc_url( home_url( '/' ) );
$products_url = esc_url( home_url( '/products/' ) );
$about_url    = esc_url( home_url( '/about/' ) );
$contact_url  = esc_url( home_url( '/contact/' ) );
$shop_url     = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#';
$account_url  = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'myaccount' ) ) : '#';
$cart_url     = class_exists( 'WooCommerce' ) ? esc_url( wc_get_cart_url() ) : '#';
$cart_count   = class_exists( 'WooCommerce' ) ? WC()->cart->get_cart_contents_count() : 0;
$is_logged    = is_user_logged_in();
$can_buy      = function_exists( 'kaiko_user_can_see_prices' ) ? kaiko_user_can_see_prices() : $is_logged;
?>

<!-- KAIKO NAVIGATION -->
<nav class="kaiko-nav" id="kaiko-nav" role="navigation" aria-label="Main navigation">
    <a href="<?php echo $home_url; ?>" class="kaiko-nav-logo">KAIKO</a>

    <div class="kaiko-nav-links" id="kaiko-nav-links">
        <a href="<?php echo $products_url; ?>"<?php if ( is_page( 'products' ) ) echo ' class="active"'; ?>>Products</a>
        <?php if ( $can_buy ) : ?>
            <a href="<?php echo $shop_url; ?>"<?php if ( is_shop() || is_product_category() || is_product() ) echo ' class="active"'; ?>>Shop</a>
        <?php endif; ?>
        <a href="<?php echo $about_url; ?>"<?php if ( is_page( 'about' ) ) echo ' class="active"'; ?>>About</a>
        <a href="<?php echo $contact_url; ?>"<?php if ( is_page( 'contact' ) ) echo ' class="active"'; ?>>Contact</a>

        <?php
        if ( function_exists( 'kaiko_render_nav_cart' ) ) {
            echo kaiko_render_nav_cart();
        }
        ?>

        <?php if ( $can_buy ) : ?>
            <a href="<?php echo $account_url; ?>" class="kaiko-nav-cta">My Account</a>
        <?php else : ?>
            <a href="<?php echo $account_url; ?>" class="kaiko-nav-cta">Trade Login</a>
        <?php endif; ?>
    </div>

    <!-- Mobile hamburger -->
    <button class="kaiko-nav-hamburger" id="kaiko-nav-hamburger" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- MOBILE SLIDE-OUT MENU -->
<div class="kaiko-mobile-overlay" id="kaiko-mobile-overlay"></div>
<div class="kaiko-mobile-menu" id="kaiko-mobile-menu" aria-hidden="true">
    <div class="kaiko-mobile-menu__header">
        <span class="kaiko-mobile-menu__logo">KAIKO</span>
        <button class="kaiko-mobile-menu__close" id="kaiko-mobile-close" aria-label="Close menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="kaiko-mobile-menu__links">
        <a href="<?php echo $products_url; ?>">Products</a>
        <?php if ( $can_buy ) : ?>
            <a href="<?php echo $shop_url; ?>">Shop</a>
        <?php endif; ?>
        <a href="<?php echo $about_url; ?>">About</a>
        <a href="<?php echo $contact_url; ?>">Contact</a>
        <?php if ( $can_buy && $cart_count > 0 ) : ?>
            <a href="<?php echo $cart_url; ?>">Cart (<?php echo (int) $cart_count; ?>)</a>
        <?php endif; ?>
    </div>
    <div class="kaiko-mobile-menu__footer">
        <?php if ( $can_buy ) : ?>
            <a href="<?php echo $account_url; ?>" class="btn-primary">My Account</a>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn-secondary">Log Out</a>
        <?php else : ?>
            <a href="<?php echo $account_url; ?>" class="btn-primary">Trade Login</a>
            <a href="<?php echo $account_url; ?>" class="btn-secondary">Apply for Trade</a>
        <?php endif; ?>
    </div>
</div>
