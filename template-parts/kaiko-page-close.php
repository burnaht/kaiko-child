<?php
/**
 * Kaiko — Page Shell (Closing)
 *
 * Closes the <main>, renders Kaiko footer, closes <body> and <html>.
 * Pair with kaiko-page-open.php.
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php get_template_part( 'template-parts/kaiko-footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
