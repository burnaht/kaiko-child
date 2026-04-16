<?php
/**
 * Template Name: Kaiko About Page
 * Description: Full-width about page with Kaiko custom design system.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap">
<?php wp_head(); ?>

<style>
/* Hide WoodMart theme wrapper elements */
.whb-header, .woodmart-prefooter, .footer-container, .website-wrapper > footer,
.page-title, .breadcrumbs, .woodmart-breadcrumbs, .title-size-default,
.woodmart-main-container,
.wd-toolbar, .wd-sticky-btn, .woodmart-sticky-toolbar,
.wd-toolbar-shop, .whb-sticky-toolbar,
div[class*="wd-toolbar"], div[class*="sticky-toolbar"],
#wp-admin-bar-root-default { display: none !important; }
.wd-content-layout.container { display: block !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
.wd-content-area { width: 100% !important; max-width: 100% !important; }
.website-wrapper { padding-top: 0 !important; }
body { margin: 0; padding: 0; }

/* --- Kaiko Design System --- */
:root {
  --kaiko-white: #fff;
  --kaiko-off-white: #f7f7f5;
  --kaiko-black: #0a0a0a;
  --kaiko-dark: #1a1a1a;
  --kaiko-teal: #1a5c52;
  --kaiko-deep-teal: #0d3d35;
  --kaiko-lime: #7ab800;
  --kaiko-gold: #c4a962;
  --kaiko-border: #e4e2de;
  --kaiko-mid-gray: #6b6b6b;
  --kaiko-font-display: 'Gotham', 'Gotham Bold', -apple-system, sans-serif;
  --kaiko-font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --kaiko-radius-sm: 6px;
  --kaiko-radius-md: 12px;
  --kaiko-radius-lg: 20px;
  --kaiko-transition-fast: 150ms ease;
  --kaiko-transition-base: 300ms ease;
  --kaiko-container-max: 1320px;
  --kaiko-space-xs: 0.5rem;
  --kaiko-space-sm: 1rem;
  --kaiko-space-md: 2rem;
  --kaiko-space-lg: 4rem;
  --kaiko-space-xl: 6rem;
}

/* --- Base Typography --- */
.kaiko-about-wrap {
  font-family: var(--kaiko-font-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--kaiko-black);
  background: var(--kaiko-white);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  overflow-x: hidden;
}
.kaiko-about-wrap *, .kaiko-about-wrap *::before, .kaiko-about-wrap *::after {
  box-sizing: border-box; margin: 0; padding: 0;
}
.kaiko-about-wrap img { display: block; max-width: 100%; }
.kaiko-about-wrap a { color: inherit; text-decoration: none; }
.kaiko-about-wrap h1, .kaiko-about-wrap h2, .kaiko-about-wrap h3,
.kaiko-about-wrap h4, .kaiko-about-wrap h5, .kaiko-about-wrap h6 {
  font-family: var(--kaiko-font-display);
  font-weight: 700;
  line-height: 1.15;
  color: var(--kaiko-dark);
  letter-spacing: -0.02em;
}

/* --- Navigation --- */
.kaiko-about-wrap .kaiko-nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
  background: rgba(255,255,255,0.97);
  backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--kaiko-border);
  padding: 0 var(--kaiko-space-xl); height: 72px;
  display: flex; align-items: center; justify-content: space-between;
}
.kaiko-about-wrap .kaiko-nav-logo {
  font-family: var(--kaiko-font-display); font-size: 1.75rem;
  font-weight: 700; letter-spacing: -0.03em;
  color: var(--kaiko-dark); text-decoration: none;
}
.kaiko-about-wrap .kaiko-nav-links {
  display: flex; gap: var(--kaiko-space-xl); align-items: center;
}
.kaiko-about-wrap .kaiko-nav-links a {
  font-family: var(--kaiko-font-body); font-size: 0.9rem; font-weight: 500;
  color: var(--kaiko-mid-gray); text-decoration: none;
  transition: color 0.2s; letter-spacing: 0.025em;
}
.kaiko-about-wrap .kaiko-nav-links a:hover { color: var(--kaiko-teal); }
.kaiko-about-wrap .kaiko-nav-links a.active { color: var(--kaiko-teal); font-weight: 600; }
.kaiko-about-wrap .kaiko-nav-cta {
  background: var(--kaiko-teal) !important; color: var(--kaiko-white) !important;
  padding: 0.5rem 1.25rem !important; border-radius: 100px !important;
  font-weight: 600 !important; font-size: 0.85rem !important;
  transition: background 0.2s !important;
}
.kaiko-about-wrap .kaiko-nav-cta:hover {
  background: var(--kaiko-deep-teal) !important;
}

