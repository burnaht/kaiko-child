<?php
/**
 * Kaiko — Checkout Layout
 *
 * Suppresses Woodmart's sidebar pollution on /checkout/, adds the
 * kaiko-checkout-page body class, auto-assigns template-checkout.php
 * to the WooCommerce Checkout page, and guarantees the template is
 * used even when the page meta was never saved.
 *
 * Mirror of inc/cart-layout.php's sidebar/template wiring, minus the
 * cart-specific tier helpers and AJAX handlers (those don't belong
 * on the checkout page).
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;


/* ============================================================
   1. BODY CLASS — scope CSS to the checkout page
   ============================================================ */

add_filter( 'body_class', 'kaiko_checkout_body_class' );

function kaiko_checkout_body_class( $classes ) {
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) ) {
		if ( ! in_array( 'kaiko-page', $classes, true ) )          $classes[] = 'kaiko-page';
		if ( ! in_array( 'kaiko-checkout-page', $classes, true ) ) $classes[] = 'kaiko-checkout-page';
	}
	return $classes;
}


/* ============================================================
   2. KILL WOODMART SIDEBAR ON CHECKOUT
   ============================================================ */

/**
 * Woodmart reads sidebar/layout preferences through woodmart_get_opt().
 * Force full-width + no sidebar on the checkout regardless of theme
 * settings. Don't apply on order-received — that page has its own shell.
 *
 * Also disables Woodmart's page-title + breadcrumbs module on checkout so
 * it can't render a plain-text "Checkout" bar above our styled hero.
 */
add_filter( 'woodmart_get_opt', 'kaiko_checkout_force_full_width', 10, 2 );

function kaiko_checkout_force_full_width( $value, $option_name = '' ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return $value;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		return $value;
	}
	$full_width_keys = array(
		'checkout-sidebar',
		'checkout_page_sidebar',
		'cart-sidebar',
		'shop-page-sidebar',
		'shop_sidebar',
		'single-product-page-sidebar',
	);
	$layout_keys = array(
		'checkout-page-layout',
		'shop-page-layout',
		'site_width',
	);
	// page-title-design === 'disable' makes woodmart_page_title() bail early.
	$page_title_disable_keys = array(
		'page-title-design',
		'page_title_design',
	);
	$breadcrumb_off_keys = array(
		'breadcrumbs',
	);
	if ( in_array( $option_name, $full_width_keys, true ) ) {
		return '0';
	}
	if ( in_array( $option_name, $layout_keys, true ) ) {
		return 'full-width';
	}
	if ( in_array( $option_name, $page_title_disable_keys, true ) ) {
		return 'disable';
	}
	if ( in_array( $option_name, $breadcrumb_off_keys, true ) ) {
		return false;
	}
	return $value;
}

/**
 * Per-page meta — some Woodmart builds store layout on the page itself.
 */
add_filter( 'get_post_metadata', 'kaiko_checkout_force_full_width_meta', 10, 4 );

function kaiko_checkout_force_full_width_meta( $value, $object_id, $meta_key, $single ) {
	static $checkout_page_id = null;
	if ( null === $checkout_page_id ) {
		$checkout_page_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'checkout' ) : 0;
	}
	if ( ! $checkout_page_id || (int) $object_id !== $checkout_page_id ) {
		return $value;
	}

	$force = array(
		'_woodmart_sidebar'          => 'no-sidebar',
		'_woodmart_sidebar_position' => 'no-sidebar',
		'_woodmart_new_layout'       => '1',
		'_woodmart_main_layout'      => 'full-width',
		'_wd_full_width'             => '1',
		// Kill Woodmart's page-title bar on the Checkout page specifically —
		// woodmart_page_title() reads this meta via woodmart_get_post_meta_value().
		'_woodmart_title_off'        => '1',
	);
	if ( array_key_exists( $meta_key, $force ) ) {
		return $single ? $force[ $meta_key ] : array( $force[ $meta_key ] );
	}
	return $value;
}

/**
 * Stop WC from rendering its default sidebar container on checkout.
 */
add_action( 'template_redirect', 'kaiko_checkout_strip_sidebars', 20 );

function kaiko_checkout_strip_sidebars() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	// Belt-and-braces: the woodmart_get_opt filter above already disables
	// the page-title module, but drop the hooks too so nothing renders a
	// plain-text title bar even if a plugin re-reads the options elsewhere.
	remove_action( 'woodmart_after_header', 'woodmart_page_title', 20 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}


/* ============================================================
   3. AUTO-ASSIGN template-checkout.php TO THE CHECKOUT PAGE
   ============================================================ */

/**
 * Runs once when the theme is (re)activated.
 */
add_action( 'after_switch_theme', 'kaiko_checkout_assign_template' );

/**
 * Runs once ever as a safety net for existing installs.
 */
add_action( 'admin_init', 'kaiko_checkout_assign_template_once' );

function kaiko_checkout_assign_template_once() {
	if ( get_option( 'kaiko_checkout_template_assigned' ) ) {
		return;
	}
	kaiko_checkout_assign_template();
	update_option( 'kaiko_checkout_template_assigned', 1, false );
}

function kaiko_checkout_assign_template() {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}
	$checkout_page_id = (int) wc_get_page_id( 'checkout' );
	if ( $checkout_page_id <= 0 ) {
		return;
	}
	$current = get_page_template_slug( $checkout_page_id );
	// Only overwrite defaults; don't stomp a deliberately-picked template.
	if ( '' === $current || 'default' === $current ) {
		update_post_meta( $checkout_page_id, '_wp_page_template', 'template-checkout.php' );
	}
}

/**
 * Safety net — force-load template-checkout.php on /checkout/ even when
 * the page meta is empty or another plugin clobbered it. Skip the
 * order-received endpoint; that page has its own dedicated template.
 */
add_filter( 'template_include', 'kaiko_checkout_template_include', 99 );

function kaiko_checkout_template_include( $template ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return $template;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		return $template;
	}
	// If the page meta already picks us, fine.
	if ( $template && false !== strpos( $template, 'template-checkout.php' ) ) {
		return $template;
	}
	$ours = locate_template( 'template-checkout.php' );
	return $ours ? $ours : $template;
}
