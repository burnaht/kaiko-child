<?php
/**
 * Template Name: Kaiko Homepage
 * Template Post Type: page
 *
 * Renders the Kaiko homepage inside the site shell (header.php / footer.php).
 * Page-scoped styles land in <head> via a wp_head action — nav / hamburger
 * / mobile-menu rules have been removed in favour of kaiko-shell.css.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
    ?>
    <style>
    /* Homepage-specific design system */
    body.kaiko-homepage { margin: 0; padding: 0; background: var(--kaiko-white, #ffffff); font-family: var(--kaiko-font-body); color: var(--kaiko-black); -webkit-font-smoothing: antialiased; }

    /* Hero */
    body.kaiko-homepage .kaiko-hero { min-height: 90vh; display: flex; align-items: center; padding: 120px var(--kaiko-space-xl) 80px; background: linear-gradient(135deg, var(--kaiko-off-white) 0%, var(--kaiko-warm-gray) 100%); position: relative; overflow: hidden; }
    body.kaiko-homepage .kaiko-hero::after { content: ''; position: absolute; top: -20%; right: -10%; width: 60vw; height: 60vw; background: radial-gradient(circle, rgba(26,92,82,0.06) 0%, transparent 70%); border-radius: 50%; }
    body.kaiko-homepage .kaiko-hero-inner { max-width: 1400px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; position: relative; z-index: 2; }
    body.kaiko-homepage .kaiko-hero-tag { display: inline-block; font-family: var(--kaiko-font-display); font-size: 0.75rem; font-weight: var(--kaiko-weight-semibold); letter-spacing: 0.12em; text-transform: uppercase; color: var(--kaiko-teal); background: rgba(26,92,82,0.08); padding: 6px 16px; border-radius: 20px; margin-bottom: var(--kaiko-space-lg); }
    body.kaiko-homepage .kaiko-hero h1 { font-family: var(--kaiko-font-display); font-size: clamp(2.5rem, 5vw, 4.5rem); font-weight: var(--kaiko-weight-bold); line-height: 1.2; color: var(--kaiko-dark); margin-bottom: var(--kaiko-space-lg); letter-spacing: -0.01em; }
    body.kaiko-homepage .kaiko-hero h1 span { color: var(--kaiko-teal); }
    body.kaiko-homepage .kaiko-hero p { font-size: 1.15rem; line-height: 1.75; color: var(--kaiko-mid-gray); margin-bottom: var(--kaiko-space-xl); max-width: 520px; }
    body.kaiko-homepage .kaiko-hero-buttons { display: flex; gap: var(--kaiko-space-md); flex-wrap: wrap; }
    body.kaiko-homepage .kaiko-hero-visual { display: flex; justify-content: center; align-items: center; }
    body.kaiko-homepage .kaiko-hero-image-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--kaiko-space-md); overflow: visible; padding: 20px 0; }
    body.kaiko-homepage .kaiko-hero-image-grid .img-placeholder { border-radius: 20px; overflow: hidden; aspect-ratio: 3/4; background: var(--kaiko-cream); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: var(--kaiko-light-gray); }
    body.kaiko-homepage .kaiko-hero-image-grid .img-placeholder:nth-child(2) { margin-top: 40px; }

    /* Buttons */
    body.kaiko-homepage .btn-primary { display: inline-flex; align-items: center; gap: 8px; background: var(--kaiko-teal); color: var(--kaiko-white); padding: 14px 32px; border-radius: 8px; font-family: var(--kaiko-font-body); font-weight: 600; font-size: 0.95rem; text-decoration: none; transition: all 0.25s; border: none; cursor: pointer; letter-spacing: 0.02em; }
    body.kaiko-homepage .btn-primary:hover { background: var(--kaiko-deep-teal); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,92,82,0.2); }
    body.kaiko-homepage .btn-secondary { display: inline-flex; align-items: center; gap: 8px; background: transparent; color: var(--kaiko-dark); padding: 14px 32px; border-radius: 8px; border: 1px solid var(--kaiko-border); font-family: var(--kaiko-font-body); font-weight: 500; font-size: 0.95rem; text-decoration: none; transition: all 0.25s; cursor: pointer; }
    body.kaiko-homepage .btn-secondary:hover { background: var(--kaiko-off-white); border-color: var(--kaiko-teal); color: var(--kaiko-teal); }

    /* Marquee */
    body.kaiko-homepage .kaiko-marquee { background: var(--kaiko-teal); padding: 14px 0; overflow: hidden; white-space: nowrap; }
    body.kaiko-homepage .kaiko-marquee-inner { display: flex; gap: 60px; animation: kaiko-marquee-scroll 30s linear infinite; }
    body.kaiko-homepage .kaiko-marquee span { font-family: var(--kaiko-font-display); font-size: 0.85rem; font-weight: 600; color: var(--kaiko-white); letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap; }
    @keyframes kaiko-marquee-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

    /* Sections */
    body.kaiko-homepage .section { padding: 100px var(--kaiko-space-xl); }
    body.kaiko-homepage .section-inner { max-width: 1400px; margin: 0 auto; }
    body.kaiko-homepage .section--alt { background: var(--kaiko-off-white); }
    body.kaiko-homepage .section--dark { background: var(--kaiko-dark); color: var(--kaiko-white); }
    body.kaiko-homepage .section-header { text-align: center; margin-bottom: 60px; }
    body.kaiko-homepage .section-tag { display: inline-block; font-family: var(--kaiko-font-display); font-size: 0.7rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--kaiko-teal); margin-bottom: var(--kaiko-space-md); }
    body.kaiko-homepage .section--dark .section-tag { color: var(--kaiko-lime); }
    body.kaiko-homepage .section-title { font-family: var(--kaiko-font-display); font-size: clamp(1.75rem, 3vw, 2.75rem); font-weight: 700; color: var(--kaiko-dark); margin-bottom: var(--kaiko-space-md); letter-spacing: -0.01em; }
    body.kaiko-homepage .section--dark .section-title { color: var(--kaiko-white); }
    body.kaiko-homepage .section-subtitle { font-size: 1.05rem; color: var(--kaiko-mid-gray); max-width: 600px; margin: 0 auto; line-height: 1.75; }

    /* Species cards */
    body.kaiko-homepage .species-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--kaiko-space-lg); }
    body.kaiko-homepage .species-card { background: var(--kaiko-white); border: 1px solid var(--kaiko-border); border-radius: 16px; padding: var(--kaiko-space-xl); text-align: center; transition: all 0.3s cubic-bezier(0.16,1,0.3,1); cursor: pointer; }
    body.kaiko-homepage .species-card:hover { transform: translateY(-6px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); border-color: var(--kaiko-teal); }
    body.kaiko-homepage .species-icon { width: 64px; height: 64px; background: var(--kaiko-off-white); border-radius: 9999px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; overflow: hidden; }
    body.kaiko-homepage .species-card.has-photo .species-icon { width: 120px; height: 120px; background: transparent; border-radius: 0; }
    body.kaiko-homepage .species-card.has-photo .species-icon img { width: 100%; height: 100%; object-fit: contain; display: block; transition: transform 0.3s cubic-bezier(0.16,1,0.3,1); }
    body.kaiko-homepage .species-card.has-photo:hover .species-icon img { transform: scale(1.04); }
    body.kaiko-homepage .species-card h3 { font-family: var(--kaiko-font-display); font-size: 1rem; font-weight: 600; color: var(--kaiko-dark); margin: 0 0 4px; }
    body.kaiko-homepage .species-card p { font-size: 0.8rem; color: var(--kaiko-mid-gray); margin: 0; }

    /* Testimonials */
    body.kaiko-homepage .testimonial-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; max-width: 1200px; margin: 0 auto; }
    body.kaiko-homepage .testimonial-card { background: var(--kaiko-white); border: 1px solid var(--kaiko-border); border-radius: 16px; padding: 36px 32px; display: flex; flex-direction: column; transition: transform 260ms ease, box-shadow 260ms ease, border-color 260ms ease; }
    body.kaiko-homepage .testimonial-card:hover { transform: translateY(-4px); box-shadow: 0 18px 42px rgba(26,92,82,0.08); border-color: rgba(26,92,82,0.2); }
    body.kaiko-homepage .testimonial-stars { color: var(--kaiko-gold); font-size: 1.1rem; letter-spacing: 2px; margin-bottom: 16px; }
    body.kaiko-homepage .testimonial-text { font-size: 1rem; line-height: 1.7; color: var(--kaiko-dark); margin-bottom: 24px; font-style: italic; flex: 1; }
    body.kaiko-homepage .testimonial-author { font-weight: 600; color: var(--kaiko-dark); font-size: 0.95rem; }
    body.kaiko-homepage .testimonial-role { font-size: 0.8rem; color: var(--kaiko-mid-gray); margin-top: 2px; }
    @media (max-width: 980px) { body.kaiko-homepage .testimonial-grid { grid-template-columns: 1fr; max-width: 560px; } }

    /* Newsletter */
    body.kaiko-homepage .newsletter-form { display: flex; gap: 12px; max-width: 500px; margin: 0 auto; }
    body.kaiko-homepage .newsletter-form input { flex: 1; padding: 14px 20px; border: 1px solid var(--kaiko-border); border-radius: 8px; font-family: var(--kaiko-font-body); font-size: 0.95rem; background: var(--kaiko-white); color: var(--kaiko-dark); }
    body.kaiko-homepage .newsletter-form input:focus { outline: none; border-color: var(--kaiko-teal); box-shadow: 0 0 0 3px rgba(26,92,82,0.1); }

    /* Responsive */
    @media (max-width: 1024px) {
      body.kaiko-homepage .kaiko-hero-inner { grid-template-columns: 1fr; gap: 40px; }
      body.kaiko-homepage .kaiko-hero-visual { display: none; }
    }

    @media (max-width: 768px) {
      body.kaiko-homepage .kaiko-hero { padding: 100px 20px 60px; min-height: 70vh; }
      body.kaiko-homepage .kaiko-hero h1 { font-size: clamp(2rem, 8vw, 2.75rem); }
      body.kaiko-homepage .kaiko-hero-buttons { flex-direction: column; }
      body.kaiko-homepage .kaiko-hero-buttons a { text-align: center; justify-content: center; }
      body.kaiko-homepage .section { padding: 60px 20px; }
      body.kaiko-homepage .species-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
      body.kaiko-homepage .species-card { padding: 20px 12px; }
      body.kaiko-homepage .testimonial-card { padding: 28px 20px; }
      body.kaiko-homepage .newsletter-form { flex-direction: column; }
    }
    </style>
    <?php
}, 100 );

