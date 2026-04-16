<?php
/**
 * Kaiko — My Account Page
 *
 * Override of WooCommerce myaccount/my-account.php
 * Sidebar nav layout with Kaiko styling.
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="kaiko-account-layout">
    <?php
    /**
     * My Account navigation.
     */
    do_action( 'woocommerce_account_navigation' );
    ?>

    <div class="woocommerce-MyAccount-content">
        <?php
        /**
         * My Account content.
         */
        do_action( 'woocommerce_account_content' );
        ?>
    </div>
</div>
