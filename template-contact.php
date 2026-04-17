<?php
/**
 * Template Name: Kaiko Contact
 * Template Post Type: page
 *
 * Custom contact page — bypasses WoodMart, uses Kaiko design system.
 * Sections: Hero, Contact Method Cards, CF7 Form + Info, FAQ Accordion,
 *           Location Card, Newsletter CTA.
 *
 * CF7 form ID: update kaiko_cf7_contact_id option via wp-admin.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

$cf7_id = (int) get_option( 'kaiko_cf7_contact_id', 0 );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us &mdash; Kaiko</title>
<meta name="description" content="Get in touch with the Kaiko team. Trade enquiries, product questions, and wholesale applications.">
<?php wp_head(); ?>
<style>
/* ============================================================
   KAIKO — CONTACT PAGE
   All CSS scoped to body.kaiko-contact
   Uses --kaiko-* design tokens throughout
   ============================================================ */

/* ---- Token fallbacks (defined here so the page renders
       even if kaiko-design-system.css fails to load, and to
       define the few tokens this template uses that aren't
       in the global design system: container widths, section
       padding, font stack overrides, extended colour palette). */
body.kaiko-contact {
  --kaiko-white: #ffffff;
  --kaiko-off-white: #f6f5f1;
  --kaiko-warm-gray: #ece9e0;
  --kaiko-cream: #f3ede0;
  --kaiko-dark: #1a1a1a;
  --kaiko-teal: #1a5c52;
  --kaiko-deep-teal: #0d3d35;
  --kaiko-lime: #7ab800;
  --kaiko-gold: #c4a962;
  --kaiko-error: #ef4444;
  --kaiko-border: #e4e2de;
  --kaiko-mid-gray: #6b6b6b;
  --kaiko-light-gray: #a8a6a0;
  --kaiko-font-display: 'Gotham', 'Gotham Bold', -apple-system, sans-serif;
  --kaiko-font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --kaiko-weight-medium: 500;
  --kaiko-weight-semibold: 600;
  --kaiko-weight-bold: 700;
  --kaiko-line-height-tight: 1.15;
  --kaiko-line-height-relaxed: 1.7;
  --kaiko-letter-spacing-tight: -0.02em;
  --kaiko-letter-spacing-wide: 0.025em;
  --kaiko-letter-spacing-wider: 0.05em;
  --kaiko-radius-sm: 6px;
  --kaiko-radius-md: 10px;
  --kaiko-radius-lg: 14px;
  --kaiko-radius-xl: 18px;
  --kaiko-radius-2xl: 22px;
  --kaiko-radius-full: 9999px;
  --kaiko-shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
  --kaiko-shadow-lift: 0 18px 42px rgba(26,92,82,0.12);
  --kaiko-transition-fast: 150ms ease;
  --kaiko-transition-base: 260ms cubic-bezier(0.4,0,0.2,1);
  --kaiko-transition-slow: 420ms cubic-bezier(0.4,0,0.2,1);
  --kaiko-ease-out-expo: cubic-bezier(0.19,1,0.22,1);
  --kaiko-z-navbar: 1010;

  /* Container + section spacing (these are the ones that were
     missing — the cause of the edge-to-edge layout bug). */
  --kaiko-container-max: 1240px;
  --kaiko-container-narrow: 880px;
  --kaiko-section-pad: 96px;
  --kaiko-space-xs: 0.5rem;
  --kaiko-space-sm: 0.75rem;
  --kaiko-space-md: 1rem;
  --kaiko-space-lg: 1.5rem;
  --kaiko-space-xl: 2.5rem;
  --kaiko-space-2xl: 3rem;
  --kaiko-space-3xl: 4rem;
}

/* Hide WoodMart chrome */
.whb-header,
.woodmart-prefooter,
.footer-container,
.wd-footer,
.website-wrapper > footer,
#wp-admin-bar-root-default { display: none !important; }
.website-wrapper { padding-top: 0 !important; }

/* ---- Base ---- */
body.kaiko-contact {
  margin: 0; padding: 0;
  background: var(--kaiko-white);
  font-family: var(--kaiko-font-body);
  color: var(--kaiko-dark);
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}
body.kaiko-contact *, body.kaiko-contact *::before, body.kaiko-contact *::after {
  box-sizing: border-box;
}

/* ---- Nav (exact homepage pattern) ---- */
body.kaiko-contact .kaiko-nav {
  position: fixed; top: 0; left: 0; right: 0;
  z-index: var(--kaiko-z-navbar);
  background: rgba(255,255,255,0.97);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--kaiko-border);
  padding: 0 var(--kaiko-space-xl);
  height: 72px;
  display: flex; align-items: center; justify-content: space-between;
}
body.kaiko-contact .kaiko-nav-logo {
  font-family: var(--kaiko-font-display);
  font-size: 1.75rem; font-weight: var(--kaiko-weight-bold);
  letter-spacing: var(--kaiko-letter-spacing-tight);
  color: var(--kaiko-dark); text-decoration: none;
}
body.kaiko-contact .kaiko-nav-links {
  display: flex; gap: var(--kaiko-space-xl); align-items: center;
}
body.kaiko-contact .kaiko-nav-links a {
  font-family: var(--kaiko-font-body);
  font-size: 0.9rem; font-weight: var(--kaiko-weight-medium);
  color: var(--kaiko-mid-gray); text-decoration: none;
  transition: color var(--kaiko-transition-base);
  letter-spacing: var(--kaiko-letter-spacing-wide);
}
body.kaiko-contact .kaiko-nav-links a:hover,
body.kaiko-contact .kaiko-nav-links a.active { color: var(--kaiko-teal); }
body.kaiko-contact .kaiko-nav-cta {
  background: var(--kaiko-teal) !important;
  color: var(--kaiko-white) !important;
  padding: 8px 20px !important;
  border-radius: var(--kaiko-radius-md) !important;
  font-size: 0.85rem !important;
  text-transform: uppercase;
  letter-spacing: var(--kaiko-letter-spacing-wider);
}
body.kaiko-contact .kaiko-nav-cta:hover {
  background: var(--kaiko-deep-teal) !important;
}

/* ---- Shared layout helpers ---- */
body.kaiko-contact .section {
  padding-top: var(--kaiko-section-pad);
  padding-bottom: var(--kaiko-section-pad);
  padding-left: clamp(20px, 5vw, 48px);
  padding-right: clamp(20px, 5vw, 48px);
}
body.kaiko-contact .section--alt {
  background: var(--kaiko-off-white);
}
body.kaiko-contact .section--dark {
  background: var(--kaiko-dark); color: var(--kaiko-white);
}
body.kaiko-contact .section-inner {
  max-width: var(--kaiko-container-max); margin: 0 auto;
  width: 100%;
}
body.kaiko-contact .section-inner--narrow {
  max-width: var(--kaiko-container-narrow); margin: 0 auto;
  width: 100%;
}
body.kaiko-contact .section-header {
  text-align: center; margin-bottom: var(--kaiko-space-3xl);
}
body.kaiko-contact .section-tag {
  display: inline-block;
  font-family: var(--kaiko-font-display);
  font-size: 0.7rem; font-weight: var(--kaiko-weight-semibold);
  letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--kaiko-teal); margin-bottom: var(--kaiko-space-md);
}
body.kaiko-contact .section-title {
  font-family: var(--kaiko-font-display);
  font-size: clamp(1.75rem, 3vw, 2.75rem);
  font-weight: var(--kaiko-weight-bold);
  color: var(--kaiko-dark);
  margin: 0 0 var(--kaiko-space-md);
  letter-spacing: var(--kaiko-letter-spacing-tight);
  line-height: var(--kaiko-line-height-tight);
}
body.kaiko-contact .section-subtitle {
  font-size: 1.05rem; color: var(--kaiko-mid-gray);
  max-width: 600px; margin: 0 auto; line-height: var(--kaiko-line-height-relaxed);
}

