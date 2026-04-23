<?php
/**
 * Trade Approval — login gate + admin pending badge.
 *
 * Ported from Code Snippet #12 (KAIKO Trade Account Approval System).
 *
 * The theme's functions.php already owns the bulk of the trade-approval
 * workflow — role registration (kaiko_pending / kaiko_approved), new-user
 * role assignment, admin notification email, applicant pending email,
 * approval/rejection emails, registration redirect, force-logout safety
 * net, checkout redirect, MyAccount dashboard notice, bulk approve, user
 * columns, and the pending-users dashboard widget.
 *
 * Two pieces were missing from the theme and are added here:
 *
 *   1. authenticate filter — blocks login for kaiko_pending users. Without
 *      this, a pending user who knows their password can still sign in via
 *      /my-account/; the force-logout hook only runs at registration. This
 *      closes that gap with a branded error message.
 *
 *   2. Admin menu pending count — adds the "awaiting moderation" bubble
 *      next to the Users menu so admins notice new trade applications at
 *      a glance.
 *
 * The snippet was also using the role name "pending_approval" — the theme
 * uses "kaiko_pending". This file uses the theme's role name so there's
 * one source of truth and no parallel role to reconcile.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;


/**
 * Block login attempts by pending trade users.
 *
 * Runs at priority 40 so it executes after WordPress's own credential
 * checks (priorities 20 & 30) — we only want to intervene once the user
 * has otherwise been authenticated.
 *
 * @param WP_User|WP_Error|null $user     User object, error, or null.
 * @param string                $username Submitted username (unused).
 * @param string                $password Submitted password (unused).
 * @return WP_User|WP_Error
 */
add_filter( 'authenticate', 'kaiko_block_pending_login', 40, 3 );

function kaiko_block_pending_login( $user, $username, $password ) {
    // Let earlier filters' errors pass through unchanged.
    if ( is_wp_error( $user ) || ! $user ) {
        return $user;
    }

    if ( in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
        return new WP_Error(
            'kaiko_pending_approval',
            __( 'Your Kaiko trade account is awaiting approval. You\'ll receive an email once it\'s active.', 'kaiko' )
        );
    }

    return $user;
}


/**
 * Show a "pending approvals" count bubble next to the Users admin menu.
 *
 * Only queries when an admin is on an admin page — `admin_menu` only
 * fires in admin, so the get_users() call is inherently scoped there.
 */
add_action( 'admin_menu', 'kaiko_add_pending_users_badge', 999 );

function kaiko_add_pending_users_badge() {
    if ( ! current_user_can( 'list_users' ) ) {
        return;
    }

    $pending = get_users(
        array(
            'role'   => 'kaiko_pending',
            'fields' => 'ID',
            'number' => 50, // cap the query — we only need the count for the badge
        )
    );
    $count = count( $pending );

    if ( $count < 1 ) {
        return;
    }

    global $menu;
    if ( ! is_array( $menu ) ) {
        return;
    }

    foreach ( $menu as $index => $item ) {
        if ( isset( $item[2] ) && 'users.php' === $item[2] ) {
            $menu[ $index ][0] .= sprintf(
                ' <span class="awaiting-mod"><span class="pending-count">%d</span></span>',
                (int) $count
            );
            break;
        }
    }
}
