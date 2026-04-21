<?php
/**
 * Kaiko — My Account: Addresses
 *
 * Overrides WC's default `myaccount/my-address.php`. Two-card grid
 * (Billing / Shipping) with edit links, formatted address, Default
 * badge on billing, and an inline "Add now" empty state when the type
 * is unset.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! $customer_id ) {
	return;
}

$address_types = apply_filters(
	'woocommerce_my_account_get_addresses',
	array(
		'billing'  => __( 'Billing address', 'kaiko-child' ),
		'shipping' => __( 'Shipping address', 'kaiko-child' ),
	),
	$customer_id
);
?>

<section class="kaiko-addresses-view">

	<header class="kaiko-welcome-card">
		<div class="kaiko-welcome-card__left">
			<h2 class="kaiko-welcome-card__title"><?php esc_html_e( 'Your addresses', 'kaiko-child' ); ?></h2>
			<p class="kaiko-welcome-card__subline">
				<?php esc_html_e( 'Billing and shipping details Kaiko uses at checkout. Update them any time.', 'kaiko-child' ); ?>
			</p>
		</div>
	</header>

	<div class="kaiko-addresses-grid">
		<?php foreach ( $address_types as $type => $label ) :
			// wc_get_account_formatted_address() is the idiomatic helper. It
			// returns the fully-formatted HTML address string for billing or
			// shipping, or an empty string when the type is unset. The previous
			// pass used $customer->get_billing_address()/get_shipping_address(),
			// which aren't real methods on WC_Customer — they resolved via the
			// legacy __call magic and threw on the live site.
			$formatted  = wc_get_account_formatted_address( $type, $customer_id );
			$edit_url   = wc_get_endpoint_url( 'edit-address', $type, wc_get_page_permalink( 'myaccount' ) );
			$is_default = ( 'billing' === $type );
			$has_content = '' !== trim( wp_strip_all_tags( (string) $formatted ) );
			?>
			<div class="kaiko-address-card">
				<div class="kaiko-address-card__head">
					<span class="kaiko-address-card__label"><?php echo esc_html( $label ); ?></span>
					<a class="kaiko-address-card__edit" href="<?php echo esc_url( $edit_url ); ?>">
						<?php echo $has_content ? esc_html__( 'Edit →', 'kaiko-child' ) : esc_html__( 'Add →', 'kaiko-child' ); ?>
					</a>
				</div>
				<div class="kaiko-address-card__body">
					<?php if ( $has_content ) : ?>
						<?php echo wp_kses_post( $formatted ); ?>
						<?php if ( $is_default ) : ?>
							<span class="kaiko-address-card__default"><?php esc_html_e( 'Default', 'kaiko-child' ); ?></span>
						<?php endif; ?>
					<?php else : ?>
						<p class="kaiko-address-card__empty">
							<?php
							printf(
								/* translators: %s: link to add address */
								esc_html__( 'Not added yet — %s', 'kaiko-child' ),
								'<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Add now', 'kaiko-child' ) . '</a>'
							);
							?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

</section>