/* ---- Buttons ---- */
body.kaiko-contact .btn-primary {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--kaiko-teal); color: var(--kaiko-white);
  padding: 14px 32px; border-radius: var(--kaiko-radius-md);
  font-family: var(--kaiko-font-body); font-weight: var(--kaiko-weight-semibold);
  font-size: 0.95rem; text-decoration: none;
  transition: all var(--kaiko-transition-slow);
  border: none; cursor: pointer; letter-spacing: var(--kaiko-letter-spacing-wide);
}
body.kaiko-contact .btn-primary:hover {
  background: var(--kaiko-deep-teal);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26,92,82,0.2);
}
body.kaiko-contact .btn-secondary {
  display: inline-flex; align-items: center; gap: 8px;
  background: transparent; color: var(--kaiko-dark);
  padding: 14px 32px; border-radius: var(--kaiko-radius-md);
  border: 1px solid var(--kaiko-border);
  font-family: var(--kaiko-font-body); font-weight: var(--kaiko-weight-medium);
  font-size: 0.95rem; text-decoration: none;
  transition: all var(--kaiko-transition-base); cursor: pointer;
}
body.kaiko-contact .btn-secondary:hover {
  background: var(--kaiko-off-white);
  border-color: var(--kaiko-teal); color: var(--kaiko-teal);
}

/* ---- Reveal animations ---- */
body.kaiko-contact .kaiko-reveal {
  opacity: 0; transform: translateY(30px);
  transition: opacity 0.7s var(--kaiko-ease-out-expo), transform 0.7s var(--kaiko-ease-out-expo);
}
body.kaiko-contact .kaiko-reveal.is-visible {
  opacity: 1; transform: translateY(0);
}
body.kaiko-contact .kaiko-stagger .kaiko-reveal:nth-child(1) { transition-delay: 0s; }
body.kaiko-contact .kaiko-stagger .kaiko-reveal:nth-child(2) { transition-delay: 0.08s; }
body.kaiko-contact .kaiko-stagger .kaiko-reveal:nth-child(3) { transition-delay: 0.16s; }
body.kaiko-contact .kaiko-stagger .kaiko-reveal:nth-child(4) { transition-delay: 0.24s; }
body.kaiko-contact .kaiko-stagger .kaiko-reveal:nth-child(5) { transition-delay: 0.32s; }

/* ==============================================================
   SECTION 1 — HERO
   Compact, warm gradient, inviting — not full-viewport
   ============================================================== */
body.kaiko-contact .contact-hero {
  min-height: 55vh;
  display: flex; align-items: center;
  padding: 120px var(--kaiko-space-xl) 80px;
  background: linear-gradient(135deg, var(--kaiko-off-white) 0%, var(--kaiko-warm-gray) 100%);
  position: relative; overflow: hidden;
}
body.kaiko-contact .contact-hero::before {
  content: '';
  position: absolute; top: -30%; right: -5%;
  width: 50vw; height: 50vw;
  background: radial-gradient(circle, rgba(26,92,82,0.05) 0%, transparent 70%);
  border-radius: 50%; pointer-events: none;
}
body.kaiko-contact .contact-hero::after {
  content: '';
  position: absolute; bottom: -30%; left: -5%;
  width: 35vw; height: 35vw;
  background: radial-gradient(circle, rgba(196,169,98,0.06) 0%, transparent 70%);
  border-radius: 50%; pointer-events: none;
}
body.kaiko-contact .contact-hero-inner {
  max-width: var(--kaiko-container-max); margin: 0 auto; width: 100%;
  position: relative; z-index: 2;
}
body.kaiko-contact .contact-hero-tag {
  display: inline-block;
  font-family: var(--kaiko-font-display);
  font-size: 0.75rem; font-weight: var(--kaiko-weight-semibold);
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--kaiko-teal); background: rgba(26,92,82,0.08);
  padding: 6px 16px; border-radius: var(--kaiko-radius-full);
  margin-bottom: var(--kaiko-space-lg);
}
body.kaiko-contact .contact-hero h1 {
  font-family: var(--kaiko-font-display);
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: var(--kaiko-weight-bold);
  line-height: var(--kaiko-line-height-tight);
  color: var(--kaiko-dark);
  margin-bottom: var(--kaiko-space-lg);
  letter-spacing: var(--kaiko-letter-spacing-tight);
}
body.kaiko-contact .contact-hero h1 span { color: var(--kaiko-teal); }
body.kaiko-contact .contact-hero-sub {
  font-size: 1.15rem; line-height: var(--kaiko-line-height-relaxed);
  color: var(--kaiko-mid-gray); max-width: 560px;
  margin-bottom: var(--kaiko-space-xl);
}
body.kaiko-contact .contact-hero-badges {
  display: flex; gap: var(--kaiko-space-md); flex-wrap: wrap;
}
body.kaiko-contact .contact-hero-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--kaiko-white); border: 1px solid var(--kaiko-border);
  border-radius: var(--kaiko-radius-full); padding: 8px 18px;
  font-size: 0.8rem; font-weight: var(--kaiko-weight-medium);
  color: var(--kaiko-dark); box-shadow: var(--kaiko-shadow-xs);
}
body.kaiko-contact .contact-hero-badge-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--kaiko-teal); flex-shrink: 0;
}

/* ==============================================================
   SECTION 2 — CONTACT METHOD CARDS
   3 cards, coloured top borders (teal / lime / gold)
   Border animates on hover; card lifts
   ============================================================== */
body.kaiko-contact .methods-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--kaiko-space-xl);
}
body.kaiko-contact .method-card {
  background: var(--kaiko-white);
  border: 1px solid var(--kaiko-border);
  border-radius: var(--kaiko-radius-2xl);
  padding: var(--kaiko-space-2xl);
  position: relative; overflow: hidden;
  transition: transform var(--kaiko-transition-slow), box-shadow var(--kaiko-transition-slow);
}
/* Coloured top border strip */
body.kaiko-contact .method-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 4px; border-radius: var(--kaiko-radius-2xl) var(--kaiko-radius-2xl) 0 0;
  transform: scaleX(0.3);
  transition: transform 0.5s var(--kaiko-ease-out-expo);
  transform-origin: left;
}
body.kaiko-contact .method-card:hover::before { transform: scaleX(1); }
body.kaiko-contact .method-card--teal::before  { background: var(--kaiko-teal); }
body.kaiko-contact .method-card--lime::before  { background: var(--kaiko-lime); }
body.kaiko-contact .method-card--gold::before  { background: var(--kaiko-gold); }

body.kaiko-contact .method-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--kaiko-shadow-lift);
}

