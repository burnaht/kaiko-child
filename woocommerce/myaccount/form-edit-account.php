<?php
/**
 * Kaiko — My Account: Edit account form
 *
 * Overrides WC's default `myaccount/form-edit-account.php`. Concept's
 * three-section form (Personal / Business / Password) in a single
 * .kaiko-form-card. Keeps WC's field names + nonce so the core save
 * handler at WC_Form_Handler::save_account_details() still runs.
 *
 * @var WP_User $user Injected by WC via wc_get_template().
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="kaiko-edit-account-view">

	<header class="kaiko-welcome-card">
		<div class="kaiko-welcome-card__left">
			<h2 class="kaiko-welcome-card__title"><?php esc_html_e( 'Account details', 'kaiko-child' ); ?></h2>
			<p class="kaiko-welcome-card__subline">
				<?php esc_html_e( 'Update your personal info, business details, and password.', 'kaiko-child' ); ?>
			</p>
		</div>
	</header>

	<form
		class="woocommerce-EditAccountForm edit-account kaiko-form"
		action=""
		method="post"
		<?php do_action( 'woocommerce_edit_account_form_tag' ); ?>
	>
		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<div class="kaiko-form-card">

			<!-- Personal -->
			<h3 class="kaiko-form-section-title"><?php esc_html_e( 'Personal', 'kaiko-child' ); ?></h3>

			<div class="kaiko-form-row">
				<div class="kaiko-form-field">
					<label for="account_first_name">
						<?php esc_html_e( 'First name', 'kaiko-child' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
					<input
						type="text"
						name="account_first_name"
						id="account_first_name"
						autocomplete="given-name"
						value="<?php echo esc_attr( $user->first_name ); ?>"
						required
					>
				</div>
				<div class="kaiko-form-field">
					<label for="account_last_name">
						<?php esc_html_e( 'Last name', 'kaiko-child' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
					<input
						type="text"
						name="account_last_name"
						id="account_last_name"
						autocomplete="family-name"
						value="<?php echo esc_attr( $user->last_name ); ?>"
						required
					>
				</div>
			</div>

			<div class="kaiko-form-row">
				<div class="kaiko-form-field">
					<label for="account_display_name"><?php esc_html_e( 'Display name', 'kaiko-child' ); ?></label>
					<input
						type="text"
						name="account_display_name"
						id="account_display_name"
						value="<?php echo esc_attr( $user->display_name ); ?>"
					>
				</div>
				<div class="kaiko-form-field">
					<label for="account_email">
						<?php esc_html_e( 'Email', 'kaiko-child' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
					<input
						type="email"
						name="account_email"
						id="account_email"
						autocomplete="email"
						value="<?php echo esc_attr( $user->user_email ); ?>"
						required
					>
				</div>
			</div>

			<div class="kaiko-form-divider" aria-hidden="true"></div>

			<!-- Business -->
			<h3 class="kaiko-form-section-title"><?php esc_html_e( 'Business', 'kaiko-child' ); ?></h3>

			<div class="kaiko-form-row kaiko-form-row--1">
				<div class="kaiko-form-field">
					<label for="billing_company"><?php esc_html_e( 'Company name', 'kaiko-child' ); ?></label>
					<input
						type="text"
						name="billing_company"
						id="billing_company"
						autocomplete="organization"
						value="<?php echo esc_attr( get_user_meta( $user->ID, 'billing_company', true ) ); ?>"
					>
				</div>
			</div>
			<?php
			/*
			 * TODO: VAT number field — separate ticket.
			 * No vat_number user_meta infrastructure exists in kaiko-child yet,
			 * and the billing_vat_number field isn't wired into checkout either.
			 * Leaving the markup commented so the section's structure is visible
			 * when we come back to it. See Tom's note in the Phase 2 brief.
			 *
			 * <div class="kaiko-form-field">
			 *     <label for="vat_number">VAT number</label>
			 *     <input type="text" name="vat_number" id="vat_number"
			 *            value="<?php echo esc_attr( get_user_meta( $user->ID, 'vat_number', true ) ); ?>">
			 * </div>
			 */
			?>

			<div class="kaiko-form-divider" aria-hidden="true"></div>

			<!-- Password -->
			<h3 class="kaiko-form-section-title"><?php esc_html_e( 'Password change', 'kaiko-child' ); ?></h3>
			<p style="font-size:13px;color:var(--k-stone-500);margin:-6px 0 14px;">
				<?php esc_html_e( 'Leave blank to keep your current password.', 'kaiko-child' ); ?>
			</p>

			<div class="kaiko-form-row kaiko-form-row--1">
				<div class="kaiko-form-field">
					<label for="password_current"><?php esc_html_e( 'Current password', 'kaiko-child' ); ?></label>
					<input
						type="password"
						name="password_current"
						id="password_current"
						autocomplete="current-password"
					>
				</div>
			</div>

			<div class="kaiko-form-row">
				<div class="kaiko-form-field">
					<label for="password_1"><?php esc_html_e( 'New password', 'kaiko-child' ); ?></label>
					<input
						type="password"
						name="password_1"
						id="password_1"
						autocomplete="new-password"
					>
				</div>
				<div class="kaiko-form-field">
					<label for="password_2"><?php esc_html_e( 'Confirm new password', 'kaiko-child' ); ?></label>
					<input
						type="password"
						name="password_2"
						id="password_2"
						autocomplete="new-password"
					>
				</div>
			</div>

			<?php do_action( 'woocommerce_edit_account_form' ); ?>

			<div class="kaiko-form-actions">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="kaiko-btn kaiko-btn--ghost">
					<?php esc_html_e( 'Cancel', 'kaiko-child' ); ?>
				</a>
				<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
				<button type="submit" class="kaiko-btn kaiko-btn--primary" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'kaiko-child' ); ?>">
					<?php esc_html_e( 'Save changes', 'kaiko-child' ); ?>
				</button>
				<input type="hidden" name="action" value="save_account_details">
			</div>

		</div>

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>

</section>