/* --- About Hero --- */
.about-hero {
  padding: 160px var(--kaiko-space-xl) 80px;
  background: var(--kaiko-off-white);
  text-align: center;
}
.about-hero h1 {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 700; letter-spacing: -0.03em;
  margin-bottom: 1rem; color: var(--kaiko-dark);
}
.about-hero p {
  font-size: clamp(1.05rem, 1.5vw, 1.25rem);
  color: var(--kaiko-mid-gray); max-width: 650px;
  margin: 0 auto; line-height: 1.7;
}
.about-hero .hero-badge {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.375rem 1rem; background: rgba(26,92,82,0.08);
  color: var(--kaiko-teal); font-size: 0.75rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.08em;
  border-radius: 100px; margin-bottom: 1.5rem;
}

/* --- Story Section --- */
.about-story {
  max-width: 1200px; margin: 0 auto;
  padding: var(--kaiko-space-xl) var(--kaiko-space-xl) var(--kaiko-space-lg);
  display: grid; grid-template-columns: 1fr 1fr; gap: 80px;
  align-items: center;
}
.about-story-text h2 {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  margin-bottom: 1.5rem; color: var(--kaiko-dark);
}
.about-story-text p {
  color: var(--kaiko-mid-gray); font-size: 1.05rem;
  line-height: 1.8; margin-bottom: 1.25rem;
}
.about-story-text p:last-child { margin-bottom: 0; }
.about-story-image {
  position: relative; border-radius: var(--kaiko-radius-lg);
  overflow: hidden; aspect-ratio: 16/9;
  background: var(--kaiko-off-white);
}
.about-story-image img {
  width: 100%; height: 100%; object-fit: cover;
}

/* --- Values Section --- */
.about-values {
  background: var(--kaiko-off-white);
  padding: var(--kaiko-space-xl) var(--kaiko-space-xl);
}
.about-values-inner {
  max-width: 1200px; margin: 0 auto;
}
.about-values-header {
  text-align: center; margin-bottom: var(--kaiko-space-lg);
}
.about-values-header h2 {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  margin-bottom: 1rem; color: var(--kaiko-dark);
}
.about-values-header p {
  color: var(--kaiko-mid-gray); max-width: 550px;
  margin: 0 auto; font-size: 1.05rem; line-height: 1.7;
}
.values-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 2.5rem;
}
.value-card {
  background: var(--kaiko-white);
  border-radius: var(--kaiko-radius-md);
  padding: 2.5rem 2rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.value-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.08);
}
.value-icon {
  width: 56px; height: 56px;
  background: rgba(26,92,82,0.08);
  border-radius: var(--kaiko-radius-md);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.5rem;
}
.value-icon svg {
  width: 28px; height: 28px; color: var(--kaiko-teal);
}
.value-card h3 {
  font-size: 1.25rem; margin-bottom: 0.75rem;
  color: var(--kaiko-dark);
}
.value-card p {
  color: var(--kaiko-mid-gray); font-size: 0.95rem;
  line-height: 1.7;
}

