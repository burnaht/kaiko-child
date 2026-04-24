<?php
/**
 * Kaiko — PDP Mix-and-Match Accumulator
 *
 * Replaces the native WooCommerce variations form on variable products
 * that (a) have wholesale tiers configured and (b) expose a `pa_colour`
 * (or `pa_color`) attribute with at least two terms. Customers build a
 * list of (colour, size, qty) rows across multiple sizes and commit
 * everything on a single Add-to-Cart click — one line item per row.
 *
 * Server-side responsibilities here:
 *   - Gate: kaiko_pdp_should_use_accumulator()
 *   - Attribute slug resolvers (colour / size)
 *   - Variation + term payloads for the JS state machine
 *   - Conditional asset enqueue
 *   - AJAX batch add-to-cart endpoint
 *
 * Tier qualification is per-(parent, size) group, not parent-wide. The
 * matching cart-side change lives in inc/mix-and-match-pricing.php so
 * the PDP display and the cart line totals agree.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;


/* ============================================================
   1. GATE + ATTRIBUTE RESOLUTION
   ============================================================ */

/**
 * Should this product render the mix-and-match accumulator instead of
 * the native WC variations form?
 *
 * @param WC_Product|null $product
 * @return bool
 */
function kaiko_pdp_should_use_accumulator( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}
	if ( ! $product->is_type( 'variable' ) ) {
		return false;
	}

	$tiers = function_exists( 'kaiko_get_product_tiers' ) ? kaiko_get_product_tiers( $product->get_id() ) : array();
	if ( empty( $tiers ) ) {
		return false;
	}

	$stack_slug = kaiko_pdp_stack_attribute_slug( $product );
	if ( ! $stack_slug ) {
		return false;
	}

	$attrs = $product->get_variation_attributes();
	if ( empty( $attrs[ $stack_slug ] ) || count( $attrs[ $stack_slug ] ) < 2 ) {
		return false;
	}

	return (bool) apply_filters( 'kaiko_pdp_accumulator_enabled', true, $product );
}

/**
 * Attribute slug to stack (accumulate) on. Returns `pa_colour` or
 * `pa_color` depending on which the product uses; null if neither.
 *
 * @param WC_Product|null $product
 * @return string|null
 */
function kaiko_pdp_stack_attribute_slug( $product ) {
	$slug = null;
	if ( $product instanceof WC_Product ) {
		$attrs = $product->get_variation_attributes();
		foreach ( array( 'pa_colour', 'pa_color' ) as $candidate ) {
			if ( isset( $attrs[ $candidate ] ) ) {
				$slug = $candidate;
				break;
			}
		}
	}
	return apply_filters( 'kaiko_pdp_stack_attribute', $slug, $product );
}

/**
 * Attribute slug for the size tab axis — the first non-colour
 * variation attribute. Returns null if the product has no other axis
 * (colour-only).
 *
 * Products with >1 non-colour attribute currently ignore all but the
 * first. See the "Open calls for Tom" note in MIX_MATCH_CC_BRIEF.md.
 *
 * @param WC_Product|null $product
 * @return string|null
 */
function kaiko_pdp_size_attribute_slug( $product ) {
	$slug  = null;
	$stack = kaiko_pdp_stack_attribute_slug( $product );
	if ( $product instanceof WC_Product ) {
		$attrs = $product->get_variation_attributes();
		foreach ( $attrs as $name => $values ) {
			if ( $name === $stack ) {
				continue;
			}
			$slug = $name;
			break;
		}
	}
	return apply_filters( 'kaiko_pdp_size_attribute', $slug, $product );
}


/* ============================================================
   2. PAYLOADS FOR THE JS STATE MACHINE
   ============================================================ */

/**
 * Compact variation map for the front-end state machine.
 *
 *   [ variation_id => [
 *       'colour'    => slug,
 *       'size'      => slug|null,
 *       'price'     => float display_price,
 *       'in_stock'  => bool,
 *       'image_url' => url|string,
 *   ] ]
 *
 * @param WC_Product $product
 * @return array
 */
