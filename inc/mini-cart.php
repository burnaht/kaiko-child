<?php
/**
 * Kaiko — Mini-cart drawer (Scene 1 of kaiko-cart-concept.html)
 *
 * Builds the slide-out cart: header badge + pulse, slide-in drawer,
 * toast, free-delivery progress, per-line qty stepper. Owns the
 * AJAX fragment filter so add-to-cart feels instant.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Free-delivery threshold (ex. VAT, GBP). Matches the concept.
 */
if ( ! defined( 'KAIKO_FREE_SHIPPING_THRESHOLD' ) ) {
	define( 'KAIKO_FREE_SHIPPING_THRESHOLD', 150.0 );
}


/* ============================================================
   1. ENQUEUE + LOCALIZE
   ============================================================ */

add_action( 'wp_enqueue_scripts', 'kaiko_mini_cart_enqueue', 25 );

function kaiko_mini_cart_enqueue() {
	if ( is_admin() || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	wp_enqueue_style(
		'kaiko-mini-cart',
		KAIKO_URI . '/assets/css/kaiko-mini-cart.css',
		array( 'kaiko-shell' ),
		KAIKO_VERSION
	);

	wp_enqueue_script(
		'kaiko-mini-cart',
		KAIKO_URI . '/assets/js/kaiko-mini-cart.js',
		array( 'jquery' ),
		KAIKO_VERSION,
		true
	);

	// Consume the session flag set by non-AJAX POST adds so we can open
	// the drawer once on the next page load, then clear it to prevent
	// re-firing on manual refresh.
	$just_added = 0;
	if ( function_exists( 'WC' ) && WC()->session ) {
		$just_added = (int) WC()->session->get( 'kaiko_just_added', 0 );
		if ( $just_added ) {
			WC()->session->set( 'kaiko_just_added', 0 );
		}
	}

	wp_localize_script( 'kaiko-mini-cart', 'kaikoMiniCart', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'kaiko_mini_cart' ),
		'cartUrl'     => wc_get_cart_url(),
		'checkoutUrl' => wc_get_checkout_url(),
		'justAdded'   => (bool) $just_added,
	) );
}


/* ============================================================
   2. FOOTER — print drawer once per page
   ============================================================ */

add_action( 'wp_footer', 'kaiko_mini_cart_print_drawer', 20 );

function kaiko_mini_cart_print_drawer() {
	if ( is_admin() || ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	// Skip on pages where it would fight a checkout modal etc.
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	get_template_part( 'template-parts/kaiko-mini-cart' );
}


/* ============================================================
   3. FRAGMENTS — update UI after every cart change
   ============================================================ */

add_filter( 'woocommerce_add_to_cart_fragments', 'kaiko_mini_cart_fragments', 20 );

function kaiko_mini_cart_fragments( $fragments ) {
	// Mini-cart drawer + header
	$fragments['div.kaiko-nav-cart-wrap']             = kaiko_render_nav_cart();
	$fragments['div.kaiko-drawer__body']              = kaiko_render_drawer_body();
	$fragments['div.kaiko-drawer__subtotal']          = kaiko_render_drawer_subtotal();
	$fragments['small.kaiko-drawer__head__meta']      = kaiko_render_drawer_head_meta();

	// Full cart page — only meaningful when those selectors are in the DOM,
	// WC's fragment applier no-ops when they aren't.
	if ( function_exists( 'kaiko_render_cart_lines' ) ) {
		$fragments['div.kaiko-cart-lines']                = kaiko_render_cart_lines();
		$fragments['div.kaiko-cart-summary__shipbar']     = kaiko_render_cart_shipbar();
		$fragments['.kaiko-cart-summary__subtotal__amt']  = kaiko_render_cart_summary_subtotal_amt();
		$fragments['.kaiko-cart-summary__savings__amt']   = kaiko_render_cart_summary_savings_amt();
		$fragments['.kaiko-cart-summary__total__amt']     = kaiko_render_cart_summary_total_amt();
	}
	return $fragments;
}

/**
 * Non-AJAX adds (POST form submit) don't hit the fragments filter —
 * flag the session so the next page load can fire the drawer chain.
 */
add_action( 'woocommerce_add_to_cart', 'kaiko_mini_cart_flag_non_ajax_add', 10, 0 );

function kaiko_mini_cart_flag_non_ajax_add() {
	if ( wp_doing_ajax() ) {
		return;
	}
	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->set( 'kaiko_just_added', 1 );
	}
}


/* ============================================================
   4. RENDER HELPERS
   ============================================================ */

