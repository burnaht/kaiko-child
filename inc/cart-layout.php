<?php
/**
 * Kaiko — Cart Layout
 *
 * Suppresses Woodmart's sidebar pollution on /cart/, adds the
 * kaiko-cart-page body class, auto-assigns template-cart.php to
 * the WooCommerce Cart page, and guarantees the template is used
 * even when the page meta was never saved.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Free-delivery threshold (ex. VAT, GBP). Filterable.
 */
function kaiko_cart_free_delivery_threshold() {
	return (float) apply_filters( 'kaiko_free_delivery_threshold', 150.0 );
}

/**
 * Thin alias so future tier migrations are a one-file change
 * regardless of whether the storage moves off the functions.php
 * default-schedule implementation.
 */
if ( ! function_exists( 'kaiko_get_product_tier_rules' ) ) {
	function kaiko_get_product_tier_rules( $product_id ) {
		if ( function_exists( 'kaiko_get_product_tiers' ) ) {
			return kaiko_get_product_tiers( $product_id );
		}
		return array();
	}
}


/* ============================================================
   1. BODY CLASS — scope CSS to the cart page
   ============================================================ */

add_filter( 'body_class', 'kaiko_cart_body_class' );

function kaiko_cart_body_class( $classes ) {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		if ( ! in_array( 'kaiko-page', $classes, true ) )      $classes[] = 'kaiko-page';
		if ( ! in_array( 'kaiko-cart-page', $classes, true ) ) $classes[] = 'kaiko-cart-page';
	}
	return $classes;
}


/* ============================================================
   2. KILL WOODMART SIDEBAR ON CART
   ============================================================ */

/**
 * Woodmart reads sidebar/layout preferences through woodmart_get_opt().
 * Force full-width + no sidebar on the cart regardless of theme settings.
 */
add_filter( 'woodmart_get_opt', 'kaiko_cart_force_full_width', 10, 2 );

function kaiko_cart_force_full_width( $value, $option_name = '' ) {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return $value;
	}
	$full_width_keys = array(
		'cart-sidebar',
		'cart_page_sidebar',
		'shop-page-sidebar',
		'shop_sidebar',
		'single-product-page-sidebar',
		'checkout-sidebar',
	);
	$layout_keys = array(
		'cart-page-layout',
		'shop-page-layout',
		'site_width',
	);
	if ( in_array( $option_name, $full_width_keys, true ) ) {
		return '0';
	}
	if ( in_array( $option_name, $layout_keys, true ) ) {
		return 'full-width';
	}
	return $value;
}

/**
 * Per-page meta — some Woodmart builds store layout on the page itself.
 */
add_filter( 'get_post_metadata', 'kaiko_cart_force_full_width_meta', 10, 4 );

function kaiko_cart_force_full_width_meta( $value, $object_id, $meta_key, $single ) {
	static $cart_page_id = null;
	if ( null === $cart_page_id ) {
		$cart_page_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'cart' ) : 0;
	}
	if ( ! $cart_page_id || (int) $object_id !== $cart_page_id ) {
		return $value;
	}

	$force = array(
		'_woodmart_sidebar'          => 'no-sidebar',
		'_woodmart_sidebar_position' => 'no-sidebar',
		'_woodmart_new_layout'       => '1',
		'_woodmart_main_layout'      => 'full-width',
		'_wd_full_width'             => '1',
	);
	if ( array_key_exists( $meta_key, $force ) ) {
		return $single ? $force[ $meta_key ] : array( $force[ $meta_key ] );
	}
	return $value;
}

/**
 * Stop WC from rendering its default sidebar container on cart.
 */
add_action( 'template_redirect', 'kaiko_cart_strip_sidebars', 20 );

function kaiko_cart_strip_sidebars() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}


/* ============================================================
   3. AUTO-ASSIGN template-cart.php TO THE CART PAGE
   ============================================================ */

/**
 * Runs once when the theme is (re)activated.
 */
add_action( 'after_switch_theme', 'kaiko_cart_assign_template' );

/**
 * Runs once ever as a safety net for existing installs.
 */
add_action( 'admin_init', 'kaiko_cart_assign_template_once' );

