/* ── KAIKO Nav ── */
.kaiko-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: rgba(255,255,255,0.97); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid #e4e2de; padding: 0 6rem; height: 72px; display: flex; align-items: center; justify-content: space-between; font-family: 'Inter', -apple-system, sans-serif; }
.kaiko-nav-logo { font-family: 'Gotham', 'Gotham Bold', -apple-system, sans-serif; font-size: 1.75rem; font-weight: 700; letter-spacing: -0.03em; color: #1a1a1a !important; text-decoration: none !important; }
.kaiko-nav-links { display: flex; gap: 3rem; align-items: center; }
.kaiko-nav-links a { font-family: 'Inter', -apple-system, sans-serif; font-size: 0.95rem; font-weight: 500; color: #57534E !important; text-decoration: none !important; transition: color 0.2s; }
.kaiko-nav-links a:hover { color: #1a1a1a !important; }
.kaiko-nav-cta { background: #1a5c52 !important; color: #fff !important; padding: 0.6rem 1.5rem; border-radius: 999px; font-size: 0.85rem !important; font-weight: 600 !important; letter-spacing: 0.03em; text-transform: uppercase; transition: background 0.2s; }
.kaiko-nav-cta:hover { background: #134840 !important; }

/* Cart icon */
.kaiko-nav-cart { position: relative; display: none; color: #57534E !important; text-decoration: none !important; padding: 4px; }
.kaiko-nav-cart.has-items { display: flex; align-items: center; }
.kaiko-nav-cart:hover { color: #1a1a1a !important; }
.kaiko-cart-count { position: absolute; top: -4px; right: -8px; background: #1a5c52; color: #fff; font-size: 0.65rem; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1; }

/* Hamburger */
.kaiko-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 8px; flex-direction: column; gap: 5px; }
.kaiko-hamburger span { display: block; width: 24px; height: 2px; background: #1a1a1a; transition: 0.3s; }
.kaiko-hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.kaiko-hamburger.active span:nth-child(2) { opacity: 0; }
.kaiko-hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* Mobile */
@media (max-width: 768px) {
  .kaiko-nav { padding: 0 1.5rem; }
  .kaiko-hamburger { display: flex; }
  .kaiko-nav-links { display: none; position: fixed; top: 72px; left: 0; right: 0; background: #fff; flex-direction: column; padding: 2rem; gap: 1.5rem; border-bottom: 1px solid #e4e2de; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
  .kaiko-nav-links.mobile-open { display: flex; }
  .kaiko-nav-cart.has-items { display: flex; }
  .kaiko-nav-cta { text-align: center; }
}
