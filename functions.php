<?php
/**
 * KAIKO Child Theme — functions.php
 *
 * @package KaikoChild
 * @version 2.0.0
 *
 * Core functionality:
 * 1. Asset enqueuing (parent + child + Kaiko design system)
 * 2. Custom user roles (kaiko_pending, kaiko_approved)
 * 3. WooCommerce price hiding for non-approved users
 * 4. Admin approval workflow with email notifications
 * 5. Registration customizations (business fields)
 * 6. ACF field groups for product data
 * 7. WooCommerce hooks (species display, hardware specs)
 * 8. Admin dashboard widget & user columns
 * 9. Theme support & custom image sizes
 */

defined( 'ABSPATH' ) || exit;

define( 'KAIKO_VERSION', '3.0.0' );
define( 'KAIKO_DIR', get_stylesheet_directory() );
define( 'KAIKO_URI', get_stylesheet_directory_uri() );

/* Phase 4: Elementor helpers, shortcodes, and conditional enqueuing */
require_once KAIKO_DIR . '/inc/elementor-helpers.php';
require_once KAIKO_DIR . '/inc/elementor-enqueue.php';

/* Phase 5: Integrations, performance, SEO, and security */
require_once KAIKO_DIR . '/inc/chatbot-integration.php';
require_once KAIKO_DIR . '/inc/performance.php';
require_once KAIKO_DIR . '/inc/seo.php';
require_once KAIKO_DIR . '/inc/security.php';
require_once KAIKO_DIR . '/inc/contact-form.php';
require_once KAIKO_DIR . '/inc/mini-cart.php';
require_once KAIKO_DIR . '/inc/cart-layout.php';
require_once KAIKO_DIR . '/inc/account-layout.php';
require_once KAIKO_DIR . '/inc/checkout-layout.php';
require_once KAIKO_DIR . '/inc/mix-and-match-pricing.php';


/* ============================================
   1. ENQUEUE STYLES & SCRIPTS
   ============================================ */

add_action( 'wp_enqueue_scripts', 'kaiko_enqueue_assets', 20 );

function kaiko_enqueue_assets() {
    // Parent theme (WoodMart)
    wp_enqueue_style( 'woodmart-style', get_template_directory_uri() . '/style.css' );

    // Child theme stylesheet (header only)
    wp_enqueue_style( 'kaiko-child-style', get_stylesheet_uri(), array( 'woodmart-style' ), KAIKO_VERSION );

    // Google Fonts — Space Grotesk + Inter
    wp_enqueue_style(
        'kaiko-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );

    // Design System CSS (tokens + global styles)
    wp_enqueue_style( 'kaiko-design-system', KAIKO_URI . '/assets/css/kaiko-design-system.css', array( 'kaiko-child-style' ), KAIKO_VERSION );

    // Animation CSS (reveal, parallax, cursor, loader)
    wp_enqueue_style( 'kaiko-animations', KAIKO_URI . '/assets/css/kaiko-animations.css', array( 'kaiko-design-system' ), KAIKO_VERSION );

    // Kaiko Shell CSS (shared nav, footer, mobile menu, page wrappers)
    wp_enqueue_style( 'kaiko-shell', KAIKO_URI . '/assets/css/kaiko-shell.css', array( 'kaiko-design-system' ), KAIKO_VERSION );

    // WooCommerce overrides
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_style( 'kaiko-woocommerce', KAIKO_URI . '/assets/css/kaiko-woocommerce.css', array( 'kaiko-shell' ), KAIKO_VERSION );

        // Shop archive — hero, sidebar, grid, drawer
        if ( is_shop() || is_product_category() || is_product_tag() ) {
            wp_enqueue_style( 'kaiko-shop', KAIKO_URI . '/assets/css/kaiko-shop.css', array( 'kaiko-woocommerce' ), KAIKO_VERSION );
            wp_enqueue_script( 'kaiko-shop', KAIKO_URI . '/assets/js/kaiko-shop.js', array(), KAIKO_VERSION, true );
        }

        // Cart page — full rebuild
        if ( function_exists( 'is_cart' ) && is_cart() ) {
            wp_enqueue_style( 'kaiko-cart', KAIKO_URI . '/assets/css/kaiko-cart.css', array( 'kaiko-woocommerce' ), KAIKO_VERSION );
            wp_enqueue_script( 'kaiko-cart', KAIKO_URI . '/assets/js/kaiko-cart.js', array( 'jquery', 'kaiko-mini-cart' ), KAIKO_VERSION, true );
        }

        // My Account — standalone template. is_page_template() catches the
        // page-assignment path; is_account_page() catches any configuration
        // where the shortcode / default WC page rule fires instead.
        if (
            ( function_exists( 'is_page_template' ) && is_page_template( 'template-myaccount.php' ) )
            || ( function_exists( 'is_account_page' ) && is_account_page() )
        ) {
            wp_enqueue_style( 'kaiko-my-account', KAIKO_URI . '/assets/css/kaiko-my-account.css', array( 'kaiko-woocommerce' ), KAIKO_VERSION );
            wp_enqueue_script( 'kaiko-my-account', KAIKO_URI . '/assets/js/kaiko-account.js', array(), KAIKO_VERSION, true );
        }

        // Order-received / thank-you — branded BACS payment screen.
        // CSS only; clipboard JS lives inline at the foot of the template
        // (single-use, no reuse elsewhere on the site).
        if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
            wp_enqueue_style( 'kaiko-thankyou', KAIKO_URI . '/assets/css/kaiko-thankyou.css', array( 'kaiko-woocommerce' ), KAIKO_VERSION );
        }
    }

    // Animation engine (scroll reveals, parallax, cursor, countup, marquee)
    wp_enqueue_script( 'kaiko-animations-js', KAIKO_URI . '/assets/js/kaiko-animations.js', array(), KAIKO_VERSION, true );

    // Main JS (nav, AJAX filtering, dynamic behaviours)
    wp_enqueue_script( 'kaiko-main-js', KAIKO_URI . '/assets/js/kaiko-main.js', array( 'jquery' ), KAIKO_VERSION, true );

    // Shared header JS (hamburger, scroll-shrink, homepage WoodMart override)
    wp_enqueue_script( 'kaiko-header-js', KAIKO_URI . '/assets/js/kaiko-header.js', array(), KAIKO_VERSION, true );

    // Localize for AJAX
    wp_localize_script( 'kaiko-main-js', 'kaikoData', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'kaiko_nonce' ),
        'themeUri' => KAIKO_URI,
        'shopUrl'  => class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : '',
    ) );
}


/* ============================================
   2. CUSTOM USER ROLES
   ============================================ */

add_action( 'after_setup_theme', 'kaiko_register_custom_roles' );

function kaiko_register_custom_roles() {
    // Pending — registered but not yet approved
    add_role( 'kaiko_pending', 'Kaiko Pending', array( 'read' => true ) );

    // Approved — can see prices and purchase
    add_role( 'kaiko_approved', 'Kaiko Approved', array(
        'read'     => true,
        'customer' => true,
    ) );
}

