<?php
/**
 * Kaiko — WC account-nav template (neutralised).
 *
 * WC's default template at woocommerce/templates/myaccount/navigation.php
 * renders <nav class="woocommerce-MyAccount-navigation"><ul>…</ul></nav>.
 * That was leaking into the top-left of every my-account view alongside
 * our Kaiko sidebar.
 *
 * Previous attempts fixed this at one layer:
 *   1. `remove_action( 'woocommerce_account_navigation',
 *       'woocommerce_account_navigation' )` (inc/account-layout.php)
 *   2. Neutralising woocommerce/myaccount/my-account.php
 *
 * Both still leave room for leakage — Woodmart's parent theme or a
 * plugin could call wc_get_template('myaccount/navigation.php') directly,
 * or fire do_action('woocommerce_account_navigation') before our init:20
 * remove_action runs. Overriding the template itself with an empty body
 * is the deterministic fix: every path that eventually renders the nav
 * ends up loading this file, which outputs nothing.
 *
 * Account navigation lives in template-parts/kaiko-my-account-approved.php
 * inside the .kaiko-account-sidebar__nav block. That's the canonical UI.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;
