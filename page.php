<?php
/**
 * Kaiko — Default Page template (passthrough).
 *
 * Resolves for any WP Page that doesn't have a specific Kaiko template
 * (Kaiko Homepage / About / Products / Contact / Cart / Checkout / My
 * Account) assigned. Keeps every Page inside the Kaiko shell so no
 * route falls through to Woodmart's page.php.
 *
 * Minimal by design — any Page that wants polished presentation should
 * use one of the dedicated Kaiko templates or have its content authored
 * as block-editor layout.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="kaiko-page-wrapper">
    <?php while ( have_posts() ) : the_post(); ?>
        <article <?php post_class( 'kaiko-page-content' ); ?>>
            <?php if ( ! is_front_page() ) : ?>
                <section class="kaiko-page-hero">
                    <h1 class="kaiko-page-hero__title"><?php the_title(); ?></h1>
                </section>
            <?php endif; ?>
            <div class="kaiko-page-content__body">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php
get_footer();