function kaiko_cart_assign_template_once() {
	if ( get_option( 'kaiko_cart_template_assigned' ) ) {
		return;
	}
	kaiko_cart_assign_template();
	update_option( 'kaiko_cart_template_assigned', 1, false );
}

function kaiko_cart_assign_template() {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}
	$cart_page_id = (int) wc_get_page_id( 'cart' );
	if ( $cart_page_id <= 0 ) {
		return;
	}
	$current = get_page_template_slug( $cart_page_id );
	// Only overwrite defaults; don't stomp a deliberately-picked template.
	if ( '' === $current || 'default' === $current ) {
		update_post_meta( $cart_page_id, '_wp_page_template', 'template-cart.php' );
	}
}

/**
 * Safety net — force-load template-cart.php on /cart/ even when the
 * page meta is empty or another plugin clobbered it.
 */
add_filter( 'template_include', 'kaiko_cart_template_include', 99 );

function kaiko_cart_template_include( $template ) {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return $template;
	}
	// If the page meta already picks us, fine.
	if ( $template && false !== strpos( $template, 'template-cart.php' ) ) {
		return $template;
	}
	$ours = locate_template( 'template-cart.php' );
	return $ours ? $ours : $template;
}


/* ============================================================
   4. TIER DATA PER LINE
   ============================================================ */

/**
 * Return tier info for a cart line:
 *   [ base_unit, applied_unit, saved_total, active_tier_index (1-based),
 *     active_tier, next_tier, next_tier_index, next_tier_unit ]
 * or null when the product has no usable tier schedule.
 */
function kaiko_cart_line_tier_data( $product_id, $qty, $applied_unit ) {
	$tiers = kaiko_get_product_tier_rules( $product_id );
	if ( empty( $tiers ) ) {
		return null;
	}

	$active       = function_exists( 'kaiko_find_tier_for_qty' ) ? kaiko_find_tier_for_qty( $tiers, $qty ) : null;
	$active_index = 0;
	foreach ( $tiers as $i => $t ) {
		if ( $t === $active ) { $active_index = $i + 1; break; }
	}

	// Base unit = the qty=1 tier price. Default schedule always has
	// min_qty=1 as first row with 0% discount; ACF tiers may differ.
	$base_unit = (float) $tiers[0]['unit_price'];

	// For variable products with the default schedule, the tiers were
	// computed against the PRODUCT's base price, not this variation's.
	// Recompute the variation-specific base from the applied unit:
	$active_pct = $active ? (float) $active['discount_pct'] : 0.0;
	if ( $active && $active_pct > 0 ) {
		$denom = 1 - ( $active_pct / 100 );
		if ( $denom > 0 ) {
			$base_unit = round( (float) $applied_unit / $denom, 2 );
		}
	} elseif ( $active_pct === 0.0 ) {
		// qty is in tier 1 band — applied_unit IS the base.
		$base_unit = (float) $applied_unit;
	}

	$saved_per_unit = max( 0, $base_unit - (float) $applied_unit );
	$saved_total    = $saved_per_unit * (int) $qty;

	// Next tier — the smallest min_qty > current qty.
	$next = null;
	$next_index = 0;
	foreach ( $tiers as $i => $t ) {
		if ( $t['min_qty'] > $qty ) {
			if ( ! $next || $t['min_qty'] < $next['min_qty'] ) {
				$next       = $t;
				$next_index = $i + 1;
			}
		}
	}
	$next_unit = null;
	if ( $next ) {
		$next_pct = (float) $next['discount_pct'];
		$next_unit = $base_unit > 0 ? round( $base_unit * ( 1 - $next_pct / 100 ), 2 ) : null;
	}

	return array(
		'tiers'           => $tiers,
		'active'          => $active,
		'active_index'    => $active_index,
		'base_unit'       => $base_unit,
		'applied_unit'    => (float) $applied_unit,
		'saved_per_unit'  => $saved_per_unit,
		'saved_total'     => $saved_total,
		'next'            => $next,
		'next_index'      => $next_index,
		'next_unit'       => $next_unit,
	);
}


