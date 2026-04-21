<?php
/**
 * Kaiko — WC shortcode wrapper (neutralised).
 *
 * This file is loaded by WC's shortcode fallback path
 * (WC_Shortcode_My_Account::my_account() → wc_get_template('myaccount/my-account.php'))
 * whenever the [woocommerce_my_account] shortcode is invoked — e.g. if a page
 * renders via the_content() instead of our standalone template-myaccount.php.
 *
 * The previous contents fired do_action('woocommerce_account_navigation') +
 * do_action('woocommerce_account_content') which injected a stock
 * <nav class="woocommerce-MyAccount-navigation"> alongside our Kaiko sidebar.
 *
 * template-myaccount.php is the canonical entry point now, so this wrapper is
 * intentionally empty. Content + navigation rendering lives entirely in
 * template-parts/kaiko-my-account-approved.php (and its pending / logged-out
 * siblings), driven by template-myaccount.php.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;
