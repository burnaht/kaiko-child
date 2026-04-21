<?php
/**
 * Template Name: Kaiko Checkout
 * Template Post Type: page
 *
 * Standalone full-width page template for the WooCommerce checkout.
 * Mirrors template-cart.php's skeleton so the theme owns the checkout
 * page end-to-end — no Woodmart sidebar, no fallback widgets, no
 * double page title. The [woocommerce_checkout] shortcode lives in
 * the page content; this template renders it inside .kaiko-checkout-section
 * so woocommerce/checkout/form-checkout.php's .kaiko-checkout-columns
 * grid (fields + sticky review) picks up the existing shell styles.
 *
 * Auto-assigned to the Checkout page by inc/checkout-layout.php, with a
 * template_include safety net for installs where page meta is missing.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

// Ensure body class is applied even when WP's page meta route skipped
// the body_class filter for any reason (caching edge-case).
add_filter( 'body_class', function ( $classes ) {
	if ( ! in_array( 'kaiko-page', $classes, true ) )          $classes[] = 'kaiko-page';
	if ( ! in_array( 'kaiko-checkout-page', $classes, true ) ) $classes[] = 'kaiko-checkout-page';
	return $classes;
} );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'template-parts/kaiko-header' ); ?>

<main class="kaiko-main kaiko-checkout-main-root" id="kaiko-main" role="main">

	<section class="kaiko-checkout-hero">
		<div class="kaiko-checkout-hero__eyebrow"><?php esc_html_e( 'Checkout', 'kaiko-child' ); ?></div>
		<h1 class="kaiko-checkout-hero__title"><?php esc_html_e( 'Complete Your Order', 'kaiko-child' ); ?></h1>
		<p class="kaiko-checkout-hero__subtitle"><?php esc_html_e( 'Secure checkout powered by Kaiko.', 'kaiko-child' ); ?></p>
	</section>

	<section class="kaiko-checkout-section">
		<?php
		// Render the Checkout page's content — [woocommerce_checkout] runs
		// here and in turn uses our woocommerce/checkout/form-checkout.php
		// override for the .kaiko-checkout-columns markup.
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</section>

</main>

<?php get_template_part( 'template-parts/kaiko-footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
