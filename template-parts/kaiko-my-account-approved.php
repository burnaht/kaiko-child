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

// Quick-reorder: rank top products by total ordered quantity across recent orders.
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
		<div class="kaiko-avatar-block">
			<?php echo get_avatar( $current_user->ID, 88, '', '', array( 'class' => 'kaiko-avatar-img' ) ); ?>
			<div class="kaiko-avatar-meta">
				<span class="name"><?php echo esc_html( $first_name ); ?></span>
				<span class="role">
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

		<nav class="kaiko-account-nav" aria-label="<?php esc_attr_e( 'Account navigation', 'kaiko-child' ); ?>">
			<ul>
				<?php foreach ( $nav_items as $key => $item ) :
					$is_active = ( $active_key === $key );
					?>
					<li class="<?php echo $is_active ? 'active' : ''; ?>">
						<a
							href="<?php echo esc_url( $item['url'] ); ?>"
							<?php echo $is_active ? 'aria-current="page"' : ''; ?>
						><?php echo esc_html( $item['label'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
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

			$recent_orders = wc_get_orders( array(
				'customer' => $current_user->ID,
				'limit'    => 5,
				'orderby'  => 'date',
				'order'    => 'DESC',
			) );

			$show_trade_card = ( $is_trade || $is_admin );
			$stats_class     = $show_trade_card ? 'kaiko-stats-grid' : 'kaiko-stats-grid kaiko-stats-grid--3';
			?>

			<header class="kaiko-welcome-banner">
				<span class="kaiko-eyebrow">
					<?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?>
				</span>
				<h2><?php echo esc_html( sprintf( '%s, %s', $greeting_word, $first_name ) ); ?></h2>
				<p class="kaiko-greeting">
					<?php esc_html_e( 'Welcome back. From here you can manage orders, update your business details, and reorder your favourites.', 'kaiko-child' ); ?>
				</p>
			</header>

			<?php if ( $show_trade_card ) : ?>
				<div class="kaiko-account-notice success">
					<h3><?php esc_html_e( 'Trade Account Active', 'kaiko-child' ); ?></h3>
					<p><?php esc_html_e( 'Wholesale pricing is enabled across all products. Free UK shipping on every trade order.', 'kaiko-child' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="<?php echo esc_attr( $stats_class ); ?>">
				<div class="kaiko-stat-card">
					<dl>
						<dt><?php esc_html_e( 'Orders placed', 'kaiko-child' ); ?></dt>
						<dd><?php echo esc_html( number_format_i18n( $order_count ) ); ?></dd>
					</dl>
				</div>
				<div class="kaiko-stat-card">
					<dl>
						<dt><?php esc_html_e( 'Lifetime spend', 'kaiko-child' ); ?></dt>
						<dd><?php echo wp_kses_post( wc_price( $total_spent ) ); ?></dd>
					</dl>
				</div>
				<div class="kaiko-stat-card">
					<dl>
						<dt><?php esc_html_e( 'Member since', 'kaiko-child' ); ?></dt>
						<dd class="is-text"><?php echo esc_html( $registered_on ?: '—' ); ?></dd>
					</dl>
				</div>
				<?php if ( $show_trade_card ) : ?>
					<div class="kaiko-stat-card">
						<dl>
							<dt><?php esc_html_e( 'Trade tier', 'kaiko-child' ); ?></dt>
							<dd class="is-text"><?php echo $is_admin ? esc_html__( 'Admin', 'kaiko-child' ) : esc_html__( 'Trade', 'kaiko-child' ); ?></dd>
							<p class="kaiko-stat-meta"><?php esc_html_e( 'Wholesale enabled', 'kaiko-child' ); ?></p>
						</dl>
					</div>
				<?php endif; ?>
			</div>

			<h3 class="kaiko-section-heading"><?php esc_html_e( 'Recent Orders', 'kaiko-child' ); ?></h3>

			<?php if ( ! empty( $recent_orders ) ) : ?>
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
								<td>#<?php echo esc_html( $order->get_order_number() ); ?></td>
								<td><?php echo esc_html( wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) ) ); ?></td>
								<td><span class="status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
								<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
								<td>
									<a class="button-sm" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
										<?php esc_html_e( 'View', 'kaiko-child' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="kaiko-empty-state">
					<?php
					printf(
						/* translators: %s: shop URL */
						wp_kses(
							__( 'No orders yet. <a href="%s">Browse the catalogue</a> to place your first order.', 'kaiko-child' ),
							array( 'a' => array( 'href' => array() ) )
						),
						esc_url( wc_get_page_permalink( 'shop' ) )
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $reorder_products ) ) : ?>
				<h3 class="kaiko-section-heading"><?php esc_html_e( 'Quick reorder', 'kaiko-child' ); ?></h3>
				<div class="kaiko-quick-reorder">
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
							<p class="kaiko-reorder-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
							<a
								class="kaiko-reorder-cta"
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

			<div class="kaiko-help-strip">
				<p class="kaiko-help-text">
					<?php
					printf(
						/* translators: %s: support email link */
						esc_html__( 'Need help? %s', 'kaiko-child' ),
						'<a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a>'
					);
					?>
				</p>
				<p class="kaiko-help-text">
					<a href="<?php echo esc_url( home_url( '/shipping/' ) ); ?>">
						<?php esc_html_e( 'Shipping &amp; returns policy →', 'kaiko-child' ); ?>
					</a>
				</p>
			</div>

		<?php else :
			// Sub-endpoint: dispatch the same action [woocommerce_my_account] uses
			// internally so WC's own templates render — but inside our shell.
			$value = WC()->query->get_endpoint_query_var_value( $endpoint );
			do_action( 'woocommerce_account_' . $endpoint . '_endpoint', $value );
			?>
		<?php endif; ?>
	</div>

</div>
