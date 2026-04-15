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
    <?php if ( function_exists('WC') ) : ?>
      <?php $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
      <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="kaiko-nav-cart <?php echo $cart_count > 0 ? 'has-items' : ''; ?>" aria-label="Cart">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <?php if ( $cart_count > 0 ) : ?>
          <span class="kaiko-cart-count"><?php echo esc_html($cart_count); ?></span>
        <?php endif; ?>
      </a>
    <?php endif; ?>
    <a href="<?php echo esc_url(home_url('/my-account/')); ?>" class="kaiko-nav-cta">Trade Login</a>
  </div>
</nav>