get_header();
?>

  <!-- HERO -->
  <section class="kaiko-hero">
    <div class="kaiko-hero-inner">
      <div class="kaiko-hero-content">
        <div class="kaiko-hero-tag">Wholesale Reptile Supplies</div>
        <h1>Quality Habitat<br>Equipment for<br><span>Exotic Keepers</span></h1>
        <p>Handcrafted feeding bowls, humidity hides, and habitat accessories designed by reptile enthusiasts. Wholesale pricing for approved trade partners.</p>
        <div class="kaiko-hero-buttons">
          <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn-primary">Browse Products &rarr;</a>
          <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>" class="btn-secondary">Apply for Trade</a>
        </div>
      </div>
      <div class="kaiko-hero-visual">
        <div class="kaiko-hero-image-grid">
          <div class="img-placeholder"><img src="/wp-content/uploads/2026/03/kaiko-lifestyle-28.jpg" alt="Kaiko reptile habitat products" style="width:100%;height:100%;object-fit:cover;" loading="eager" /></div>
          <div class="img-placeholder"><img src="/wp-content/uploads/2026/03/kaiko-lifestyle-30.jpg" alt="Kaiko reptile habitat products" style="width:100%;height:100%;object-fit:cover;" loading="eager" /></div>
          <div class="img-placeholder"><img src="/wp-content/uploads/2026/03/kaiko-lifestyle-22.jpg" alt="Kaiko reptile habitat products" style="width:100%;height:100%;object-fit:cover;" loading="lazy" /></div>
          <div class="img-placeholder"><img src="/wp-content/uploads/2026/03/kaiko-lifestyle-05.jpg" alt="Kaiko reptile habitat products" style="width:100%;height:100%;object-fit:cover;" loading="lazy" /></div>
        </div>
      </div>
    </div>
  </section>

  <!-- MARQUEE -->
  <div class="kaiko-marquee">
    <div class="kaiko-marquee-inner">
      <span>&#9733; Free UK Shipping on Orders Over &pound;150</span>
      <span>&#9733; Handcrafted in the UK</span>
      <span>&#9733; Species-Specific Design</span>
      <span>&#9733; Trade Accounts Available</span>
      <span>&#9733; 30-Day Returns</span>
      <span>&#9733; Free UK Shipping on Orders Over &pound;150</span>
      <span>&#9733; Handcrafted in the UK</span>
      <span>&#9733; Species-Specific Design</span>
      <span>&#9733; Trade Accounts Available</span>
      <span>&#9733; 30-Day Returns</span>
    </div>
  </div>

  <!-- SPECIES GRID -->
  <section class="section">
    <div class="section-inner">
      <div class="section-header">
        <div class="section-tag">Shop by Species</div>
        <h2 class="section-title">Find the Perfect Fit</h2>
        <p class="section-subtitle">Every product is designed with specific species in mind. Browse by reptile to find compatible equipment.</p>
      </div>
      <?php $kaiko_species_img_base = get_stylesheet_directory_uri() . '/assets/images/species/'; ?>
      <div class="species-grid">
        <div class="species-card has-photo"><div class="species-icon"><img src="<?php echo esc_url( $kaiko_species_img_base . 'bearded-dragon.png' ); ?>" alt="Bearded Dragon" loading="lazy" decoding="async" width="120" height="120" /></div><h3>Bearded Dragons</h3><p>42 products</p></div>
        <div class="species-card has-photo"><div class="species-icon"><img src="<?php echo esc_url( $kaiko_species_img_base . 'snake.png' ); ?>" alt="Snake" loading="lazy" decoding="async" width="120" height="120" /></div><h3>Snakes</h3><p>38 products</p></div>
        <div class="species-card has-photo"><div class="species-icon"><img src="<?php echo esc_url( $kaiko_species_img_base . 'leopard-gecko.png' ); ?>" alt="Leopard Gecko" loading="lazy" decoding="async" width="120" height="120" /></div><h3>Leopard Geckos</h3><p>35 products</p></div>
        <div class="species-card has-photo"><div class="species-icon"><img src="<?php echo esc_url( $kaiko_species_img_base . 'tortoise.png' ); ?>" alt="Tortoise" loading="lazy" decoding="async" width="120" height="120" /></div><h3>Tortoises</h3><p>28 products</p></div>
        <div class="species-card has-photo"><div class="species-icon"><img src="<?php echo esc_url( $kaiko_species_img_base . 'chameleon.png' ); ?>" alt="Chameleon" loading="lazy" decoding="async" width="120" height="120" /></div><h3>Chameleons</h3><p>24 products</p></div>
        <div class="species-card has-photo"><div class="species-icon"><img src="<?php echo esc_url( $kaiko_species_img_base . 'crested-gecko.png' ); ?>" alt="Crested Gecko" loading="lazy" decoding="async" width="120" height="120" /></div><h3>Crested Geckos</h3><p>31 products</p></div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="section">
    <div class="section-inner">
      <div class="section-header">
        <div class="section-tag">What Keepers Say</div>
        <h2 class="section-title">Trusted by the Community</h2>
      </div>
      <div class="testimonial-grid">

        <div class="testimonial-card">
          <div class="testimonial-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="testimonial-text">&ldquo;Finally, a supplier that understands what species actually need. The escape-proof roach bowls are exactly what I needed.&rdquo;</p>
          <div class="testimonial-author">Sarah M.</div>
          <div class="testimonial-role">Reptile Keeper, Manchester</div>
        </div>

        <div class="testimonial-card">
          <div class="testimonial-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="testimonial-text">&ldquo;The quality of the feeding bowls is on another level. My customers notice the difference, and they keep coming back for more Kaiko stock.&rdquo;</p>
          <div class="testimonial-author">James R.</div>
          <div class="testimonial-role">Exotic Pet Shop Owner, Bristol</div>
        </div>

        <div class="testimonial-card">
          <div class="testimonial-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="testimonial-text">&ldquo;Species-specific design that actually works. My bearded dragon finally has a water bowl he can&rsquo;t flip and his humidity hide is a proper upgrade.&rdquo;</p>
          <div class="testimonial-author">Emma T.</div>
          <div class="testimonial-role">Bearded Dragon Keeper, Leeds</div>
        </div>

        <div class="testimonial-card">
          <div class="testimonial-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="testimonial-text">&ldquo;Trade signup was painless and shipping was next-day. Finally a UK supplier built for reptile shops who knows the brief.&rdquo;</p>
          <div class="testimonial-author">Daniel K.</div>
          <div class="testimonial-role">Reptile Store Manager, Glasgow</div>
        </div>

        <div class="testimonial-card">
          <div class="testimonial-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="testimonial-text">&ldquo;The water bowls actually look like part of the setup &mdash; properly considered design that sits naturally in the enclosure instead of looking like a bit of plastic bolted on. Aesthetic matters and Kaiko nails it.&rdquo;</p>
          <div class="testimonial-author">Olivia P.</div>
          <div class="testimonial-role">Tortoise Keeper, Brighton</div>
        </div>

        <div class="testimonial-card">
          <div class="testimonial-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <p class="testimonial-text">&ldquo;The humidity hide has made a proper difference to shedding across my leopard gecko collection. Holds moisture exactly as it should and looks great in the enclosure &mdash; the design is spot on for hatchlings too.&rdquo;</p>
          <div class="testimonial-author">Michael H.</div>
          <div class="testimonial-role">Leopard Gecko Breeder, Cardiff</div>
        </div>

      </div>
    </div>
  </section>

  <!-- NEWSLETTER -->
  <section class="section section--alt">
    <div class="section-inner">
      <div class="section-header">
        <div class="section-tag">Stay Updated</div>
        <h2 class="section-title">Join the Kaiko Community</h2>
        <p class="section-subtitle">New products, care tips, and exclusive trade offers delivered to your inbox.</p>
      </div>
      <form class="newsletter-form" onsubmit="return false;">
        <input type="email" placeholder="Enter your email address">
        <button class="btn-primary" type="submit">Subscribe</button>
      </form>
    </div>
  </section>

<?php
get_footer();
