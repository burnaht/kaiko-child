<?php
/**
 * Kaiko — 404 Not Found.
 *
 * Branded empty state with CTAs back to Shop and Home.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

get_header();

$kaiko_shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<div class="kaiko-page-wrapper">
    <section class="kaiko-page-hero">
        <div class="kaiko-page-hero__tag"><?php esc_html_e( '404', 'kaiko-child' ); ?></div>
        <h1 class="kaiko-page-hero__title"><?php esc_html_e( 'Page not found', 'kaiko-child' ); ?></h1>
        <p class="kaiko-page-hero__subtitle">
            <?php esc_html_e( "The page you were looking for has moved, or never existed. Here are good places to start.", 'kaiko-child' ); ?>
        </p>
    </section>

    <div class="kaiko-empty-state">
        <div class="kaiko-empty-state__ctas">
            <a href="<?php echo esc_url( $kaiko_shop_url ); ?>" class="btn btn--primary"><?php esc_html_e( 'Browse the shop', 'kaiko-child' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--ghost"><?php esc_html_e( 'Back to home', 'kaiko-child' ); ?></a>
        </div>
    </div>
</div>

<?php
get_footer();
