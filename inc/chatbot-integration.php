<?php
/**
 * Kaiko — Chatbot & n8n Integration
 *
 * Custom REST API endpoints for chatbot/n8n integration.
 * Provides product search, species info, care guides, and stock alerts.
 *
 * @package KaikoChild
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ============================================
   RATE LIMITING
   ============================================ */

/**
 * Simple transient-based rate limiter.
 * 60 requests per minute per IP.
 *
 * @return bool|WP_Error True if allowed, WP_Error if rate limited.
 */
function kaiko_api_rate_check() {
    $ip         = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
    $key        = 'kaiko_rate_' . md5( $ip );
    $limit      = apply_filters( 'kaiko_api_rate_limit', 60 );
    $window     = apply_filters( 'kaiko_api_rate_window', 60 ); // seconds
    $current    = (int) get_transient( $key );

    if ( $current >= $limit ) {
        return new WP_Error(
            'kaiko_rate_limited',
            __( 'Rate limit exceeded. Please wait before making more requests.', 'kaiko-child' ),
            array( 'status' => 429 )
        );
    }

    set_transient( $key, $current + 1, $window );
    return true;
}

/* ============================================
   API KEY AUTHENTICATION
   ============================================ */

/**
 * Validate the API key from X-Kaiko-Key header.
 *
 * API keys are stored as a comma-separated option: kaiko_api_keys.
 * Set via WP Admin > Settings or wp-config.php constant KAIKO_API_KEY.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return bool|WP_Error True if valid, WP_Error if not.
 */
function kaiko_api_authenticate( $request ) {
    $provided_key = $request->get_header( 'X-Kaiko-Key' );

    if ( empty( $provided_key ) ) {
        return new WP_Error(
            'kaiko_missing_key',
            __( 'API key required. Include X-Kaiko-Key header.', 'kaiko-child' ),
            array( 'status' => 401 )
        );
    }

    $provided_key = sanitize_text_field( $provided_key );

    // Check wp-config constant first
    if ( defined( 'KAIKO_API_KEY' ) && hash_equals( KAIKO_API_KEY, $provided_key ) ) {
        return true;
    }

    // Check stored keys option
    $stored_keys = get_option( 'kaiko_api_keys', '' );
    if ( ! empty( $stored_keys ) ) {
        $keys = array_map( 'trim', explode( ',', $stored_keys ) );
        foreach ( $keys as $key ) {
            if ( hash_equals( $key, $provided_key ) ) {
                return true;
            }
        }
    }

    return new WP_Error(
        'kaiko_invalid_key',
        __( 'Invalid API key.', 'kaiko-child' ),
        array( 'status' => 403 )
    );
}

/**
 * Combined permission check: rate limit + API key.
 *
 * @param WP_REST_Request $request The incoming request.
 * @return bool|WP_Error
 */
function kaiko_api_permission_check( $request ) {
    $rate = kaiko_api_rate_check();
    if ( is_wp_error( $rate ) ) {
        return $rate;
    }

    return kaiko_api_authenticate( $request );
}

/* ============================================
   REGISTER REST ROUTES
   ============================================ */

add_action( 'rest_api_init', 'kaiko_register_api_routes' );