/**
 * Echo the shared cart-line tier partial (chip + nudge).
 *
 * Used by BOTH woocommerce/cart/cart.php (via kaiko_render_cart_line_row)
 * AND the mini-cart drawer (inc/mini-cart.php::kaiko_render_drawer_item)
 * so tier display is parity-by-construction.
 *
 * @param array|null $tier          Output of kaiko_cart_line_tier_data(), or null.
 * @param string     $cart_item_key WC cart item key.
 * @param int        $qty           Line quantity.
 * @param float      $applied_unit  Per-unit price currently applied.
 * @param bool       $compact       Pass true in the drawer for tighter padding.
 */
function kaiko_render_cart_line_tier( $tier, $cart_item_key, $qty, $applied_unit, $compact = false ) {
	if ( ! is_array( $tier ) ) {
		return;
	}
	get_template_part(
		'template-parts/kaiko-cart-line-tier',
		null,
		array(
			'tier'          => $tier,
			'cart_item_key' => (string) $cart_item_key,
			'qty'           => (int) $qty,
			'applied_unit'  => (float) $applied_unit,
			'compact'       => (bool) $compact,
		)
	);
}

/**
 * Return the "<s>£base</s> qty × £applied" unit line for a cart/drawer row.
 *
 * Drops the strikethrough when no saving is applied so the markup is
 * identical for tier-1 / simple products. Both cart.php and the drawer
 * route through this so the line-total sub-copy stays consistent.
 *
 * @param array|null $tier         Output of kaiko_cart_line_tier_data(), or null.
 * @param int        $qty          Line quantity.
 * @param float      $applied_unit Per-unit price currently applied.
 * @return string
 */
function kaiko_tier_unit_line_html( $tier, $qty, $applied_unit ) {
	$qty          = (int) $qty;
	$applied_unit = (float) $applied_unit;
	$strike       = '';
	if ( is_array( $tier ) && ! empty( $tier['saved_per_unit'] ) && (float) $tier['saved_per_unit'] > 0 ) {
		$strike = '<s>' . wp_kses_post( wc_price( (float) $tier['base_unit'] ) ) . '</s> ';
	}
	return $strike . esc_html( $qty ) . ' &times; ' . wp_kses_post( wc_price( $applied_unit ) );
}


/* ============================================================
   5. RENDER HELPERS — summary sidebar + lines
   ============================================================ */

/**
 * Sum of per-line tier savings across the current cart.
 */
function kaiko_cart_total_savings() {
	$total = 0.0;
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $total;
	}
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product = $cart_item['data'];
		if ( ! $product ) continue;
		$tier = kaiko_cart_line_tier_data( $cart_item['product_id'], (int) $cart_item['quantity'], (float) $product->get_price() );
		if ( $tier && $tier['saved_total'] > 0 ) {
			$total += $tier['saved_total'];
		}
	}
	return $total;
}

/**
 * Full summary sidebar — shipping bar + rows + total + checkout CTA + trust.
 */
