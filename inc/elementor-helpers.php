<?php
/**
 * Kaiko — Elementor Helpers & Shortcodes
 *
 * Provides shortcodes and helper functions for Elementor-built pages.
 * Included from functions.php.
 *
 * @package KaikoChild
 * @since   2.1.0
 */

defined( 'ABSPATH' ) || exit;


/* ============================================
   SHORTCODE: [kaiko_species_grid]
   Renders a grid of species cards linking to filtered shop views.
   
   Attributes:
     columns      — 2|3|4 (default: 4)
     show_count   — true|false (default: true) — show product count per species
     category     — product category slug to filter species by (optional)
     style        — default|compact (default: default)
   ============================================ */

add_shortcode( 'kaiko_species_grid', 'kaiko_species_grid_shortcode' );

function kaiko_species_grid_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'columns'    => 4,
        'show_count' => 'true',
        'category'   => '',
        'link_to'    => 'shop',
        'style'      => 'default',
    ), $atts, 'kaiko_species_grid' );

    /**
     * Species list. The 'image' key references a file in
     * assets/images/species/ (theme directory). Empty string = no photo yet,
     * template falls back to emoji.
     */
    $species_list = array(
        array(
            'name'       => 'Ball Pythons',
            'slug'       => 'ball-python',
            'scientific' => 'Python regius',
            'emoji'      => '🐍',
            'image'      => '', // TODO: add ball-python.png to assets/images/species/
        ),
        array(
            'name'       => 'Leopard Geckos',
            'slug'       => 'leopard-gecko',
            'scientific' => 'Eublepharis macularius',
            'emoji'      => '🦎',
            'image'      => 'leopard-gecko.png',
        ),
        array(
            'name'       => 'Crested Geckos',
            'slug'       => 'crested-gecko',
            'scientific' => 'Correlophus ciliatus',
            'emoji'      => '🦎',
            'image'      => 'crested-gecko.png',
        ),
        array(
            'name'       => 'Bearded Dragons',
            'slug'       => 'bearded-dragon',
            'scientific' => 'Pogona vitticeps',
            'emoji'      => '🐉',
            'image'      => 'bearded-dragon.png',
        ),
        array(
            'name'       => 'Corn Snakes',
            'slug'       => 'corn-snake',
            'scientific' => 'Pantherophis guttatus',
            'emoji'      => '🐍',
            'image'      => '', // TODO: add corn-snake.png to assets/images/species/
        ),
        array(
            'name'       => 'Chameleons',
            'slug'       => 'chameleon',
            'scientific' => 'Chamaeleonidae',
            'emoji'      => '🦎',
            'image'      => 'chameleon.png',
        ),
        array(
            'name'       => 'Blue Tongue Skinks',
            'slug'       => 'blue-tongue-skink',
            'scientific' => 'Tiliqua scincoides',
            'emoji'      => '🦎',
            'image'      => 'blue-tongue-skink.png',
        ),
        array(
            'name'       => 'Monitor Lizards',
            'slug'       => 'monitor-lizard',
            'scientific' => 'Varanus',
            'emoji'      => '🦎',
            'image'      => '', // TODO: add monitor-lizard.png to assets/images/species/
        ),
    );

    /**
     * Filter: allow themes/plugins to modify the species list.
     */
    $species_list = apply_filters( 'kaiko_species_list', $species_list );

    $columns    = absint( $atts['columns'] );
    $show_count = 'true' === $atts['show_count'];
    $link_to    = sanitize_text_field( $atts['link_to'] );
    $style      = sanitize_html_class( $atts['style'] );
    $category   = sanitize_text_field( $atts['category'] );

    ob_start();
    ?>
    <div class="kaiko-species-grid kaiko-species-grid--cols-<?php echo esc_attr( $columns ); ?> kaiko-species-grid--<?php echo esc_attr( $style ); ?>">
        <?php foreach ( $species_list as $species ) :
            // Build link URL
            $url = home_url( '/shop/?filter_species=' . $species['slug'] );
            if ( $category ) {
                $url = add_query_arg( 'product_cat', $category, $url );
            }

            // Count products for this species (searches ACF repeater)
            $count = 0;
            if ( $show_count && class_exists( 'WooCommerce' ) ) {
                $count_query = new WP_Query( array(
                    'post_type'      => 'product',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'meta_query'     => array(
                        array(
                            'key'     => 'compatible_species_%_species_name',
                            'value'   => $species['name'],
                            'compare' => 'LIKE',
                        ),
                    ),
                ) );
                $count = $count_query->found_posts;
                wp_reset_postdata();
            }
        ?>
        <a href="<?php echo esc_url( $url ); ?>" class="kaiko-species-card kaiko-reveal<?php echo ! empty( $species['image'] ) ? ' has-photo' : ''; ?>" aria-label="<?php echo esc_attr( $species['name'] ); ?>">
            <div class="kaiko-species-card__image">
                <?php if ( ! empty( $species['image'] ) ) :
                    $photo_url = get_stylesheet_directory_uri() . '/assets/images/species/' . $species['image'];
                ?>
                    <img
                        src="<?php echo esc_url( $photo_url ); ?>"
                        alt="<?php echo esc_attr( $species['name'] ); ?>"
                        class="kaiko-species-card__photo"
                        loading="lazy"
                        decoding="async"
                        width="120"
                        height="120"
                    />
                <?php else : ?>
                    <span class="kaiko-species-card__emoji" aria-hidden="true"><?php echo esc_html( $species['emoji'] ); ?></span>
                <?php endif; ?>
            </div>
            <div class="kaiko-species-card__body">
                <h3 class="kaiko-species-card__name"><?php echo esc_html( $species['name'] ); ?></h3>
                <span class="kaiko-species-card__scientific"><?php echo esc_html( $species['scientific'] ); ?></span>
                <?php if ( $show_count ) : ?>
                    <span class="kaiko-species-card__count"><?php echo esc_html( $count ); ?> product<?php echo 1 !== $count ? 's' : ''; ?></span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}


