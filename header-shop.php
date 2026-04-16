<?php
/**
 * Kaiko — Shop Header
 *
 * Intercepts get_header('shop') calls from WooCommerce templates
 * that still use the old pattern (e.g. checkout, cart page wrappers).
 * Outputs the Kaiko page shell opening.
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/kaiko-page-open' );