function kaiko_render_cart_summary() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$cart      = WC()->cart;
	$count     = (int) $cart->get_cart_contents_count();
	$subtotal  = (float) $cart->get_subtotal();
	$savings   = (float) kaiko_cart_total_savings();
	$shipping  = (float) $cart->get_shipping_total();
	$tax       = (float) $cart->get_total_tax();
	$total     = (float) $cart->get_total( 'edit' );
	?>
	<h3><?php esc_html_e( 'Order summary', 'kaiko-child' ); ?></h3>

	<?php echo kaiko_render_cart_shipbar(); ?>

	<div class="kaiko-cart-summary__row">
		<span><?php
			/* translators: %d: item count */
			printf( esc_html( _n( 'Subtotal (%d item)', 'Subtotal (%d items)', $count, 'kaiko-child' ) ), $count );
		?></span>
		<strong class="kaiko-cart-summary__subtotal__amt"><?php echo wp_kses_post( wc_price( $subtotal ) ); ?></strong>
	</div>

	<div class="kaiko-cart-summary__row savings" <?php echo $savings > 0 ? '' : 'hidden'; ?>>
		<span><?php esc_html_e( 'Tier trade discount', 'kaiko-child' ); ?></span>
		<strong class="kaiko-cart-summary__savings__amt">−<?php echo wp_kses_post( wc_price( $savings ) ); ?></strong>
	</div>

	<?php if ( $cart->needs_shipping() ) : ?>
		<div class="kaiko-cart-summary__row">
			<span><?php esc_html_e( 'Delivery', 'kaiko-child' ); ?></span>
			<strong>
				<?php if ( $shipping > 0 ) : ?>
					<?php echo wp_kses_post( wc_price( $shipping ) ); ?> <small style="color:var(--kaiko-mid-gray);font-weight:500;">(<?php esc_html_e( 'est.', 'kaiko-child' ); ?>)</small>
				<?php else : ?>
					<?php esc_html_e( 'Calculated at checkout', 'kaiko-child' ); ?>
				<?php endif; ?>
			</strong>
		</div>
	<?php endif; ?>

	<?php if ( wc_tax_enabled() && $tax > 0 ) : ?>
		<div class="kaiko-cart-summary__row">
			<span><?php esc_html_e( 'VAT', 'kaiko-child' ); ?></span>
			<strong><?php echo wp_kses_post( wc_price( $tax ) ); ?></strong>
		</div>
	<?php endif; ?>

	<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
		<div class="kaiko-cart-summary__row">
			<span class="kaiko-coupon-chip">
				<?php echo esc_html( wc_cart_totals_coupon_label( $coupon, false ) ); ?>
				<button type="button" class="kaiko-coupon-remove" data-coupon="<?php echo esc_attr( $code ); ?>" aria-label="<?php esc_attr_e( 'Remove coupon', 'kaiko-child' ); ?>">
					<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
				</button>
			</span>
			<strong>−<?php echo wp_kses_post( wc_price( $cart->get_coupon_discount_amount( $code, $cart->display_cart_ex_tax ) ) ); ?></strong>
		</div>
	<?php endforeach; ?>

	<div class="kaiko-cart-summary__divider"></div>

	<div class="kaiko-cart-summary__total">
		<span class="kaiko-cart-summary__total__label">
			<?php esc_html_e( 'Total', 'kaiko-child' ); ?>
			<small><?php echo wc_tax_enabled() ? esc_html__( 'inc. VAT · GBP', 'kaiko-child' ) : esc_html__( 'GBP', 'kaiko-child' ); ?></small>
		</span>
		<span class="kaiko-cart-summary__total__amt"><?php echo wp_kses_post( wc_price( $total ) ); ?></span>
	</div>

	<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="kaiko-cart-summary__checkout">
		<?php esc_html_e( 'Proceed to checkout', 'kaiko-child' ); ?>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
	</a>

	<div class="kaiko-cart-summary__trust">
		<div>
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
			<?php esc_html_e( 'Secure checkout via Mollie', 'kaiko-child' ); ?>
		</div>
		<div>
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
			<?php esc_html_e( '30-day trade returns', 'kaiko-child' ); ?>
		</div>
		<div>
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			<?php esc_html_e( 'Tier pricing auto-applied', 'kaiko-child' ); ?>
		</div>
	</div>

	<div class="kaiko-cart-summary__pay" aria-hidden="true">
		<span>VISA</span><span>MC</span><span>KLARNA</span><span>PayBB</span>
	</div>
	<?php
}

/**
 * Free-delivery progress block (used by summary + fragments).
 */