function kaiko_pdp_variation_map( $product ) {
	$map = array();
	if ( ! $product instanceof WC_Product_Variable ) {
		return $map;
	}

	$stack_slug = kaiko_pdp_stack_attribute_slug( $product );
	$size_slug  = kaiko_pdp_size_attribute_slug( $product );

	$available = $product->get_available_variations();
	foreach ( $available as $v ) {
		$variation_id = isset( $v['variation_id'] ) ? (int) $v['variation_id'] : 0;
		if ( $variation_id <= 0 ) {
			continue;
		}

		$attributes = isset( $v['attributes'] ) && is_array( $v['attributes'] ) ? $v['attributes'] : array();
		$colour_key = $stack_slug ? 'attribute_' . $stack_slug : '';
		$size_key   = $size_slug  ? 'attribute_' . $size_slug  : '';

		$image_url = '';
		if ( isset( $v['image'] ) && is_array( $v['image'] ) ) {
			if ( ! empty( $v['image']['full_src'] ) ) {
				$image_url = $v['image']['full_src'];
			} elseif ( ! empty( $v['image']['src'] ) ) {
				$image_url = $v['image']['src'];
			}
		}

		$map[ $variation_id ] = array(
			'colour'    => $colour_key && isset( $attributes[ $colour_key ] ) ? (string) $attributes[ $colour_key ] : '',
			'size'      => $size_key   && isset( $attributes[ $size_key ]   ) ? (string) $attributes[ $size_key ]   : null,
			'price'     => isset( $v['display_price'] ) ? (float) $v['display_price'] : 0.0,
			'in_stock'  => ! empty( $v['is_in_stock'] ),
			'image_url' => (string) $image_url,
		);
	}

	return $map;
}

/**
 * Ordered colour swatch terms for the product, with a hex fallback
 * chain: ACF `kaiko_colour_hex` on term → #CEC6B3.
 *
 * @param WC_Product $product
 * @return array of [ slug, name, hex ]
 */
function kaiko_pdp_colour_terms( $product ) {
	$out = array();
	if ( ! $product instanceof WC_Product ) {
		return $out;
	}
	$stack = kaiko_pdp_stack_attribute_slug( $product );
	if ( ! $stack ) {
		return $out;
	}

	$terms = wc_get_product_terms( $product->get_id(), $stack, array( 'fields' => 'all' ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return $out;
	}

	foreach ( $terms as $term ) {
		$hex = '';
		if ( function_exists( 'get_field' ) ) {
			$maybe = get_field( 'kaiko_colour_hex', $stack . '_' . $term->term_id );
			if ( $maybe && function_exists( 'sanitize_hex_color' ) ) {
				$hex = (string) sanitize_hex_color( $maybe );
			} elseif ( $maybe ) {
				$hex = (string) $maybe;
			}
		}
		if ( '' === $hex ) {
			$hex = '#CEC6B3';
		}
		$out[] = array(
			'slug' => (string) $term->slug,
			'name' => (string) $term->name,
			'hex'  => $hex,
		);
	}
	return $out;
}

/**
 * Ordered size chip terms for the product. Empty if the product has no
 * size-like attribute (colour-only).
 *
 * @param WC_Product $product
 * @return array of [ slug, name ]
 */
function kaiko_pdp_size_terms( $product ) {
	$out  = array();
	if ( ! $product instanceof WC_Product ) {
		return $out;
	}
	$size = kaiko_pdp_size_attribute_slug( $product );
	if ( ! $size ) {
		return $out;
	}

	$terms = wc_get_product_terms( $product->get_id(), $size, array( 'fields' => 'all' ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return $out;
	}
	foreach ( $terms as $term ) {
		$out[] = array(
			'slug' => (string) $term->slug,
			'name' => (string) $term->name,
		);
	}
	return $out;
}


/* ============================================================
   3. CONDITIONAL ASSET ENQUEUE
   ============================================================ */

add_action( 'wp_enqueue_scripts', 'kaiko_pdp_accumulator_enqueue', 30 );

function kaiko_pdp_accumulator_enqueue() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product || ! kaiko_pdp_should_use_accumulator( $product ) ) {
		return;
	}

	wp_enqueue_style(
		'kaiko-mix-match',
		KAIKO_URI . '/assets/css/kaiko-mix-match.css',
		array( 'kaiko-woocommerce' ),
		KAIKO_VERSION
	);

	wp_enqueue_script(
		'kaiko-mix-match',
		KAIKO_URI . '/assets/js/kaiko-mix-match.js',
		array( 'jquery', 'wc-add-to-cart-variation' ),
		KAIKO_VERSION,
		true
	);

	wp_localize_script( 'kaiko-mix-match', 'kaikoMixMatchData', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'kaiko_batch_atc' ),
	) );
}