add_action( 'after_switch_theme', 'kaiko_theme_activation' );

function kaiko_theme_activation() {
    kaiko_register_custom_roles();
    flush_rewrite_rules();
}


/* ============================================
   3. WOOCOMMERCE — PRICE HIDING SYSTEM
   ============================================ */

/**
 * Check if current user is approved to see prices.
 *
 * Blocklist model, not allowlist: any logged-in user who is NOT on
 * the `kaiko_pending` role is treated as approved. This mirrors the
 * my-account dashboard's state machine (template-myaccount.php:26-31),
 * which is the user-facing source of truth for trade status.
 *
 * Previously this was an explicit allowlist of
 * [kaiko_approved, administrator, shop_manager, editor], which broke
 * legacy accounts still on the plain `customer` role (created before
 * the kaiko_pending → kaiko_approved flow existed, or demoted by a
 * plugin). Those users could log in, see the approved dashboard, but
 * got empty price HTML and a hidden Shop link everywhere else — a
 * full shopping-path regression for trade customers.
 */
function kaiko_user_can_see_prices() {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $user = wp_get_current_user();
    $blocked_roles = array( 'kaiko_pending' );

    foreach ( $blocked_roles as $role ) {
        if ( in_array( $role, (array) $user->roles, true ) ) {
            return false;
        }
    }

    return true;
}

/**
 * Hide prices from non-approved users.
 */
add_filter( 'woocommerce_get_price_html', 'kaiko_hide_price_html', 9999, 2 );

function kaiko_hide_price_html( $price, $product ) {
    if ( kaiko_user_can_see_prices() ) {
        return $price;
    }

    // On shop archives (shop, category, tag, search, product loops) keep cards
    // clean — the "View details" CTA on the card is enough. The full pending /
    // trade-gating messaging is shown on the single product page and in the
    // my-account dashboard. This avoids the cluttered pill on every card.
    $in_shop_loop = function_exists( 'is_shop' ) && (
        is_shop()
        || is_product_category()
        || is_product_tag()
        || is_product_taxonomy()
        || ( function_exists( 'is_search' ) && is_search() && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] )
    );
    if ( $in_shop_loop ) {
        return '';
    }

    $lock_icon = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="7" width="12" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/><path d="M4.5 7V5a3.5 3.5 0 117 0v2" stroke="currentColor" stroke-width="1.2"/></svg>';

    if ( is_user_logged_in() ) {
        return '<span class="kaiko-price-locked kaiko-pending-msg">' . $lock_icon . ' Approval pending — pricing available once approved</span>';
    }

    return '<span class="kaiko-price-locked">' . $lock_icon . ' <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Login for trade pricing</a></span>';
}

/**
 * Hide "Add to Cart" for non-approved users.
 */
add_action( 'wp', 'kaiko_hide_add_to_cart_button' );

function kaiko_hide_add_to_cart_button() {
    if ( ! kaiko_user_can_see_prices() ) {
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        add_action( 'woocommerce_single_product_summary', 'kaiko_restricted_purchase_message', 30 );
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
    }
}

function kaiko_restricted_purchase_message() {
    if ( is_user_logged_in() ) {
        echo '<div class="kaiko-access-notice kaiko-access-pending">
            <h4>Account Under Review</h4>
            <p>Your account is being reviewed by our team. You\'ll receive an email once approved for trade pricing and ordering.</p>
        </div>';
    } else {
        echo '<div class="kaiko-access-notice">
            <h4>Trade Access Required</h4>
            <p>Product pricing and purchasing is available to approved trade partners.</p>
            <div class="kaiko-access-actions">
                <a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" class="kaiko-btn kaiko-btn-primary">Apply for Access</a>
                <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="kaiko-btn kaiko-btn-secondary">Login</a>
            </div>
        </div>';
    }
}

/**
 * Prevent non-approved users from adding to cart via URL manipulation.
 */
add_filter( 'woocommerce_add_to_cart_validation', 'kaiko_prevent_add_to_cart', 10, 3 );

function kaiko_prevent_add_to_cart( $passed, $product_id, $quantity ) {
    if ( ! kaiko_user_can_see_prices() ) {
        wc_add_notice( __( 'You must have an approved trade account to purchase.', 'kaiko-child' ), 'error' );
        return false;
    }
    return $passed;
}

/**
 * Hide variation prices and cart prices for non-approved.
 */
add_filter( 'woocommerce_variation_price_html', 'kaiko_hide_variation_price', 9999, 2 );
add_filter( 'woocommerce_variation_sale_price_html', 'kaiko_hide_variation_price', 9999, 2 );

function kaiko_hide_variation_price( $price, $variation ) {
    return kaiko_user_can_see_prices() ? $price : '';
}

add_filter( 'woocommerce_cart_item_price', 'kaiko_hide_cart_price', 9999, 3 );

function kaiko_hide_cart_price( $price, $cart_item, $cart_item_key ) {
    return kaiko_user_can_see_prices() ? $price : '';
}


/* ============================================
   4. REGISTRATION — ASSIGN PENDING ROLE
   ============================================ */

add_action( 'woocommerce_created_customer', 'kaiko_set_new_user_role', 10, 1 );

function kaiko_set_new_user_role( $customer_id ) {
    $user = new WP_User( $customer_id );
    $user->set_role( 'kaiko_pending' );
    update_user_meta( $customer_id, 'kaiko_registration_date', current_time( 'mysql' ) );
    update_user_meta( $customer_id, 'kaiko_approval_status', 'pending' );
}

add_action( 'user_register', 'kaiko_set_new_wp_user_role', 10, 1 );

function kaiko_set_new_wp_user_role( $user_id ) {
    if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
        $user = new WP_User( $user_id );
        $user->set_role( 'kaiko_pending' );
        update_user_meta( $user_id, 'kaiko_registration_date', current_time( 'mysql' ) );
        update_user_meta( $user_id, 'kaiko_approval_status', 'pending' );
    }
}

/**
 * Extra registration fields.
 */
add_action( 'woocommerce_register_form', 'kaiko_registration_fields' );

function kaiko_registration_fields() {
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="kaiko_business_name"><?php esc_html_e( 'Business / Shop Name', 'kaiko-child' ); ?> <span class="required">*</span></label>
        <input type="text" class="woocommerce-Input input-text" name="kaiko_business_name" id="kaiko_business_name" required />
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="kaiko_business_type"><?php esc_html_e( 'Business Type', 'kaiko-child' ); ?></label>
        <select name="kaiko_business_type" id="kaiko_business_type" class="woocommerce-Input input-text">
            <option value="">Select type...</option>
            <option value="retailer">Retailer / Pet Shop</option>
            <option value="breeder">Professional Breeder</option>
            <option value="wholesaler">Wholesaler / Distributor</option>
            <option value="online">Online Store</option>
            <option value="other">Other</option>
        </select>
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="kaiko_phone"><?php esc_html_e( 'Phone Number', 'kaiko-child' ); ?></label>
        <input type="tel" class="woocommerce-Input input-text" name="kaiko_phone" id="kaiko_phone" />
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="kaiko_message"><?php esc_html_e( 'Tell us about your business', 'kaiko-child' ); ?></label>
        <textarea class="woocommerce-Input input-text" name="kaiko_message" id="kaiko_message" rows="3" placeholder="Brief description of your business and how you intend to stock Kaiko products..."></textarea>
    </p>
    <?php
}