/* --- Origin Story Section --- */
.about-origin {
  max-width: 1200px; margin: 0 auto;
  padding: var(--kaiko-space-xl) var(--kaiko-space-xl);
  display: grid; grid-template-columns: 1fr 1fr; gap: 80px;
  align-items: center;
}
.about-origin-image {
  border-radius: var(--kaiko-radius-lg); overflow: hidden;
  aspect-ratio: 16/9;
}
.about-origin-image img {
  width: 100%; height: 100%; object-fit: cover;
}
.about-origin-text h2 {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  margin-bottom: 1.5rem; color: var(--kaiko-dark);
}
.about-origin-text p {
  color: var(--kaiko-mid-gray); font-size: 1.05rem;
  line-height: 1.8; margin-bottom: 1.25rem;
}
.about-origin-text p:last-child { margin-bottom: 0; }
.origin-highlight {
  background: var(--kaiko-off-white);
  border-radius: var(--kaiko-radius-md);
  padding: 2rem 2.5rem;
  border-left: 4px solid var(--kaiko-teal);
}
.origin-highlight p {
  color: var(--kaiko-dark) !important;
  font-size: 1.1rem !important;
  font-weight: 500;
  font-style: italic;
  line-height: 1.7 !important;
  margin-bottom: 0 !important;
}

/* --- Stats Section --- */
.about-stats {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem;
  padding: var(--kaiko-space-lg) var(--kaiko-space-xl);
  max-width: 1200px; margin: 0 auto;
  border-top: 1px solid var(--kaiko-border);
  border-bottom: 1px solid var(--kaiko-border);
}
.stat-item { text-align: center; padding: 1.5rem 0; }
.stat-number {
  font-family: var(--kaiko-font-display); font-size: 2.5rem;
  font-weight: 700; color: var(--kaiko-teal); margin-bottom: 0.25rem;
}
.stat-label {
  font-size: 0.85rem; color: var(--kaiko-mid-gray);
  text-transform: uppercase; letter-spacing: 0.08em; font-weight: 500;
}

/* --- Team Section --- */
.about-team {
  padding: var(--kaiko-space-xl) var(--kaiko-space-xl);
  max-width: 1200px; margin: 0 auto;
}
.about-team-header {
  text-align: center; margin-bottom: var(--kaiko-space-lg);
}
.about-team-header h2 {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  margin-bottom: 1rem; color: var(--kaiko-dark);
}
.about-team-header p {
  color: var(--kaiko-mid-gray); max-width: 550px;
  margin: 0 auto; font-size: 1.05rem; line-height: 1.7;
}
.team-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 2.5rem;
}
.team-card {
  text-align: center;
}
.team-avatar {
  width: 160px; height: 160px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(26,92,82,0.15), rgba(26,92,82,0.05));
  margin: 0 auto 1.5rem;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.team-avatar svg {
  width: 48px; height: 48px; color: var(--kaiko-teal); opacity: 0.5;
}
.team-card h3 {
  font-size: 1.15rem; margin-bottom: 0.25rem;
  color: var(--kaiko-dark);
}
.team-card .team-role {
  font-size: 0.85rem; color: var(--kaiko-teal);
  font-weight: 600; text-transform: uppercase;
  letter-spacing: 0.06em; margin-bottom: 0.75rem;
}
.team-card p {
  color: var(--kaiko-mid-gray); font-size: 0.9rem;
  line-height: 1.6; max-width: 280px; margin: 0 auto;
}

