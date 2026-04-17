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

$current_user   = wp_get_current_user();
$display_name   = $current_user->display_name ?: ( $current_user->first_name ?: $current_user->user_login );
$myaccount_url  = wc_get_page_permalink( 'myaccount' );
$endpoint       = WC()->query->get_current_endpoint();
$is_dashboard   = ! $endpoint || 'dashboard' === $endpoint;
$has_downloads  = function_exists( 'wc_customer_has_downloads' ) && wc_customer_has_downloads();

// Build sidebar items. Hide downloads if the customer has none.
$nav_items = array(
	'dashboard' => array(
		'label' => __( 'Dashboard', 'kaiko-child' ),
		'url'   => $myaccount_url,
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

// Map view-order back to the orders item for active highlighting.
$active_key = $is_dashboard ? 'dashboard' : $endpoint;
if ( 'view-order' === $endpoint ) {
	$active_key = 'orders';
}
?>
<div class="kaiko-account-layout">

	<nav class="kaiko-account-nav" aria-label="<?php esc_attr_e( 'Account navigation', 'kaiko-child' ); ?>">
		<ul>
			<?php foreach ( $nav_items as $key => $item ) : ?>
				<li class="<?php echo $active_key === $key ? 'active' : ''; ?>">
					<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="kaiko-account-content">
		<?php if ( function_exists( 'wc_print_notices' ) ) { wc_print_notices(); } ?>

		<?php if ( $is_dashboard ) : ?>

			<?php
			$order_count   = wc_get_customer_order_count( $current_user->ID );
			$total_spent   = wc_get_customer_total_spent( $current_user->ID );
			$registered_on = $current_user->user_registered
				? date_i18n( 'M Y', strtotime( $current_user->user_registered ) )
				: '';

			$is_trade = in_array( 'kaiko_trade', (array) $current_user->roles, true );
			$is_admin = user_can( $current_user, 'manage_options' ) || user_can( $current_user, 'manage_woocommerce' );

			$recent_orders = wc_get_orders(
				array(
					'customer' => $current_user->ID,
					'limit'    => 5,
					'orderby'  => 'date',
					'order'    => 'DESC',
				)
			);
			?>

			<h2><?php echo esc_html( sprintf( __( 'Hello, %s', 'kaiko-child' ), $display_name ) ); ?></h2>
			<p class="kaiko-greeting">
				<?php esc_html_e( 'Welcome back to your trade account. From here you can manage orders, update your business details, and access wholesale pricing.', 'kaiko-child' ); ?>
			</p>

			<?php if ( $is_trade || $is_admin ) : ?>
				<div class="kaiko-account-notice success">
					<h3><?php esc_html_e( 'Trade Account Active', 'kaiko-child' ); ?></h3>
					<p><?php esc_html_e( 'Your wholesale pricing is enabled across all products. Free UK shipping applies to orders over £150.', 'kaiko-child' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="kaiko-stats-grid">
				<div class="kaiko-stat-card">
					<p class="label"><?php esc_html_e( 'Total Orders', 'kaiko-child' ); ?></p>
					<p class="value"><?php echo esc_html( number_format_i18n( $order_count ) ); ?></p>
				</div>
				<div class="kaiko-stat-card">
					<p class="label"><?php esc_html_e( 'Lifetime Spend', 'kaiko-child' ); ?></p>
					<p class="value"><?php echo wp_kses_post( wc_price( $total_spent ) ); ?></p>
				</div>
				<div class="kaiko-stat-card">
					<p class="label"><?php esc_html_e( 'Account Status', 'kaiko-child' ); ?></p>
					<p class="value is-text"><?php esc_html_e( 'Approved', 'kaiko-child' ); ?></p>
					<?php if ( $registered_on ) : ?>
						<p class="delta"><?php echo esc_html( sprintf( __( 'Since %s', 'kaiko-child' ), $registered_on ) ); ?></p>
					<?php endif; ?>
				</div>
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
							$status        = $order->get_status();
							$status_label  = wc_get_order_status_name( $status );
							$status_class  = sanitize_html_class( $status );
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

		<?php else : ?>

			<?php
			// Sub-endpoint: dispatch the same action [woocommerce_my_account] uses
			// internally so WC's own templates render — but inside our shell.
			$value = WC()->query->get_endpoint_query_var_value( $endpoint );
			do_action( 'woocommerce_account_' . $endpoint . '_endpoint', $value );
			?>

		<?php endif; ?>
	</div>

</div>
