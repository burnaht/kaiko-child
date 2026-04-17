<?php
/**
 * Template Name: Kaiko My Account
 * Template Post Type: page
 *
 * Role-aware account template with three states:
 *   A — Logged out:  login + apply forms (form-login.php override)
 *   B — kaiko_pending: application status dashboard, no shop access
 *   C — Approved (kaiko_trade / customer / admin / shop_manager): full dashboard
 *
 * Bypasses the [woocommerce_my_account] shortcode entirely. Sub-endpoints
 * (orders, addresses, edit-account, etc.) are rendered inside our own layout
 * by dispatching the same action WC uses internally.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$current_user    = wp_get_current_user();
$logged_in       = is_user_logged_in();
$is_admin_role   = $logged_in && user_can( $current_user, 'manage_options' );
$is_shop_manager = $logged_in && user_can( $current_user, 'manage_woocommerce' );
$user_roles      = $logged_in ? (array) $current_user->roles : array();

$is_pending = $logged_in
	&& in_array( 'kaiko_pending', $user_roles, true )
	&& ! $is_admin_role
	&& ! $is_shop_manager;

$is_approved = $logged_in && ! $is_pending;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700&display=swap">
<?php wp_head(); ?>

<style>
/* ============================================
   KAIKO DESIGN TOKENS — Liquid Glass
   ============================================ */
:root {
  --k-teal:        #1a5c52;
  --k-deep-teal:   #134840;
  --k-lime:        #b8d435;
  --k-gold:        #c89b3c;
  --k-gold-soft:   #d4a853;

  --k-dark:        #1C1917;
  --k-charcoal:    #292524;
  --k-stone-700:   #44403C;
  --k-stone-500:   #78716C;
  --k-stone-400:   #A8A29E;
  --k-stone-300:   #D6D3D1;
  --k-stone-200:   #E7E5E4;
  --k-stone-100:   #F5F5F4;
  --k-stone-50:    #FAFAF9;
  --k-white:       #FFFFFF;

  --k-glass-bg:        rgba(255,255,255,0.72);
  --k-glass-bg-strong: rgba(255,255,255,0.88);
  --k-glass-border:    rgba(255,255,255,0.45);
  --k-glass-shadow:    0 8px 32px rgba(28,25,23,0.08), 0 2px 8px rgba(28,25,23,0.04);
  --k-glass-shadow-lg: 0 16px 48px rgba(28,25,23,0.12), 0 4px 16px rgba(28,25,23,0.06);
  --k-glass-blur:      blur(20px);
  --k-glass-blur-lg:   blur(32px);

  --k-font-display: 'Cormorant Garamond', 'Georgia', serif;
  --k-font-body:    'Inter', 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;

  --k-radius-sm:  8px;
  --k-radius-md:  12px;
  --k-radius-lg:  20px;
  --k-radius-xl:  28px;

  --k-ease:       cubic-bezier(0.23, 1, 0.32, 1);
  --k-duration:   0.25s;
  --k-duration-lg: 0.4s;
}

/* ============================================
   RESET & BASE
   ============================================ */
body.kaiko-myaccount-page {
  margin: 0; padding: 0;
  background: var(--k-stone-50);
  font-family: var(--k-font-body);
  color: var(--k-dark);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  line-height: 1.6;
}

.kaiko-myaccount-wrap {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: relative;
}
.kaiko-myaccount-wrap *,
.kaiko-myaccount-wrap *::before,
.kaiko-myaccount-wrap *::after { box-sizing: border-box; }

.kaiko-myaccount-wrap::before {
  content: '';
  position: fixed; inset: 0;
  background: radial-gradient(ellipse at 20% 0%, rgba(26,92,82,0.04) 0%, transparent 60%),
              radial-gradient(ellipse at 80% 100%, rgba(200,155,60,0.03) 0%, transparent 60%);
  pointer-events: none; z-index: 0;
}

/* ============================================
   NAVIGATION — Frosted glass bar
   ============================================ */
.kaiko-myaccount-wrap .kaiko-nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
  background: var(--k-glass-bg-strong);
  backdrop-filter: var(--k-glass-blur);
  -webkit-backdrop-filter: var(--k-glass-blur);
  border-bottom: 1px solid var(--k-glass-border);
  padding: 0 4rem;
  height: 72px;
  display: flex; align-items: center; justify-content: space-between;
  transition: box-shadow var(--k-duration) var(--k-ease);
}
.kaiko-myaccount-wrap .kaiko-nav.scrolled {
  box-shadow: 0 4px 24px rgba(28,25,23,0.06);
}

.kaiko-myaccount-wrap .kaiko-nav-logo {
  font-family: var(--k-font-display);
  font-size: 1.85rem; font-weight: 700;
  letter-spacing: 0.08em; color: var(--k-dark);
  text-decoration: none;
}