/* --- CTA Section --- */
.about-cta {
  text-align: center; padding: var(--kaiko-space-xl) var(--kaiko-space-xl);
  background: var(--kaiko-off-white);
}
.about-cta h2 {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  margin-bottom: 1rem; color: var(--kaiko-dark);
}
.about-cta p {
  color: var(--kaiko-mid-gray); max-width: 500px;
  margin: 0 auto 2rem; font-size: 1.05rem;
}
.cta-btn {
  display: inline-block; padding: 0.875rem 2.5rem;
  background: var(--kaiko-teal); color: var(--kaiko-white);
  border-radius: 100px; font-weight: 600; font-size: 0.95rem;
  transition: background 0.2s; text-decoration: none;
}
.cta-btn:hover { background: var(--kaiko-deep-teal); }
.cta-btn-outline {
  display: inline-block; padding: 0.875rem 2.5rem;
  border: 1.5px solid var(--kaiko-teal); color: var(--kaiko-teal);
  border-radius: 100px; font-weight: 600; font-size: 0.95rem;
  transition: all 0.2s; text-decoration: none; margin-left: 1rem;
}
.cta-btn-outline:hover { background: var(--kaiko-teal); color: var(--kaiko-white); }

/* --- Footer --- */
.kaiko-about-wrap .kaiko-footer {
  background: var(--kaiko-dark); color: rgba(255,255,255,0.6);
  padding: 80px var(--kaiko-space-xl) 40px;
}
.kaiko-about-wrap .footer-inner {
  max-width: 1400px; margin: 0 auto;
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 60px; margin-bottom: 60px;
}
.kaiko-about-wrap .footer-brand h3 {
  font-family: var(--kaiko-font-display); font-size: 1.5rem;
  font-weight: 700; color: var(--kaiko-white); margin: 0 0 16px;
}
.kaiko-about-wrap .footer-brand p {
  font-size: 0.9rem; line-height: 1.7; max-width: 300px; margin: 0;
}
.kaiko-about-wrap .footer-col h4 {
  font-family: var(--kaiko-font-display); font-size: 0.8rem;
  font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--kaiko-white); margin: 0 0 24px;
}
.kaiko-about-wrap .footer-col ul { list-style: none; padding: 0; margin: 0; }
.kaiko-about-wrap .footer-col li { margin-bottom: 10px; }
.kaiko-about-wrap .footer-col a {
  color: rgba(255,255,255,0.5); text-decoration: none;
  font-size: 0.9rem; transition: color 0.2s;
}
.kaiko-about-wrap .footer-col a:hover { color: var(--kaiko-white); }
.kaiko-about-wrap .footer-bottom {
  max-width: 1400px; margin: 0 auto;
  padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1);
  display: flex; justify-content: space-between;
  font-size: 0.8rem; color: rgba(255,255,255,0.35);
}

/* --- Responsive --- */
@media (max-width: 1024px) {
  .about-story { grid-template-columns: 1fr; gap: 40px; }
  .about-origin { grid-template-columns: 1fr; gap: 40px; }
  .values-grid { grid-template-columns: repeat(2, 1fr); }
  .team-grid { grid-template-columns: repeat(2, 1fr); }
  .about-stats { grid-template-columns: repeat(2, 1fr); }
}
/* --- Mobile Nav Hamburger --- */
.kaiko-hamburger {
  display: none; background: none; border: none; cursor: pointer;
  padding: 8px; z-index: 1001; position: relative;
}
.kaiko-hamburger span {
  display: block; width: 24px; height: 2px; background: var(--kaiko-dark);
  margin: 6px 0; transition: all 0.3s ease; border-radius: 2px;
}
.kaiko-hamburger.active span:nth-child(1) {
  transform: rotate(45deg) translate(5px, 6px);
}
.kaiko-hamburger.active span:nth-child(2) { opacity: 0; }
.kaiko-hamburger.active span:nth-child(3) {
  transform: rotate(-45deg) translate(5px, -6px);
}

