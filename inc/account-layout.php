<?php
/**
 * Kaiko — My Account helpers.
 *
 * - kaiko_account_nav_icon()       sidebar nav SVGs
 * - kaiko_order_line_tier_meta()   tier data for a historic order line,
 *                                  thin adapter over kaiko_cart_line_tier_data()
 * - kaiko_handle_reorder()         admin-post handler: add every line of a
 *                                  past order back to the cart, variation-aware,
 *                                  with a customer-id assertion
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


/**
 * Return tier data for a single historic-order line.
 *
 * Thin adapter over kaiko_cart_line_tier_data() (inc/cart-layout.php). Uses
 * the line's per-unit total (pre-tax, post-discount) as $applied_unit so the
 * returned shape matches what the cart page + drawer consume, and the shared
 * template-parts/kaiko-cart-line-tier.php partial can render from it.
 *
 * @param WC_Order_Item_Product $order_item
 * @return array|null Same shape as kaiko_cart_line_tier_data().
 */
function kaiko_order_line_tier_meta( $order_item ) {
	if ( ! $order_item instanceof WC_Order_Item_Product ) {
		return null;
	}
	if ( ! function_exists( 'kaiko_cart_line_tier_data' ) ) {
		return null;
	}
	$product_id = (int) $order_item->get_product_id();
	$qty        = (int) $order_item->get_quantity();
	if ( ! $product_id || $qty <= 0 ) {
		return null;
	}
	// get_total() is the line net, post-discount, pre-tax. Dividing by qty
	// gives the per-unit price the customer actually paid — the same "applied
	// unit" shape kaiko_cart_line_tier_data() expects.
	$applied_unit = (float) $order_item->get_total() / $qty;
	if ( $applied_unit <= 0 ) {
		return null;
	}
	return kaiko_cart_line_tier_data( $product_id, $qty, $applied_unit );
}


/**
 * Reorder a past order — add every line back to the active cart.
 *
 * Hooked to `admin_post_kaiko_reorder`. Requires login (no nopriv variant).
 * The View-Order page renders a POST form that hits this endpoint.
 *
 * Safety:
 *  - Nonce check scoped to the order ID.
 *  - Ownership assertion: $order->get_customer_id() MUST equal the current
 *    user ID. Stops anyone with a valid session from dropping someone else's
 *    order into their cart.
 *  - Variation-aware: uses variation_id when present and forwards the
 *    variation attribute array so WC's cart add treats it as a variant pick.
 *  - Unavailable lines (missing product, out of stock, not purchasable) are
 *    skipped silently; the summary notice reports how many succeeded / skipped.
 */
function kaiko_handle_reorder() {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}

	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	$nonce    = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

	if ( ! $order_id || ! wp_verify_nonce( $nonce, 'kaiko_reorder_' . $order_id ) ) {
		wp_die( esc_html__( 'Security check failed.', 'kaiko-child' ), '', array( 'response' => 403 ) );
	}

	$order = wc_get_order( $order_id );
	if ( ! $order || (int) $order->get_customer_id() !== get_current_user_id() ) {
		wp_die( esc_html__( 'Order not found.', 'kaiko-child' ), '', array( 'response' => 404 ) );
	}

	$added   = 0;
	$skipped = 0;

	foreach ( $order->get_items() as $item ) {
		if ( ! $item instanceof WC_Order_Item_Product ) {
			continue;
		}

		$variation_id = (int) $item->get_variation_id();
		$product_id   = (int) $item->get_product_id();
		$add_id       = $variation_id ?: $product_id;
		$qty          = max( 1, (int) $item->get_quantity() );

		$product = wc_get_product( $add_id );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			$skipped++;
			continue;
		}

		// Pull variation attributes into the attribute-name shape WC expects
		// (keys prefixed with "attribute_"). Falls back to an empty array for
		// simple products, which WC handles fine.
		$variation_attrs = array();
		if ( $variation_id ) {
			foreach ( $item->get_meta_data() as $meta ) {
				$key = is_object( $meta ) ? (string) $meta->key : '';
				if ( '' === $key || 0 === strpos( $key, '_' ) ) {
					continue;
				}
				$attr_key = 'attribute_' . sanitize_title( $key );
				$variation_attrs[ $attr_key ] = wc_clean( $meta->value );
			}
		}

		$result = WC()->cart->add_to_cart(
			$product_id,
			$qty,
			$variation_id,
			$variation_attrs
		);

		if ( $result ) {
			$added++;
		} else {
			$skipped++;
			// add_to_cart can emit a wc_notice on failure; clear it so we can
			// emit one coherent summary at the end.
			wc_clear_notices();
		}
	}

	if ( $added > 0 ) {
		wc_add_notice(
			sprintf(
				/* translators: 1: lines added, 2: lines skipped */
				_n(
					'%1$d item added to your cart.',
					'%1$d items added to your cart.',
					$added,
					'kaiko-child'
				) . ( $skipped > 0 ? ' ' . sprintf( _n( '%2$d line was skipped (unavailable).', '%2$d lines were skipped (unavailable).', $skipped, 'kaiko-child' ), $added, $skipped ) : '' ),
				$added,
				$skipped
			),
			'success'
		);
		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	wc_add_notice(
		__( 'None of the items from that order are available right now. Nothing was added to your cart.', 'kaiko-child' ),
		'error'
	);
	wp_safe_redirect( $order->get_view_order_url() );
	exit;
}
add_action( 'admin_post_kaiko_reorder', 'kaiko_handle_reorder' );
