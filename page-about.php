<?php
/**
 * Template Name: KAIKO About
 * Description: Redesigned About page for KAIKO Products
 */

get_header(); ?>

<style>
/* ── WoodMart Override: hide page title & break out of theme container ── */
.page-template-page-about .main-page-wrapper { padding-top: 0 !important; }
.page-template-page-about .page-title,
.page-template-page-about .wd-page-title,
.page-template-page-about .page-title-default { display: none !important; }
.page-template-page-about .site-content { padding: 0 !important; }
.page-template-page-about .site-content > .container,
.page-template-page-about .content-area,
.page-template-page-about .site-main { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
.page-template-page-about .entry-content { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }

/* ── KAIKO About Page Styles ── */
.kaiko-about * { box-sizing: border-box; }
.kaiko-about img { max-width: 100%; display: block; }
.kaiko-about a { text-decoration: none; color: inherit; }
.kaiko-about {
  font-family: 'Inter', -apple-system, system-ui, 'Segoe UI', sans-serif;
  color: #1a1a1a;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  --teal: #1a5c52;
  --deep-teal: #134840;
  --lime: #b8d435;
  --gold: #c89b3c;
  --cream: #faf8f3;
  --white: #ffffff;
  --dark: #1a1a1a;
  --light-gray: #f5f5f5;
  --medium-gray: #888888;
}

/* Fade animation */
@keyframes kaikoFadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
.kaiko-about .fade-in { opacity: 0; animation: kaikoFadeInUp 0.8s ease forwards; }

/* Hero */
.kaiko-about .k-hero { background-color: var(--cream); padding: 6rem 2rem; text-align: center; }
.kaiko-about .k-hero-label { font-size: 0.875rem; font-weight: 600; color: var(--teal); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem; }
.kaiko-about .k-hero h1 { font-size: 3rem; font-weight: 700; color: var(--deep-teal); margin-bottom: 1rem; line-height: 1.2; }
.kaiko-about .k-hero p { font-size: 1.1rem; color: var(--medium-gray); max-width: 600px; margin: 0 auto; }

/* Passion section */
.kaiko-about .k-passion { padding: 5rem 2rem; max-width: 1200px; margin: 0 auto; }
.kaiko-about .k-passion-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
.kaiko-about .k-passion-text h3 { font-size: 1.5rem; color: var(--teal); margin-bottom: 1.5rem; font-weight: 600; }
.kaiko-about .k-passion-text p { font-size: 1rem; color: var(--dark); margin-bottom: 1rem; line-height: 1.8; }
.kaiko-about .k-passion-image { width: 100%; height: 400px; border-radius: 8px; overflow: hidden; background-color: var(--light-gray); }
.kaiko-about .k-passion-image img { width: 100%; height: 100%; object-fit: cover; }

/* Section heading */
.kaiko-about .k-section-heading { font-size: 2rem; font-weight: 700; color: var(--deep-teal); margin-bottom: 3rem; text-align: center; }

/* Drives section */
.kaiko-about .k-drives { padding: 5rem 2rem; background-color: var(--light-gray); }
.kaiko-about .k-drives-container { max-width: 1200px; margin: 0 auto; }
.kaiko-about .k-cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 3rem; }
.kaiko-about .k-card { background-color: var(--white); padding: 2.5rem 2rem; border-radius: 8px; text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.kaiko-about .k-card:hover { transform: translateY(-8px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.kaiko-about .k-card-icon { width: 60px; height: 60px; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; }
.kaiko-about .k-card-icon svg { width: 100%; height: 100%; stroke: var(--teal); fill: none; stroke-width: 1.5; }
.kaiko-about .k-card h3 { font-size: 1.25rem; color: var(--deep-teal); margin-bottom: 1rem; font-weight: 600; }
.kaiko-about .k-card p { font-size: 0.95rem; color: var(--medium-gray); line-height: 1.7; }

/* Legacy section */
.kaiko-about .k-legacy { padding: 5rem 2rem; max-width: 900px; margin: 0 auto; text-align: center; }
.kaiko-about .k-legacy h2 { font-size: 2rem; color: var(--deep-teal); margin-bottom: 2rem; font-weight: 700; }
.kaiko-about .k-legacy p { font-size: 1rem; color: var(--dark); line-height: 1.9; margin-bottom: 1rem; }

/* Stats bar */
.kaiko-about .k-stats-bar { background-color: var(--deep-teal); padding: 3rem 2rem; color: var(--white); }
.kaiko-about .k-stats-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center; }
.kaiko-about .k-stat-number { font-size: 2.5rem; font-weight: 700; color: var(--lime); margin-bottom: 0.5rem; }
.kaiko-about .k-stat-label { font-size: 0.9rem; font-weight: 500; color: var(--white); text-transform: uppercase; letter-spacing: 0.05em; }

/* Team section */
.kaiko-about .k-team { padding: 5rem 2rem; max-width: 1200px; margin: 0 auto; }
.kaiko-about .k-team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.5rem; margin-top: 3rem; }
.kaiko-about .k-team-card { text-align: center; }
.kaiko-about .k-avatar { width: 120px; height: 120px; margin: 0 auto 1.5rem; border-radius: 50%; background: linear-gradient(135deg, var(--teal) 0%, var(--deep-teal) 100%); }
.kaiko-about .k-team-card h3 { font-size: 1.1rem; color: var(--deep-teal); margin-bottom: 0.5rem; font-weight: 600; }
.kaiko-about .k-team-card .role { font-size: 0.9rem; color: var(--teal); font-weight: 500; margin-bottom: 1rem; }
.kaiko-about .k-team-card p { font-size: 0.95rem; color: var(--medium-gray); line-height: 1.7; }

/* CTA section */
.kaiko-about .k-cta { padding: 5rem 2rem; background-color: var(--cream); text-align: center; }
.kaiko-about .k-cta-container { max-width: 600px; margin: 0 auto; }
.kaiko-about .k-cta h2 { font-size: 2rem; color: var(--deep-teal); margin-bottom: 1.5rem; font-weight: 700; }
.kaiko-about .k-cta p { font-size: 1rem; color: var(--dark); margin-bottom: 2rem; }
.kaiko-about .k-btn-primary { display: inline-block; padding: 1rem 2.5rem; background-color: var(--teal); color: var(--white); text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 1rem; transition: background-color 0.3s ease, transform 0.2s ease; }
.kaiko-about .k-btn-primary:hover { background-color: var(--deep-teal); transform: translateY(-2px); color: var(--white); }

/* Responsive */
@media (max-width: 1024px) {
  .kaiko-about .k-hero h1 { font-size: 2.25rem; }
  .kaiko-about .k-passion-grid { grid-template-columns: 1fr; gap: 2rem; }
  .kaiko-about .k-passion-image { height: 300px; }
  .kaiko-about .k-cards-grid { grid-template-columns: repeat(2, 1fr); }
  .kaiko-about .k-stats-container { grid-template-columns: repeat(2, 1fr); }
  .kaiko-about .k-team-grid { grid-template-columns: repeat(2, 1fr); }
  .kaiko-about .k-section-heading { font-size: 1.5rem; }
}
@media (max-width: 640px) {
  .kaiko-about .k-hero { padding: 3rem 1.5rem; }
  .kaiko-about .k-hero h1 { font-size: 1.75rem; }
  .kaiko-about .k-hero p { font-size: 1rem; }
  .kaiko-about .k-passion, .kaiko-about .k-drives, .kaiko-about .k-legacy, .kaiko-about .k-team, .kaiko-about .k-cta { padding: 3rem 1.5rem; }
  .kaiko-about .k-passion-image { height: 250px; }
  .kaiko-about .k-cards-grid, .kaiko-about .k-team-grid, .kaiko-about .k-stats-container { grid-template-columns: 1fr; gap: 1.5rem; }
  .kaiko-about .k-section-heading { font-size: 1.25rem; margin-bottom: 2rem; }
  .kaiko-about .k-card { padding: 1.5rem 1rem; }
  .kaiko-about .k-avatar { width: 100px; height: 100px; }
}
</style>

<div class="kaiko-about">

<!-- Hero Section -->
<section class="k-hero">
  <div class="k-hero-label fade-in">Our Story</div>
  <h1 class="fade-in">Crafted In Britain, Built For Reptiles</h1>
  <p class="fade-in">A British brand dedicated to designing premium reptile supplies with sustainability at our core. Every product is thoughtfully engineered for the keepers who care most.</p>
</section>

<!-- From Passion to Purpose -->
<section class="k-passion">
  <div class="k-passion-grid">
    <div class="k-passion-text fade-in">
      <h3>From Passion To Purpose</h3>
      <p>KAIKO was born from a simple observation: reptile keepers deserve products designed with the same care they give their animals. Every bowl, hide, and accessory is designed and manufactured right here in the United Kingdom.</p>
      <p>We started as a carefully curated product line within Silkworm Store Limited, serving a growing community of dedicated keepers. The response was overwhelming. Demand grew. Collectors were enthusiastic. Keepers wanted more.</p>
      <p>So we launched KAIKO as a standalone brand with a singular mission: to become one of the most respected names in the reptile industry through craft, not shortcuts. Through thoughtfulness, not trends.</p>
    </div>
    <div class="k-passion-image fade-in">
      <img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/03/kaiko-lifestyle-28.jpg') ); ?>" alt="KAIKO Reptile Supplies Lifestyle" loading="lazy">
    </div>
  </div>
</section>

<!-- What Drives Us -->
<section class="k-drives">
  <div class="k-drives-container">
    <h2 class="k-section-heading fade-in">What Drives Us</h2>
    <div class="k-cards-grid">
      <div class="k-card fade-in">
        <div class="k-card-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"/><path d="M12 2v20M2 12h20" stroke-linecap="round"/><path d="M7 6.5c3-2.5 5.5-3 5-1.5M17 6.5c-3-2.5-5.5-3-5-1.5" stroke-linecap="round"/></svg>
        </div>
        <h3>Sustainability First</h3>
        <p>Environmental responsibility is woven into every decision — from material selection to packaging design. We believe premium products don't require environmental compromise.</p>
      </div>
      <div class="k-card fade-in">
        <div class="k-card-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3 5h5l-4 3 1 5-5-3-5 3 1-5-4-3h5z"/><line x1="3" y1="18" x2="21" y2="18"/><line x1="5" y1="21" x2="19" y2="21"/></svg>
        </div>
        <h3>British Craftsmanship</h3>
        <p>Every product is designed and manufactured in the UK. We partner with skilled makers who share our commitment to quality and use local materials wherever possible.</p>
      </div>
      <div class="k-card fade-in">
        <div class="k-card-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <h3>Species-Specific Design</h3>
        <p>There's no one-size-fits-all in reptile keeping. Each product is researched and developed for the specific species it serves, informed by keeper feedback and biological needs.</p>
      </div>
    </div>
  </div>
</section>

<!-- Legacy Section -->
<section class="k-legacy">
  <h2>The Silkworm Store Legacy</h2>
  <p>Our products first found their home within Silkworm Store Limited, where we learned directly from keepers what truly matters. Their feedback was invaluable. Their enthusiasm was inspiring. Their demand for better products drove us forward.</p>
  <p>KAIKO exists to carry that legacy into the future — built on a foundation of innovation, sustainability, and design excellence. We're not just making products. We're building the standard for what British reptile care can be.</p>
</section>

<!-- Stats Bar -->
<section class="k-stats-bar">
  <div class="k-stats-container">
    <div class="fade-in"><div class="k-stat-number">100%</div><div class="k-stat-label">UK Designed &amp; Made</div></div>
    <div class="fade-in"><div class="k-stat-number">6</div><div class="k-stat-label">Product Lines</div></div>
    <div class="fade-in"><div class="k-stat-number">100%</div><div class="k-stat-label">British Made</div></div>
    <div class="fade-in"><div class="k-stat-number">0</div><div class="k-stat-label">Compromises</div></div>
  </div>
</section>

<!-- Team Section -->
<section class="k-team">
  <h2 class="k-section-heading fade-in">The People Behind Kaiko</h2>
  <div class="k-team-grid">
    <div class="k-team-card fade-in">
      <div class="k-avatar"></div>
      <h3>Founder &amp; Direction</h3>
      <div class="role">CEO &amp; Product Designer</div>
      <p>Driving vision and design philosophy. Every product carries the mark of thoughtful engineering and keeper-first thinking.</p>
    </div>
    <div class="k-team-card fade-in">
      <div class="k-avatar"></div>
      <h3>Manufacturing Partner</h3>
      <div class="role">Production &amp; Quality</div>
      <p>Overseeing every detail of our UK manufacturing process. Quality assurance and craftsmanship standards ensure every product meets our exacting specifications.</p>
    </div>
    <div class="k-team-card fade-in">
      <div class="k-avatar"></div>
      <h3>Sustainability Lead</h3>
      <div class="role">Materials &amp; Environment</div>
      <p>Responsible for material selection and environmental impact. Ensuring our growth doesn't compromise the ecosystems our keepers care about.</p>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="k-cta">
  <div class="k-cta-container">
    <h2 class="fade-in">See What We Make</h2>
    <p class="fade-in">Explore our full range of species-specific reptile supplies, designed in Britain and built to last.</p>
    <a href="<?php echo esc_url( home_url('/products/') ); ?>" class="k-btn-primary fade-in">View Products</a>
  </div>
</section>

</div><!-- .kaiko-about -->

<script>
(function() {
  var observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        var siblings = entry.target.parentElement ? entry.target.parentElement.children : [];
        var delay = Array.from(siblings).indexOf(entry.target) * 0.1;
        entry.target.style.animationDelay = delay + 's';
        entry.target.classList.add('fade-in');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  document.querySelectorAll('.kaiko-about .fade-in').forEach(function(el) {
    observer.observe(el);
  });
})();
</script>

<?php get_footer(); ?>