@media (max-width: 768px) {
  .kaiko-about-wrap .kaiko-nav { padding: 0 1.5rem !important; height: 64px !important; }
  .kaiko-hamburger { display: block !important; }
  .kaiko-about-wrap .kaiko-nav-links {
    display: none !important; flex-direction: column !important;
    position: fixed !important; top: 64px !important; left: 0 !important; right: 0 !important;
    background: rgba(255,255,255,0.98) !important;
    backdrop-filter: blur(20px) !important; -webkit-backdrop-filter: blur(20px) !important;
    padding: 1.5rem !important; gap: 0 !important;
    border-bottom: 1px solid var(--kaiko-border) !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08) !important;
  }
  .kaiko-about-wrap .kaiko-nav-links.mobile-open {
    display: flex !important;
  }
  .kaiko-about-wrap .kaiko-nav-links a {
    padding: 0.875rem 0 !important; font-size: 1rem !important;
    border-bottom: 1px solid var(--kaiko-border) !important;
    width: 100% !important; display: block !important;
  }
  .kaiko-about-wrap .kaiko-nav-links a:last-child {
    border-bottom: none !important;
  }
  .kaiko-about-wrap .kaiko-nav-links .kaiko-nav-cta {
    margin-top: 0.75rem !important; text-align: center !important;
    display: inline-block !important; width: auto !important;
  }
  .about-hero { padding: 100px 1.5rem 50px; }
  .about-hero h1 { font-size: 2rem; }
  .about-story { padding: 3rem 1.5rem; gap: 2rem; }
  .about-origin { padding: 3rem 1.5rem; gap: 2rem; }
  .about-values { padding: 3rem 1.5rem; }
  .values-grid { grid-template-columns: 1fr; }
  .team-grid { grid-template-columns: 1fr; }
  .about-stats { grid-template-columns: repeat(2, 1fr); padding: 2rem 1.5rem; }
  .about-team { padding: 3rem 1.5rem; }
  .kaiko-about-wrap .footer-inner { grid-template-columns: 1fr; gap: 40px; }
  .kaiko-about-wrap .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
  .about-cta { padding: 3rem 1.5rem; }
}
</style>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="kaiko-about-wrap">

<!-- Navigation -->
<nav class="kaiko-nav">
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kaiko-nav-logo">KAIKO</a>
  <button class="kaiko-hamburger" aria-label="Menu" onclick="this.classList.toggle('active');document.querySelector('.kaiko-nav-links').classList.toggle('mobile-open');">
    <span></span><span></span><span></span>
  </button>
  <div class="kaiko-nav-links">
    <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>">Products</a>
    <?php if ( is_user_logged_in() ) : ?><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Shop</a><?php endif; ?>
    <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="active">About</a>
    <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
    <a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>" class="kaiko-nav-cta"><?php echo is_user_logged_in() ? 'My Account' : 'Trade Login'; ?></a>
  </div>
</nav>

<!-- Hero Section -->
<section class="about-hero">
  <div class="hero-badge">Our Story</div>
  <h1>Crafted in Britain,<br>Built for Reptiles</h1>
  <p>We're a British brand designing and manufacturing premium reptile supplies with sustainability at the heart of everything we do.</p>
</section>

<!-- Brand Story Section -->
<section class="about-story">
  <div class="about-story-text">
    <h2>From Passion to Purpose</h2>
    <p>KAIKO was born from a simple observation: reptile keepers deserve products that are as thoughtfully designed as the habitats they create. Every bowl, hide, and accessory we produce is designed and manufactured right here in the United Kingdom.</p>
    <p>What started as a product line within Silkworm Store Limited, our sister company, quickly became something bigger. The response from the reptile community was overwhelming — keepers recognised the difference that British-made quality and species-specific design could make to their setups.</p>
    <p>That success gave us the confidence to launch KAIKO as a standalone brand, with a clear mission: to become one of the most respected names in the reptile industry, not through shortcuts, but through craft.</p>
  </div>
  <div class="about-story-image">
    <img src="<?php echo esc_url( content_url( '/uploads/kaiko-images/asd2_web.jpg' ) ); ?>" alt="KAIKO products displayed on shelves with varsity jacket — The Reptile Lifestyle" loading="lazy" />
  </div>
</section>

