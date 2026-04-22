<?php
/**
 * Template Name: Kaiko Products Page
 * Description: Full-width products gallery page with Kaiko custom design system.
 *              Full-width products gallery with filter, lightbox, and responsive grid.
 *
 * Page-scoped styles land in <head> via a wp_head action — nav /
 * hamburger / Woodmart-suppression rules are gone (kaiko-shell.css
 * owns the nav; Woodmart's header is never rendered).
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
    ?>
    <style>
    /* --- Base Typography (scoped to the page wrap) --- */
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
      box-sizing: border-box;
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
      margin: 0;
    }
    .kaiko-products-wrap p { margin: 0; }

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
    .overlay .item-cat { font-size: 0.8rem; opacity: 0.8; }
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

    @media (max-width: 768px) {
      .products-hero { padding: 100px 1.5rem 50px; }
      .products-hero h1 { font-size: 2rem; }
      .products-filters { padding: 1.5rem; }
      .products-gallery { grid-template-columns: 1fr; padding: 1.5rem; gap: 1rem; }
      .gallery-item.featured { grid-column: span 1; aspect-ratio: 1; }
      .gallery-item .overlay { opacity: 1; }
      .products-stats { grid-template-columns: repeat(2, 1fr); padding: 2rem 1.5rem; }
      .products-cta { padding: 3rem 1.5rem; }
      .lightbox-prev { left: 10px; }
      .lightbox-next { right: 10px; }
    }
    </style>
    <?php
}, 100 );

get_header();
?>

<div class="kaiko-products-wrap">

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

</div><!-- /.kaiko-products-wrap -->

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

<?php
get_footer();