function kaiko_render_cart_shipbar() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}
	$subtotal  = (float) WC()->cart->get_subtotal();
	$threshold = kaiko_cart_free_delivery_threshold();
	$remaining = max( 0, $threshold - $subtotal );
	$pct       = $threshold > 0 ? min( 100, ( $subtotal / $threshold ) * 100 ) : 0;
	$done      = $remaining <= 0;

	ob_start();
	?>
	<div class="kaiko-cart-summary__shipbar<?php echo $done ? ' is-complete' : ''; ?>">
		<div class="kaiko-cart-summary__shipbar__row">
			<span>
				<?php if ( $done ) : ?>
					<?php esc_html_e( 'Free UK delivery unlocked', 'kaiko-child' ); ?>
				<?php else : ?>
					<?php
					/* translators: %s: money remaining to free delivery */
					printf( esc_html__( '%s away from free UK delivery', 'kaiko-child' ), wp_kses_post( wc_price( $remaining ) ) );
					?>
				<?php endif; ?>
			</span>
			<small><?php echo wp_kses_post( wc_price( $threshold ) ); ?></small>
		</div>
		<div class="kaiko-cart-summary__shipbar__bar">
			<div class="kaiko-cart-summary__shipbar__fill" style="width: <?php echo esc_attr( number_format( $pct, 2, '.', '' ) ); ?>%;"></div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Full lines card (used for AJAX refresh fragment).
 */
function kaiko_render_cart_lines() {
	ob_start();
	if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
		?>
		<div class="kaiko-cart-lines">
			<div class="kaiko-cart-lines__heading" aria-hidden="true">
				<span></span>
				<span><?php esc_html_e( 'Product', 'kaiko-child' ); ?></span>
				<span><?php esc_html_e( 'Quantity', 'kaiko-child' ); ?></span>
				<span><?php esc_html_e( 'Line total', 'kaiko-child' ); ?></span>
				<span></span>
			</div>
			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$product = $cart_item['data'];
				if ( ! $product || ! $product->exists() || $cart_item['quantity'] <= 0 ) continue;
				if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;
				kaiko_render_cart_line_row( $cart_item_key, $cart_item );
			}
			?>
		</div>
		<?php
	} else {
		echo '<div class="kaiko-cart-lines kaiko-cart-lines--empty"></div>';
	}
	return ob_get_clean();
}

/**
 * Single cart line row (echoes).
 */
function kaiko_render_cart_line_row( $cart_item_key, $cart_item ) {
	$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	if ( ! $product ) return;

	$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
	$qty        = (int) $cart_item['quantity'];
	$title      = $product->get_name();
	$permalink  = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
	$sku        = $product->get_sku();

	$thumb = apply_filters(
		'woocommerce_cart_item_thumbnail',
		$product->get_image( 'woocommerce_thumbnail' ),
		$cart_item,
		$cart_item_key
	);

	$applied_unit = (float) $product->get_price();
	$line_total   = $applied_unit * $qty;

	// Variation attr chips
	$attr_bits = array();
	if ( ! empty( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $name => $value ) {
			if ( '' === $value ) continue;
			$attr_bits[] = ucwords( str_replace( array( '-', '_' ), ' ', $value ) );
		}
	}

	$tier = kaiko_cart_line_tier_data( $product_id, $qty, $applied_unit );

	$sold_individually = $product->is_sold_individually();
	$max_qty           = $product->get_max_purchase_quantity();
	?>
	<div class="kaiko-cart-row woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
		<div class="kaiko-cart-row__thumb">
			<?php if ( $permalink ) : ?><a href="<?php echo esc_url( $permalink ); ?>"><?php echo $thumb; ?></a><?php else : echo $thumb; endif; ?>
		</div>

		<div class="kaiko-cart-row__body">
			<div class="title">
				<?php
				$name_html = $permalink
					? sprintf( '<a href="%s">%s</a>', esc_url( $permalink ), esc_html( $title ) )
					: esc_html( $title );
				echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $name_html, $cart_item, $cart_item_key ) );
				?>
			</div>

			<?php if ( ! empty( $attr_bits ) ) : ?>
				<div class="attrs">
					<?php foreach ( $attr_bits as $bit ) : ?>
						<span class="kaiko-attr-chip"><?php echo esc_html( $bit ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
			<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>

			<?php kaiko_render_cart_line_tier( $tier, $cart_item_key, $qty, $applied_unit ); ?>

			<?php if ( $sku ) : ?>
				<div class="sku">SKU · <?php echo esc_html( $sku ); ?></div>
			<?php endif; ?>
		</div>

		<div class="kaiko-cart-row__qty">
			<div class="kaiko-qty-stepper" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
				<button type="button" data-action="dec" <?php echo $sold_individually ? 'disabled' : ''; ?> aria-label="<?php esc_attr_e( 'Decrease quantity', 'kaiko-child' ); ?>">−</button>
				<input type="text" inputmode="numeric" pattern="[0-9]*" value="<?php echo esc_attr( $qty ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'kaiko-child' ); ?>" <?php echo $sold_individually ? 'readonly' : ''; ?>>
				<button type="button" data-action="inc" <?php echo ( $sold_individually || ( $max_qty > 0 && $qty >= $max_qty ) ) ? 'disabled' : ''; ?> aria-label="<?php esc_attr_e( 'Increase quantity', 'kaiko-child' ); ?>">+</button>
			</div>
		</div>

		<div class="kaiko-cart-row__total">
			<?php echo wp_kses_post( wc_price( $line_total ) ); ?>
			<span class="kaiko-cart-row__total__unit">
				<?php echo kaiko_tier_unit_line_html( $tier, $qty, $applied_unit ); ?>
			</span>
		</div>

		<?php
		$remove_link = sprintf(
			'<a href="%s" class="kaiko-cart-row__remove" data-cart-item-key="%s" aria-label="%s"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></a>',
			esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
			esc_attr( $cart_item_key ),
			esc_attr__( 'Remove this item', 'kaiko-child' )
		);
		echo apply_filters( 'woocommerce_cart_item_remove_link', $remove_link, $cart_item_key );
		?>
	</div>
	<?php
}


