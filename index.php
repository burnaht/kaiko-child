<?php
/**
 * Kaiko — Default index.
 *
 * Required by WordPress as the theme's fallback template. Also resolves
 * for routes that don't match any other template file (blog archives,
 * category archives, etc.). Exists here so get_header() dispatches to
 * Kaiko's header.php rather than falling through to Woodmart's.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="kaiko-page-wrapper">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class( 'kaiko-post' ); ?>>
                <h1 class="kaiko-page-hero__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h1>
                <div class="kaiko-post__excerpt">
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile; ?>

        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p><?php esc_html_e( 'Nothing to show here yet.', 'kaiko-child' ); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
