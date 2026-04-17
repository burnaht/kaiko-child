<?php
/**
 * Kaiko — Single Product Page
 *
 * Wraps WooCommerce single-product in the branded Kaiko shell and layout.
 *
 * Visual language matches the sample at
 * /previews/kaiko-product-preview.html — sticky gallery (left),
 * branded buy panel (right) with wholesale tier table, quantity stepper
 * + live total, trust strip, tabs, related products.
 *
 * Access states:
 *  - approved    : full tier table + buy form
 *  - pending     : "Trade pricing unlocks once approved" card
 *  - logged-out  : "Log in for wholesale pricing" card
 *
 * @package KaikoChild
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/kaiko-page-open' );

// --- REMOVE DEFAULT WC HOOKS WE'RE REPLACING (scoped to this page only) ---
// Title/rating/price/excerpt/add-to-cart/meta — we render these manually so
// we control the order and styling. We leave sharing ($10 → 50) alone.
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

// Also remove the tabs that render by default after summary — we use our
// own tab shell so we can style + order them.
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

// Access state helper
$kaiko_can_buy       = function_exists( 'kaiko_user_can_see_prices' ) ? kaiko_user_can_see_prices() : is_user_logged_in();
$kaiko_is_pending    = is_user_logged_in() && ! $kaiko_can_buy;
$kaiko_is_loggedout  = ! is_user_logged_in();
?>

<?php while ( have_posts() ) : the_post(); global $product; ?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'kaiko-product', $product ); ?>>

    <!-- Breadcrumbs -->
    <nav class="kaiko-product__breadcrumb" aria-label="Breadcrumb">
        <div class="kaiko-product__container">
            <?php woocommerce_breadcrumb( array(
                'delimiter'   => '<span class="kaiko-bc-sep">/</span>',
                'wrap_before' => '<div class="kaiko-bc">',
                'wrap_after'  => '</div>',
            ) ); ?>
        </div>
    </nav>

    <div class="kaiko-product__container kaiko-product__layout">

        <!-- =================================================
             GALLERY (sticky on desktop)
             ================================================= -->
        <div class="kaiko-product__gallery">
            <?php
            /**
             * @hooked woocommerce_show_product_sale_flash - 10
             * @hooked woocommerce_show_product_images - 20
             */
            do_action( 'woocommerce_before_single_product_summary' );
            ?>
        </div>

        <!-- =================================================
             SUMMARY / BUY PANEL
             ================================================= -->
        <div class="kaiko-product__summary">

            <?php
            // Category eyebrow
            $terms = get_the_terms( $product->get_id(), 'product_cat' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $primary_term = reset( $terms );
                echo '<p class="kaiko-product__eyebrow">' . esc_html( strtoupper( $primary_term->name ) ) . '</p>';
            }
            ?>

            <h1 class="kaiko-product__title"><?php the_title(); ?></h1>

            <?php
            // Rating (only if there are reviews)
            if ( wc_review_ratings_enabled() ) {
                $rating_count = $product->get_rating_count();
                $review_count = $product->get_review_count();
                $avg          = $product->get_average_rating();
                if ( $rating_count > 0 ) {
                    echo '<div class="kaiko-product__rating">';
                    echo wc_get_rating_html( $avg );
                    echo '<span class="kaiko-product__rating-meta"><strong>' . esc_html( $avg ) . '</strong> · ' . esc_html( $review_count ) . ' ' . _n( 'review', 'reviews', $review_count, 'kaiko-child' ) . '</span>';
                    echo '</div>';
                }
            }
            ?>

            <?php if ( $product->get_short_description() ) : ?>
                <div class="kaiko-product__pitch">
                    <?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
                </div>
            <?php endif; ?>

            <?php if ( $kaiko_is_loggedout ) : ?>
                <!-- LOGGED-OUT STATE -->
                <div class="kaiko-product__gate kaiko-product__gate--loggedout">
                    <div class="kaiko-product__gate-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </div>
                    <div class="kaiko-product__gate-body">
                        <h3>Trade pricing locked</h3>
                        <p>Wholesale pricing is visible to approved trade partners only. Log in or apply for a trade account to see our volume tiers.</p>
                        <div class="kaiko-product__gate-actions">
                            <a class="kaiko-btn kaiko-btn-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">Log in</a>
                            <a class="kaiko-btn kaiko-btn-ghost" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>#apply">Apply for trade access</a>
                        </div>
                    </div>
                </div>

            <?php elseif ( $kaiko_is_pending ) : ?>
                <!-- PENDING STATE -->
                <div class="kaiko-product__gate kaiko-product__gate--pending">
                    <div class="kaiko-product__gate-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="kaiko-product__gate-body">
                        <h3>Approval pending</h3>
                        <p>Your trade account is with our team for review — usually within 24 hours. Once approved, full wholesale pricing unlocks across the catalogue.</p>
                        <div class="kaiko-product__gate-actions">
                            <a class="kaiko-btn kaiko-btn-ghost" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">My account</a>
                        </div>
                    </div>
                </div>

            <?php else : ?>

                <?php
                // =========================================
                // WHOLESALE TIER TABLE (approved only)
                // =========================================
                $tiers = function_exists( 'kaiko_get_product_tiers' ) ? kaiko_get_product_tiers( $product->get_id() ) : array();

                if ( ! empty( $tiers ) ) :
                    $base_price = (float) $product->get_price();
                ?>
                    <div class="kaiko-tiers" data-kaiko-tiers='<?php echo esc_attr( wp_json_encode( $tiers ) ); ?>' data-base-price="<?php echo esc_attr( $base_price ); ?>">
                        <div class="kaiko-tiers__head">
                            <span class="kaiko-tiers__eyebrow">Wholesale pricing</span>
                            <span class="kaiko-tiers__note">Buy more, save more</span>
                        </div>
                        <table class="kaiko-tiers__table">
                            <thead>
                                <tr>
                                    <th>Quantity</th>
                                    <th>Unit price</th>
                                    <th>Saving</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $tiers as $i => $tier ) :
                                    $qty_label = $tier['max_qty']
                                        ? esc_html( $tier['min_qty'] . '–' . $tier['max_qty'] )
                                        : esc_html( $tier['min_qty'] . '+' );
                                    $saving = 0;
                                    if ( $base_price > 0 ) {
                                        $saving = round( ( ( $base_price - (float) $tier['unit_price'] ) / $base_price ) * 100 );
                                    }
                                ?>
                                    <tr class="kaiko-tiers__row" data-min="<?php echo esc_attr( $tier['min_qty'] ); ?>" data-max="<?php echo esc_attr( $tier['max_qty'] ); ?>" data-price="<?php echo esc_attr( $tier['unit_price'] ); ?>">
                                        <td><?php echo $qty_label; ?></td>
                                        <td><?php echo wp_kses_post( wc_price( $tier['unit_price'] ) ); ?></td>
                                        <td>
                                            <?php if ( $saving > 0 ) : ?>
                                                <span class="kaiko-tiers__saving">−<?php echo esc_html( $saving ); ?>%</span>
                                            <?php else : ?>
                                                <span class="kaiko-tiers__saving kaiko-tiers__saving--none">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Buy form (WooCommerce handles simple/variable/grouped natively) -->
                <div class="kaiko-product__buy">
                    <?php woocommerce_template_single_add_to_cart(); ?>

                    <!-- Running total -->
                    <div class="kaiko-product__running-total" aria-live="polite">
                        <div class="kaiko-product__running-total-row">
                            <span class="kaiko-product__running-total-label">Order total</span>
                            <span class="kaiko-product__running-total-value" data-kaiko-total>
                                <?php echo wp_kses_post( wc_price( (float) $product->get_price() ) ); ?>
                            </span>
                        </div>
                        <div class="kaiko-product__running-total-save" data-kaiko-save hidden>
                            <span class="kaiko-product__save-pill" data-kaiko-save-pill></span>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <!-- Trust strip (always visible) -->
            <ul class="kaiko-product__trust" role="list">
                <li>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <div><strong>Free UK shipping</strong><span>On every trade order</span></div>
                </li>
                <li>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div><strong>Dispatched within 48h</strong><span>Tracked delivery</span></div>
                </li>
                <li>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 11-9-9c2.39 0 4.68.94 6.4 2.6L21 8"/><polyline points="21 3 21 8 16 8"/></svg>
                    <div><strong>30-day returns</strong><span>No-quibble guarantee</span></div>
                </li>
            </ul>

            <!-- Meta grid -->
            <dl class="kaiko-product__meta-grid">
                <?php if ( $product->get_sku() ) : ?>
                    <div><dt>SKU</dt><dd><?php echo esc_html( $product->get_sku() ); ?></dd></div>
                <?php endif; ?>
                <?php
                $carton = get_field( 'carton_qty' );
                if ( $carton ) : ?>
                    <div><dt>Carton qty</dt><dd><?php echo esc_html( $carton ); ?> units</dd></div>
                <?php endif; ?>
                <?php if ( $product->managing_stock() || $product->is_in_stock() ) :
                    $stock_label = $product->is_in_stock() ? __( 'In stock', 'kaiko-child' ) : __( 'Out of stock', 'kaiko-child' );
                    if ( $product->managing_stock() && $product->get_stock_quantity() ) {
                        $stock_label = sprintf( _n( '%d in stock', '%d in stock', $product->get_stock_quantity(), 'kaiko-child' ), $product->get_stock_quantity() );
                    }
                ?>
                    <div><dt>Availability</dt><dd class="kaiko-product__stock <?php echo $product->is_in_stock() ? 'is-in' : 'is-out'; ?>"><?php echo esc_html( $stock_label ); ?></dd></div>
                <?php endif; ?>
                <?php
                $lead = get_field( 'lead_time' );
                if ( $lead ) : ?>
                    <div><dt>Lead time</dt><dd><?php echo esc_html( $lead ); ?></dd></div>
                <?php endif; ?>
            </dl>

        </div>

    </div>

    <!-- =================================================
         TABBED DETAIL SECTION
         ================================================= -->
    <section class="kaiko-product__details">
        <div class="kaiko-product__container">

            <?php
            // Build tab list. Standard WC tabs gives us description, additional
            // info, and reviews — we filter to reorder/rename and we'll inject
            // our ACF-driven ones.
            $tabs = array();

            if ( $product->get_description() ) {
                $tabs['description'] = array(
                    'title'    => __( 'Description', 'kaiko-child' ),
                    'callback' => function() { echo wpautop( wp_kses_post( get_the_content() ) ); },
                );
            }

            // Specifications (ACF-driven, same data as hardware specs hook)
            $dims     = get_field( 'dimensions' );
            $power    = get_field( 'power_requirements' );
            $material = get_field( 'material' );
            $weight   = get_field( 'weight_kg' );
            if ( ! empty( $dims['length'] ) || ! empty( $power ) || ! empty( $material ) || ! empty( $weight ) ) {
                $tabs['specs'] = array(
                    'title'    => __( 'Specifications', 'kaiko-child' ),
                    'callback' => function() use ( $dims, $power, $material, $weight ) {
                        echo '<table class="kaiko-spec-table"><tbody>';
                        if ( ! empty( $dims['length'] ) ) {
                            echo '<tr><th>Dimensions (L×W×H)</th><td>' . esc_html( "{$dims['length']} × {$dims['width']} × {$dims['height']} cm" ) . '</td></tr>';
                        }
                        if ( ! empty( $material ) ) echo '<tr><th>Material</th><td>' . esc_html( ucfirst( $material ) ) . '</td></tr>';
                        if ( ! empty( $weight ) )   echo '<tr><th>Weight</th><td>' . esc_html( $weight ) . ' kg</td></tr>';
                        if ( ! empty( $power ) )    echo '<tr><th>Power</th><td>' . esc_html( $power ) . '</td></tr>';
                        echo '</tbody></table>';
                    },
                );
            }

            // Species compatibility
            $species = get_field( 'compatible_species' );
            if ( ! empty( $species ) ) {
                $tabs['species'] = array(
                    'title'    => __( 'Species Compatibility', 'kaiko-child' ),
                    'callback' => function() use ( $species ) {
                        echo '<div class="kaiko-species-grid">';
                        foreach ( $species as $row ) {
                            $lvl = sanitize_html_class( $row['compatibility_level'] );
                            echo '<div class="kaiko-species-card kaiko-species-card--' . $lvl . '">';
                            echo '<div class="kaiko-species-card__top">';
                            echo '<h4>' . esc_html( $row['species_name'] ) . '</h4>';
                            echo '<span class="kaiko-species-card__pill">' . esc_html( ucfirst( $row['compatibility_level'] ) ) . '</span>';
                            echo '</div>';
                            if ( ! empty( $row['species_scientific'] ) ) {
                                echo '<p class="kaiko-species-card__sci"><em>' . esc_html( $row['species_scientific'] ) . '</em></p>';
                            }
                            if ( ! empty( $row['compatibility_notes'] ) ) {
                                echo '<p class="kaiko-species-card__notes">' . esc_html( $row['compatibility_notes'] ) . '</p>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    },
                );
            }

            // Shipping & returns
            $tabs['shipping'] = array(
                'title'    => __( 'Shipping &amp; Returns', 'kaiko-child' ),
                'callback' => function() {
                    ?>
                    <table class="kaiko-spec-table">
                        <tbody>
                            <tr><th>UK shipping</th><td>Free on every trade order — tracked 24/48h service.</td></tr>
                            <tr><th>Dispatch</th><td>Within 48 hours, Mon–Fri.</td></tr>
                            <tr><th>Returns</th><td>30-day no-quibble returns on unopened stock.</td></tr>
                            <tr><th>International</th><td>Quote on request — contact <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a>.</td></tr>
                        </tbody>
                    </table>
                    <?php
                },
            );

            // Trade reviews (WC reviews)
            if ( comments_open() || get_comments_number() ) {
                $tabs['reviews'] = array(
                    'title'    => sprintf( __( 'Trade Reviews (%d)', 'kaiko-child' ), $product->get_review_count() ),
                    'callback' => function() { comments_template(); },
                );
            }
            ?>

            <nav class="kaiko-product__tabs" role="tablist">
                <?php $first = true; foreach ( $tabs as $key => $tab ) : ?>
                    <button
                        type="button"
                        class="kaiko-product__tab <?php echo $first ? 'is-active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
                        aria-controls="kaiko-tab-<?php echo esc_attr( $key ); ?>"
                        data-tab="<?php echo esc_attr( $key ); ?>">
                        <?php echo esc_html( $tab['title'] ); ?>
                    </button>
                <?php $first = false; endforeach; ?>
            </nav>

            <div class="kaiko-product__tab-panels">
                <?php $first = true; foreach ( $tabs as $key => $tab ) : ?>
                    <div
                        id="kaiko-tab-<?php echo esc_attr( $key ); ?>"
                        class="kaiko-product__panel <?php echo $first ? 'is-active' : ''; ?>"
                        role="tabpanel"
                        <?php echo $first ? '' : 'hidden'; ?>>
                        <?php call_user_func( $tab['callback'] ); ?>
                    </div>
                <?php $first = false; endforeach; ?>
            </div>

        </div>
    </section>

    <!-- =================================================
         RELATED PRODUCTS
         ================================================= -->
    <section class="kaiko-product__related">
        <div class="kaiko-product__container">
            <?php
            // Re-run the related products action; we removed the tabs action
            // above but upsells + related are still attached.
            woocommerce_upsell_display();
            woocommerce_output_related_products();
            ?>
        </div>
    </section>

</div>

<?php endwhile; ?>

<?php
// Inline product-page CSS — scoped to body.kaiko-product-page so it
// never bleeds out. Matches the design tokens used by page-about /
// template-homepage.
?>
<style id="kaiko-product-css">
body.kaiko-product-page {
    --k-teal: #1a5c52;
    --k-deep-teal: #134840;
    --k-lime: #b8d435;
    --k-gold: #c89b3c;

    --k-ink: #1C1917;
    --k-text: #44403C;
    --k-muted: #78716C;
    --k-soft: #A8A29E;
    --k-border: #E7E5E4;
    --k-border-light: #F5F5F4;
    --k-bg: #FFFFFF;
    --k-bg-2: #FAFAF9;

    --k-font-display: 'Gotham', 'Space Grotesk', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --k-font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

    --k-r-sm: 8px;
    --k-r-md: 12px;
    --k-r-lg: 18px;
    --k-r-xl: 24px;

    --k-shadow-sm: 0 2px 8px rgba(28,25,23,0.04);
    --k-shadow-md: 0 8px 32px rgba(28,25,23,0.06);
}

/* Shell */
body.kaiko-product-page .kaiko-product__container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 32px;
}

/* Breadcrumb */
body.kaiko-product-page .kaiko-product__breadcrumb {
    padding: 24px 0 4px;
    border-bottom: 1px solid var(--k-border-light);
    background: var(--k-bg-2);
}
body.kaiko-product-page .kaiko-bc {
    font-family: var(--k-font-body);
    font-size: 0.82rem;
    color: var(--k-muted);
    letter-spacing: 0.02em;
}
body.kaiko-product-page .kaiko-bc a { color: var(--k-muted); text-decoration: none; transition: color .2s; }
body.kaiko-product-page .kaiko-bc a:hover { color: var(--k-teal); }
body.kaiko-product-page .kaiko-bc-sep { margin: 0 10px; color: var(--k-soft); }

/* Two-column layout */
body.kaiko-product-page .kaiko-product__layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
    padding: 56px 32px 72px;
    max-width: 1320px;
    margin: 0 auto;
}
@media (min-width: 1024px) {
    body.kaiko-product-page .kaiko-product__layout {
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
        gap: 72px;
    }
}

/* Gallery */
body.kaiko-product-page .kaiko-product__gallery {
    position: relative;
}
@media (min-width: 1024px) {
    body.kaiko-product-page .kaiko-product__gallery {
        position: sticky;
        top: 100px;
        align-self: start;
    }
}
body.kaiko-product-page .kaiko-product__gallery .woocommerce-product-gallery {
    width: 100% !important;
    margin: 0 !important;
    float: none !important;
}
body.kaiko-product-page .kaiko-product__gallery .woocommerce-product-gallery__image {
    border-radius: var(--k-r-xl);
    overflow: hidden;
    background: var(--k-bg-2);
    border: 1px solid var(--k-border);
}
body.kaiko-product-page .kaiko-product__gallery .flex-control-thumbs {
    margin-top: 14px !important;
    gap: 10px;
    display: flex;
    flex-wrap: wrap;
}
body.kaiko-product-page .kaiko-product__gallery .flex-control-thumbs li {
    width: calc(20% - 8px) !important;
    margin: 0 !important;
}
body.kaiko-product-page .kaiko-product__gallery .flex-control-thumbs img {
    border-radius: var(--k-r-md);
    border: 1.5px solid var(--k-border);
    transition: border-color .2s, transform .2s;
    opacity: .85;
}
body.kaiko-product-page .kaiko-product__gallery .flex-control-thumbs img:hover,
body.kaiko-product-page .kaiko-product__gallery .flex-control-thumbs img.flex-active {
    border-color: var(--k-teal);
    opacity: 1;
}

/* Summary */
body.kaiko-product-page .kaiko-product__eyebrow {
    font-family: var(--k-font-display);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    color: var(--k-teal);
    text-transform: uppercase;
    margin: 0 0 12px;
}
body.kaiko-product-page .kaiko-product__title {
    font-family: var(--k-font-display);
    font-size: clamp(1.9rem, 3vw, 2.5rem);
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: -0.01em;
    color: var(--k-ink);
    margin: 0 0 18px;
}
body.kaiko-product-page .kaiko-product__rating {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
body.kaiko-product-page .kaiko-product__rating .star-rating {
    color: var(--k-gold);
    font-size: 0.9rem;
}
body.kaiko-product-page .kaiko-product__rating-meta {
    font-size: 0.85rem;
    color: var(--k-muted);
}
body.kaiko-product-page .kaiko-product__rating-meta strong {
    color: var(--k-ink);
    font-weight: 600;
}
body.kaiko-product-page .kaiko-product__pitch {
    font-size: 1rem;
    line-height: 1.65;
    color: var(--k-text);
    margin: 0 0 28px;
    padding-bottom: 28px;
    border-bottom: 1px solid var(--k-border-light);
}
body.kaiko-product-page .kaiko-product__pitch p:last-child { margin-bottom: 0; }

/* Gate cards (logged-out / pending) */
body.kaiko-product-page .kaiko-product__gate {
    display: flex;
    gap: 18px;
    padding: 24px 24px;
    border-radius: var(--k-r-lg);
    border: 1px solid rgba(26,92,82,0.16);
    background: linear-gradient(180deg, rgba(184,212,53,0.06) 0%, rgba(26,92,82,0.03) 100%);
    margin: 0 0 24px;
}
body.kaiko-product-page .kaiko-product__gate--loggedout {
    border-color: rgba(26,92,82,0.2);
}
body.kaiko-product-page .kaiko-product__gate--pending {
    border-color: rgba(200,155,60,0.28);
    background: linear-gradient(180deg, rgba(200,155,60,0.08) 0%, rgba(200,155,60,0.03) 100%);
}
body.kaiko-product-page .kaiko-product__gate-icon {
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(26,92,82,0.1);
    color: var(--k-teal);
    display: flex;
    align-items: center;
    justify-content: center;
}
body.kaiko-product-page .kaiko-product__gate--pending .kaiko-product__gate-icon {
    background: rgba(200,155,60,0.12);
    color: var(--k-gold);
}
body.kaiko-product-page .kaiko-product__gate-body { flex: 1; min-width: 0; }
body.kaiko-product-page .kaiko-product__gate-body h3 {
    font-family: var(--k-font-display);
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 6px;
    color: var(--k-ink);
    letter-spacing: 0.005em;
}
body.kaiko-product-page .kaiko-product__gate-body p {
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--k-text);
    margin: 0 0 16px;
}
body.kaiko-product-page .kaiko-product__gate-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Wholesale tier table */
body.kaiko-product-page .kaiko-tiers {
    background: var(--k-bg-2);
    border: 1px solid var(--k-border);
    border-radius: var(--k-r-lg);
    padding: 20px 22px;
    margin: 0 0 20px;
}
body.kaiko-product-page .kaiko-tiers__head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 14px;
}
body.kaiko-product-page .kaiko-tiers__eyebrow {
    font-family: var(--k-font-display);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--k-teal);
}
body.kaiko-product-page .kaiko-tiers__note {
    font-size: 0.78rem;
    color: var(--k-muted);
    letter-spacing: 0.02em;
}
body.kaiko-product-page .kaiko-tiers__table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.92rem;
}
body.kaiko-product-page .kaiko-tiers__table th {
    font-family: var(--k-font-display);
    font-weight: 600;
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--k-muted);
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid var(--k-border);
}
body.kaiko-product-page .kaiko-tiers__table td {
    padding: 12px;
    color: var(--k-text);
    border-bottom: 1px solid var(--k-border-light);
}
body.kaiko-product-page .kaiko-tiers__table tbody tr:last-child td { border-bottom: 0; }
body.kaiko-product-page .kaiko-tiers__row.is-active {
    background: rgba(184,212,53,0.14);
}
body.kaiko-product-page .kaiko-tiers__row.is-active td { color: var(--k-ink); font-weight: 600; }
body.kaiko-product-page .kaiko-tiers__saving {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    background: rgba(184,212,53,0.22);
    color: var(--k-deep-teal);
}
body.kaiko-product-page .kaiko-tiers__saving--none { background: transparent; color: var(--k-soft); font-weight: 500; }

