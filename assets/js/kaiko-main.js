/**
 * Kaiko Main - WordPress/WooCommerce Integration
 *
 * jQuery-based plugin for:
 * - Mobile navigation toggle
 * - WooCommerce AJAX cart interactions
 * - Product image hover zoom
 * - Accordion/FAQ toggles
 * - Back to top button
 * - Contact form AJAX submission
 * - Newsletter form handling
 * - Lightbox gallery trigger
 * - Product tabs
 * - Sticky access bar
 *
 * @version 1.0.0
 * @requires jQuery
 * @license GPL-2.0+
 */

(function($) {
	'use strict';

	const KaikoMain = {
		/**
		 * Initialize all components
		 */
		init: function() {
			this.setupMobileNav();
			this.setupWoocommerceAjax();
			this.setupProductImageZoom();
			this.setupAccordion();
			this.setupBackToTop();
			this.setupContactForm();
			this.setupNewsletterForm();
			this.setupLightbox();
			this.setupProductTabs();
			this.setupAccessBar();
		},

		/**
		 * Mobile navigation toggle
		 */
		setupMobileNav: function() {
			const $toggle = $('.kaiko-nav-toggle');
			const $menu = $('.kaiko-nav-menu');

			$toggle.on('click', function(e) {
				e.preventDefault();
				$toggle.toggleClass('active');
				$menu.toggleClass('active');
			});

			// Close menu on menu item click
			$menu.find('a').on('click', function() {
				$toggle.removeClass('active');
				$menu.removeClass('active');
			});

			// Close menu on document click
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.kaiko-nav-toggle, .kaiko-nav-menu').length) {
					$toggle.removeClass('active');
					$menu.removeClass('active');
				}
			});
		},

		/**
		 * WooCommerce AJAX add to cart enhancement
		 */
		setupWoocommerceAjax: function() {
			if (typeof kaikoAjax === 'undefined') {
				return;
			}

			$(document).on('click', '.single_add_to_cart_button', function(e) {
				const $button = $(this);
				const $form = $button.closest('form.cart');

				if ($button.hasClass('disabled')) {
					return;
				}

				e.preventDefault();

				const productId = $form.find('[name="product_id"]').val();
				const quantity = $form.find('[name="quantity"]').val() || 1;
				const variation = $form.find('.variations_form').data('product_variations');

				// Show loading state
				$button.addClass('loading');
				$button.prop('disabled', true);
				const originalText = $button.text();
				$button.text('Adding...');

				// AJAX request
				$.ajax({
					type: 'POST',
					url: kaikoAjax.ajaxurl,
					data: {
						action: 'kaiko_add_to_cart',
						product_id: productId,
						quantity: quantity,
						nonce: kaikoAjax.nonce
					},
					success: function(response) {
						if (response.success) {
							$button.text('Added to cart!');
							$button.addClass('success');

							// Trigger WooCommerce events
							$(document.body).trigger('wc_fragment_refresh');
							$(document.body).trigger('added_to_cart');

							// Reset after delay
							setTimeout(function() {
								$button.removeClass('loading success');
								$button.prop('disabled', false);
								$button.text(originalText);
							}, 2000);
						} else {
							$button.text('Error adding to cart');
							$button.addClass('error');

							setTimeout(function() {
								$button.removeClass('loading error');
								$button.prop('disabled', false);
								$button.text(originalText);
							}, 2000);
						}
					},
					error: function() {
						$button.text('Error adding to cart');
						$button.addClass('error');

						setTimeout(function() {
							$button.removeClass('loading error');
							$button.prop('disabled', false);
							$button.text(originalText);
						}, 2000);
					}
				});
			});
		},

		/**
		 * Product image hover zoom effect
		 */
		setupProductImageZoom: function() {
			const $productImages = $('.kaiko-product-image');

			$productImages.each(function() {
				const $img = $(this).find('img');

				if (!$img.length) return;

				const originalWidth = $img.width();
				const originalHeight = $img.height();

				$(this).on('mousemove', function(e) {
					const rect = this.getBoundingClientRect();
					const x = e.clientX - rect.left;
					const y = e.clientY - rect.top;

					const xPercent = (x / rect.width) * 100;
					const yPercent = (y / rect.height) * 100;

					$img.css({
						'transform-origin': xPercent + '% ' + yPercent + '%',
						'transform': 'scale(1.1)'
					});
				});

				$(this).on('mouseleave', function() {
					$img.css({
						'transform': 'scale(1)',
						'transform-origin': '50% 50%'
					});
				});
			});
		},

		/**
		 * Accordion/FAQ toggle
		 */
		setupAccordion: function() {
			const $accordionTriggers = $('.kaiko-accordion-trigger');

			$accordionTriggers.on('click', function(e) {
				e.preventDefault();

				const $trigger = $(this);
				const $panel = $trigger.next('.kaiko-accordion-panel');
				const $accordion = $trigger.closest('.kaiko-accordion');

				// Close other panels in same accordion if not multi-open
				if (!$accordion.hasClass('kaiko-accordion-multi')) {
					$accordion.find('.kaiko-accordion-trigger.active').not($trigger).each(function() {
						const $otherPanel = $(this).next('.kaiko-accordion-panel');
						$(this).removeClass('active');
						$otherPanel.slideUp(300);
					});
				}

				// Toggle current panel
				$trigger.toggleClass('active');

				if ($trigger.hasClass('active')) {
					$panel.slideDown(300);
				} else {
					$panel.slideUp(300);
				}
			});
		},

		/**
		 * Back to top button
		 */
		setupBackToTop: function() {
			const $button = $('.kaiko-back-to-top');

			if (!$button.length) return;

			$(window).on('scroll', function() {
				if ($(this).scrollTop() > 600) {
					$button.addClass('visible');
				} else {
					$button.removeClass('visible');
				}
			});

			$button.on('click', function(e) {
				e.preventDefault();
				$('html, body').animate({ scrollTop: 0 }, 800);
			});
		},

		/**
		 * Contact form AJAX submission
		 */
		setupContactForm: function() {
			const $form = $('.kaiko-contact-form');

			if (!$form.length || typeof kaikoAjax === 'undefined') return;

			$form.on('submit', function(e) {
				e.preventDefault();

				const $submitBtn = $(this).find('button[type="submit"]');
				const originalText = $submitBtn.text();
				const $response = $(this).find('.kaiko-form-response');

				const formData = {
					action: 'kaiko_contact_form',
					nonce: kaikoAjax.nonce,
					name: $(this).find('[name="name"]').val(),
					email: $(this).find('[name="email"]').val(),
					subject: $(this).find('[name="subject"]').val(),
					message: $(this).find('[name="message"]').val()
				};

				$submitBtn.prop('disabled', true).text('Sending...');

				$.ajax({
					type: 'POST',
					url: kaikoAjax.ajaxurl,
					data: formData,
					success: function(response) {
						if (response.success) {
							$response.html('<div class="kaiko-message success">Message sent successfully!</div>').show();
							$form[0].reset();
						} else {
							$response.html('<div class="kaiko-message error">Error sending message. Please try again.</div>').show();
						}
					},
					error: function() {
						$response.html('<div class="kaiko-message error">An error occurred. Please try again later.</div>').show();
					},
					complete: function() {
						$submitBtn.prop('disabled', false).text(originalText);
					}
				});
			});
		},

		/**
		 * Newsletter form handler
		 */
		setupNewsletterForm: function() {
			const $form = $('.kaiko-newsletter-form');

			if (!$form.length || typeof kaikoAjax === 'undefined') return;

			$form.on('submit', function(e) {
				e.preventDefault();

				const $email = $(this).find('[name="email"]');
				const $submitBtn = $(this).find('button[type="submit"]');
				const $response = $(this).find('.kaiko-form-response');
				const originalText = $submitBtn.text();

				// Basic email validation
				if (!$email.val() || !this.checkValidity()) {
					$response.html('<div class="kaiko-message error">Please enter a valid email address.</div>').show();
					return;
				}

				$submitBtn.prop('disabled', true).text('Subscribing...');

				$.ajax({
					type: 'POST',
					url: kaikoAjax.ajaxurl,
					data: {
						action: 'kaiko_newsletter',
						nonce: kaikoAjax.nonce,
						email: $email.val()
					},
					success: function(response) {
						if (response.success) {
							$response.html('<div class="kaiko-message success">Thank you for subscribing!</div>').show();
							$form[0].reset();

							// Hide after delay
							setTimeout(function() {
								$response.fadeOut(400);
							}, 3000);
						} else {
							$response.html('<div class="kaiko-message error">' + (response.data || 'Subscription failed.') + '</div>').show();
						}
					},
					error: function() {
						$response.html('<div class="kaiko-message error">An error occurred. Please try again.</div>').show();
					},
					complete: function() {
						$submitBtn.prop('disabled', false).text(originalText);
					}
				});
			});
		},

		/**
		 * Lightbox trigger for product gallery
		 */
		setupLightbox: function() {
			$(document).on('click', '.kaiko-lightbox-trigger', function(e) {
				e.preventDefault();

				const src = $(this).attr('href') || $(this).data('src');
				const alt = $(this).attr('title') || 'Image';

				// Create lightbox if not exists
				if (!$('#kaiko-lightbox').length) {
					$('body').append(
						'<div id="kaiko-lightbox" class="kaiko-lightbox">' +
						'<span class="kaiko-lightbox-close">&times;</span>' +
						'<img class="kaiko-lightbox-image" src="" alt="" />' +
						'<span class="kaiko-lightbox-prev">&lsaquo;</span>' +
						'<span class="kaiko-lightbox-next">&rsaquo;</span>' +
						'</div>'
					);
				}

				const $lightbox = $('#kaiko-lightbox');
				$lightbox.find('.kaiko-lightbox-image').attr('src', src).attr('alt', alt);
				$lightbox.addClass('active');

				// Close on click
				$(document).on('click', '.kaiko-lightbox-close, #kaiko-lightbox', function(e) {
					if (e.target.id === 'kaiko-lightbox' || $(e.target).hasClass('kaiko-lightbox-close')) {
						$lightbox.removeClass('active');
					}
				});

				// Close on escape
				$(document).on('keydown', function(e) {
					if (e.key === 'Escape') {
						$lightbox.removeClass('active');
					}
				});
			});
		},

		/**
		 * Product tabs
		 */
		setupProductTabs: function() {
			const $tabs = $('.kaiko-product-tabs');

			if (!$tabs.length) return;

			$tabs.each(function() {
				const $tabList = $(this).find('.kaiko-product-tabs-list');
				const $tabPanels = $(this).find('.kaiko-product-tab-panel');

				$tabList.find('button').on('click', function(e) {
					e.preventDefault();

					const tabIndex = $(this).attr('data-tab-index');
					const $activePanel = $tabPanels.eq(tabIndex);

					// Update active states
					$tabList.find('button').removeClass('active');
					$(this).addClass('active');

					$tabPanels.removeClass('active').hide();
					$activePanel.addClass('active').fadeIn(200);
				});
			});
		},

		/**
		 * Sticky access bar for non-logged-in users
		 */
		setupAccessBar: function() {
			const $bar = $('.kaiko-access-bar');

			if (!$bar.length) return;

			// Only show for non-logged-in users (check for body class)
			if ($('body').hasClass('logged-in')) {
				$bar.remove();
				return;
			}

			const scrollTrigger = 800; // Show after scrolling past hero

			$(window).on('scroll', function() {
				if ($(this).scrollTop() > scrollTrigger) {
					$bar.addClass('visible');
				} else {
					$bar.removeClass('visible');
				}
			});

			// Close button
			$bar.find('.kaiko-access-bar-close').on('click', function(e) {
				e.preventDefault();
				$bar.removeClass('visible');
			});
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		KaikoMain.init();
		KaikoFilters.init();
	});

	// Export for external use
	window.KaikoMain = KaikoMain;


	/* ============================================
	   AJAX PRODUCT FILTERING
	   ============================================ */

	const KaikoFilters = {
		state: {
			category: '',
			species: '',
			difficulty: '',
			orderby: 'date',
			paged: 1,
			isLoading: false
		},

		init: function() {
			const $filterBar = $('#kaiko-filter-bar');
			if (!$filterBar.length) return;

			this.$grid = $('#kaiko-product-grid');
			this.$count = $('#kaiko-product-count');
			this.$reset = $('#kaiko-filter-reset');

			this.bindEvents();
		},

		bindEvents: function() {
			const self = this;

			$('#kaiko-filter-category').on('change', function() {
				self.state.category = $(this).val();
				self.state.paged = 1;
				self.fetch();
				self.toggleReset();
			});

			$('#kaiko-filter-species').on('change', function() {
				self.state.species = $(this).val();
				self.state.paged = 1;
				self.fetch();
				self.toggleReset();
			});

			$('#kaiko-filter-difficulty').on('change', function() {
				self.state.difficulty = $(this).val();
				self.state.paged = 1;
				self.fetch();
				self.toggleReset();
			});

			$('#kaiko-filter-sort').on('change', function() {
				self.state.orderby = $(this).val();
				self.state.paged = 1;
				self.fetch();
			});

			this.$reset.on('click', function() {
				self.resetFilters();
			});
		},

		toggleReset: function() {
			const hasFilters = this.state.category || this.state.species || this.state.difficulty;
			this.$reset.toggle(!!hasFilters);
		},

		resetFilters: function() {
			this.state.category = '';
			this.state.species = '';
			this.state.difficulty = '';
			this.state.orderby = 'date';
			this.state.paged = 1;

			$('#kaiko-filter-category, #kaiko-filter-species, #kaiko-filter-difficulty').val('');
			$('#kaiko-filter-sort').val('date');
			this.$reset.hide();
			this.fetch();
		},

		fetch: function() {
			if (this.state.isLoading) return;
			this.state.isLoading = true;

			const self = this;
			this.$grid.addClass('loading');

			$.ajax({
				url: kaikoData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'kaiko_filter_products',
					nonce: kaikoData.nonce,
					category: this.state.category,
					species: this.state.species,
					difficulty: this.state.difficulty,
					orderby: this.state.orderby,
					paged: this.state.paged,
					per_page: 12
				},
				success: function(response) {
					if (response.success) {
						// Replace grid content with animated fade
						self.$grid.css('opacity', 0);

						setTimeout(function() {
							// Replace the inner products list
							const $list = self.$grid.find('.products, ul');
							if ($list.length) {
								$list.html(response.data.html);
							} else {
								self.$grid.html('<ul class="products columns-3">' + response.data.html + '</ul>');
							}

							// Update count
							self.$count.text(response.data.found);

							// Fade back in
							self.$grid.css('opacity', 1);

							// Re-trigger reveal animations on new cards
							if (window.KaikoAnimations && window.KaikoAnimations.state.observer) {
								self.$grid.find('.kaiko-reveal, .kaiko-reveal-left, .kaiko-reveal-right, .kaiko-reveal-scale').each(function() {
									window.KaikoAnimations.state.observer.observe(this);
								});
							}
						}, 200);
					}
				},
				error: function() {
					console.warn('Kaiko: Filter request failed');
				},
				complete: function() {
					self.state.isLoading = false;
					self.$grid.removeClass('loading');
				}
			});
		}
	};

	window.KaikoFilters = KaikoFilters;

})(jQuery);
