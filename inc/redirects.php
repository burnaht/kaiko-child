<?php
/**
 * Legacy URL redirects.
 *
 * Ported from Code Snippet #19 (KAIKO Redirect Lookbook to Products).
 *
 * The brand renamed "lookbook" to "products"; this keeps any outstanding
 * external links, SEO crawlers, and old printed assets working by issuing
 * a 301 to the equivalent /products/... URL.
 *
 * Snippet #20 (a wp_footer <script> that client-side rewrote /lookbook/…
 * hrefs on every page) was intentionally NOT ported — this server-side
 * redirect already fixes incoming URLs in one hop and removes the need
 * to ship JS that loops every anchor on the page.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', 'kaiko_redirect_lookbook_to_products', 1 );

function kaiko_redirect_lookbook_to_products() {
    if ( empty( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }

    $request = wp_unslash( $_SERVER['REQUEST_URI'] );
    $path    = wp_parse_url( $request, PHP_URL_PATH );

    if ( ! is_string( $path ) ) {
        return;
    }

    // Only fire on /lookbook or /lookbook/... — not on strings that
    // merely contain "lookbook" elsewhere in the URL.
    if ( 0 !== strpos( $path, '/lookbook' ) ) {
        return;
    }

    $new_url = preg_replace( '#^/lookbook#', '/products', $request, 1 );
    wp_safe_redirect( home_url( $new_url ), 301 );
    exit;
}
