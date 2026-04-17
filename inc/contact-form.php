<?php
/**
 * Kaiko — Native Contact Form AJAX Handler
 *
 * Powers the native-fallback form in template-contact.php when CF7 is not
 * configured. Registers wp-ajax endpoints, validates input, and sends via
 * wp_mail() (which uses the configured SMTP — info@kaikoproducts.com).
 *
 * Security: nonce, honeypot, per-IP rate limit, output escaping in email.
 *
 * @package KaikoChild
 * @since   3.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Recipient address for contact-form submissions. Override via the
 * `kaiko_contact_form_recipient` filter if you need to route elsewhere.
 */
if ( ! function_exists( 'kaiko_contact_form_recipient' ) ) {
	function kaiko_contact_form_recipient() {
		return apply_filters( 'kaiko_contact_form_recipient', 'info@kaikoproducts.com' );
	}
}

/**
 * Map the subject select value to a human-readable label.
 */
if ( ! function_exists( 'kaiko_contact_subject_label' ) ) {
	function kaiko_contact_subject_label( $value ) {
		$map = array(
			'general' => 'General Enquiry',
			'trade'   => 'Trade & Wholesale',
			'support' => 'Product Support',
			'returns' => 'Returns & Refunds',
			'press'   => 'Press & Media',
			'other'   => 'Other',
		);
		return isset( $map[ $value ] ) ? $map[ $value ] : 'General Enquiry';
	}
}

/**
 * Print the kaikoContact JS object (ajax URL + nonce) to the footer on
 * the contact page template only. Used by the submit handler in
 * template-contact.php.
 */
add_action( 'wp_footer', 'kaiko_contact_form_print_js_config', 5 );
function kaiko_contact_form_print_js_config() {
	if ( ! is_page_template( 'template-contact.php' ) && ! is_page( 'contact' ) ) {
		return;
	}
	$config = array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'kaiko_contact_submit' ),
	);
	echo '<script id="kaiko-contact-config">window.kaikoContact = ' . wp_json_encode( $config ) . ';</script>' . "\n";
}

/**
 * AJAX handler — processes the contact form submission.
 * Hooked to both `nopriv` (anonymous visitors, the normal case) and the
 * logged-in action so logged-in customers can also send enquiries.
 */
add_action( 'wp_ajax_nopriv_kaiko_contact_submit', 'kaiko_contact_submit_handler' );
add_action( 'wp_ajax_kaiko_contact_submit', 'kaiko_contact_submit_handler' );

function kaiko_contact_submit_handler() {

	// --- Nonce check --------------------------------------------------------
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'kaiko_contact_submit' ) ) {
		wp_send_json_error(
			array( 'message' => 'Security check failed — please refresh the page and try again.' ),
			403
		);
	}

	// --- Honeypot -----------------------------------------------------------
	// If the hidden `website_url` field is filled, it's a bot. Pretend success
	// (don't tip off the spammer) but don't actually send anything.
	if ( ! empty( $_POST['website_url'] ) ) {
		wp_send_json_success( array( 'message' => 'Message sent — we\'ll get back to you within 24 hours.' ) );
	}

	// --- Rate limit: 3 submissions per IP per hour -------------------------
	$ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$rate_key  = 'kaiko_contact_rl_' . md5( $ip );
	$rate_used = (int) get_transient( $rate_key );
	if ( $rate_used >= 3 ) {
		wp_send_json_error(
			array( 'message' => 'Too many messages from your connection. Please try again in an hour, or email info@kaikoproducts.com directly.' ),
			429
		);
	}

	// --- Collect + sanitize input ------------------------------------------
	$first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$subject    = isset( $_POST['subject'] ) ? sanitize_key( wp_unslash( $_POST['subject'] ) ) : '';
	$message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	// --- Validation --------------------------------------------------------
	$errors = array();

	if ( '' === $first_name || mb_strlen( $first_name ) > 80 ) {
		$errors[] = 'Please enter your first name.';
	}
	if ( '' === $last_name || mb_strlen( $last_name ) > 80 ) {
		$errors[] = 'Please enter your last name.';
	}
	if ( '' === $email || ! is_email( $email ) ) {
		$errors[] = 'Please enter a valid email address.';
	}
	if ( '' === $subject ) {
		$errors[] = 'Please choose a subject.';
	}
	$msg_len = mb_strlen( $message );
	if ( $msg_len < 10 ) {
		$errors[] = 'Your message is too short (minimum 10 characters).';
	}
	if ( $msg_len > 5000 ) {
		$errors[] = 'Your message is too long (maximum 5,000 characters).';
	}

	if ( ! empty( $errors ) ) {
		wp_send_json_error(
			array(
				'message' => implode( ' ', $errors ),
			),
			400
		);
	}

	// --- Build the email ---------------------------------------------------
	$subject_label = kaiko_contact_subject_label( $subject );
	$full_name     = trim( $first_name . ' ' . $last_name );
	$site_name     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$to            = kaiko_contact_form_recipient();

	$mail_subject = sprintf( '[Kaiko Contact] %s — %s', $subject_label, $full_name );

	$body  = '<html><body style="font-family:-apple-system,BlinkMacSystemFont,Helvetica,Arial,sans-serif;color:#1a1917;line-height:1.5;">';
	$body .= '<h2 style="color:#134840;margin:0 0 16px;font-size:18px;">New contact form submission</h2>';
	$body .= '<table cellpadding="8" cellspacing="0" border="0" style="border-collapse:collapse;font-size:14px;">';
	$body .= '<tr><td style="color:#8a867a;width:120px;"><strong>From</strong></td><td>' . esc_html( $full_name ) . '</td></tr>';
	$body .= '<tr><td style="color:#8a867a;"><strong>Email</strong></td><td><a href="mailto:' . esc_attr( $email ) . '" style="color:#134840;">' . esc_html( $email ) . '</a></td></tr>';
	$body .= '<tr><td style="color:#8a867a;"><strong>Subject</strong></td><td>' . esc_html( $subject_label ) . '</td></tr>';
	$body .= '<tr><td style="color:#8a867a;vertical-align:top;"><strong>Message</strong></td><td style="white-space:pre-wrap;">' . esc_html( $message ) . '</td></tr>';
	$body .= '</table>';
	$body .= '<hr style="border:none;border-top:1px solid #e5e3db;margin:20px 0;">';
	$body .= '<p style="color:#8a867a;font-size:12px;margin:0;">Sent via kaikoproducts.com/contact/ at ' . esc_html( current_time( 'd M Y, H:i' ) ) . ' — IP ' . esc_html( $ip ) . '</p>';
	$body .= '</body></html>';

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $site_name . ' <' . $to . '>',
		'Reply-To: ' . $full_name . ' <' . $email . '>',
	);

	$sent = wp_mail( $to, $mail_subject, $body, $headers );

	if ( ! $sent ) {
		wp_send_json_error(
			array(
				'message' => 'Something went wrong sending your message. Please email info@kaikoproducts.com directly.',
			),
			500
		);
	}

	// --- Success: bump rate-limit counter ----------------------------------
	set_transient( $rate_key, $rate_used + 1, HOUR_IN_SECONDS );

	wp_send_json_success(
		array(
			'message' => 'Message sent — we\'ll get back to you within 24 hours.',
		)
	);
}
