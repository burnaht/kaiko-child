<?php
/**
 * Template Name: KAIKO Contact
 * Description: Redesigned Contact page for KAIKO Products
 */

get_header(); ?>

<style>
/* ── WoodMart Override: hide page title & break out of theme container ── */
body.page-template-template-contact .main-page-wrapper { padding-top: 0 !important; }
body.page-template-template-contact .page-title,
body.page-template-template-contact .wd-page-title,
body.page-template-template-contact .page-title-default { display: none !important; }
body.page-template-template-contact .site-content { padding: 0 !important; max-width: 100% !important; width: 100% !important; }
body.page-template-template-contact .site-content > .container,
body.page-template-template-contact .container,
body.page-template-template-contact .content-area,
body.page-template-template-contact .site-main { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; box-sizing: border-box !important; }
body.page-template-template-contact .entry-content { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
body.page-template-template-contact .website-wrapper { overflow-x: hidden; }
body.page-template-template-contact .main-page-wrapper .container { max-width: 100% !important; width: 100% !important; padding: 0 !important; }

/* ── Hide WoodMart header ── */
.whb-header { display: none !important; }

<?php include get_stylesheet_directory() . '/parts/nav-styles.php'; ?>

/* ── KAIKO Contact Page Styles ── */
.kaiko-contact * { box-sizing: border-box; }
.kaiko-contact {
  font-family: 'Inter', -apple-system, system-ui, 'Segoe UI', sans-serif;
  color: #2a2a2a;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  --teal: #1a5c52;
  --deep-teal: #134840;
  --lime: #b8d435;
  --gold: #c89b3c;
  --cream: #faf8f3;
  --light-gray: #f5f3f0;
  --dark-gray: #2a2a2a;
  --border: #e8e6e1;
}

/* Scroll reveal */
.kaiko-contact .reveal { opacity: 0; transform: translateY(30px); transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
.kaiko-contact .reveal.active { opacity: 1; transform: translateY(0); }

/* Hero */
.kaiko-contact .k-hero { background: var(--cream); padding: 5rem 2rem 4rem; text-align: center; }
.kaiko-contact .k-hero-label { display: inline-block; background: rgba(26,92,82,0.1); color: var(--teal); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem; }
.kaiko-contact .k-hero-heading { font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 700; margin-bottom: 1rem; line-height: 1.2; }
.kaiko-contact .k-hero-heading .highlight { color: var(--teal); }
.kaiko-contact .k-hero-subtitle { font-size: 1.1rem; color: #666; max-width: 600px; margin: 0 auto 3rem; line-height: 1.7; }
.kaiko-contact .k-hero-badges { display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2rem; }
.kaiko-contact .k-badge { display: flex; align-items: center; gap: 0.8rem; font-size: 0.95rem; font-weight: 500; }
.kaiko-contact .k-badge-dot { width: 8px; height: 8px; border-radius: 50%; }
.kaiko-contact .k-badge-dot.teal { background: var(--teal); }
.kaiko-contact .k-badge-dot.gold { background: var(--gold); }
.kaiko-contact .k-badge-dot.lime { background: var(--lime); }

/* Contact cards */
.kaiko-contact .k-contact-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; padding: 4rem 2rem; max-width: 1200px; margin: 0 auto; }
.kaiko-contact .k-card { background: white; border: 1px solid var(--border); border-radius: 12px; padding: 2rem; transition: all 0.3s ease; border-top: 4px solid var(--teal); }
.kaiko-contact .k-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: translateY(-2px); }
.kaiko-contact .k-card.lime { border-top-color: var(--lime); }
.kaiko-contact .k-card.gold { border-top-color: var(--gold); }
.kaiko-contact .k-card-icon { width: 48px; height: 48px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; }
.kaiko-contact .k-card-icon svg { width: 100%; height: 100%; stroke: var(--teal); fill: none; stroke-width: 1.5; }
.kaiko-contact .k-card.lime .k-card-icon svg { stroke: var(--lime); }
.kaiko-contact .k-card.gold .k-card-icon svg { stroke: var(--gold); }
.kaiko-contact .k-card-label { font-size: 0.8rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem; font-weight: 600; }
.kaiko-contact .k-card-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; }
.kaiko-contact .k-card-content { font-size: 0.95rem; color: #666; margin-bottom: 1.5rem; line-height: 1.6; }
.kaiko-contact .k-card-email { font-weight: 600; color: var(--teal); text-decoration: none; transition: color 0.2s ease; }
.kaiko-contact .k-card-email:hover { color: var(--deep-teal); }

/* Form section */
.kaiko-contact .k-form-section { padding: 4rem 2rem; max-width: 1200px; margin: 0 auto; }
.kaiko-contact .k-form-container { display: grid; grid-template-columns: 1fr 350px; gap: 3rem; }
.kaiko-contact .k-form { display: flex; flex-direction: column; gap: 1.5rem; }
.kaiko-contact .k-form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.kaiko-contact .k-form-group label { font-weight: 600; font-size: 0.95rem; color: var(--dark-gray); }
.kaiko-contact .k-form-group input,
.kaiko-contact .k-form-group select,
.kaiko-contact .k-form-group textarea { padding: 0.9rem 1.2rem; border: 1px solid var(--border); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.95rem; transition: all 0.2s ease; background: white; }
.kaiko-contact .k-form-group input:focus,
.kaiko-contact .k-form-group select:focus,
.kaiko-contact .k-form-group textarea:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(26,92,82,0.1); }
.kaiko-contact .k-form-group textarea { resize: vertical; min-height: 150px; }
.kaiko-contact .k-form-submit { background: var(--teal); color: white; border: none; padding: 0.9rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.2s ease; width: fit-content; }
.kaiko-contact .k-form-submit:hover { background: var(--deep-teal); transform: translateY(-2px); }