add_filter( 'woocommerce_registration_errors', 'kaiko_validate_registration', 10, 3 );

function kaiko_validate_registration( $errors, $username, $email ) {
    if ( empty( $_POST['kaiko_business_name'] ) ) {
        $errors->add( 'kaiko_business_name_error', __( 'Business name is required.', 'kaiko-child' ) );
    }
    return $errors;
}

add_action( 'woocommerce_created_customer', 'kaiko_save_registration_fields' );

function kaiko_save_registration_fields( $customer_id ) {
    $fields = array( 'kaiko_business_name', 'kaiko_business_type', 'kaiko_phone' );
    foreach ( $fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_user_meta( $customer_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }
    if ( isset( $_POST['kaiko_message'] ) ) {
        update_user_meta( $customer_id, 'kaiko_message', sanitize_textarea_field( $_POST['kaiko_message'] ) );
    }
}


/* ============================================
   5. ADMIN APPROVAL SYSTEM
   ============================================ */

add_filter( 'manage_users_columns', 'kaiko_add_user_columns' );

function kaiko_add_user_columns( $columns ) {
    $columns['kaiko_status']   = 'Kaiko Status';
    $columns['kaiko_business'] = 'Business';
    return $columns;
}

add_filter( 'manage_users_custom_column', 'kaiko_show_user_column', 10, 3 );

function kaiko_show_user_column( $value, $column_name, $user_id ) {
    if ( 'kaiko_status' === $column_name ) {
        $status = get_user_meta( $user_id, 'kaiko_approval_status', true );
        if ( 'approved' === $status ) {
            return '<span style="color:#0a7;font-weight:600;">Approved</span>';
        } elseif ( 'rejected' === $status ) {
            return '<span style="color:#d33;font-weight:600;">Rejected</span>';
        } else {
            $user = get_userdata( $user_id );
            if ( in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
                $approve = wp_nonce_url( admin_url( "admin-post.php?action=kaiko_approve_user&user_id=$user_id" ), 'kaiko_approve_' . $user_id );
                $reject  = wp_nonce_url( admin_url( "admin-post.php?action=kaiko_reject_user&user_id=$user_id" ), 'kaiko_reject_' . $user_id );
                return '<span style="color:#f90;font-weight:600;">Pending</span><br><a href="' . $approve . '" style="color:#0a7;font-weight:bold;">Approve</a> | <a href="' . $reject . '" style="color:#d33;">Reject</a>';
            }
            return '—';
        }
    }

    if ( 'kaiko_business' === $column_name ) {
        $biz  = get_user_meta( $user_id, 'kaiko_business_name', true );
        $type = get_user_meta( $user_id, 'kaiko_business_type', true );
        return esc_html( $biz ) . ( $type ? '<br><small>' . esc_html( ucfirst( $type ) ) . '</small>' : '' );
    }

    return $value;
}

add_action( 'admin_post_kaiko_approve_user', 'kaiko_handle_approve_user' );

function kaiko_handle_approve_user() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }
    $user_id = intval( $_GET['user_id'] );
    check_admin_referer( 'kaiko_approve_' . $user_id );

    $user = new WP_User( $user_id );
    $user->set_role( 'kaiko_approved' );
    update_user_meta( $user_id, 'kaiko_approval_status', 'approved' );
    update_user_meta( $user_id, 'kaiko_approval_date', current_time( 'mysql' ) );
    kaiko_send_approval_email( $user_id );

    wp_redirect( admin_url( 'users.php?kaiko_notice=approved' ) );
    exit;
}

add_action( 'admin_post_kaiko_reject_user', 'kaiko_handle_reject_user' );

function kaiko_handle_reject_user() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }
    $user_id = intval( $_GET['user_id'] );
    check_admin_referer( 'kaiko_reject_' . $user_id );

    update_user_meta( $user_id, 'kaiko_approval_status', 'rejected' );
    kaiko_send_rejection_email( $user_id );

    wp_redirect( admin_url( 'users.php?kaiko_notice=rejected' ) );
    exit;
}

add_action( 'admin_notices', 'kaiko_admin_notices' );

function kaiko_admin_notices() {
    if ( isset( $_GET['kaiko_notice'] ) ) {
        $type = sanitize_text_field( $_GET['kaiko_notice'] );
        if ( 'approved' === $type ) {
            echo '<div class="notice notice-success is-dismissible"><p>User has been <strong>approved</strong> for trade pricing access.</p></div>';
        } elseif ( 'rejected' === $type ) {
            echo '<div class="notice notice-warning is-dismissible"><p>User application has been <strong>rejected</strong>.</p></div>';
        }
    }
}

// Bulk approve
add_filter( 'bulk_actions-users', 'kaiko_bulk_approve_action' );

function kaiko_bulk_approve_action( $actions ) {
    $actions['kaiko_bulk_approve'] = 'Approve for Kaiko Trade Access';
    return $actions;
}

add_filter( 'handle_bulk_actions-users', 'kaiko_handle_bulk_approve', 10, 3 );

function kaiko_handle_bulk_approve( $redirect_to, $doaction, $user_ids ) {
    if ( 'kaiko_bulk_approve' !== $doaction ) {
        return $redirect_to;
    }
    foreach ( $user_ids as $user_id ) {
        $user = new WP_User( $user_id );
        if ( in_array( 'kaiko_pending', (array) $user->roles, true ) ) {
            $user->set_role( 'kaiko_approved' );
            update_user_meta( $user_id, 'kaiko_approval_status', 'approved' );
            update_user_meta( $user_id, 'kaiko_approval_date', current_time( 'mysql' ) );
            kaiko_send_approval_email( $user_id );
        }
    }
    return add_query_arg( 'kaiko_notice', 'approved', $redirect_to );
}

// User profile fields
add_action( 'show_user_profile', 'kaiko_user_profile_fields' );
add_action( 'edit_user_profile', 'kaiko_user_profile_fields' );