/* ============================================================
   5b. RENDER HELPERS — AJAX fragment targets
   ============================================================ */

function kaiko_render_cart_summary_subtotal_amt() {
	$amt = function_exists( 'WC' ) && WC()->cart ? wc_price( WC()->cart->get_subtotal() ) : wc_price( 0 );
	return '<strong class="kaiko-cart-summary__subtotal__amt">' . wp_kses_post( $amt ) . '</strong>';
}

function kaiko_render_cart_summary_savings_amt() {
	$savings = kaiko_cart_total_savings();
	$html    = '<strong class="kaiko-cart-summary__savings__amt">−' . wp_kses_post( wc_price( $savings ) ) . '</strong>';
	// JS will toggle the parent row's [hidden] attr based on the numeric in data-amount.
	return $html;
}

function kaiko_render_cart_summary_total_amt() {
	$amt = function_exists( 'WC' ) && WC()->cart ? wc_price( WC()->cart->get_total( 'edit' ) ) : wc_price( 0 );
	return '<span class="kaiko-cart-summary__total__amt">' . wp_kses_post( $amt ) . '</span>';
}


/* ============================================================
   6. AJAX — remove + undo on the cart page
   ============================================================ */

add_action( 'wp_ajax_kaiko_remove_cart_item', 'kaiko_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_kaiko_remove_cart_item', 'kaiko_ajax_remove_cart_item' );

function kaiko_ajax_remove_cart_item() {
	check_ajax_referer( 'kaiko_mini_cart', 'nonce' );

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
	if ( '' === $cart_item_key || ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => 'Bad request' ), 400 );
	}
	$cart = WC()->cart;
	$item = $cart->get_cart_item( $cart_item_key );
	if ( ! $item ) {
		wp_send_json_error( array( 'message' => 'Item not found' ), 404 );
	}

	// Stash for undo (30 seconds). Can't serialize WC_Product, so snapshot the raw fields WC's add_to_cart() expects.
	$snapshot = array(
		'product_id'     => (int) ( $item['product_id'] ?? 0 ),
		'variation_id'   => (int) ( $item['variation_id'] ?? 0 ),
		'quantity'       => (int) ( $item['quantity'] ?? 1 ),
		'variation'      => isset( $item['variation'] ) && is_array( $item['variation'] ) ? $item['variation'] : array(),
		'cart_item_data' => array(),
	);
	$token = 'kaiko_undo_' . wp_generate_password( 12, false );
	set_transient( $token, $snapshot, 30 );

	$cart->remove_cart_item( $cart_item_key );
	$cart->calculate_totals();

	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );

	wp_send_json_success( array(
		'fragments' => $fragments,
		'is_empty'  => $cart->is_empty(),
		'count'     => (int) $cart->get_cart_contents_count(),
		'undo'      => $token,
	) );
}

