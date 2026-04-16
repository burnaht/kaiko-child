<?php
/**
 * Kaiko — Security Hardening
 *
 * Disables unnecessary features, adds security headers,
 * limits login attempts, and hardens WordPress.
 *
 * @package KaikoChild
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin compatibility check.
 * Wordfence handles login limiting and some security headers natively.
 * Defer to it when active to avoid conflicts.
 */
function kaiko_is_wordfence_active() {
    return defined( 'WORDFENCE_VERSION' ) || class_exists( 'wordfence' );
}

/* ============================================
   1. DISABLE XML-RPC
   ============================================ */

add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove XML-RPC discovery from wp_head.
 */
add_action( 'wp', 'kaiko_remove_xmlrpc_headers' );

function kaiko_remove_xmlrpc_headers() {
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
}

/**
 * Block XML-RPC requests entirely via htaccess-level header.
 */
add_filter( 'xmlrpc_methods', 'kaiko_disable_xmlrpc_methods' );

function kaiko_disable_xmlrpc_methods( $methods ) {
    return array();
}


/* ============================================
   2. REMOVE WORDPRESS VERSION
   ============================================ */

/**
 * Remove version from head and RSS feeds.
 */
add_filter( 'the_generator', '__return_empty_string' );

remove_action( 'wp_head', 'wp_generator' );

/**
 * Remove version from script and style tags.
 */
add_filter( 'style_loader_src', 'kaiko_remove_wp_version_strings', 9999 );
add_filter( 'script_loader_src', 'kaiko_remove_wp_version_strings', 9999 );

function kaiko_remove_wp_version_strings( $src ) {
    if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}


/* ============================================
   3. DISABLE FILE EDITING IN ADMIN
   ============================================ */

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}


/* ============================================
   4. SECURITY HEADERS
   ============================================ */

add_action( 'send_headers', 'kaiko_security_headers' );

function kaiko_security_headers() {
    if ( is_admin() ) {
        return;
    }

    $headers = apply_filters( 'kaiko_security_headers', array(
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options'        => 'SAMEORIGIN',
        'Referrer-Policy'        => 'strict-origin-when-cross-origin',
        'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(self), payment=(self)',
        'X-XSS-Protection'      => '1; mode=block',
    ) );

    foreach ( $headers as $name => $value ) {
        if ( ! headers_sent() ) {
            header( sprintf( '%s: %s', $name, $value ) );
        }
    }
}


/* ============================================
   5. LIMIT LOGIN ATTEMPTS
   ============================================ */

/**
 * Simple transient-based login attempt limiter.
 * Locks out an IP after 5 failed attempts for 15 minutes.
 */
// Only use our login limiter if Wordfence is NOT active (it has its own brute force protection)
if ( ! kaiko_is_wordfence_active() ) {
    add_filter( 'authenticate', 'kaiko_check_login_attempts', 30, 3 );
    add_action( 'wp_login_failed', 'kaiko_login_failed' );
    add_action( 'wp_login', 'kaiko_login_success', 10, 2 );
    add_filter( 'login_message', 'kaiko_login_security_message' );
}

function kaiko_check_login_attempts( $user, $username, $password ) {
    if ( empty( $username ) ) {
        return $user;
    }

    $ip          = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
    $key         = 'kaiko_login_' . md5( $ip );
    $max_attempts = apply_filters( 'kaiko_max_login_attempts', 5 );
    $lockout_time = apply_filters( 'kaiko_login_lockout_time', 15 * MINUTE_IN_SECONDS );
    $attempts    = (int) get_transient( $key );

    if ( $attempts >= $max_attempts ) {
        return new WP_Error(
            'kaiko_too_many_attempts',
            sprintf(
                /* translators: %d: lockout duration in minutes */
                __( '<strong>Error:</strong> Too many failed login attempts. Please try again in %d minutes.', 'kaiko-child' ),
                ceil( $lockout_time / MINUTE_IN_SECONDS )
            )
        );
    }

    return $user;
}

/**
 * Increment failed login counter.
 */
function kaiko_login_failed( $username ) {
    $ip          = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
    $key         = 'kaiko_login_' . md5( $ip );
    $lockout_time = apply_filters( 'kaiko_login_lockout_time', 15 * MINUTE_IN_SECONDS );
    $attempts    = (int) get_transient( $key );

    set_transient( $key, $attempts + 1, $lockout_time );
}

/**
 * Reset login counter on successful login.
 */
function kaiko_login_success( $username, $user ) {
    $ip  = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
    $key = 'kaiko_login_' . md5( $ip );
    delete_transient( $key );
}


/* ============================================
   6. DISABLE REST API USER ENUMERATION
   ============================================ */

/**
 * Prevent non-authenticated users from listing users via REST API.
 */
add_filter( 'rest_endpoints', 'kaiko_disable_user_endpoints' );

function kaiko_disable_user_endpoints( $endpoints ) {
    if ( ! is_user_logged_in() || ! current_user_can( 'list_users' ) ) {
        if ( isset( $endpoints['/wp/v2/users'] ) ) {
            unset( $endpoints['/wp/v2/users'] );
        }
        if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
            unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        }
    }
    return $endpoints;
}


/* ============================================
   7. HIDE AUTHOR ARCHIVES
   ============================================ */

/**
 * Redirect author archive pages to homepage to prevent username discovery.
 */
add_action( 'template_redirect', 'kaiko_disable_author_archives' );

function kaiko_disable_author_archives() {
    if ( is_author() ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
}

/**
 * Remove author name from RSS feeds.
 */
add_filter( 'the_author', 'kaiko_hide_author_name_in_feeds' );

function kaiko_hide_author_name_in_feeds( $author ) {
    if ( is_feed() ) {
        return get_bloginfo( 'name' );
    }
    return $author;
}


/* ============================================
   8. ADDITIONAL HARDENING
   ============================================ */

/**
 * Remove unnecessary head links.
 */
add_action( 'init', 'kaiko_cleanup_head' );

function kaiko_cleanup_head() {
    // Remove shortlink
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
    remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

    // Remove REST API link from head
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

    // Remove oEmbed discovery
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

    // Remove feed links if not using blog
    if ( apply_filters( 'kaiko_remove_feed_links', false ) ) {
        remove_action( 'wp_head', 'feed_links', 2 );
        remove_action( 'wp_head', 'feed_links_extra', 3 );
    }
}

/**
 * Disable application passwords if not needed.
 */
add_filter( 'wp_is_application_passwords_available', function() {
    return apply_filters( 'kaiko_enable_application_passwords', false );
} );

/**
 * Add security notice to login page.
 */
function kaiko_login_security_message( $message ) {
    $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
    $key = 'kaiko_login_' . md5( $ip );
    $attempts = (int) get_transient( $key );
    $max = apply_filters( 'kaiko_max_login_attempts', 5 );

    if ( $attempts > 0 && $attempts < $max ) {
        $remaining = $max - $attempts;
        $message .= '<div class="message" style="border-left-color:#f59e0b;"><strong>' . esc_html( sprintf( '%d login attempt(s) remaining before lockout.', $remaining ) ) . '</strong></div>';
    }

    return $message;
}