function kaiko_user_profile_fields( $user ) {
    $status   = get_user_meta( $user->ID, 'kaiko_approval_status', true );
    $biz_name = get_user_meta( $user->ID, 'kaiko_business_name', true );
    $biz_type = get_user_meta( $user->ID, 'kaiko_business_type', true );
    $phone    = get_user_meta( $user->ID, 'kaiko_phone', true );
    $message  = get_user_meta( $user->ID, 'kaiko_message', true );
    $reg_date = get_user_meta( $user->ID, 'kaiko_registration_date', true );
    $app_date = get_user_meta( $user->ID, 'kaiko_approval_date', true );
    ?>
    <h2>Kaiko Trade Account</h2>
    <table class="form-table">
        <tr><th>Status</th><td>
            <strong><?php echo esc_html( ucfirst( $status ?: 'N/A' ) ); ?></strong>
            <?php if ( 'pending' === $status && current_user_can( 'manage_options' ) ) : ?>
                <br>
                <a href="<?php echo wp_nonce_url( admin_url( "admin-post.php?action=kaiko_approve_user&user_id={$user->ID}" ), 'kaiko_approve_' . $user->ID ); ?>" class="button button-primary" style="margin-top:8px;">Approve</a>
                <a href="<?php echo wp_nonce_url( admin_url( "admin-post.php?action=kaiko_reject_user&user_id={$user->ID}" ), 'kaiko_reject_' . $user->ID ); ?>" class="button" style="margin-top:8px;">Reject</a>
            <?php endif; ?>
        </td></tr>
        <tr><th>Business Name</th><td><?php echo esc_html( $biz_name ); ?></td></tr>
        <tr><th>Business Type</th><td><?php echo esc_html( ucfirst( $biz_type ) ); ?></td></tr>
        <tr><th>Phone</th><td><?php echo esc_html( $phone ); ?></td></tr>
        <tr><th>Application Message</th><td><?php echo esc_html( $message ); ?></td></tr>
        <tr><th>Registration Date</th><td><?php echo esc_html( $reg_date ); ?></td></tr>
        <?php if ( $app_date ) : ?>
        <tr><th>Approval Date</th><td><?php echo esc_html( $app_date ); ?></td></tr>
        <?php endif; ?>
    </table>
    <?php
}


/* ============================================
   6. EMAIL NOTIFICATIONS
   ============================================ */

add_action( 'woocommerce_created_customer', 'kaiko_notify_admin_new_registration', 20 );

function kaiko_notify_admin_new_registration( $customer_id ) {
    $user    = get_userdata( $customer_id );
    $biz     = get_user_meta( $customer_id, 'kaiko_business_name', true );
    $type    = get_user_meta( $customer_id, 'kaiko_business_type', true );
    $msg     = get_user_meta( $customer_id, 'kaiko_message', true );
    $subject = '[Kaiko] New Trade Account Application — ' . $biz;

    $body  = "A new trade account application has been submitted.\n\n";
    $body .= "Name: {$user->first_name} {$user->last_name}\n";
    $body .= "Email: {$user->user_email}\n";
    $body .= "Business: {$biz}\nType: {$type}\nMessage: {$msg}\n\n";
    $body .= "Review: " . admin_url( "user-edit.php?user_id={$customer_id}" ) . "\n";

    wp_mail( get_option( 'admin_email' ), $subject, $body );
}

function kaiko_send_approval_email( $user_id ) {
    $user = get_userdata( $user_id );
    wp_mail( $user->user_email, 'Your Kaiko Trade Account Has Been Approved',
        "Hi {$user->first_name},\n\nGreat news — your Kaiko trade account has been approved.\n\nYou now have full access to trade pricing: " . wc_get_page_permalink( 'shop' ) . "\n\nLog in: " . wc_get_page_permalink( 'myaccount' ) . "\n\n— The Kaiko Team"
    );
}

function kaiko_send_rejection_email( $user_id ) {
    $user = get_userdata( $user_id );
    wp_mail( $user->user_email, 'Kaiko Trade Account Update',
        "Hi {$user->first_name},\n\nThank you for your interest in becoming a Kaiko trade partner.\n\nAfter reviewing your application, we're unable to approve trade access at this time.\n\nIf you have questions, please contact us at trade@kaiko.com.\n\n— The Kaiko Team"
    );
}

add_action( 'woocommerce_created_customer', 'kaiko_send_pending_email', 30 );

function kaiko_send_pending_email( $customer_id ) {
    $user     = get_userdata( $customer_id );
    $business = get_user_meta( $customer_id, 'kaiko_business_name', true );
    $greeting = $user->first_name
        ? 'Hi ' . $user->first_name
        : ( $business ? 'Hi ' . $business : 'Hi there' );
    wp_mail( $user->user_email, 'Kaiko — Application Received',
        "{$greeting},\n\nThank you for applying for a Kaiko trade account.\n\nYour application is now being reviewed. We'll be in touch within 24 hours.\n\nIn the meantime, browse our range: " . wc_get_page_permalink( 'shop' ) . "\n\n— The Kaiko Team"
    );
}


/* ============================================
   7. MY ACCOUNT CUSTOMIZATIONS
   ============================================ */

add_action( 'woocommerce_account_dashboard', 'kaiko_myaccount_dashboard_notice' );

function kaiko_myaccount_dashboard_notice() {
    $user   = wp_get_current_user();
    $status = get_user_meta( $user->ID, 'kaiko_approval_status', true );

    if ( 'pending' === $status ) {
        echo '<div class="kaiko-account-notice info"><h3>Account Under Review</h3><p>Your trade account application is being reviewed. You\'ll receive an email once approved.</p></div>';
    } elseif ( 'rejected' === $status ) {
        echo '<div class="kaiko-account-notice warning"><h3>Application Status</h3><p>Your trade application was not approved at this time. Please <a href="' . esc_url( home_url( '/contact' ) ) . '">contact us</a> for more information.</p></div>';
    } elseif ( 'approved' === $status ) {
        echo '<div class="kaiko-account-notice success"><h3>Trade Account Active</h3><p>You have full access to trade pricing. <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Browse products</a></p></div>';
    }
}

add_action( 'template_redirect', 'kaiko_redirect_pending_from_checkout' );

function kaiko_redirect_pending_from_checkout() {
    if ( is_checkout() && is_user_logged_in() && ! kaiko_user_can_see_prices() ) {
        wp_redirect( wc_get_page_permalink( 'myaccount' ) );
        exit;
    }
}

add_filter( 'woocommerce_enable_myaccount_registration', '__return_true' );

/**
 * Trade applications are NOT auto-logged-in. Pending users shouldn't be
 * dropped onto the "logged in" dashboard before a human has approved them —
 * that reads as auto-approval and isn't the trade flow we want.
 *
 * Instead, after submitting the register form the user is signed out and
 * redirected back to /my-account/?application=received, where the logged-out
 * template renders a branded "Application received" confirmation banner.
 */
add_filter( 'woocommerce_registration_auth_automatically', '__return_false' );

add_filter( 'woocommerce_registration_redirect', 'kaiko_registration_redirect', 10, 1 );

function kaiko_registration_redirect( $redirect ) {
    $myaccount = wc_get_page_permalink( 'myaccount' );
    return add_query_arg( 'application', 'received', $myaccount );
}

/**
 * Safety-net: if any other plugin forces an auth cookie during trade
 * registration, log the user straight back out so they hit the logged-out
 * confirmation state. Only applies to newly-registered pending users.
 */
add_action( 'woocommerce_created_customer', 'kaiko_force_logout_after_registration', 999, 1 );