/* ============================================
   SHORTCODE: [kaiko_featured_products]
   Renders a grid/carousel of featured WooCommerce products.
   
   Attributes:
     limit    — number of products (default: 8)
     columns  — 2|3|4 (default: 4)
     orderby  — date|price|popularity|rating (default: date)
   ============================================ */

add_shortcode( 'kaiko_featured_products', 'kaiko_featured_products_shortcode' );

function kaiko_featured_products_shortcode( $atts ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return '<p class="kaiko-text-muted">WooCommerce is required for this feature.</p>';
    }

    $atts = shortcode_atts( array(
        'limit'   => 8,
        'columns' => 4,
        'orderby' => 'date',
    ), $atts, 'kaiko_featured_products' );

    $limit   = absint( $atts['limit'] );
    $columns = absint( $atts['columns'] );
    $orderby = sanitize_text_field( $atts['orderby'] );

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'featured',
            ),
        ),
    );

    // Ordering
    switch ( $orderby ) {
        case 'price':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'ASC';
            break;
        case 'popularity':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order']    = 'DESC';
            break;
        case 'rating':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_wc_average_rating';
            $args['order']    = 'DESC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
    }

    $query = new WP_Query( $args );

    // Fallback to recent products if no featured set
    if ( ! $query->have_posts() ) {
        unset( $args['tax_query'] );
        $query = new WP_Query( $args );
    }

    if ( ! $query->have_posts() ) {
        return '<p class="kaiko-text-muted">No products to display.</p>';
    }

    ob_start();
    ?>
    <div class="kaiko-featured-products kaiko-featured-products--cols-<?php echo esc_attr( $columns ); ?>" data-kaiko-carousel>
        <?php while ( $query->have_posts() ) : $query->the_post();
            global $product;
            $image_id  = $product->get_image_id();
            $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'kaiko-product-card' ) : wc_placeholder_img_src( 'kaiko-product-card' );
        ?>
        <div class="kaiko-featured-product kaiko-reveal">
            <a href="<?php the_permalink(); ?>" class="kaiko-featured-product__link">
                <div class="kaiko-featured-product__image">
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" />
                </div>
                <div class="kaiko-featured-product__body">
                    <h3 class="kaiko-featured-product__title"><?php the_title(); ?></h3>
                    <div class="kaiko-featured-product__price"><?php echo $product->get_price_html(); ?></div>
                </div>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}


/* ============================================
   SHORTCODE: [kaiko_newsletter_form]
   Renders a styled newsletter signup form.
   
   Attributes:
     placeholder — input placeholder (default: "Your email address")
     button_text — submit text (default: "Subscribe")
     privacy     — show privacy note (default: true)
   ============================================ */

add_shortcode( 'kaiko_newsletter_form', 'kaiko_newsletter_form_shortcode' );

