<?php
/**
 * Kaiko — My Account: Orders list
 *
 * Overrides WC's default `myaccount/orders.php`. Runs inside our account
 * layout's right column (see template-parts/kaiko-my-account-approved.php).
 *
 * Queries all of the current customer's orders in one go (no pagination —
 * Kaiko is B2B, order counts per customer are small) and renders the
 * concept's filter chips + table. Row filtering is client-side via
 * assets/js/kaiko-account.js, keyed off data-status attributes.
 *
 * Styling: assets/css/kaiko-my-account.css (.kaiko-chip*, .kaiko-orders-*).
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id();

// 'customer_id' (strict int) rather than 'customer' (email-or-int) — explicit
// intent + matches WC's documented query arg. Status list is the full set of
// registered statuses with the wc- prefix stripped, which wc_get_orders expects.
$customer_orders = $user_id
	? wc_get_orders( array(
		'customer_id' => $user_id,
		'limit'       => -1,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'type'        => 'shop_order',
		'status'      => array_map(
			static function ( $s ) { return 0 === strpos( $s, 'wc-' ) ? substr( $s, 3 ) : $s; },
			array_keys( wc_get_order_statuses() )
		),
	) )
	: array();

// Count per WC status slug (no leading "wc-"). Keys must match the values
// the rows emit via $order->get_status() and the JS filter.
$counts = array(
	'all'        => count( $customer_orders ),
	'processing' => 0,
	'completed'  => 0,
	'on-hold'    => 0,
	'pending'    => 0,
	'cancelled'  => 0,
	'failed'     => 0,
	'refunded'   => 0,
);
foreach ( $customer_orders as $cust_order ) {
	$s = $cust_order->get_status();
	if ( isset( $counts[ $s ] ) ) {
		$counts[ $s ]++;
	}
}

// Only render chips the user can act on. "All" + any status with ≥1 order
// + a "Cancelled" chip even at zero (matches concept — communicates the
// filter exists).
$chip_order = array( 'all', 'processing', 'completed', 'on-hold', 'pending', 'cancelled', 'failed', 'refunded' );
$chip_labels = array(
	'all'        => __( 'All', 'kaiko-child' ),
	'processing' => __( 'Processing', 'kaiko-child' ),
	'completed'  => __( 'Completed', 'kaiko-child' ),
	'on-hold'    => __( 'On hold', 'kaiko-child' ),
	'pending'    => __( 'Pending', 'kaiko-child' ),
	'cancelled'  => __( 'Cancelled', 'kaiko-child' ),
	'failed'     => __( 'Failed', 'kaiko-child' ),
	'refunded'   => __( 'Refunded', 'kaiko-child' ),
);
?>

<section class="kaiko-orders-view">

	<header class="kaiko-welcome-card">
		<div class="kaiko-welcome-card__left">
			<h2 class="kaiko-welcome-card__title"><?php esc_html_e( 'Your orders', 'kaiko-child' ); ?></h2>
			<p class="kaiko-welcome-card__subline">
				<?php esc_html_e( 'Track, reorder, and review every order you\'ve placed.', 'kaiko-child' ); ?>
			</p>
		</div>
	</header>

	<?php if ( empty( $customer_orders ) ) : ?>

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

	<?php else : ?>

		<div class="kaiko-orders-filters" role="tablist" aria-label="<?php esc_attr_e( 'Filter orders by status', 'kaiko-child' ); ?>" data-kaiko-orders-filters>
			<?php foreach ( $chip_order as $chip_key ) :
				// Skip zero-count chips except "All" and "Cancelled" (concept baseline).
				if ( 0 === $counts[ $chip_key ] && ! in_array( $chip_key, array( 'all', 'cancelled' ), true ) ) {
					continue;
				}
				$is_active = ( 'all' === $chip_key );
				?>
				<button
					type="button"
					class="kaiko-chip<?php echo $is_active ? ' active' : ''; ?>"
					data-status-filter="<?php echo esc_attr( $chip_key ); ?>"
					role="tab"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
				>
					<?php echo esc_html( $chip_labels[ $chip_key ] ); ?>
					<span class="kaiko-chip__count"><?php echo esc_html( number_format_i18n( $counts[ $chip_key ] ) ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

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
				<tbody data-kaiko-orders-tbody>
					<?php foreach ( $customer_orders as $customer_order ) :
						if ( ! $customer_order instanceof WC_Order ) {
							continue;
						}
						$status       = $customer_order->get_status();
						$status_label = wc_get_order_status_name( $status );
						$status_class = sanitize_html_class( $status );
						// Item count is a subtitle line, not a data point we
						// need to be precise about — drop the refund subtraction
						// (get_item_count_refunded isn't universally available
						// across every WC order class).
						$item_count   = (int) $customer_order->get_item_count();
						?>
						<tr data-status="<?php echo esc_attr( $status ); ?>">
							<td><span class="order-num">#<?php echo esc_html( $customer_order->get_order_number() ); ?></span></td>
							<td><?php echo esc_html( wc_format_datetime( $customer_order->get_date_created(), get_option( 'date_format' ) ) ); ?></td>
							<td>
								<span class="kaiko-status-pill kaiko-status-pill--<?php echo esc_attr( $status_class ); ?>">
									<?php echo esc_html( $status_label ); ?>
								</span>
							</td>
							<td class="order-total">
								<?php echo wp_kses_post( $customer_order->get_formatted_order_total() ); ?>
								<?php if ( $item_count > 0 ) : ?>
									<br><span style="font-size:11px;color:var(--k-stone-500);font-weight:500">
										<?php
										printf(
											/* translators: %d: item count */
											esc_html( _n( '%d item', '%d items', $item_count, 'kaiko-child' ) ),
											$item_count
										);
										?>
									</span>
								<?php endif; ?>
							</td>
							<td>
								<a class="action-btn" href="<?php echo esc_url( $customer_order->get_view_order_url() ); ?>">
									<?php esc_html_e( 'View', 'kaiko-child' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	<?php endif; ?>

</section>