body.kaiko-contact .method-card-icon {
  width: 56px; height: 56px;
  border-radius: var(--kaiko-radius-xl);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: var(--kaiko-space-lg);
  font-size: 1.5rem;
  transition: transform var(--kaiko-transition-base);
}
body.kaiko-contact .method-card:hover .method-card-icon { transform: scale(1.1); }
body.kaiko-contact .method-card--teal .method-card-icon { background: rgba(26,92,82,0.08); }
body.kaiko-contact .method-card--lime .method-card-icon { background: rgba(122,184,0,0.08); }
body.kaiko-contact .method-card--gold .method-card-icon { background: rgba(196,169,98,0.1); }

body.kaiko-contact .method-card-label {
  font-family: var(--kaiko-font-display);
  font-size: 0.7rem; font-weight: var(--kaiko-weight-semibold);
  letter-spacing: 0.12em; text-transform: uppercase;
  margin-bottom: var(--kaiko-space-sm);
}
body.kaiko-contact .method-card--teal .method-card-label { color: var(--kaiko-teal); }
body.kaiko-contact .method-card--lime .method-card-label { color: #5a8800; }
body.kaiko-contact .method-card--gold .method-card-label { color: #a08030; }

body.kaiko-contact .method-card h3 {
  font-family: var(--kaiko-font-display);
  font-size: 1.25rem; font-weight: var(--kaiko-weight-bold);
  color: var(--kaiko-dark); margin-bottom: var(--kaiko-space-sm);
}
body.kaiko-contact .method-card p {
  font-size: 0.9rem; color: var(--kaiko-mid-gray);
  line-height: var(--kaiko-line-height-relaxed);
  margin-bottom: var(--kaiko-space-lg);
}
body.kaiko-contact .method-card-value {
  font-size: 1rem; font-weight: var(--kaiko-weight-semibold);
  color: var(--kaiko-dark);
}
body.kaiko-contact .method-card-value a {
  text-decoration: none; transition: color var(--kaiko-transition-base);
}
body.kaiko-contact .method-card--teal .method-card-value a { color: var(--kaiko-teal); }
body.kaiko-contact .method-card--teal .method-card-value a:hover { color: var(--kaiko-deep-teal); }
body.kaiko-contact .method-card--lime .method-card-value a { color: #4a7200; }
body.kaiko-contact .method-card--lime .method-card-value a:hover { color: #365500; }
body.kaiko-contact .method-card--gold .method-card-value { color: #806020; }
body.kaiko-contact .method-card-note {
  font-size: 0.8rem; color: var(--kaiko-mid-gray); margin-top: 4px;
}

/* ==============================================================
   SECTION 3 — CONTACT FORM + INFO PANEL
   Two-column layout: CF7 form left, business info right
   ============================================================== */
body.kaiko-contact .form-info-grid {
  display: grid; grid-template-columns: minmax(0, 1fr) 360px;
  gap: clamp(40px, 5vw, 72px); align-items: start;
}

/* Left: form column */
body.kaiko-contact .form-col-heading {
  margin-bottom: var(--kaiko-space-2xl);
}
body.kaiko-contact .form-col-heading h2 {
  font-family: var(--kaiko-font-display);
  font-size: clamp(1.5rem, 2.5vw, 2.25rem);
  font-weight: var(--kaiko-weight-bold);
  color: var(--kaiko-dark); margin-bottom: var(--kaiko-space-sm);
  letter-spacing: var(--kaiko-letter-spacing-tight);
}
body.kaiko-contact .form-col-heading p {
  font-size: 0.95rem; color: var(--kaiko-mid-gray);
  line-height: var(--kaiko-line-height-relaxed);
}

/* CF7 integration styles */
body.kaiko-contact .wpcf7-form { width: 100%; }
body.kaiko-contact .wpcf7-form .form-row {
  display: grid; grid-template-columns: 1fr 1fr; gap: var(--kaiko-space-md);
  margin-bottom: var(--kaiko-space-md);
}
body.kaiko-contact .wpcf7-form .form-row.full {
  grid-template-columns: 1fr;
}
body.kaiko-contact .wpcf7-form label {
  display: block;
  font-size: 0.8rem; font-weight: var(--kaiko-weight-semibold);
  letter-spacing: 0.06em; text-transform: uppercase;
  color: var(--kaiko-dark); margin-bottom: var(--kaiko-space-sm);
}
body.kaiko-contact .wpcf7-form input[type="text"],
body.kaiko-contact .wpcf7-form input[type="email"],
body.kaiko-contact .wpcf7-form input[type="tel"],
body.kaiko-contact .wpcf7-form select,
body.kaiko-contact .wpcf7-form textarea,
body.kaiko-contact .native-form input,
body.kaiko-contact .native-form select,
body.kaiko-contact .native-form textarea {
  width: 100%; padding: 13px 18px;
  line-height: 1.4;
  background: var(--kaiko-white);
  border: 1.5px solid var(--kaiko-border);
  border-radius: var(--kaiko-radius-lg);
  font-family: var(--kaiko-font-body);
  font-size: 0.95rem; color: var(--kaiko-dark);
  transition: border-color var(--kaiko-transition-base), box-shadow var(--kaiko-transition-base);
  outline: none; appearance: none;
}
body.kaiko-contact .wpcf7-form input:focus,
body.kaiko-contact .wpcf7-form select:focus,
body.kaiko-contact .wpcf7-form textarea:focus,
body.kaiko-contact .native-form input:focus,
body.kaiko-contact .native-form select:focus,
body.kaiko-contact .native-form textarea:focus {
  border-color: var(--kaiko-teal);
  box-shadow: 0 0 0 3px rgba(26,92,82,0.1);
}
body.kaiko-contact .wpcf7-form textarea,
body.kaiko-contact .native-form textarea {
  resize: vertical; min-height: 140px;
  line-height: var(--kaiko-line-height-relaxed);
}
body.kaiko-contact .wpcf7-form input[type="submit"],
body.kaiko-contact .native-form .form-submit {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--kaiko-teal); color: var(--kaiko-white);
  padding: 14px 36px;
  border-radius: var(--kaiko-radius-md);
  font-family: var(--kaiko-font-body); font-weight: var(--kaiko-weight-semibold);
  font-size: 0.95rem; letter-spacing: var(--kaiko-letter-spacing-wide);
  border: none; cursor: pointer;
  transition: all var(--kaiko-transition-slow); margin-top: var(--kaiko-space-md);
}
body.kaiko-contact .wpcf7-form input[type="submit"]:hover,
body.kaiko-contact .native-form .form-submit:hover {
  background: var(--kaiko-deep-teal);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26,92,82,0.2);
}
body.kaiko-contact .wpcf7-response-output {
  margin-top: var(--kaiko-space-lg) !important;
  padding: 14px 18px !important;
  border-radius: var(--kaiko-radius-lg) !important;
  font-size: 0.9rem !important;
}
body.kaiko-contact .wpcf7-mail-sent-ok {
  background: rgba(122,184,0,0.07) !important;
  border: 1px solid var(--kaiko-lime) !important;
  color: #3a6000 !important;
}
body.kaiko-contact .wpcf7-validation-errors,
body.kaiko-contact .wpcf7-acceptance-missing {
  background: rgba(239,68,68,0.05) !important;
  border: 1px solid var(--kaiko-error) !important;
  color: #991b1b !important;
}
body.kaiko-contact .wpcf7-not-valid-tip {
  font-size: 0.8rem; color: var(--kaiko-error);
  margin-top: 4px; display: block;
}

/* Native form fallback */
body.kaiko-contact .native-form .form-row {
  display: grid; grid-template-columns: 1fr 1fr; gap: var(--kaiko-space-md);
  margin-bottom: var(--kaiko-space-md);
}
body.kaiko-contact .native-form .form-group { margin-bottom: var(--kaiko-space-md); }
body.kaiko-contact .native-form label {
  display: block; font-size: 0.8rem; font-weight: var(--kaiko-weight-semibold);
  letter-spacing: 0.06em; text-transform: uppercase;
  color: var(--kaiko-dark); margin-bottom: var(--kaiko-space-sm);
}
body.kaiko-contact .native-form input::placeholder,
body.kaiko-contact .native-form textarea::placeholder {
  color: var(--kaiko-light-gray);
}
body.kaiko-contact .native-form select {
  cursor: pointer;
  line-height: 1.4;
  min-height: 50px;
  padding-right: 44px;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='8' viewBox='0 0 14 8' fill='none' stroke='%236b6b6b' stroke-width='1.75' stroke-linecap='round' stroke-linejoin='round'><polyline points='1,1 7,7 13,1'/></svg>");
  background-repeat: no-repeat;
  background-position: right 18px center;
  background-size: 12px 7px;
}
body.kaiko-contact .form-success {
  background: rgba(122,184,0,0.08);
  border: 1px solid var(--kaiko-lime);
  color: #3a6000;
  padding: 14px 18px; border-radius: var(--kaiko-radius-lg);
  font-size: 0.9rem; margin-top: var(--kaiko-space-md); display: none;
}
body.kaiko-contact .form-error {
  background: rgba(239,68,68,0.05);
  border: 1px solid var(--kaiko-error);
  color: #991b1b;
  padding: 14px 18px; border-radius: var(--kaiko-radius-lg);
  font-size: 0.9rem; margin-top: var(--kaiko-space-md); display: none;
}

/* Right: info panel */
body.kaiko-contact .info-panel {
  display: flex; flex-direction: column; gap: var(--kaiko-space-lg);
  position: sticky; top: 90px;
}
body.kaiko-contact .info-card {
  background: var(--kaiko-off-white);
  border: 1px solid var(--kaiko-border);
  border-radius: var(--kaiko-radius-2xl);
  padding: var(--kaiko-space-xl);
}
body.kaiko-contact .info-card h4 {
  font-family: var(--kaiko-font-display);
  font-size: 0.75rem; font-weight: var(--kaiko-weight-semibold);
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--kaiko-teal); margin-bottom: var(--kaiko-space-lg);
}
body.kaiko-contact .info-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 0; border-bottom: 1px solid var(--kaiko-border);
  font-size: 0.9rem;
}
body.kaiko-contact .info-row:last-child { border-bottom: none; padding-bottom: 0; }
body.kaiko-contact .info-row-label { color: var(--kaiko-mid-gray); font-weight: var(--kaiko-weight-medium); }
body.kaiko-contact .info-row-value { color: var(--kaiko-dark); font-weight: var(--kaiko-weight-semibold); }
body.kaiko-contact .info-row-value.available { color: var(--kaiko-teal); }
body.kaiko-contact .info-response-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(122,184,0,0.1); border: 1px solid rgba(122,184,0,0.25);
  border-radius: var(--kaiko-radius-full); padding: 8px 16px;
  font-size: 0.8rem; font-weight: var(--kaiko-weight-semibold);
  color: #3a6000; margin-bottom: var(--kaiko-space-md);
}
body.kaiko-contact .info-response-badge-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--kaiko-lime);
  animation: pulse-badge 2s ease-in-out infinite;
}
@keyframes pulse-badge {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.75); }
}
body.kaiko-contact .info-socials {
  display: flex; gap: 10px; margin-top: var(--kaiko-space-md);
}
body.kaiko-contact .info-social-link {
  width: 40px; height: 40px; border-radius: 50%;
  border: 1px solid var(--kaiko-border);
  background: var(--kaiko-white);
  display: flex; align-items: center; justify-content: center;
  color: var(--kaiko-mid-gray); text-decoration: none;
  font-size: 0.7rem; font-weight: var(--kaiko-weight-bold);
  letter-spacing: 0.03em;
  transition: all var(--kaiko-transition-base);
}
body.kaiko-contact .info-social-link:hover {
  background: var(--kaiko-teal); color: var(--kaiko-white);
  border-color: var(--kaiko-teal); transform: scale(1.08);
}
body.kaiko-contact .info-trade-note {
  background: rgba(26,92,82,0.05);
  border: 1px solid rgba(26,92,82,0.15);
  border-radius: var(--kaiko-radius-xl);
  padding: var(--kaiko-space-lg);
}
body.kaiko-contact .info-trade-note p {
  font-size: 0.875rem; color: var(--kaiko-dark);
  line-height: var(--kaiko-line-height-relaxed); margin-bottom: 12px;
}
body.kaiko-contact .info-trade-note a {
  color: var(--kaiko-teal); font-weight: var(--kaiko-weight-semibold);
  text-decoration: none;
}
body.kaiko-contact .info-trade-note a:hover { text-decoration: underline; }

