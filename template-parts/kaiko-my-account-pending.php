<?php
/**
 * Kaiko — My Account: Pending State
 *
 * Shown to users with the kaiko_pending role (not admins / shop managers).
 * Limited sidebar (Dashboard, Addresses, Account details, Logout) and a
 * pending-application status card. WC sub-endpoints render in-place so
 * pending users can still update their email / address pre-approval.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();

/**
 * Greeting fallback chain.
 *
 * The WC register form only captures email + business name — WooCommerce
 * then auto-generates a username from the email local part (e.g.
 * "silkwormstore.co.uk-0344"). If we fall through to $user_login the
 * greeting reads "HELLO, SILKWORMSTORE.CO.UK-0344" which looks broken.
 *
 * Order: Business name → First name → Email local part → "Trade Partner"
 */
$business_name = get_user_meta( $current_user->ID, 'kaiko_business_name', true );
$email_local   = $current_user->user_email ? strstr( $current_user->user_email, '@', true ) : '';

if ( ! empty( $business_name ) ) {
	$first_name = $business_name;
} elseif ( ! empty( $current_user->first_name ) ) {
	$first_name = $current_user->first_name;
} elseif ( $email_local && false === strpos( $email_local, '.' ) ) {
	// Only use email local part if it looks like a person's handle
	// (not "mail" or "info" or "contact" — those read weird).
	$generic = array( 'mail', 'info', 'contact', 'hello', 'admin', 'sales', 'support', 'trade' );
	$first_name = in_array( strtolower( $email_local ), $generic, true ) ? __( 'Trade Partner', 'kaiko-child' ) : ucfirst( $email_local );
} else {
	$first_name = __( 'Trade Partner', 'kaiko-child' );
}

$applied_date = $current_user->user_registered
	? date_i18n( get_option( 'date_format' ), strtotime( $current_user->user_registered ) )
	: '—';
$myaccount_url = wc_get_page_permalink( 'myaccount' );

$endpoint = WC()->query->get_current_endpoint();

$pending_nav_items = array(
	'dashboard'    => array(
		'label' => __( 'Dashboard', 'kaiko-child' ),
		'url'   => $myaccount_url,
	),
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

$active_key = ( ! $endpoint || 'dashboard' === $endpoint ) ? 'dashboard' : $endpoint;
?>
<style>
/* Defensive layout rules — scoped so theme / plugin CSS can't
   collapse the sidebar or horizontalise the nav items. */
.kaiko-account-layout {
	display: grid !important;
	grid-template-columns: 260px minmax(0, 1fr) !important;
	gap: 36px !important;
	align-items: start !important;
	max-width: 1240px;
	margin: 0 auto;
	padding: 0 24px;
	box-sizing: border-box;
}
.kaiko-account-layout .kaiko-account-nav { min-width: 0; }
.kaiko-account-layout .kaiko-account-nav ul {
	display: block !important;
	list-style: none !important;
	padding: 10px 0 !important;
	margin: 0 !important;
}
.kaiko-account-layout .kaiko-account-nav li {
	display: block !important;
	margin: 0 !important;
	padding: 0 !important;
	border: 0 !important;
}
.kaiko-account-layout .kaiko-account-nav li a {
	display: flex !important;
	width: 100% !important;
	padding: 14px 28px !important;
	font-size: 0.9rem !important;
	color: #78716C;
	text-decoration: none !important;
	border-left: 3px solid transparent;
}
.kaiko-account-layout .kaiko-account-nav li.active a {
	background: rgba(26,92,82,0.06);
	color: #1a5c52;
	font-weight: 600;
	border-left-color: #1a5c52;
}
.kaiko-account-layout .kaiko-account-content {
	min-width: 0;
}
@media (max-width: 860px) {
	.kaiko-account-layout {
		grid-template-columns: 1fr !important;
		gap: 20px !important;
	}
}
</style>
<div class="kaiko-account-layout">

	<nav class="kaiko-account-nav" aria-label="<?php esc_attr_e( 'Account navigation', 'kaiko-child' ); ?>">
		<ul>
			<?php foreach ( $pending_nav_items as $key => $item ) : ?>
				<li class="<?php echo $active_key === $key ? 'active' : ''; ?>">
					<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="kaiko-account-content">
		<?php if ( function_exists( 'wc_print_notices' ) ) { wc_print_notices(); } ?>

		<?php if ( ! $endpoint || 'dashboard' === $endpoint ) : ?>

			<h2><?php echo esc_html( sprintf( __( 'Hello, %s', 'kaiko-child' ), $first_name ) ); ?></h2>
			<p class="kaiko-greeting">
				<?php esc_html_e( 'Thanks for applying for a KAIKO trade account. We review every application personally — you’ll hear from us within 24 hours.', 'kaiko-child' ); ?>
			</p>

			<div class="kaiko-account-notice pending">
				<h3><?php esc_html_e( 'Application Under Review', 'kaiko-child' ); ?></h3>
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: customer email address */
							__( 'Your account is awaiting approval from our team. You’ll get an email at %s as soon as you’re approved and can start placing trade orders.', 'kaiko-child' ),
							$current_user->user_email
						)
					);
					?>
				</p>
			</div>

			<div class="kaiko-stats-grid">
				<div class="kaiko-stat-card">
					<p class="label"><?php esc_html_e( 'Application Status', 'kaiko-child' ); ?></p>
					<p class="value is-text"><?php esc_html_e( 'Pending Review', 'kaiko-child' ); ?></p>
				</div>
				<div class="kaiko-stat-card">
					<p class="label"><?php esc_html_e( 'Estimated Decision', 'kaiko-child' ); ?></p>
					<p class="value is-text"><?php esc_html_e( 'Within 24 hours', 'kaiko-child' ); ?></p>
				</div>
				<div class="kaiko-stat-card">
					<p class="label"><?php esc_html_e( 'Applied', 'kaiko-child' ); ?></p>
					<p class="value is-text"><?php echo esc_html( $applied_date ); ?></p>
				</div>
			</div>

			<h3 class="kaiko-section-heading"><?php esc_html_e( 'While You Wait', 'kaiko-child' ); ?></h3>
			<p style="margin: 0 0 20px; color: var(--k-stone-500); font-size: 0.92rem; line-height: 1.7;">
				<?php esc_html_e( 'Explore our product range to plan your first order. Trade pricing unlocks across the catalogue once you’re approved.', 'kaiko-child' ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" class="kaiko-cta-button">
				<?php esc_html_e( 'Browse Products', 'kaiko-child' ); ?>
			</a>

		<?php else : ?>

			<?php
			// Pending users may visit edit-account / edit-address sub-routes.
			// Dispatch the same WC action [woocommerce_my_account] uses internally.
			$value = WC()->query->get_endpoint_query_var_value( $endpoint );
			do_action( 'woocommerce_account_' . $endpoint . '_endpoint', $value );
			?>

		<?php endif; ?>
	</div>

</div>