add_action( 'wp_ajax_kaiko_undo_cart_remove', 'kaiko_ajax_undo_cart_remove' );
add_action( 'wp_ajax_nopriv_kaiko_undo_cart_remove', 'kaiko_ajax_undo_cart_remove' );

function kaiko_ajax_undo_cart_remove() {
	check_ajax_referer( 'kaiko_mini_cart', 'nonce' );

	$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
	if ( '' === $token || 0 !== strpos( $token, 'kaiko_undo_' ) ) {
		wp_send_json_error( array( 'message' => 'Bad token' ), 400 );
	}
	$snapshot = get_transient( $token );
	if ( ! is_array( $snapshot ) || empty( $snapshot['product_id'] ) ) {
		wp_send_json_error( array( 'message' => 'Undo expired' ), 410 );
	}
	delete_transient( $token );

	$ok = WC()->cart->add_to_cart(
		(int) $snapshot['product_id'],
		(int) $snapshot['quantity'],
		(int) $snapshot['variation_id'],
		$snapshot['variation'],
		$snapshot['cart_item_data']
	);
	if ( ! $ok ) {
		wp_send_json_error( array( 'message' => 'Restore failed' ), 500 );
	}
	WC()->cart->calculate_totals();

	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );
	wp_send_json_success( array(
		'fragments' => $fragments,
		'count'     => (int) WC()->cart->get_cart_contents_count(),
	) );
}

/**
 * Coupon apply / remove — AJAX versions so the cart page doesn't reload.
 */
add_action( 'wp_ajax_kaiko_apply_coupon', 'kaiko_ajax_apply_coupon' );
add_action( 'wp_ajax_nopriv_kaiko_apply_coupon', 'kaiko_ajax_apply_coupon' );

function kaiko_ajax_apply_coupon() {
	check_ajax_referer( 'kaiko_mini_cart', 'nonce' );
	$code = isset( $_POST['code'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';
	if ( '' === $code || ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a coupon code.', 'kaiko-child' ) ), 400 );
	}
	$applied = WC()->cart->apply_coupon( $code );
	if ( ! $applied ) {
		$notices = wc_get_notices( 'error' );
		wc_clear_notices();
		$msg = ! empty( $notices[0]['notice'] ) ? $notices[0]['notice'] : __( 'Coupon could not be applied.', 'kaiko-child' );
		wp_send_json_error( array( 'message' => wp_strip_all_tags( $msg ) ), 400 );
	}
	wc_clear_notices();
	WC()->cart->calculate_totals();
	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );
	wp_send_json_success( array( 'fragments' => $fragments ) );
}

add_action( 'wp_ajax_kaiko_remove_coupon', 'kaiko_ajax_remove_coupon' );
add_action( 'wp_ajax_nopriv_kaiko_remove_coupon', 'kaiko_ajax_remove_coupon' );

function kaiko_ajax_remove_coupon() {
	check_ajax_referer( 'kaiko_mini_cart', 'nonce' );
	$code = isset( $_POST['code'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';
	if ( '' === $code || ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => 'Bad request' ), 400 );
	}
	WC()->cart->remove_coupon( $code );
	WC()->cart->calculate_totals();
	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );
	wp_send_json_success( array( 'fragments' => $fragments ) );
}


/* ============================================================
   7. REMOVE the default WC remove-link filter handler (so only our × renders)
   ============================================================ */

/**
 * Replace WC's default remove link markup on the cart page with our
 * single SVG × button. Priority 20 runs after WC/Woodmart defaults.
 */
add_filter( 'woocommerce_cart_item_remove_link', 'kaiko_cart_single_remove_link', 20, 2 );

function kaiko_cart_single_remove_link( $existing, $cart_item_key ) {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return $existing;
	}
	return sprintf(
		'<a href="%s" class="kaiko-cart-row__remove" data-cart-item-key="%s" aria-label="%s"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></a>',
		esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
		esc_attr( $cart_item_key ),
		esc_attr__( 'Remove this item', 'kaiko-child' )
	);
}