<!-- Values Section -->
<section class="about-values">
  <div class="about-values-inner">
    <div class="about-values-header">
      <h2>What Drives Us</h2>
      <p>Three principles guide every product we create and every decision we make.</p>
    </div>
    <div class="values-grid">
      <div class="value-card">
        <div class="value-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
          </svg>
        </div>
        <h3>Sustainability First</h3>
        <p>Environmental responsibility isn't an afterthought — it's woven into our process from material selection to packaging. We actively seek out sustainable materials and manufacturing methods that minimise our footprint.</p>
      </div>
      <div class="value-card">
        <div class="value-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" />
          </svg>
        </div>
        <h3>British Craftsmanship</h3>
        <p>Every KAIKO product is designed and manufactured in the UK. We work with local materials and skilled manufacturing partners to ensure every piece meets the highest standards of quality and durability.</p>
      </div>
      <div class="value-card">
        <div class="value-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
          </svg>
        </div>
        <h3>Species-Specific Design</h3>
        <p>We don't believe in one-size-fits-all. Each product is researched and engineered for specific reptile species, because a bearded dragon's needs are completely different from a ball python's.</p>
      </div>
    </div>
  </div>
</section>

<!-- Origin Story / Silkworm Section -->
<section class="about-origin">
  <div class="origin-highlight">
    <p>What began as a collection of handcrafted products for Silkworm Store has grown into something far bigger than we ever imagined. KAIKO is our commitment to the reptile community — a promise that British-made, sustainably produced products can also be the very best in the world.</p>
  </div>
  <div class="about-origin-image">
    <img src="<?php echo esc_url( content_url( '/uploads/kaiko-images/glass2_web.jpg' ) ); ?>" alt="KAIKO embossed glass logo" loading="lazy" />
  </div>
  <div class="about-origin-text">
    <h2>The Silkworm Store Legacy</h2>
    <p>Before KAIKO existed as its own brand, our products first found their home through Silkworm Store Limited — a trusted name in the exotic pet community. It was there that we learned what keepers truly needed.</p>
    <p>The feedback was immediate and encouraging. Customers appreciated the attention to detail, the natural aesthetics, and above all, the quality that comes from UK manufacturing. Demand grew rapidly, and it became clear that these products deserved a dedicated brand and identity.</p>
    <p>KAIKO was the answer — a brand built to carry that legacy forward, with the ambition and focus to push even further into innovation, sustainability, and design excellence.</p>
  </div>
</section>

<!-- Stats Section -->
<div class="about-stats">
  <div class="stat-item">
    <div class="stat-number">UK</div>
    <div class="stat-label">Designed & Made</div>
  </div>
  <div class="stat-item">
    <div class="stat-number">6</div>
    <div class="stat-label">Product Lines</div>
  </div>
  <div class="stat-item">
    <div class="stat-number">100%</div>
    <div class="stat-label">British Made</div>
  </div>
  <div class="stat-item">
    <div class="stat-number">0</div>
    <div class="stat-label">Compromises</div>
  </div>
</div>

<!-- Team Section -->
<section class="about-team">
  <div class="about-team-header">
    <h2>The People Behind KAIKO</h2>
    <p>A small, dedicated team of reptile enthusiasts, designers, and makers.</p>
  </div>
  <div class="team-grid">
    <div class="team-card">
      <div class="team-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
      </div>
      <h3>Founder</h3>
      <div class="team-role">CEO & Product Designer</div>
      <p>Reptile keeper and entrepreneur, driven by the belief that exotic pets deserve premium, purpose-built products.</p>
    </div>
    <div class="team-card">
      <div class="team-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" />
        </svg>
      </div>
      <h3>Manufacturing</h3>
      <div class="team-role">Production & Quality</div>
      <p>Ensuring every product that leaves our workshop meets the exacting standards KAIKO is known for.</p>
    </div>
    <div class="team-card">
      <div class="team-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
        </svg>
      </div>
      <h3>Sustainability</h3>
      <div class="team-role">Materials & Environment</div>
      <p>Researching greener materials and processes to ensure KAIKO leads on environmental responsibility in the industry.</p>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="about-cta">
  <h2>See What We Make</h2>
  <p>Explore our full range of handcrafted, British-made reptile supplies.</p>
  <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" class="cta-btn">View Products</a>
  <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="cta-btn-outline">Get in Touch</a>
