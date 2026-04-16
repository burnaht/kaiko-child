<?php
/**
 * Kaiko — Elementor Asset Enqueuing
 *
 * Conditionally loads Elementor-page CSS and JS only when needed.
 * Included from functions.php.
 *
 * @package KaikoChild
 * @since   2.1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'kaiko_enqueue_elementor_assets', 25 );

/**
 * Enqueue Elementor-specific CSS and JS.
 *
 * Loads on pages built with Elementor, or pages that use Kaiko shortcodes.
 * Priority 25 ensures it loads after the main design system (priority 20).
 */
function kaiko_enqueue_elementor_assets() {

    // Check if current page uses Elementor
    $is_elementor_page = false;

    if ( function_exists( 'elementor_theme_do_location' ) || class_exists( '\Elementor\Plugin' ) ) {
        $post_id = get_the_ID();
        if ( $post_id && \Elementor\Plugin::$instance->documents->get( $post_id ) ) {
            $document = \Elementor\Plugin::$instance->documents->get( $post_id );
            if ( $document && $document->is_built_with_elementor() ) {
                $is_elementor_page = true;
            }
        }
    }

    // Also load on pages with Kaiko shortcodes (homepage, about, etc.)
    $kaiko_pages = array( 'kaiko-page-home', 'kaiko-page-about', 'kaiko-page-contact' );
    $body_classes = get_body_class();
    foreach ( $kaiko_pages as $page_class ) {
        if ( in_array( $page_class, $body_classes, true ) ) {
            $is_elementor_page = true;
            break;
        }
    }

    // Check post content for Kaiko shortcodes as fallback
    if ( ! $is_elementor_page ) {
        global $post;
        if ( $post && is_a( $post, 'WP_Post' ) ) {
            $kaiko_shortcodes = array(
                'kaiko_species_grid',
                'kaiko_featured_products',
                'kaiko_newsletter_form',
                'kaiko_brand_stats',
                'kaiko_testimonial_slider',

                'kaiko_value_props',
            );
            foreach ( $kaiko_shortcodes as $shortcode ) {
                if ( has_shortcode( $post->post_content, $shortcode ) ) {
                    $is_elementor_page = true;
                    break;
                }
            }
        }
    }

    if ( ! $is_elementor_page ) {
        return;
    }

    // Elementor page CSS
    wp_enqueue_style(
        'kaiko-elementor',
        KAIKO_URI . '/assets/css/kaiko-elementor.css',
        array( 'kaiko-design-system' ),
        KAIKO_VERSION
    );

    // Elementor page JS
    wp_enqueue_script(
        'kaiko-elementor-js',
        KAIKO_URI . '/assets/js/kaiko-elementor.js',
        array(), // No jQuery dependency — vanilla JS
        KAIKO_VERSION,
        true // Load in footer
    );

    // Pass data to Elementor JS
    wp_localize_script( 'kaiko-elementor-js', 'kaikoElementor', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'kaiko_nonce' ),
        'themeUri' => KAIKO_URI,
    ) );
}


/**
 * Add body classes for Kaiko page types.
 *
 * Allows Elementor pages to get the correct body class for targeted styling.
 * Set the page slug or a custom field to trigger the appropriate class.
 */
add_filter( 'body_class', 'kaiko_add_page_body_classes' );

function kaiko_add_page_body_classes( $classes ) {
    if ( ! is_page() ) {
        return $classes;
    }

    $slug = get_post_field( 'post_name', get_post() );
    $page_map = array(
        'home'        => 'kaiko-page-home',
        'front-page'  => 'kaiko-page-home',
        'about'       => 'kaiko-page-about',
        'about-us'    => 'kaiko-page-about',
        'our-story'   => 'kaiko-page-about',

        'contact'     => 'kaiko-page-contact',
        'contact-us'  => 'kaiko-page-contact',
    );

    if ( isset( $page_map[ $slug ] ) ) {
        $classes[] = $page_map[ $slug ];
    }

    // Also check if it's the front page
    if ( is_front_page() ) {
        $classes[] = 'kaiko-page-home';
    }

    // Category landing pages
    if ( is_product_category() ) {
        $classes[] = 'kaiko-page-category';
        $term = get_queried_object();
        if ( $term ) {
            $classes[] = 'kaiko-page-category--' . $term->slug;
        }
    }

    return $classes;
}
