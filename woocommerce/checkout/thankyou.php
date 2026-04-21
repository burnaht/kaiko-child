<?php
/**
 * Kaiko — Order Received (thank-you) page.
 *
 * Overrides woocommerce/templates/checkout/thankyou.php for BACS-only
 * Kaiko. Kaiko is a trade-only B2B storefront; bank transfer is the
 * single accepted payment method. This template treats the post-checkout
 * screen as the hero of the payment flow — bank details + order number
 * payment reference are the primary content, not an afterthought under
 * an order summary.
 *
 * Fires the standard WC hooks so WC core, Subscriptions, and any other
 * plugin that injects rows into the order-received page keep working:
 *   - woocommerce_before_thankyou
 *   - woocommerce_thankyou_{$payment_method}
 *   - woocommerce_thankyou
 * Order-details + customer-details sections are rendered via the
 * woocommerce_thankyou action chain (core hooks woocommerce_order_details_table
 * + the customer details block onto that action).
 *
 * @var WC_Order|false $order  Passed in by wc_get_template().
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

// Graceful fallback — WC passes false when the order ID is missing or
// invalid (cancelled, wrong key, etc.). Don't fatal, just render a
// helpful message and a way back to the shop.
if ( ! $order || ! $order instanceof WC_Order ) {
	?>
	<section class="kaiko-thankyou kaiko-thankyou--empty">
		<div class="kaiko-thankyou__inner">
			<h1 class="kaiko-thankyou__title"><?php esc_html_e( 'Order not found', 'kaiko-child' ); ?></h1>
			<p class="kaiko-thankyou__lede">
				<?php esc_html_e( 'We couldn\'t load that order — the link may have expired. If you\'ve just placed an order, check your email for the receipt.', 'kaiko-child' ); ?>
			</p>
			<a class="kaiko-btn kaiko-btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
				<?php esc_html_e( 'Go to My Account', 'kaiko-child' ); ?>
			</a>
		</div>
	</section>
	<?php
	return;
}

$order_id       = $order->get_id();
$order_number   = $order->get_order_number();
$payment_method = $order->get_payment_method();
$first_name     = $order->get_billing_first_name() ?: __( 'there', 'kaiko-child' );
$email          = $order->get_billing_email();

// BACS gateway details. Kaiko is BACS-only; if it's disabled we surface
// a notice rather than a half-broken card. Tom configures this in
// WooCommerce → Settings → Payments → Direct Bank Transfer.
$bacs_gateway = null;
if ( class_exists( 'WooCommerce' ) && WC()->payment_gateways() ) {
	$gateways     = WC()->payment_gateways()->payment_gateways();
	$bacs_gateway = isset( $gateways['bacs'] ) ? $gateways['bacs'] : null;
}
$bacs_accounts = array();
if ( $bacs_gateway && isset( $bacs_gateway->account_details ) && is_array( $bacs_gateway->account_details ) ) {
	$bacs_accounts = $bacs_gateway->account_details;
}

do_action( 'woocommerce_before_thankyou', $order_id );
?>

<section class="kaiko-thankyou">

	<!-- Hero -->
	<header class="kaiko-thankyou__hero">
		<span class="kaiko-thankyou__eyebrow"><?php esc_html_e( 'Order received', 'kaiko-child' ); ?></span>
		<h1 class="kaiko-thankyou__title">
			<?php esc_html_e( "Order received — here's how to pay", 'kaiko-child' ); ?>
		</h1>
		<p class="kaiko-thankyou__order-num">
			<?php
			printf(
				/* translators: %s: order number (e.g. KPW-1234) */
				esc_html__( 'Order #%s', 'kaiko-child' ),
				esc_html( $order_number )
			);
			?>
		</p>
		<p class="kaiko-thankyou__lede">
			<?php
			printf(
				/* translators: 1: first name, 2: email address */
				esc_html__( 'Thanks, %1$s. We\'ve emailed a copy of your order to %2$s.', 'kaiko-child' ),
				esc_html( $first_name ),
				esc_html( $email )
			);
			?>
		</p>
	</header>

	<!-- Payment card — the hero of this page -->
	<?php if ( 'bacs' === $payment_method && ! empty( $bacs_accounts ) ) : ?>
		<article class="kaiko-thankyou__payment" aria-labelledby="kaiko-pay-label">
			<div class="kaiko-thankyou__payment__head">
				<span class="kaiko-thankyou__payment__label" id="kaiko-pay-label">
					<?php esc_html_e( 'Bank details — please pay within 7 days', 'kaiko-child' ); ?>
				</span>
				<span class="kaiko-thankyou__payment__total">
					<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
				</span>
			</div>

			<?php foreach ( $bacs_accounts as $account_index => $account ) :
				$account      = (array) $account;
				$account_name = isset( $account['account_name'] ) ? $account['account_name'] : '';
				$bank_name    = isset( $account['bank_name'] ) ? $account['bank_name'] : '';
				$account_no   = isset( $account['account_number'] ) ? $account['account_number'] : '';
				$sort_code    = isset( $account['sort_code'] ) ? $account['sort_code'] : '';
				$iban         = isset( $account['iban'] ) ? $account['iban'] : '';
				$bic          = isset( $account['bic'] ) ? $account['bic'] : '';

				// Map of label → value, only rendering rows that actually have data.
				$rows = array_filter( array(
					'account_name'   => array( 'label' => __( 'Account name', 'kaiko-child' ),   'value' => $account_name ),
					'bank_name'      => array( 'label' => __( 'Bank', 'kaiko-child' ),           'value' => $bank_name ),
					'account_number' => array( 'label' => __( 'Account number', 'kaiko-child' ), 'value' => $account_no ),
					'sort_code'      => array( 'label' => __( 'Sort code', 'kaiko-child' ),      'value' => $sort_code ),
					'iban'           => array( 'label' => __( 'IBAN', 'kaiko-child' ),           'value' => $iban ),
					'bic'            => array( 'label' => __( 'BIC / SWIFT', 'kaiko-child' ),    'value' => $bic ),
				), static function ( $row ) { return '' !== trim( (string) $row['value'] ); } );
				?>

				<dl class="kaiko-thankyou__payment__rows">
					<?php foreach ( $rows as $key => $row ) : ?>
						<div class="kaiko-thankyou__payment__row">
							<dt><?php echo esc_html( $row['label'] ); ?></dt>
							<dd>
								<span class="kaiko-thankyou__payment__val" data-copy-value="<?php echo esc_attr( $row['value'] ); ?>">
									<?php echo esc_html( $row['value'] ); ?>
								</span>
								<button
									type="button"
									class="kaiko-thankyou__payment__copy"
									data-copy-target
									aria-label="<?php
									/* translators: %s: field label, e.g. Sort code */
									printf( esc_attr__( 'Copy %s', 'kaiko-child' ), esc_attr( $row['label'] ) );
									?>"
								>
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
										<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
									</svg>
									<span class="kaiko-thankyou__payment__copy__label"><?php esc_html_e( 'Copy', 'kaiko-child' ); ?></span>
								</button>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<?php if ( count( $bacs_accounts ) > 1 && $account_index < count( $bacs_accounts ) - 1 ) : ?>
					<hr class="kaiko-thankyou__payment__sep" aria-hidden="true">
				<?php endif; ?>
			<?php endforeach; ?>

			<div class="kaiko-thankyou__payment__reference">
				<dt><?php esc_html_e( 'Use this as your payment reference', 'kaiko-child' ); ?></dt>
				<dd>
					<span class="kaiko-thankyou__payment__val kaiko-thankyou__payment__val--reference" data-copy-value="<?php echo esc_attr( $order_number ); ?>">
						<?php echo esc_html( $order_number ); ?>
					</span>
					<button
						type="button"
						class="kaiko-thankyou__payment__copy kaiko-thankyou__payment__copy--primary"
						data-copy-target
						aria-label="<?php esc_attr_e( 'Copy payment reference', 'kaiko-child' ); ?>"
					>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
							<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
						</svg>
						<span class="kaiko-thankyou__payment__copy__label"><?php esc_html_e( 'Copy', 'kaiko-child' ); ?></span>
					</button>
				</dd>
			</div>

			<!-- Screen-reader announcement region for the copy action -->
			<div class="kaiko-thankyou__payment__live" aria-live="polite" aria-atomic="true"></div>
		</article>
	<?php elseif ( 'bacs' === $payment_method ) : ?>
		<article class="kaiko-thankyou__payment kaiko-thankyou__payment--disabled">
			<p>
				<?php esc_html_e( 'Bank details aren\'t configured yet. We\'ll be in touch by email with payment instructions shortly.', 'kaiko-child' ); ?>
			</p>
		</article>
	<?php endif; ?>

	<!-- What happens next -->
	<section class="kaiko-thankyou__steps" aria-label="<?php esc_attr_e( 'What happens next', 'kaiko-child' ); ?>">
		<h2 class="kaiko-thankyou__section-title"><?php esc_html_e( 'What happens next', 'kaiko-child' ); ?></h2>
		<ol class="kaiko-thankyou__steps__list">
			<li class="kaiko-thankyou__step">
				<span class="kaiko-thankyou__step__num" aria-hidden="true">1</span>
				<div class="kaiko-thankyou__step__body">
					<h3><?php esc_html_e( 'Transfer the amount above', 'kaiko-child' ); ?></h3>
					<p><?php
						printf(
							/* translators: %s: order number used as payment reference */
							esc_html__( 'Use %s as your payment reference so we can match it to your order.', 'kaiko-child' ),
							'<strong>' . esc_html( $order_number ) . '</strong>'
						);
					?></p>
				</div>
			</li>
			<li class="kaiko-thankyou__step">
				<span class="kaiko-thankyou__step__num" aria-hidden="true">2</span>
				<div class="kaiko-thankyou__step__body">
					<h3><?php esc_html_e( 'We match payment to your order', 'kaiko-child' ); ?></h3>
					<p><?php esc_html_e( 'Usually same working day — we\'ll email you once the funds clear.', 'kaiko-child' ); ?></p>
				</div>
			</li>
			<li class="kaiko-thankyou__step">
				<span class="kaiko-thankyou__step__num" aria-hidden="true">3</span>
				<div class="kaiko-thankyou__step__body">
					<h3><?php esc_html_e( 'We dispatch your order', 'kaiko-child' ); ?></h3>
					<p><?php esc_html_e( 'Tracking email follows the moment your parcel leaves our warehouse.', 'kaiko-child' ); ?></p>
				</div>
			</li>
		</ol>
	</section>

	<!-- Order summary + customer details.
	     Both render via the woocommerce_thankyou action chain:
	       - woocommerce_thankyou_{$gateway}  (gateway-specific hook)
	       - woocommerce_thankyou             (generic; WC core attaches
	         the order-details table + customer details block to this).
	     Keeping the action chain intact means WC Subscriptions / other
	     plugins that inject rows continue to fire on this screen. -->
	<section class="kaiko-thankyou__details">
		<h2 class="kaiko-thankyou__section-title"><?php esc_html_e( 'Order summary', 'kaiko-child' ); ?></h2>
		<?php do_action( 'woocommerce_thankyou_' . $payment_method, $order_id ); ?>
		<?php do_action( 'woocommerce_thankyou', $order_id ); ?>
	</section>