/* Buy form */
body.kaiko-product-page .kaiko-product__buy form.cart {
    margin: 0 0 20px;
    padding: 22px;
    background: var(--k-bg);
    border: 1px solid var(--k-border);
    border-radius: var(--k-r-lg);
    box-shadow: var(--k-shadow-sm);
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: center;
}
body.kaiko-product-page .kaiko-product__buy form.cart .variations {
    width: 100%;
    margin: 0 0 8px;
}
body.kaiko-product-page .kaiko-product__buy form.cart .variations td { padding: 8px 8px 8px 0; }
body.kaiko-product-page .kaiko-product__buy form.cart .variations label {
    font-family: var(--k-font-display);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--k-muted);
}
body.kaiko-product-page .kaiko-product__buy form.cart .variations select {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--k-border);
    border-radius: var(--k-r-md);
    font-family: var(--k-font-body);
    font-size: 0.95rem;
    background: var(--k-bg);
    color: var(--k-ink);
}
body.kaiko-product-page .kaiko-product__buy form.cart .quantity {
    display: inline-flex;
    align-items: stretch;
    border: 1px solid var(--k-border);
    border-radius: var(--k-r-md);
    overflow: hidden;
    background: var(--k-bg);
}
body.kaiko-product-page .kaiko-product__buy form.cart .quantity input.qty {
    width: 56px;
    text-align: center;
    border: 0;
    font-family: var(--k-font-body);
    font-weight: 600;
    font-size: 1rem;
    color: var(--k-ink);
    background: transparent;
    padding: 12px 0;
    -moz-appearance: textfield;
}
body.kaiko-product-page .kaiko-product__buy form.cart .quantity input.qty::-webkit-outer-spin-button,
body.kaiko-product-page .kaiko-product__buy form.cart .quantity input.qty::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
body.kaiko-product-page .kaiko-product__buy form.cart button.single_add_to_cart_button {
    flex: 1;
    min-width: 180px;
    background: var(--k-teal) !important;
    color: #fff !important;
    border: 0 !important;
    padding: 14px 26px !important;
    font-family: var(--k-font-body) !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-radius: var(--k-r-md) !important;
    cursor: pointer;
    transition: background .2s, transform .15s;
}
body.kaiko-product-page .kaiko-product__buy form.cart button.single_add_to_cart_button:hover {
    background: var(--k-deep-teal) !important;
    transform: translateY(-1px);
}
body.kaiko-product-page .kaiko-product__buy .woocommerce-variation-add-to-cart { display: contents; }
body.kaiko-product-page .kaiko-product__buy .woocommerce-variation-price .price {
    font-family: var(--k-font-display);
    font-size: 1.3rem;
    color: var(--k-ink);
    font-weight: 700;
}

