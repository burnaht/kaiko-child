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

$home_url    = esc_url( home_url( '/' ) );
$shop_url    = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#';
$account_url = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'myaccount' ) ) : '#';
$cart_url    = class_exists( 'WooCommerce' ) ? esc_url( wc_get_cart_url() ) : '#';
$cart_count  = class_exists( 'WooCommerce' ) ? WC()->cart->get_cart_contents_count() : 0;
$is_logged   = is_user_logged_in();
$can_buy     = function_exists( 'kaiko_user_can_see_prices' ) ? kaiko_user_can_see_prices() : false;
?>

<!-- KAIKO NAVIGATION -->
<nav class="kaiko-nav" id="kaiko-nav" role="navigation" aria-label="Main navigation">
    <a href="<?php echo $home_url; ?>" class="kaiko-nav-logo">KAIKO</a>

    <div class="kaiko-nav-links" id="kaiko-nav-links">
        <a href="<?php echo $shop_url; ?>"<?php if ( is_shop() || is_product_category() || is_product() ) echo ' class="active"'; ?>>Shop</a>
        <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"<?php if ( is_page( 'about' ) ) echo ' class="active"'; ?>>About</a>
        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"<?php if ( is_page( 'contact' ) ) echo ' class="active"'; ?>>Contact</a>

        <?php if ( $can_buy ) : ?>
            <a href="<?php echo $cart_url; ?>" class="kaiko-nav-cart" aria-label="Cart">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                <?php if ( $cart_count > 0 ) : ?>
                    <span class="kaiko-nav-cart-count"><?php echo esc_html( $cart_count ); ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <?php if ( $is_logged ) : ?>
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
        <a href="<?php echo $shop_url; ?>">Shop</a>
        <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
        <?php if ( $can_buy ) : ?>
            <a href="<?php echo $cart_url; ?>">Cart<?php if ( $cart_count > 0 ) echo ' (' . $cart_count . ')'; ?></a>
        <?php endif; ?>
    </div>
    <div class="kaiko-mobile-menu__footer">
        <?php if ( $is_logged ) : ?>
            <a href="<?php echo $account_url; ?>" class="btn-primary">My Account</a>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn-secondary">Log Out</a>
        <?php else : ?>
            <a href="<?php echo $account_url; ?>" class="btn-primary">Trade Login</a>
            <a href="<?php echo $account_url; ?>" class="btn-secondary">Apply for Trade</a>
        <?php endif; ?>
    </div>
</div>