function kaiko_force_logout_after_registration( $customer_id ) {
    if ( is_admin() ) {
        return;
    }
    // Only force-logout if WC (or a plugin) has already set the auth cookie
    // for this brand new customer. Existing sessions are untouched.
    if ( get_current_user_id() === (int) $customer_id ) {
        wp_logout();
        wp_clear_auth_cookie();
    }
}


/* ============================================
   8. ACF FIELD GROUPS — PRODUCT DATA
   ============================================ */

add_action( 'acf/init', 'kaiko_register_acf_fields' );

function kaiko_register_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $product_location = array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ) );

    // Species Compatibility
    acf_add_local_field_group( array(
        'key'      => 'group_kaiko_species',
        'title'    => 'Species Compatibility',
        'fields'   => array(
            array(
                'key'          => 'field_compatible_species',
                'label'        => 'Compatible Species',
                'name'         => 'compatible_species',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Add Species',
                'sub_fields'   => array(
                    array( 'key' => 'field_species_name', 'label' => 'Common Name', 'name' => 'species_name', 'type' => 'text', 'wrapper' => array( 'width' => '30' ) ),
                    array( 'key' => 'field_species_scientific', 'label' => 'Scientific Name', 'name' => 'species_scientific', 'type' => 'text', 'wrapper' => array( 'width' => '30' ) ),
                    array( 'key' => 'field_compatibility_level', 'label' => 'Level', 'name' => 'compatibility_level', 'type' => 'select', 'choices' => array( 'full' => 'Full', 'partial' => 'Partial', 'supervised' => 'Supervised' ), 'default_value' => 'full', 'wrapper' => array( 'width' => '20' ) ),
                    array( 'key' => 'field_compatibility_notes', 'label' => 'Notes', 'name' => 'compatibility_notes', 'type' => 'textarea', 'rows' => 2, 'wrapper' => array( 'width' => '20' ) ),
                ),
            ),
        ),
        'location'   => $product_location,
        'menu_order' => 10,
    ) );

    // Hardware Specifications
    acf_add_local_field_group( array(
        'key'    => 'group_kaiko_hardware',
        'title'  => 'Hardware Specifications',
        'fields' => array(
            array( 'key' => 'field_dimensions', 'label' => 'Dimensions', 'name' => 'dimensions', 'type' => 'group', 'layout' => 'table', 'sub_fields' => array(
                array( 'key' => 'field_dim_length', 'label' => 'Length (cm)', 'name' => 'length', 'type' => 'number', 'append' => 'cm' ),
                array( 'key' => 'field_dim_width', 'label' => 'Width (cm)', 'name' => 'width', 'type' => 'number', 'append' => 'cm' ),
                array( 'key' => 'field_dim_height', 'label' => 'Height (cm)', 'name' => 'height', 'type' => 'number', 'append' => 'cm' ),
            ) ),
            array( 'key' => 'field_power_requirements', 'label' => 'Power Requirements', 'name' => 'power_requirements', 'type' => 'text', 'placeholder' => 'e.g. 50W, 240V', 'wrapper' => array( 'width' => '33' ) ),
            array( 'key' => 'field_material', 'label' => 'Material', 'name' => 'material', 'type' => 'select', 'choices' => array( 'glass' => 'Glass', 'wood' => 'Wood', 'pvc' => 'PVC', 'mesh' => 'Mesh', 'hybrid' => 'Hybrid' ), 'allow_null' => true, 'wrapper' => array( 'width' => '33' ) ),
            array( 'key' => 'field_weight_kg', 'label' => 'Weight', 'name' => 'weight_kg', 'type' => 'number', 'append' => 'kg', 'step' => 0.1, 'wrapper' => array( 'width' => '33' ) ),
        ),
        'location'   => $product_location,
        'menu_order' => 20,
    ) );

    // Wholesale Tier Pricing
    acf_add_local_field_group( array(
        'key'    => 'group_kaiko_tiers',
        'title'  => 'Wholesale Tier Pricing',
        'fields' => array(
            array(
                'key'           => 'field_kaiko_tiers',
                'label'         => 'Quantity Tiers',
                'name'          => 'kaiko_wholesale_tiers',
                'type'          => 'repeater',
                'instructions'  => 'Leave empty to use the product\'s base price with no tier discount. Set the final tier\'s Max Qty to 0 (or blank) to mean "and above".',
                'layout'        => 'table',
                'button_label'  => 'Add Tier',
                'sub_fields'    => array(
                    array( 'key' => 'field_tier_min', 'label' => 'Min Qty', 'name' => 'min_qty', 'type' => 'number', 'default_value' => 1, 'min' => 1, 'wrapper' => array( 'width' => '20' ) ),
                    array( 'key' => 'field_tier_max', 'label' => 'Max Qty', 'name' => 'max_qty', 'type' => 'number', 'instructions' => '0 or blank = unlimited', 'wrapper' => array( 'width' => '20' ) ),
                    array( 'key' => 'field_tier_price', 'label' => 'Unit Price (£)', 'name' => 'unit_price', 'type' => 'number', 'step' => 0.01, 'required' => 1, 'wrapper' => array( 'width' => '30' ) ),
                    array( 'key' => 'field_tier_label', 'label' => 'Label (optional)', 'name' => 'label', 'type' => 'text', 'placeholder' => 'e.g. Best Value', 'wrapper' => array( 'width' => '30' ) ),
                ),
            ),
            array(
                'key'           => 'field_kaiko_carton_qty',
                'label'         => 'Carton Quantity',
                'name'          => 'carton_qty',
                'type'          => 'number',
                'instructions'  => 'Units per carton (shown in meta grid).',
                'wrapper'       => array( 'width' => '50' ),
            ),
            array(
                'key'           => 'field_kaiko_lead_time',
                'label'         => 'Lead Time',
                'name'          => 'lead_time',
                'type'          => 'text',
                'placeholder'   => 'e.g. 3–5 working days',
                'wrapper'       => array( 'width' => '50' ),
            ),
        ),
        'location'   => $product_location,
        'menu_order' => 5,
    ) );

}


/* ============================================
   8b. WHOLESALE TIER PRICING HELPERS
   ============================================ */

/**
 * Default wholesale-tier schedule applied to every purchasable product
 * when no per-product ACF tiers are configured.
 *
 * Matches the silkwormstore.co.uk/wholesale pattern: 6+=12% off, 12+=22% off,
 * 24+=30% off. Filterable so the schedule can be tuned without a code change.
 *
 * @return array of ['min_qty'=>int,'max_qty'=>int,'discount_pct'=>float]
 */
function kaiko_get_default_tier_schedule() {
    return apply_filters( 'kaiko_default_tier_schedule', array(
        array( 'min_qty' => 1,  'max_qty' => 5,  'discount_pct' => 0 ),
        array( 'min_qty' => 6,  'max_qty' => 11, 'discount_pct' => 12 ),
        array( 'min_qty' => 12, 'max_qty' => 23, 'discount_pct' => 22 ),
        array( 'min_qty' => 24, 'max_qty' => 0,  'discount_pct' => 30 ),
    ) );
}

