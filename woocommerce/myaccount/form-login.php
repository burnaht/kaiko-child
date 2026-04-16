<?php
/**
 * Kaiko — Login / Register Form
 *
 * Override of WooCommerce myaccount/form-login.php
 * Two-card layout: Login on left, Trade Application on right.
 * Styled with Kaiko design system.
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
?>

<!-- Trade intro banner -->
<div class="kaiko-trade-intro kaiko-reveal">
    <h3>Wholesale Trade Access</h3>
    <p>Kaiko offers wholesale pricing exclusively to approved trade partners. Apply below and our team will review your application within 24 hours.</p>
    <div class="kaiko-trade-benefits">
        <div class="kaiko-trade-benefit">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Exclusive trade pricing</span>
        </div>
        <div class="kaiko-trade-benefit">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Free UK shipping over &pound;150</span>
        </div>
        <div class="kaiko-trade-benefit">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Priority access to new products</span>
        </div>
        <div class="kaiko-trade-benefit">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Dedicated account manager</span>
        </div>
    </div>
</div>

<div class="kaiko-auth-grid" id="customer_login">

    <!-- LOGIN CARD -->
    <div class="kaiko-auth-card kaiko-reveal">
        <h2><?php esc_html_e( 'Login', 'woocommerce' ); ?></h2>
        <p class="kaiko-card-subtitle">Sign in to access your trade account and orders.</p>

        <form class="woocommerce-form woocommerce-form-login login" method="post">

            <?php do_action( 'woocommerce_login_form_start' ); ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
            </p>

            <?php do_action( 'woocommerce_login_form' ); ?>

            <p class="form-row">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                    <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
                </label>
            </p>

            <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
            <button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>

            <p class="woocommerce-LostPassword lost_password">
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
            </p>

            <?php do_action( 'woocommerce_login_form_end' ); ?>

        </form>
    </div>

    <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

    <!-- REGISTER CARD -->
    <div class="kaiko-auth-card kaiko-reveal">
        <h2><?php esc_html_e( 'Apply for Trade Access', 'woocommerce' ); ?></h2>
        <p class="kaiko-card-subtitle">New to Kaiko? Complete the form below and we'll review your application within 24 hours.</p>

        <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

            <?php do_action( 'woocommerce_register_form_start' ); ?>

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
                </p>
            <?php endif; ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" />
            </p>

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
                </p>
            <?php else : ?>
                <p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
            <?php endif; ?>

            <?php
            /**
             * This hook renders the Kaiko custom fields:
             * - Business Name (required)
             * - Business Type (select)
             * - Phone Number
             * - Business Description (textarea)
             *
             * @hooked kaiko_registration_fields - 10
             */
            do_action( 'woocommerce_register_form' );
            ?>

            <p class="woocommerce-form-row form-row">
                <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                <button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Submit Application', 'woocommerce' ); ?>"><?php esc_html_e( 'Submit Application', 'woocommerce' ); ?></button>
            </p>

            <?php do_action( 'woocommerce_register_form_end' ); ?>

        </form>
    </div>

    <?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
