<?php
/**
 * Kaiko — Site Navigation partial.
 *
 * Single source of truth for the site-wide nav markup. Called by
 * header.php via get_template_part(). All user-state conditionals are
 * delegated to inc/nav-gates.php — do not re-check roles, login, or
 * pricing gates inline here.
 *
 * @package KaikoChild
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

$home_url     = esc_url( home_url( '/' ) );
$products_url = esc_url( home_url( '/products/' ) );
$about_url    = esc_url( home_url( '/about/' ) );
$contact_url  = esc_url( home_url( '/contact/' ) );
$shop_url     = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#';
$cart_url     = class_exists( 'WooCommerce' ) ? esc_url( wc_get_cart_url() ) : '#';
$cart_count   = class_exists( 'WooCommerce' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

$show_shop_link = kaiko_can_show_shop_link();
$show_cart      = kaiko_can_show_cart();
$pill_label     = kaiko_pill_label();
$pill_href      = esc_url( kaiko_pill_href() );
?>

<!-- KAIKO NAVIGATION -->
<nav class="kaiko-nav" id="kaiko-nav" role="navigation" aria-label="<?php esc_attr_e( 'Main navigation', 'kaiko-child' ); ?>">
    <a href="<?php echo $home_url; ?>" class="kaiko-nav-logo">KAIKO</a>

    <div class="kaiko-nav-links" id="kaiko-nav-links">
        <a href="<?php echo $products_url; ?>"<?php if ( is_page( 'products' ) ) echo ' class="active"'; ?>><?php esc_html_e( 'Products', 'kaiko-child' ); ?></a>

        <?php if ( $show_shop_link ) : ?>
            <a href="<?php echo $shop_url; ?>"<?php if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product() ) ) echo ' class="active"'; ?>><?php esc_html_e( 'Shop', 'kaiko-child' ); ?></a>
        <?php endif; ?>

        <a href="<?php echo $about_url; ?>"<?php if ( is_page( 'about' ) ) echo ' class="active"'; ?>><?php esc_html_e( 'About', 'kaiko-child' ); ?></a>
        <a href="<?php echo $contact_url; ?>"<?php if ( is_page( 'contact' ) ) echo ' class="active"'; ?>><?php esc_html_e( 'Contact', 'kaiko-child' ); ?></a>

        <?php
        // Header cart button + badge. kaiko_render_nav_cart() owns the
        // show/hide (via kaiko_can_show_cart) and the wrapper always sits
        // in the DOM so the woocommerce_add_to_cart_fragments replacer
        // has a stable target even when the button is hidden.
        if ( function_exists( 'kaiko_render_nav_cart' ) ) {
            echo kaiko_render_nav_cart();
        }
        ?>

        <a href="<?php echo $pill_href; ?>" class="kaiko-nav-cta"><?php echo esc_html( $pill_label ); ?></a>
    </div>

    <!-- Mobile hamburger -->
    <button class="kaiko-nav-hamburger" id="kaiko-nav-hamburger" aria-label="<?php esc_attr_e( 'Open menu', 'kaiko-child' ); ?>" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- MOBILE SLIDE-OUT MENU -->
<div class="kaiko-mobile-overlay" id="kaiko-mobile-overlay"></div>
<div class="kaiko-mobile-menu" id="kaiko-mobile-menu" aria-hidden="true">
    <div class="kaiko-mobile-menu__header">
        <span class="kaiko-mobile-menu__logo">KAIKO</span>
        <button class="kaiko-mobile-menu__close" id="kaiko-mobile-close" aria-label="<?php esc_attr_e( 'Close menu', 'kaiko-child' ); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="kaiko-mobile-menu__links">
        <a href="<?php echo $products_url; ?>"><?php esc_html_e( 'Products', 'kaiko-child' ); ?></a>
        <?php if ( $show_shop_link ) : ?>
            <a href="<?php echo $shop_url; ?>"><?php esc_html_e( 'Shop', 'kaiko-child' ); ?></a>
        <?php endif; ?>
        <a href="<?php echo $about_url; ?>"><?php esc_html_e( 'About', 'kaiko-child' ); ?></a>
        <a href="<?php echo $contact_url; ?>"><?php esc_html_e( 'Contact', 'kaiko-child' ); ?></a>
        <?php if ( $show_cart && $cart_count > 0 ) : ?>
            <a href="<?php echo $cart_url; ?>">
                <?php
                /* translators: %d: number of items in the cart */
                printf( esc_html__( 'Cart (%d)', 'kaiko-child' ), (int) $cart_count );
                ?>
            </a>
        <?php endif; ?>
    </div>
    <div class="kaiko-mobile-menu__footer">
        <a href="<?php echo $pill_href; ?>" class="btn-primary"><?php echo esc_html( $pill_label ); ?></a>
        <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn-secondary"><?php esc_html_e( 'Log Out', 'kaiko-child' ); ?></a>
        <?php else : ?>
            <a href="<?php echo $pill_href; ?>" class="btn-secondary"><?php esc_html_e( 'Apply for Trade', 'kaiko-child' ); ?></a>
        <?php endif; ?>
    </div>
</div>