/* Running total */
body.kaiko-product-page .kaiko-product__running-total {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 16px 22px;
    background: linear-gradient(180deg, #FAFAF9 0%, #F5F5F4 100%);
    border-radius: var(--k-r-md);
    border: 1px solid var(--k-border);
    margin-bottom: 28px;
}
body.kaiko-product-page .kaiko-product__running-total-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
}
body.kaiko-product-page .kaiko-product__running-total-label {
    font-family: var(--k-font-display);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--k-muted);
}
body.kaiko-product-page .kaiko-product__running-total-value {
    font-family: var(--k-font-display);
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--k-ink);
    letter-spacing: -0.005em;
}
body.kaiko-product-page .kaiko-product__save-pill {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--k-deep-teal);
    background: rgba(184,212,53,0.25);
    padding: 3px 12px;
    border-radius: 999px;
}

/* Trust strip */
body.kaiko-product-page .kaiko-product__trust {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    padding: 0;
    margin: 0 0 28px;
    list-style: none;
}
body.kaiko-product-page .kaiko-product__trust li {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding: 14px 14px;
    background: var(--k-bg-2);
    border: 1px solid var(--k-border-light);
    border-radius: var(--k-r-md);
}
body.kaiko-product-page .kaiko-product__trust li svg { color: var(--k-teal); flex: 0 0 18px; margin-top: 2px; }
body.kaiko-product-page .kaiko-product__trust li strong {
    display: block;
    font-family: var(--k-font-display);
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--k-ink);
    margin-bottom: 2px;
}
body.kaiko-product-page .kaiko-product__trust li span {
    font-size: 0.75rem;
    color: var(--k-muted);
    line-height: 1.4;
}
@media (max-width: 720px) {
    body.kaiko-product-page .kaiko-product__trust { grid-template-columns: 1fr; }
}