/**
 * Get normalised tier array for a product.
 *
 * First honours per-product ACF tiers (kaiko_wholesale_tiers). If none are
 * configured, falls back to the default schedule applied to the product's
 * base price — guaranteeing every purchasable product shows bulk discounts.
 *
 * For variable products, $base comes from $product->get_price() which is
 * the min variation price. Callers (single-product page JS) can recompute
 * tier prices per-variation using the emitted discount_pct.
 *
 * @param int $product_id
 * @return array of ['min_qty'=>int,'max_qty'=>int,'unit_price'=>float,'discount_pct'=>float,'label'=>string,'is_default'=>bool]
 */
function kaiko_get_product_tiers( $product_id ) {
    // 1) ACF-configured per-product tiers take precedence.
    if ( function_exists( 'get_field' ) ) {
        $raw = get_field( 'kaiko_wholesale_tiers', $product_id );
        if ( ! empty( $raw ) && is_array( $raw ) ) {
            $tiers = array();
            $max_price = 0;
            foreach ( $raw as $row ) {
                $price = isset( $row['unit_price'] ) ? (float) $row['unit_price'] : 0;
                if ( $price <= 0 ) continue;
                if ( $price > $max_price ) $max_price = $price;
                $tiers[] = array(
                    'min_qty'      => max( 1, isset( $row['min_qty'] ) ? (int) $row['min_qty'] : 1 ),
                    'max_qty'      => max( 0, isset( $row['max_qty'] ) ? (int) $row['max_qty'] : 0 ),
                    'unit_price'   => $price,
                    'discount_pct' => 0,
                    'label'        => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
                    'is_default'   => false,
                );
            }
            if ( ! empty( $tiers ) ) {
                // Compute discount_pct vs highest configured price so the
                // JS recompute + "X% off" labels are consistent.
                foreach ( $tiers as $i => $t ) {
                    $tiers[ $i ]['discount_pct'] = ( $max_price > 0 )
                        ? round( ( 1 - ( $t['unit_price'] / $max_price ) ) * 100, 2 )
                        : 0;
                }
                usort( $tiers, function( $a, $b ) { return $a['min_qty'] - $b['min_qty']; } );
                return $tiers;
            }
        }
    }

    // 2) Fallback: default schedule applied to product base price.
    $product = wc_get_product( $product_id );
    if ( ! $product ) return array();
    $base = (float) $product->get_price();
    if ( $base <= 0 ) return array();

    $schedule = kaiko_get_default_tier_schedule();
    $tiers    = array();
    foreach ( $schedule as $row ) {
        $pct  = isset( $row['discount_pct'] ) ? (float) $row['discount_pct'] : 0;
        $unit = round( $base * ( 1 - $pct / 100 ), 2 );
        $tiers[] = array(
            'min_qty'      => (int) $row['min_qty'],
            'max_qty'      => (int) $row['max_qty'],
            'unit_price'   => $unit,
            'discount_pct' => $pct,
            'label'        => '',
            'is_default'   => true,
        );
    }
    return $tiers;
}

/**
 * Find the tier that matches a given quantity (or null).
 */
function kaiko_find_tier_for_qty( $tiers, $qty ) {
    foreach ( $tiers as $tier ) {
        $in_band = ( $tier['max_qty'] === 0 )
            ? ( $qty >= $tier['min_qty'] )
            : ( $qty >= $tier['min_qty'] && $qty <= $tier['max_qty'] );
        if ( $in_band ) return $tier;
    }
    return null;
}

/**
 * Shop archive: show "From £X" when tiers exist (approved users only).
 */
add_filter( 'woocommerce_get_price_html', 'kaiko_tier_from_price_html', 20, 2 );

function kaiko_tier_from_price_html( $price_html, $product ) {
    if ( is_admin() ) return $price_html;
    if ( ! function_exists( 'kaiko_user_can_see_prices' ) || ! kaiko_user_can_see_prices() ) return $price_html;
    $tiers = kaiko_get_product_tiers( $product->get_id() );
    if ( empty( $tiers ) ) return $price_html;

    // Lowest tier price
    $min_tier_price = min( array_column( $tiers, 'unit_price' ) );
    $base           = (float) $product->get_price();
    if ( $min_tier_price >= $base || $min_tier_price <= 0 ) return $price_html;

    return sprintf(
        '%s <span class="kaiko-from-price">from %s</span>',
        $price_html,
        wp_kses_post( wc_price( $min_tier_price ) )
    );
}


/* ============================================
   9. WOOCOMMERCE PRODUCT DISPLAY HOOKS
   ============================================ */

// Species compatibility on single product
add_action( 'woocommerce_after_single_product_summary', 'kaiko_display_species_compatibility', 15 );

function kaiko_display_species_compatibility() {
    $species = get_field( 'compatible_species' );
    if ( empty( $species ) ) return;

    echo '<div class="kaiko-species-compat kaiko-reveal">';
    echo '<h3 class="kaiko-species-compat__title">Species Compatibility</h3>';
    echo '<div class="kaiko-species-compat__grid">';
    foreach ( $species as $row ) {
        $level = sanitize_html_class( $row['compatibility_level'] );
        echo '<div class="kaiko-species-compat__item kaiko-species-compat__item--' . $level . '">';
        echo '<span class="kaiko-species-compat__name">' . esc_html( $row['species_name'] ) . '</span>';
        if ( ! empty( $row['species_scientific'] ) ) {
            echo '<span class="kaiko-species-compat__scientific">' . esc_html( $row['species_scientific'] ) . '</span>';
        }
        echo '<span class="kaiko-species-compat__level">' . esc_html( ucfirst( $row['compatibility_level'] ) ) . '</span>';
        if ( ! empty( $row['compatibility_notes'] ) ) {
            echo '<span class="kaiko-species-compat__notes">' . esc_html( $row['compatibility_notes'] ) . '</span>';
        }
        echo '</div>';
    }
    echo '</div></div>';
}

// Hardware specs on single product
add_action( 'woocommerce_after_single_product_summary', 'kaiko_display_hardware_specs', 16 );

function kaiko_display_hardware_specs() {
    $dims     = get_field( 'dimensions' );
    $power    = get_field( 'power_requirements' );
    $material = get_field( 'material' );
    $weight   = get_field( 'weight_kg' );

    if ( empty( $dims['length'] ) && empty( $power ) && empty( $material ) && empty( $weight ) ) return;

    echo '<div class="kaiko-hardware-specs kaiko-reveal">';
    echo '<h3>Specifications</h3><table class="kaiko-hardware-specs__table">';
    if ( ! empty( $dims['length'] ) ) echo '<tr><td>Dimensions</td><td>' . esc_html( "{$dims['length']} x {$dims['width']} x {$dims['height']} cm" ) . '</td></tr>';
    if ( ! empty( $material ) )       echo '<tr><td>Material</td><td>' . esc_html( ucfirst( $material ) ) . '</td></tr>';
    if ( ! empty( $weight ) )         echo '<tr><td>Weight</td><td>' . esc_html( $weight . ' kg' ) . '</td></tr>';
    if ( ! empty( $power ) )          echo '<tr><td>Power</td><td>' . esc_html( $power ) . '</td></tr>';
    echo '</table></div>';
}