</section>

<script>
(function () {
	'use strict';

	// Progressive enhancement: hide copy buttons entirely when the
	// Clipboard API isn't available. A broken button is worse than
	// no button — the value is still selectable + copyable manually.
	if ( ! navigator.clipboard || typeof navigator.clipboard.writeText !== 'function' ) {
		document.querySelectorAll( '.kaiko-thankyou__payment__copy' ).forEach( function ( btn ) {
			btn.hidden = true;
		} );
		return;
	}

	var LIVE_SELECTOR = '.kaiko-thankyou__payment__live';
	var liveRegion   = document.querySelector( LIVE_SELECTOR );

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest ? e.target.closest( '.kaiko-thankyou__payment__copy' ) : null;
		if ( ! btn ) return;
		e.preventDefault();

		// Sibling span carries the actual value in a data-attribute so
		// whitespace stripping / locale formatting is under our control.
		var row    = btn.closest( 'dd' ) || btn.parentElement;
		var source = row && row.querySelector( '[data-copy-value]' );
		var value  = source ? source.getAttribute( 'data-copy-value' ) : '';
		if ( ! value ) return;

		navigator.clipboard.writeText( value ).then( function () {
			var label = btn.querySelector( '.kaiko-thankyou__payment__copy__label' );
			var prev  = label ? label.textContent : '';
			if ( label ) label.textContent = <?php echo wp_json_encode( __( 'Copied!', 'kaiko-child' ) ); ?>;
			btn.classList.add( 'is-copied' );
			if ( liveRegion ) {
				liveRegion.textContent = <?php echo wp_json_encode( __( 'Copied to clipboard', 'kaiko-child' ) ); ?>;
			}
			setTimeout( function () {
				if ( label && prev ) label.textContent = prev;
				btn.classList.remove( 'is-copied' );
				if ( liveRegion ) liveRegion.textContent = '';
			}, 1500 );
		} ).catch( function () {
			// Swallow — the value is still selectable manually. No fake UX.
		} );
	} );
})();
</script>
