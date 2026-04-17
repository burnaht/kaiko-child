<?php
/**
 * Kaiko — My Account: Pending State
 *
 * Intentionally minimal. A pending applicant can't do anything yet, so
 * the old "dashboard" with sidebar nav + stats grid + sub-endpoints was
 * the wrong shape entirely. This template now shows a single centred
 * confirmation card — same design language as the logged-out
 * "Application received" banner — with a logout button.
 *
 * The core trade flow (set in functions.php) now prevents auto-login on
 * registration, so new applicants should almost never land here. This
 * template covers the edge case where a pending user does log in (e.g.
 * admin testing, or a user who was manually registered).
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$current_user  = wp_get_current_user();
$myaccount_url = wc_get_page_permalink( 'myaccount' );

$business_name = get_user_meta( $current_user->ID, 'kaiko_business_name', true );
$greeting_name = $business_name
	? $business_name
	: ( $current_user->first_name ?: __( 'Trade Partner', 'kaiko-child' ) );

$applied_date = $current_user->user_registered
	? date_i18n( get_option( 'date_format' ), strtotime( $current_user->user_registered ) )
	: '';
?>
<section class="kaiko-pending-state" aria-live="polite">
	<div class="kaiko-pending-state__card">
		<div class="kaiko-pending-state__icon" aria-hidden="true">
			<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="10"/>
				<polyline points="12 6 12 12 16 14"/>
			</svg>
		</div>

		<p class="kaiko-pending-state__eyebrow"><?php esc_html_e( 'Application Received', 'kaiko-child' ); ?></p>
		<h1 class="kaiko-pending-state__title">
			<?php
			/* translators: %s: business name or trade partner */
			echo esc_html( sprintf( __( 'Thanks, %s.', 'kaiko-child' ), $greeting_name ) );
			?>
		</h1>
		<p class="kaiko-pending-state__lede">
			<?php esc_html_e( 'We review every trade application personally — you’ll hear back within 24 hours. Once approved, we’ll email you a link to log in and start placing trade orders.', 'kaiko-child' ); ?>
		</p>

		<?php if ( $applied_date ) : ?>
			<p class="kaiko-pending-state__meta">
				<?php echo esc_html( sprintf( __( 'Submitted %s', 'kaiko-child' ), $applied_date ) ); ?>
				&nbsp;·&nbsp;
				<?php echo esc_html( sprintf( __( 'Confirmation sent to %s', 'kaiko-child' ), $current_user->user_email ) ); ?>
			</p>
		<?php endif; ?>

		<div class="kaiko-pending-state__actions">
			<a class="kaiko-pending-state__btn kaiko-pending-state__btn--primary" href="<?php echo esc_url( home_url( '/products/' ) ); ?>">
				<?php esc_html_e( 'Browse products', 'kaiko-child' ); ?>
			</a>
			<a class="kaiko-pending-state__btn kaiko-pending-state__btn--ghost" href="<?php echo esc_url( wp_logout_url( $myaccount_url ) ); ?>">
				<?php esc_html_e( 'Log out', 'kaiko-child' ); ?>
			</a>
		</div>
	</div>
</section>
<style>
.kaiko-pending-state {
	display: flex;
	justify-content: center;
	width: 100%;
	padding: 0 24px;
	box-sizing: border-box;
}
.kaiko-pending-state__card {
	width: 100%;
	max-width: 620px;
	padding: 56px 48px;
	background: #ffffff;
	border: 1px solid rgba(26,92,82,0.12);
	border-radius: 24px;
	box-shadow: 0 12px 40px rgba(28,25,23,0.06);
	text-align: center;
}
.kaiko-pending-state__icon {
	width: 64px; height: 64px;
	margin: 0 auto 24px;
	border-radius: 50%;
	background: rgba(26,92,82,0.08);
	color: #1a5c52;
	display: flex; align-items: center; justify-content: center;
}
.kaiko-pending-state__eyebrow {
	font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
	font-size: 0.72rem;
	font-weight: 600;
	letter-spacing: 0.2em;
	text-transform: uppercase;
	color: #1a5c52;
	margin: 0 0 12px;
}
.kaiko-pending-state__title {
	font-family: 'Cormorant Garamond', Georgia, serif;
	font-size: 2.1rem;
	font-weight: 600;
	line-height: 1.15;
	margin: 0 0 16px;
	color: #1C1917;
	letter-spacing: 0.005em;
}
.kaiko-pending-state__lede {
	font-size: 1rem;
	line-height: 1.65;
	color: #44403C;
	margin: 0 0 24px;
}
.kaiko-pending-state__meta {
	font-size: 0.82rem;
	color: #78716C;
	margin: 0 0 32px;
	padding: 14px 20px;
	background: #FAFAF9;
	border-radius: 10px;
	border: 1px solid #E7E5E4;
	line-height: 1.6;
}
.kaiko-pending-state__actions {
	display: flex;
	gap: 12px;
	justify-content: center;
	flex-wrap: wrap;
}
.kaiko-pending-state__btn {
	display: inline-flex;
	align-items: center;
	padding: 14px 28px;
	font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
	font-size: 0.88rem;
	font-weight: 600;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	text-decoration: none;
	border-radius: 12px;
	border: 1px solid transparent;
	transition: all 200ms ease;
}
.kaiko-pending-state__btn--primary {
	background: #1a5c52;
	color: #ffffff;
	border-color: #1a5c52;
}
.kaiko-pending-state__btn--primary:hover {
	background: #134840;
	border-color: #134840;
}
.kaiko-pending-state__btn--ghost {
	background: transparent;
	color: #44403C;
	border-color: #D6D3D1;
}
.kaiko-pending-state__btn--ghost:hover {
	border-color: #78716C;
	color: #1C1917;
}
@media (max-width: 640px) {
	.kaiko-pending-state__card { padding: 40px 28px; }
	.kaiko-pending-state__title { font-size: 1.7rem; }
	.kaiko-pending-state__actions { flex-direction: column; }
	.kaiko-pending-state__btn { width: 100%; justify-content: center; }
}
</style>
