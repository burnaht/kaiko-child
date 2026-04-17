<?php
/**
 * Kaiko — SEO Structure
 *
 * Structured data (JSON-LD), Open Graph, Twitter Cards,
 * canonical URL management, and meta description helpers.
 *
 * @package KaikoChild
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ============================================
   1. JSON-LD STRUCTURED DATA
   ============================================ */

add_action( 'wp_head', 'kaiko_output_structured_data', 5 );

/**
 * Output all JSON-LD structured data.
 */
function kaiko_output_structured_data() {
    $schemas = array();

    // WebSite schema (all pages)
    $schemas[] = kaiko_schema_website();

    // Organization schema (all pages)
    $schemas[] = kaiko_schema_organization();

    // BreadcrumbList (all pages except front page)
    if ( ! is_front_page() ) {
        $breadcrumb = kaiko_schema_breadcrumb();
        if ( $breadcrumb ) {
            $schemas[] = $breadcrumb;
        }
    }

    // Product schema (single product)
    if ( function_exists( 'is_product' ) && is_product() ) {
        $product_schema = kaiko_schema_product();
        if ( $product_schema ) {
            $schemas[] = $product_schema;
        }
    }

    // LocalBusiness (front page only)
    if ( is_front_page() ) {
        $local = kaiko_schema_local_business();
        if ( $local ) {
            $schemas[] = $local;
        }
    }

    // FAQ schema (contact page)
    if ( is_page( 'contact' ) || kaiko_is_page_slug( 'contact' ) ) {
        $faq = kaiko_schema_faq();
        if ( $faq ) {
            $schemas[] = $faq;
        }
    }

    // Output each schema
    foreach ( $schemas as $schema ) {
        if ( ! empty( $schema ) ) {
            echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>' . "\n";
        }
    }
}

/**
 * WebSite schema with SearchAction.
 */
function kaiko_schema_website() {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => get_bloginfo( 'name' ),
        'url'      => home_url( '/' ),
    );

    // Add SearchAction if WooCommerce is active
    if ( class_exists( 'WooCommerce' ) ) {
        $schema['potentialAction'] = array(
            '@type'       => 'SearchAction',
            'target'      => array(
                '@type'        => 'EntryPoint',
                'urlTemplate'  => home_url( '/?s={search_term_string}&post_type=product' ),
            ),
            'query-input' => 'required name=search_term_string',
        );
    }

    return apply_filters( 'kaiko_schema_website', $schema );
}

/**
 * Organization schema.
 */
function kaiko_schema_organization() {
    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => apply_filters( 'kaiko_org_name', get_bloginfo( 'name' ) ),
        'url'         => home_url( '/' ),
        'description' => apply_filters( 'kaiko_org_description', 'Premium reptile and exotic pet supplies. Curated equipment, enclosures, and care essentials for discerning keepers.' ),
    );

    // Logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $logo_url = wp_get_attachment_url( $custom_logo_id );
        if ( $logo_url ) {
            $schema['logo'] = esc_url( $logo_url );
            $schema['image'] = esc_url( $logo_url );
        }
    }

    // Contact
    $schema['contactPoint'] = apply_filters( 'kaiko_org_contact', array(
        '@type'       => 'ContactPoint',
        'contactType' => 'customer service',
        'email'       => get_option( 'admin_email' ),
    ) );

    // Social profiles
    $social = apply_filters( 'kaiko_social_profiles', array() );
    if ( ! empty( $social ) ) {
        $schema['sameAs'] = $social;
    }

    return apply_filters( 'kaiko_schema_organization', $schema );
}

/**
 * Product schema on single product pages.
 */
