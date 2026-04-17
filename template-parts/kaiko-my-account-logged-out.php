<?php
/**
 * Kaiko — My Account: Logged-Out State
 *
 * Renders the WooCommerce login + register forms via the
 * theme's overridden form-login.php (which contains the
 * trade intro banner and two-card auth grid).
 *
 * Going through wc_get_template ensures the WC-handled
 * notices, nonces, and login/register actions all fire.
 *
 * If the user has just submitted a trade application we detect the
 * ?application=received query arg (set via woocommerce_registration_redirect
 * in functions.php) and show a branded confirmation banner instead of the
 * default "Registration complete" WC notice.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$application_received = isset( $_GET['application'] ) && 'received' === $_GET['application'];

if ( $application_received ) :
	// Suppress WC's default "Your account was created successfully..." notice
	// — we're showing a branded equivalent below.
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}
	?>
	<section class="kaiko-application-received" role="status" aria-live="polite">
		<div class="kaiko-application-received__inner">
			<div class="kaiko-application-received__icon" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
					<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
					<polyline points="22 4 12 14.01 9 11.01"/>
				</svg>
			</div>
			<div class="kaiko-application-received__body">
				<h2>Application received</h2>
				<p>Thanks for applying for a KAIKO trade account. Every application is reviewed personally — you&rsquo;ll hear back within 24 hours. Once approved, we&rsquo;ll email you a link to log in and start placing trade orders.</p>
				<p class="kaiko-application-received__meta">
					<strong>What happens next:</strong>
					approval email &rarr; log in &rarr; full trade pricing unlocks across the catalogue.
				</p>
			</div>
		</div>
	</section>
	<style>
		.kaiko-application-received {
			margin: 0 auto 48px;
			max-width: 820px;
			border: 1px solid rgba(26,92,82,0.22);
			background: linear-gradient(180deg, rgba(184,212,53,0.08) 0%, rgba(26,92,82,0.04) 100%);
			border-radius: 20px;
			padding: 32px 36px;
			box-shadow: 0 8px 32px rgba(28,25,23,0.06);
		}
		.kaiko-application-received__inner {
			display: flex;
			gap: 22px;
			align-items: flex-start;
		}
		.kaiko-application-received__icon {
			flex: 0 0 56px;
			width: 56px; height: 56px;
			border-radius: 50%;
			background: rgba(26,92,82,0.1);
			color: #1a5c52;
			display: flex; align-items: center; justify-content: center;
		}
		.kaiko-application-received__body { flex: 1; min-width: 0; }
		.kaiko-application-received__body h2 {
			font-family: 'Cormorant Garamond', Georgia, serif;
			font-size: 1.8rem; font-weight: 600;
			margin: 0 0 10px;
			color: #1C1917;
			letter-spacing: 0.01em;
		}
		.kaiko-application-received__body p {
			font-size: 0.95rem;
			color: #44403C;
			line-height: 1.65;
			margin: 0 0 12px;
		}
		.kaiko-application-received__body p:last-child { margin-bottom: 0; }
		.kaiko-application-received__meta {
			font-size: 0.85rem !important;
			color: #78716C !important;
			padding: 12px 16px;
			background: rgba(255,255,255,0.6);
			border-radius: 10px;
			border: 1px solid rgba(255,255,255,0.8);
		}
		.kaiko-application-received__meta strong {
			color: #1a5c52;
			font-weight: 600;
			margin-right: 6px;
		}
		@media (max-width: 640px) {
			.kaiko-application-received { padding: 24px; }
			.kaiko-application-received__inner { flex-direction: column; gap: 16px; }
		}
	</style>
	<?php
else :
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
endif;

wc_get_template( 'myaccount/form-login.php' );