/**
 * Header cart wrapper. Stays in the DOM even when the button is
 * hidden so the fragments filter always has a target to replace.
 *
 * Trade model: the cart is only useful to users who can actually
 * purchase. Gating is delegated to kaiko_can_show_cart() (see
 * inc/nav-gates.php) so every kaiko-buying-ability signal shares one
 * source of truth. The count badge is still conditional on cart_count > 0.
 */
function kaiko_render_nav_cart() {
	$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$show  = function_exists( 'kaiko_can_show_cart' ) && kaiko_can_show_cart();
	ob_start();
	?>
	<div class="kaiko-nav-cart-wrap">
		<?php if ( $show ) : ?>
			<button type="button" class="kaiko-nav-cart" data-kaiko-open-cart aria-label="<?php esc_attr_e( 'Open cart', 'kaiko-child' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
				<?php if ( $count > 0 ) : ?>
					<span class="kaiko-nav-cart-count"><?php echo esc_html( $count ); ?></span>
				<?php endif; ?>
			</button>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Drawer body — line items + progress bar.
 */
function kaiko_render_drawer_body() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '<div class="kaiko-drawer__body"></div>';
	}
	$cart = WC()->cart;
	ob_start();
	?>
	<div class="kaiko-drawer__body">
		<?php if ( $cart->is_empty() ) : ?>
			<div class="kaiko-drawer__empty">
				<p><?php esc_html_e( 'Your cart is empty.', 'kaiko-child' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn--primary"><?php esc_html_e( 'Browse products', 'kaiko-child' ); ?></a>
			</div>
		<?php else : ?>
			<?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
				$product = $cart_item['data'];
				if ( ! $product || ! $product->exists() || $cart_item['quantity'] <= 0 ) {
					continue;
				}
				kaiko_render_drawer_item( $cart_item_key, $cart_item );
			endforeach; ?>

			<?php echo kaiko_render_drawer_progress(); ?>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Single drawer line item (echoes).
 */
function kaiko_render_drawer_item( $cart_item_key, $cart_item ) {
	$product    = $cart_item['data'];
	$product_id = $cart_item['product_id'];
	$qty        = (int) $cart_item['quantity'];
	$title      = $product->get_name();
	$permalink  = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
	$thumb_id   = $product->get_image_id();
	$thumb_html = $thumb_id
		? wp_get_attachment_image( $thumb_id, array( 144, 144 ), false, array( 'alt' => $title ) )
		: wc_placeholder_img( array( 144, 144 ) );

	// The currently-applied per-unit price. For variations + tiered simple
	// products this is the WC-cart-applied unit price (already tier-discounted
	// when kaiko_apply_mix_and_match_tiers has run on the current request —
	// see inc/mix-and-match-pricing.php).
	$applied_unit = (float) $product->get_price();
	$line_total   = $applied_unit * $qty;

	// Tier data — single source of truth. Same call the cart page uses so
	// the chip + nudge + strikethrough render identically on both surfaces.
	// Mix-and-match: tier is picked from the per-(parent, size) total qty,
	// not this line's qty, so the chip mirrors what's actually applied.
	$size_value = function_exists( 'kaiko_cart_size_attr_value' ) ? kaiko_cart_size_attr_value( $cart_item ) : '';
	$lookup_qty = function_exists( 'kaiko_cart_group_total_qty' )
		? (int) kaiko_cart_group_total_qty( (int) $product_id, $size_value )
		: $qty;
	$tier = function_exists( 'kaiko_cart_line_tier_data' )
		? kaiko_cart_line_tier_data( $product_id, $qty, $applied_unit, $lookup_qty )
		: null;

	// Variation attrs (e.g. "Large · Moss Green")
	$attr_bits = array();
	if ( ! empty( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $name => $value ) {
			if ( '' === $value ) continue;
			$attr_bits[] = esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $value ) ) );
		}
	}
	?>
	<div class="kaiko-drawer__item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
		<div class="kaiko-drawer__thumb">
			<?php if ( $permalink ) : ?><a href="<?php echo esc_url( $permalink ); ?>"><?php echo $thumb_html; ?></a><?php else : ?><?php echo $thumb_html; ?><?php endif; ?>
		</div>
		<div class="kaiko-drawer__item__body">
			<div class="kaiko-drawer__title">
				<?php if ( $permalink ) : ?><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a><?php else : ?><?php echo esc_html( $title ); ?><?php endif; ?>
			</div>
			<?php if ( ! empty( $attr_bits ) ) : ?>
				<div class="kaiko-drawer__meta">
					<?php foreach ( $attr_bits as $bit ) : ?><span><?php echo $bit; ?></span><?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php kaiko_render_cart_line_tier( $tier, $cart_item_key, $qty, $applied_unit, true ); ?>
		</div>
		<div class="kaiko-drawer__right">
			<div>
				<div class="kaiko-drawer__line-total"><?php echo wp_kses_post( wc_price( $line_total ) ); ?></div>
				<div class="kaiko-drawer__unit"><?php echo kaiko_tier_unit_line_html( $tier, $qty, $applied_unit ); ?></div>
			</div>
			<div class="kaiko-drawer__qtymini" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
				<button type="button" data-action="dec" aria-label="<?php esc_attr_e( 'Decrease quantity', 'kaiko-child' ); ?>">−</button>
				<span class="q"><?php echo esc_html( $qty ); ?></span>
				<button type="button" data-action="inc" aria-label="<?php esc_attr_e( 'Increase quantity', 'kaiko-child' ); ?>">+</button>
			</div>
		</div>
		<button type="button" class="kaiko-drawer__item__remove" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Remove this item', 'kaiko-child' ); ?>">
			<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
	</div>
	<?php
}