/* ==============================================================
   SECTION 4 — FAQ ACCORDION
   kaiko-accordion classes from design system
   ============================================================== */
body.kaiko-contact .kaiko-accordion {
  display: flex; flex-direction: column; gap: 10px;
}
body.kaiko-contact .kaiko-accordion-item {
  background: var(--kaiko-white);
  border: 1px solid var(--kaiko-border);
  border-radius: var(--kaiko-radius-xl);
  overflow: hidden;
  transition: border-color var(--kaiko-transition-base), box-shadow var(--kaiko-transition-base);
}
body.kaiko-contact .kaiko-accordion-item.is-active {
  border-color: var(--kaiko-teal);
  box-shadow: 0 4px 20px rgba(26,92,82,0.08);
}
body.kaiko-contact .kaiko-accordion-trigger {
  width: 100%; padding: 22px var(--kaiko-space-xl);
  background: transparent; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: flex-start; gap: 20px;
  text-align: left;
  transition: background var(--kaiko-transition-base);
}
body.kaiko-contact .kaiko-accordion-trigger:hover { background: var(--kaiko-off-white); }
body.kaiko-contact .kaiko-accordion-item.is-active .kaiko-accordion-trigger {
  background: rgba(26,92,82,0.03);
}
body.kaiko-contact .kaiko-accordion-question {
  font-family: var(--kaiko-font-display);
  font-size: 1rem; font-weight: var(--kaiko-weight-semibold);
  color: var(--kaiko-dark); line-height: 1.4;
}
body.kaiko-contact .kaiko-accordion-icon {
  width: 30px; height: 30px; border-radius: 50%;
  border: 1.5px solid var(--kaiko-border);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  font-size: 1.25rem; line-height: 1; color: var(--kaiko-mid-gray);
  transition: all var(--kaiko-transition-base);
}
body.kaiko-contact .kaiko-accordion-trigger::before,
body.kaiko-contact .kaiko-accordion-trigger::after {
  content: none !important;
  display: none !important;
}
body.kaiko-contact .kaiko-accordion-item::before,
body.kaiko-contact .kaiko-accordion-item::after {
  content: none !important;
  display: none !important;
}
body.kaiko-contact .kaiko-accordion-icon {
  margin-left: auto;
}
body.kaiko-contact .kaiko-accordion-item.is-active .kaiko-accordion-icon {
  background: var(--kaiko-teal); border-color: var(--kaiko-teal);
  color: var(--kaiko-white); transform: rotate(45deg);
}
body.kaiko-contact .kaiko-accordion-panel {
  max-height: 0; overflow: hidden;
  transition: max-height 0.45s var(--kaiko-ease-out-expo), padding 0.3s ease;
  padding: 0 var(--kaiko-space-xl);
}
body.kaiko-contact .kaiko-accordion-item.is-active .kaiko-accordion-panel {
  max-height: 400px; padding: 0 var(--kaiko-space-xl) var(--kaiko-space-xl);
}
body.kaiko-contact .kaiko-accordion-answer {
  font-size: 0.95rem; color: var(--kaiko-mid-gray);
  line-height: var(--kaiko-line-height-relaxed);
  border-top: 1px solid var(--kaiko-border); padding-top: var(--kaiko-space-lg);
}
body.kaiko-contact .kaiko-accordion-answer a {
  color: var(--kaiko-teal); text-decoration: none; font-weight: var(--kaiko-weight-medium);
}
body.kaiko-contact .kaiko-accordion-answer a:hover { text-decoration: underline; }