function kaiko_schema_product() {
    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return null;
    }

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->get_name(),
        'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
        'url'         => get_permalink( $product->get_id() ),
        'sku'         => $product->get_sku() ?: null,
    );

    // Image
    $image_id = $product->get_image_id();
    if ( $image_id ) {
        $schema['image'] = wp_get_attachment_url( $image_id );
    }

    // Brand
    $schema['brand'] = array(
        '@type' => 'Brand',
        'name'  => apply_filters( 'kaiko_product_brand', get_bloginfo( 'name' ), $product ),
    );

    // Offers
    $schema['offers'] = array(
        '@type'           => 'Offer',
        'url'             => get_permalink( $product->get_id() ),
        'priceCurrency'   => get_woocommerce_currency(),
        'price'           => $product->get_price(),
        'availability'    => $product->is_in_stock()
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'seller'          => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
        ),
    );

    // Sale price validity
    $sale_from = $product->get_date_on_sale_from();
    $sale_to   = $product->get_date_on_sale_to();
    if ( $sale_to ) {
        $schema['offers']['priceValidUntil'] = $sale_to->date( 'Y-m-d' );
    }

    // Reviews / AggregateRating
    $rating_count = $product->get_rating_count();
    if ( $rating_count > 0 ) {
        $schema['aggregateRating'] = array(
            '@type'       => 'AggregateRating',
            'ratingValue' => (float) $product->get_average_rating(),
            'reviewCount' => $rating_count,
        );
    }

    // Species compatibility as additionalProperty
    $species = get_field( 'compatible_species', $product->get_id() );
    if ( ! empty( $species ) && is_array( $species ) ) {
        $schema['additionalProperty'] = array();
        foreach ( $species as $row ) {
            $schema['additionalProperty'][] = array(
                '@type' => 'PropertyValue',
                'name'  => 'Compatible Species',
                'value' => sanitize_text_field( $row['species_name'] ?? '' ),
            );
        }


    }

    return apply_filters( 'kaiko_schema_product', $schema, $product );
}

/**
 * BreadcrumbList schema.
 */
function kaiko_schema_breadcrumb() {
    $items = array();
    $position = 1;

    // Home
    $items[] = array(
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => __( 'Home', 'kaiko-child' ),
        'item'     => home_url( '/' ),
    );

    // Shop
    if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
        $shop_url = wc_get_page_permalink( 'shop' );
        if ( $shop_url && ! is_shop() ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => __( 'Shop', 'kaiko-child' ),
                'item'     => $shop_url,
            );
        }
    }

    // Product category
    if ( function_exists( 'is_product_category' ) && is_product_category() ) {
        $term = get_queried_object();
        if ( $term ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $term->name,
                'item'     => get_term_link( $term ),
            );
        }
    }

    // Single product
    if ( function_exists( 'is_product' ) && is_product() ) {
        global $product;
        $cats = get_the_terms( get_the_ID(), 'product_cat' );
        if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
            $cat = $cats[0];
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $cat->name,
                'item'     => get_term_link( $cat ),
            );
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
        );
    }

    // Regular pages
    if ( is_page() && ! is_front_page() ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
        );
    }

    if ( count( $items ) < 2 ) {
        return null;
    }

    return apply_filters( 'kaiko_schema_breadcrumb', array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ) );
}

/**
 * LocalBusiness schema.
 */
function kaiko_schema_local_business() {
    $business = apply_filters( 'kaiko_local_business', array() );

    if ( empty( $business ) ) {
        return null;
    }

    $schema = array_merge( array(
        '@context' => 'https://schema.org',
        '@type'    => 'PetStore',
    ), $business );

    return $schema;
}

/**
 * FAQ schema for contact page.
 */
function kaiko_schema_faq() {
    $faqs = apply_filters( 'kaiko_faq_items', array(
        array(
            'question' => 'Do you offer wholesale pricing?',
            'answer'   => 'Yes! We offer trade pricing for approved business partners. Apply for a trade account through our registration page and our team will review your application.',
        ),
        array(
            'question' => 'What species do you cater for?',
            'answer'   => 'We stock products for a wide range of reptiles and exotic pets including ball pythons, leopard geckos, crested geckos, bearded dragons, corn snakes, chameleons, blue tongue skinks, and monitor lizards.',
        ),
        array(
            'question' => 'Do you ship internationally?',
            'answer'   => 'Currently we ship within the UK. International shipping is planned for the future. Contact us for special arrangements on large orders.',
        ),
        array(
            'question' => 'What is your returns policy?',
            'answer'   => 'We offer a 30-day returns policy on unused items in original packaging. Electrical items must be returned within 14 days. See our full returns policy page for details.',
        ),
        array(
            'question' => 'How do I become a Kaiko trade partner?',
            'answer'   => 'Register for a trade account on our website. Our team will review your application and, once approved, you\'ll have full access to trade pricing and ordering.',
        ),
    ) );

    if ( empty( $faqs ) ) {
        return null;
    }

    $main_entity = array();
    foreach ( $faqs as $faq ) {
        $main_entity[] = array(
            '@type'          => 'Question',
            'name'           => sanitize_text_field( $faq['question'] ),
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => wp_kses_post( $faq['answer'] ),
            ),
        );
    }

    return apply_filters( 'kaiko_schema_faq', array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $main_entity,
    ) );
}