/* Meta grid */
body.kaiko-product-page .kaiko-product__meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 28px;
    margin: 0;
    padding-top: 24px;
    border-top: 1px solid var(--k-border-light);
}
body.kaiko-product-page .kaiko-product__meta-grid > div { margin: 0; }
body.kaiko-product-page .kaiko-product__meta-grid dt {
    font-family: var(--k-font-display);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--k-muted);
    margin-bottom: 4px;
}
body.kaiko-product-page .kaiko-product__meta-grid dd {
    font-size: 0.92rem;
    color: var(--k-ink);
    font-weight: 500;
    margin: 0;
}
body.kaiko-product-page .kaiko-product__stock.is-in { color: #0a7a4b; }
body.kaiko-product-page .kaiko-product__stock.is-out { color: #b43b3b; }

/* =====================================================
   TABS
   ===================================================== */
body.kaiko-product-page .kaiko-product__details {
    background: var(--k-bg-2);
    padding: 56px 0 64px;
    border-top: 1px solid var(--k-border-light);
}
body.kaiko-product-page .kaiko-product__tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    border-bottom: 1px solid var(--k-border);
    margin-bottom: 36px;
    overflow-x: auto;
}
body.kaiko-product-page .kaiko-product__tab {
    flex-shrink: 0;
    background: transparent;
    border: 0;
    border-bottom: 2px solid transparent;
    padding: 14px 22px;
    font-family: var(--k-font-display);
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--k-muted);
    cursor: pointer;
    transition: color .2s, border-color .2s;
}
body.kaiko-product-page .kaiko-product__tab:hover { color: var(--k-ink); }
body.kaiko-product-page .kaiko-product__tab.is-active {
    color: var(--k-teal);
    border-bottom-color: var(--k-teal);
}
body.kaiko-product-page .kaiko-product__panel { display: none; }
body.kaiko-product-page .kaiko-product__panel.is-active { display: block; animation: kFadeIn .3s ease; }
@keyframes kFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
body.kaiko-product-page .kaiko-product__panel { font-size: 1rem; line-height: 1.7; color: var(--k-text); max-width: 920px; }
body.kaiko-product-page .kaiko-product__panel p { margin: 0 0 1em; }
body.kaiko-product-page .kaiko-product__panel h2, body.kaiko-product-page .kaiko-product__panel h3 {
    font-family: var(--k-font-display);
    color: var(--k-ink);
    letter-spacing: -0.005em;
}