function kaiko_newsletter_form_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'placeholder' => 'Your email address',
        'button_text' => 'Subscribe',
        'privacy'     => 'true',
    ), $atts, 'kaiko_newsletter_form' );

    $show_privacy = 'true' === $atts['privacy'];

    ob_start();
    ?>
    <div class="kaiko-newsletter" data-kaiko-newsletter>
        <form class="kaiko-newsletter-form" method="post" data-kaiko-newsletter-form>
            <?php wp_nonce_field( 'kaiko_newsletter', 'kaiko_newsletter_nonce' ); ?>
            <input
                type="email"
                name="kaiko_newsletter_email"
                placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
                required
                autocomplete="email"
                aria-label="Email address for newsletter"
            />
            <button type="submit" class="kaiko-btn kaiko-btn-accent">
                <?php echo esc_html( $atts['button_text'] ); ?>
            </button>
        </form>
        <div class="kaiko-newsletter__response" data-kaiko-newsletter-response style="display:none;"></div>
        <?php if ( $show_privacy ) : ?>
            <p class="kaiko-newsletter__privacy kaiko-caption kaiko-text-muted kaiko-mt-sm">
                We respect your privacy. Unsubscribe anytime.
            </p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// AJAX handler for newsletter form
add_action( 'wp_ajax_kaiko_newsletter_subscribe', 'kaiko_newsletter_subscribe_handler' );
add_action( 'wp_ajax_nopriv_kaiko_newsletter_subscribe', 'kaiko_newsletter_subscribe_handler' );

function kaiko_newsletter_subscribe_handler() {
    check_ajax_referer( 'kaiko_newsletter', 'nonce' );

    $email = sanitize_email( $_POST['email'] ?? '' );

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
    }

    /**
     * Hook: kaiko_newsletter_subscribe
     * Integrate with Mailchimp, Sendinblue, etc.
     * Receives the validated email address.
     */
    do_action( 'kaiko_newsletter_subscribe', $email );

    // Store locally as fallback
    $subscribers = get_option( 'kaiko_newsletter_subscribers', array() );
    if ( ! in_array( $email, $subscribers, true ) ) {
        $subscribers[] = $email;
        update_option( 'kaiko_newsletter_subscribers', $subscribers );
    }

    wp_send_json_success( array( 'message' => 'You\'re in! Check your inbox for a confirmation.' ) );
}


/* ============================================
   SHORTCODE: [kaiko_brand_stats]
   Renders animated stat counters for social proof.
   
   Attributes:
     layout — row|grid (default: row)
   ============================================ */

add_shortcode( 'kaiko_brand_stats', 'kaiko_brand_stats_shortcode' );

function kaiko_brand_stats_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'layout' => 'row',
    ), $atts, 'kaiko_brand_stats' );

    $stats = array(
        array( 'value' => 50,    'suffix' => '+', 'label' => 'Species Supported' ),
        array( 'value' => 2000,  'suffix' => '+', 'label' => 'Products Available' ),
        array( 'value' => 10000, 'suffix' => '+', 'label' => 'Orders Delivered' ),
        array( 'value' => 8,     'suffix' => '+', 'label' => 'Years Experience' ),
    );

    /**
     * Filter: allow modification of brand stats.
     */
    $stats = apply_filters( 'kaiko_brand_stats', $stats );

    $layout_class = 'grid' === $atts['layout'] ? 'kaiko-stats--grid' : 'kaiko-stats--row';

    ob_start();
    ?>
    <div class="kaiko-stats <?php echo esc_attr( $layout_class ); ?>" data-kaiko-stats>
        <?php foreach ( $stats as $stat ) : ?>
        <div class="kaiko-stat kaiko-reveal">
            <span
                class="kaiko-stat__number"
                data-kaiko-countup="<?php echo esc_attr( $stat['value'] ); ?>"
                data-kaiko-countup-suffix="<?php echo esc_attr( $stat['suffix'] ); ?>"
            >0<?php echo esc_html( $stat['suffix'] ); ?></span>
            <span class="kaiko-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}


/* ============================================
   SHORTCODE: [kaiko_testimonial_slider]
   Renders a customer testimonial carousel.
   
   Attributes:
     speed     — auto-advance interval in ms (default: 6000)
     count     — max testimonials to show (default: 5)
   ============================================ */

add_shortcode( 'kaiko_testimonial_slider', 'kaiko_testimonial_slider_shortcode' );