.kaiko-myaccount-wrap .kaiko-nav-links {
  display: flex; gap: 3rem; align-items: center;
}
.kaiko-myaccount-wrap .kaiko-nav-links a {
  font-family: var(--k-font-body);
  font-size: 0.82rem; font-weight: 500;
  color: var(--k-stone-500); text-decoration: none;
  letter-spacing: 0.06em; text-transform: uppercase;
  transition: color var(--k-duration) var(--k-ease);
  position: relative;
}
.kaiko-myaccount-wrap .kaiko-nav-links a::after {
  content: '';
  position: absolute; bottom: -4px; left: 0; right: 0;
  height: 1.5px; background: var(--k-teal);
  transform: scaleX(0); transform-origin: center;
  transition: transform var(--k-duration) var(--k-ease);
}
.kaiko-myaccount-wrap .kaiko-nav-links a:hover { color: var(--k-dark); }
.kaiko-myaccount-wrap .kaiko-nav-links a:hover::after { transform: scaleX(1); }
.kaiko-myaccount-wrap .kaiko-nav-links a.active { color: var(--k-teal); }
.kaiko-myaccount-wrap .kaiko-nav-links a.active::after { transform: scaleX(1); }

.kaiko-myaccount-wrap .kaiko-nav-cta {
  background: var(--k-teal) !important; color: var(--k-white) !important;
  padding: 10px 24px !important; border-radius: var(--k-radius-sm) !important;
  font-size: 0.78rem !important; text-transform: uppercase !important;
  letter-spacing: 0.08em !important; font-weight: 600 !important;
  transition: all var(--k-duration) var(--k-ease) !important;
}
.kaiko-myaccount-wrap .kaiko-nav-cta::after { display: none !important; }
.kaiko-myaccount-wrap .kaiko-nav-cta:hover {
  background: var(--k-deep-teal) !important;
  box-shadow: 0 4px 16px rgba(26,92,82,0.25) !important;
  transform: translateY(-1px) !important;
}

/* HAMBURGER */
.kaiko-myaccount-wrap .kaiko-hamburger {
  display: none; background: none; border: none; cursor: pointer;
  padding: 8px; z-index: 1001; position: relative;
  flex-direction: column; justify-content: center; gap: 5px;
  width: 40px; height: 40px;
}
.kaiko-myaccount-wrap .kaiko-hamburger span {
  display: block; width: 22px; height: 1.5px; background: var(--k-dark);
  border-radius: 2px;
  transition: all 0.35s var(--k-ease);
  transform-origin: center;
}
.kaiko-myaccount-wrap .kaiko-hamburger.active span:nth-child(1) {
  transform: translateY(6.5px) rotate(45deg);
}
.kaiko-myaccount-wrap .kaiko-hamburger.active span:nth-child(2) {
  opacity: 0; transform: scaleX(0);
}
.kaiko-myaccount-wrap .kaiko-hamburger.active span:nth-child(3) {
  transform: translateY(-6.5px) rotate(-45deg);
}
.kaiko-myaccount-wrap .kaiko-hamburger:focus-visible {
  outline: 2px solid var(--k-teal);
  outline-offset: 4px;
  border-radius: 4px;
}

/* MOBILE MENU */
.kaiko-mobile-overlay {
  position: fixed; inset: 0;
  background: rgba(28,25,23,0.35);
  backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
  z-index: 998; opacity: 0; visibility: hidden;
  transition: opacity 0.35s var(--k-ease), visibility 0.35s var(--k-ease);
}
.kaiko-mobile-overlay.active { opacity: 1; visibility: visible; }

.kaiko-mobile-menu {
  position: fixed; top: 0; right: -340px; width: 340px; max-width: 88vw;
  height: 100vh;
  background: var(--k-glass-bg-strong);
  backdrop-filter: var(--k-glass-blur-lg);
  -webkit-backdrop-filter: var(--k-glass-blur-lg);
  z-index: 999;
  transition: right 0.4s var(--k-ease);
  display: flex; flex-direction: column;
  box-shadow: -12px 0 48px rgba(28,25,23,0.1);
  overflow-y: auto;
}
.kaiko-mobile-menu.open { right: 0; }
.kaiko-mobile-menu__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 24px 28px;
  border-bottom: 1px solid var(--k-stone-200);
}
.kaiko-mobile-menu__logo {
  font-family: var(--k-font-display);
  font-size: 1.5rem; font-weight: 700;
  color: var(--k-dark); letter-spacing: 0.08em;
}
.kaiko-mobile-menu__close {
  background: none; border: none; cursor: pointer; padding: 6px;
  color: var(--k-stone-400);
  transition: color var(--k-duration) var(--k-ease);
  border-radius: 6px;
}
.kaiko-mobile-menu__close:hover { color: var(--k-dark); }