// Species columns in admin products list
add_filter( 'manage_edit-product_columns', 'kaiko_product_columns' );

function kaiko_product_columns( $columns ) {
    $new = array();
    foreach ( $columns as $k => $v ) {
        $new[ $k ] = $v;
        if ( 'price' === $k ) {
            $new['kaiko_species']    = 'Species';
        }
    }
    return $new;
}

add_action( 'manage_product_posts_custom_column', 'kaiko_product_column_data', 10, 2 );

function kaiko_product_column_data( $column, $post_id ) {
    if ( 'kaiko_species' === $column ) {
        $species = get_field( 'compatible_species', $post_id );
        echo ! empty( $species ) ? esc_html( implode( ', ', wp_list_pluck( $species, 'species_name' ) ) ) : '—';
    }

}


/* ============================================
   10. ADMIN DASHBOARD WIDGET
   ============================================ */

add_action( 'wp_dashboard_setup', 'kaiko_dashboard_widget' );

function kaiko_dashboard_widget() {
    wp_add_dashboard_widget( 'kaiko_pending_users', 'Kaiko — Pending Trade Applications', 'kaiko_dashboard_widget_content' );
}

function kaiko_dashboard_widget_content() {
    $pending = get_users( array( 'role' => 'kaiko_pending', 'orderby' => 'registered', 'order' => 'DESC', 'number' => 10 ) );

    if ( empty( $pending ) ) {
        echo '<p style="color:#666;">No pending applications.</p>';
        return;
    }

    echo '<table style="width:100%;border-collapse:collapse;"><thead><tr style="text-align:left;border-bottom:2px solid #eee;"><th style="padding:8px 4px;">User</th><th style="padding:8px 4px;">Business</th><th style="padding:8px 4px;">Action</th></tr></thead><tbody>';
    foreach ( $pending as $user ) {
        $biz = get_user_meta( $user->ID, 'kaiko_business_name', true );
        $url = wp_nonce_url( admin_url( "admin-post.php?action=kaiko_approve_user&user_id={$user->ID}" ), 'kaiko_approve_' . $user->ID );
        echo '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:8px 4px;">' . esc_html( $user->user_email ) . '</td><td style="padding:8px 4px;">' . esc_html( $biz ) . '</td><td style="padding:8px 4px;"><a href="' . esc_url( $url ) . '" class="button button-primary button-small">Approve</a></td></tr>';
    }
    echo '</tbody></table><p style="margin-top:12px;"><a href="' . admin_url( 'users.php?role=kaiko_pending' ) . '">View all pending</a></p>';
}


/* ============================================
   11. THEME SUPPORT & MISC
   ============================================ */

add_action( 'after_setup_theme', 'kaiko_theme_support' );

function kaiko_theme_support() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 300, 'flex-height' => true, 'flex-width' => true ) );
}

add_action( 'init', 'kaiko_register_menus' );

function kaiko_register_menus() {
    register_nav_menus( array(
        'primary'  => 'Primary Navigation',
        'footer'   => 'Footer Navigation',
        'footer-2' => 'Footer Secondary',
        'footer-3' => 'Footer Support',
    ) );
}

// Custom image sizes
add_image_size( 'kaiko-hero', 1920, 1080, true );
add_image_size( 'kaiko-product-card', 800, 1000, true );
add_image_size( 'kaiko-product-thumb', 400, 500, true );
add_image_size( 'kaiko-category-banner', 1600, 600, true );


/* ============================================
   12. AJAX PRODUCT FILTERING
   ============================================ */

/**
 * Shop archive — apply URL filter params to the main query so that
 * deep-linked / no-JS form submissions behave the same as AJAX calls.
 */
/**
 * Shop archive — strip WC's default result-count + orderby dropdown;
 * Kaiko renders its own in the hero and sidebar.
 */
add_action( 'wp', 'kaiko_shop_strip_default_controls' );

function kaiko_shop_strip_default_controls() {
    if ( ! function_exists( 'is_shop' ) ) return;
    if ( ! ( is_shop() || is_product_category() || is_product_tag() ) ) return;
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count',    20 );
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
}

add_action( 'pre_get_posts', 'kaiko_shop_apply_url_filters' );

function kaiko_shop_apply_url_filters( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) {
        return;
    }
    if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_category() || is_product_tag() ) ) {
        return;
    }

    $tax_query  = (array) $q->get( 'tax_query' );
    $meta_query = (array) $q->get( 'meta_query' );

    if ( ! empty( $_GET['category'] ) && is_shop() ) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( wp_unslash( $_GET['category'] ) ),
        );
    }

    if ( ! empty( $_GET['species'] ) ) {
        $meta_query[] = array(
            'key'     => 'compatible_species_%_species_name',
            'value'   => sanitize_text_field( wp_unslash( $_GET['species'] ) ),
            'compare' => 'LIKE',
        );
    }

    if ( ! empty( $_GET['difficulty'] ) ) {
        $meta_query[] = array(
            'key'     => 'kaiko_difficulty',
            'value'   => sanitize_text_field( wp_unslash( $_GET['difficulty'] ) ),
            'compare' => '=',
        );
    }

    $min = isset( $_GET['min_price'] ) ? trim( wp_unslash( $_GET['min_price'] ) ) : '';
    $max = isset( $_GET['max_price'] ) ? trim( wp_unslash( $_GET['max_price'] ) ) : '';
    if ( is_numeric( $min ) && is_numeric( $max ) ) {
        $meta_query[] = array( 'key' => '_price', 'value' => array( (float) $min, (float) $max ), 'type' => 'NUMERIC', 'compare' => 'BETWEEN' );
    } elseif ( is_numeric( $min ) ) {
        $meta_query[] = array( 'key' => '_price', 'value' => (float) $min, 'type' => 'NUMERIC', 'compare' => '>=' );
    } elseif ( is_numeric( $max ) ) {
        $meta_query[] = array( 'key' => '_price', 'value' => (float) $max, 'type' => 'NUMERIC', 'compare' => '<=' );
    }

    if ( ! empty( $tax_query ) )  $q->set( 'tax_query',  $tax_query );
    if ( ! empty( $meta_query ) ) $q->set( 'meta_query', $meta_query );

    switch ( sanitize_text_field( wp_unslash( $_GET['orderby'] ?? '' ) ) ) {
        case 'price':
            $q->set( 'orderby',  'meta_value_num' );
            $q->set( 'meta_key', '_price' );
            $q->set( 'order',    'ASC' );
            break;
        case 'price-desc':
            $q->set( 'orderby',  'meta_value_num' );
            $q->set( 'meta_key', '_price' );
            $q->set( 'order',    'DESC' );
            break;
        case 'title':
            $q->set( 'orderby', 'title' );
            $q->set( 'order',   'ASC' );
            break;
    }
}