/* ==============================================================
   SECTION 5 — LOCATION (Great Dunmow, Essex)
   Real Google Maps iframe embed + details grid
   ============================================================== */
body.kaiko-contact .location-grid {
  display: grid; grid-template-columns: 1.1fr 1fr; gap: var(--kaiko-space-3xl); align-items: stretch;
}
body.kaiko-contact .location-map {
  aspect-ratio: 4/3;
  background: var(--kaiko-warm-gray);
  border-radius: var(--kaiko-radius-2xl);
  border: 1px solid var(--kaiko-border);
  overflow: hidden;
  box-shadow: var(--kaiko-shadow-xs);
  min-height: 380px;
}
body.kaiko-contact .location-map iframe {
  display: block; width: 100%; height: 100%; border: 0;
  filter: saturate(0.9) contrast(1.02);
}
body.kaiko-contact .location-info { display: flex; flex-direction: column; gap: var(--kaiko-space-lg); }
body.kaiko-contact .location-info h2 {
  font-family: var(--kaiko-font-display);
  font-size: clamp(1.5rem, 2.5vw, 2.25rem); font-weight: var(--kaiko-weight-bold);
  color: var(--kaiko-dark); letter-spacing: var(--kaiko-letter-spacing-tight);
  margin-bottom: var(--kaiko-space-sm);
}
body.kaiko-contact .location-info p {
  font-size: 0.95rem; color: var(--kaiko-mid-gray);
  line-height: var(--kaiko-line-height-relaxed);
}
body.kaiko-contact .location-details {
  display: flex; flex-direction: column; gap: var(--kaiko-space-md);
}
body.kaiko-contact .location-detail-row {
  display: flex; align-items: flex-start; gap: var(--kaiko-space-md);
}
body.kaiko-contact .location-detail-icon {
  width: 40px; height: 40px; border-radius: var(--kaiko-radius-md);
  background: rgba(26,92,82,0.07); color: var(--kaiko-teal);
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
}
body.kaiko-contact .location-detail-text strong {
  display: block; font-size: 0.85rem; font-weight: var(--kaiko-weight-semibold);
  color: var(--kaiko-dark); margin-bottom: 2px;
}
body.kaiko-contact .location-detail-text span {
  font-size: 0.85rem; color: var(--kaiko-mid-gray);
}

/* ==============================================================
   SECTION 6 — NEWSLETTER CTA (homepage pattern)
   ============================================================== */
body.kaiko-contact .newsletter-form {
  display: flex; gap: var(--kaiko-space-sm); max-width: 500px; margin: 0 auto;
}
body.kaiko-contact .newsletter-form input {
  flex: 1; padding: 14px 20px;
  border: 1px solid var(--kaiko-border); border-radius: var(--kaiko-radius-md);
  font-family: var(--kaiko-font-body); font-size: 0.95rem;
  background: var(--kaiko-white); color: var(--kaiko-dark); outline: none;
}
body.kaiko-contact .newsletter-form input:focus {
  border-color: var(--kaiko-teal);
  box-shadow: 0 0 0 3px rgba(26,92,82,0.1);
}

/* ---- Footer (dark, exact homepage pattern) ---- */
body.kaiko-contact .kaiko-footer {
  background: var(--kaiko-dark);
  color: rgba(255,255,255,0.6);
  padding: 80px var(--kaiko-space-xl) 40px;
}
body.kaiko-contact .footer-inner {
  max-width: var(--kaiko-container-max); margin: 0 auto;
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 60px; margin-bottom: 60px;
}
body.kaiko-contact .footer-brand h3 {
  font-family: var(--kaiko-font-display); font-size: 1.5rem;
  font-weight: var(--kaiko-weight-bold); color: var(--kaiko-white); margin: 0 0 16px;
}
body.kaiko-contact .footer-brand p {
  font-size: 0.9rem; line-height: 1.7; max-width: 300px; margin: 0;
}
body.kaiko-contact .footer-col h4 {
  font-family: var(--kaiko-font-display); font-size: 0.8rem;
  font-weight: var(--kaiko-weight-semibold); letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--kaiko-white); margin: 0 0 24px;
}
body.kaiko-contact .footer-col ul { list-style: none; padding: 0; margin: 0; }
body.kaiko-contact .footer-col li { margin-bottom: 10px; }
body.kaiko-contact .footer-col a {
  color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.9rem;
  transition: color var(--kaiko-transition-base);
}
body.kaiko-contact .footer-col a:hover { color: var(--kaiko-lime); }
body.kaiko-contact .footer-bottom {
  max-width: var(--kaiko-container-max); margin: 0 auto;
  border-top: 1px solid rgba(255,255,255,0.08); padding-top: 30px;
  display: flex; justify-content: space-between; align-items: center;
  font-size: 0.8rem;
}

/* ---- Reduced motion ---- */
@media (prefers-reduced-motion: reduce) {
  body.kaiko-contact *, body.kaiko-contact *::before, body.kaiko-contact *::after {
    animation-duration: 0.01ms !important; transition-duration: 0.01ms !important;
  }
}

