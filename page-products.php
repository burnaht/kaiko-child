<?php
/**
 * Template Name: Kaiko Products Page
 * Description: Full-width products gallery page with Kaiko custom design system.
 *              Full-width products gallery with filter, lightbox, and responsive grid.
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
.whb-header, .woodmart-prefooter, .footer-container, .wd-footer, .website-wrapper > footer,
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
.kaiko-products-wrap {
  font-family: var(--kaiko-font-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--kaiko-black);
  background: var(--kaiko-white);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  overflow-x: hidden;
}
.kaiko-products-wrap *, .kaiko-products-wrap *::before, .kaiko-products-wrap *::after {
  box-sizing: border-box; margin: 0; padding: 0;
}
.kaiko-products-wrap img { display: block; max-width: 100%; }
.kaiko-products-wrap a { color: inherit; text-decoration: none; }
.kaiko-products-wrap h1, .kaiko-products-wrap h2, .kaiko-products-wrap h3,
.kaiko-products-wrap h4, .kaiko-products-wrap h5, .kaiko-products-wrap h6 {
  font-family: var(--kaiko-font-display);
  font-weight: 700;
  line-height: 1.15;
  color: var(--kaiko-dark);
  letter-spacing: -0.02em;
}

/* --- Navigation --- */
.kaiko-products-wrap .kaiko-nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
  background: rgba(255,255,255,0.97);
  backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--kaiko-border);
  padding: 0 var(--kaiko-space-xl); height: 72px;
  display: flex; align-items: center; justify-content: space-between;
}
.kaiko-products-wrap .kaiko-nav-logo {
  font-family: var(--kaiko-font-display); font-size: 1.75rem;
  font-weight: 700; letter-spacing: -0.03em;
  color: var(--kaiko-dark); text-decoration: none;
}
.kaiko-products-wrap .kaiko-nav-links {
  display: flex; gap: var(--kaiko-space-xl); align-items: center;
}
.kaiko-products-wrap .kaiko-nav-links a {
  font-family: var(--kaiko-font-body); font-size: 0.9rem; font-weight: 500;
  color: var(--kaiko-mid-gray); text-decoration: none;
  transition: color 0.2s; letter-spacing: 0.025em;
}
.kaiko-products-wrap .kaiko-nav-links a:hover { color: var(--kaiko-teal); }
.kaiko-products-wrap .kaiko-nav-links a.active { color: var(--kaiko-teal); font-weight: 600; }
.kaiko-products-wrap .kaiko-nav-cta {
  background: var(--kaiko-teal) !important; color: var(--kaiko-white) !important;
  padding: 0.5rem 1.25rem !important; border-radius: 100px !important;
  font-weight: 600 !important; font-size: 0.85rem !important;
  transition: background 0.2s !important;
}
.kaiko-products-wrap .kaiko-nav-cta:hover {
  background: var(--kaiko-deep-teal) !important;
}

/* --- Products Hero --- */
.products-hero {
  padding: 160px var(--kaiko-space-xl) 80px;
  background: var(--kaiko-off-white);
  text-align: center;
}
.products-hero h1 {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 700; letter-spacing: -0.03em;
  margin-bottom: 1rem; color: var(--kaiko-dark);
}
.products-hero p {
  font-size: clamp(1.05rem, 1.5vw, 1.25rem);
  color: var(--kaiko-mid-gray); max-width: 600px;
  margin: 0 auto 2rem; line-height: 1.6;
}
.products-hero .hero-badge {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.375rem 1rem; background: rgba(26,92,82,0.08);
  color: var(--kaiko-teal); font-size: 0.75rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.08em;
  border-radius: 100px; margin-bottom: 1.5rem;
}

/* --- Filter Buttons --- */
.products-filters {
  display: flex; justify-content: center; gap: 0.75rem;
  padding: 2rem var(--kaiko-space-xl);
  flex-wrap: wrap; background: var(--kaiko-white);
  position: sticky; top: 72px; z-index: 99;
  border-bottom: 1px solid var(--kaiko-border);
}
.filter-btn {
  padding: 0.5rem 1.25rem; border: 1.5px solid var(--kaiko-border);
  border-radius: 100px; background: var(--kaiko-white);
  font-family: var(--kaiko-font-body); font-size: 0.85rem;
  font-weight: 500; color: var(--kaiko-mid-gray);
  cursor: pointer; transition: all 0.2s;
}
.filter-btn:hover { border-color: var(--kaiko-teal); color: var(--kaiko-teal); }
.filter-btn.active {
  background: var(--kaiko-teal); color: var(--kaiko-white);
  border-color: var(--kaiko-teal);
}