.kaiko-mobile-menu__links {
  flex: 1; display: flex; flex-direction: column; padding: 12px 0;
}
.kaiko-mobile-menu__links a {
  display: block; padding: 16px 28px;
  font-family: var(--k-font-body);
  font-size: 0.95rem; font-weight: 500; color: var(--k-stone-700);
  text-decoration: none;
  letter-spacing: 0.03em;
  transition: background var(--k-duration) var(--k-ease), color var(--k-duration) var(--k-ease);
  border-bottom: 1px solid rgba(0,0,0,0.03);
}
.kaiko-mobile-menu__links a:hover {
  background: rgba(26,92,82,0.04); color: var(--k-teal);
}
.kaiko-mobile-menu__footer {
  padding: 24px 28px;
  border-top: 1px solid var(--k-stone-200);
  display: flex; flex-direction: column; gap: 12px;
}
.kaiko-mobile-menu__footer .btn-primary {
  display: block; text-align: center;
  background: var(--k-teal); color: var(--k-white);
  padding: 14px 24px; border-radius: var(--k-radius-sm);
  font-weight: 600; text-decoration: none; font-size: 0.9rem;
  letter-spacing: 0.04em;
  transition: all var(--k-duration) var(--k-ease);
}
.kaiko-mobile-menu__footer .btn-primary:hover { background: var(--k-deep-teal); }
.kaiko-mobile-menu__footer .btn-secondary {
  display: block; text-align: center;
  background: transparent; color: var(--k-stone-700);
  padding: 14px 24px; border-radius: var(--k-radius-sm);
  border: 1px solid var(--k-stone-200);
  font-weight: 500; text-decoration: none; font-size: 0.9rem;
  transition: all var(--k-duration) var(--k-ease);
}
.kaiko-mobile-menu__footer .btn-secondary:hover {
  border-color: var(--k-stone-300); background: var(--k-stone-50);
}

/* ============================================
   PAGE CONTENT AREA
   ============================================ */
.kaiko-myaccount-content {
  flex: 1;
  padding: 128px 4rem 100px;
  max-width: 1140px;
  margin: 0 auto; width: 100%;
  position: relative; z-index: 1;
}

/* ============================================
   PAGE HEADER
   ============================================ */
.kaiko-page-header {
  text-align: center; margin-bottom: 56px;
}
.kaiko-page-header .kaiko-tag {
  display: inline-flex; align-items: center; gap: 6px;
  font-family: var(--k-font-body);
  font-size: 0.68rem; font-weight: 600; letter-spacing: 0.18em;
  text-transform: uppercase; color: var(--k-teal);
  background: rgba(26,92,82,0.06);
  padding: 7px 18px;
  border-radius: 100px; margin-bottom: 20px;
  border: 1px solid rgba(26,92,82,0.1);
}
.kaiko-page-header .kaiko-tag svg {
  width: 14px; height: 14px; stroke: var(--k-teal);
}
.kaiko-page-header h1 {
  font-family: var(--k-font-display);
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 600;
  color: var(--k-dark);
  margin: 0 0 16px;
  letter-spacing: -0.01em;
  line-height: 1.15;
}
.kaiko-page-header p {
  font-size: 1rem; color: var(--k-stone-500);
  max-width: 520px; margin: 0 auto; line-height: 1.75;
  font-weight: 400;
}

/* ============================================
   TRADE INTRO (logged-out only)
   ============================================ */
.kaiko-trade-intro {
  background: var(--k-glass-bg);
  backdrop-filter: var(--k-glass-blur);
  -webkit-backdrop-filter: var(--k-glass-blur);
  border: 1px solid var(--k-glass-border);
  border-radius: var(--k-radius-xl);
  padding: 44px 52px;
  margin-bottom: 44px;
  text-align: center;
  box-shadow: var(--k-glass-shadow);
  transition: box-shadow var(--k-duration-lg) var(--k-ease);
}
.kaiko-trade-intro:hover { box-shadow: var(--k-glass-shadow-lg); }
.kaiko-trade-intro h3 {
  font-family: var(--k-font-display);
  font-size: 1.5rem; font-weight: 600;
  color: var(--k-dark); margin: 0 0 10px;
  letter-spacing: 0.01em;
}
.kaiko-trade-intro > p {
  font-size: 0.92rem; color: var(--k-stone-500);
  max-width: 560px; margin: 0 auto 28px; line-height: 1.75;
}
.kaiko-trade-benefits {
  display: flex; flex-wrap: wrap; justify-content: center; gap: 16px 36px;
}
.kaiko-trade-benefit {
  display: flex; align-items: center; gap: 10px;
  font-size: 0.88rem; font-weight: 500; color: var(--k-stone-700);
}
.kaiko-trade-benefit svg {
  width: 20px; height: 20px;
  color: var(--k-teal); flex-shrink: 0;
}

/* ============================================
   AUTH GRID — login + register cards
   ============================================ */
.kaiko-auth-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 36px; align-items: start;
}
.kaiko-auth-card {
  background: var(--k-glass-bg);
  backdrop-filter: var(--k-glass-blur);
  -webkit-backdrop-filter: var(--k-glass-blur);
  border: 1px solid var(--k-glass-border);
  border-radius: var(--k-radius-xl);
  padding: 44px;
  box-shadow: var(--k-glass-shadow);
  transition: box-shadow var(--k-duration-lg) var(--k-ease),
              transform var(--k-duration-lg) var(--k-ease);
}
.kaiko-auth-card:hover {
  box-shadow: var(--k-glass-shadow-lg);
  transform: translateY(-2px);
}
.kaiko-auth-card h2 {
  font-family: var(--k-font-display);
  font-size: 1.65rem; font-weight: 600;
  color: var(--k-dark); margin: 0 0 8px;
  letter-spacing: 0.01em;
}
.kaiko-auth-card .kaiko-card-subtitle {
  font-size: 0.88rem; color: var(--k-stone-400);
  margin: 0 0 32px; line-height: 1.6;
}

