<?php
/**
 * Kaiko — My Account: Logged-Out State
 *
 * Renders the WooCommerce login + register forms via the
 * theme's overridden form-login.php (which contains the
 * trade intro banner and two-card auth grid).
 *
 * Going through wc_get_template ensures the WC-handled
 * notices, nonces, and login/register actions all fire.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'wc_print_notices' ) ) {
	wc_print_notices();
}

wc_get_template( 'myaccount/form-login.php' );