/* ============================================
   2. OPEN GRAPH & TWITTER CARDS
   ============================================ */

add_action( 'wp_head', 'kaiko_output_og_meta', 6 );

/**
 * Output Open Graph and Twitter Card meta tags.
 */
function kaiko_output_og_meta() {
    // Don't output if Yoast, Rank Math, or All in One SEO is active
    if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ) ) {
        return;
    }

    $og = array(
        'og:type'        => 'website',
        'og:title'       => kaiko_get_page_title(),
        'og:description' => kaiko_get_meta_description(),
        'og:url'         => kaiko_get_canonical_url(),
        'og:site_name'   => get_bloginfo( 'name' ),
        'og:locale'      => get_locale(),
    );

    // Image
    $image = kaiko_get_og_image();
    if ( $image ) {
        $og['og:image'] = $image;
    }

    // Product-specific OG
    // Note: wp_head fires before the main loop sets up $GLOBALS['product'],
    // so we must resolve the product from the queried object instead of the global.
    if ( function_exists( 'is_product' ) && is_product() && function_exists( 'wc_get_product' ) ) {
        $product_obj = wc_get_product( get_queried_object_id() );
        if ( $product_obj instanceof WC_Product ) {
            $og['og:type']                = 'product';
            $og['product:price:amount']   = $product_obj->get_price();
            $og['product:price:currency'] = get_woocommerce_currency();
            $og['product:availability']   = $product_obj->is_in_stock() ? 'in stock' : 'out of stock';
        }
    }

    $og = apply_filters( 'kaiko_og_meta', $og );

    foreach ( $og as $property => $content ) {
        if ( ! empty( $content ) ) {
            echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
        }
    }

    // Twitter Cards
    $twitter = array(
        'twitter:card'        => ! empty( $image ) ? 'summary_large_image' : 'summary',
        'twitter:title'       => $og['og:title'],
        'twitter:description' => $og['og:description'],
    );

    if ( $image ) {
        $twitter['twitter:image'] = $image;
    }

    $twitter_site = apply_filters( 'kaiko_twitter_site', '' );
    if ( ! empty( $twitter_site ) ) {
        $twitter['twitter:site'] = $twitter_site;
    }

    $twitter = apply_filters( 'kaiko_twitter_meta', $twitter );

    foreach ( $twitter as $name => $content ) {
        if ( ! empty( $content ) ) {
            echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
        }
    }
}


/* ============================================
   3. CANONICAL URL MANAGEMENT
   ============================================ */

add_action( 'wp_head', 'kaiko_output_canonical', 7 );

/**
 * Output canonical URL.
 */
function kaiko_output_canonical() {
    // Skip if an SEO plugin handles this
    if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ) ) {
        return;
    }

    $canonical = kaiko_get_canonical_url();
    if ( $canonical ) {
        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
    }
}


/* ============================================
   4. META DESCRIPTION
   ============================================ */

add_action( 'wp_head', 'kaiko_output_meta_description', 8 );

/**
 * Output meta description tag.
 */
function kaiko_output_meta_description() {
    // Skip if an SEO plugin handles this
    if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ) ) {
        return;
    }

    $description = kaiko_get_meta_description();
    if ( ! empty( $description ) ) {
        echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
    }
}


/* ============================================
   5. XML SITEMAP ENHANCEMENTS
   ============================================ */

/**
 * Add custom product attributes to WordPress core sitemap.
 */
add_filter( 'wp_sitemaps_posts_entry', 'kaiko_sitemap_product_entry', 10, 3 );

function kaiko_sitemap_product_entry( $entry, $post, $post_type ) {
    if ( 'product' !== $post_type ) {
        return $entry;
    }

    $product = wc_get_product( $post->ID );
    if ( ! $product ) {
        return $entry;
    }

    // Set higher priority for featured/in-stock products
    if ( $product->is_featured() ) {
        $entry['priority'] = 0.8;
    }

    // Add last modified based on product data
    $modified = $product->get_date_modified();
    if ( $modified ) {
        $entry['lastmod'] = $modified->date( 'Y-m-d\TH:i:sP' );
    }

    return $entry;
}