/* ============================================
   FORMS
   ============================================ */
.kaiko-myaccount-wrap .woocommerce-form-row,
.kaiko-myaccount-wrap .form-row {
  margin-bottom: 22px;
}
.kaiko-myaccount-wrap label {
  display: block;
  font-family: var(--k-font-body);
  font-size: 0.78rem; font-weight: 600;
  color: var(--k-stone-700);
  margin-bottom: 8px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}
.kaiko-myaccount-wrap label .required { color: var(--k-gold); }

.kaiko-myaccount-wrap input[type="text"],
.kaiko-myaccount-wrap input[type="email"],
.kaiko-myaccount-wrap input[type="password"],
.kaiko-myaccount-wrap input[type="tel"],
.kaiko-myaccount-wrap input[type="number"],
.kaiko-myaccount-wrap select,
.kaiko-myaccount-wrap textarea {
  width: 100%; padding: 13px 18px;
  border: 1px solid var(--k-stone-200);
  border-radius: var(--k-radius-sm);
  font-family: var(--k-font-body); font-size: 0.92rem;
  color: var(--k-dark);
  background: rgba(255,255,255,0.7);
  transition: border-color var(--k-duration) var(--k-ease),
              box-shadow var(--k-duration) var(--k-ease),
              background var(--k-duration) var(--k-ease);
  -webkit-appearance: none; appearance: none;
}
.kaiko-myaccount-wrap input::placeholder,
.kaiko-myaccount-wrap textarea::placeholder {
  color: var(--k-stone-400); font-weight: 400;
}
.kaiko-myaccount-wrap input:hover,
.kaiko-myaccount-wrap select:hover,
.kaiko-myaccount-wrap textarea:hover {
  border-color: var(--k-stone-300);
  background: rgba(255,255,255,0.9);
}
.kaiko-myaccount-wrap input:focus,
.kaiko-myaccount-wrap select:focus,
.kaiko-myaccount-wrap textarea:focus {
  outline: none; border-color: var(--k-teal);
  box-shadow: 0 0 0 3px rgba(26,92,82,0.1);
  background: var(--k-white);
}
.kaiko-myaccount-wrap select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2378716C' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 16px center;
  padding-right: 40px;
}
.kaiko-myaccount-wrap textarea { min-height: 100px; resize: vertical; }

.kaiko-myaccount-wrap .woocommerce-form__label-for-checkbox {
  display: flex; align-items: center; gap: 10px; cursor: pointer;
  text-transform: none; font-size: 0.88rem; font-weight: 500;
  color: var(--k-stone-500);
}
.kaiko-myaccount-wrap .woocommerce-form__label-for-checkbox input[type="checkbox"] {
  width: 18px; height: 18px; accent-color: var(--k-teal);
  cursor: pointer;
}

/* BUTTONS */
.kaiko-myaccount-wrap button[type="submit"],
.kaiko-myaccount-wrap .button,
.kaiko-myaccount-wrap .woocommerce-Button {
  display: inline-flex; align-items: center; justify-content: center;
  gap: 8px; width: 100%; padding: 15px 28px;
  background: var(--k-teal); color: var(--k-white);
  border: none; border-radius: var(--k-radius-sm);
  font-family: var(--k-font-body); font-size: 0.88rem;
  font-weight: 600; cursor: pointer;
  letter-spacing: 0.06em; text-transform: uppercase;
  transition: all var(--k-duration) var(--k-ease);
  text-decoration: none; text-align: center;
  position: relative; overflow: hidden;
}
.kaiko-myaccount-wrap button[type="submit"]:hover,
.kaiko-myaccount-wrap .button:hover,
.kaiko-myaccount-wrap .woocommerce-Button:hover {
  background: var(--k-deep-teal);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26,92,82,0.2);
}

.kaiko-myaccount-wrap .lost_password {
  margin-top: 18px; text-align: center;
}
.kaiko-myaccount-wrap .lost_password a {
  font-size: 0.82rem; color: var(--k-stone-400);
  text-decoration: none;
  transition: color var(--k-duration) var(--k-ease);
  font-weight: 500;
}
.kaiko-myaccount-wrap .lost_password a:hover { color: var(--k-teal); }

.kaiko-myaccount-wrap .woocommerce-form-register > p:not(.form-row) {
  font-size: 0.82rem; color: var(--k-stone-500);
  background: var(--k-stone-100); padding: 14px 18px;
  border-radius: var(--k-radius-sm); margin-bottom: 22px;
  line-height: 1.65;
  border: 1px solid var(--k-stone-200);
}