/* --- Gallery Grid --- */
.products-gallery {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  padding: var(--kaiko-space-lg) var(--kaiko-space-xl);
  max-width: 1400px; margin: 0 auto;
}
.gallery-item {
  position: relative; border-radius: var(--kaiko-radius-md);
  overflow: hidden; cursor: pointer;
  aspect-ratio: 1; background: var(--kaiko-off-white);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.gallery-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.12);
}
.gallery-item img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.5s ease;
}
.gallery-item:hover img { transform: scale(1.05); }
.gallery-item .overlay {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 1.25rem; background: linear-gradient(transparent, rgba(0,0,0,0.7));
  color: var(--kaiko-white); opacity: 0;
  transition: opacity 0.3s ease;
}
.gallery-item:hover .overlay { opacity: 1; }
.overlay .item-title {
  font-family: var(--kaiko-font-display); font-size: 1rem;
  font-weight: 700; margin-bottom: 0.25rem;
}
.overlay .item-cat {
  font-size: 0.8rem; opacity: 0.8;
}
/* Featured items span 2 columns */
.gallery-item.featured { grid-column: span 2; aspect-ratio: 2/1; }

/* --- Stats Section --- */
.products-stats {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem;
  padding: var(--kaiko-space-lg) var(--kaiko-space-xl);
  max-width: 1400px; margin: 0 auto;
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

/* --- CTA Section --- */
.products-cta {
  text-align: center; padding: var(--kaiko-space-xl) var(--kaiko-space-xl);
  background: var(--kaiko-off-white);
}
.products-cta h2 {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  margin-bottom: 1rem; color: var(--kaiko-dark);
}
.products-cta p {
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
.kaiko-products-wrap .kaiko-footer {
  background: var(--kaiko-dark); color: rgba(255,255,255,0.6);
  padding: 80px var(--kaiko-space-xl) 40px;
}
.kaiko-products-wrap .footer-inner {
  max-width: 1400px; margin: 0 auto;
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 60px; margin-bottom: 60px;
}
.kaiko-products-wrap .footer-brand h3 {
  font-family: var(--kaiko-font-display); font-size: 1.5rem;
  font-weight: 700; color: var(--kaiko-white); margin: 0 0 16px;
}
.kaiko-products-wrap .footer-brand p {
  font-size: 0.9rem; line-height: 1.7; max-width: 300px; margin: 0;
}
.kaiko-products-wrap .footer-col h4 {
  font-family: var(--kaiko-font-display); font-size: 0.8rem;
  font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--kaiko-white); margin: 0 0 24px;
}
.kaiko-products-wrap .footer-col ul { list-style: none; padding: 0; margin: 0; }
.kaiko-products-wrap .footer-col li { margin-bottom: 10px; }
.kaiko-products-wrap .footer-col a {
  color: rgba(255,255,255,0.5); text-decoration: none;
  font-size: 0.9rem; transition: color 0.2s;
}
.kaiko-products-wrap .footer-col a:hover { color: var(--kaiko-white); }
.kaiko-products-wrap .footer-bottom {
  max-width: 1400px; margin: 0 auto;
  padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1);
  display: flex; justify-content: space-between;
  font-size: 0.8rem; color: rgba(255,255,255,0.35);
}

/* --- Lightbox --- */
.lightbox-overlay {
  display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.92); z-index: 10000;
  align-items: center; justify-content: center; padding: 2rem;
}
.lightbox-overlay.active { display: flex; }
.lightbox-content {
  position: relative; max-width: 900px; max-height: 85vh; width: 100%;
}
.lightbox-content img {
  width: 100%; height: auto; max-height: 85vh; object-fit: contain;
  border-radius: var(--kaiko-radius-md);
}
.lightbox-close {
  position: absolute; top: -40px; right: 0;
  background: none; border: none; color: white;
  font-size: 2rem; cursor: pointer; padding: 0.5rem;
}
.lightbox-caption {
  text-align: center; color: rgba(255,255,255,0.7);
  margin-top: 1rem; font-size: 0.9rem;
}
.lightbox-nav {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(255,255,255,0.15); border: none;
  color: white; width: 50px; height: 50px; border-radius: 50%;
  font-size: 1.5rem; cursor: pointer; transition: background 0.2s;
}
.lightbox-nav:hover { background: rgba(255,255,255,0.3); }
.lightbox-prev { left: -70px; }
.lightbox-next { right: -70px; }