</section>

<!-- Footer -->
<footer class="kaiko-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <h3>KAIKO</h3>
      <p>Premium reptile and exotic pet supplies, designed by keepers for keepers. Handcrafted in the UK with species-specific precision.</p>
    </div>
    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">All Products</a></li>
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
        <li><a href="#">Ball Pythons</a></li>
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
        <li><a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>">Trade Account</a></li>
        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
        <li><a href="">Privacy Policy</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; <?php echo date('Y'); ?> Kaiko. All rights reserved.</span>
    <span>Designed for reptile enthusiasts</span>
  </div>
</footer>

</div>

<script>
(function() {
  var isMobile = window.innerWidth <= 768;
  // Fix nav styling (WoodMart theme overrides stylesheet rules, so apply inline)
  var nav = document.querySelector('.kaiko-nav');
  if (nav) {
    var navPad = isMobile ? '0 1.5rem' : '0 6rem';
    var navH = isMobile ? '64px' : '72px';
    nav.style.cssText = 'position:fixed!important;top:0!important;left:0!important;right:0!important;z-index:1000!important;background:rgba(255,255,255,0.97)!important;backdrop-filter:blur(20px)!important;-webkit-backdrop-filter:blur(20px)!important;border-bottom:1px solid #e4e2de!important;padding:'+navPad+'!important;height:'+navH+'!important;display:flex!important;align-items:center!important;justify-content:space-between!important;';
  }
  var navLogo = document.querySelector('.kaiko-nav-logo');
  if (navLogo) {
    navLogo.style.cssText = "font-family:'Gotham','Gotham Bold',-apple-system,sans-serif!important;font-size:1.75rem!important;font-weight:700!important;letter-spacing:-0.03em!important;color:#1a1a1a!important;text-decoration:none!important;";
  }
  var navLinks = document.querySelector('.kaiko-nav-links');
  if (navLinks && !isMobile) {
    navLinks.style.cssText = 'display:flex!important;gap:6rem!important;align-items:center!important;';
    var links = navLinks.querySelectorAll('a');
    links.forEach(function(a) {
      if (!a.classList.contains('kaiko-nav-cta')) {
        a.style.cssText = "font-family:'Inter',-apple-system,sans-serif!important;font-size:0.9rem!important;font-weight:500!important;color:#6b6b6b!important;text-decoration:none!important;letter-spacing:0.025em!important;";
      } else {
        a.style.cssText = "background:#1a5c52!important;color:#fff!important;padding:0.5rem 1.25rem!important;border-radius:100px!important;font-weight:600!important;font-size:0.85rem!important;text-decoration:none!important;font-family:'Inter',-apple-system,sans-serif!important;";
      }
    });
  }

  // Logged-in nav updates (bypasses page cache)
  var isLoggedIn = document.body.classList.contains('logged-in') || document.getElementById('wpadminbar') !== null;
  if (isLoggedIn) {
    var navLinks = document.querySelector('.kaiko-nav-links');
    if (navLinks) {
      var aboutLink = navLinks.querySelector('a[href*="/about/"]');
      if (aboutLink && !navLinks.querySelector('a[href*="/shop/"]')) {
        var shopLink = document.createElement('a');
        shopLink.href = '/shop/';
        shopLink.textContent = 'Shop';
        navLinks.insertBefore(shopLink, aboutLink);
      }
    }
    var ctas = document.querySelectorAll('.kaiko-nav-cta, .btn-primary[href*="my-account"]');
    ctas.forEach(function(cta) {
      if (cta.textContent.trim() === 'Trade Login') {
        cta.textContent = 'My Account';
      }
    });
  }
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