function kaiko_register_api_routes() {

    $namespace = 'kaiko/v1';

    // GET /wp-json/kaiko/v1/products
    register_rest_route( $namespace, '/products', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'kaiko_api_get_products',
        'permission_callback' => 'kaiko_api_permission_check',
        'args'                => array(
            'search'     => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            'category'   => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            'species'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            'difficulty' => array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'enum'              => array( 'beginner', 'intermediate', 'advanced' ),
            ),
            'per_page'   => array( 'type' => 'integer', 'default' => 12, 'minimum' => 1, 'maximum' => 50 ),
            'page'       => array( 'type' => 'integer', 'default' => 1, 'minimum' => 1 ),
            'orderby'    => array(
                'type'    => 'string',
                'default' => 'date',
                'enum'    => array( 'date', 'title', 'price', 'price-desc', 'popularity' ),
            ),
        ),
    ) );

    // GET /wp-json/kaiko/v1/product/{id}
    register_rest_route( $namespace, '/product/(?P<id>\d+)', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'kaiko_api_get_product',
        'permission_callback' => 'kaiko_api_permission_check',
        'args'                => array(
            'id' => array( 'type' => 'integer', 'required' => true ),
        ),
    ) );

    // GET /wp-json/kaiko/v1/species
    register_rest_route( $namespace, '/species', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'kaiko_api_get_species',
        'permission_callback' => 'kaiko_api_permission_check',
    ) );

    // GET /wp-json/kaiko/v1/care-guide/{species}
    register_rest_route( $namespace, '/care-guide/(?P<species>[a-zA-Z0-9_-]+)', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'kaiko_api_get_care_guide',
        'permission_callback' => 'kaiko_api_permission_check',
        'args'                => array(
            'species' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
        ),
    ) );

    // GET /wp-json/kaiko/v1/stock-status
    register_rest_route( $namespace, '/stock-status', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'kaiko_api_get_stock_status',
        'permission_callback' => 'kaiko_api_permission_check',
        'args'                => array(
            'threshold' => array( 'type' => 'integer', 'default' => 5, 'minimum' => 0 ),
        ),
    ) );
}

/* ============================================
   ENDPOINT: GET /products
   ============================================ */

/**
 * Search and filter products.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function kaiko_api_get_products( $request ) {
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $request->get_param( 'per_page' ),
        'paged'          => $request->get_param( 'page' ),
    );

    // Search
    $search = $request->get_param( 'search' );
    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    // Tax query container
    $tax_query = array();

    // Category filter
    $category = $request->get_param( 'category' );
    if ( ! empty( $category ) ) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $category,
        );
    }

    if ( ! empty( $tax_query ) ) {
        $args['tax_query'] = $tax_query;
    }

    // Meta query container
    $meta_query = array();

    // Species filter (ACF repeater)
    $species = $request->get_param( 'species' );
    if ( ! empty( $species ) ) {
        $meta_query[] = array(
            'key'     => 'compatible_species_%_species_name',
            'value'   => $species,
            'compare' => 'LIKE',
        );
    }

    // Difficulty filter
    $difficulty = $request->get_param( 'difficulty' );
    if ( ! empty( $difficulty ) ) {
        $meta_query[] = array(
            'key'     => 'care_difficulty',
            'value'   => $difficulty,
            'compare' => '=',
        );
    }

    if ( ! empty( $meta_query ) ) {
        $args['meta_query'] = $meta_query;
    }

    // Ordering
    $orderby = $request->get_param( 'orderby' );
    switch ( $orderby ) {
        case 'price':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'ASC';
            break;
        case 'price-desc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'DESC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        case 'popularity':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order']    = 'DESC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
    }

    $query = new WP_Query( $args );
    $products = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $product = wc_get_product( get_the_ID() );
            if ( ! $product ) continue;

            $products[] = kaiko_api_format_product_summary( $product );
        }
        wp_reset_postdata();
    }

    return new WP_REST_Response( array(
        'products'   => $products,
        'total'      => (int) $query->found_posts,
        'pages'      => (int) $query->max_num_pages,
        'page'       => (int) $request->get_param( 'page' ),
        'per_page'   => (int) $request->get_param( 'per_page' ),
    ), 200 );
}

/* ============================================
   ENDPOINT: GET /product/{id}
   ============================================ */

