<?php
/**
 * Kaiko — My Account: Approved State
 *
 * Shown to anyone who is logged in and not in the kaiko_pending state:
 * kaiko_trade, kaiko_approved, customer (legacy), administrator,
 * shop_manager.
 *
 * Renders the dashboard overview on the default endpoint and dispatches
 * WC's own action for sub-endpoints (orders, view-order, edit-address,
 * edit-account, downloads). Bypasses the [woocommerce_my_account]
 * shortcode to avoid double-rendering the WC nav and outer wrapper.
 *
 * Markup + class names match kaiko-my-account-concept.html.
 * Styling lives in assets/css/kaiko-my-account.css (single rule set,
 * no duplicates) — see functions.php for the enqueue.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$current_user  = wp_get_current_user();
$first_name    = $current_user->first_name ?: ( $current_user->display_name ?: $current_user->user_login );
$myaccount_url = wc_get_page_permalink( 'myaccount' );
$endpoint      = WC()->query->get_current_endpoint();
$is_dashboard  = ! $endpoint || 'dashboard' === $endpoint;
$has_downloads = function_exists( 'wc_customer_has_downloads' ) && wc_customer_has_downloads();

$user_roles = (array) $current_user->roles;
$is_trade   = in_array( 'kaiko_trade', $user_roles, true );
$is_admin   = user_can( $current_user, 'manage_options' ) || user_can( $current_user, 'manage_woocommerce' );

// Time-based greeting (server timezone).
$hour = (int) current_time( 'G' );
if ( $hour < 12 ) {
	$greeting_word = __( 'Good morning', 'kaiko-child' );
} elseif ( $hour < 18 ) {
	$greeting_word = __( 'Good afternoon', 'kaiko-child' );
} else {
	$greeting_word = __( 'Good evening', 'kaiko-child' );
}

// Sidebar items. Hide downloads if the customer has none.
$nav_items = array(
	'dashboard' => array(
		'label' => __( 'Dashboard', 'kaiko-child' ),
		'url'   => $myaccount_url,
	),
	'shop' => array(
		'label' => __( 'Shop', 'kaiko-child' ),
		'url'   => wc_get_page_permalink( 'shop' ),
	),
	'orders' => array(
		'label' => __( 'Orders', 'kaiko-child' ),
		'url'   => wc_get_account_endpoint_url( 'orders' ),
	),
);

if ( $has_downloads ) {
	$nav_items['downloads'] = array(
		'label' => __( 'Downloads', 'kaiko-child' ),
		'url'   => wc_get_account_endpoint_url( 'downloads' ),
	);
}

$nav_items += array(
	'edit-address' => array(
		'label' => __( 'Addresses', 'kaiko-child' ),
		'url'   => wc_get_account_endpoint_url( 'edit-address' ),
	),
	'edit-account' => array(
		'label' => __( 'Account details', 'kaiko-child' ),
		'url'   => wc_get_account_endpoint_url( 'edit-account' ),
	),
	'customer-logout' => array(
		'label' => __( 'Logout', 'kaiko-child' ),
		'url'   => wp_logout_url( $myaccount_url ),
	),
);

// Map view-order → orders for active highlighting.
$active_key = $is_dashboard ? 'dashboard' : $endpoint;
if ( 'view-order' === $endpoint ) {
	$active_key = 'orders';
}

// Quick-reorder: rank top products by total ordered quantity across the
// customer's own completed/processing orders. Brief §6 says defer trending
// fallback — hide the whole section when the customer has no history.
$reorder_products = array();
if ( $is_dashboard ) {
	$ranking = array();
	$source_orders = wc_get_orders( array(
		'customer' => $current_user->ID,
		'limit'    => 25,
		'status'   => array( 'completed', 'processing' ),
		'orderby'  => 'date',
		'order'    => 'DESC',
	) );
	foreach ( $source_orders as $src_order ) {
		foreach ( $src_order->get_items() as $line_item ) {
			$pid = $line_item->get_product_id();
			if ( ! $pid ) {
				continue;
			}
			$ranking[ $pid ] = ( $ranking[ $pid ] ?? 0 ) + $line_item->get_quantity();
		}
	}
	arsort( $ranking );
	foreach ( array_keys( $ranking ) as $pid ) {
		$product = wc_get_product( $pid );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			continue;
		}
		$reorder_products[] = $product;
		if ( count( $reorder_products ) >= 3 ) {
			break;
		}
	}
}
?>
<div class="kaiko-account-layout">

	<aside class="kaiko-account-sidebar">
		<div class="kaiko-account-sidebar__avatar">
			<?php
			// WP's get_avatar emits the class on its <img>. If Gravatar returns
			// nothing (shouldn't on a WP install but defensive) fall back to a
			// coloured circle with the first-letter initial.
			$avatar_html = get_avatar( $current_user->ID, 44, '', '', array( 'class' => 'kaiko-avatar-img' ) );
			if ( $avatar_html ) {
				echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP escapes.
			} else {
				printf(
					'<span class="kaiko-avatar-fallback" aria-hidden="true">%s</span>',
					esc_html( mb_strtoupper( mb_substr( $first_name, 0, 1 ) ) )
				);
			}
			?>
			<div class="kaiko-avatar-meta">
				<span class="kaiko-avatar-meta__name"><?php echo esc_html( $first_name ); ?></span>
				<span class="kaiko-avatar-meta__role">
					<?php
					if ( $is_admin ) {
						esc_html_e( 'Admin', 'kaiko-child' );
					} elseif ( $is_trade ) {
						esc_html_e( 'Trade Member', 'kaiko-child' );
					} else {
						esc_html_e( 'Member', 'kaiko-child' );
					}
					?>
				</span>
			</div>
		</div>

		<nav class="kaiko-account-sidebar__nav" aria-label="<?php esc_attr_e( 'Account navigation', 'kaiko-child' ); ?>">
			<ul>
				<?php foreach ( $nav_items as $key => $item ) :
					if ( 'customer-logout' === $key ) {
						continue; // Rendered below the divider.
					}
					$is_active = ( $active_key === $key );
					?>
					<li class="<?php echo $is_active ? 'active' : ''; ?>">
						<a href="<?php echo esc_url( $item['url'] ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
							<?php echo kaiko_account_nav_icon( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG ?>
							<span><?php echo esc_html( $item['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( isset( $nav_items['customer-logout'] ) ) : ?>
				<div class="kaiko-account-sidebar__divider" aria-hidden="true"></div>
				<ul>
					<li>
						<a href="<?php echo esc_url( $nav_items['customer-logout']['url'] ); ?>" class="is-danger">
							<?php echo kaiko_account_nav_icon( 'customer-logout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $nav_items['customer-logout']['label'] ); ?></span>
						</a>
					</li>
				</ul>
			<?php endif; ?>
		</nav>
	</aside>

	<div class="kaiko-account-content">
		<?php if ( function_exists( 'wc_print_notices' ) ) { wc_print_notices(); } ?>

		<?php if ( $is_dashboard ) :

			$order_count   = wc_get_customer_order_count( $current_user->ID );
			$total_spent   = wc_get_customer_total_spent( $current_user->ID );
			$registered_on = $current_user->user_registered
				? date_i18n( 'M Y', strtotime( $current_user->user_registered ) )
				: '';

			// "Member since" meta — dynamic months/years string.
			$months_since = 0;
			$since_meta   = '';
			if ( $current_user->user_registered ) {
				$diff_seconds = max( 0, time() - strtotime( $current_user->user_registered ) );
				$months_since = max( 1, (int) floor( $diff_seconds / ( 30 * DAY_IN_SECONDS ) ) );
				if ( $months_since >= 12 ) {
					$years      = (int) floor( $months_since / 12 );
					$since_meta = sprintf( _n( '%d year', '%d years', $years, 'kaiko-child' ), $years );
				} else {
					$since_meta = sprintf( _n( '%d month', '%d months', $months_since, 'kaiko-child' ), $months_since );
				}
			}

			$recent_orders = wc_get_orders( array(
				'customer' => $current_user->ID,
				'limit'    => 5,
				'orderby'  => 'date',
				'order'    => 'DESC',
			) );

			$show_trade_card = ( $is_trade || $is_admin );
			$stats_class     = $show_trade_card ? 'kaiko-stats-grid' : 'kaiko-stats-grid kaiko-stats-grid--3';

			// Formatted date for the lime-dot pill. Example: "Friday · 17 April 2026".
			$date_pill = date_i18n( 'l · j F Y' );
			?>

			<!-- Welcome card — single greeting, no duplicate page title -->
			<section class="kaiko-welcome-card">
				<div class="kaiko-welcome-card__left">
					<span class="kaiko-welcome-card__date"><?php echo esc_html( $date_pill ); ?></span>
					<h2 class="kaiko-welcome-card__title">
						<?php echo esc_html( sprintf( '%s, %s', $greeting_word, $first_name ) ); ?>
					</h2>
					<p class="kaiko-welcome-card__subline">
						<?php esc_html_e( 'Welcome back. From here you can manage orders, update your business details, and reorder your favourites.', 'kaiko-child' ); ?>
					</p>
				</div>
				<div class="kaiko-welcome-card__right">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="kaiko-btn kaiko-btn--ghost">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18l-2 13H5L3 6z"/></svg>
						<?php esc_html_e( 'Continue shopping', 'kaiko-child' ); ?>
					</a>
				</div>
			</section>

			<?php if ( $show_trade_card ) : ?>
				<!-- Trade status — lime accent, not Bootstrap green -->
				<div class="kaiko-trade-card">
					<div class="kaiko-trade-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="9"/>
							<path d="M9 12l2 2 4-4"/>
						</svg>
					</div>
					<div class="kaiko-trade-card__body">
						<h3 class="kaiko-trade-card__title"><?php esc_html_e( 'Trade Account Active', 'kaiko-child' ); ?></h3>
						<p class="kaiko-trade-card__desc"><?php esc_html_e( 'Wholesale pricing is enabled across all products. Free UK shipping on every trade order.', 'kaiko-child' ); ?></p>
					</div>
					<span class="kaiko-trade-card__badge"><?php esc_html_e( 'Verified', 'kaiko-child' ); ?></span>
				</div>
			<?php endif; ?>

			<!-- Stats -->
			<div class="<?php echo esc_attr( $stats_class ); ?>">
				<div class="kaiko-stat">
					<span class="kaiko-stat__label"><?php esc_html_e( 'Orders placed', 'kaiko-child' ); ?></span>
					<span class="kaiko-stat__value"><?php echo esc_html( number_format_i18n( $order_count ) ); ?></span>
					<span class="kaiko-stat__meta"><?php esc_html_e( 'Lifetime', 'kaiko-child' ); ?></span>
				</div>
				<div class="kaiko-stat">
					<span class="kaiko-stat__label"><?php esc_html_e( 'Lifetime spend', 'kaiko-child' ); ?></span>
					<span class="kaiko-stat__value kaiko-stat__value--money"><?php echo wp_kses_post( wc_price( $total_spent ) ); ?></span>
					<span class="kaiko-stat__meta"><?php esc_html_e( 'Excl. VAT', 'kaiko-child' ); ?></span>
				</div>
				<div class="kaiko-stat">
					<span class="kaiko-stat__label"><?php esc_html_e( 'Member since', 'kaiko-child' ); ?></span>
					<span class="kaiko-stat__value"><?php echo esc_html( $registered_on ?: '—' ); ?></span>
					<span class="kaiko-stat__meta"><?php echo esc_html( $since_meta ?: '—' ); ?></span>
				</div>
				<?php if ( $show_trade_card ) : ?>
					<div class="kaiko-stat">
						<span class="kaiko-stat__label"><?php esc_html_e( 'Trade tier', 'kaiko-child' ); ?></span>
						<span class="kaiko-stat__value kaiko-stat__value--teal">
							<?php echo $is_admin ? esc_html__( 'Admin', 'kaiko-child' ) : esc_html__( 'Trade', 'kaiko-child' ); ?>
						</span>
						<span class="kaiko-stat__meta kaiko-stat__meta--teal"><?php esc_html_e( 'Wholesale enabled', 'kaiko-child' ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<!-- Recent orders -->
			<div class="kaiko-section-head">
				<h3 class="kaiko-section-head__title"><?php esc_html_e( 'Recent orders', 'kaiko-child' ); ?></h3>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="kaiko-section-head__link">
					<?php esc_html_e( 'View all →', 'kaiko-child' ); ?>
				</a>
			</div>

			<?php if ( ! empty( $recent_orders ) ) : ?>
				<div class="kaiko-orders-card">
					<table class="kaiko-orders-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Order', 'kaiko-child' ); ?></th>
								<th><?php esc_html_e( 'Date', 'kaiko-child' ); ?></th>
								<th><?php esc_html_e( 'Status', 'kaiko-child' ); ?></th>
								<th><?php esc_html_e( 'Total', 'kaiko-child' ); ?></th>
								<th><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'kaiko-child' ); ?></span></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $recent_orders as $order ) :
								$status       = $order->get_status();
								$status_label = wc_get_order_status_name( $status );
								$status_class = sanitize_html_class( $status );
								?>
								<tr>
									<td><span class="order-num">#<?php echo esc_html( $order->get_order_number() ); ?></span></td>
									<td><?php echo esc_html( wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) ) ); ?></td>
									<td>
										<span class="kaiko-status-pill kaiko-status-pill--<?php echo esc_attr( $status_class ); ?>">
											<?php echo esc_html( $status_label ); ?>
										</span>
									</td>
									<td class="order-total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
									<td>
										<a class="action-btn" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
											<?php esc_html_e( 'View', 'kaiko-child' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<div class="kaiko-empty-state">
					<div class="kaiko-empty-state__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
							<path d="M20 7H4a1 1 0 00-1 1v12a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1zM16 3l-4 4-4-4"/>
						</svg>
					</div>
					<h4 class="kaiko-empty-state__title"><?php esc_html_e( 'No orders yet', 'kaiko-child' ); ?></h4>
					<p class="kaiko-empty-state__text"><?php esc_html_e( 'Browse the catalogue to place your first wholesale order.', 'kaiko-child' ); ?></p>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="kaiko-btn kaiko-btn--primary">
						<?php esc_html_e( 'Browse products →', 'kaiko-child' ); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $reorder_products ) ) : ?>
				<div class="kaiko-section-head">
					<h3 class="kaiko-section-head__title"><?php esc_html_e( 'Quick reorder', 'kaiko-child' ); ?></h3>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="kaiko-section-head__link">
						<?php esc_html_e( 'Full catalogue →', 'kaiko-child' ); ?>
					</a>
				</div>
				<div class="kaiko-reorder-grid">
					<?php foreach ( $reorder_products as $product ) :
						$image = $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) );
						?>
						<div class="kaiko-reorder-card">
							<a class="kaiko-reorder-thumb" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-hidden="true" tabindex="-1">
								<?php echo wp_kses_post( $image ); ?>
							</a>
							<a class="kaiko-reorder-title" href="<?php echo esc_url( $product->get_permalink() ); ?>">
								<?php echo esc_html( $product->get_name() ); ?>
							</a>
							<p class="kaiko-reorder-price">
								<strong><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
							</p>
							<a class="kaiko-reorder-cta"
								href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
								data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
								data-quantity="1"
								rel="nofollow"
							>
								<?php echo esc_html( $product->add_to_cart_text() ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Help strip -->
			<div class="kaiko-help-strip">
				<span>
					<?php esc_html_e( 'Need help?', 'kaiko-child' ); ?>
					<a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a>
				</span>
				<span class="kaiko-help-strip__divider" aria-hidden="true"></span>
				<a href="<?php echo esc_url( home_url( '/shipping/' ) ); ?>">
					<?php esc_html_e( 'Shipping &amp; returns policy →', 'kaiko-child' ); ?>
				</a>
			</div>

		<?php else :
			// Sub-endpoint: dispatch the same action [woocommerce_my_account] uses
			// internally so WC's own templates render — but inside our shell.
			// Phase 2 replaced these WC-native views with concept-matching
			// overrides under woocommerce/myaccount/.
			//
			// Pull the endpoint's query-var value the same way core WC does
			// in WC_Shortcode_My_Account::output() — $wp->query_vars lookup.
			// The previous call to WC()->query->get_endpoint_query_var_value()
			// was a non-existent method and caused the critical error.
			global $wp;
			$value = ( $endpoint && isset( $wp->query_vars[ $endpoint ] ) )
				? $wp->query_vars[ $endpoint ]
				: '';
			do_action( 'woocommerce_account_' . $endpoint . '_endpoint', $value );
			?>
		<?php endif; ?>
	</div>

</div>