/* WC NOTICES */
.kaiko-myaccount-wrap .woocommerce-error,
.kaiko-myaccount-wrap .woocommerce-message,
.kaiko-myaccount-wrap .woocommerce-info {
  background: var(--k-glass-bg);
  backdrop-filter: var(--k-glass-blur);
  -webkit-backdrop-filter: var(--k-glass-blur);
  border: 1px solid var(--k-glass-border);
  border-radius: var(--k-radius-md); padding: 18px 24px;
  margin-bottom: 28px; font-size: 0.88rem;
  list-style: none; line-height: 1.6;
  box-shadow: var(--k-glass-shadow);
}
.kaiko-myaccount-wrap .woocommerce-error {
  border-color: rgba(220,38,38,0.25);
  background: rgba(254,242,242,0.8);
  color: #991b1b;
}
.kaiko-myaccount-wrap .woocommerce-message {
  border-color: rgba(26,92,82,0.2);
  color: var(--k-teal);
}

/* ============================================
   ACCOUNT DASHBOARD LAYOUT (pending + approved)
   ============================================ */
.kaiko-account-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 36px;
  align-items: start;
}

.kaiko-account-nav {
  background: var(--k-glass-bg);
  backdrop-filter: var(--k-glass-blur);
  -webkit-backdrop-filter: var(--k-glass-blur);
  border: 1px solid var(--k-glass-border);
  border-radius: var(--k-radius-lg);
  overflow: hidden;
  box-shadow: var(--k-glass-shadow);
}
.kaiko-account-nav ul { list-style: none; margin: 0; padding: 10px 0; }
.kaiko-account-nav li a {
  display: flex; align-items: center;
  padding: 14px 28px;
  font-family: var(--k-font-body);
  font-size: 0.88rem; font-weight: 500;
  color: var(--k-stone-500);
  text-decoration: none;
  transition: all var(--k-duration) var(--k-ease);
  border-left: 3px solid transparent;
}
.kaiko-account-nav li a:hover {
  background: rgba(26,92,82,0.04);
  color: var(--k-teal);
}
.kaiko-account-nav li.active a {
  background: rgba(26,92,82,0.06);
  color: var(--k-teal);
  font-weight: 600;
  border-left-color: var(--k-teal);
}

.kaiko-account-content {
  background: var(--k-glass-bg);
  backdrop-filter: var(--k-glass-blur);
  -webkit-backdrop-filter: var(--k-glass-blur);
  border: 1px solid var(--k-glass-border);
  border-radius: var(--k-radius-lg);
  padding: 44px;
  box-shadow: var(--k-glass-shadow);
}
.kaiko-account-content > h2 {
  font-family: var(--k-font-display);
  font-size: 1.65rem; font-weight: 600;
  margin: 0 0 8px; color: var(--k-dark);
  letter-spacing: 0.01em;
}
.kaiko-account-content .kaiko-greeting {
  font-size: 0.92rem; color: var(--k-stone-500);
  margin: 0 0 32px; line-height: 1.7;
}
.kaiko-account-content a {
  color: var(--k-teal); text-decoration: none; font-weight: 500;
  transition: color var(--k-duration) var(--k-ease);
}
.kaiko-account-content a:hover { color: var(--k-deep-teal); }

/* Account notice */
.kaiko-account-notice {
  padding: 22px 28px; border-radius: var(--k-radius-md);
  margin-bottom: 28px;
  border: 1px solid var(--k-glass-border);
  background: var(--k-glass-bg);
  backdrop-filter: var(--k-glass-blur);
  -webkit-backdrop-filter: var(--k-glass-blur);
}
.kaiko-account-notice h3 {
  font-family: var(--k-font-display); font-size: 1.15rem;
  font-weight: 600; margin: 0 0 6px; color: var(--k-dark);
}
.kaiko-account-notice p {
  font-size: 0.88rem; margin: 0; line-height: 1.65; color: var(--k-stone-500);
}
.kaiko-account-notice.info {
  background: rgba(26,92,82,0.04); border-color: rgba(26,92,82,0.12);
}
.kaiko-account-notice.success {
  background: rgba(26,92,82,0.04); border-color: var(--k-teal);
}
.kaiko-account-notice.pending {
  background: rgba(200,155,60,0.08);
  border-color: rgba(200,155,60,0.35);
}
.kaiko-account-notice.pending h3 { color: var(--k-charcoal); }

/* Stats grid */
.kaiko-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
  margin-bottom: 32px;
}
.kaiko-stat-card {
  background: var(--k-stone-50);
  border: 1px solid var(--k-stone-200);
  border-radius: var(--k-radius-md);
  padding: 22px;
  transition: box-shadow var(--k-duration) var(--k-ease);
}
.kaiko-stat-card:hover { box-shadow: 0 4px 16px rgba(28,25,23,0.06); }
.kaiko-stat-card .label {
  font-size: 0.7rem; font-weight: 600;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--k-stone-500); margin: 0 0 8px;
}
.kaiko-stat-card .value {
  font-family: var(--k-font-display);
  font-size: 1.85rem; font-weight: 700;
  color: var(--k-dark); line-height: 1; margin: 0;
  letter-spacing: -0.01em;
}
.kaiko-stat-card .delta {
  font-size: 0.78rem; color: var(--k-teal);
  margin-top: 6px; font-weight: 500;
}
.kaiko-stat-card .value.is-text {
  font-size: 1.1rem; padding-top: 4px;
}