/**
 * Get full product detail including ACF fields.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function kaiko_api_get_product( $request ) {
    $product_id = (int) $request->get_param( 'id' );
    $product    = wc_get_product( $product_id );

    if ( ! $product || 'publish' !== get_post_status( $product_id ) ) {
        return new WP_Error(
            'kaiko_product_not_found',
            __( 'Product not found.', 'kaiko-child' ),
            array( 'status' => 404 )
        );
    }

    $data = kaiko_api_format_product_summary( $product );

    // Add full description
    $data['description']       = wp_strip_all_tags( $product->get_description() );
    $data['short_description'] = wp_strip_all_tags( $product->get_short_description() );

    // Species compatibility (ACF)
    $species = get_field( 'compatible_species', $product_id );
    $data['species_compatibility'] = array();
    if ( ! empty( $species ) && is_array( $species ) ) {
        foreach ( $species as $row ) {
            $data['species_compatibility'][] = array(
                'name'       => sanitize_text_field( $row['species_name'] ?? '' ),
                'scientific' => sanitize_text_field( $row['species_scientific'] ?? '' ),
                'level'      => sanitize_text_field( $row['compatibility_level'] ?? '' ),
                'notes'      => sanitize_text_field( $row['compatibility_notes'] ?? '' ),
            );
        }
    }

    // Hardware specifications (ACF)
    $dims     = get_field( 'dimensions', $product_id );
    $material = get_field( 'material', $product_id );
    $weight   = get_field( 'weight_kg', $product_id );
    $power    = get_field( 'power_requirements', $product_id );

    $data['specifications'] = array(
        'dimensions' => array(
            'length' => ! empty( $dims['length'] ) ? (float) $dims['length'] : null,
            'width'  => ! empty( $dims['width'] ) ? (float) $dims['width'] : null,
            'height' => ! empty( $dims['height'] ) ? (float) $dims['height'] : null,
            'unit'   => 'cm',
        ),
        'material'           => $material ?: null,
        'weight_kg'          => $weight ? (float) $weight : null,
        'power_requirements' => $power ?: null,
    );

    // Care information (ACF)
    $data['care'] = array(
        'difficulty'            => get_field( 'care_difficulty', $product_id ) ?: null,
        'setup_time'            => get_field( 'setup_time', $product_id ) ?: null,
        'maintenance_frequency' => get_field( 'maintenance_frequency', $product_id ) ?: null,
        'care_guide_link'       => get_field( 'care_guide_link', $product_id ) ?: null,
    );

    // Gallery images
    $gallery_ids   = $product->get_gallery_image_ids();
    $data['gallery'] = array();
    foreach ( $gallery_ids as $img_id ) {
        $url = wp_get_attachment_url( $img_id );
        if ( $url ) {
            $data['gallery'][] = esc_url( $url );
        }
    }

    // Reviews summary
    $data['reviews'] = array(
        'average_rating' => (float) $product->get_average_rating(),
        'review_count'   => (int) $product->get_review_count(),
    );

    // Related products
    $related_ids = wc_get_related_products( $product_id, 4 );
    $data['related_products'] = array();
    foreach ( $related_ids as $rid ) {
        $rp = wc_get_product( $rid );
        if ( $rp ) {
            $data['related_products'][] = array(
                'id'    => $rid,
                'name'  => $rp->get_name(),
                'price' => $rp->get_price(),
                'url'   => get_permalink( $rid ),
            );
        }
    }

    return new WP_REST_Response( $data, 200 );
}

/* ============================================
   ENDPOINT: GET /species
   ============================================ */

/**
 * List all species with product counts.
 *
 * Aggregates species names from ACF repeater fields across all products.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function kaiko_api_get_species( $request ) {
    // Cache for 1 hour
    $cache_key = 'kaiko_api_species_list';
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return new WP_REST_Response( $cached, 200 );
    }

    $species_map = array();

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    );

    $product_ids = get_posts( $args );

    foreach ( $product_ids as $pid ) {
        $species = get_field( 'compatible_species', $pid );
        if ( empty( $species ) || ! is_array( $species ) ) continue;

        foreach ( $species as $row ) {
            $name = sanitize_text_field( $row['species_name'] ?? '' );
            if ( empty( $name ) ) continue;

            $slug = sanitize_title( $name );
            if ( ! isset( $species_map[ $slug ] ) ) {
                $species_map[ $slug ] = array(
                    'name'          => $name,
                    'slug'          => $slug,
                    'scientific'    => sanitize_text_field( $row['species_scientific'] ?? '' ),
                    'product_count' => 0,
                    'product_ids'   => array(),
                );
            }
            $species_map[ $slug ]['product_count']++;
            $species_map[ $slug ]['product_ids'][] = $pid;
        }
    }

    // Deduplicate product IDs and sort by name
    foreach ( $species_map as &$s ) {
        $s['product_ids'] = array_values( array_unique( $s['product_ids'] ) );
        $s['product_count'] = count( $s['product_ids'] );
    }
    unset( $s );

    usort( $species_map, function( $a, $b ) {
        return strcasecmp( $a['name'], $b['name'] );
    } );

    $result = array(
        'species' => array_values( $species_map ),
        'total'   => count( $species_map ),
    );

    set_transient( $cache_key, $result, HOUR_IN_SECONDS );

    return new WP_REST_Response( $result, 200 );
}

/* ============================================
   ENDPOINT: GET /care-guide/{species}
   ============================================ */