function kaiko_testimonial_slider_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'speed' => 6000,
        'count' => 5,
    ), $atts, 'kaiko_testimonial_slider' );

    /**
     * Testimonials data.
     * In production, these could come from ACF options page, custom post type, or reviews.
     * Filter available for dynamic data sources.
     */
    $testimonials = array(
        array(
            'quote'   => 'Kaiko is the only place I trust for my ball python setups. The species-specific filtering saved me hours of research, and the quality is genuinely premium.',
            'name'    => 'James H.',
            'species' => 'Ball Python keeper',
            'rating'  => 5,
        ),
        array(
            'quote'   => 'Finally, a reptile shop that actually understands what keepers need. The care guides alone are worth bookmarking.',
            'name'    => 'Sarah T.',
            'species' => 'Leopard Gecko & Crested Gecko keeper',
            'rating'  => 5,
        ),
        array(
            'quote'   => 'The bioactive substrate kits are brilliant. Everything arrives ready to go, and the instructions are clear even for a first-timer.',
            'name'    => 'Mark D.',
            'species' => 'Bearded Dragon keeper',
            'rating'  => 5,
        ),
        array(
            'quote'   => 'I\'ve been keeping reptiles for 15 years and Kaiko is by far the best-curated shop I\'ve found. No filler products, just quality.',
            'name'    => 'Lisa R.',
            'species' => 'Monitor Lizard keeper',
            'rating'  => 5,
        ),
        array(
            'quote'   => 'Ordered a complete chameleon setup and the team helped me pick every single item. Above and beyond service.',
            'name'    => 'Tom W.',
            'species' => 'Chameleon keeper',
            'rating'  => 5,
        ),
    );

    $testimonials = apply_filters( 'kaiko_testimonials', $testimonials );
    $testimonials = array_slice( $testimonials, 0, absint( $atts['count'] ) );
    $speed        = absint( $atts['speed'] );

    if ( empty( $testimonials ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="kaiko-testimonials" data-kaiko-testimonials data-speed="<?php echo esc_attr( $speed ); ?>">
        <div class="kaiko-testimonials__track">
            <?php foreach ( $testimonials as $i => $t ) : ?>
            <div class="kaiko-testimonials__slide<?php echo 0 === $i ? ' active' : ''; ?>" data-slide="<?php echo $i; ?>">
                <div class="kaiko-testimonials__stars">
                    <?php echo str_repeat( '★', $t['rating'] ); ?>
                </div>
                <blockquote class="kaiko-testimonials__quote">
                    <?php echo esc_html( $t['quote'] ); ?>
                </blockquote>
                <div class="kaiko-testimonials__author">
                    <span class="kaiko-testimonials__name"><?php echo esc_html( $t['name'] ); ?></span>
                    <span class="kaiko-testimonials__species"><?php echo esc_html( $t['species'] ); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="kaiko-testimonials__nav">
            <?php foreach ( $testimonials as $i => $t ) : ?>
                <button
                    class="kaiko-testimonials__dot<?php echo 0 === $i ? ' active' : ''; ?>"
                    data-slide="<?php echo $i; ?>"
                    aria-label="Testimonial <?php echo $i + 1; ?>"
                ></button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


/* ============================================
   SHORTCODE: [kaiko_value_props]
   Renders value proposition items for homepage/about.
   
   Attributes:
     layout — horizontal|vertical (default: vertical)
   ============================================ */

add_shortcode( 'kaiko_value_props', 'kaiko_value_props_shortcode' );

function kaiko_value_props_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'layout' => 'vertical',
    ), $atts, 'kaiko_value_props' );

    $props = array(
        array(
            'icon'  => '🔬',
            'title' => 'Species-Specific Curation',
            'text'  => 'Products matched to exact species requirements, not generic "one size fits all" recommendations.',
        ),
        array(
            'icon'  => '🏆',
            'title' => 'Premium Quality Only',
            'text'  => 'No budget filler — only equipment we\'d use in our own setups. Tested by real keepers.',
        ),
        array(
            'icon'  => '📦',
            'title' => 'Expert Support',
            'text'  => 'Real keepers answering your questions. Species-specific advice, not scripts.',
        ),
    );

    $props = apply_filters( 'kaiko_value_props', $props );

    ob_start();
    ?>
    <div class="kaiko-value-props kaiko-value-props--<?php echo esc_attr( $atts['layout'] ); ?> kaiko-stagger">
        <?php foreach ( $props as $prop ) : ?>
        <div class="kaiko-value-prop kaiko-reveal">
            <span class="kaiko-value-prop__icon"><?php echo $prop['icon']; ?></span>
            <div class="kaiko-value-prop__content">
                <h4 class="kaiko-value-prop__title"><?php echo esc_html( $prop['title'] ); ?></h4>
                <p class="kaiko-value-prop__text"><?php echo esc_html( $prop['text'] ); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