/* Section heading */
.kaiko-section-heading {
  font-family: var(--k-font-display);
  font-size: 1.15rem; font-weight: 600;
  color: var(--k-dark);
  margin: 32px 0 18px;
  letter-spacing: 0.01em;
}

/* Orders table */
.kaiko-orders-table {
  width: 100%; border-collapse: collapse; font-size: 0.88rem;
}
.kaiko-orders-table th {
  font-family: var(--k-font-body);
  font-size: 0.72rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.1em;
  color: var(--k-stone-400);
  padding: 14px 18px;
  border-bottom: 1px solid var(--k-stone-200);
  text-align: left;
}
.kaiko-orders-table td {
  padding: 16px 18px;
  border-bottom: 1px solid var(--k-stone-100);
  color: var(--k-dark);
  transition: background var(--k-duration) var(--k-ease);
}
.kaiko-orders-table tr:hover td { background: rgba(26,92,82,0.02); }
.kaiko-orders-table .status {
  display: inline-block;
  padding: 4px 10px; border-radius: 100px;
  font-size: 0.72rem; font-weight: 600;
  letter-spacing: 0.04em; text-transform: uppercase;
}
.kaiko-orders-table .status.completed { background: rgba(26,92,82,0.08); color: var(--k-teal); }
.kaiko-orders-table .status.processing { background: rgba(200,155,60,0.1); color: var(--k-gold); }
.kaiko-orders-table .status.pending,
.kaiko-orders-table .status.on-hold,
.kaiko-orders-table .status.cancelled,
.kaiko-orders-table .status.refunded,
.kaiko-orders-table .status.failed { background: var(--k-stone-100); color: var(--k-stone-500); }
.kaiko-orders-table .button-sm {
  display: inline-block;
  background: var(--k-teal); color: var(--k-white);
  padding: 7px 14px; border-radius: var(--k-radius-sm);
  font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em;
  text-decoration: none; font-weight: 600;
  width: auto;
  transition: all var(--k-duration) var(--k-ease);
}
.kaiko-orders-table .button-sm:hover { background: var(--k-deep-teal); color: var(--k-white); }

.kaiko-empty-state {
  background: var(--k-stone-50);
  border: 1px dashed var(--k-stone-200);
  border-radius: var(--k-radius-md);
  padding: 32px; text-align: center;
  color: var(--k-stone-500);
  font-size: 0.92rem;
}
.kaiko-empty-state a { color: var(--k-teal); font-weight: 600; }

/* WC native content inside our shell (orders/edit-account/etc.) */
.kaiko-myaccount-wrap .woocommerce-MyAccount-content {
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
  box-shadow: none !important;
  width: 100% !important; max-width: 100% !important;
  float: none !important; margin: 0 !important;
}
.kaiko-myaccount-wrap .woocommerce-MyAccount-navigation {
  display: none !important;
}
.kaiko-myaccount-wrap table.woocommerce-orders-table,
.kaiko-myaccount-wrap table.woocommerce-table {
  width: 100%; border-collapse: collapse; font-size: 0.88rem;
}
.kaiko-myaccount-wrap .woocommerce-table th {
  font-size: 0.72rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.1em;
  color: var(--k-stone-400);
  padding: 14px 18px;
  border-bottom: 1px solid var(--k-stone-200);
  text-align: left;
}
.kaiko-myaccount-wrap .woocommerce-table td {
  padding: 16px 18px;
  border-bottom: 1px solid var(--k-stone-100);
  color: var(--k-dark);
}
.kaiko-myaccount-wrap .woocommerce-Addresses {
  display: grid; grid-template-columns: 1fr 1fr; gap: 28px;
}
.kaiko-myaccount-wrap .woocommerce-Address {
  background: var(--k-stone-50);
  border: 1px solid var(--k-stone-200);
  border-radius: var(--k-radius-md);
  padding: 28px;
}
.kaiko-myaccount-wrap .woocommerce-Address h3 {
  font-family: var(--k-font-display);
  font-size: 1.05rem; font-weight: 600;
  margin: 0 0 14px;
  letter-spacing: 0.02em;
}

/* Suppress "lost your password" form inside dashboard if it tries to render */
.kaiko-myaccount-wrap .woocommerce-MyAccount-content .lost_password { display: none; }

/* CTA helper button */
.kaiko-cta-button {
  display: inline-flex; align-items: center; justify-content: center;
  background: var(--k-teal); color: var(--k-white) !important;
  padding: 12px 26px;
  border-radius: var(--k-radius-sm);
  font-size: 0.82rem; font-weight: 600;
  letter-spacing: 0.06em; text-transform: uppercase;
  text-decoration: none;
  width: auto !important;
  transition: all var(--k-duration) var(--k-ease);
}
.kaiko-cta-button:hover {
  background: var(--k-deep-teal); color: var(--k-white) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26,92,82,0.2);
}

