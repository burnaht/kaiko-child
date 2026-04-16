/**
 * Kaiko Animations - Lightweight Scroll Animation Engine
 *
 * A dependency-free animation system featuring:
 * - IntersectionObserver-based reveal animations
 * - Parallax scrolling effects
 * - Navigation scroll detection
 * - Smooth scroll for anchors
 * - Custom cursor tracking (desktop)
 * - Loading screen handler
 * - Seamless marquee animation
 * - CountUp stats animation
 *
 * @version 1.0.0
 * @license GPL-2.0+
 */

(function() {
	'use strict';

	const Kaiko = {
		// Configuration
		config: {
			revealThreshold: 0.15,
			revealRootMargin: '-50px',
			parallaxSpeed: 0.3,
			navScrollTrigger: 80,
			loaderDuration: 1600,
			lerpFactor: 0.15,
			cursorExpandElements: 'a, button, .kaiko-btn-primary, .kaiko-btn-secondary, [role="button"]'
		},

		// State
		state: {
			mouseX: 0,
			mouseY: 0,
			scrollY: 0,
			isDesktop: true,
			cursorElement: null,
			parallaxElements: [],
			countUpElements: [],
			observer: null,
			isInitialized: false
		},

		/**
		 * Initialize all animation systems
		 */
		init() {
			if (this.state.isInitialized) return;

			this.detectDevice();
			this.setupRevealObserver();
			this.setupParallax();
			this.setupCountUp();
			this.setupNavScroll();
			this.setupSmoothScroll();
			this.setupCursor();
			this.setupLoader();
			this.setupMarquee();
			this.setupEventListeners();

			this.state.isInitialized = true;
		},

		/**
		 * Detect if device supports hover (desktop)
		 */
		detectDevice() {
			this.state.isDesktop = window.matchMedia('(hover: hover)').matches;
		},

		/**
		 * Setup IntersectionObserver for reveal animations
		 */
		setupRevealObserver() {
			const options = {
				threshold: this.config.revealThreshold,
				rootMargin: `0px 0px ${this.config.revealRootMargin} 0px`
			};

			this.state.observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						entry.target.classList.add('visible');
						this.state.observer.unobserve(entry.target);
					}
				});
			}, options);

			// Observe all reveal elements
			const revealSelectors = [
				'.kaiko-reveal',
				'.kaiko-reveal-left',
				'.kaiko-reveal-right',
				'.kaiko-reveal-scale'
			];

			revealSelectors.forEach(selector => {
				document.querySelectorAll(selector).forEach(el => {
					this.state.observer.observe(el);
				});
			});
		},

		/**
		 * Setup parallax scrolling
		 */
		setupParallax() {
			const parallaxElements = document.querySelectorAll('[data-kaiko-parallax]');

			parallaxElements.forEach(el => {
				const speed = parseFloat(el.getAttribute('data-kaiko-parallax')) || this.config.parallaxSpeed;
				this.state.parallaxElements.push({ el, speed });
			});
		},

		/**
		 * Update parallax on scroll
		 */
		updateParallax() {
			this.state.parallaxElements.forEach(item => {
				const offset = this.state.scrollY * item.speed;
				item.el.style.transform = `translateY(${offset}px)`;
			});
		},

		/**
		 * Setup CountUp animation for statistics
		 */
		setupCountUp() {
			const countUpElements = document.querySelectorAll('[data-countup]');

			if (countUpElements.length === 0) return;

			const countUpObserver = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting && !entry.target.dataset.counted) {
						this.animateCountUp(entry.target);
						entry.target.dataset.counted = 'true';
						countUpObserver.unobserve(entry.target);
					}
				});
			}, { threshold: 0.5 });

			countUpElements.forEach(el => {
				countUpObserver.observe(el);
			});
		},

		/**
		 * Animate number count up
		 */
		animateCountUp(element) {
			const target = parseInt(element.getAttribute('data-countup'), 10);
			const duration = parseInt(element.getAttribute('data-countup-duration') || 2000, 10);
			const startTime = performance.now();

			const animate = (currentTime) => {
				const elapsed = currentTime - startTime;
				const progress = Math.min(elapsed / duration, 1);
				const current = Math.floor(progress * target);

				element.textContent = current.toLocaleString();

				if (progress < 1) {
					requestAnimationFrame(animate);
				}
			};

			requestAnimationFrame(animate);
		},

		/**
		 * Setup navigation scroll detection
		 */
		setupNavScroll() {
			this.updateNavScroll();
		},

		/**
		 * Update nav scroll state
		 */
		updateNavScroll() {
			const body = document.body;
			if (this.state.scrollY > this.config.navScrollTrigger) {
				body.classList.add('kaiko-nav-scrolled');
			} else {
				body.classList.remove('kaiko-nav-scrolled');
			}
		},

		/**
		 * Setup smooth scroll for anchor links
		 */
		setupSmoothScroll() {
			document.addEventListener('click', (e) => {
				if (e.target.tagName === 'A' && e.target.getAttribute('href')?.startsWith('#')) {
					const href = e.target.getAttribute('href');
					const target = document.querySelector(href);

					if (target) {
						e.preventDefault();
						target.scrollIntoView({ behavior: 'smooth' });
					}
				}
			});
		},

		/**
		 * Setup custom cursor (desktop only)
		 */
		setupCursor() {
			if (!this.state.isDesktop) return;

			// Create cursor element
			const cursor = document.createElement('div');
			cursor.className = 'kaiko-cursor';
			cursor.innerHTML = '<div class="kaiko-cursor-inner"></div><div class="kaiko-cursor-outer"></div>';
			document.body.appendChild(cursor);
			this.state.cursorElement = cursor;

			// Track mouse movement
			document.addEventListener('mousemove', (e) => {
				this.state.mouseX = e.clientX;
				this.state.mouseY = e.clientY;
			});

			// Update cursor position on animation frame
			const updateCursor = () => {
				if (!this.state.cursorElement) return;

				const inner = this.state.cursorElement.querySelector('.kaiko-cursor-inner');
				const outer = this.state.cursorElement.querySelector('.kaiko-cursor-outer');

				// Smooth lerp to mouse position
				const lerpX = this.state.mouseX;
				const lerpY = this.state.mouseY;

				inner.style.transform = `translate(${lerpX}px, ${lerpY}px)`;
				outer.style.transform = `translate(${lerpX}px, ${lerpY}px)`;

				requestAnimationFrame(updateCursor);
			};

			updateCursor();

			// Expand cursor on interactive elements
			document.addEventListener('mouseenter', (e) => {
				if (e.target.matches(this.config.cursorExpandElements)) {
					this.state.cursorElement.classList.add('expanded');
				}
			}, true);

			document.addEventListener('mouseleave', (e) => {
				if (e.target.matches(this.config.cursorExpandElements)) {
					this.state.cursorElement.classList.remove('expanded');
				}
			}, true);
		},

		/**
		 * Setup loading screen animation
		 */
		setupLoader() {
			const loader = document.querySelector('.kaiko-loader');
			const pageContent = document.querySelector('.kaiko-page-content');

			if (!loader || !pageContent) return;

			setTimeout(() => {
				loader.style.opacity = '0';
				loader.style.pointerEvents = 'none';
				loader.style.transition = 'opacity 0.8s ease-out';
				pageContent.classList.add('kaiko-page-visible');
			}, this.config.loaderDuration);
		},

		/**
		 * Setup seamless marquee animation
		 */
		setupMarquee() {
			document.querySelectorAll('.kaiko-marquee').forEach(marquee => {
				const track = marquee.querySelector('.kaiko-marquee-track');

				if (!track) return;

				const items = track.querySelectorAll('.kaiko-marquee-item');

				if (items.length === 0) return;

				// Clone items for seamless loop
				items.forEach(item => {
					track.appendChild(item.cloneNode(true));
				});

				// Calculate animation duration based on content
				const trackWidth = track.scrollWidth;
				const animationDuration = (trackWidth / 100) * 40; // Adjust speed here

				track.style.animation = `kaiko-marquee ${animationDuration}s linear infinite`;
			});
		},

		/**
		 * Setup event listeners
		 */
		setupEventListeners() {
			// Scroll listener
			window.addEventListener('scroll', () => {
				this.state.scrollY = window.scrollY;
				this.updateParallax();
				this.updateNavScroll();
			}, { passive: true });

			// Handle window resize
			window.addEventListener('resize', () => {
				this.detectDevice();
			});

			// Mutation observer for dynamically added reveal elements
			const mutationObserver = new MutationObserver((mutations) => {
				mutations.forEach(mutation => {
					mutation.addedNodes.forEach(node => {
						if (node.nodeType === 1) { // Element node
							if (node.matches('[data-kaiko-parallax]')) {
								const speed = parseFloat(node.getAttribute('data-kaiko-parallax')) || this.config.parallaxSpeed;
								this.state.parallaxElements.push({ el: node, speed });
							}

							// Check children for reveal elements
							const revealSelectors = [
								'.kaiko-reveal',
								'.kaiko-reveal-left',
								'.kaiko-reveal-right',
								'.kaiko-reveal-scale'
							];

							revealSelectors.forEach(selector => {
								node.querySelectorAll?.(selector).forEach(el => {
									if (this.state.observer) {
										this.state.observer.observe(el);
									}
								});
							});
						}
					});
				});
			});

			mutationObserver.observe(document.body, {
				childList: true,
				subtree: true
			});
		},

		/**
		 * Destroy and cleanup
		 */
		destroy() {
			if (this.state.observer) {
				this.state.observer.disconnect();
			}
			if (this.state.cursorElement) {
				this.state.cursorElement.remove();
			}
			this.state.isInitialized = false;
		}
	};

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => Kaiko.init());
	} else {
		Kaiko.init();
	}

	// Export for external use
	window.KaikoAnimations = Kaiko;
})();
