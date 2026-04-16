<?php
/**
 * Kaiko — Performance Optimisation
 *
 * Image optimisation, critical CSS, script management, resource hints,
 * and WooCommerce performance tuning.
 *
 * @package KaikoChild
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ============================================
   1. IMAGE OPTIMISATION
   ============================================ */

/**
 * Add native lazy loading to all images.
 * WordPress 5.5+ adds loading="lazy" by default for content images,
 * but this ensures coverage on theme images too.
 */
add_filter( 'wp_get_attachment_image_attributes', 'kaiko_lazy_load_images', 10, 3 );

function kaiko_lazy_load_images( $attr, $attachment, $size ) {
    // Don't lazy-load above-the-fold images (hero, logo)
    $skip_classes = apply_filters( 'kaiko_skip_lazy_classes', array( 'kaiko-hero-img', 'custom-logo', 'kaiko-no-lazy' ) );

    $classes = $attr['class'] ?? '';
    foreach ( $skip_classes as $skip ) {
        if ( strpos( $classes, $skip ) !== false ) {
            $attr['loading'] = 'eager';
            $attr['fetchpriority'] = 'high';
            return $attr;
        }
    }

    if ( ! isset( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }

    // Add decoding attribute for non-critical images
    if ( ! isset( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }

    return $attr;
}

/**
 * WebP detection and serving helper.
 * Checks if a .webp version exists alongside the original and serves it.
 */
add_filter( 'wp_get_attachment_url', 'kaiko_maybe_serve_webp', 10, 2 );

function kaiko_maybe_serve_webp( $url, $attachment_id ) {
    if ( ! apply_filters( 'kaiko_serve_webp', true ) ) {
        return $url;
    }

    // Cache browser WebP support check per request (avoid repeated string operations)
    static $supports_webp = null;
    if ( null === $supports_webp ) {
        $supports_webp = ! empty( $_SERVER['HTTP_ACCEPT'] ) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false;
    }
    if ( ! $supports_webp ) {
        return $url;
    }

    // Check for WebP version
    $file = get_attached_file( $attachment_id );
    if ( ! $file ) {
        return $url;
    }

    $webp_file = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );
    if ( file_exists( $webp_file ) ) {
        return preg_replace( '/\.(jpe?g|png)$/i', '.webp', $url );
    }

    return $url;
}

/**
 * Responsive srcset helper for theme images (non-attachment).
 *
 * Usage: echo kaiko_responsive_image( $image_url, 'Hero image', array(400, 800, 1200, 1920) );
 *
 * @param string $url      Base image URL.
 * @param string $alt      Alt text.
 * @param array  $widths   Array of widths for srcset.
 * @param string $sizes    Sizes attribute.
 * @param string $classes  CSS classes.
 * @return string HTML img tag.
 */
function kaiko_responsive_image( $url, $alt = '', $widths = array(), $sizes = '100vw', $classes = '' ) {
    $srcset_parts = array();

    foreach ( $widths as $w ) {
        $resized_url = add_query_arg( 'w', $w, $url );
        $srcset_parts[] = esc_url( $resized_url ) . ' ' . (int) $w . 'w';
    }

    $srcset = implode( ', ', $srcset_parts );

    return sprintf(
        '<img src="%s" srcset="%s" sizes="%s" alt="%s" class="%s" loading="lazy" decoding="async" />',
        esc_url( $url ),
        $srcset,
        esc_attr( $sizes ),
        esc_attr( $alt ),
        esc_attr( $classes )
    );
}


/* ============================================
   2. CRITICAL CSS
   ============================================ */

/**
 * Inline critical CSS for above-the-fold rendering on key pages.
 */
add_action( 'wp_head', 'kaiko_inline_critical_css', 1 );

function kaiko_inline_critical_css() {
    if ( ! apply_filters( 'kaiko_enable_critical_css', true ) ) {
        return;
    }

    // Only on front page and key landing pages
    $pages = apply_filters( 'kaiko_critical_css_pages', array(
        'front_page' => is_front_page(),
        'shop'       => function_exists( 'is_shop' ) && is_shop(),
    ) );

    $should_inline = false;
    foreach ( $pages as $check ) {
        if ( $check ) {
            $should_inline = true;
            break;
        }
    }

    if ( ! $should_inline ) {
        return;
    }

    // Cache critical CSS in transient to avoid file_get_contents on every page load
    $css = get_transient( 'kaiko_critical_css' );
    if ( false === $css ) {
        $critical_css_path = KAIKO_DIR . '/assets/css/kaiko-critical.css';
        if ( file_exists( $critical_css_path ) ) {
            $css = file_get_contents( $critical_css_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
            $css = wp_strip_all_tags( $css );
            set_transient( 'kaiko_critical_css', $css, DAY_IN_SECONDS );
        }
    }
    if ( ! empty( $css ) ) {
        echo '<style id="kaiko-critical-css">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- pre-sanitised CSS
    }
}


/* ============================================
   3. SCRIPT OPTIMISATION
   ============================================ */

/**
 * Defer non-critical JavaScript.
 */
add_filter( 'script_loader_tag', 'kaiko_defer_scripts', 10, 3 );

function kaiko_defer_scripts( $tag, $handle, $src ) {
    // Scripts that should NOT be deferred
    $no_defer = apply_filters( 'kaiko_no_defer_scripts', array(
        'jquery',
        'jquery-core',
        'jquery-migrate',
        'wp-polyfill',
        'kaiko-critical-js',
    ) );

    if ( in_array( $handle, $no_defer, true ) ) {
        return $tag;
    }

    // Don't double-add defer
    if ( strpos( $tag, 'defer' ) !== false ) {
        return $tag;
    }

    // Add defer attribute
    return str_replace( ' src=', ' defer src=', $tag );
}

/**
 * Remove unused WooCommerce scripts on non-shop pages.
 */
add_action( 'wp_enqueue_scripts', 'kaiko_remove_unused_wc_scripts', 99 );

function kaiko_remove_unused_wc_scripts() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // On non-WooCommerce pages, remove WC scripts
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );
        wp_dequeue_script( 'wc-add-to-cart' );
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-add-to-cart-variation' );

        // Also remove Select2 if loaded by WooCommerce
        wp_dequeue_style( 'select2' );
        wp_dequeue_script( 'select2' );
        wp_dequeue_script( 'selectWoo' );
    }
}