/* ---- Responsive ---- */
@media (max-width: 1200px) {
  body.kaiko-contact .form-info-grid { grid-template-columns: 1fr 320px; gap: 48px; }
  body.kaiko-contact .footer-inner { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 1024px) {
  body.kaiko-contact .methods-grid { grid-template-columns: 1fr; max-width: 480px; margin: 0 auto; }
  body.kaiko-contact .form-info-grid { grid-template-columns: 1fr; gap: 48px; }
  body.kaiko-contact .info-panel { position: static; }
  body.kaiko-contact .location-grid { grid-template-columns: 1fr; gap: 40px; }
}
@media (max-width: 768px) {
  body.kaiko-contact {
    --kaiko-section-pad: 64px;
  }
  body.kaiko-contact .kaiko-nav-links { display: none; }
  body.kaiko-contact .section {
    padding-left: 20px; padding-right: 20px;
  }
  body.kaiko-contact .contact-hero { padding: 100px 20px 60px; min-height: auto; }
  body.kaiko-contact .native-form .form-row { grid-template-columns: 1fr; }
  body.kaiko-contact .newsletter-form { flex-direction: column; }
  body.kaiko-contact .footer-inner { grid-template-columns: 1fr; gap: 40px; }
  body.kaiko-contact .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
  body.kaiko-contact .contact-hero-badges { flex-direction: column; align-items: flex-start; }
  body.kaiko-contact .location-map { min-height: 280px; }
}
@media (max-width: 480px) {
  body.kaiko-contact {
    --kaiko-section-pad: 48px;
  }
  body.kaiko-contact .section { padding-left: 16px; padding-right: 16px; }
  body.kaiko-contact .contact-hero { padding: 90px 16px 48px; }
  body.kaiko-contact .kaiko-footer { padding: 60px 16px 32px; }
  body.kaiko-contact .kaiko-accordion-trigger { padding: 18px 20px; }
  body.kaiko-contact .kaiko-accordion-panel { padding: 0 20px; }
  body.kaiko-contact .kaiko-accordion-item.is-active .kaiko-accordion-panel { padding: 0 20px 20px; }
  body.kaiko-contact .method-card { padding: var(--kaiko-space-xl); }
}
</style>
</head>
<body <?php body_class( 'kaiko-contact' ); ?>>
<?php wp_body_open(); ?>

<!-- NAVIGATION -->
<nav class="kaiko-nav">
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kaiko-nav-logo">KAIKO</a>
  <div class="kaiko-nav-links">
    <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>">Products</a>
    <?php if ( is_user_logged_in() ) : ?><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Shop</a><?php endif; ?>
    <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
    <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="active">Contact</a>
    <a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>" class="kaiko-nav-cta"><?php echo is_user_logged_in() ? 'My Account' : 'Trade Login'; ?></a>
  </div>
</nav>

<!-- ========================================================
     SECTION 1 — HERO
     ======================================================== -->
<section class="contact-hero">
  <div class="contact-hero-inner">
    <div class="contact-hero-tag">We&rsquo;re here to help</div>
    <h1>Get in <span>Touch</span><br>with Kaiko.</h1>
    <p class="contact-hero-sub">Trade enquiries, product questions, wholesale applications &mdash; we respond fast and we&rsquo;re run by people who actually keep reptiles.</p>
    <div class="contact-hero-badges">
      <div class="contact-hero-badge">
        <span class="contact-hero-badge-dot"></span>
        Replies within 24 hours
      </div>
      <div class="contact-hero-badge">
        <span class="contact-hero-badge-dot" style="background: var(--kaiko-gold);"></span>
        UK-based team
      </div>
      <div class="contact-hero-badge">
        <span class="contact-hero-badge-dot" style="background: var(--kaiko-lime);"></span>
        Trade accounts available
      </div>
    </div>
  </div>
</section>

<!-- ========================================================
     SECTION 2 — CONTACT METHOD CARDS
     3 cards: Email (teal), Phone/Trade (lime), Location (gold)
     Coloured top border animates in from left on hover
     ======================================================== -->
<section class="section">
  <div class="section-inner">
    <div class="methods-grid kaiko-stagger">

      <div class="method-card method-card--teal kaiko-reveal">
        <div class="method-card-icon">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--kaiko-teal)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div class="method-card-label">General Enquiries</div>
        <h3>Send Us an Email</h3>
        <p>Product questions, press enquiries, community collaborations. Our general inbox is monitored daily.</p>
        <div class="method-card-value"><a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a></div>
        <div class="method-card-note">Typical response: within 24 hours</div>
      </div>

      <div class="method-card method-card--lime kaiko-reveal">
        <div class="method-card-icon">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#5a8800" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        </div>
        <div class="method-card-label">Trade &amp; Wholesale</div>
        <h3>Trade Enquiries</h3>
        <p>Wholesale accounts, pricing, minimum orders, and distribution. Fast-tracked for business accounts.</p>
        <div class="method-card-value"><a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a></div>
        <div class="method-card-note">Priority response: within 12 hours</div>
      </div>

      <div class="method-card method-card--gold kaiko-reveal">
        <div class="method-card-icon">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#a08030" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="method-card-label">Based in</div>
        <h3>Great Dunmow, Essex</h3>
        <p>We&rsquo;re a British brand based in Great Dunmow, Essex. Every product is designed, tested, and shipped from our Essex base.</p>
        <div class="method-card-value">Great Dunmow, Essex</div>
        <div class="method-card-note">Free UK shipping on orders over &pound;150</div>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================
     SECTION 3 — CONTACT FORM + INFO PANEL
     ======================================================== -->
<section class="section section--alt">
  <div class="section-inner">
    <div class="form-info-grid">

      <!-- Form column -->
      <div class="kaiko-reveal">
        <div class="form-col-heading">
          <div class="section-tag">Send a Message</div>
          <h2>Drop Us a Line.</h2>
          <p>Use the form below and we&rsquo;ll get back to you as quickly as we can. For trade enquiries, select the relevant subject to make sure your message reaches the right person.</p>
        </div>

        <?php if ( $cf7_id > 0 && function_exists( 'wpcf7_contact_form' ) ) : ?>
          <?php echo do_shortcode( '[contact-form-7 id="' . esc_attr( $cf7_id ) . '" title="Contact Form"]' ); ?>
        <?php else : ?>
          <!-- Native fallback — replace with CF7 shortcode when form ID is configured -->
          <!-- Set kaiko_cf7_contact_id option in wp-admin > Options or via Code Snippets -->
          <form class="native-form" id="kaikoContactForm" novalidate>
            <div class="form-row">
              <div class="form-group">
                <label for="kc-first">First Name</label>
                <input type="text" id="kc-first" name="first_name" placeholder="Alex" required autocomplete="given-name">
              </div>
              <div class="form-group">
                <label for="kc-last">Last Name</label>
                <input type="text" id="kc-last" name="last_name" placeholder="Smith" required autocomplete="family-name">
              </div>
            </div>
            <div class="form-group">
              <label for="kc-email">Email Address</label>
              <input type="email" id="kc-email" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>
            <div class="form-group">
              <label for="kc-subject">Subject</label>
              <select id="kc-subject" name="subject" required>
                <option value="" disabled selected>Select a subject&hellip;</option>
                <option value="general">General Enquiry</option>
                <option value="trade">Trade &amp; Wholesale</option>
                <option value="support">Product Support</option>
                <option value="returns">Returns &amp; Refunds</option>
                <option value="press">Press &amp; Media</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="kc-message">Your Message</label>
              <textarea id="kc-message" name="message" placeholder="Tell us how we can help&hellip;" required></textarea>
            </div>
            <!-- Honeypot: real users never fill this; bots often do. -->
            <div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
              <label for="kc-website">Website (leave blank)</label>
              <input type="text" id="kc-website" name="website_url" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-success" id="kc-success" role="status" aria-live="polite" style="display:none;">
              &#10003; Message sent &mdash; we&rsquo;ll get back to you within 24 hours.
            </div>
            <div class="form-error" id="kc-error" role="alert" aria-live="assertive" style="display:none;"></div>
            <button type="submit" class="form-submit">Send Message &rarr;</button>
          </form>
        <?php endif; ?>
      </div>

      <!-- Info panel -->
      <aside class="info-panel kaiko-reveal">

        <div class="info-response-badge">
          <span class="info-response-badge-dot"></span>
          Fast response &mdash; within 24 hrs
        </div>

        <div class="info-card">
          <h4>Business Hours</h4>
          <div class="info-row">
            <span class="info-row-label">Mon &ndash; Fri</span>
            <span class="info-row-value available">9am &ndash; 6pm GMT</span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Saturday</span>
            <span class="info-row-value">10am &ndash; 4pm</span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Sunday</span>
            <span class="info-row-value">Closed</span>
          </div>
          <div class="info-row">
            <span class="info-row-label">Trade enquiries</span>
            <span class="info-row-value available">Priority &lt;12hrs</span>
          </div>

          <div class="info-socials" aria-label="Follow Kaiko on social media">
            <a href="https://instagram.com/kaikoproducts" class="info-social-link" aria-label="Instagram" rel="noopener noreferrer" target="_blank">IG</a>
            <a href="https://tiktok.com/@kaikoproducts" class="info-social-link" aria-label="TikTok" rel="noopener noreferrer" target="_blank">TK</a>
            <a href="https://facebook.com/kaikoproducts" class="info-social-link" aria-label="Facebook" rel="noopener noreferrer" target="_blank">FB</a>
            <a href="https://youtube.com/@kaikoproducts" class="info-social-link" aria-label="YouTube" rel="noopener noreferrer" target="_blank">YT</a>
          </div>
        </div>

        <div class="info-trade-note">
          <p><strong>Applying for a wholesale account?</strong> Use the form and select &ldquo;Trade &amp; Wholesale&rdquo; &mdash; or apply directly through our trade portal for faster processing.</p>
          <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>" class="btn-primary" style="font-size:0.85rem; padding:10px 24px;">Apply for Trade Access &rarr;</a>
        </div>

      </aside>
    </div>
  </div>