add_action( 'wp_ajax_kaiko_filter_products', 'kaiko_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_kaiko_filter_products', 'kaiko_ajax_filter_products' );

function kaiko_ajax_filter_products() {
    check_ajax_referer( 'kaiko_nonce', 'nonce' );

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => intval( $_POST['per_page'] ?? 12 ),
        'paged'          => max( 1, intval( $_POST['paged'] ?? 1 ) ),
        'post_status'    => 'publish',
        'tax_query'      => array(),
        'meta_query'     => array(),
    );

    // Category filter
    if ( ! empty( $_POST['category'] ) ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( wp_unslash( $_POST['category'] ) ),
        );
    }

    // Species filter (ACF repeater stores entries as compatible_species_{i}_species_name)
    if ( ! empty( $_POST['species'] ) ) {
        $args['meta_query'][] = array(
            'key'     => 'compatible_species_%_species_name',
            'value'   => sanitize_text_field( wp_unslash( $_POST['species'] ) ),
            'compare' => 'LIKE',
        );
    }

    // Difficulty filter (meta: kaiko_difficulty — may be unset on legacy products)
    if ( ! empty( $_POST['difficulty'] ) ) {
        $args['meta_query'][] = array(
            'key'     => 'kaiko_difficulty',
            'value'   => sanitize_text_field( wp_unslash( $_POST['difficulty'] ) ),
            'compare' => '=',
        );
    }

    // Price range — only apply when a value is numeric.
    $min_price = isset( $_POST['min_price'] ) ? trim( wp_unslash( $_POST['min_price'] ) ) : '';
    $max_price = isset( $_POST['max_price'] ) ? trim( wp_unslash( $_POST['max_price'] ) ) : '';
    $min_ok    = is_numeric( $min_price );
    $max_ok    = is_numeric( $max_price );

    if ( $min_ok && $max_ok ) {
        $args['meta_query'][] = array(
            'key'     => '_price',
            'value'   => array( (float) $min_price, (float) $max_price ),
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        );
    } elseif ( $min_ok ) {
        $args['meta_query'][] = array(
            'key'     => '_price',
            'value'   => (float) $min_price,
            'type'    => 'NUMERIC',
            'compare' => '>=',
        );
    } elseif ( $max_ok ) {
        $args['meta_query'][] = array(
            'key'     => '_price',
            'value'   => (float) $max_price,
            'type'    => 'NUMERIC',
            'compare' => '<=',
        );
    }

    if ( empty( $args['tax_query'] ) )  unset( $args['tax_query'] );
    if ( empty( $args['meta_query'] ) ) unset( $args['meta_query'] );

    // Sort
    $orderby = sanitize_text_field( wp_unslash( $_POST['orderby'] ?? 'date' ) );
    switch ( $orderby ) {
        case 'price':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'ASC';
            break;
        case 'price-desc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'DESC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
    }

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) {
        echo '<ul class="products columns-3">';
        while ( $query->have_posts() ) {
            $query->the_post();
            wc_get_template_part( 'content', 'product' );
        }
        echo '</ul>';
    } else {
        echo '<div class="kaiko-no-products">';
        echo '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
        echo '<h3>' . esc_html__( 'No products match your filters', 'kaiko-child' ) . '</h3>';
        echo '<p>' . esc_html__( 'Try adjusting or clearing your filters to see more products.', 'kaiko-child' ) . '</p>';
        echo '</div>';
    }
    $html = ob_get_clean();

    wp_reset_postdata();

    wp_send_json_success( array(
        'html'      => $html,
        'found'     => (int) $query->found_posts,
        'max_pages' => (int) $query->max_num_pages,
    ) );
}


/* ============================================
   13. WOOCOMMERCE PAGE WRAPPERS
   ============================================ */

/**
 * Add Kaiko page hero and wrapper to WooCommerce pages
 * that don't have their own custom templates (cart, checkout, account).
 */

// Remove default WooCommerce content wrappers
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

// Cart hero + layout are rendered by template-cart.php + inc/cart-layout.php —
// the legacy kaiko_cart_page_hero / _close hooks were removed to stop the hero
// from double-rendering inside the new template.

// Checkout page hero
add_action( 'woocommerce_before_checkout_form', 'kaiko_checkout_page_hero', 5 );

function kaiko_checkout_page_hero() {
    echo '<div class="kaiko-page-hero">
        <div class="kaiko-page-hero__tag kaiko-reveal">Checkout</div>
        <h1 class="kaiko-page-hero__title kaiko-hero-title">Complete Your Order</h1>
        <p class="kaiko-page-hero__subtitle">Secure checkout powered by Kaiko.</p>
    </div>
    <div class="kaiko-checkout-section">';
}

add_action( 'woocommerce_after_checkout_form', 'kaiko_checkout_page_close', 99 );

function kaiko_checkout_page_close() {
    echo '</div><!-- .kaiko-checkout-section -->';
}

// My Account page hero (for logged-in users)
add_action( 'woocommerce_account_navigation', 'kaiko_account_page_hero', 1 );

function kaiko_account_page_hero() {
    static $hero_rendered = false;
    if ( $hero_rendered ) return;
    $hero_rendered = true;

    $user = wp_get_current_user();
    $name = $user->first_name ? $user->first_name : $user->display_name;

    echo '<div class="kaiko-page-hero" style="margin-bottom:0;">
        <div class="kaiko-page-hero__tag kaiko-reveal">Trade Account</div>
        <h1 class="kaiko-page-hero__title kaiko-hero-title">Welcome back, ' . esc_html( $name ) . '</h1>
        <p class="kaiko-page-hero__subtitle">Manage your orders, account details, and trade preferences.</p>
    </div>';

    // Open wrapper — closed by kaiko_account_page_close via wp_footer
    echo '<div class="kaiko-account-section">';
}

// Close the account section wrapper
add_action( 'wp_footer', 'kaiko_account_page_close', 1 );

function kaiko_account_page_close() {
    if ( function_exists( 'is_account_page' ) && is_account_page() && is_user_logged_in() ) {
        echo '</div><!-- .kaiko-account-section -->';
    }
}

// My Account login/register page hero
add_action( 'woocommerce_before_customer_login_form', 'kaiko_login_page_hero', 5 );

function kaiko_login_page_hero() {
    echo '<div class="kaiko-page-hero">
        <div class="kaiko-page-hero__tag kaiko-reveal">Trade Partners</div>
        <h1 class="kaiko-page-hero__title kaiko-hero-title">Trade Access</h1>
        <p class="kaiko-page-hero__subtitle">Log in to your trade account or apply for wholesale access.</p>
    </div>
    <div class="kaiko-auth-section">';
}

add_action( 'woocommerce_after_customer_login_form', 'kaiko_login_page_close', 99 );

function kaiko_login_page_close() {
    echo '</div><!-- .kaiko-auth-section -->';
}