/* Spec table */
body.kaiko-product-page .kaiko-spec-table {
    width: 100%;
    max-width: 720px;
    border-collapse: separate;
    border-spacing: 0;
    background: var(--k-bg);
    border: 1px solid var(--k-border);
    border-radius: var(--k-r-md);
    overflow: hidden;
}
body.kaiko-product-page .kaiko-spec-table th,
body.kaiko-product-page .kaiko-spec-table td {
    padding: 14px 20px;
    text-align: left;
    border-bottom: 1px solid var(--k-border-light);
}
body.kaiko-product-page .kaiko-spec-table tr:last-child th,
body.kaiko-product-page .kaiko-spec-table tr:last-child td { border-bottom: 0; }
body.kaiko-product-page .kaiko-spec-table th {
    font-family: var(--k-font-display);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--k-muted);
    background: var(--k-bg-2);
    width: 40%;
}
body.kaiko-product-page .kaiko-spec-table td { color: var(--k-ink); }

/* Species cards */
body.kaiko-product-page .kaiko-species-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px;
    max-width: 1000px;
}
body.kaiko-product-page .kaiko-species-card {
    background: var(--k-bg);
    border: 1px solid var(--k-border);
    border-radius: var(--k-r-md);
    padding: 18px 20px;
}
body.kaiko-product-page .kaiko-species-card__top {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 6px;
}
body.kaiko-product-page .kaiko-species-card h4 {
    font-family: var(--k-font-display);
    font-size: 1rem;
    font-weight: 700;
    color: var(--k-ink);
    margin: 0;
    letter-spacing: -0.005em;
}
body.kaiko-product-page .kaiko-species-card__pill {
    display: inline-block;
    font-family: var(--k-font-display);
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 999px;
}
body.kaiko-product-page .kaiko-species-card--full .kaiko-species-card__pill { background: rgba(184,212,53,0.22); color: var(--k-deep-teal); }
body.kaiko-product-page .kaiko-species-card--partial .kaiko-species-card__pill { background: rgba(200,155,60,0.18); color: var(--k-gold); }
body.kaiko-product-page .kaiko-species-card--supervised .kaiko-species-card__pill { background: var(--k-border); color: var(--k-muted); }
body.kaiko-product-page .kaiko-species-card__sci { font-size: 0.82rem; color: var(--k-muted); margin: 0 0 8px; }
body.kaiko-product-page .kaiko-species-card__notes { font-size: 0.88rem; color: var(--k-text); line-height: 1.55; margin: 0; }

