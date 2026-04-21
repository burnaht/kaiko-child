<?php
/**
 * Kaiko — My Account helpers.
 *
 * Phase 1: sidebar nav icons.
 * Phase 2 will add kaiko_order_line_tier_meta() for the single-order view.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return an inline SVG for a sidebar nav item.
 *
 * stroke="currentColor" fill="none" so the icon inherits the link colour
 * (stone-700 default → teal active → red on the danger logout link).
 *
 * @param string $key One of: dashboard, shop, orders, downloads,
 *                    edit-address, edit-account, customer-logout.
 * @return string Inline SVG markup (safe to echo — no user input).
 */
function kaiko_account_nav_icon( $key ) {
	$icons = array(
		'dashboard'       => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2h-4V12H9v10H5a2 2 0 01-2-2V9z"/>',
		'shop'            => '<path d="M3 6h18l-2 13H5L3 6zM8 6V4a4 4 0 018 0v2"/>',
		'orders'          => '<path d="M20 7H4a1 1 0 00-1 1v12a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 3l-4 4-4-4"/>',
		'downloads'       => '<path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>',
		'edit-address'    => '<path d="M12 2a7 7 0 017 7c0 5-7 13-7 13S5 14 5 9a7 7 0 017-7z"/><circle cx="12" cy="9" r="2.5"/>',
		'edit-account'    => '<circle cx="12" cy="8" r="4"/><path d="M4 22a8 8 0 0116 0"/>',
		'customer-logout' => '<path d="M16 17l5-5-5-5M21 12H9M9 22H5a2 2 0 01-2-2V4a2 2 0 012-2h4"/>',
	);

	$body = isset( $icons[ $key ] ) ? $icons[ $key ] : $icons['dashboard'];

	return sprintf(
		'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
		$body
	);
}