/**
 * Get care information for a species.
 *
 * Aggregates care data from products compatible with the given species.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function kaiko_api_get_care_guide( $request ) {
    $species_slug = $request->get_param( 'species' );

    // Find products for this species
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'compatible_species_%_species_name',
                'value'   => str_replace( '-', ' ', $species_slug ),
                'compare' => 'LIKE',
            ),
        ),
    );

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return new WP_Error(
            'kaiko_species_not_found',
            __( 'No products found for this species.', 'kaiko-child' ),
            array( 'status' => 404 )
        );
    }

    $species_info   = array();
    $products       = array();
    $care_guides    = array();
    $difficulty_map = array();

    while ( $query->have_posts() ) {
        $query->the_post();
        $pid     = get_the_ID();
        $product = wc_get_product( $pid );
        if ( ! $product ) continue;

        // Collect species details from first match
        if ( empty( $species_info ) ) {
            $species_rows = get_field( 'compatible_species', $pid );
            if ( $species_rows ) {
                foreach ( $species_rows as $row ) {
                    if ( sanitize_title( $row['species_name'] ) === $species_slug ) {
                        $species_info = array(
                            'name'       => sanitize_text_field( $row['species_name'] ),
                            'scientific' => sanitize_text_field( $row['species_scientific'] ?? '' ),
                        );
                        break;
                    }
                }
            }
        }

        // Collect product summaries
        $products[] = array(
            'id'         => $pid,
            'name'       => $product->get_name(),
            'price'      => $product->get_price(),
            'category'   => kaiko_api_get_primary_category( $pid ),
            'difficulty' => get_field( 'care_difficulty', $pid ) ?: null,
            'url'        => get_permalink( $pid ),
        );

        // Collect care guide links
        $guide_link = get_field( 'care_guide_link', $pid );
        if ( ! empty( $guide_link ) ) {
            $care_guides[] = esc_url( $guide_link );
        }

        // Track difficulty distribution
        $diff = get_field( 'care_difficulty', $pid );
        if ( $diff ) {
            $difficulty_map[ $diff ] = ( $difficulty_map[ $diff ] ?? 0 ) + 1;
        }
    }
    wp_reset_postdata();

    return new WP_REST_Response( array(
        'species'              => $species_info,
        'total_products'       => count( $products ),
        'products'             => $products,
        'care_guide_links'     => array_unique( $care_guides ),
        'difficulty_breakdown' => $difficulty_map,
    ), 200 );
}

/* ============================================
   ENDPOINT: GET /stock-status
   ============================================ */