/* ============================================
   FOOTER
   ============================================ */
.kaiko-myaccount-footer {
  background: var(--k-dark);
  color: var(--k-stone-400);
  padding: 72px 4rem 32px;
  position: relative; z-index: 1;
}
.kaiko-myaccount-footer::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, var(--k-gold-soft), transparent);
  opacity: 0.3;
}
.kaiko-footer-inner {
  max-width: 1140px; margin: 0 auto;
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 60px; margin-bottom: 48px;
}
.kaiko-footer-brand h3 {
  font-family: var(--k-font-display); font-size: 1.75rem;
  font-weight: 600; color: var(--k-white); margin: 0 0 14px;
  letter-spacing: 0.05em;
}
.kaiko-footer-brand p {
  font-size: 0.85rem; line-height: 1.75; margin: 0; max-width: 280px;
  color: var(--k-stone-500);
}
.kaiko-footer-col h4 {
  font-family: var(--k-font-body); font-size: 0.7rem;
  font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--k-stone-300); margin: 0 0 22px;
}
.kaiko-footer-col ul { list-style: none; padding: 0; margin: 0; }
.kaiko-footer-col li { margin-bottom: 12px; }
.kaiko-footer-col a {
  color: var(--k-stone-500); text-decoration: none; font-size: 0.85rem;
  transition: color var(--k-duration) var(--k-ease);
  font-weight: 400;
}
.kaiko-footer-col a:hover { color: var(--k-gold-soft); }
.kaiko-footer-bottom {
  max-width: 1140px; margin: 0 auto;
  border-top: 1px solid rgba(255,255,255,0.06);
  padding-top: 28px;
  display: flex; justify-content: space-between;
  align-items: center; font-size: 0.78rem;
  color: var(--k-stone-500);
}

/* HIDE WOODMART ELEMENTS */
.whb-header, .woodmart-prefooter, .footer-container,
.website-wrapper > footer, .page-title, .breadcrumbs,
.woodmart-breadcrumbs, .title-size-default, .woodmart-main-container,
.wd-toolbar, .wd-sticky-btn, .woodmart-sticky-toolbar,
.wd-toolbar-shop, .whb-sticky-toolbar,
div[class*="wd-toolbar"], div[class*="sticky-toolbar"],
#wp-admin-bar-root-default,
.wd-footer { display: none !important; }
.website-wrapper { padding-top: 0 !important; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .kaiko-account-layout { grid-template-columns: 1fr; }
  .kaiko-footer-inner { grid-template-columns: 1fr 1fr; }
  .kaiko-myaccount-content { padding: 120px 3rem 80px; }
}

@media (max-width: 768px) {
  .kaiko-myaccount-wrap .kaiko-nav {
    padding: 0 1.25rem !important; height: 64px !important;
  }
  .kaiko-myaccount-wrap .kaiko-nav-links { display: none !important; }
  .kaiko-myaccount-wrap .kaiko-hamburger { display: flex !important; }

  .kaiko-myaccount-content { padding: 96px 1.25rem 60px; }
  .kaiko-page-header { margin-bottom: 36px; }
  .kaiko-page-header h1 { font-size: clamp(1.75rem, 6vw, 2.25rem); }

  .kaiko-trade-intro { padding: 28px 22px; border-radius: var(--k-radius-lg); }
  .kaiko-trade-benefits { flex-direction: column; align-items: flex-start; gap: 14px; }

  .kaiko-auth-grid { grid-template-columns: 1fr; gap: 24px; }
  .kaiko-auth-card { padding: 32px 24px; border-radius: var(--k-radius-lg); }

  .kaiko-account-content { padding: 28px 22px; }
  .kaiko-stats-grid { grid-template-columns: 1fr; }
  .kaiko-myaccount-wrap .woocommerce-Addresses { grid-template-columns: 1fr; }

  .kaiko-myaccount-footer { padding: 48px 1.25rem 24px; }
  .kaiko-footer-inner { grid-template-columns: 1fr; gap: 36px; }
  .kaiko-footer-bottom { flex-direction: column; gap: 10px; text-align: center; }
}

@media (max-width: 375px) {
  .kaiko-auth-card { padding: 24px 18px; }
  .kaiko-myaccount-content { padding: 88px 1rem 48px; }
}
</style>
</head>

<body <?php body_class( 'kaiko-page kaiko-myaccount-page' ); ?>>
<?php wp_body_open(); ?>