/* Related products */
body.kaiko-product-page .kaiko-product__related { padding: 64px 0 80px; background: var(--k-bg); }
body.kaiko-product-page .kaiko-product__related h2 {
    font-family: var(--k-font-display);
    font-size: clamp(1.6rem, 2.5vw, 2rem);
    font-weight: 700;
    letter-spacing: -0.005em;
    color: var(--k-ink);
    margin: 0 0 32px;
}
body.kaiko-product-page .kaiko-product__related ul.products {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
    gap: 24px !important;
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
body.kaiko-product-page .kaiko-product__related ul.products li.product {
    width: auto !important;
    float: none !important;
    margin: 0 !important;
}

/* Mobile tweaks */
@media (max-width: 720px) {
    body.kaiko-product-page .kaiko-product__container { padding: 0 20px; }
    body.kaiko-product-page .kaiko-product__layout { padding: 32px 20px 56px; gap: 36px; }
    body.kaiko-product-page .kaiko-product__buy form.cart { padding: 16px; }
    body.kaiko-product-page .kaiko-product__buy form.cart button.single_add_to_cart_button { width: 100%; }
    body.kaiko-product-page .kaiko-product__meta-grid { grid-template-columns: 1fr; gap: 14px 0; }
}
</style>

<!-- Tab switcher + live tier highlight -->
<script>
(function(){
    var root = document.querySelector('body.kaiko-product-page');
    if (!root) return;

    // --- Tabs ---
    var tabs = document.querySelectorAll('.kaiko-product__tab');
    var panels = document.querySelectorAll('.kaiko-product__panel');
    tabs.forEach(function(tab){
        tab.addEventListener('click', function(){
            var key = tab.getAttribute('data-tab');
            tabs.forEach(function(t){
                t.classList.toggle('is-active', t === tab);
                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
            });
            panels.forEach(function(p){
                var active = p.id === 'kaiko-tab-' + key;
                p.classList.toggle('is-active', active);
                if (active) { p.removeAttribute('hidden'); } else { p.setAttribute('hidden', ''); }
            });
        });
    });

    // --- Tier highlight + running total ---
    var tierBox = document.querySelector('.kaiko-tiers');
    var qtyInput = document.querySelector('form.cart input.qty');
    var totalEl = document.querySelector('[data-kaiko-total]');
    var saveWrap = document.querySelector('[data-kaiko-save]');
    var savePill = document.querySelector('[data-kaiko-save-pill]');
    if (!qtyInput || !totalEl) return;

    var basePrice = tierBox ? parseFloat(tierBox.getAttribute('data-base-price')) : parseFloat(qtyInput.getAttribute('data-base-price') || '0');
    if (!basePrice) {
        var priceStr = document.querySelector('.woocommerce-variation-price .amount, p.price .amount');
        if (priceStr) {
            basePrice = parseFloat(priceStr.textContent.replace(/[^0-9.]/g, '')) || 0;
        }
    }

    var rows = tierBox ? Array.from(tierBox.querySelectorAll('.kaiko-tiers__row')) : [];

    function formatGBP(n){
        return '£' + n.toFixed(2);
    }

    function recalc(){
        var q = parseInt(qtyInput.value, 10) || 1;
        var unit = basePrice;
        var activeRow = null;

        if (rows.length) {
            rows.forEach(function(row){
                var min = parseInt(row.getAttribute('data-min'), 10) || 1;
                var max = parseInt(row.getAttribute('data-max'), 10) || 0;
                var hit = (max === 0) ? (q >= min) : (q >= min && q <= max);
                row.classList.toggle('is-active', hit);
                if (hit) { activeRow = row; unit = parseFloat(row.getAttribute('data-price')) || unit; }
            });
        }

        var total = q * unit;
        totalEl.textContent = formatGBP(total);

        if (saveWrap && savePill) {
            if (basePrice > 0 && unit < basePrice) {
                var savingPct = Math.round(((basePrice - unit) / basePrice) * 100);
                var savingAmt = (basePrice - unit) * q;
                savePill.textContent = 'You save ' + formatGBP(savingAmt) + ' (' + savingPct + '%)';
                saveWrap.hidden = false;
            } else {
                saveWrap.hidden = true;
            }
        }
    }

    qtyInput.addEventListener('input', recalc);
    qtyInput.addEventListener('change', recalc);
    // Recalc after variation price appears
    jQuery && jQuery(document.body).on('found_variation', function(_, v){
        if (v && v.display_price) {
            basePrice = parseFloat(v.display_price);
            recalc();
        }
    });
    recalc();
})();
</script>

<?php
get_template_part( 'template-parts/kaiko-page-close' );