/* ============================================
   4. RESOURCE HINTS & PRELOADING
   ============================================ */

/**
 * Add preconnect, DNS prefetch, and preload hints.
 */
add_action( 'wp_head', 'kaiko_resource_hints', 2 );

function kaiko_resource_hints() {
    $preconnects = apply_filters( 'kaiko_preconnect_origins', array(
        'https://fonts.googleapis.com',
        'https://fonts.gstatic.com',
    ) );

    foreach ( $preconnects as $origin ) {
        echo '<link rel="preconnect" href="' . esc_url( $origin ) . '" crossorigin />' . "\n";
    }

    $dns_prefetch = apply_filters( 'kaiko_dns_prefetch_origins', array(
        'https://www.google-analytics.com',
        'https://www.googletagmanager.com',
        'https://cdnjs.cloudflare.com',
    ) );

    foreach ( $dns_prefetch as $origin ) {
        echo '<link rel="dns-prefetch" href="' . esc_url( $origin ) . '" />' . "\n";
    }
}

/**
 * Preload key fonts.
 */
add_action( 'wp_head', 'kaiko_preload_fonts', 3 );

function kaiko_preload_fonts() {
    $fonts = apply_filters( 'kaiko_preload_fonts', array() );

    foreach ( $fonts as $font ) {
        echo '<link rel="preload" href="' . esc_url( $font['url'] ) . '" as="font" type="' . esc_attr( $font['type'] ?? 'font/woff2' ) . '" crossorigin />' . "\n";
    }
}

/**
 * Preload hero image on front page.
 */
add_action( 'wp_head', 'kaiko_preload_hero', 4 );

function kaiko_preload_hero() {
    if ( ! is_front_page() ) {
        return;
    }

    $hero_image = apply_filters( 'kaiko_hero_image_url', '' );
    if ( ! empty( $hero_image ) ) {
        echo '<link rel="preload" href="' . esc_url( $hero_image ) . '" as="image" fetchpriority="high" />' . "\n";
    }
}


