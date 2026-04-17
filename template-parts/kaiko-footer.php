<?php
/**
 * Kaiko — Shared Footer Partial
 *
 * Renders the dark Kaiko footer used across ALL pages.
 * Replaces WoodMart's default footer for visual consistency.
 *
 * Usage: get_template_part( 'template-parts/kaiko-footer' );
 *
 * @package KaikoChild
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

$shop_url    = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#';
$account_url = class_exists( 'WooCommerce' ) ? esc_url( wc_get_page_permalink( 'myaccount' ) ) : '#';
?>

<!-- KAIKO FOOTER -->
<footer class="kaiko-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <h3>KAIKO</h3>
            <p>Premium reptile and exotic pet supplies, designed by keepers for keepers. Handcrafted in the UK with species-specific precision.</p>
        </div>
        <div class="footer-col">
            <h4>Shop</h4>
            <ul>
                <li><a href="<?php echo $shop_url; ?>">All Products</a></li>
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
                <li><a href="<?php echo $account_url; ?>">Trade Account</a></li>
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
<script>
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