<div class="kaiko-myaccount-wrap">

  <!-- NAVIGATION -->
  <nav class="kaiko-nav" id="kaiko-nav" role="navigation" aria-label="Main navigation">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kaiko-nav-logo">KAIKO</a>
    <div class="kaiko-nav-links">
      <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>">Products</a>
      <?php if ( $is_approved ) : ?>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop</a>
      <?php endif; ?>
      <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
      <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="kaiko-nav-cta active">
        <?php echo $logged_in ? esc_html__( 'My Account', 'kaiko-child' ) : esc_html__( 'Trade Login', 'kaiko-child' ); ?>
      </a>
    </div>
    <button class="kaiko-hamburger" id="kaiko-hamburger" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- MOBILE OVERLAY -->
  <div class="kaiko-mobile-overlay" id="kaiko-mobile-overlay"></div>

  <!-- MOBILE MENU -->
  <div class="kaiko-mobile-menu" id="kaiko-mobile-menu" role="dialog" aria-label="Mobile menu">
    <div class="kaiko-mobile-menu__header">
      <span class="kaiko-mobile-menu__logo">KAIKO</span>
      <button class="kaiko-mobile-menu__close" id="kaiko-mobile-close" aria-label="Close menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="kaiko-mobile-menu__links">
      <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>">Products</a>
      <?php if ( $is_approved ) : ?>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop</a>
      <?php endif; ?>
      <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
    </div>
    <div class="kaiko-mobile-menu__footer">
      <?php if ( $logged_in ) : ?>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn-primary">My Account</a>
        <a href="<?php echo esc_url( wp_logout_url( wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="btn-secondary">Log Out</a>
      <?php else : ?>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn-primary">Trade Login</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="kaiko-myaccount-content">

    <?php
    // ─────────────── Page header ───────────────
    if ( ! $logged_in ) :
      // Logged-out: no page header — go straight into the login/signup cards.
    elseif ( $is_pending ) :
      ?>
      <div class="kaiko-page-header">
        <div class="kaiko-tag">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Trade Application
        </div>
        <h1>My Account</h1>
        <p>Your application is being reviewed. We&rsquo;ll be in touch within 24 hours.</p>
      </div>
      <?php
    else :
      ?>
      <div class="kaiko-page-header">
        <div class="kaiko-tag">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Your Account
        </div>
        <h1>My Account</h1>
        <p>Manage your orders, addresses, and account details.</p>
      </div>
      <?php
    endif;

    // ─────────────── State routing ───────────────
    if ( ! $logged_in ) {
      get_template_part( 'template-parts/kaiko-my-account', 'logged-out' );
    } elseif ( $is_pending ) {
      get_template_part( 'template-parts/kaiko-my-account', 'pending' );
    } else {
      get_template_part( 'template-parts/kaiko-my-account', 'approved' );
    }
    ?>

  </div>

  <!-- FOOTER -->
  <footer class="kaiko-myaccount-footer" role="contentinfo">
    <div class="kaiko-footer-inner">
      <div class="kaiko-footer-brand">
        <h3>KAIKO</h3>
        <p>Premium reptile and exotic pet supplies, designed by keepers for keepers. Handcrafted in the UK.</p>
      </div>
      <div class="kaiko-footer-col">
        <h4>Shop</h4>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/products/' ) ); ?>">All Products</a></li>
          <li><a href="#">Feeding Bowls</a></li>
          <li><a href="#">Humidity Hides</a></li>
          <li><a href="#">Accessories</a></li>
        </ul>
      </div>
      <div class="kaiko-footer-col">
        <h4>Company</h4>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Us</a></li>
          <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
          <li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">Trade Account</a></li>
        </ul>
      </div>
      <div class="kaiko-footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Help Centre</a></li>
          <li><a href="#">Shipping Info</a></li>
          <li><a href="#">Returns Policy</a></li>
        </ul>
      </div>
    </div>
    <div class="kaiko-footer-bottom">
      <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> KAIKO. All rights reserved.</span>
      <span>Designed for reptile enthusiasts</span>
    </div>
  </footer>

</div><!-- .kaiko-myaccount-wrap -->

<?php wp_footer(); ?>
<script>
(function() {
  'use strict';

  /* === Hamburger toggle === */
  var hamburger = document.getElementById('kaiko-hamburger');
  var mobileMenu = document.getElementById('kaiko-mobile-menu');
  var overlay = document.getElementById('kaiko-mobile-overlay');
  var closeBtn = document.getElementById('kaiko-mobile-close');
  var nav = document.getElementById('kaiko-nav');

  if (!hamburger || !mobileMenu) return;

  function openMenu() {
    hamburger.classList.add('active');
    hamburger.setAttribute('aria-expanded', 'true');
    mobileMenu.classList.add('open');
    if (overlay) overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (closeBtn) closeBtn.focus();
  }

  function closeMenu() {
    hamburger.classList.remove('active');
    hamburger.setAttribute('aria-expanded', 'false');
    mobileMenu.classList.remove('open');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
    hamburger.focus();
  }

  hamburger.addEventListener('click', function() {
    mobileMenu.classList.contains('open') ? closeMenu() : openMenu();
  });
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  if (overlay) overlay.addEventListener('click', closeMenu);

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && mobileMenu.classList.contains('open')) closeMenu();
  });

  /* === Nav scroll shadow === */
  if (nav) {
    var ticking = false;
    window.addEventListener('scroll', function() {
      if (!ticking) {
        window.requestAnimationFrame(function() {
          nav.classList.toggle('scrolled', window.scrollY > 10);
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });
  }
})();
</script>
</body>
</html>
