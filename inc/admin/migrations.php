<?php
/**
 * Kaiko — One-shot admin migration triggers.
 *
 * Visit (logged in as an admin):
 *   /wp-admin/?kaiko_run_migration=create-water-bowl&_wpnonce=…
 * to run scripts/create-water-bowl.php from the browser when SSH /
 * WP-CLI isn't available. The preferred path is still
 * `wp eval-file scripts/create-water-bowl.php`.
 *
 * Generate the trigger URL from a separate logged-in admin tab:
 *   wp_nonce_url( admin_url( '/?kaiko_run_migration=create-water-bowl' ),
 *                 'kaiko_run_migration_create-water-bowl' )
 *
 * Or quicker: load wp-admin, then visit
 *   /wp-admin/?kaiko_run_migration=create-water-bowl
 * once — when the nonce is missing the handler echoes a one-click link
 * with a freshly minted nonce so you can confirm and proceed.
 *
 * Delete this file (and scripts/create-water-bowl.php) after the run is
 * verified — both are one-shots.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'kaiko_run_migration_admin_handler' );

function kaiko_run_migration_admin_handler() {
	if ( empty( $_GET['kaiko_run_migration'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$slug   = sanitize_key( wp_unslash( $_GET['kaiko_run_migration'] ) );
	$action = 'kaiko_run_migration_' . $slug;
	$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

	// Missing/invalid nonce → render a confirmation page with a one-click
	// link that has a valid nonce attached. Stops accidental triggers and
	// gives the admin a chance to bail out.
	if ( ! $nonce || ! wp_verify_nonce( $nonce, $action ) ) {
		$confirm_url = wp_nonce_url(
			admin_url( '/?kaiko_run_migration=' . $slug ),
			$action
		);
		wp_die(
			sprintf(
				'<h1>Kaiko migration: %1$s</h1>
				 <p>This is a one-shot migration. Click below to run it.</p>
				 <p><a class="button button-primary" href="%2$s">Run %1$s</a></p>',
				esc_html( $slug ),
				esc_url( $confirm_url )
			),
			'Kaiko migration',
			array( 'response' => 200 )
		);
	}

	$script_map = array(
		'create-water-bowl' => array(
			'file' => KAIKO_DIR . '/scripts/create-water-bowl.php',
			'fn'   => 'kaiko_create_water_bowl_run',
		),
	);
	if ( ! isset( $script_map[ $slug ] ) ) {
		wp_die( 'Unknown migration: ' . esc_html( $slug ), 'Kaiko migration', array( 'response' => 404 ) );
	}

	$cfg = $script_map[ $slug ];
	if ( ! is_readable( $cfg['file'] ) ) {
		wp_die( 'Migration script missing: ' . esc_html( $cfg['file'] ), 'Kaiko migration', array( 'response' => 500 ) );
	}
	require_once $cfg['file'];

	if ( ! function_exists( $cfg['fn'] ) ) {
		wp_die( 'Migration entry function missing: ' . esc_html( $cfg['fn'] ), 'Kaiko migration', array( 'response' => 500 ) );
	}

	@set_time_limit( 300 );
	$lines = call_user_func( $cfg['fn'] );
	if ( ! is_array( $lines ) ) {
		$lines = array( 'No log returned.' );
	}

	$body = '<h1>Kaiko migration: ' . esc_html( $slug ) . '</h1><pre style="background:#0f172a;color:#e2e8f0;padding:16px;border-radius:8px;white-space:pre-wrap;">';
	$body .= esc_html( implode( "\n", $lines ) );
	$body .= '</pre><p><a href="' . esc_url( admin_url( 'edit.php?post_type=product' ) ) . '">Back to Products</a></p>';
	wp_die( $body, 'Kaiko migration', array( 'response' => 200 ) );
}
