<?php
/**
 * Template Name: KAIKO Homepage
 * Description: Redesigned homepage for KAIKO Products
 */

get_header(); ?>

<style>
/* ── WoodMart Override: hide page title & break out of theme container ── */
body.page-template-template-homepage .main-page-wrapper { padding-top: 0 !important; }
body.page-template-template-homepage .page-title,
body.page-template-template-homepage .wd-page-title,
body.page-template-template-homepage .page-title-default { display: none !important; }
body.page-template-template-homepage .site-content { padding: 0 !important; max-width: 100% !important; width: 100% !important; }
body.page-template-template-homepage .site-content > .container,
body.page-template-template-homepage .container,
body.page-template-template-homepage .content-area,
body.page-template-template-homepage .site-main { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; box-sizing: border-box !important; }
body.page-template-template-homepage .entry-content { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
body.page-template-template-homepage .website-wrapper { overflow-x: hidden; }
body.page-template-template-homepage .main-page-wrapper .container { max-width: 100% !important; width: 100% !important; padding: 0 !important; }

/* ── KAIKO Nav ── */
.kaiko-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: rgba(255,255,255,0.97); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid #e4e2de; padding: 0 6rem; height: 72px; display: flex; align-items: center; justify-content: space-between; }
.kaiko-nav-logo { font-family: 'Gotham', 'Gotham Bold', -apple-system, sans-serif; font-size: 1.75rem; font-weight: 700; letter-spacing: -0.03em; color: #1a1a1a; text-decoration: none; }
.kaiko-nav-links { display: flex; gap: 6rem; align-items: center; }
.kaiko-nav-links a { font-family: 'Inter', -apple-system, sans-serif; font-size: 0.95rem; font-weight: 500; color: #57534E; text-decoration: none; transition: color 0.2s; }
.kaiko-nav-links a:hover { color: #1a1a1a; }
.kaiko-nav-cta { background: #1a5c52 !important; color: #fff !important; padding: 0.6rem 1.5rem; border-radius: 999px; font-size: 0.85rem !important; font-weight: 600 !important; letter-spacing: 0.03em; text-transform: uppercase; transition: background 0.2s; }
.kaiko-nav-cta:hover { background: #134840 !important; }
.kaiko-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 8px; flex-direction: column; gap: 5px; }
.kaiko-hamburger span { display: block; width: 24px; height: 2px; background: #1a1a1a; transition: 0.3s; }
@media (max-width: 768px) {
  .kaiko-nav { padding: 0 1.5rem; }
  .kaiko-hamburger { display: flex; }
  .kaiko-nav-links { display: none; position: fixed; top: 72px; left: 0; right: 0; background: #fff; flex-direction: column; padding: 2rem; gap: 1.5rem; border-bottom: 1px solid #e4e2de; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
  .kaiko-nav-links.mobile-open { display: flex; }
  .kaiko-nav-cta { text-align: center; }
}

/* ── KAIKO Homepage Styles ── */
:root {
  --k-teal: #1a5c52;
  --k-deep-teal: #134840;
  --k-light-teal: #2d8073;
  --k-lime: #b8d435;
  --k-lime-soft: #c8df5c;
  --k-gold: #c89b3c;
  --k-dark: #1a1a1a;
  --k-charcoal: #292524;
  --k-stone-600: #57534E;
  --k-stone-500: #78716C;
  --k-stone-400: #A8A29E;
  --k-stone-300: #D6D3D1;
  --k-stone-200: #E7E5E4;
  --k-stone-100: #F5F5F4;
  --k-stone-50: #FAFAF9;
  --k-cream: #f5f1ea;
  --k-white: #ffffff;
  --k-font: 'Inter', -apple-system, system-ui, 'Segoe UI', sans-serif;
  --k-r-sm: 6px;
  --k-r-md: 10px;
  --k-r-lg: 16px;
  --k-r-pill: 999px;
  --k-ease: cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --k-dur: 0.3s;
}

/* Reset only within our template */
.kaiko-home * { box-sizing: border-box; }
.kaiko-home img { max-width: 100%; display: block; }
.kaiko-home a { text-decoration: none; color: inherit; }

.kaiko-home { font-family: var(--k-font); color: var(--k-dark); line-height: 1.65; -webkit-font-smoothing: antialiased; }

/* Section utilities */
.kaiko-home .section-label { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: var(--k-teal); margin-bottom: 16px; }
.kaiko-home .section-heading { font-family: var(--k-font); font-size: clamp(1.8rem, 3vw, 2.6rem); font-weight: 700; color: var(--k-dark); line-height: 1.15; letter-spacing: -0.02em; }
.kaiko-home .section-sub { font-size: 0.95rem; color: var(--k-stone-500); max-width: 520px; margin: 14px auto 0; line-height: 1.7; }
.kaiko-home .text-center { text-align: center; }

/* Scroll reveal */
.kaiko-home .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.7s var(--k-ease), transform 0.7s var(--k-ease); }
.kaiko-home .reveal.visible { opacity: 1; transform: translateY(0); }

/* ── HERO ── */
.kaiko-home .k-hero { padding: 80px clamp(1.5rem, 4vw, 4rem) 80px; background: linear-gradient(180deg, var(--k-cream) 0%, var(--k-white) 100%); }
.kaiko-home .k-hero-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.kaiko-home .k-hero-label { display: inline-block; font-size: 0.68rem; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: var(--k-teal); background: rgba(26,92,82,0.06); border: 1px solid rgba(26,92,82,0.1); padding: 7px 16px; border-radius: var(--k-r-pill); margin-bottom: 24px; }
.kaiko-home .k-hero h1 { font-family: var(--k-font); font-size: clamp(2.2rem, 4.5vw, 3.6rem); font-weight: 700; line-height: 1.08; letter-spacing: -0.03em; color: var(--k-dark); margin-bottom: 20px; }
.kaiko-home .k-hero h1 .accent { color: var(--k-teal); }
.kaiko-home .k-hero-desc { font-size: 1rem; color: var(--k-stone-500); line-height: 1.7; max-width: 460px; margin-bottom: 32px; }
.kaiko-home .k-hero-actions { display: flex; gap: 14px; flex-wrap: wrap; }
.kaiko-home .btn-primary { display: inline-flex; align-items: center; gap: 8px; background: var(--k-teal); color: #fff; padding: 14px 28px; border-radius: var(--k-r-sm); font-size: 0.85rem; font-weight: 600; letter-spacing: 0.02em; transition: background var(--k-dur), transform var(--k-dur); }
.kaiko-home .btn-primary:hover { background: var(--k-deep-teal); transform: translateY(-1px); color: #fff; }
.kaiko-home .btn-secondary { display: inline-flex; align-items: center; gap: 8px; background: transparent; color: var(--k-dark); padding: 14px 28px; border-radius: var(--k-r-sm); font-size: 0.85rem; font-weight: 500; border: 1px solid var(--k-stone-300); transition: border-color var(--k-dur), background var(--k-dur); }
.kaiko-home .btn-secondary:hover { border-color: var(--k-stone-400); background: var(--k-stone-50); }

/* Hero image grid */
.kaiko-home .k-hero-visual { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.kaiko-home .k-hero-img { background: var(--k-stone-100); border-radius: var(--k-r-md); aspect-ratio: 1; overflow: hidden; border: 1px solid var(--k-stone-200); transition: transform 0.4s var(--k-ease); }
.kaiko-home .k-hero-img img { width: 100%; height: 100%; object-fit: cover; }
.kaiko-home .k-hero-img:hover { transform: scale(1.02); }
.kaiko-home .k-hero-img:nth-child(2) { transform: translateY(20px); }
.kaiko-home .k-hero-img:nth-child(2):hover { transform: translateY(20px) scale(1.02); }
.kaiko-home .k-hero-img:nth-child(3) { transform: translateY(-10px); }
.kaiko-home .k-hero-img:nth-child(3):hover { transform: translateY(-10px) scale(1.02); }

/* ── TRUST STRIP ── */
.kaiko-home .k-trust-strip { overflow: hidden; background: var(--k-teal); padding: 14px 0; }
.kaiko-home .k-trust-track { display: flex; gap: 3rem; animation: kaikoScrollLeft 30s linear infinite; white-space: nowrap; }
.kaiko-home .k-trust-item { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.85); display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.kaiko-home .k-trust-item svg { width: 14px; height: 14px; opacity: 0.6; }
@keyframes kaikoScrollLeft { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

/* ── CATEGORIES ── */
.kaiko-home .k-categories { padding: 90px clamp(1.5rem, 4vw, 4rem); }
.kaiko-home .k-categories-inner { max-width: 1200px; margin: 0 auto; }
.kaiko-home .k-categories-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 18px; margin-top: 42px; }
.kaiko-home .k-cat-card { background: var(--k-white); border: 1px solid var(--k-stone-200); border-radius: var(--k-r-md); padding: 28px 16px 22px; text-align: center; cursor: pointer; transition: border-color var(--k-dur), box-shadow var(--k-dur), transform var(--k-dur); }
.kaiko-home .k-cat-card:hover { border-color: var(--k-stone-300); box-shadow: 0 4px 20px rgba(0,0,0,0.04); transform: translateY(-3px); }
.kaiko-home .k-cat-icon { width: 48px; height: 48px; margin: 0 auto 14px; background: var(--k-stone-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.kaiko-home .k-cat-icon svg { width: 22px; height: 22px; color: var(--k-teal); }
.kaiko-home .k-cat-card h3 { font-size: 0.82rem; font-weight: 600; color: var(--k-dark); margin-bottom: 4px; }
.kaiko-home .k-cat-card p { font-size: 0.72rem; color: var(--k-stone-400); }

/* ── FEATURED PRODUCTS ── */
.kaiko-home .k-featured { padding: 80px clamp(1.5rem, 4vw, 4rem) 90px; background: var(--k-stone-50); }
.kaiko-home .k-featured-inner { max-width: 1200px; margin: 0 auto; }
.kaiko-home .k-products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 48px; }
.kaiko-home .k-product-card { background: var(--k-white); border: 1px solid var(--k-stone-200); border-radius: var(--k-r-md); overflow: hidden; transition: box-shadow var(--k-dur), transform var(--k-dur); cursor: pointer; }
.kaiko-home .k-product-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.06); transform: translateY(-3px); }
.kaiko-home .k-product-img { aspect-ratio: 1; background: var(--k-stone-100); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: var(--k-stone-400); position: relative; }
.kaiko-home .k-product-badge { position: absolute; top: 12px; right: 12px; font-size: 0.65rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; padding: 4px 10px; border-radius: var(--k-r-pill); }
.kaiko-home .badge-new { background: var(--k-lime); color: var(--k-dark); }
.kaiko-home .badge-sale { background: var(--k-gold); color: #fff; }
.kaiko-home .k-product-info { padding: 16px 18px 18px; }
.kaiko-home .k-product-info h3 { font-size: 0.88rem; font-weight: 600; color: var(--k-dark); margin-bottom: 6px; line-height: 1.3; }
.kaiko-home .k-product-info .price { font-size: 0.85rem; font-weight: 600; color: var(--k-teal); }
.kaiko-home .k-product-info .price .old { text-decoration: line-through; color: var(--k-stone-400); font-weight: 400; margin-left: 6px; font-size: 0.78rem; }

/* ── STATS ── */
.kaiko-home .k-stats { padding: 80px clamp(1.5rem, 4vw, 4rem); background: var(--k-charcoal); }
.kaiko-home .k-stats-inner { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; text-align: center; }
.kaiko-home .k-stat-num { font-family: var(--k-font); font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 700; color: var(--k-lime); letter-spacing: -0.02em; line-height: 1; margin-bottom: 8px; }
.kaiko-home .k-stat-label { font-size: 0.78rem; color: rgba(255,255,255,0.55); letter-spacing: 0.02em; }

/* ── WHY KAIKO ── */
.kaiko-home .k-why { padding: 90px clamp(1.5rem, 4vw, 4rem); }
.kaiko-home .k-why-inner { max-width: 1200px; margin: 0 auto; }
.kaiko-home .k-why-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 42px; }
.kaiko-home .k-why-card { padding: 32px 28px; border: 1px solid var(--k-stone-200); border-radius: var(--k-r-md); transition: border-color var(--k-dur), box-shadow var(--k-dur); }
.kaiko-home .k-why-card:hover { border-color: rgba(26,92,82,0.2); box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
.kaiko-home .k-why-icon { width: 40px; height: 40px; background: rgba(26,92,82,0.06); border-radius: var(--k-r-sm); display: flex; align-items: center; justify-content: center; margin-bottom: 18px; }
.kaiko-home .k-why-icon svg { width: 20px; height: 20px; color: var(--k-teal); }
.kaiko-home .k-why-card h3 { font-size: 0.95rem; font-weight: 600; color: var(--k-dark); margin-bottom: 8px; }
.kaiko-home .k-why-card p { font-size: 0.88rem; color: var(--k-stone-500); line-height: 1.65; }

/* ── TESTIMONIALS ── */
.kaiko-home .k-testimonials { padding: 80px clamp(1.5rem, 4vw, 4rem) 90px; background: var(--k-stone-50); }
.kaiko-home .k-testimonials-inner { max-width: 1200px; margin: 0 auto; }
.kaiko-home .k-testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 42px; }
.kaiko-home .k-testimonial-card { background: var(--k-white); border: 1px solid var(--k-stone-200); border-radius: var(--k-r-md); padding: 28px; text-align: left; }
.kaiko-home .k-testimonial-stars { color: var(--k-gold); font-size: 0.85rem; letter-spacing: 2px; margin-bottom: 14px; }
.kaiko-home .k-testimonial-text { font-size: 0.9rem; color: var(--k-stone-600); line-height: 1.7; margin-bottom: 18px; font-style: italic; }
.kaiko-home .k-testimonial-author { font-size: 0.8rem; font-weight: 600; color: var(--k-dark); }
.kaiko-home .k-testimonial-role { font-size: 0.72rem; color: var(--k-stone-400); margin-top: 2px; }

/* ── NEWSLETTER ── */
.kaiko-home .k-newsletter { padding: 90px clamp(1.5rem, 4vw, 4rem); }
.kaiko-home .k-newsletter-inner { max-width: 600px; margin: 0 auto; text-align: center; }
.kaiko-home .k-newsletter-form { display: flex; gap: 10px; margin-top: 32px; }
.kaiko-home .k-newsletter-form input { flex: 1; padding: 14px 18px; border: 1px solid var(--k-stone-300); border-radius: var(--k-r-sm); font-size: 0.88rem; font-family: var(--k-font); outline: none; transition: border-color var(--k-dur); background: var(--k-white); }
.kaiko-home .k-newsletter-form input:focus { border-color: var(--k-teal); }
.kaiko-home .k-newsletter-form button { background: var(--k-teal); color: #fff; border: none; padding: 14px 24px; border-radius: var(--k-r-sm); font-size: 0.78rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer; font-family: var(--k-font); transition: background var(--k-dur); white-space: nowrap; }
.kaiko-home .k-newsletter-form button:hover { background: var(--k-deep-teal); }

/* ── RESPONSIVE ── */
@media (max-width: 1024px) {
  .kaiko-home .k-hero-inner { grid-template-columns: 1fr; gap: 40px; text-align: center; }
  .kaiko-home .k-hero-desc { margin-left: auto; margin-right: auto; }
  .kaiko-home .k-hero-actions { justify-content: center; }
  .kaiko-home .k-hero-visual { max-width: 400px; margin: 0 auto; }
  .kaiko-home .k-categories-grid { grid-template-columns: repeat(3, 1fr); }
  .kaiko-home .k-products-grid { grid-template-columns: repeat(2, 1fr); }
  .kaiko-home .k-stats-inner { grid-template-columns: repeat(2, 1fr); gap: 32px; }
  .kaiko-home .k-why-grid { grid-template-columns: 1fr; }
  .kaiko-home .k-testimonials-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .kaiko-home .k-hero { padding-top: 50px; padding-bottom: 50px; }
  .kaiko-home .k-hero h1 { font-size: 2rem; }
  .kaiko-home .k-hero-actions { flex-direction: column; align-items: stretch; }
  .kaiko-home .k-hero-visual { grid-template-columns: 1fr 1fr; gap: 10px; }
  .kaiko-home .k-categories-grid { grid-template-columns: repeat(2, 1fr); }
  .kaiko-home .k-products-grid { grid-template-columns: 1fr; }
  .kaiko-home .k-stats-inner { grid-template-columns: repeat(2, 1fr); gap: 24px; }
  .kaiko-home .k-newsletter-form { flex-direction: column; }
}
</style>

<!-- KAIKO Navigation -->
<nav class="kaiko-nav">
  <a href="<?php echo esc_url(home_url('/')); ?>" class="kaiko-nav-logo">KAIKO</a>
  <button class="kaiko-hamburger" aria-label="Menu" onclick="this.classList.toggle('active');document.querySelector('.kaiko-nav-links').classList.toggle('mobile-open');">
    <span></span><span></span><span></span>
  </button>
  <div class="kaiko-nav-links">
    <a href="<?php echo esc_url(home_url('/products/')); ?>">Products</a>
    <a href="<?php echo esc_url(home_url('/about/')); ?>">About</a>
    <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a>
    <a href="<?php echo esc_url(home_url('/my-account/')); ?>" class="kaiko-nav-cta">Trade Login</a>
  </div>
</nav>

<div class="kaiko-home">

<!-- HERO -->
<section class="k-hero">
  <div class="k-hero-inner">
    <div>
      <span class="k-hero-label">Wholesale Reptile Supplies</span>
      <h1>Premium Habitat Equipment for <span class="accent">Exotic Keepers</span></h1>
      <p class="k-hero-desc">Handcrafted feeding bowls, humidity hides, and habitat accessories designed by reptile enthusiasts. Wholesale pricing for approved trade partners.</p>
      <div class="k-hero-actions">
        <a href="<?php echo esc_url( home_url('/shop/') ); ?>" class="btn-primary">Browse Products <span>&rarr;</span></a>
        <a href="<?php echo esc_url( home_url('/my-account/') ); ?>" class="btn-secondary">Apply for Trade</a>
      </div>
    </div>
    <div class="k-hero-visual">
      <div class="k-hero-img"><img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/03/kaiko-lifestyle-28.jpg') ); ?>" alt="Kaiko product lifestyle" loading="eager" /></div>
      <div class="k-hero-img"><img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/03/kaiko-lifestyle-30.jpg') ); ?>" alt="Kaiko product lifestyle" loading="eager" /></div>
      <div class="k-hero-img"><img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/03/kaiko-lifestyle-22.jpg') ); ?>" alt="Kaiko product lifestyle" loading="lazy" /></div>
      <div class="k-hero-img"><img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/03/kaiko-lifestyle-05.jpg') ); ?>" alt="Kaiko product lifestyle" loading="lazy" /></div>
    </div>
  </div>
</section>

<!-- TRUST STRIP -->
<div class="k-trust-strip">
  <div class="k-trust-track" id="kaikoTrustTrack">
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Free UK Shipping on Orders Over &pound;150</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Handcrafted in the UK</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Species-Specific Design</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Trade Accounts Available</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> 30-Day Returns</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Free UK Shipping on Orders Over &pound;150</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Handcrafted in the UK</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Species-Specific Design</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> Trade Accounts Available</span>
    <span class="k-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg> 30-Day Returns</span>
  </div>
</div>

<!-- CATEGORIES -->
<section class="k-categories reveal">
  <div class="k-categories-inner text-center">
    <p class="section-label">Shop by Species</p>
    <h2 class="section-heading">Find the Right Fit</h2>
    <p class="section-sub">Every product is designed with a specific species in mind — browse by your animal to see what works best.</p>
    <div class="k-categories-grid">
      <div class="k-cat-card">
        <div class="k-cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="0.5" fill="currentColor"/><circle cx="15" cy="10" r="0.5" fill="currentColor"/></svg></div>
        <h3>Bearded Dragons</h3>
        <p>42 products</p>
      </div>
      <div class="k-cat-card">
        <div class="k-cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M8 12h8"/></svg></div>
        <h3>Ball Pythons</h3>
        <p>38 products</p>
      </div>
      <div class="k-cat-card">
        <div class="k-cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></div>
        <h3>Leopard Geckos</h3>
        <p>35 products</p>
      </div>
      <div class="k-cat-card">
        <div class="k-cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22c5.52 0 10-4.48 10-10S17.52 2 12 2 2 6.48 2 12s4.48 10 10 10z"/><path d="M12 6v6l4 2"/></svg></div>
        <h3>Tortoises</h3>
        <p>28 products</p>
      </div>
      <div class="k-cat-card">
        <div class="k-cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg></div>
        <h3>Chameleons</h3>
        <p>24 products</p>
      </div>
      <div class="k-cat-card">
        <div class="k-cat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/></svg></div>
        <h3>Crested Geckos</h3>
        <p>31 products</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="k-featured reveal">
  <div class="k-featured-inner text-center">
    <p class="section-label">Featured Products</p>
    <h2 class="section-heading">Bestsellers This Month</h2>
    <p class="section-sub">Our most popular products, trusted by keepers and breeders across the UK.</p>
    <div class="k-products-grid">
      <div class="k-product-card">
        <div class="k-product-img"><span class="k-product-badge badge-new">New</span>Product Image</div>
        <div class="k-product-info"><h3>Naturalistic Water Bowl – Medium</h3><p class="price">&pound;8.50</p></div>
      </div>
      <div class="k-product-card">
        <div class="k-product-img">Product Image</div>
        <div class="k-product-info"><h3>Humidity Hide – Ball Python</h3><p class="price">&pound;12.00</p></div>
      </div>
      <div class="k-product-card">
        <div class="k-product-img"><span class="k-product-badge badge-sale">Sale</span>Product Image</div>
        <div class="k-product-info"><h3>Feeding Ledge – Crested Gecko</h3><p class="price">&pound;6.00 <span class="old">&pound;7.50</span></p></div>
      </div>
      <div class="k-product-card">
        <div class="k-product-img">Product Image</div>
        <div class="k-product-info"><h3>Basking Platform – Bearded Dragon</h3><p class="price">&pound;14.00</p></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="k-stats reveal">
  <div class="k-stats-inner">
    <div><div class="k-stat-num">2,400+</div><div class="k-stat-label">Products Shipped Monthly</div></div>
    <div><div class="k-stat-num">150+</div><div class="k-stat-label">Trade Partners</div></div>
    <div><div class="k-stat-num">12</div><div class="k-stat-label">Species Supported</div></div>
    <div><div class="k-stat-num">4.9</div><div class="k-stat-label">Average Rating</div></div>
  </div>
</section>

<!-- WHY KAIKO -->
<section class="k-why reveal">
  <div class="k-why-inner text-center">
    <p class="section-label">Why Kaiko</p>
    <h2 class="section-heading">Built for Keepers, by Keepers</h2>
    <p class="section-sub">We're a small UK team obsessed with getting the details right for every species.</p>
    <div class="k-why-grid">
      <div class="k-why-card">
        <div class="k-why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></div>
        <h3>Species-Specific Design</h3>
        <p>Every product is shaped around the natural behaviour and anatomy of the species it's made for. No generic fits.</p>
      </div>
      <div class="k-why-card">
        <div class="k-why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
        <h3>Handcrafted in the UK</h3>
        <p>Made in small batches with care. We quality-check every item before it ships so you can trust what you stock.</p>
      </div>
      <div class="k-why-card">
        <div class="k-why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <h3>Trade Partner Programme</h3>
        <p>Wholesale pricing, dedicated support, and priority access to new lines — built around serious retailers.</p>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="k-testimonials reveal">
  <div class="k-testimonials-inner text-center">
    <p class="section-label">What Keepers Say</p>
    <h2 class="section-heading">Trusted by the Community</h2>
    <div class="k-testimonials-grid">
      <div class="k-testimonial-card">
        <div class="k-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <p class="k-testimonial-text">"The quality is night and day compared to what we were stocking before. Customers notice the difference."</p>
        <p class="k-testimonial-author">James T.</p>
        <p class="k-testimonial-role">Reptile Retailer, Bristol</p>
      </div>
      <div class="k-testimonial-card">
        <div class="k-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <p class="k-testimonial-text">"Finally, a supplier that understands what species actually need. The crested gecko ledges are perfect."</p>
        <p class="k-testimonial-author">Sarah M.</p>
        <p class="k-testimonial-role">Exotic Pet Shop, Manchester</p>
      </div>
      <div class="k-testimonial-card">
        <div class="k-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <p class="k-testimonial-text">"Fast dispatch, great packaging, and useful products. The trade programme is well run too."</p>
        <p class="k-testimonial-author">Daniel K.</p>
        <p class="k-testimonial-role">Breeder, Nottingham</p>
      </div>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<section class="k-newsletter reveal">
  <div class="k-newsletter-inner">
    <p class="section-label">Stay Updated</p>
    <h2 class="section-heading">Join the Kaiko Community</h2>
    <p class="section-sub">New products, care tips, and exclusive trade offers delivered to your inbox.</p>
    <form class="k-newsletter-form" onsubmit="event.preventDefault()">
      <input type="email" placeholder="Enter your email address" />
      <button type="submit">Subscribe</button>
    </form>
  </div>
</section>

</div><!-- .kaiko-home -->

<script>
(function() {
  // Scroll reveal
  var reveals = document.querySelectorAll('.kaiko-home .reveal');
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry, i) {
      if (entry.isIntersecting) {
        setTimeout(function() { entry.target.classList.add('visible'); }, i * 80);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  reveals.forEach(function(el) { observer.observe(el); });

  // Animated stat counters
  var statNums = document.querySelectorAll('.kaiko-home .k-stat-num');
  var statObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        var el = entry.target;
        var text = el.textContent;
        var num = parseFloat(text.replace(/[^0-9.]/g, ''));
        var suffix = text.replace(/[0-9.,]/g, '');
        var hasComma = text.indexOf(',') > -1;
        var isDecimal = text.indexOf('.') > -1 && !hasComma;
        var duration = 1200;
        var start = performance.now();
        function animate(now) {
          var t = Math.min((now - start) / duration, 1);
          var ease = 1 - Math.pow(1 - t, 3);
          var val = num * ease;
          if (isDecimal) val = val.toFixed(1);
          else val = Math.round(val);
          if (hasComma) val = Number(val).toLocaleString();
          el.textContent = val + suffix;
          if (t < 1) requestAnimationFrame(animate);
        }
        requestAnimationFrame(animate);
        statObserver.unobserve(el);
      }
    });
  }, { threshold: 0.5 });
  statNums.forEach(function(el) { statObserver.observe(el); });
})();
</script>

<?php get_footer(); ?>
