<?php
/**
 * Kaiko — Site Footer
 *
 * Closes the Kaiko <main>, renders the shared footer partial, fires
 * wp_footer (with Woodmart's before-wp-footer action chain preserved —
 * that's where cookie popups, age-verify, and most third-party footer
 * widgets live), and closes body/html.
 *
 * Pair with header.php.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php
// Elementor Theme Builder short-circuit, mirroring header.php.
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) :
    get_template_part( 'template-parts/kaiko-footer' );
endif;
?>

<?php do_action( 'woodmart_before_wp_footer' ); ?>
<?php wp_footer(); ?>
</body>
</html>