/* --- Responsive --- */
@media (max-width: 1024px) {
  .products-gallery { grid-template-columns: repeat(2, 1fr); }
  .gallery-item.featured { grid-column: span 2; }
  .products-stats { grid-template-columns: repeat(2, 1fr); }
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
  .kaiko-products-wrap .kaiko-nav { padding: 0 1.5rem !important; height: 64px !important; }
  .kaiko-hamburger { display: block !important; }
  .kaiko-products-wrap .kaiko-nav-links {
    display: none !important; flex-direction: column !important;
    position: fixed !important; top: 64px !important; left: 0 !important; right: 0 !important;
    background: rgba(255,255,255,0.98) !important;
    backdrop-filter: blur(20px) !important; -webkit-backdrop-filter: blur(20px) !important;
    padding: 1.5rem !important; gap: 0 !important;
    border-bottom: 1px solid var(--kaiko-border) !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08) !important;
  }
  .kaiko-products-wrap .kaiko-nav-links.mobile-open {
    display: flex !important;
  }
  .kaiko-products-wrap .kaiko-nav-links a {
    padding: 0.875rem 0 !important; font-size: 1rem !important;
    border-bottom: 1px solid var(--kaiko-border) !important;
    width: 100% !important; display: block !important;
  }
  .kaiko-products-wrap .kaiko-nav-links a:last-child {
    border-bottom: none !important;
  }
  .kaiko-products-wrap .kaiko-nav-links .kaiko-nav-cta {
    margin-top: 0.75rem !important; text-align: center !important;
    display: inline-block !important; width: auto !important;
  }
  .products-hero { padding: 100px 1.5rem 50px; }
  .products-hero h1 { font-size: 2rem; }
  .products-filters { padding: 1.5rem; }
  .products-gallery { grid-template-columns: 1fr; padding: 1.5rem; gap: 1rem; }
  .gallery-item.featured { grid-column: span 1; aspect-ratio: 1; }
  .gallery-item .overlay { opacity: 1; }
  .products-stats { grid-template-columns: repeat(2, 1fr); padding: 2rem 1.5rem; }
  .kaiko-products-wrap .footer-inner { grid-template-columns: 1fr; gap: 40px; }
  .kaiko-products-wrap .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
  .products-cta { padding: 3rem 1.5rem; }
  .lightbox-prev { left: 10px; }
  .lightbox-next { right: 10px; }
}
</style>
</head>

<body <?php body_class( 'kaiko-page kaiko-products-page' ); ?>>
<?php wp_body_open(); ?>

<div class="kaiko-products-wrap">

<?php get_template_part( 'template-parts/kaiko-header' ); ?>

<!-- Hero Section -->
<section class="products-hero">
  <div class="hero-badge">2026 Collection</div>
  <h1>Our Products</h1>
  <p>Handcrafted reptile supplies, beautifully photographed in natural habitats. See how our products bring vivariums to life.</p>
</section>

<!-- Filter Buttons -->
<div class="products-filters">
  <button class="filter-btn active" data-filter="all">All Products</button>
  <button class="filter-btn" data-filter="water-bowls">Water Bowls</button>
  <button class="filter-btn" data-filter="tree-bowls">Tree Bowls</button>
  <button class="filter-btn" data-filter="rock-bowls">Rock Bowls</button>
  <button class="filter-btn" data-filter="feeder-pro">Feeder Pro</button>
  <button class="filter-btn" data-filter="red-bowls">Red Bowls</button>
  <button class="filter-btn" data-filter="food-bowls">Food Bowls</button>
</div>

