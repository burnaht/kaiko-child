<?php
/**
 * Kaiko — One-shot admin migration triggers.
 *
 * Adds Tools → Kaiko Migrations. Each registered migration in $script_map
 * renders as a row with a POST form + nonce + Run button. The previous
 * GET-query-string trigger (?kaiko_run_migration=…) was being intercepted
 * by 20i's WAF and bouncing admins to the login screen, so the surface is
 * now a standard Tools page using POST.
 *
 * Delete this file (and scripts/*) after the pending one-shots have been
 * run on production.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registry of available one-shot migrations. Add a new key to register
 * another migration on the Tools page.
 */
function kaiko_migrations_script_map() {
	return array(
		'create-water-bowl' => array(
			'file'        => KAIKO_DIR . '/scripts/create-water-bowl.php',
			'fn'          => 'kaiko_create_water_bowl_run',
			'title'       => 'Create Reptile Water and Food Bowl Rock product',
			'description' => 'Creates a new variable product with 15 variations, sideloads images, and mirrors ACF tier rows from Escape-Proof Dubia Roach Dish. Idempotent — safe to re-run.',
		),
	);
}

add_action( 'admin_menu', 'kaiko_migrations_register_menu' );

function kaiko_migrations_register_menu() {
	add_management_page(
		'Kaiko Migrations',
		'Kaiko Migrations',
		'manage_options',
		'kaiko-migrations',
		'kaiko_migrations_render_page'
	);
}

/**
 * Render the Tools → Kaiko Migrations page. Handles its own POST so the
 * run output can render above the table without a redirect round-trip.
 */
function kaiko_migrations_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions.', 'Kaiko Migrations', array( 'response' => 403 ) );
	}

	$script_map = kaiko_migrations_script_map();
	$run_log    = null;
	$run_slug   = '';
	$run_error  = '';

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['kaiko_migration_slug'] ) ) {
		$slug   = sanitize_key( wp_unslash( $_POST['kaiko_migration_slug'] ) );
		$action = 'kaiko_run_migration_' . $slug;
		$nonce  = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			$run_error = 'Nonce verification failed. Reload the page and try again.';
		} elseif ( ! isset( $script_map[ $slug ] ) ) {
			$run_error = 'Unknown migration: ' . $slug;
		} else {
			$cfg = $script_map[ $slug ];
			if ( ! is_readable( $cfg['file'] ) ) {
				$run_error = 'Migration script missing: ' . $cfg['file'];
			} else {
				require_once $cfg['file'];
				if ( ! function_exists( $cfg['fn'] ) ) {
					$run_error = 'Migration entry function missing: ' . $cfg['fn'];
				} else {
					@set_time_limit( 300 );
					$run_slug = $slug;
					$lines    = call_user_func( $cfg['fn'] );
					$run_log  = is_array( $lines ) ? $lines : array( 'No log returned.' );
				}
			}
		}
	}

	?>
	<div class="wrap">
		<h1>Kaiko Migrations</h1>
		<p>One-shot data migrations. Each one is idempotent — safe to re-run if you're not sure whether it completed.</p>

		<?php if ( $run_error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $run_error ); ?></p></div>
		<?php endif; ?>

		<?php if ( is_array( $run_log ) ) : ?>
			<h2>Output: <?php echo esc_html( $run_slug ); ?></h2>
			<pre style="background:#0f172a;color:#e2e8f0;padding:16px;border-radius:8px;white-space:pre-wrap;max-width:960px;"><?php echo esc_html( implode( "\n", $run_log ) ); ?></pre>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:960px;margin-top:16px;">
			<thead>
				<tr>
					<th style="width:30%;">Migration</th>
					<th>Description</th>
					<th style="width:120px;">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $script_map as $slug => $cfg ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $cfg['title'] ); ?></strong><br>
							<code><?php echo esc_html( $slug ); ?></code>
						</td>
						<td><?php echo esc_html( $cfg['description'] ); ?></td>
						<td>
							<form method="post" action="">
								<?php wp_nonce_field( 'kaiko_run_migration_' . $slug ); ?>
								<input type="hidden" name="kaiko_migration_slug" value="<?php echo esc_attr( $slug ); ?>">
								<button type="submit" class="button button-primary">Run</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php if ( empty( $script_map ) ) : ?>
					<tr><td colspan="3"><em>No migrations registered.</em></td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