</section>

<!-- ========================================================
     SECTION 4 — FAQ ACCORDION
     ======================================================== -->
<section class="section">
  <div class="section-inner--narrow">
    <div class="section-header kaiko-reveal">
      <div class="section-tag">Common Questions</div>
      <h2 class="section-title">Frequently Asked</h2>
      <p class="section-subtitle">The questions we get asked most &mdash; answered. If you can&rsquo;t find what you need, just reach out directly.</p>
    </div>

    <div class="kaiko-accordion kaiko-reveal" id="contactFaq" role="list">

      <div class="kaiko-accordion-item" role="listitem">
        <button class="kaiko-accordion-trigger" aria-expanded="false" aria-controls="faq-1">
          <span class="kaiko-accordion-question">How do I become a trade partner?</span>
          <span class="kaiko-accordion-icon" aria-hidden="true">+</span>
        </button>
        <div class="kaiko-accordion-panel" id="faq-1" role="region">
          <div class="kaiko-accordion-answer">
            Register through our <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>">trade account portal</a>. Once you submit your details, our team reviews your application typically within 48 hours. On approval you get immediate access to wholesale pricing, the full product catalogue, and account management tools.
          </div>
        </div>
      </div>

      <div class="kaiko-accordion-item" role="listitem">
        <button class="kaiko-accordion-trigger" aria-expanded="false" aria-controls="faq-2">
          <span class="kaiko-accordion-question">Why can&rsquo;t I see prices on the site?</span>
          <span class="kaiko-accordion-icon" aria-hidden="true">+</span>
        </button>
        <div class="kaiko-accordion-panel" id="faq-2" role="region">
          <div class="kaiko-accordion-answer">
            Kaiko is a trade-only brand. Wholesale pricing is exclusive to approved trade partners to maintain consistent retail pricing and protect our stockists. <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>">Apply for trade access</a> to unlock the full catalogue with pricing.
          </div>
        </div>
      </div>

      <div class="kaiko-accordion-item" role="listitem">
        <button class="kaiko-accordion-trigger" aria-expanded="false" aria-controls="faq-3">
          <span class="kaiko-accordion-question">How long does trade approval take?</span>
          <span class="kaiko-accordion-icon" aria-hidden="true">+</span>
        </button>
        <div class="kaiko-accordion-panel" id="faq-3" role="region">
          <div class="kaiko-accordion-answer">
            Most applications are reviewed and approved within 48 hours. During busy periods it may take up to 72 hours. You&rsquo;ll receive a confirmation email the moment you&rsquo;re approved &mdash; or a message if we need more information.
          </div>
        </div>
      </div>

      <div class="kaiko-accordion-item" role="listitem">
        <button class="kaiko-accordion-trigger" aria-expanded="false" aria-controls="faq-4">
          <span class="kaiko-accordion-question">What are your shipping times and costs?</span>
          <span class="kaiko-accordion-icon" aria-hidden="true">+</span>
        </button>
        <div class="kaiko-accordion-panel" id="faq-4" role="region">
          <div class="kaiko-accordion-answer">
            UK standard shipping is 2&ndash;5 working days. Orders over &pound;150 ship free within the UK. International shipping is available to 30+ countries &mdash; contact our trade team for bulk international rates. All orders are fully tracked.
          </div>
        </div>
      </div>

      <div class="kaiko-accordion-item" role="listitem">
        <button class="kaiko-accordion-trigger" aria-expanded="false" aria-controls="faq-5">
          <span class="kaiko-accordion-question">What is your returns policy?</span>
          <span class="kaiko-accordion-icon" aria-hidden="true">+</span>
        </button>
        <div class="kaiko-accordion-panel" id="faq-5" role="region">
          <div class="kaiko-accordion-answer">
            We offer a 30-day returns policy on all orders. Items must be unused and in their original packaging for a full refund. If something arrived damaged or faulty, contact us at <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a> with your order number and a photo &mdash; we&rsquo;ll sort it immediately.
          </div>
        </div>
      </div>

      <div class="kaiko-accordion-item" role="listitem">
        <button class="kaiko-accordion-trigger" aria-expanded="false" aria-controls="faq-6">
          <span class="kaiko-accordion-question">Do you have minimum order quantities?</span>
          <span class="kaiko-accordion-icon" aria-hidden="true">+</span>
        </button>
        <div class="kaiko-accordion-panel" id="faq-6" role="region">
          <div class="kaiko-accordion-answer">
            Our minimum first order is &pound;99 &mdash; intentionally low so it&rsquo;s easy to trial our range without tying up capital. There&rsquo;s no minimum on repeat orders. Volume discounts are applied per product based on the quantity you order, and you&rsquo;ll see the exact tier pricing on each individual product page once your trade account is approved.
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================
     SECTION 5 — LOCATION (Great Dunmow, Essex)
     ======================================================== -->
