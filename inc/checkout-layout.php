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
	// kaiko-page is added globally by kaiko_add_page_body_classes()
	// (inc/elementor-enqueue.php). Only the page-specific scoping class
	// is owned here.
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) ) {
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

	// functions.php still hooks a legacy .kaiko-page-hero into the checkout
	// form (add_action woocommerce_before_checkout_form / after_checkout_form).
	// template-checkout.php already renders its own .kaiko-checkout-hero, so
	// those legacy hooks only produce a duplicate hero inside the form. Drop
	// them here; leave the functions and the shared .kaiko-page-hero partial
	// alone — About / Products / Contact / login still use them.
	remove_action( 'woocommerce_before_checkout_form', 'kaiko_checkout_page_hero', 5 );
	remove_action( 'woocommerce_after_checkout_form', 'kaiko_checkout_page_close', 99 );
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


/* ============================================================
   4. KAIKO CHECKOUT REDESIGN — hooks + helpers
   ============================================================ */

/**
 * Remove WC's default "Have a coupon? Click here to enter your code"
 * toggle + hidden coupon <form> that it injects via
 * woocommerce_before_checkout_form. We render a single always-visible
 * Coupon card at the top of the left column instead (see
 * woocommerce/checkout/form-checkout.php).
 *
 * Safe to call at file-load: WC adds its coupon-form action during
 * plugin init, which runs BEFORE the theme's functions.php require_once
 * chain, so remove_action reliably matches.
 */
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

/**
 * Also unhook WC's default `woocommerce_checkout_payment()` from the
 * `woocommerce_checkout_order_review` action. WC ships two priorities
 * on that hook:
 *
 *   add_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review',   10 );
 *   add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
 *
 * The first one loads our review-order.php override and is what we
 * want. The second one loads WC's default checkout/payment.php — a
 * full gateway list + terms wrapper + a second `#place_order` button
 * — rendered inside #order_review ALONGSIDE our override. That was
 * the "payment rendering twice" bug on live: our own #payment sits
 * clip-rect hidden, but the default one that WC injects at priority
 * 20 has no .kaiko-co-payment-hidden wrapper, so nothing targets it
 * and it renders visibly.
 *
 * Our review-order.php already emits its own #payment with the BACS
 * radio in an accessibly-hidden wrapper (so WC's submit validator
 * still has payment_method posted) plus the single visible branded
 * place-order button, so the default render is redundant.
 */
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

/**
 * Place Order button label — set centrally so a future WC upgrade that
 * re-ships review-order.php doesn't silently revert us to "Place order".
 */
add_filter( 'woocommerce_order_button_text', 'kaiko_checkout_place_order_label' );

function kaiko_checkout_place_order_label( $label ) {
	return __( 'Place order &amp; receive bank details', 'kaiko-child' );
}

/**
 * Branded bank-transfer notice — rendered in the left-column Payment
 * card in place of a gateway list (Kaiko is BACS-only).
 *
 * Returned HTML is passed through wp_kses_post at the render site. The
 * SVG attribute surface is small, so wp_kses_post retains it.
 *
 * @return string
 */
function kaiko_render_bank_transfer_notice() {
	ob_start();
	?>
	<div class="kaiko-co-bank">
		<div class="kaiko-co-bank__icon" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M3 10l9-6 9 6"/>
				<path d="M5 10v9"/><path d="M19 10v9"/><path d="M9 10v9"/><path d="M15 10v9"/>
				<path d="M3 19h18"/>
			</svg>
		</div>
		<div class="kaiko-co-bank__body">
			<div class="kaiko-co-bank__title"><?php esc_html_e( 'Pay by bank transfer', 'kaiko-child' ); ?></div>
			<p><?php esc_html_e( "Once you place your order, we'll email you our bank details along with a reference number. Your order is held until payment arrives, then dispatched from our UK warehouse.", 'kaiko-child' ); ?></p>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Trust-strap copy, rendered under the Place Order button in
 * woocommerce/checkout/review-order.php. Kept as a filterable helper
 * so marketing can tweak the copy without editing the template.
 *
 * @return string
 */
function kaiko_checkout_trust_line() {
	return (string) apply_filters(
		'kaiko_checkout_trust_line',
		__( 'Secure SSL checkout — bank details sent after order', 'kaiko-child' )
	);
}


/* ============================================================
   5. CONDITIONAL ASSET ENQUEUE — only on /checkout/
   ============================================================ */

add_action( 'wp_enqueue_scripts', 'kaiko_checkout_enqueue_assets', 30 );

/**
 * Load the redesigned checkout CSS + coupon-apply JS only on the
 * checkout page (not on the order-received / thank-you endpoint, which
 * has its own stylesheet).
 */
function kaiko_checkout_enqueue_assets() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	$version = defined( 'KAIKO_VERSION' ) ? KAIKO_VERSION : null;
	$uri     = defined( 'KAIKO_URI' )     ? KAIKO_URI     : get_stylesheet_directory_uri();

	wp_enqueue_style(
		'kaiko-checkout',
		$uri . '/assets/css/kaiko-checkout.css',
		array( 'kaiko-woocommerce' ),
		$version
	);

	wp_enqueue_script(
		'kaiko-checkout',
		$uri . '/assets/js/kaiko-checkout.js',
		array( 'jquery', 'wc-checkout' ),
		$version,
		true
	);
}