/* ============================================================
   4. AJAX — BATCH ADD TO CART
   ============================================================ */

add_action( 'wp_ajax_kaiko_batch_add_to_cart',        'kaiko_ajax_batch_add_to_cart' );
add_action( 'wp_ajax_nopriv_kaiko_batch_add_to_cart', 'kaiko_ajax_batch_add_to_cart' );

/**
 * Accept a list of (variation_id, product_id, quantity) rows and add
 * each as its own cart line. Partial failures are non-fatal — we return
 * the partial count so the UI can show "added N of M" rather than
 * rolling back already-successful adds.
 */
function kaiko_ajax_batch_add_to_cart() {
	check_ajax_referer( 'kaiko_batch_atc', 'nonce' );

	if ( function_exists( 'kaiko_user_can_see_prices' ) && ! kaiko_user_can_see_prices() ) {
		wp_send_json( array(
			'ok'    => false,
			'added' => 0,
			'error' => __( 'Trade approval required to add to cart.', 'kaiko-child' ),
		) );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json( array(
			'ok'    => false,
			'added' => 0,
			'error' => __( 'Cart is not available.', 'kaiko-child' ),
		) );
	}

	$rows = isset( $_POST['rows'] ) && is_array( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array();
	if ( empty( $rows ) ) {
		wp_send_json( array(
			'ok'    => false,
			'added' => 0,
			'error' => __( 'No rows received.', 'kaiko-child' ),
		) );
	}

	$added       = 0;
	$attempts    = 0;
	$first_error = '';

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$variation_id = isset( $row['variation_id'] ) ? (int) $row['variation_id'] : 0;
		$product_id   = isset( $row['product_id'] )   ? (int) $row['product_id']   : 0;
		$quantity     = isset( $row['quantity'] )     ? (int) $row['quantity']     : 0;

		if ( $variation_id <= 0 || $product_id <= 0 || $quantity <= 0 ) {
			continue;
		}
		$attempts++;

		$variation_obj   = wc_get_product( $variation_id );
		$variation_attrs = array();
		if ( $variation_obj instanceof WC_Product_Variation ) {
			$variation_attrs = $variation_obj->get_variation_attributes();
		}

		$key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_attrs );
		if ( $key ) {
			$added++;
		} elseif ( '' === $first_error ) {
			$notices = function_exists( 'wc_get_notices' ) ? wc_get_notices( 'error' ) : array();
			if ( ! empty( $notices ) ) {
				$n = array_shift( $notices );
				$first_error = is_array( $n ) && isset( $n['notice'] ) ? (string) $n['notice'] : (string) $n;
			} else {
				$first_error = sprintf(
					/* translators: %d: variation id */
					__( 'Could not add variation %d.', 'kaiko-child' ),
					$variation_id
				);
			}
		}
	}

	// Clear any notices WC may have queued so they don't appear on the
	// next page load — the UI reports status inline.
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}

	wp_send_json( array(
		'ok'                  => ( $added > 0 && $added === $attempts ),
		'added'               => $added,
		'attempted'           => $attempts,
		'cart_hash'           => WC()->cart->get_cart_hash(),
		'cart_contents_count' => (int) WC()->cart->get_cart_contents_count(),
		'error'               => $first_error,
	) );
}
