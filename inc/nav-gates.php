<?php
/**
 * Kaiko — Navigation permission helpers.
 *
 * Single source of truth for every user-state conditional in the site
 * header. Every helper delegates to kaiko_user_can_see_prices() so the
 * nav gate can never drift apart from the pricing gate — one function
 * determines "is this user trade-approved?" and these four helpers
 * read from it.
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
 * Show the Shop link for trade-approved users only.
 *
 * Delegates to kaiko_user_can_see_prices() — the single source of
 * truth for trade approval. A user who can see prices on PDPs will
 * always see the Shop link in the header; a user who can't, won't.
 */
function kaiko_can_show_shop_link() {
    return kaiko_user_can_see_prices();
}

/**
 * Show the header cart icon for trade-approved users only.
 *
 * Same delegation as kaiko_can_show_shop_link(). Kept as a distinct
 * helper so a future product split (e.g. "cart visible while pending")
 * is a one-function change.
 */
function kaiko_can_show_cart() {
    return kaiko_user_can_see_prices();
}

/**
 * Header pill CTA label.
 *
 * - logged-out                          → "Trade Login"
 * - logged-in but not trade-approved    → "Pending Approval"
 * - trade-approved                      → "My Account"
 *
 * Uses kaiko_user_can_see_prices() rather than an inline role check so
 * the pill label cannot disagree with what the user can actually do.
 */
function kaiko_pill_label() {
    if ( ! is_user_logged_in() ) {
        return 'Trade Login';
    }
    return kaiko_user_can_see_prices() ? 'My Account' : 'Pending Approval';
}

/**
 * Header pill CTA href.
 *
 * All three user states point at /my-account/ — the my-account page
 * itself renders per-state (login form / pending dashboard / approved
 * dashboard).
 */
function kaiko_pill_href() {
    return home_url( '/my-account/' );
}