<section class="section section--alt">
  <div class="section-inner">
    <div class="location-grid kaiko-reveal">

      <div class="location-map">
        <iframe
          src="https://www.google.com/maps?q=Great+Dunmow,+Essex,+UK&amp;output=embed"
          width="100%" height="100%"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Kaiko HQ — Great Dunmow, Essex"></iframe>
      </div>

      <div class="location-info">
        <div class="section-tag">Where We Are</div>
        <h2>Designed &amp; Shipped<br>direct from the manufacturer.</h2>
        <p>Kaiko is a British brand based in Great Dunmow, Essex. Every product is designed, tested with real animals, and fulfilled from our Essex base &mdash; no middlemen, no overseas warehouses.</p>
        <div class="location-details">
          <div class="location-detail-row">
            <div class="location-detail-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="location-detail-text">
              <strong>Great Dunmow, Essex</strong>
              <span>UK-based team, UK fulfilment, UK customer service</span>
            </div>
          </div>
          <div class="location-detail-row">
            <div class="location-detail-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4L7.55 4.24"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
            <div class="location-detail-text">
              <strong>Free UK Shipping &pound;150+</strong>
              <span>Tracked delivery on every order</span>
            </div>
          </div>
          <div class="location-detail-row">
            <div class="location-detail-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
            </div>
            <div class="location-detail-text">
              <strong>Worldwide Distribution</strong>
              <span>Shipping to 30+ countries for trade partners</span>
            </div>
          </div>
        </div>
        <a href="https://www.google.com/maps/place/Great+Dunmow" target="_blank" rel="noopener noreferrer" class="btn-secondary" style="align-self:flex-start; margin-top:var(--kaiko-space-md);">
          Open in Google Maps
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
      </div>

    </div>
  </div>
</section>

<!-- ========================================================
     SECTION 6 — NEWSLETTER CTA
     ======================================================== -->
<section class="section section--alt">
  <div class="section-inner">
    <div class="section-header kaiko-reveal">
      <div class="section-tag">Stay Updated</div>
      <h2 class="section-title">Join the Kaiko Community</h2>
      <p class="section-subtitle">New products, care tips, and exclusive trade offers delivered to your inbox. No spam &mdash; just the good stuff.</p>
    </div>
    <form class="newsletter-form kaiko-reveal" onsubmit="kaikoNewsletterSubmit(event)" novalidate>
      <label for="nl-email" class="sr-only">Email address</label>
      <input type="email" id="nl-email" name="email" placeholder="Enter your email address" required autocomplete="email">
      <button class="btn-primary" type="submit">Subscribe</button>
    </form>
  </div>
</section>

<!-- FOOTER (exact homepage pattern, dark) -->
<footer class="kaiko-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <h3>KAIKO</h3>
      <p>Premium reptile and exotic pet supplies, designed by keepers for keepers. Handcrafted in the UK with species-specific precision.</p>
    </div>
    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">All Products</a></li>
        <li><a href="#">Feeding Bowls</a></li>
        <li><a href="#">Water Bowls</a></li>
        <li><a href="#">Humidity Hides</a></li>
        <li><a href="#">Accessories</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Species</h4>
      <ul>
        <li><a href="#">Bearded Dragons</a></li>
        <li><a href="#">Snakes</a></li>
        <li><a href="#">Leopard Geckos</a></li>
        <li><a href="#">Tortoises</a></li>
        <li><a href="#">Chameleons</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Us</a></li>
        <li><a href="<?php echo esc_url( home_url( '/care-guides/' ) ); ?>">Care Guides</a></li>
        <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>">Trade Account</a></li>
        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
        <li><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Privacy Policy</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> Kaiko. All rights reserved.</span>
    <span>Designed for reptile enthusiasts</span>
  </div>
</footer>

<?php wp_footer(); ?>
<script>
(function () {
  'use strict';

  /* Scroll reveal */
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  document.querySelectorAll('.kaiko-reveal').forEach(function (el) { io.observe(el); });

  /* FAQ Accordion */
  document.querySelectorAll('.kaiko-accordion-trigger').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item   = btn.closest('.kaiko-accordion-item');
      var isOpen = item.classList.contains('is-active');
      /* close all in same accordion */
      var parent = btn.closest('.kaiko-accordion');
      parent.querySelectorAll('.kaiko-accordion-item').forEach(function (i) {
        i.classList.remove('is-active');
        i.querySelector('.kaiko-accordion-trigger').setAttribute('aria-expanded', 'false');
      });
      if (!isOpen) {
        item.classList.add('is-active');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
    btn.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
    });
  });

  /* Native contact form — real AJAX submit via kaikoContact.ajaxUrl */
  var form = document.getElementById('kaikoContactForm');
  if (form && typeof window.kaikoContact !== 'undefined') {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('.form-submit');
      var success = document.getElementById('kc-success');
      var errBox = document.getElementById('kc-error');

      // Basic HTML5 validity check
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      // Reset any previous messages
      if (success) success.style.display = 'none';
      if (errBox) { errBox.style.display = 'none'; errBox.textContent = ''; }

      btn.textContent = 'Sending\u2026';
      btn.disabled = true;

      var fd = new FormData(form);
      fd.append('action', 'kaiko_contact_submit');
      fd.append('nonce', window.kaikoContact.nonce);

      fetch(window.kaikoContact.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      })
      .then(function (res) { return res.json().then(function (json) { return { ok: res.ok, status: res.status, json: json }; }); })
      .then(function (result) {
        if (result.json && result.json.success) {
          if (success) {
            success.textContent = '\u2713 ' + (result.json.data && result.json.data.message ? result.json.data.message : 'Message sent \u2014 we\'ll get back to you within 24 hours.');
            success.style.display = 'block';
          }
          btn.textContent = 'Message Sent \u2713';
          form.reset();
        } else {
          var msg = (result.json && result.json.data && result.json.data.message)
            ? result.json.data.message
            : 'Something went wrong. Please try again or email info@kaikoproducts.com directly.';
          if (errBox) { errBox.textContent = msg; errBox.style.display = 'block'; }
          btn.textContent = 'Send Message \u2192';
          btn.disabled = false;
        }
      })
      .catch(function () {
        if (errBox) {
          errBox.textContent = 'Network error. Please check your connection and try again, or email info@kaikoproducts.com directly.';
          errBox.style.display = 'block';
        }
        btn.textContent = 'Send Message \u2192';
        btn.disabled = false;
      });
    });
  }

  /* Newsletter */
  window.kaikoNewsletterSubmit = function (e) {
    e.preventDefault();
    var btn = e.target.querySelector('button');
    btn.textContent = 'Subscribed \u2713'; btn.disabled = true;
    e.target.reset();
  };

})();

// Logged-in nav updates (bypasses page cache)
(function() {
  function applyLoggedInNav() {
    var isLoggedIn = document.body.classList.contains('logged-in')
                     || document.getElementById('wpadminbar') !== null;
    if (!isLoggedIn) return;

    document.querySelectorAll('.kaiko-nav .kaiko-nav-cta').forEach(function(el) {
      if (el.textContent.trim() === 'Trade Login') el.textContent = 'My Account';
    });

    document.querySelectorAll('.kaiko-nav').forEach(function(nav) {
      if (nav.querySelector('[data-kaiko-shop-link]')) return;
      var aboutLink = Array.from(nav.querySelectorAll('a')).find(function(a) {
        return a.textContent.trim() === 'About';
      });
      if (!aboutLink || !aboutLink.parentNode) return;
      var shopLink = document.createElement('a');
      shopLink.href = '/shop/';
      shopLink.textContent = 'Shop';
      shopLink.setAttribute('data-kaiko-shop-link', '1');
      shopLink.className = aboutLink.className;
      aboutLink.parentNode.insertBefore(shopLink, aboutLink);
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyLoggedInNav);
  } else {
    applyLoggedInNav();
  }
  window.addEventListener('load', applyLoggedInNav);
})();
</script>
</body>
</html>
