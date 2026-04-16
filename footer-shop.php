<?php
/**
 * Kaiko — Shop Footer
 *
 * Intercepts get_footer('shop') calls from WooCommerce templates
 * that still use the old pattern. Outputs the Kaiko page shell closing.
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/kaiko-page-close' );