/**
 * Add product image to sitemap entry.
 */
add_filter( 'wp_sitemaps_posts_show_on_front_entry', 'kaiko_sitemap_front_page', 10, 1 );

function kaiko_sitemap_front_page( $entry ) {
    $entry['priority'] = 1.0;
    $entry['changefreq'] = 'daily';
    return $entry;
}


/* ============================================
   HELPER FUNCTIONS
   ============================================ */

/**
 * Get the page title for meta tags.
 *
 * @return string
 */
function kaiko_get_page_title() {
    if ( is_front_page() ) {
        return get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' );
    }

    if ( function_exists( 'is_product' ) && is_product() ) {
        return get_the_title() . ' — ' . get_bloginfo( 'name' );
    }

    if ( function_exists( 'is_product_category' ) && is_product_category() ) {
        $term = get_queried_object();
        return $term->name . ' — ' . get_bloginfo( 'name' );
    }

    if ( function_exists( 'is_shop' ) && is_shop() ) {
        return __( 'Shop', 'kaiko-child' ) . ' — ' . get_bloginfo( 'name' );
    }

    return wp_get_document_title();
}

/**
 * Get meta description from ACF, excerpt, or auto-generate.
 *
 * @return string
 */
function kaiko_get_meta_description() {
    $description = '';

    if ( is_front_page() ) {
        $description = apply_filters( 'kaiko_home_meta_description',
            'Premium reptile and exotic pet supplies. Quality enclosures, heating, lighting, substrate, and care essentials from specialist keepers.'
        );
    } elseif ( is_singular() ) {
        // Try ACF meta_description field first
        if ( function_exists( 'get_field' ) ) {
            $acf_desc = get_field( 'meta_description', get_the_ID() );
            if ( ! empty( $acf_desc ) ) {
                $description = $acf_desc;
            }
        }

        // Fall back to excerpt
        if ( empty( $description ) ) {
            $description = get_the_excerpt();
        }

        // Fall back to trimmed content
        if ( empty( $description ) ) {
            $content = wp_strip_all_tags( get_the_content() );
            $description = wp_trim_words( $content, 25, '...' );
        }
    } elseif ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            $description = wp_strip_all_tags( $term->description );
        }
    } elseif ( function_exists( 'is_shop' ) && is_shop() ) {
        $description = apply_filters( 'kaiko_shop_meta_description',
            'Browse our curated range of premium reptile supplies. Enclosures, heating, lighting, substrates, and care essentials for every species.'
        );
    }

    // Truncate to 160 characters
    if ( mb_strlen( $description ) > 160 ) {
        $description = mb_substr( $description, 0, 157 ) . '...';
    }

    return apply_filters( 'kaiko_meta_description', $description );
}

/**
 * Get canonical URL for the current page.
 *
 * @return string
 */
function kaiko_get_canonical_url() {
    if ( is_front_page() ) {
        return home_url( '/' );
    }

    if ( is_singular() ) {
        return get_permalink();
    }

    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        return $term ? get_term_link( $term ) : '';
    }

    if ( function_exists( 'is_shop' ) && is_shop() ) {
        return wc_get_page_permalink( 'shop' );
    }

    return '';
}

/**
 * Get OG image for the current page.
 *
 * @return string|null
 */
function kaiko_get_og_image() {
    // Single post/product — use featured image
    if ( is_singular() && has_post_thumbnail() ) {
        return get_the_post_thumbnail_url( null, 'large' );
    }

    // Product category — use category thumbnail
    if ( function_exists( 'is_product_category' ) && is_product_category() ) {
        $term = get_queried_object();
        if ( $term ) {
            $thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            if ( $thumb_id ) {
                return wp_get_attachment_url( $thumb_id );
            }
        }
    }

    // Fallback to site logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        return wp_get_attachment_url( $custom_logo_id );
    }

    return apply_filters( 'kaiko_default_og_image', null );
}

/**
 * Check if current page matches a slug.
 *
 * @param string $slug
 * @return bool
 */
function kaiko_is_page_slug( $slug ) {
    global $post;
    return $post && is_a( $post, 'WP_Post' ) && $post->post_name === $slug;
}