/**
 * Free-delivery progress block.
 */
function kaiko_render_drawer_progress() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}
	$subtotal  = (float) WC()->cart->get_subtotal(); // ex. tax
	$threshold = (float) KAIKO_FREE_SHIPPING_THRESHOLD;
	$remaining = max( 0, $threshold - $subtotal );
	$pct       = $threshold > 0 ? min( 100, ( $subtotal / $threshold ) * 100 ) : 0;

	ob_start();
	?>
	<div class="kaiko-drawer__progress">
		<div class="kaiko-drawer__progress__row">
			<span>
				<?php if ( $remaining <= 0 ) : ?>
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
		<div class="kaiko-drawer__progress__bar">
			<div class="kaiko-drawer__progress__fill" style="width: <?php echo esc_attr( number_format( $pct, 2, '.', '' ) ); ?>%;"></div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Subtotal row in the drawer foot.
 */
function kaiko_render_drawer_subtotal() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '<div class="kaiko-drawer__subtotal"></div>';
	}
	$subtotal = WC()->cart->get_subtotal();
	ob_start();
	?>
	<div class="kaiko-drawer__subtotal">
		<span><?php esc_html_e( 'Subtotal', 'kaiko-child' ); ?></span>
		<span><?php echo wp_kses_post( wc_price( $subtotal ) ); ?></span>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * "N items · £X.XX" meta in the drawer head.
 */
function kaiko_render_drawer_head_meta() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '<small class="kaiko-drawer__head__meta"></small>';
	}
	$count    = (int) WC()->cart->get_cart_contents_count();
	$subtotal = WC()->cart->get_subtotal();
	ob_start();
	?>
	<small class="kaiko-drawer__head__meta">
		<?php
		/* translators: 1: item count, 2: subtotal */
		printf(
			esc_html( _n( '%1$s item · %2$s', '%1$s items · %2$s', $count, 'kaiko-child' ) ),
			esc_html( $count ),
			wp_kses_post( wc_price( $subtotal ) )
		);
		?>
	</small>
	<?php
	return ob_get_clean();
}


/* ============================================================
   5. AJAX — qty stepper in the drawer
   ============================================================ */

add_action( 'wp_ajax_kaiko_update_cart_qty', 'kaiko_ajax_update_cart_qty' );
add_action( 'wp_ajax_nopriv_kaiko_update_cart_qty', 'kaiko_ajax_update_cart_qty' );

function kaiko_ajax_update_cart_qty() {
	check_ajax_referer( 'kaiko_mini_cart', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => 'Cart unavailable' ), 500 );
	}

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
	$qty           = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : -1;

	if ( '' === $cart_item_key || $qty < 0 ) {
		wp_send_json_error( array( 'message' => 'Bad request' ), 400 );
	}

	$cart = WC()->cart;
	$item = $cart->get_cart_item( $cart_item_key );
	if ( ! $item ) {
		wp_send_json_error( array( 'message' => 'Item not found' ), 404 );
	}

	if ( 0 === $qty ) {
		$cart->remove_cart_item( $cart_item_key );
	} else {
		$cart->set_quantity( $cart_item_key, $qty, true );
	}
	$cart->calculate_totals();

	// Fragments filter expects these to apply globally too.
	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );

	wp_send_json_success( array(
		'fragments' => $fragments,
		'count'     => (int) $cart->get_cart_contents_count(),
	) );
}
