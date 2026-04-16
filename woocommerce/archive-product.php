<?php
/**
 * Kaiko — Shop / Category Archive
 *
 * Override of WooCommerce archive-product.php
 * Uses Kaiko shell (nav + footer) for visual consistency with homepage.
 * Adds: AJAX filtering bar, species/difficulty filters, animated grid, parallax category header
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/kaiko-page-open' );

do_action( 'woocommerce_before_main_content' );
?>

<!-- Page Hero -->
<div class="kaiko-page-hero">
    <div class="kaiko-page-hero__tag kaiko-reveal">Our Collection</div>
    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
        <h1 class="kaiko-page-hero__title kaiko-hero-title"><?php woocommerce_page_title(); ?></h1>
    <?php endif; ?>
    <?php if ( is_product_category() ) : ?>
        <div class="kaiko-page-hero__subtitle"><?php do_action( 'woocommerce_archive_description' ); ?></div>
    <?php else : ?>
        <p class="kaiko-page-hero__subtitle">Premium habitat equipment designed by reptile enthusiasts, for reptile enthusiasts.</p>
    <?php endif; ?>
</div>

<div class="kaiko-shop-wrapper">

    <!-- Filter Bar -->
    <div class="kaiko-filter-bar kaiko-reveal" id="kaiko-filter-bar">
        <div class="kaiko-filter-bar__inner">

            <!-- Category filter -->
            <div class="kaiko-filter-group">
                <label class="kaiko-filter-label">Category</label>
                <select id="kaiko-filter-category" class="kaiko-filter-select">
                    <option value="">All Products</option>
                    <?php
                    $cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0 ) );
                    if ( ! is_wp_error( $cats ) ) :
                        foreach ( $cats as $cat ) :
                            $selected = ( is_product_category( $cat->slug ) ) ? 'selected' : '';
                            echo '<option value="' . esc_attr( $cat->slug ) . '" ' . $selected . '>' . esc_html( $cat->name ) . '</option>';
                        endforeach;
                    endif;
                    ?>
                </select>
            </div>

            <!-- Species filter -->
            <div class="kaiko-filter-group">
                <label class="kaiko-filter-label">Species</label>
                <select id="kaiko-filter-species" class="kaiko-filter-select">
                    <option value="">All Species</option>
                    <option value="Bearded Dragon">Bearded Dragon</option>
                    <option value="Leopard Gecko">Leopard Gecko</option>
                    <option value="Crested Gecko">Crested Gecko</option>
                    <option value="Ball Python">Ball Python</option>
                    <option value="Corn Snake">Corn Snake</option>
                    <option value="Blue Tongue Skink">Blue Tongue Skink</option>
                    <option value="Chameleon">Chameleon</option>
                    <option value="Tortoise">Tortoise</option>
                </select>
            </div>

            <!-- Difficulty filter -->
            <div class="kaiko-filter-group">
                <label class="kaiko-filter-label">Difficulty</label>
                <select id="kaiko-filter-difficulty" class="kaiko-filter-select">
                    <option value="">All Levels</option>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>

            <!-- Sort -->
            <div class="kaiko-filter-group">
                <label class="kaiko-filter-label">Sort</label>
                <select id="kaiko-filter-sort" class="kaiko-filter-select">
                    <option value="date">Newest</option>
                    <option value="title">Name A-Z</option>
                    <option value="price">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                </select>
            </div>

            <button id="kaiko-filter-reset" class="kaiko-btn kaiko-btn-ghost" style="display:none;">Clear Filters</button>

        </div>

        <div class="kaiko-filter-bar__count">
            <span id="kaiko-product-count"><?php echo esc_html( wc_get_loop_prop( 'total' ) ); ?></span> products
        </div>
    </div>

    <!-- Product Grid -->
    <div class="kaiko-product-grid" id="kaiko-product-grid">
        <?php
        if ( woocommerce_product_loop() ) {
            do_action( 'woocommerce_before_shop_loop' );
            woocommerce_product_loop_start();

            if ( wc_get_loop_prop( 'total' ) ) {
                while ( have_posts() ) {
                    the_post();
                    do_action( 'woocommerce_shop_loop' );
                    wc_get_template_part( 'content', 'product' );
                }
            }

            woocommerce_product_loop_end();
            do_action( 'woocommerce_after_shop_loop' );
        } else {
            echo '<div class="kaiko-no-products kaiko-reveal">';
            echo '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
            echo '<p>No products found matching your criteria.</p>';
            echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="btn-primary" style="margin-top:16px;">Browse All Products</a>';
            echo '</div>';
        }
        ?>
    </div>

</div>

<?php
do_action( 'woocommerce_after_main_content' );

get_template_part( 'template-parts/kaiko-page-close' );