/* Sidebar */
.kaiko-contact .k-form-sidebar { display: flex; flex-direction: column; gap: 2rem; }
.kaiko-contact .k-sidebar-card { background: var(--light-gray); border-radius: 12px; padding: 1.5rem; }
.kaiko-contact .k-sidebar-card h3 { font-size: 1.1rem; margin-bottom: 1.5rem; }
.kaiko-contact .k-response-times { font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.8rem; }
.kaiko-contact .k-response-item { display: flex; justify-content: space-between; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(0,0,0,0.05); }
.kaiko-contact .k-response-item:last-child { border-bottom: none; }
.kaiko-contact .k-response-label { font-weight: 600; color: var(--dark-gray); }
.kaiko-contact .k-response-value { color: #999; }
.kaiko-contact .k-response-priority { color: var(--teal); font-weight: 600; }
.kaiko-contact .k-social-links { display: flex; gap: 0.8rem; margin-top: 1rem; }
.kaiko-contact .k-social-btn { width: 40px; height: 40px; border-radius: 50%; background: white; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; text-decoration: none; color: var(--teal); transition: all 0.2s ease; }
.kaiko-contact .k-social-btn:hover { background: var(--teal); color: white; border-color: var(--teal); }
.kaiko-contact .k-social-btn svg { width: 20px; height: 20px; fill: currentColor; }
.kaiko-contact .k-trade-callout { background: linear-gradient(135deg, rgba(26,92,82,0.08) 0%, rgba(184,212,53,0.08) 100%); border-left: 4px solid var(--teal); }
.kaiko-contact .k-trade-callout p { font-size: 0.9rem; color: #666; line-height: 1.6; margin-bottom: 1.5rem; }
.kaiko-contact .k-trade-btn { background: var(--teal); color: white; border: none; padding: 0.7rem 1.2rem; border-radius: 6px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s ease; width: 100%; text-align: center; display: block; text-decoration: none; }
.kaiko-contact .k-trade-btn:hover { background: var(--deep-teal); color: white; }

/* FAQ section */
.kaiko-contact .k-faq-section { padding: 4rem 2rem; background: var(--light-gray); }
.kaiko-contact .k-faq-container { max-width: 900px; margin: 0 auto; }
.kaiko-contact .k-faq-heading { font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 700; margin-bottom: 3rem; text-align: center; }
.kaiko-contact .k-faq-item { background: white; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 1rem; overflow: hidden; }
.kaiko-contact .k-faq-trigger { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; background: white; border: none; width: 100%; text-align: left; font-size: 1.1rem; font-weight: 600; color: var(--dark-gray); cursor: pointer; transition: all 0.2s ease; font-family: 'Inter', sans-serif; }
.kaiko-contact .k-faq-trigger:hover { background: var(--light-gray); }
.kaiko-contact .k-faq-trigger.active { background: rgba(26,92,82,0.05); color: var(--teal); }
.kaiko-contact .k-faq-icon { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; transition: transform 0.3s ease; flex-shrink: 0; }
.kaiko-contact .k-faq-trigger.active .k-faq-icon { transform: rotate(180deg); }
.kaiko-contact .k-faq-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
.kaiko-contact .k-faq-content.active { max-height: 500px; }
.kaiko-contact .k-faq-text { padding: 0 1.5rem 1.5rem; color: #666; line-height: 1.7; font-size: 0.95rem; }

/* Responsive */
@media (max-width: 1024px) {
  .kaiko-contact .k-form-container { grid-template-columns: 1fr; }
  .kaiko-contact .k-contact-cards { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
}
@media (max-width: 768px) {
  .kaiko-contact .k-hero { padding: 4rem 1rem 2rem; }
  .kaiko-contact .k-hero-heading { font-size: 1.8rem; }
  .kaiko-contact .k-hero-badges { flex-direction: column; gap: 1rem; }
  .kaiko-contact .k-contact-cards { gap: 1.5rem; padding: 2rem 1rem; }
  .kaiko-contact .k-form-section { padding: 2rem 1rem; }
  .kaiko-contact .k-faq-section { padding: 2rem 1rem; }
}
@media (max-width: 640px) {
  .kaiko-contact .k-hero-heading { font-size: 1.5rem; }
  .kaiko-contact .k-hero-subtitle { font-size: 1rem; }
  .kaiko-contact .k-card-title { font-size: 1.1rem; }
  .kaiko-contact .k-faq-trigger { font-size: 1rem; padding: 1.2rem; }
  .kaiko-contact .k-faq-text { padding: 0 1.2rem 1.2rem; }
}
</style>

<?php include get_stylesheet_directory() . '/parts/nav.php'; ?>

<div class="kaiko-contact">

<!-- Hero Section -->
<section class="k-hero">
  <div class="k-hero-label">We're Here to Help</div>
  <h1 class="k-hero-heading">Get in <span class="highlight">Touch</span> with Kaiko.</h1>
  <p class="k-hero-subtitle">Trade enquiries, product questions, wholesale applications — we respond fast and we're run by people who actually keep reptiles.</p>
  <div class="k-hero-badges">
    <div class="k-badge"><div class="k-badge-dot teal"></div><span>Replies within 24 hours</span></div>
    <div class="k-badge"><div class="k-badge-dot gold"></div><span>UK-based team</span></div>
    <div class="k-badge"><div class="k-badge-dot lime"></div><span>Trade accounts available</span></div>
  </div>
</section>

<!-- Contact Method Cards -->
<section class="k-contact-cards reveal">
  <div class="k-card reveal">
    <div class="k-card-icon">
      <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 6l10 7 10-7"/></svg>
    </div>
    <div class="k-card-label">General Enquiries</div>
    <h3 class="k-card-title">Send Us an Email</h3>
    <p class="k-card-content">Product questions, press, collaborations.</p>
    <a href="mailto:hello@kaikoproducts.com" class="k-card-email">hello@kaikoproducts.com</a>
  </div>

  <div class="k-card lime reveal">
    <div class="k-card-icon">
      <svg viewBox="0 0 24 24"><path d="M3 3h8v8H3zM13 3h8v8h-8zM3 13h8v8H3zM13 13h8v8h-8z"/></svg>
    </div>
    <div class="k-card-label">Trade &amp; Wholesale</div>
    <h3 class="k-card-title">Trade Enquiries</h3>
    <p class="k-card-content">Wholesale accounts, pricing, minimum orders, distribution.</p>
    <a href="mailto:trade@kaikoproducts.com" class="k-card-email">trade@kaikoproducts.com</a>
  </div>

  <div class="k-card gold reveal">
    <div class="k-card-icon">
      <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
    </div>
    <div class="k-card-label">Based In</div>
    <h3 class="k-card-title">United Kingdom</h3>
    <p class="k-card-content">We're a UK brand. All products designed and manufactured locally.</p>
  </div>
</section>

<!-- Form & Sidebar Section -->
<section class="k-form-section reveal">
  <div class="k-form-container">
    <form class="k-form" id="kaikoContactForm" onsubmit="event.preventDefault()">
      <div class="k-form-group">
        <label for="kaiko-name">Your Name</label>
        <input type="text" id="kaiko-name" name="name" required>
      </div>
      <div class="k-form-group">
        <label for="kaiko-email">Email Address</label>
        <input type="email" id="kaiko-email" name="email" required>
      </div>
      <div class="k-form-group">
        <label for="kaiko-subject">Subject</label>
        <select id="kaiko-subject" name="subject" required>
          <option value="">Select a subject</option>
          <option value="general">General Enquiry</option>
          <option value="trade">Trade/Wholesale</option>
          <option value="product">Product Question</option>
          <option value="press">Press/Media</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="k-form-group">
        <label for="kaiko-message">Your Message</label>
        <textarea id="kaiko-message" name="message" required></textarea>
      </div>
      <button type="submit" class="k-form-submit">Send Message</button>
    </form>

    <aside class="k-form-sidebar">
      <div class="k-sidebar-card">
        <h3>Response Times</h3>
        <div class="k-response-times">
          <div class="k-response-item"><span class="k-response-label">Monday–Friday</span><span class="k-response-value">Within 24hrs</span></div>
          <div class="k-response-item"><span class="k-response-label">Saturday</span><span class="k-response-value">Limited</span></div>
          <div class="k-response-item"><span class="k-response-label">Sunday</span><span class="k-response-value">Closed</span></div>
          <div class="k-response-item"><span class="k-response-label">Trade Enquiries</span><span class="k-response-priority">Priority &lt;12hrs</span></div>
        </div>
      </div>

      <div class="k-sidebar-card">
        <h3>Follow Us</h3>
        <div class="k-social-links">
          <a href="#" class="k-social-btn" title="Instagram">
            <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="4.5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
          </a>
          <a href="#" class="k-social-btn" title="Facebook">
            <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="currentColor"/></svg>
          </a>
        </div>
      </div>

      <div class="k-sidebar-card k-trade-callout">
        <h3>Wholesale Account?</h3>
        <p>Applying for a wholesale account? Use the form and select Trade &amp; Wholesale — or apply directly through our trade portal.</p>
        <a href="<?php echo esc_url( home_url('/my-account/') ); ?>" class="k-trade-btn">Apply for Trade</a>
      </div>
    </aside>
  </div>
</section>

<!-- FAQ Section -->
<section class="k-faq-section reveal">
  <div class="k-faq-container">
    <h2 class="k-faq-heading">Frequently Asked</h2>

    <div class="k-faq-item">
      <button class="k-faq-trigger" data-faq="0">
        <span>What are your response times?</span>
        <div class="k-faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
      </button>
      <div class="k-faq-content" data-faq="0">
        <p class="k-faq-text">We aim to reply within 24 hours on weekdays. Trade partners get priority and typically receive responses within 12 hours. During weekends, our response times may be longer, and we're closed on Sundays.</p>
      </div>
    </div>

    <div class="k-faq-item">
      <button class="k-faq-trigger" data-faq="1">
        <span>How do I apply for a trade account?</span>
        <div class="k-faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
      </button>
      <div class="k-faq-content" data-faq="1">
        <p class="k-faq-text">Fill out the contact form above and select "Trade/Wholesale" as your subject, or visit our trade portal directly to apply. Our trade team will review your application and get in touch within 12 hours to discuss your wholesale requirements.</p>
      </div>
    </div>

    <div class="k-faq-item">
      <button class="k-faq-trigger" data-faq="2">
        <span>Do you ship internationally?</span>
        <div class="k-faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
      </button>
      <div class="k-faq-content" data-faq="2">
        <p class="k-faq-text">Currently we ship within the UK only. However, international wholesale enquiries are welcome — please get in touch using the form above and select "Trade/Wholesale" to discuss potential distribution partnerships.</p>
      </div>
    </div>

    <div class="k-faq-item">
      <button class="k-faq-trigger" data-faq="3">
        <span>Can I visit your workshop?</span>
        <div class="k-faq-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
      </button>
      <div class="k-faq-content" data-faq="3">
        <p class="k-faq-text">We don't have a public showroom, but we're happy to arrange visits for trade partners and key accounts. If you're interested, mention this in your message and we'll discuss arrangements with you.</p>
      </div>
    </div>
  </div>
</section>

</div><!-- .kaiko-contact -->

<script>
(function() {
  // Scroll reveal
  var reveals = document.querySelectorAll('.kaiko-contact .reveal');
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  reveals.forEach(function(el) { observer.observe(el); });

  // FAQ accordion
  document.querySelectorAll('.kaiko-contact .k-faq-trigger').forEach(function(trigger) {
    trigger.addEventListener('click', function() {
      var faqId = this.getAttribute('data-faq');
      var content = document.querySelector('.kaiko-contact .k-faq-content[data-faq="' + faqId + '"]');
      var isActive = this.classList.contains('active');

      // Close all
      document.querySelectorAll('.kaiko-contact .k-faq-trigger').forEach(function(t) { t.classList.remove('active'); });
      document.querySelectorAll('.kaiko-contact .k-faq-content').forEach(function(c) { c.classList.remove('active'); });

      // Toggle current
      if (!isActive) {
        this.classList.add('active');
        content.classList.add('active');
      }
    });
  });
})();
</script>

<?php get_footer(); ?>