/* ============================================
   5. DISABLE UNNECESSARY WP FEATURES
   ============================================ */

/**
 * Disable WordPress emoji scripts and styles.
 */
add_action( 'init', 'kaiko_disable_emojis' );

function kaiko_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

    // Remove TinyMCE emoji plugin
    add_filter( 'tiny_mce_plugins', function( $plugins ) {
        return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : $plugins;
    } );

    // Remove emoji DNS prefetch
    add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
        if ( 'dns-prefetch' === $relation_type ) {
            $urls = array_filter( $urls, function( $url ) {
                return strpos( $url, 'https://s.w.org/images/core/emoji' ) === false;
            } );
        }
        return $urls;
    }, 10, 2 );
}

/**
 * Disable jQuery Migrate if not needed.
 */
add_action( 'wp_default_scripts', 'kaiko_disable_jquery_migrate' );

function kaiko_disable_jquery_migrate( $scripts ) {
    if ( ! is_admin() && apply_filters( 'kaiko_disable_jquery_migrate', true ) ) {
        if ( isset( $scripts->registered['jquery'] ) ) {
            $deps = $scripts->registered['jquery']->deps;
            $scripts->registered['jquery']->deps = array_diff( $deps, array( 'jquery-migrate' ) );
        }
    }
}

/**
 * Remove query strings from static resources.
 */
add_filter( 'style_loader_src', 'kaiko_remove_query_strings', 10, 2 );
add_filter( 'script_loader_src', 'kaiko_remove_query_strings', 10, 2 );

function kaiko_remove_query_strings( $src, $handle ) {
    if ( ! apply_filters( 'kaiko_remove_query_strings', true ) ) {
        return $src;
    }

    // Keep query strings for admin and customizer
    if ( is_admin() || is_customize_preview() ) {
        return $src;
    }

    // Remove version query string
    if ( strpos( $src, '?ver=' ) !== false ) {
        $src = remove_query_arg( 'ver', $src );
    }

    return $src;
}


/* ============================================
   6. WOOCOMMERCE PERFORMANCE
   ============================================ */

/**
 * Disable WooCommerce cart fragments on non-cart pages.
 * Cart fragments cause an AJAX request on every page load.
 */
add_action( 'wp_enqueue_scripts', 'kaiko_disable_cart_fragments', 99 );

function kaiko_disable_cart_fragments() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    if ( apply_filters( 'kaiko_disable_cart_fragments', true ) ) {
        // Only load cart fragments on cart, checkout, and product pages
        if ( ! is_cart() && ! is_checkout() && ! is_product() && ! is_shop() ) {
            wp_dequeue_script( 'wc-cart-fragments' );
        }
    }
}

/**
 * Limit WooCommerce widget overhead.
 * Disable unnecessary WooCommerce widgets from loading.
 */
add_action( 'widgets_init', 'kaiko_limit_wc_widgets', 20 );

function kaiko_limit_wc_widgets() {
    if ( ! apply_filters( 'kaiko_limit_wc_widgets', true ) ) {
        return;
    }

    // Unregister less-used WooCommerce widgets
    $remove_widgets = apply_filters( 'kaiko_remove_wc_widgets', array(
        'WC_Widget_Recent_Reviews',
        'WC_Widget_Recently_Viewed',
        'WC_Widget_Top_Rated_Products',
    ) );

    foreach ( $remove_widgets as $widget ) {
        if ( class_exists( $widget ) ) {
            unregister_widget( $widget );
        }
    }
}


/* ============================================
   7. DATABASE QUERY OPTIMISATION
   ============================================ */

/**
 * Disable heartbeat on front-end (only keep in admin post editor).
 */
add_action( 'init', 'kaiko_manage_heartbeat', 1 );

function kaiko_manage_heartbeat() {
    if ( ! is_admin() ) {
        wp_deregister_script( 'heartbeat' );
    }
}

/**
 * Limit post revisions to reduce database bloat.
 */
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
    define( 'WP_POST_REVISIONS', 5 );
}
