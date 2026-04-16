/**
 * Kaiko — Elementor Page Interactivity
 *
 * Handles:
 *  1. Counter animation (brand stats)
 *  2. Testimonial slider
 *  3. Newsletter form AJAX
 *  4. Guide search filtering
 *  5. Pill filter toggle
 *  6. Featured product carousel (mobile)
 *
 * @package KaikoChild
 * @since   2.1.0
 */

(function () {
    'use strict';

    /* ============================================
       1. COUNTER ANIMATION
       Animates numbers from 0 to target on scroll.
       Uses IntersectionObserver to trigger.
       ============================================ */

    function initCounters() {
        const counters = document.querySelectorAll('[data-kaiko-countup]');
        if (!counters.length) return;

        // Respect reduced motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        if (el.dataset.kaikoCounted) return;
                        el.dataset.kaikoCounted = 'true';

                        const target = parseInt(el.dataset.kaikoCountup, 10);
                        const suffix = el.dataset.kaikoCountupSuffix || '';
                        const duration = prefersReducedMotion ? 0 : 2000;

                        if (duration === 0) {
                            el.textContent = formatNumber(target) + suffix;
                            return;
                        }

                        animateCounter(el, target, suffix, duration);
                        observer.unobserve(el);
                    }
                });
            },
            { threshold: 0.3 }
        );

        counters.forEach((el) => observer.observe(el));
    }

    function animateCounter(el, target, suffix, duration) {
        const start = performance.now();
        const easeOutExpo = (t) => (t === 1 ? 1 : 1 - Math.pow(2, -10 * t));

        function tick(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = easeOutExpo(progress);
            const current = Math.round(eased * target);

            el.textContent = formatNumber(current) + suffix;

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        }

        requestAnimationFrame(tick);
    }

    function formatNumber(n) {
        return n.toLocaleString('en-GB');
    }


    /* ============================================
       2. TESTIMONIAL SLIDER
       Auto-advancing with pause on hover.
       ============================================ */

    function initTestimonials() {
        const containers = document.querySelectorAll('[data-kaiko-testimonials]');
        containers.forEach(initTestimonialInstance);
    }

    function initTestimonialInstance(container) {
        const slides = container.querySelectorAll('.kaiko-testimonials__slide');
        const dots = container.querySelectorAll('.kaiko-testimonials__dot');
        if (slides.length < 2) return;

        const speed = parseInt(container.dataset.speed, 10) || 6000;
        let current = 0;
        let timer = null;
        let paused = false;

        function goTo(index) {
            slides[current].classList.remove('active');
            dots[current].classList.remove('active');

            current = index;
            if (current >= slides.length) current = 0;
            if (current < 0) current = slides.length - 1;

            slides[current].classList.add('active');
            dots[current].classList.add('active');

            // Adjust track height to active slide
            const track = container.querySelector('.kaiko-testimonials__track');
            if (track) {
                track.style.minHeight = slides[current].offsetHeight + 'px';
            }
        }

        function next() {
            goTo(current + 1);
        }

        function startAutoPlay() {
            stopAutoPlay();
            timer = setInterval(() => {
                if (!paused) next();
            }, speed);
        }

        function stopAutoPlay() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        // Dot clicks
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const target = parseInt(dot.dataset.slide, 10);
                goTo(target);
                startAutoPlay(); // Reset timer on manual navigation
            });
        });

        // Pause on hover
        container.addEventListener('mouseenter', () => { paused = true; });
        container.addEventListener('mouseleave', () => { paused = false; });

        // Touch/swipe support
        let touchStartX = 0;
        container.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });

        container.addEventListener('touchend', (e) => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                diff > 0 ? goTo(current + 1) : goTo(current - 1);
                startAutoPlay();
            }
        }, { passive: true });

        // Respect reduced motion
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            startAutoPlay();
        }

        // Set initial track height
        requestAnimationFrame(() => {
            const track = container.querySelector('.kaiko-testimonials__track');
            if (track && slides[0]) {
                track.style.minHeight = slides[0].offsetHeight + 'px';
            }
        });
    }


    /* ============================================
       3. NEWSLETTER FORM AJAX
       Handles form submission without page reload.
       ============================================ */

    function initNewsletterForms() {
        const forms = document.querySelectorAll('[data-kaiko-newsletter-form]');
        forms.forEach(initNewsletterForm);
    }

    function initNewsletterForm(form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const container = form.closest('[data-kaiko-newsletter]');
            const response = container
                ? container.querySelector('[data-kaiko-newsletter-response]')
                : null;
            const button = form.querySelector('button[type="submit"]');
            const emailInput = form.querySelector('input[type="email"]');

            if (!emailInput || !emailInput.value) return;

            // Disable button
            if (button) {
                button.disabled = true;
                button.textContent = 'Subscribing...';
            }

            try {
                const formData = new FormData();
                formData.append('action', 'kaiko_newsletter_subscribe');
                formData.append('email', emailInput.value);

                // Get nonce from form
                const nonceField = form.querySelector('[name="kaiko_newsletter_nonce"]');
                if (nonceField) {
                    formData.append('nonce', nonceField.value);
                }

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);

                const res = await fetch(
                    typeof kaikoData !== 'undefined' ? kaikoData.ajaxUrl : '/wp-admin/admin-ajax.php',
                    { method: 'POST', body: formData, signal: controller.signal }
                );
                clearTimeout(timeoutId);

                if (!res.ok) throw new Error('Server error');
                const data = await res.json();

                if (response) {
                    response.style.display = 'block';
                    response.className = 'kaiko-newsletter__response';

                    if (data.success) {
                        response.classList.add('success');
                        response.textContent = data.data.message || 'You\'re subscribed!';
                        emailInput.value = '';
                    } else {
                        response.classList.add('error');
                        response.textContent = data.data.message || 'Something went wrong. Please try again.';
                    }

                    // Auto-hide after 5 seconds
                    setTimeout(() => {
                        response.style.display = 'none';
                    }, 5000);
                }
            } catch (err) {
                if (response) {
                    response.style.display = 'block';
                    response.className = 'kaiko-newsletter__response error';
                    response.textContent = 'Connection error. Please try again.';
                }
            } finally {
                if (button) {
                    button.disabled = false;
                    button.textContent = 'Subscribe';
                }
            }
        });
    }


    /* ============================================
       4. GUIDE SEARCH FILTERING
       Client-side search on care guide cards.
       ============================================ */

    function initGuideSearch() {
        const searchInput = document.querySelector('[data-kaiko-guide-search]');
        if (!searchInput) return;

        let debounceTimer;

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const query = searchInput.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.kaiko-guide-card');

                cards.forEach((card) => {
                    const text = card.textContent.toLowerCase();
                    card.style.display = !query || text.includes(query) ? '' : 'none';
                });
            }, 300);
        });
    }


    /* ============================================
       5. PILL FILTER TOGGLE
       Handles pill/tab filtering for guide difficulty, etc.
       ============================================ */

    function initPillFilters() {
        const filterContainers = document.querySelectorAll('.kaiko-guide-filters');

        filterContainers.forEach((container) => {
            const pills = container.querySelectorAll('.kaiko-pill');

            pills.forEach((pill) => {
                pill.addEventListener('click', () => {
                    // Update active state
                    pills.forEach((p) => p.classList.remove('kaiko-pill--active'));
                    pill.classList.add('kaiko-pill--active');

                    const filter = pill.dataset.filter;
                    const cards = document.querySelectorAll('.kaiko-guide-card');

                    cards.forEach((card) => {
                        if (filter === 'all') {
                            card.style.display = '';
                        } else {
                            const difficulty = card.dataset.difficulty || '';
                            card.style.display = difficulty === filter ? '' : 'none';
                        }
                    });
                });
            });
        });
    }


    /* ============================================
       6. FEATURED PRODUCTS MOBILE CAROUSEL
       Converts grid to horizontal scroll on mobile.
       ============================================ */

    function initMobileCarousel() {
        const carousels = document.querySelectorAll('[data-kaiko-carousel]');
        if (!carousels.length) return;

        function update() {
            const isMobile = window.innerWidth <= 768;

            carousels.forEach((el) => {
                if (isMobile) {
                    el.style.display = 'flex';
                    el.style.overflowX = 'auto';
                    el.style.scrollSnapType = 'x mandatory';
                    el.style.webkitOverflowScrolling = 'touch';
                    el.style.gap = 'var(--kaiko-space-md)';
                    el.style.paddingBottom = 'var(--kaiko-space-sm)';

                    // Make children fixed width for carousel
                    Array.from(el.children).forEach((child) => {
                        child.style.minWidth = '280px';
                        child.style.scrollSnapAlign = 'start';
                        child.style.flexShrink = '0';
                    });
                } else {
                    // Reset to grid
                    el.style.display = '';
                    el.style.overflowX = '';
                    el.style.scrollSnapType = '';
                    el.style.webkitOverflowScrolling = '';
                    el.style.gap = '';
                    el.style.paddingBottom = '';

                    Array.from(el.children).forEach((child) => {
                        child.style.minWidth = '';
                        child.style.scrollSnapAlign = '';
                        child.style.flexShrink = '';
                    });
                }
            });
        }

        update();
        window.addEventListener('resize', debounce(update, 250));
    }


    /* ============================================
       UTILITY: Debounce
       ============================================ */

    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }


    /* ============================================
       INIT — Run on DOM ready
       ============================================ */

    function init() {
        initCounters();
        initTestimonials();
        initNewsletterForms();
        initGuideSearch();
        initPillFilters();
        initMobileCarousel();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init on Elementor frontend loaded (for preview mode)
    if (typeof jQuery !== 'undefined') {
        jQuery(window).on('elementor/frontend/init', function () {
            setTimeout(init, 500);
        });
    }
})();
