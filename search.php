<?php
/**
 * Kaiko — Search Results.
 *
 * Branded search surface. Lists hits with title + excerpt; renders a
 * clean empty state with CTAs to Shop and Home when no matches.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

get_header();

$kaiko_query    = trim( (string) get_search_query() );
$kaiko_shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<div class="kaiko-page-wrapper">
    <section class="kaiko-page-hero">
        <div class="kaiko-page-hero__tag"><?php esc_html_e( 'Search', 'kaiko-child' ); ?></div>
        <h1 class="kaiko-page-hero__title">
            <?php
            if ( '' !== $kaiko_query ) {
                /* translators: %s: search term */
                printf( esc_html__( 'Results for "%s"', 'kaiko-child' ), esc_html( $kaiko_query ) );
            } else {
                esc_html_e( 'Search', 'kaiko-child' );
            }
            ?>
        </h1>
    </section>

    <?php if ( have_posts() ) : ?>
        <ul class="kaiko-search-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <li class="kaiko-search-list__item">
                    <a href="<?php the_permalink(); ?>" class="kaiko-search-list__link">
                        <h2 class="kaiko-search-list__title"><?php the_title(); ?></h2>
                        <p class="kaiko-search-list__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>

        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <div class="kaiko-empty-state">
            <h2 class="kaiko-empty-state__title"><?php esc_html_e( 'No results found', 'kaiko-child' ); ?></h2>
            <p class="kaiko-empty-state__body">
                <?php esc_html_e( "We couldn't find anything matching that search. Try a different term, or browse the catalogue.", 'kaiko-child' ); ?>
            </p>
            <div class="kaiko-empty-state__ctas">
                <a href="<?php echo esc_url( $kaiko_shop_url ); ?>" class="btn btn--primary"><?php esc_html_e( 'Browse the shop', 'kaiko-child' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--ghost"><?php esc_html_e( 'Back to home', 'kaiko-child' ); ?></a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