<!-- Gallery Grid -->
<div class="products-gallery">

  <!-- Food Bowls -->
  <div class="gallery-item" data-category="food-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-24.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item featured" data-category="food-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-25.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="food-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-26.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>

  <!-- Red Bowls -->
  <div class="gallery-item" data-category="red-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-20.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item featured" data-category="red-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-21.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="red-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-22.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="red-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-23.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>

  <!-- Feeder Pro -->
  <div class="gallery-item" data-category="feeder-pro">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-18.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="feeder-pro">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-19.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>

  <!-- Rock Bowls -->
  <div class="gallery-item featured" data-category="rock-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-15.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="rock-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-16.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="rock-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-30.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>

  <!-- Tree Bowls -->
  <div class="gallery-item featured" data-category="tree-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-10.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="tree-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-13.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="tree-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-28.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>

  <!-- Water Bowls -->
  <div class="gallery-item" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-03.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-04.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-05.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item featured" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-06.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-07.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-08.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-09.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>
  <div class="gallery-item" data-category="water-bowls">
    <img src="<?php echo esc_url( site_url( '/wp-content/uploads/2026/03/kaiko-lifestyle-27.jpg' ) ); ?>" alt="Kaiko product" loading="lazy">
  </div>

</div>

<!-- Stats Section -->
<div class="products-stats">
  <div class="stat-item">
    <div class="stat-number">60+</div>
    <div class="stat-label">Lifestyle Shots</div>
  </div>
  <div class="stat-item">
    <div class="stat-number">6</div>
    <div class="stat-label">Product Lines</div>
  </div>
  <div class="stat-item">
    <div class="stat-number">100%</div>
    <div class="stat-label">Handcrafted</div>
  </div>
  <div class="stat-item">
    <div class="stat-number">UK</div>
    <div class="stat-label">Made In Britain</div>
  </div>
</div>

<!-- CTA Section -->
<section class="products-cta">
  <h2>Ready to Transform Your Setup?</h2>
  <p>Every Kaiko product is handcrafted in the UK with species-specific precision. Explore our full collection.</p>
  <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="cta-btn">Shop Collection</a>
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

<!-- Lightbox -->
<div class="lightbox-overlay" id="lightbox">
  <button class="lightbox-close" id="lightbox-close">&times;</button>
  <button class="lightbox-nav lightbox-prev" id="lightbox-prev">&#8249;</button>
  <div class="lightbox-content">
    <img id="lightbox-img" src="" alt="">
    <div class="lightbox-caption" id="lightbox-caption"></div>
  </div>
  <button class="lightbox-nav lightbox-next" id="lightbox-next">&#8250;</button>
</div>

</div>

<script>
(function() {
  // Filter functionality
  const filterBtns = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      galleryItems.forEach(item => {
        if (filter === 'all' || item.dataset.category === filter) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });

  // Lightbox functionality
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  const lightboxCaption = document.getElementById('lightbox-caption');
  let currentIndex = 0;
  let visibleItems = [];

  function getVisibleItems() {
    return Array.from(galleryItems).filter(item => item.style.display !== 'none');
  }

  galleryItems.forEach(item => {
    item.addEventListener('click', () => {
      visibleItems = getVisibleItems();
      currentIndex = visibleItems.indexOf(item);
      const img = item.querySelector('img');
      const title = item.querySelector('.item-title');
      lightboxImg.src = img.src;
      lightboxCaption.textContent = title ? title.textContent : '';
      lightbox.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

  function closeLightbox() {
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
  }

  document.getElementById('lightbox-prev').addEventListener('click', (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + visibleItems.length) % visibleItems.length;
    updateLightbox();
  });

  document.getElementById('lightbox-next').addEventListener('click', (e) => {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % visibleItems.length;
    updateLightbox();
  });

  function updateLightbox() {
    const item = visibleItems[currentIndex];
    const img = item.querySelector('img');
    const title = item.querySelector('.item-title');
    lightboxImg.src = img.src;
    lightboxCaption.textContent = title ? title.textContent : '';
  }

  document.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('active')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') { currentIndex = (currentIndex - 1 + visibleItems.length) % visibleItems.length; updateLightbox(); }
    if (e.key === 'ArrowRight') { currentIndex = (currentIndex + 1) % visibleItems.length; updateLightbox(); }
  });

})();
</script>

<?php wp_footer(); ?>
</body>
</html>
