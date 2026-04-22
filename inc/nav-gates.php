<?php
/**
 * Kaiko — Navigation permission helpers.
 *
 * Single source of truth for every user-state conditional in the site
 * header. Wraps kaiko_user_can_see_prices() (the underlying pricing
 * gate) with named, self-documenting helpers so templates never
 * re-check the user role directly — if the trade-approval rule ever
 * changes, it changes in exactly one place.
 *
 * Three user states (logged_out / pending / approved) drive four
 * answers:
 *
 *   kaiko_can_show_shop_link()  bool    Shop link visibility
 *   kaiko_can_show_cart()       bool    Cart icon + badge visibility
 *   kaiko_pill_label()          string  Header CTA text
 *   kaiko_pill_href()           string  Header CTA destination
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Show the Shop link for approved users only.
 *
 * Approved === logged in AND not kaiko_pending — matches the pricing
 * gate so link visibility never drifts from what the user can actually
 * do on the shop page.
 */
function kaiko_can_show_shop_link() {
    return function_exists( 'kaiko_user_can_see_prices' ) && kaiko_user_can_see_prices();
}

/**
 * Show the header cart icon for approved users only.
 *
 * Same semantic as kaiko_can_show_shop_link() today, kept as a distinct
 * helper so a future product split (e.g. "cart visible while pending")
 * is a one-function change without hunting through templates.
 */
function kaiko_can_show_cart() {
    return function_exists( 'kaiko_user_can_see_prices' ) && kaiko_user_can_see_prices();
}

/**
 * Header pill CTA label.
 *
 * - logged-out  → "Trade Login"         (sign in or apply)
 * - pending     → "Application Status"  (application-status page)
 * - approved    → "My Account"          (dashboard)
 */
function kaiko_pill_label() {
    if ( ! is_user_logged_in() ) {
        return __( 'Trade Login', 'kaiko-child' );
    }
    $user = wp_get_current_user();
    if ( $user && in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
        return __( 'Application Status', 'kaiko-child' );
    }
    return __( 'My Account', 'kaiko-child' );
}

/**
 * Header pill CTA href.
 *
 * All three user states point at /my-account/ — the my-account page
 * itself renders per-state (login form / pending dashboard / approved
 * dashboard). Wrapping in a helper keeps the partial free of
 * WooCommerce-specific calls.
 */
function kaiko_pill_href() {
    if ( class_exists( 'WooCommerce' ) ) {
        return wc_get_page_permalink( 'myaccount' );
    }
    return home_url( '/my-account/' );
}
