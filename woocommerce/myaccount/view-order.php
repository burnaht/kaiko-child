<?php
/**
 * Kaiko — My Account: View single order
 *
 * Overrides WC's default `myaccount/view-order.php`. Renders the concept's
 * back-link + order number + status pill header, the items list with tier
 * meta inlined (via kaiko_order_line_tier_meta() → same tier data shape the
 * cart page + drawer use), and the totals card with gold tier-savings line.
 *
 * @var int      $order_id Injected by WC via wc_get_template().
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

// WC passes $order_id (and sometimes $order) via wc_get_template() args, but
// don't trust that — rederive defensively. The previous version relied on
// current_user_can('view_order', $order_id), which goes through WC's meta-cap
// mapping and can fatal if the order isn't loadable at map time. Direct
// customer-id comparison avoids that code path entirely.
if ( ! isset( $order_id ) ) {
	$order_id = absint( get_query_var( 'view-order' ) );
}
$order = isset( $order ) && $order instanceof WC_Order ? $order : wc_get_order( $order_id );

$user_id    = get_current_user_id();
$owns_order = $order && $user_id && (int) $order->get_customer_id() === $user_id;
$is_staff   = current_user_can( 'manage_woocommerce' );

if ( ! $order || ! ( $owns_order || $is_staff ) ) {
	?>
	<div class="kaiko-empty-state">
		<h4 class="kaiko-empty-state__title"><?php esc_html_e( 'Order not found', 'kaiko-child' ); ?></h4>
		<p class="kaiko-empty-state__text"><?php esc_html_e( 'That order either doesn\'t exist or isn\'t yours to view.', 'kaiko-child' ); ?></p>
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="kaiko-btn kaiko-btn--primary">
			<?php esc_html_e( 'Back to orders', 'kaiko-child' ); ?>
		</a>
	</div>
	<?php
	return;
}

$status       = $order->get_status();
$status_label = wc_get_order_status_name( $status );
$status_class = sanitize_html_class( $status );

// Tier savings total — sum of per-line savings so the totals card can
// render a single "Tier savings  − £X.XX" row in gold.
$tier_savings_total = 0.0;
foreach ( $order->get_items() as $item ) {
	$meta = kaiko_order_line_tier_meta( $item );
	if ( is_array( $meta ) && ! empty( $meta['saved_total'] ) ) {
		$tier_savings_total += (float) $meta['saved_total'];
	}
}

// Back link destination — always the orders list.
$back_url = wc_get_account_endpoint_url( 'orders' );
?>

<section class="kaiko-single-order">

	<header class="kaiko-order-head">
		<div class="kaiko-order-head__left">
			<a href="<?php echo esc_url( $back_url ); ?>" class="kaiko-order-head__back">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 19"/>
				</svg>
				<?php esc_html_e( 'Back to orders', 'kaiko-child' ); ?>
			</a>
			<h2 class="kaiko-order-head__num">
				<?php
				printf(
					/* translators: %s: order number */
					esc_html__( 'Order #%s', 'kaiko-child' ),
					esc_html( $order->get_order_number() )
				);
				?>
				<span class="kaiko-status-pill kaiko-status-pill--<?php echo esc_attr( $status_class ); ?>">
					<?php echo esc_html( $status_label ); ?>
				</span>
			</h2>
			<p class="kaiko-order-head__meta">
				<?php
				printf(
					/* translators: %s: formatted order date */
					esc_html__( 'Placed %s', 'kaiko-child' ),
					esc_html( wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) ) )
				);
				?>
			</p>
		</div>

		<div class="kaiko-order-head__actions">
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="kaiko_reorder">
				<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
				<?php wp_nonce_field( 'kaiko_reorder_' . $order->get_id() ); ?>
				<button type="submit" class="kaiko-btn kaiko-btn--primary">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
					</svg>
					<?php esc_html_e( 'Reorder', 'kaiko-child' ); ?>
				</button>
			</form>
		</div>
	</header>

	<?php do_action( 'woocommerce_view_order', $order_id ); ?>

	<div class="kaiko-order-items">
		<?php foreach ( $order->get_items() as $item_id => $item ) :
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$product     = $item->get_product();
			$name        = $item->get_name();
			$permalink   = ( $product && $product->is_visible() ) ? $product->get_permalink( $item ) : '';
			$qty         = (int) $item->get_quantity();
			$thumb       = $product ? $product->get_image( array( 60, 60 ) ) : '';
			$line_total  = wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );
			$tier_meta   = kaiko_order_line_tier_meta( $item );
			$applied_unit = $qty > 0 ? (float) $item->get_total() / $qty : 0.0;

			// Variation attributes joined into a "Large · Moss Green" string.
			$attrs = array();
			if ( $product && $product->is_type( 'variation' ) ) {
				foreach ( $item->get_meta_data() as $meta ) {
					if ( is_object( $meta ) && ! empty( $meta->value ) && '' !== (string) $meta->key && 0 !== strpos( (string) $meta->key, '_' ) ) {
						$attrs[] = wc_clean( $meta->value );
					}
				}
			}
			?>
			<div class="kaiko-order-item">
				<?php if ( $permalink ) : ?>
					<a class="kaiko-order-item__thumb" href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $thumb ); ?></a>
				<?php else : ?>
					<span class="kaiko-order-item__thumb"><?php echo wp_kses_post( $thumb ); ?></span>
				<?php endif; ?>

				<div class="kaiko-order-item__body">
					<?php if ( $permalink ) : ?>
						<a class="kaiko-order-item__name" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $name ); ?></a>
					<?php else : ?>
						<span class="kaiko-order-item__name"><?php echo esc_html( $name ); ?></span>
					<?php endif; ?>

					<p class="kaiko-order-item__meta">
						<?php
						$meta_bits = array();
						$meta_bits[] = sprintf(
							/* translators: %d: qty */
							esc_html( _n( '%d unit', '%d units', $qty, 'kaiko-child' ) ),
							$qty
						);
						if ( ! empty( $attrs ) ) {
							$meta_bits[] = esc_html( implode( ' · ', $attrs ) );
						}
						echo implode( ' · ', $meta_bits ); // already escaped
						?>
					</p>

					<?php
					// Render the shared tier chip + nudge (same partial the cart
					// page + drawer use). Phase-1 parity — zero divergent tier code.
					if ( is_array( $tier_meta ) && ! empty( $tier_meta['active_index'] ) && $tier_meta['active_index'] > 1 && ! empty( $tier_meta['saved_total'] ) ) :
						?>
						<span class="kaiko-tier-applied">
							<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
							<?php
							printf(
								/* translators: 1: tier index, 2: per-unit price */
								esc_html__( 'Tier %1$d applied · %2$s each', 'kaiko-child' ),
								(int) $tier_meta['active_index'],
								wp_kses_post( wc_price( $applied_unit, array( 'currency' => $order->get_currency() ) ) )
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<div class="kaiko-order-item__total">
					<?php echo wp_kses_post( $line_total ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php
	// Totals: map WC's ordered totals into our row layout. Inject the
	// Tier savings line in gold (only when > 0) between subtotal and shipping.
	$totals = $order->get_order_item_totals();
	?>
	<div class="kaiko-order-totals">
		<?php foreach ( $totals as $key => $row ) :
			$row_class = 'kaiko-order-totals__row';
			if ( 'order_total' === $key ) {
				$row_class .= ' kaiko-order-totals__row--total';
			}
			?>
			<div class="<?php echo esc_attr( $row_class ); ?>">
				<span class="label"><?php echo esc_html( $row['label'] ); ?></span>
				<span class="val"><?php echo wp_kses_post( $row['value'] ); ?></span>
			</div>

			<?php
			// Slide the tier savings row in right after the cart_subtotal row.
			if ( 'cart_subtotal' === $key && $tier_savings_total > 0 ) :
				?>
				<div class="kaiko-order-totals__row">
					<span class="label"><?php esc_html_e( 'Tier savings', 'kaiko-child' ); ?></span>
					<span class="val savings">− <?php echo wp_kses_post( wc_price( $tier_savings_total, array( 'currency' => $order->get_currency() ) ) ); ?></span>
				</div>
				<?php
			endif;
		endforeach;

		// If there was no cart_subtotal row but savings exist, render the
		// savings row as the first entry so it still surfaces.
		if ( $tier_savings_total > 0 && ! isset( $totals['cart_subtotal'] ) ) : ?>
			<div class="kaiko-order-totals__row">
				<span class="label"><?php esc_html_e( 'Tier savings', 'kaiko-child' ); ?></span>
				<span class="val savings">− <?php echo wp_kses_post( wc_price( $tier_savings_total, array( 'currency' => $order->get_currency() ) ) ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $order->get_customer_note() ) : ?>
		<div class="kaiko-form-card" style="margin-top:20px;">
			<h4 class="kaiko-form-section-title"><?php esc_html_e( 'Customer note', 'kaiko-child' ); ?></h4>
			<p style="margin:0;color:var(--k-stone-700);font-size:14px;line-height:1.6"><?php echo wp_kses_post( wpautop( $order->get_customer_note() ) ); ?></p>
		</div>
	<?php endif; ?>

</section>