/**
 * Get low stock alerts.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function kaiko_api_get_stock_status( $request ) {
    $threshold = (int) $request->get_param( 'threshold' );

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => '_manage_stock',
                'value'   => 'yes',
                'compare' => '=',
            ),
            array(
                'relation' => 'OR',
                array(
                    'key'     => '_stock',
                    'value'   => $threshold,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ),
                array(
                    'key'     => '_stock_status',
                    'value'   => 'outofstock',
                    'compare' => '=',
                ),
            ),
        ),
    );

    $query = new WP_Query( $args );
    $alerts = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $product = wc_get_product( get_the_ID() );
            if ( ! $product ) continue;

            $stock_qty = $product->get_stock_quantity();

            $alerts[] = array(
                'id'           => get_the_ID(),
                'name'         => $product->get_name(),
                'sku'          => $product->get_sku(),
                'stock_qty'    => $stock_qty !== null ? (int) $stock_qty : null,
                'stock_status' => $product->get_stock_status(),
                'price'        => $product->get_price(),
                'category'     => kaiko_api_get_primary_category( get_the_ID() ),
                'url'          => get_permalink( get_the_ID() ),
                'edit_url'     => admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ),
            );
        }
        wp_reset_postdata();
    }

    // Sort: out of stock first, then by stock quantity ascending
    usort( $alerts, function( $a, $b ) {
        if ( $a['stock_status'] === 'outofstock' && $b['stock_status'] !== 'outofstock' ) return -1;
        if ( $b['stock_status'] === 'outofstock' && $a['stock_status'] !== 'outofstock' ) return 1;
        return ( $a['stock_qty'] ?? 0 ) - ( $b['stock_qty'] ?? 0 );
    } );

    return new WP_REST_Response( array(
        'threshold' => $threshold,
        'total'     => count( $alerts ),
        'alerts'    => $alerts,
        'generated' => current_time( 'c' ),
    ), 200 );
}

/* ============================================
   HELPER FUNCTIONS
   ============================================ */

/**
 * Format a product summary for API responses.
 *
 * @param WC_Product $product
 * @return array
 */
function kaiko_api_format_product_summary( $product ) {
    $pid = $product->get_id();

    return apply_filters( 'kaiko_api_product_summary', array(
        'id'           => $pid,
        'name'         => $product->get_name(),
        'slug'         => $product->get_slug(),
        'sku'          => $product->get_sku(),
        'price'        => $product->get_price(),
        'regular_price'=> $product->get_regular_price(),
        'sale_price'   => $product->get_sale_price(),
        'on_sale'      => $product->is_on_sale(),
        'stock_status' => $product->get_stock_status(),
        'stock_qty'    => $product->get_stock_quantity(),
        'category'     => kaiko_api_get_primary_category( $pid ),
        'categories'   => kaiko_api_get_categories( $pid ),
        'image'        => wp_get_attachment_url( $product->get_image_id() ) ?: null,
        'difficulty'   => get_field( 'care_difficulty', $pid ) ?: null,
        'url'          => get_permalink( $pid ),
    ), $product );
}

/**
 * Get the primary category name for a product.
 *
 * @param int $product_id
 * @return string|null
 */
function kaiko_api_get_primary_category( $product_id ) {
    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        // Prefer non-"uncategorized" term
        foreach ( $terms as $term ) {
            if ( 'uncategorized' !== $term->slug ) {
                return $term->name;
            }
        }
        return $terms[0]->name;
    }
    return null;
}

/**
 * Get all category names for a product.
 *
 * @param int $product_id
 * @return array
 */
function kaiko_api_get_categories( $product_id ) {
    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        return array_map( function( $term ) {
            return array(
                'id'   => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }, $terms );
    }
    return array();
}

/* ============================================
   ADMIN SETTINGS: API Key Management
   ============================================ */

add_action( 'admin_init', 'kaiko_api_register_settings' );

function kaiko_api_register_settings() {
    register_setting( 'general', 'kaiko_api_keys', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ) );

    add_settings_field(
        'kaiko_api_keys',
        'Kaiko API Keys',
        'kaiko_api_keys_field_render',
        'general',
        'default'
    );
}

function kaiko_api_keys_field_render() {
    $keys = get_option( 'kaiko_api_keys', '' );
    echo '<input type="text" name="kaiko_api_keys" value="' . esc_attr( $keys ) . '" class="regular-text" />';
    echo '<p class="description">Comma-separated API keys for chatbot/n8n access. Or define <code>KAIKO_API_KEY</code> in wp-config.php.</p>';
}
