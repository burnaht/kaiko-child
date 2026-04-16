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
  var isLoggedIn = document.cookie.indexOf('wordpress_logged_in') !== -1;
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
