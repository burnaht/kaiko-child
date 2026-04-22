<?php
/**
 * Kaiko — Single Product Page
 *
 * Override of WooCommerce single-product.php.
 * Ports the "kaiko-product-preview" design:
 *   - Breadcrumb + sticky gallery + rich summary
 *   - Wholesale tier pricing block with live-highlight
 *   - Gated states (approved / pending / logged-out)
 *   - Tabs: Description / Specifications / Species / Shipping / Reviews
 *   - Related products grid
 *
 * All classes are scoped under .kaiko-product-page to avoid collisions
 * with Woodmart / the rest of the theme.
 *
 * @package KaikoChild
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) : the_post();

	global $product;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product instanceof WC_Product ) {
		echo '<div class="kaiko-pp-wrap"><p style="padding:64px 32px;">Product not found.</p></div>';
		continue;
	}

	// -------------------------------------------------------------
	// Gather data
	// -------------------------------------------------------------
	$product_id   = $product->get_id();
	$pid_img      = $product->get_image_id();
	$gallery_ids  = $product->get_gallery_image_ids();
	if ( $pid_img ) {
		array_unshift( $gallery_ids, $pid_img );
	}

	$rating_count = $product->get_rating_count();
	$average      = (float) $product->get_average_rating();

	// Primary category eyebrow
	$cat_terms = get_the_terms( $product_id, 'product_cat' );
	$cat_name  = '';
	if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
		$cat_name = $cat_terms[0]->name;
	}

	// Trade gating
	$can_purchase = function_exists( 'kaiko_user_can_see_prices' ) && kaiko_user_can_see_prices();
	$logged_in    = is_user_logged_in();

	// Wholesale tiers (ACF)
	$tiers = function_exists( 'kaiko_get_product_tiers' ) ? kaiko_get_product_tiers( $product_id ) : array();

	// ACF extras
	$species   = function_exists( 'get_field' ) ? get_field( 'compatible_species', $product_id ) : array();
	$dims      = function_exists( 'get_field' ) ? get_field( 'dimensions', $product_id ) : array();
	$power     = function_exists( 'get_field' ) ? get_field( 'power_requirements', $product_id ) : '';
	$material  = function_exists( 'get_field' ) ? get_field( 'material', $product_id ) : '';
	$weight_kg = function_exists( 'get_field' ) ? get_field( 'weight_kg', $product_id ) : '';
	$carton    = function_exists( 'get_field' ) ? get_field( 'carton_qty', $product_id ) : '';
	$lead_time = function_exists( 'get_field' ) ? get_field( 'lead_time', $product_id ) : '';

	// Badge (sale / featured)
	$badge = '';
	if ( $product->is_on_sale() ) {
		$badge = __( 'On Sale', 'kaiko-child' );
	} elseif ( $product->is_featured() ) {
		$badge = __( 'Best Seller', 'kaiko-child' );
	}

	// Upsells / related product IDs (up to 4)
	$upsell_ids = $product->get_upsell_ids();
	if ( empty( $upsell_ids ) ) {
		$upsell_ids = wc_get_related_products( $product_id, 4 );
	}
	$upsell_ids = array_slice( array_filter( (array) $upsell_ids ), 0, 4 );
	?>

	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'kaiko-pp-wrap', $product ); ?>>

		<!-- Breadcrumb -->
		<nav class="kaiko-pp-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'kaiko-child' ); ?>">
			<?php woocommerce_breadcrumb(
				array(
					'delimiter'   => '<span>/</span>',
					'wrap_before' => '',
					'wrap_after'  => '',
					'before'      => '',
					'after'       => '',
				)
			); ?>
		</nav>

		<!-- Main product -->
		<section class="kaiko-pp-main">

			<!-- Gallery -->
			<div class="kaiko-pp-gallery">
				<div class="kaiko-pp-gallery__main">
					<?php if ( $badge ) : ?>
						<span class="kaiko-pp-gallery__badge"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $gallery_ids ) ) :
						$main_id       = (int) $gallery_ids[0];
						$main          = wp_get_attachment_image_src( $main_id, 'large' );
						$main_full     = wp_get_attachment_image_src( $main_id, 'full' );
						$main_full_url = $main_full ? $main_full[0] : ( $main ? $main[0] : '' );
						if ( $main ) :
							?>
							<button type="button"
									class="kaiko-pp-gallery__main-trigger"
									data-kaiko-lightbox-trigger
									data-full="<?php echo esc_url( $main_full_url ); ?>"
									data-index="0"
									aria-label="<?php esc_attr_e( 'View full-size image', 'kaiko-child' ); ?>">
								<img id="kaiko-pp-main-img"
									 src="<?php echo esc_url( $main[0] ); ?>"
									 data-full="<?php echo esc_url( $main_full_url ); ?>"
									 alt="<?php echo esc_attr( $product->get_name() ); ?>"
									 width="<?php echo esc_attr( $main[1] ); ?>"
									 height="<?php echo esc_attr( $main[2] ); ?>">
								<span class="kaiko-pp-gallery__zoom-hint" aria-hidden="true">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
								</span>
							</button>
						<?php endif;
					else :
						echo wc_placeholder_img( 'large' );
					endif; ?>
				</div>
				<?php if ( count( $gallery_ids ) > 1 ) : ?>
					<div class="kaiko-pp-gallery__thumbs">
						<?php foreach ( $gallery_ids as $i => $gid ) :
							$thumb  = wp_get_attachment_image_src( $gid, 'thumbnail' );
							$large  = wp_get_attachment_image_src( $gid, 'large' );
							$fullsz = wp_get_attachment_image_src( $gid, 'full' );
							if ( ! $thumb || ! $large ) { continue; }
							$full_url = $fullsz ? $fullsz[0] : $large[0];
							?>
							<button type="button"
									class="kaiko-pp-gallery__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>"
									data-src="<?php echo esc_url( $large[0] ); ?>"
									data-full="<?php echo esc_url( $full_url ); ?>"
									data-index="<?php echo (int) $i; ?>"
									aria-label="<?php esc_attr_e( 'View image', 'kaiko-child' ); ?> <?php echo (int) ( $i + 1 ); ?>">
							<img src="<?php echo esc_url( $thumb[0] ); ?>" alt="" loading="lazy">
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Lightbox (fullscreen image viewer) -->
			<?php if ( ! empty( $gallery_ids ) ) : ?>
				<div class="kaiko-pp-lightbox" id="kaiko-pp-lightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Product image viewer', 'kaiko-child' ); ?>" hidden>
					<button type="button" class="kaiko-pp-lightbox__close" data-kaiko-lightbox-close aria-label="<?php esc_attr_e( 'Close image viewer', 'kaiko-child' ); ?>">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
					</button>
					<?php if ( count( $gallery_ids ) > 1 ) : ?>
						<button type="button" class="kaiko-pp-lightbox__nav kaiko-pp-lightbox__prev" data-kaiko-lightbox-prev aria-label="<?php esc_attr_e( 'Previous image', 'kaiko-child' ); ?>">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
						</button>
						<button type="button" class="kaiko-pp-lightbox__nav kaiko-pp-lightbox__next" data-kaiko-lightbox-next aria-label="<?php esc_attr_e( 'Next image', 'kaiko-child' ); ?>">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
						</button>
					<?php endif; ?>
					<figure class="kaiko-pp-lightbox__stage">
						<img class="kaiko-pp-lightbox__img" id="kaiko-pp-lightbox-img" src="" alt="">
						<figcaption class="kaiko-pp-lightbox__caption">
							<span class="kaiko-pp-lightbox__count" id="kaiko-pp-lightbox-count"></span>
						</figcaption>
					</figure>
				</div>
			<?php endif; ?>

			<!-- Summary -->
			<div class="kaiko-pp-summary">

				<?php if ( $cat_name ) : ?>
					<p class="kaiko-pp-eyebrow"><?php echo esc_html( $cat_name ); ?></p>
				<?php endif; ?>

				<h1 class="kaiko-pp-title"><?php the_title(); ?></h1>

				<?php if ( $rating_count > 0 ) : ?>
					<div class="kaiko-pp-rating">
						<span class="kaiko-pp-rating__stars" aria-hidden="true">
							<?php
							$full = floor( $average );
							for ( $i = 1; $i <= 5; $i++ ) {
								echo $i <= $full ? '&#9733;' : '&#9734;';
							}
							?>
						</span>
						<span class="kaiko-pp-rating__count">
							<strong><?php echo esc_html( number_format_i18n( $average, 1 ) ); ?></strong>
							&nbsp;·&nbsp;
							<?php
							printf(
								/* translators: %s: number of reviews */
								esc_html( _n( '%s trade review', '%s trade reviews', $rating_count, 'kaiko-child' ) ),
								esc_html( number_format_i18n( $rating_count ) )
							);
							?>
						</span>
					</div>
				<?php endif; ?>

				<?php if ( $product->get_short_description() ) : ?>
					<div class="kaiko-pp-pitch">
						<?php echo apply_filters( 'woocommerce_short_description', $product->get_short_description() ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $logged_in && ! $can_purchase ) : ?>
					<!-- PENDING NOTICE -->
					<div class="kaiko-pp-notice kaiko-pp-notice--pending">
						<h4><?php esc_html_e( 'Trade pricing unlocks once approved', 'kaiko-child' ); ?></h4>
						<p><?php esc_html_e( 'Your application is being reviewed — you\'ll have access to tier pricing across the full catalogue as soon as we approve you. Usually within 24 hours.', 'kaiko-child' ); ?></p>
						<a class="kaiko-pp-btn kaiko-pp-btn--primary kaiko-pp-btn--sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
							<?php esc_html_e( 'View Application Status', 'kaiko-child' ); ?>
						</a>
					</div>
				<?php elseif ( ! $logged_in ) : ?>
					<!-- LOGGED-OUT NOTICE -->
					<div class="kaiko-pp-notice kaiko-pp-notice--loggedout">
						<h4><?php esc_html_e( 'Log in for wholesale pricing', 'kaiko-child' ); ?></h4>
						<p><?php esc_html_e( 'Tier pricing and ordering is available to approved trade partners. Existing customer? Log in to see your rates. New to KAIKO? Apply in 60 seconds.', 'kaiko-child' ); ?></p>
						<div class="kaiko-pp-notice__actions">
							<a class="kaiko-pp-btn kaiko-pp-btn--primary kaiko-pp-btn--sm" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
								<?php esc_html_e( 'Trade Login', 'kaiko-child' ); ?>
							</a>
							<a class="kaiko-pp-btn kaiko-pp-btn--ghost kaiko-pp-btn--sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
								<?php esc_html_e( 'Apply for Access', 'kaiko-child' ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $can_purchase ) : ?>

					<?php if ( ! empty( $tiers ) ) : ?>
						<!-- Wholesale tier pricing -->
						<div class="kaiko-pp-tiers">
							<div class="kaiko-pp-tiers__header">
								<span class="kaiko-pp-tiers__title"><?php esc_html_e( 'Wholesale Tier Pricing', 'kaiko-child' ); ?></span>
								<span class="kaiko-pp-tiers__note"><?php esc_html_e( 'Live price updates as you change quantity', 'kaiko-child' ); ?></span>
							</div>
							<div class="kaiko-pp-tiers__table">
								<?php
								$highest_price = 0;
								foreach ( $tiers as $t ) {
									if ( (float) $t['unit_price'] > $highest_price ) {
										$highest_price = (float) $t['unit_price'];
									}
								}
								foreach ( $tiers as $t ) :
									$min        = (int) $t['min_qty'];
									$max        = isset( $t['max_qty'] ) ? (int) $t['max_qty'] : 0;
									$price      = (float) $t['unit_price'];
									$pct        = isset( $t['discount_pct'] ) ? (float) $t['discount_pct'] : 0;
									$is_default = ! empty( $t['is_default'] );
									$label      = $max > 0 ? $min . ' – ' . $max : $min . '+';
									$off        = ( $pct > 0 ) ? round( $pct ) . '% off' : '';
									?>
									<button type="button" class="kaiko-pp-tier"
										 data-min="<?php echo esc_attr( $min ); ?>"
										 data-max="<?php echo esc_attr( $max ); ?>"
										 data-price="<?php echo esc_attr( $price ); ?>"
										 data-discount-pct="<?php echo esc_attr( $pct ); ?>"
										 data-default-schedule="<?php echo $is_default ? '1' : '0'; ?>"
										 aria-label="<?php echo esc_attr( sprintf( __( 'Select tier: %1$s units at %2$s each', 'kaiko-child' ), $label, strip_tags( wc_price( $price ) ) ) ); ?>">
										<div class="kaiko-pp-tier__qty"><?php echo esc_html( $label ); ?></div>
										<div class="kaiko-pp-tier__price"><?php echo wp_kses_post( wc_price( $price ) ); ?></div>
										<div class="kaiko-pp-tier__unit">
											<?php esc_html_e( 'per unit', 'kaiko-child' ); ?><?php echo $off ? ' · ' . esc_html( $off ) : ''; ?>
										</div>
									</button>
								<?php endforeach; ?>
							</div>
						</div>

						<?php
						// Mix-and-match reassurance — only renders for variable products
						// with configured tiers. See inc/mix-and-match-pricing.php.
						if ( function_exists( 'kaiko_render_pdp_mix_and_match_note' ) ) {
							kaiko_render_pdp_mix_and_match_note( $product );
						}
						?>
					<?php endif; ?>

					<!-- Add-to-cart form -->
					<div class="kaiko-pp-form">
						<?php
						// Render WC's native add-to-cart form (handles simple + variable products).
						// kaiko_hide_add_to_cart_button() on wp hook removes the template for non-
						// approved users, but we only land here when the user IS approved so it's
						// safe to call the template function directly.
						if ( $product->is_purchasable() && $product->is_in_stock() ) {
							woocommerce_template_single_add_to_cart();
						} else {
							echo '<p class="kaiko-pp-oos">' . esc_html__( 'Currently out of stock — contact info@kaikoproducts.com for availability.', 'kaiko-child' ) . '</p>';
						}
						?>
					</div>

					<?php if ( ! empty( $tiers ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
						<div class="kaiko-pp-total" aria-live="polite">
							<span class="kaiko-pp-total__label">
								<?php esc_html_e( 'Your order total', 'kaiko-child' ); ?>
								<span class="kaiko-pp-total__save" hidden></span>
							</span>
							<span class="kaiko-pp-total__value" data-kaiko-total></span>
						</div>
					<?php endif; ?>

				<?php endif; ?>

				<!-- Trust strip -->
				<ul class="kaiko-pp-trust">
					<li class="kaiko-pp-trust__item">
						<span class="kaiko-pp-trust__icon" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
						</span>
						<span class="kaiko-pp-trust__text"><strong><?php esc_html_e( 'Free UK shipping', 'kaiko-child' ); ?></strong><?php esc_html_e( 'On trade orders over £150', 'kaiko-child' ); ?></span>
					</li>
					<li class="kaiko-pp-trust__item">
						<span class="kaiko-pp-trust__icon" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
						</span>
						<span class="kaiko-pp-trust__text"><strong><?php esc_html_e( 'Dispatched in 48h', 'kaiko-child' ); ?></strong><?php esc_html_e( 'From our Essex base', 'kaiko-child' ); ?></span>
					</li>
					<li class="kaiko-pp-trust__item">
						<span class="kaiko-pp-trust__icon" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
						</span>
						<span class="kaiko-pp-trust__text"><strong><?php esc_html_e( '30-day returns', 'kaiko-child' ); ?></strong><?php esc_html_e( 'Hassle-free if not right', 'kaiko-child' ); ?></span>
					</li>
				</ul>

				<!-- Meta grid -->
				<div class="kaiko-pp-meta">
					<?php if ( $product->get_sku() ) : ?>
						<div><strong><?php esc_html_e( 'SKU', 'kaiko-child' ); ?></strong> <?php echo esc_html( $product->get_sku() ); ?></div>
					<?php endif; ?>
					<?php if ( $carton ) : ?>
						<div><strong><?php esc_html_e( 'Carton qty', 'kaiko-child' ); ?></strong> <?php echo esc_html( $carton ); ?> <?php esc_html_e( 'units', 'kaiko-child' ); ?></div>
					<?php endif; ?>
					<div>
						<strong><?php esc_html_e( 'Stock', 'kaiko-child' ); ?></strong>
						<?php if ( $product->is_in_stock() ) : ?>
							<span style="color:#6b8116;">●</span>
							<?php esc_html_e( 'In stock', 'kaiko-child' );
							$sq = $product->get_stock_quantity();
							if ( $sq ) {
								echo ' · ' . esc_html( number_format_i18n( $sq ) ) . ' ' . esc_html__( 'units', 'kaiko-child' );
							}
							?>
						<?php else : ?>
							<span style="color:#b1443c;">●</span> <?php esc_html_e( 'Out of stock', 'kaiko-child' ); ?>
						<?php endif; ?>
					</div>
					<?php if ( $lead_time ) : ?>
						<div><strong><?php esc_html_e( 'Lead time', 'kaiko-child' ); ?></strong> <?php echo esc_html( $lead_time ); ?></div>
					<?php endif; ?>
				</div>

			</div>

		</section>

		<!-- Tabs -->
		<section class="kaiko-pp-info">
			<div class="kaiko-pp-tabs" role="tablist">
				<button type="button" class="kaiko-pp-tab is-active" data-tab="description" role="tab" aria-selected="true"><?php esc_html_e( 'Description', 'kaiko-child' ); ?></button>
				<?php if ( ! empty( $dims ) || $material || $weight_kg || $power || $carton ) : ?>
					<button type="button" class="kaiko-pp-tab" data-tab="specifications" role="tab" aria-selected="false"><?php esc_html_e( 'Specifications', 'kaiko-child' ); ?></button>
				<?php endif; ?>
				<?php if ( ! empty( $species ) ) : ?>
					<button type="button" class="kaiko-pp-tab" data-tab="species" role="tab" aria-selected="false"><?php esc_html_e( 'Species Compatibility', 'kaiko-child' ); ?></button>
				<?php endif; ?>
				<button type="button" class="kaiko-pp-tab" data-tab="shipping" role="tab" aria-selected="false"><?php esc_html_e( 'Shipping & Returns', 'kaiko-child' ); ?></button>
			</div>

			<!-- Description -->
			<div class="kaiko-pp-panel is-active" id="tab-description" role="tabpanel">
				<div class="kaiko-pp-desc-grid">
					<div>
						<?php if ( $product->get_short_description() ) : ?>
							<div class="kaiko-pp-desc-lede">
								<?php echo apply_filters( 'woocommerce_short_description', $product->get_short_description() ); ?>
							</div>
						<?php endif; ?>
						<div class="kaiko-pp-desc-body">
							<?php
							$content = apply_filters( 'the_content', $product->get_description() );
							echo $content ? $content : '<p>' . esc_html__( 'Product details coming soon.', 'kaiko-child' ) . '</p>';
							?>
						</div>
					</div>
					<aside class="kaiko-pp-glance">
						<h4><?php esc_html_e( 'At a glance', 'kaiko-child' ); ?></h4>
						<ul>
							<?php
							$glance = array();
							if ( $material )                      { $glance[] = esc_html( ucfirst( $material ) ); }
							if ( $weight_kg )                     { $glance[] = esc_html( $weight_kg . ' kg' ); }
							if ( ! empty( $dims['length'] ) )     { $glance[] = esc_html( "{$dims['length']} × {$dims['width']} × {$dims['height']} cm" ); }
							if ( $carton )                        { $glance[] = esc_html( $carton . ' ' . __( 'units per carton', 'kaiko-child' ) ); }
							if ( $lead_time )                     { $glance[] = esc_html( $lead_time ); }
							$glance[] = __( 'Designed &amp; fulfilled in Great Dunmow, Essex', 'kaiko-child' );
							foreach ( $glance as $line ) : ?>
								<li>
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
									<span><?php echo wp_kses_post( $line ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</aside>
				</div>
			</div>

			<!-- Specifications -->
			<?php if ( ! empty( $dims ) || $material || $weight_kg || $power || $carton ) : ?>
				<div class="kaiko-pp-panel" id="tab-specifications" role="tabpanel" hidden>
					<table class="kaiko-pp-specs">
						<?php if ( ! empty( $dims['length'] ) ) : ?>
							<tr><td><?php esc_html_e( 'Dimensions', 'kaiko-child' ); ?></td>
								<td><?php echo esc_html( "{$dims['length']} × {$dims['width']} × {$dims['height']} cm" ); ?></td></tr>
						<?php endif; ?>
						<?php if ( $weight_kg ) : ?>
							<tr><td><?php esc_html_e( 'Weight', 'kaiko-child' ); ?></td>
								<td><?php echo esc_html( $weight_kg . ' kg' ); ?></td></tr>
						<?php endif; ?>
						<?php if ( $material ) : ?>
							<tr><td><?php esc_html_e( 'Material', 'kaiko-child' ); ?></td>
								<td><?php echo esc_html( ucfirst( $material ) ); ?></td></tr>
						<?php endif; ?>
						<?php if ( $power ) : ?>
							<tr><td><?php esc_html_e( 'Power', 'kaiko-child' ); ?></td>
								<td><?php echo esc_html( $power ); ?></td></tr>
						<?php endif; ?>
						<?php if ( $product->get_sku() ) : ?>
							<tr><td><?php esc_html_e( 'SKU', 'kaiko-child' ); ?></td>
								<td><?php echo esc_html( $product->get_sku() ); ?></td></tr>
						<?php endif; ?>
						<?php if ( $carton ) : ?>
							<tr><td><?php esc_html_e( 'Carton quantity', 'kaiko-child' ); ?></td>
								<td><?php echo esc_html( $carton . ' ' . __( 'units per carton', 'kaiko-child' ) ); ?></td></tr>
						<?php endif; ?>
						<?php if ( $lead_time ) : ?>
							<tr><td><?php esc_html_e( 'Lead time', 'kaiko-child' ); ?></td>
								<td><?php echo esc_html( $lead_time ); ?></td></tr>
						<?php endif; ?>
					</table>
				</div>
			<?php endif; ?>

			<!-- Species -->
			<?php if ( ! empty( $species ) ) : ?>
				<div class="kaiko-pp-panel" id="tab-species" role="tabpanel" hidden>
					<div class="kaiko-pp-species-grid">
						<?php
						$level_label = array(
							'full'       => __( 'Full compatibility', 'kaiko-child' ),
							'partial'    => __( 'Partial compatibility', 'kaiko-child' ),
							'supervised' => __( 'Supervised use', 'kaiko-child' ),
						);
						foreach ( $species as $row ) :
							$level = isset( $row['compatibility_level'] ) ? sanitize_html_class( $row['compatibility_level'] ) : '';
							?>
							<div class="kaiko-pp-species">
								<?php if ( $level && isset( $level_label[ $level ] ) ) : ?>
									<span class="kaiko-pp-species__level kaiko-pp-species__level--<?php echo esc_attr( $level ); ?>">
										<?php echo esc_html( $level_label[ $level ] ); ?>
									</span>
								<?php endif; ?>
								<?php if ( ! empty( $row['species_name'] ) ) : ?>
									<h5><?php echo esc_html( $row['species_name'] ); ?></h5>
								<?php endif; ?>
								<?php if ( ! empty( $row['species_scientific'] ) ) : ?>
									<p class="kaiko-pp-species__sci"><?php echo esc_html( $row['species_scientific'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $row['compatibility_notes'] ) ) : ?>
									<p><?php echo esc_html( $row['compatibility_notes'] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Shipping -->
			<div class="kaiko-pp-panel" id="tab-shipping" role="tabpanel" hidden>
				<table class="kaiko-pp-specs">
					<tr><td><?php esc_html_e( 'UK shipping', 'kaiko-child' ); ?></td>
						<td><?php esc_html_e( 'Free on trade orders over £150 — tracked via DPD 24h. Orders under £150 are charged at a standard tracked rate calculated at checkout.', 'kaiko-child' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Dispatch', 'kaiko-child' ); ?></td>
						<td><?php esc_html_e( 'Same working day for stock items on orders placed before 2pm. If any item is unavailable or dispatch is delayed, we\'ll contact you directly with an updated timeline.', 'kaiko-child' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Europe', 'kaiko-child' ); ?></td>
						<td><?php echo wp_kses_post( __( 'Contact <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a> for a shipping quote — rates depend on destination and order weight.', 'kaiko-child' ) ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Rest of world', 'kaiko-child' ); ?></td>
						<td><?php echo wp_kses_post( __( 'Quoted per consignment — contact <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a> for rates.', 'kaiko-child' ) ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Returns', 'kaiko-child' ); ?></td>
						<td><?php esc_html_e( '30-day hassle-free returns on undamaged stock in original packaging.', 'kaiko-child' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Damaged goods', 'kaiko-child' ); ?></td>
						<td><?php echo wp_kses_post( __( 'Photograph on receipt and email <a href="mailto:info@kaikoproducts.com">info@kaikoproducts.com</a> within 48h — immediate replacement or credit.', 'kaiko-child' ) ); ?></td></tr>
				</table>
			</div>
		</section>

		<!-- Related -->
		<?php if ( ! empty( $upsell_ids ) ) : ?>
			<section class="kaiko-pp-related">
				<div class="kaiko-pp-related__inner">
					<p class="kaiko-pp-related__eyebrow"><?php esc_html_e( 'Pairs well with', 'kaiko-child' ); ?></p>
					<h2 class="kaiko-pp-related__heading"><?php esc_html_e( 'Complete the setup', 'kaiko-child' ); ?></h2>
					<div class="kaiko-pp-related__grid">
						<?php foreach ( $upsell_ids as $rid ) :
							$rp = wc_get_product( $rid );
							if ( ! $rp ) { continue; }
							$rp_cats = get_the_terms( $rid, 'product_cat' );
							$rp_cat  = ( $rp_cats && ! is_wp_error( $rp_cats ) ) ? $rp_cats[0]->name : '';
							?>
							<a class="kaiko-pp-tile" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
								<div class="kaiko-pp-tile__img">
									<?php echo $rp->get_image( 'woocommerce_thumbnail' ); ?>
								</div>
								<div class="kaiko-pp-tile__body">
									<?php if ( $rp_cat ) : ?>
										<p class="kaiko-pp-tile__cat"><?php echo esc_html( $rp_cat ); ?></p>
									<?php endif; ?>
									<h3 class="kaiko-pp-tile__name"><?php echo esc_html( $rp->get_name() ); ?></h3>
									<?php if ( $can_purchase ) : ?>
										<div class="kaiko-pp-tile__price">
											<span class="kaiko-pp-tile__from"><?php esc_html_e( 'from', 'kaiko-child' ); ?></span>
											<span class="kaiko-pp-tile__value"><?php echo wp_kses_post( wc_price( $rp->get_price() ) ); ?></span>
										</div>
									<?php else : ?>
										<div class="kaiko-pp-tile__price">
											<span class="kaiko-pp-tile__from"><?php esc_html_e( 'Trade login for pricing', 'kaiko-child' ); ?></span>
										</div>
									<?php endif; ?>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

	</div>

<?php endwhile; ?>

<style>
/* =========================================================
   KAIKO PRODUCT PAGE — scoped under body.kaiko-product-page
   All selectors prefixed .kaiko-pp-* to avoid theme bleed.
   ========================================================= */
body.kaiko-product-page {
	--k-teal:       #1a5c52;
	--k-deep-teal:  #134840;
	--k-lime:       #b8d435;
	--k-gold:       #c89b3c;
	--k-dark:       #1C1917;
	--k-charcoal:   #292524;
	--k-stone-700:  #44403C;
	--k-stone-500:  #78716C;
	--k-stone-400:  #A8A29E;
	--k-stone-300:  #D6D3D1;
	--k-stone-200:  #E7E5E4;
	--k-stone-100:  #F5F5F4;
	--k-stone-50:   #FAFAF9;
	--k-white:      #FFFFFF;
	--k-r-sm: 8px; --k-r-md: 12px; --k-r-lg: 18px; --k-r-xl: 24px;
	--k-shadow-sm: 0 2px 8px rgba(28,25,23,0.04);
	--k-shadow-md: 0 8px 32px rgba(28,25,23,0.06);
	--k-shadow-lg: 0 16px 48px rgba(28,25,23,0.08);
}
/* ---------------------------------------------------------
   LAYOUT RESET — neutralise Woodmart/WooCommerce parent styles.
   wc_product_class() adds .product .type-* classes to .kaiko-pp-wrap,
   and Woodmart targets .single-product div.product with flex/grid
   that would otherwise cram our breadcrumb/main/tabs/related onto one row.
   --------------------------------------------------------- */
body.kaiko-product-page .kaiko-main,
body.kaiko-product-page #kaiko-main {
	display: block !important;
	width: 100% !important;
	max-width: none !important;
	margin: 0 !important;
	/* Keep the 72px top offset so content clears the fixed .kaiko-nav.
	   Zero left/right/bottom so our own containers control inner padding. */
	padding: 72px 0 0 !important;
	float: none !important;
}
/* WP admin bar: 32px desktop, 46px mobile — push the Kaiko nav below it
   and add matching extra top padding on .kaiko-main so content still clears.
   Extra-specific selectors (html body.*) to beat any enqueued override. */
html body.kaiko-product-page.admin-bar .kaiko-nav,
html body.kaiko-product-page.admin-bar #kaiko-nav { top: 32px !important; }
html body.kaiko-product-page.admin-bar .kaiko-main,
html body.kaiko-product-page.admin-bar #kaiko-main {
	padding-top: calc(72px + 32px) !important;
}
@media screen and (max-width: 782px) {
	html body.kaiko-product-page.admin-bar .kaiko-nav,
	html body.kaiko-product-page.admin-bar #kaiko-nav { top: 46px !important; }
	html body.kaiko-product-page.admin-bar .kaiko-main,
	html body.kaiko-product-page.admin-bar #kaiko-main {
		padding-top: calc(72px + 46px) !important;
	}
}
/* Belt-and-braces: absolutely force breadcrumb to sit below the fixed nav,
   never clipping into it. Flex/grid parents can collapse the main's
   padding — this gives the breadcrumb its own guaranteed clearance. */
body.kaiko-product-page .kaiko-pp-wrap .kaiko-pp-breadcrumb {
	margin-top: 0 !important;
	position: relative !important;
	z-index: 1 !important;
}
body.kaiko-product-page .main-page-wrapper,
body.kaiko-product-page .site-content,
body.kaiko-product-page .content-area,
body.kaiko-product-page .site-main,
body.kaiko-product-page .container,
body.kaiko-product-page .row {
	display: block !important;
	width: 100% !important;
	max-width: none !important;
	padding: 0 !important;
	margin: 0 !important;
	float: none !important;
	flex: unset !important;
	grid-template-columns: unset !important;
}
body.kaiko-product-page .kaiko-pp-wrap {
	display: block !important;
	width: 100% !important;
	max-width: none !important;
	margin: 0 !important;
	padding: 0 !important;
	float: none !important;
	flex: unset !important;
	grid-template-columns: unset !important;
	font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	color: var(--k-dark);
}
body.kaiko-product-page .kaiko-pp-wrap > * {
	float: none !important;
	clear: both;
	width: auto !important;
	max-width: none !important;
	flex: unset !important;
}
/* Defeat any Woodmart rule that puts .product > .summary / .images / .woocommerce-tabs
   into a 50/50 column layout — our children don't use those classes but better safe. */
body.kaiko-product-page .kaiko-pp-wrap .summary,
body.kaiko-product-page .kaiko-pp-wrap .images,
body.kaiko-product-page .kaiko-pp-wrap .woocommerce-tabs {
	float: none !important;
	width: 100% !important;
	max-width: 100% !important;
	margin: 0 !important;
}
body.kaiko-product-page .kaiko-pp-wrap img { max-width: 100%; display: block; }

/* Breadcrumb */
body.kaiko-product-page .kaiko-pp-breadcrumb {
	max-width: 1400px; margin: 0 auto;
	padding: 24px 32px 0;
	font-size: 0.82rem; color: var(--k-stone-500);
}
body.kaiko-product-page .kaiko-pp-breadcrumb a { color: var(--k-stone-500); text-decoration: none; }
body.kaiko-product-page .kaiko-pp-breadcrumb a:hover { color: var(--k-teal); }
body.kaiko-product-page .kaiko-pp-breadcrumb span { margin: 0 8px; color: var(--k-stone-300); }

/* Main grid */
body.kaiko-product-page .kaiko-pp-main {
	max-width: 1400px; margin: 0 auto;
	padding: 32px 32px 64px;
	display: grid;
	grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
	gap: 64px;
	align-items: start;
}

/* Gallery */
body.kaiko-product-page .kaiko-pp-gallery { position: sticky; top: 100px; }
body.kaiko-product-page .kaiko-pp-gallery__main {
	aspect-ratio: 1/1;
	background: var(--k-stone-100);
	border-radius: var(--k-r-xl);
	overflow: hidden;
	position: relative;
	border: 1px solid var(--k-stone-200);
}
body.kaiko-product-page .kaiko-pp-gallery__main img {
	width: 100%; height: 100%; object-fit: cover;
	transition: transform 500ms ease;
}
body.kaiko-product-page .kaiko-pp-gallery__main:hover img { transform: scale(1.03); }
body.kaiko-product-page .kaiko-pp-gallery__badge {
	position: absolute; top: 20px; left: 20px;
	background: var(--k-white);
	padding: 8px 14px;
	border-radius: 100px;
	font-size: 0.72rem; font-weight: 600;
	letter-spacing: 0.14em; text-transform: uppercase;
	color: var(--k-teal);
	border: 1px solid rgba(26,92,82,0.15);
	box-shadow: var(--k-shadow-sm);
	z-index: 2;
}
body.kaiko-product-page .kaiko-pp-gallery__thumbs {
	display: grid;
	grid-template-columns: repeat(5, 1fr);
	gap: 10px;
	margin-top: 14px;
}
body.kaiko-product-page .kaiko-pp-gallery__thumb {
	aspect-ratio: 1/1;
	background: var(--k-stone-100);
	border-radius: var(--k-r-md);
	overflow: hidden;
	cursor: pointer;
	border: 2px solid transparent;
	padding: 0;
	transition: border-color 200ms ease;
}
body.kaiko-product-page .kaiko-pp-gallery__thumb.is-active { border-color: var(--k-teal); }
body.kaiko-product-page .kaiko-pp-gallery__thumb:hover     { border-color: var(--k-stone-400); }
body.kaiko-product-page .kaiko-pp-gallery__thumb img { width: 100%; height: 100%; object-fit: cover; }

/* Main image trigger (clickable to open fullscreen lightbox) */
body.kaiko-product-page .kaiko-pp-gallery__main-trigger {
	appearance: none;
	-webkit-appearance: none;
	background: transparent;
	border: 0;
	padding: 0;
	margin: 0;
	display: block;
	width: 100%;
	height: 100%;
	cursor: zoom-in;
	position: relative;
	font: inherit;
	color: inherit;
}
body.kaiko-product-page .kaiko-pp-gallery__main-trigger:focus-visible {
	outline: 2px solid var(--k-teal);
	outline-offset: 4px;
}
body.kaiko-product-page .kaiko-pp-gallery__zoom-hint {
	position: absolute;
	bottom: 14px; right: 14px;
	width: 40px; height: 40px;
	display: flex; align-items: center; justify-content: center;
	background: rgba(255,255,255,0.95);
	color: var(--k-teal);
	border-radius: 50%;
	box-shadow: var(--k-shadow-sm);
	opacity: 0;
	transform: translateY(6px);
	transition: opacity 200ms ease, transform 200ms ease;
	pointer-events: none;
	z-index: 2;
}
body.kaiko-product-page .kaiko-pp-gallery__main:hover .kaiko-pp-gallery__zoom-hint,
body.kaiko-product-page .kaiko-pp-gallery__main-trigger:focus-visible .kaiko-pp-gallery__zoom-hint {
	opacity: 1;
	transform: translateY(0);
}

/* Lightbox (fullscreen image viewer) */
body.kaiko-product-page .kaiko-pp-lightbox {
	position: fixed;
	inset: 0;
	z-index: 100000;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: clamp(16px, 5vw, 72px);
	background: rgba(12, 22, 20, 0.92);
	backdrop-filter: blur(8px);
	-webkit-backdrop-filter: blur(8px);
	opacity: 0;
	transition: opacity 180ms ease;
}
body.kaiko-product-page .kaiko-pp-lightbox[hidden] { display: none; }
body.kaiko-product-page .kaiko-pp-lightbox.is-open { opacity: 1; }
body.kaiko-product-page .kaiko-pp-lightbox__stage {
	position: relative;
	max-width: min(1400px, 100%);
	max-height: 100%;
	margin: 0;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 14px;
}
body.kaiko-product-page .kaiko-pp-lightbox__img {
	display: block;
	max-width: 100%;
	max-height: calc(100vh - 180px);
	width: auto;
	height: auto;
	object-fit: contain;
	border-radius: var(--k-r-md);
	box-shadow: 0 20px 60px rgba(0,0,0,0.4);
	user-select: none;
	-webkit-user-drag: none;
}
body.kaiko-product-page .kaiko-pp-lightbox__caption {
	color: rgba(255,255,255,0.72);
	font-size: 0.82rem;
	letter-spacing: 0.05em;
	text-align: center;
}
body.kaiko-product-page .kaiko-pp-lightbox__count { font-variant-numeric: tabular-nums; }
body.kaiko-product-page .kaiko-pp-lightbox__close,
body.kaiko-product-page .kaiko-pp-lightbox__nav {
	appearance: none;
	-webkit-appearance: none;
	background: rgba(255,255,255,0.12);
	border: 1px solid rgba(255,255,255,0.25);
	color: #fff;
	border-radius: 50%;
	width: 48px; height: 48px;
	display: flex; align-items: center; justify-content: center;
	cursor: pointer;
	padding: 0;
	transition: background 180ms ease, transform 180ms ease;
}
body.kaiko-product-page .kaiko-pp-lightbox__close:hover,
body.kaiko-product-page .kaiko-pp-lightbox__nav:hover {
	background: rgba(255,255,255,0.22);
	transform: scale(1.05);
}
body.kaiko-product-page .kaiko-pp-lightbox__close:focus-visible,
body.kaiko-product-page .kaiko-pp-lightbox__nav:focus-visible {
	outline: 2px solid #fff;
	outline-offset: 3px;
}
body.kaiko-product-page .kaiko-pp-lightbox__close {
	position: absolute;
	top: clamp(14px, 2.5vw, 28px);
	right: clamp(14px, 2.5vw, 28px);
	z-index: 2;
}
body.kaiko-product-page .kaiko-pp-lightbox__nav {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	width: 52px; height: 52px;
	z-index: 2;
}
body.kaiko-product-page .kaiko-pp-lightbox__nav:hover {
	transform: translateY(-50%) scale(1.05);
}
body.kaiko-product-page .kaiko-pp-lightbox__prev { left: clamp(14px, 3vw, 40px); }
body.kaiko-product-page .kaiko-pp-lightbox__next { right: clamp(14px, 3vw, 40px); }
@media (max-width: 640px) {
	body.kaiko-product-page .kaiko-pp-lightbox__nav { width: 44px; height: 44px; }
	body.kaiko-product-page .kaiko-pp-lightbox__img { max-height: calc(100vh - 140px); }
}
body.kaiko-pp-lightbox-open { overflow: hidden; }

/* Summary */
body.kaiko-product-page .kaiko-pp-summary { padding: 8px 0; }
body.kaiko-product-page .kaiko-pp-eyebrow {
	font-size: 0.72rem; font-weight: 600;
	letter-spacing: 0.2em; text-transform: uppercase;
	color: var(--k-teal);
	margin: 0 0 14px;
}
body.kaiko-product-page .kaiko-pp-title {
	font-family: 'Gotham','Space Grotesk','Inter',-apple-system,BlinkMacSystemFont,sans-serif;
	font-size: 2.5rem; font-weight: 700;
	line-height: 1.1; letter-spacing: -0.01em;
	margin: 0 0 18px; color: var(--k-dark);
}
body.kaiko-product-page .kaiko-pp-rating {
	display: flex; align-items: center; gap: 10px;
	margin-bottom: 20px;
}
body.kaiko-product-page .kaiko-pp-rating__stars { color: var(--k-gold); letter-spacing: 2px; font-size: 1.1rem; }
body.kaiko-product-page .kaiko-pp-rating__count { font-size: 0.85rem; color: var(--k-stone-500); }
body.kaiko-product-page .kaiko-pp-rating__count strong { color: var(--k-dark); font-weight: 600; }
body.kaiko-product-page .kaiko-pp-pitch {
	font-size: 1.05rem; line-height: 1.65;
	color: var(--k-stone-700); margin: 0 0 28px;
}
body.kaiko-product-page .kaiko-pp-pitch p:last-child { margin-bottom: 0; }

/* Notices (pending / logged-out) */
body.kaiko-product-page .kaiko-pp-notice {
	background: linear-gradient(180deg, rgba(26,92,82,0.04) 0%, rgba(184,212,53,0.05) 100%);
	border: 1px solid rgba(26,92,82,0.15);
	border-radius: var(--k-r-lg);
	padding: 24px 26px;
	margin-bottom: 28px;
}
body.kaiko-product-page .kaiko-pp-notice h4 {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.25rem; font-weight: 600;
	margin: 0 0 8px; color: var(--k-dark);
}
body.kaiko-product-page .kaiko-pp-notice p {
	font-size: 0.92rem; line-height: 1.6;
	color: var(--k-stone-700); margin: 0 0 16px;
}
body.kaiko-product-page .kaiko-pp-notice p:last-child { margin-bottom: 0; }
body.kaiko-product-page .kaiko-pp-notice__actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* Buttons */
body.kaiko-product-page .kaiko-pp-btn {
	display: inline-flex; align-items: center; justify-content: center;
	gap: 10px;
	border: 0; border-radius: var(--k-r-md);
	padding: 0 24px; height: 48px;
	font-size: 0.85rem; font-weight: 600;
	letter-spacing: 0.08em; text-transform: uppercase;
	text-decoration: none;
	cursor: pointer; transition: all 200ms ease;
	font-family: inherit;
}
body.kaiko-product-page .kaiko-pp-btn--sm { height: 40px; padding: 0 18px; font-size: 0.78rem; }
body.kaiko-product-page .kaiko-pp-btn--primary { background: var(--k-teal); color: var(--k-white); }
body.kaiko-product-page .kaiko-pp-btn--primary:hover { background: var(--k-deep-teal); color: var(--k-white); }
body.kaiko-product-page .kaiko-pp-btn--ghost {
	background: transparent; color: var(--k-dark);
	border: 1.5px solid var(--k-stone-300);
}
body.kaiko-product-page .kaiko-pp-btn--ghost:hover { border-color: var(--k-dark); }

/* =========================================================
   Variable product — pill selector (replaces WC dropdowns)
   WC still renders its <select> underneath; we hide it, and
   our JS pills set select.value + dispatch 'change' so all of
   WooCommerce's variation JS still works.
   ========================================================= */
body.kaiko-product-page .kaiko-pp-summary table.variations,
body.kaiko-product-page .kaiko-pp-summary table.variations_table,
body.kaiko-product-page .kaiko-pp-summary .reset_variations {
	display: none !important;
}
/* Woodmart often wraps its select in .wd-select-wrapper — hide that too if it appears */
body.kaiko-product-page .kaiko-pp-summary .wd-select-wrapper,
body.kaiko-product-page .kaiko-pp-summary .variations select {
	display: none !important;
}
/* WC variation status blocks — we render our own price via tiers */
body.kaiko-product-page .kaiko-pp-summary .single_variation_wrap .woocommerce-variation-price,
body.kaiko-product-page .kaiko-pp-summary .single_variation_wrap .woocommerce-variation-availability,
body.kaiko-product-page .kaiko-pp-summary .single_variation_wrap .woocommerce-variation-description {
	display: none !important;
}

body.kaiko-product-page .kaiko-pp-variant-group {
	margin: 0 0 22px;
}
body.kaiko-product-page .kaiko-pp-variant-head {
	display: flex; align-items: baseline; justify-content: space-between;
	gap: 12px; flex-wrap: wrap;
	margin: 0 0 12px;
}
body.kaiko-product-page .kaiko-pp-variant-label {
	font-size: 0.8rem; font-weight: 600;
	letter-spacing: 0.14em; text-transform: uppercase;
	color: var(--k-stone-700);
}
body.kaiko-product-page .kaiko-pp-variant-current {
	font-size: 0.88rem;
	color: var(--k-teal);
	font-weight: 500;
}
body.kaiko-product-page .kaiko-pp-variant-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
	gap: 10px;
}
body.kaiko-product-page .kaiko-pp-variant-pill {
	background: var(--k-white);
	border: 1.5px solid var(--k-stone-200);
	border-radius: var(--k-r-md);
	padding: 14px 16px;
	cursor: pointer;
	transition: border-color 200ms ease, background 200ms ease, box-shadow 200ms ease;
	text-align: center;
	font-family: inherit;
	color: var(--k-dark);
	line-height: 1.2;
}
body.kaiko-product-page .kaiko-pp-variant-pill:hover {
	border-color: var(--k-stone-400);
}
body.kaiko-product-page .kaiko-pp-variant-pill.is-active {
	border-color: var(--k-teal);
	background: rgba(26,92,82,0.04);
	box-shadow: 0 0 0 2px rgba(26,92,82,0.10);
}
body.kaiko-product-page .kaiko-pp-variant-pill__label {
	display: block;
	font-weight: 700; font-size: 1.05rem;
	color: var(--k-dark);
	margin-bottom: 2px;
}
body.kaiko-product-page .kaiko-pp-variant-pill.is-active .kaiko-pp-variant-pill__label {
	color: var(--k-teal);
}
body.kaiko-product-page .kaiko-pp-variant-pill__sub {
	display: block;
	font-size: 0.82rem;
	color: var(--k-stone-500);
}
body.kaiko-product-page .kaiko-pp-variant-pill[disabled],
body.kaiko-product-page .kaiko-pp-variant-pill.is-disabled {
	opacity: 0.35;
	cursor: not-allowed;
	pointer-events: none;
}

/* Tier pricing */
body.kaiko-product-page .kaiko-pp-tiers {
	background: var(--k-stone-50);
	border: 1px solid var(--k-stone-200);
	border-radius: var(--k-r-lg);
	padding: 24px;
	margin-bottom: 28px;
}
body.kaiko-product-page .kaiko-pp-tiers__header {
	display: flex; justify-content: space-between; align-items: baseline;
	margin-bottom: 16px; gap: 12px; flex-wrap: wrap;
}
body.kaiko-product-page .kaiko-pp-tiers__title {
	font-size: 0.8rem; font-weight: 600;
	letter-spacing: 0.14em; text-transform: uppercase;
	color: var(--k-teal);
}
body.kaiko-product-page .kaiko-pp-tiers__note { font-size: 0.78rem; color: var(--k-stone-500); }
body.kaiko-product-page .kaiko-pp-tiers__table {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
	gap: 8px;
}
body.kaiko-product-page .kaiko-pp-tier {
	background: var(--k-white);
	border: 1.5px solid var(--k-stone-200);
	border-radius: var(--k-r-md);
	padding: 14px 12px;
	text-align: center;
	transition: all 200ms ease;
	/* Act as a clickable button — reset browser defaults */
	cursor: pointer;
	display: block;
	width: 100%;
	font: inherit;
	color: inherit;
	-webkit-appearance: none;
	appearance: none;
	outline: none;
}
body.kaiko-product-page .kaiko-pp-tier:hover {
	border-color: var(--k-teal);
	background: rgba(26,92,82,0.02);
	transform: translateY(-1px);
}
body.kaiko-product-page .kaiko-pp-tier:focus-visible {
	outline: 2px solid var(--k-teal);
	outline-offset: 2px;
}
body.kaiko-product-page .kaiko-pp-tier__qty {
	font-size: 0.74rem; font-weight: 600;
	letter-spacing: 0.08em; text-transform: uppercase;
	color: var(--k-stone-500);
	margin-bottom: 4px;
}
body.kaiko-product-page .kaiko-pp-tier__price {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.35rem; font-weight: 600;
	color: var(--k-dark); line-height: 1.1;
	margin-bottom: 2px;
}
body.kaiko-product-page .kaiko-pp-tier__price .woocommerce-Price-amount,
body.kaiko-product-page .kaiko-pp-tier__price bdi { font: inherit; color: inherit; }
body.kaiko-product-page .kaiko-pp-tier__unit { font-size: 0.72rem; color: var(--k-stone-500); }
body.kaiko-product-page .kaiko-pp-tier.is-active {
	border-color: var(--k-teal);
	background: rgba(26,92,82,0.04);
	box-shadow: 0 2px 12px rgba(26,92,82,0.08);
}
body.kaiko-product-page .kaiko-pp-tier.is-active .kaiko-pp-tier__price { color: var(--k-teal); }

/* Mix-and-match reassurance — sits under the tier card on variable products. */
body.kaiko-product-page .kaiko-pdp__mix-and-match-note {
	margin: -12px 0 28px;
	padding: 10px 14px;
	border-radius: var(--k-r-md);
	background: rgba(26,92,82,0.06);
	color: var(--k-teal);
	font-size: 0.82rem;
	line-height: 1.5;
}

/* Native WC add-to-cart form styling */
body.kaiko-product-page .kaiko-pp-form { margin-bottom: 20px; }
body.kaiko-product-page .kaiko-pp-form form.cart {
	display: grid;
	grid-template-columns: auto 1fr;
	gap: 12px;
	align-items: stretch;
	margin: 0;
}
body.kaiko-product-page .kaiko-pp-form form.variations_form.cart { grid-template-columns: 1fr; }
body.kaiko-product-page .kaiko-pp-form form.cart .quantity {
	display: inline-flex; align-items: center;
	background: var(--k-white);
	border: 1.5px solid var(--k-stone-300);
	border-radius: var(--k-r-md);
	overflow: hidden;
	height: 56px;
	margin: 0;
}
body.kaiko-product-page .kaiko-pp-form form.cart .quantity input.qty {
	width: 80px; height: 100%;
	border: 0; outline: 0;
	text-align: center;
	font-family: inherit;
	font-size: 1.05rem; font-weight: 600;
	color: var(--k-dark); background: transparent;
	-moz-appearance: textfield;
	padding: 0;
}
body.kaiko-product-page .kaiko-pp-form form.cart .quantity input.qty::-webkit-outer-spin-button,
body.kaiko-product-page .kaiko-pp-form form.cart .quantity input.qty::-webkit-inner-spin-button {
	-webkit-appearance: none; margin: 0;
}
body.kaiko-product-page .kaiko-pp-form form.cart button[type="submit"],
body.kaiko-product-page .kaiko-pp-form form.cart .single_add_to_cart_button {
	background: var(--k-teal) !important; color: var(--k-white) !important;
	border: 0 !important; border-radius: var(--k-r-md) !important;
	padding: 0 32px !important;
	height: 56px !important;
	font-size: 0.88rem !important; font-weight: 600 !important;
	letter-spacing: 0.1em !important; text-transform: uppercase !important;
	transition: all 200ms ease !important;
	font-family: inherit !important;
	cursor: pointer;
	min-width: 200px;
}
body.kaiko-product-page .kaiko-pp-form form.cart button[type="submit"]:hover,
body.kaiko-product-page .kaiko-pp-form form.cart .single_add_to_cart_button:hover {
	background: var(--k-deep-teal) !important;
	transform: translateY(-1px);
	box-shadow: 0 8px 24px rgba(19,72,64,0.25);
}
/* Variation selectors */
body.kaiko-product-page .kaiko-pp-form .variations { margin-bottom: 12px; width: 100%; }
body.kaiko-product-page .kaiko-pp-form .variations th,
body.kaiko-product-page .kaiko-pp-form .variations td { padding: 6px 0; vertical-align: middle; }
body.kaiko-product-page .kaiko-pp-form .variations select {
	padding: 10px 12px; border: 1.5px solid var(--k-stone-300);
	border-radius: var(--k-r-md); font-family: inherit; font-size: 0.92rem;
	background: var(--k-white);
}
body.kaiko-product-page .kaiko-pp-form .variations_form .woocommerce-variation-add-to-cart {
	display: grid; grid-template-columns: auto 1fr; gap: 12px; align-items: stretch;
}
body.kaiko-product-page .kaiko-pp-oos {
	padding: 18px; background: var(--k-stone-50);
	border: 1px solid var(--k-stone-200);
	border-radius: var(--k-r-md);
	color: var(--k-stone-700); font-size: 0.92rem;
}

/* Order total */
body.kaiko-product-page .kaiko-pp-total {
	display: flex; justify-content: space-between; align-items: center;
	padding: 14px 0;
	margin: -4px 0 24px;
	font-size: 0.92rem;
	border-top: 1px dashed var(--k-stone-200);
}
body.kaiko-product-page .kaiko-pp-total__label { color: var(--k-stone-500); }
body.kaiko-product-page .kaiko-pp-total__save {
	font-size: 0.78rem;
	background: rgba(184,212,53,0.15);
	color: var(--k-teal);
	padding: 3px 10px;
	border-radius: 100px;
	margin-left: 10px;
	font-weight: 600;
}
body.kaiko-product-page .kaiko-pp-total__value {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.35rem; font-weight: 600; color: var(--k-dark);
}

/* Trust strip */
body.kaiko-product-page .kaiko-pp-trust {
	display: grid; grid-template-columns: repeat(3, 1fr);
	gap: 16px; padding: 22px 0;
	border-top: 1px solid var(--k-stone-200);
	border-bottom: 1px solid var(--k-stone-200);
	list-style: none; margin: 0;
}
body.kaiko-product-page .kaiko-pp-trust__item {
	display: flex; align-items: center; gap: 12px;
}
body.kaiko-product-page .kaiko-pp-trust__icon {
	width: 36px; height: 36px; flex-shrink: 0;
	border-radius: 50%;
	background: rgba(26,92,82,0.08);
	color: var(--k-teal);
	display: flex; align-items: center; justify-content: center;
}
body.kaiko-product-page .kaiko-pp-trust__text {
	font-size: 0.82rem; color: var(--k-stone-700); line-height: 1.4;
}
body.kaiko-product-page .kaiko-pp-trust__text strong {
	display: block; color: var(--k-dark); font-weight: 600;
}

/* Meta grid */
body.kaiko-product-page .kaiko-pp-meta {
	display: grid; grid-template-columns: 1fr 1fr;
	gap: 8px 24px;
	margin: 24px 0 0;
	font-size: 0.82rem; color: var(--k-stone-500);
}
body.kaiko-product-page .kaiko-pp-meta strong {
	color: var(--k-dark); font-weight: 500;
	display: inline-block; margin-right: 4px;
}

/* Tabs section */
body.kaiko-product-page .kaiko-pp-info {
	max-width: 1400px; margin: 0 auto;
	padding: 32px 32px 96px;
}
body.kaiko-product-page .kaiko-pp-tabs {
	display: flex; gap: 6px;
	border-bottom: 1px solid var(--k-stone-200);
	margin-bottom: 48px;
	overflow-x: auto;
	/* Hide scrollbar (still scrollable on mobile via touch) */
	scrollbar-width: none;
	-ms-overflow-style: none;
}
body.kaiko-product-page .kaiko-pp-tabs::-webkit-scrollbar {
	display: none;
	width: 0;
	height: 0;
}
body.kaiko-product-page .kaiko-pp-tab {
	padding: 16px 24px;
	background: transparent; border: 0;
	border-bottom: 2px solid transparent;
	font-size: 0.88rem; font-weight: 500;
	color: var(--k-stone-500);
	letter-spacing: 0.04em;
	transition: all 200ms ease;
	margin-bottom: -1px;
	white-space: nowrap;
	cursor: pointer;
	font-family: inherit;
}
body.kaiko-product-page .kaiko-pp-tab:hover { color: var(--k-dark); }
body.kaiko-product-page .kaiko-pp-tab.is-active {
	color: var(--k-teal);
	border-bottom-color: var(--k-teal);
	font-weight: 600;
}
body.kaiko-product-page .kaiko-pp-panel { display: none; }
body.kaiko-product-page .kaiko-pp-panel.is-active { display: block; }

/* Description tab */
body.kaiko-product-page .kaiko-pp-desc-grid {
	display: grid; grid-template-columns: 2fr 1fr;
	gap: 64px; align-items: start;
}
body.kaiko-product-page .kaiko-pp-desc-lede {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.5rem; font-weight: 500;
	line-height: 1.4; color: var(--k-dark);
	margin: 0 0 28px;
}
body.kaiko-product-page .kaiko-pp-desc-lede p { margin: 0; }
body.kaiko-product-page .kaiko-pp-desc-body p {
	font-size: 1rem; line-height: 1.75;
	color: var(--k-stone-700); margin: 0 0 18px;
}
body.kaiko-product-page .kaiko-pp-glance {
	background: var(--k-stone-50);
	border-radius: var(--k-r-lg);
	padding: 28px;
	border: 1px solid var(--k-stone-200);
}
body.kaiko-product-page .kaiko-pp-glance h4 {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.25rem; font-weight: 600;
	margin: 0 0 16px; color: var(--k-dark);
}
body.kaiko-product-page .kaiko-pp-glance ul { list-style: none; padding: 0; margin: 0; }
body.kaiko-product-page .kaiko-pp-glance li {
	display: flex; align-items: flex-start; gap: 10px;
	padding: 10px 0;
	border-bottom: 1px solid var(--k-stone-200);
	font-size: 0.92rem; color: var(--k-stone-700);
}
body.kaiko-product-page .kaiko-pp-glance li:last-child { border-bottom: 0; }
body.kaiko-product-page .kaiko-pp-glance li svg {
	flex-shrink: 0; color: var(--k-teal); margin-top: 3px;
}

/* Specifications table */
body.kaiko-product-page .kaiko-pp-specs {
	width: 100%; border-collapse: collapse;
}
body.kaiko-product-page .kaiko-pp-specs tr { border-bottom: 1px solid var(--k-stone-200); }
body.kaiko-product-page .kaiko-pp-specs tr:last-child { border-bottom: 0; }
body.kaiko-product-page .kaiko-pp-specs td {
	padding: 18px 0; font-size: 0.98rem; vertical-align: top;
}
body.kaiko-product-page .kaiko-pp-specs td:first-child {
	width: 220px;
	font-size: 0.78rem; font-weight: 600;
	letter-spacing: 0.12em; text-transform: uppercase;
	color: var(--k-stone-500); padding-right: 20px;
}
body.kaiko-product-page .kaiko-pp-specs td:last-child {
	color: var(--k-dark); font-weight: 500;
}

/* Species cards */
body.kaiko-product-page .kaiko-pp-species-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
	gap: 16px;
}
body.kaiko-product-page .kaiko-pp-species {
	padding: 22px;
	background: var(--k-white);
	border: 1px solid var(--k-stone-200);
	border-radius: var(--k-r-lg);
	transition: all 200ms ease;
}
body.kaiko-product-page .kaiko-pp-species:hover {
	border-color: var(--k-teal);
	transform: translateY(-2px);
	box-shadow: var(--k-shadow-md);
}
body.kaiko-product-page .kaiko-pp-species__level {
	display: inline-block;
	font-size: 0.68rem; font-weight: 600;
	letter-spacing: 0.12em; text-transform: uppercase;
	padding: 4px 10px; border-radius: 100px;
	margin-bottom: 12px;
}
body.kaiko-product-page .kaiko-pp-species__level--full {
	background: rgba(184,212,53,0.2); color: #6b8116;
}
body.kaiko-product-page .kaiko-pp-species__level--partial {
	background: rgba(200,155,60,0.15); color: #8b6823;
}
body.kaiko-product-page .kaiko-pp-species__level--supervised {
	background: rgba(120,113,108,0.12); color: var(--k-stone-700);
}
body.kaiko-product-page .kaiko-pp-species h5 {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.25rem; font-weight: 600;
	margin: 0 0 4px; color: var(--k-dark);
}
body.kaiko-product-page .kaiko-pp-species__sci {
	font-size: 0.82rem; font-style: italic;
	color: var(--k-stone-500); margin: 0 0 12px;
}
body.kaiko-product-page .kaiko-pp-species p {
	font-size: 0.88rem; line-height: 1.55;
	color: var(--k-stone-700); margin: 0;
}

/* Related */
body.kaiko-product-page .kaiko-pp-related {
	background: var(--k-stone-50);
	padding: 80px 32px;
}
body.kaiko-product-page .kaiko-pp-related__inner { max-width: 1400px; margin: 0 auto; }
body.kaiko-product-page .kaiko-pp-related__eyebrow {
	font-size: 0.72rem; font-weight: 600;
	letter-spacing: 0.2em; text-transform: uppercase;
	color: var(--k-teal);
	margin: 0 0 10px;
	text-align: center;
}
body.kaiko-product-page .kaiko-pp-related__heading {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 2.5rem; font-weight: 500;
	text-align: center;
	margin: 0 0 48px; color: var(--k-dark);
}
body.kaiko-product-page .kaiko-pp-related__grid {
	display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;
}
body.kaiko-product-page .kaiko-pp-tile {
	background: var(--k-white);
	border-radius: var(--k-r-lg);
	overflow: hidden;
	border: 1px solid var(--k-stone-200);
	transition: all 300ms ease;
	text-decoration: none;
	color: inherit;
	display: block;
}
body.kaiko-product-page .kaiko-pp-tile:hover {
	border-color: var(--k-teal);
	transform: translateY(-4px);
	box-shadow: var(--k-shadow-lg);
}
body.kaiko-product-page .kaiko-pp-tile__img {
	aspect-ratio: 1/1; background: var(--k-stone-100); overflow: hidden;
}
body.kaiko-product-page .kaiko-pp-tile__img img {
	width: 100%; height: 100%; object-fit: cover;
	transition: transform 500ms ease;
}
body.kaiko-product-page .kaiko-pp-tile:hover .kaiko-pp-tile__img img { transform: scale(1.06); }
body.kaiko-product-page .kaiko-pp-tile__body { padding: 20px; }
body.kaiko-product-page .kaiko-pp-tile__cat {
	font-size: 0.7rem; font-weight: 600;
	letter-spacing: 0.14em; text-transform: uppercase;
	color: var(--k-teal);
	margin: 0 0 6px;
}
body.kaiko-product-page .kaiko-pp-tile__name {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.25rem; font-weight: 600;
	margin: 0 0 6px; color: var(--k-dark);
	line-height: 1.2;
}
body.kaiko-product-page .kaiko-pp-tile__price {
	display: flex; align-items: baseline; gap: 8px;
	margin-top: 12px;
}
body.kaiko-product-page .kaiko-pp-tile__from { font-size: 0.78rem; color: var(--k-stone-500); }
body.kaiko-product-page .kaiko-pp-tile__value {
	font-family: 'Gotham','Space Grotesk','Inter',sans-serif;
	font-size: 1.2rem; font-weight: 600;
	color: var(--k-dark);
}

/* Responsive */
@media (max-width: 1100px) {
	body.kaiko-product-page .kaiko-pp-main { grid-template-columns: 1fr; gap: 48px; }
	body.kaiko-product-page .kaiko-pp-gallery { position: static; }
	body.kaiko-product-page .kaiko-pp-desc-grid { grid-template-columns: 1fr; gap: 32px; }
	body.kaiko-product-page .kaiko-pp-related__grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 720px) {
	body.kaiko-product-page .kaiko-pp-main { padding: 20px 20px 48px; }
	body.kaiko-product-page .kaiko-pp-title { font-size: 2.1rem; }
	body.kaiko-product-page .kaiko-pp-tiers__table { grid-template-columns: repeat(2, 1fr); }
	body.kaiko-product-page .kaiko-pp-form form.cart { grid-template-columns: 1fr; }
	body.kaiko-product-page .kaiko-pp-form form.cart .quantity { width: 100%; justify-content: center; }
	body.kaiko-product-page .kaiko-pp-form form.cart .single_add_to_cart_button { width: 100%; }
	body.kaiko-product-page .kaiko-pp-trust { grid-template-columns: 1fr; gap: 14px; }
	body.kaiko-product-page .kaiko-pp-info { padding: 24px 20px 64px; }
	body.kaiko-product-page .kaiko-pp-specs td:first-child { width: 40%; }
	body.kaiko-product-page .kaiko-pp-related__grid { grid-template-columns: 1fr; }
	body.kaiko-product-page .kaiko-pp-related { padding: 56px 20px; }
	body.kaiko-product-page .kaiko-pp-related__heading { font-size: 1.8rem; }
}
</style>

<script>
(function () {
	if (!document.body.classList.contains('kaiko-product-page')) return;

	// ---- Gallery thumbs + fullscreen lightbox ----
	var thumbs       = Array.prototype.slice.call(document.querySelectorAll('.kaiko-pp-gallery__thumb'));
	var mainImg      = document.getElementById('kaiko-pp-main-img');
	var mainTrigger  = document.querySelector('.kaiko-pp-gallery__main-trigger');
	var lightbox     = document.getElementById('kaiko-pp-lightbox');
	var lightboxImg  = document.getElementById('kaiko-pp-lightbox-img');
	var lightboxCount = document.getElementById('kaiko-pp-lightbox-count');
	var lightboxClose = lightbox ? lightbox.querySelector('[data-kaiko-lightbox-close]') : null;
	var lightboxPrev  = lightbox ? lightbox.querySelector('[data-kaiko-lightbox-prev]')  : null;
	var lightboxNext  = lightbox ? lightbox.querySelector('[data-kaiko-lightbox-next]')  : null;
	var prevFocus    = null;

	// Build slides from thumbs (preferred) or from the main image alone.
	var slides = thumbs.length
		? thumbs.map(function (t) {
			return {
				full: t.dataset.full || t.dataset.src || '',
				large: t.dataset.src || t.dataset.full || '',
				alt: (t.querySelector('img') && t.querySelector('img').alt) || ''
			};
		})
		: (mainImg ? [{ full: mainImg.dataset.full || mainImg.src, large: mainImg.src, alt: mainImg.alt || '' }] : []);

	var activeIndex = 0;

	function syncMainFromIndex(i) {
		if (!mainImg) return;
		var slide = slides[i];
		if (!slide) return;
		mainImg.src = slide.large;
		mainImg.dataset.full = slide.full;
		if (mainTrigger) mainTrigger.dataset.full = slide.full;
		thumbs.forEach(function (x, n) { x.classList.toggle('is-active', n === i); });
		activeIndex = i;
	}

	function updateLightboxImage() {
		var slide = slides[activeIndex];
		if (!slide || !lightboxImg) return;
		lightboxImg.src = slide.full;
		lightboxImg.alt = slide.alt;
		if (lightboxCount) {
			lightboxCount.textContent = slides.length > 1
				? (activeIndex + 1) + ' / ' + slides.length
				: '';
		}
	}

	function openLightbox() {
		if (!lightbox) return;
		prevFocus = document.activeElement;
		updateLightboxImage();
		lightbox.hidden = false;
		// Force reflow so the transition fires.
		void lightbox.offsetWidth;
		lightbox.classList.add('is-open');
		document.body.classList.add('kaiko-pp-lightbox-open');
		if (lightboxClose) lightboxClose.focus();
	}

	function closeLightbox() {
		if (!lightbox) return;
		lightbox.classList.remove('is-open');
		document.body.classList.remove('kaiko-pp-lightbox-open');
		// Hide after transition.
		setTimeout(function () { lightbox.hidden = true; }, 200);
		if (prevFocus && typeof prevFocus.focus === 'function') prevFocus.focus();
	}

	function lightboxStep(dir) {
		if (!slides.length) return;
		activeIndex = (activeIndex + dir + slides.length) % slides.length;
		syncMainFromIndex(activeIndex);
		updateLightboxImage();
	}

	// Thumb click — swap main image, update active state, update lightbox index.
	thumbs.forEach(function (t, i) {
		t.addEventListener('click', function () {
			syncMainFromIndex(i);
		});
	});

	// Main image click — open lightbox.
	if (mainTrigger) {
		mainTrigger.addEventListener('click', function () {
			openLightbox();
		});
	}

	// Lightbox controls.
	if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
	if (lightboxPrev)  lightboxPrev.addEventListener('click', function () { lightboxStep(-1); });
	if (lightboxNext)  lightboxNext.addEventListener('click', function () { lightboxStep(1); });

	// Backdrop click closes the lightbox.
	if (lightbox) {
		lightbox.addEventListener('click', function (e) {
			if (e.target === lightbox) closeLightbox();
		});
	}

	// Keyboard — ESC closes, arrow keys navigate.
	document.addEventListener('keydown', function (e) {
		if (!lightbox || lightbox.hidden) return;
		if (e.key === 'Escape') { e.preventDefault(); closeLightbox(); }
		else if (e.key === 'ArrowLeft')  { e.preventDefault(); lightboxStep(-1); }
		else if (e.key === 'ArrowRight') { e.preventDefault(); lightboxStep(1); }
	});

	// ---- Tabs ----
	var tabs = document.querySelectorAll('.kaiko-pp-tab');
	var panels = document.querySelectorAll('.kaiko-pp-panel');
	tabs.forEach(function (b) {
		b.addEventListener('click', function () {
			tabs.forEach(function (x) { x.classList.remove('is-active'); x.setAttribute('aria-selected', 'false'); });
			panels.forEach(function (x) { x.classList.remove('is-active'); x.hidden = true; });
			b.classList.add('is-active');
			b.setAttribute('aria-selected', 'true');
			var p = document.getElementById('tab-' + b.dataset.tab);
			if (p) { p.classList.add('is-active'); p.hidden = false; }
		});
	});

	// ---- Tier highlight + running total ----
	var tiersRoot = document.querySelector('.kaiko-pp-tiers__table');
	var totalValue = document.querySelector('[data-kaiko-total]');
	var saveEl = document.querySelector('.kaiko-pp-total__save');
	var qtyInput = document.querySelector('form.cart input.qty');
	var atcBtn = document.querySelector('form.cart .single_add_to_cart_button');
	var atcBaseLabel = atcBtn ? (atcBtn.getAttribute('data-kaiko-label') || atcBtn.textContent.trim() || 'Add to cart') : 'Add to cart';
	if (atcBtn && !atcBtn.getAttribute('data-kaiko-label')) {
		atcBtn.setAttribute('data-kaiko-label', atcBaseLabel);
	}

	function formatPrice(n) {
		return '£' + n.toFixed(2);
	}

	function activeTier(qty) {
		if (!tiersRoot) return null;
		var cells = tiersRoot.querySelectorAll('.kaiko-pp-tier');
		var match = null;
		cells.forEach(function (c) {
			var min = parseInt(c.dataset.min, 10) || 1;
			var max = parseInt(c.dataset.max, 10) || 0;
			if (qty >= min && (max === 0 || qty <= max)) match = c;
		});
		if (!match && cells.length) match = cells[cells.length - 1];
		return match;
	}

	function setAtcLabel(total) {
		if (!atcBtn) return;
		if (isFinite(total) && total > 0) {
			atcBtn.textContent = atcBaseLabel.toUpperCase() + ' — ' + formatPrice(total);
		} else {
			atcBtn.textContent = atcBaseLabel;
		}
	}

	function refresh() {
		if (!tiersRoot) {
			// No tiers table (simple/non-tiered product) — still try to reflect qty × price on ATC.
			if (qtyInput && atcBtn) {
				var simpleQ = Math.max(1, parseInt(qtyInput.value, 10) || 1);
				var simplePriceEl = document.querySelector('.kaiko-pp-price__current');
				var simpleUnit = simplePriceEl ? parseFloat(simplePriceEl.getAttribute('data-unit-price')) : NaN;
				if (isFinite(simpleUnit) && simpleUnit > 0) setAtcLabel(simpleQ * simpleUnit);
			}
			return;
		}
		var q = 1;
		if (qtyInput) { q = Math.max(1, parseInt(qtyInput.value, 10) || 1); }
		var cells = tiersRoot.querySelectorAll('.kaiko-pp-tier');
		var tier = activeTier(q);
		cells.forEach(function (c) { c.classList.toggle('is-active', c === tier); });
		if (!tier) return;

		var unit = parseFloat(tier.dataset.price);
		if (isNaN(unit)) return;
		var total = q * unit;
		if (totalValue) totalValue.textContent = formatPrice(total);

		// "You save" pill — against first tier (highest per-unit, no discount)
		var baseCell = cells[0];
		var basePrice = baseCell ? parseFloat(baseCell.dataset.price) : unit;
		if (saveEl && basePrice > unit) {
			var saved = (basePrice - unit) * q;
			saveEl.textContent = 'You save £' + saved.toFixed(2);
			saveEl.hidden = false;
		} else if (saveEl) {
			saveEl.hidden = true;
		}

		// Live-update the ADD TO CART button with running total.
		setAtcLabel(total);
	}

	if (qtyInput) {
		qtyInput.addEventListener('input', refresh);
		qtyInput.addEventListener('change', refresh);
	}

	// Click-to-select tier: set the qty input to the tier's minimum (or 1 for the base tier)
	// and fire input/change so refresh() + WC's variation listeners both pick it up.
	if (tiersRoot) {
		tiersRoot.addEventListener('click', function (e) {
			var pill = e.target.closest ? e.target.closest('.kaiko-pp-tier') : null;
			if (!pill) return;
			var min = parseInt(pill.dataset.min, 10) || 1;
			if (qtyInput) {
				// Respect WC's max/step on the qty input if present.
				var max = parseInt(qtyInput.getAttribute('max'), 10);
				var step = parseInt(qtyInput.getAttribute('step'), 10) || 1;
				var target = min;
				if (isFinite(max) && max > 0 && target > max) target = max;
				if (step > 1) target = Math.ceil(target / step) * step;
				qtyInput.value = target;
				// Fire both events so any listeners (WC + our refresh) react.
				qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
				qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
			} else {
				refresh();
			}
		});
	}

	refresh();

	// ---------------------------------------------------------
	// Variable products: recompute tier prices from the picked
	// variation. For default-schedule tiers we apply the stored
	// discount_pct to the variation's display_price. ACF absolute
	// tiers are left untouched.
	// ---------------------------------------------------------
	function formatMoney(n) {
		// Match site currency format roughly — can be upgraded via wc_price on server if needed.
		return '£' + n.toFixed(2);
	}

	function recomputeTiersFromBase(baseUnit) {
		if (!tiersRoot || !isFinite(baseUnit) || baseUnit <= 0) return;
		var cells = tiersRoot.querySelectorAll('.kaiko-pp-tier');
		cells.forEach(function (c) {
			if (c.getAttribute('data-default-schedule') !== '1') return;
			var pct = parseFloat(c.getAttribute('data-discount-pct')) || 0;
			var unit = Math.round(baseUnit * (1 - pct / 100) * 100) / 100;
			c.dataset.price = unit.toFixed(2);
			var priceEl = c.querySelector('.kaiko-pp-tier__price');
			if (priceEl) priceEl.innerHTML = '<bdi>' + formatMoney(unit) + '</bdi>';
		});
		refresh();
	}

	if (window.jQuery) {
		jQuery(document.body).on('found_variation', function (evt, form, variation) {
			if (variation && typeof variation.display_price !== 'undefined') {
				recomputeTiersFromBase(parseFloat(variation.display_price));
			}
			setTimeout(refresh, 50);
		});
		jQuery(document.body).on('reset_data', function () {
			// Fall back to the original base (embedded as data-price on first tier if default).
			var first = tiersRoot ? tiersRoot.querySelector('.kaiko-pp-tier[data-default-schedule="1"]') : null;
			if (first) {
				var pct0 = parseFloat(first.getAttribute('data-discount-pct')) || 0;
				var baseFromFirst = parseFloat(first.dataset.price) / (1 - pct0 / 100);
				if (isFinite(baseFromFirst) && baseFromFirst > 0) {
					recomputeTiersFromBase(baseFromFirst);
				}
			}
		});
	}

	// ---------------------------------------------------------
	// Pill-ify variation selects
	// WC still renders <select> underneath; our CSS hides them.
	// Clicking a pill sets select.value and dispatches 'change'
	// so WC's variations_form JS handles the rest.
	// ---------------------------------------------------------
	function splitOptionText(raw) {
		// Split option text into { label, sub } ONLY when there's a clear
		// separator, so "Reptile Green" / "Stone Grey" / "Volcanic Red" stay
		// as single labels and only "Large 22cm" / "Bowl (500ml)" split.
		var t = (raw || '').trim();
		var m;
		// En-dash, em-dash, hyphen with spaces, middle-dot
		if ((m = t.match(/^(.+?)\s*[\-\—\–·]\s*(.+?)\s*$/))) {
			return { label: m[1].trim(), sub: m[2].trim() };
		}
		// Parenthesised suffix: "Large (22cm diameter)"
		if ((m = t.match(/^(.+?)\s*\(([^)]+)\)\s*$/))) {
			return { label: m[1].trim(), sub: m[2].trim() };
		}
		// Measurement-suffix: "Large 22cm", "Bowl 500ml", "Cube 15g" — only
		// when the trailing token starts with a digit and ends in a unit.
		if ((m = t.match(/^(.+?)\s+(\d+(?:\.\d+)?\s*(?:cm|mm|m|ml|l|g|kg|oz|lb|in|ft|"|'')\b.*?)\s*$/i))) {
			return { label: m[1].trim(), sub: m[2].trim() };
		}
		return { label: t, sub: '' };
	}

	function syncPillsFromSelect(group, select) {
		var val = select.value;
		var pills = group.querySelectorAll('.kaiko-pp-variant-pill');
		var current = group.querySelector('.kaiko-pp-variant-current');
		var activeText = '';
		pills.forEach(function (p) {
			var match = p.getAttribute('data-value') === val;
			p.classList.toggle('is-active', match);
			if (match) {
				var l = p.querySelector('.kaiko-pp-variant-pill__label');
				var s = p.querySelector('.kaiko-pp-variant-pill__sub');
				activeText = (l ? l.textContent : '') + (s && s.textContent ? ' · ' + s.textContent : '');
			}
		});
		if (current) current.textContent = activeText;
	}

	function buildVariationPills() {
		var form = document.querySelector('.kaiko-pp-summary form.variations_form');
		if (!form) return;

		var selects = form.querySelectorAll('table.variations select, table.variations_table select');
		selects.forEach(function (select) {
			if (select.dataset.kaikoPilled === '1') return;
			select.dataset.kaikoPilled = '1';

			var group = document.createElement('div');
			group.className = 'kaiko-pp-variant-group';
			group.setAttribute('data-attr-name', select.getAttribute('data-attribute_name') || select.name || '');

			// Label from the <th><label for="..."> that WC renders
			var row = select.closest('tr');
			var attrLabel = '';
			if (row) {
				var th = row.querySelector('th.label, th label, label');
				if (th) attrLabel = th.textContent.trim();
			}
			if (!attrLabel) {
				attrLabel = (select.getAttribute('data-attribute_name') || select.name || '')
					.replace(/^attribute_(pa_)?/, '').replace(/[_-]/g, ' ').trim();
			}

			var head = document.createElement('div');
			head.className = 'kaiko-pp-variant-head';
			var labelEl = document.createElement('span');
			labelEl.className = 'kaiko-pp-variant-label';
			labelEl.textContent = attrLabel.toUpperCase();
			var currentEl = document.createElement('span');
			currentEl.className = 'kaiko-pp-variant-current';
			head.appendChild(labelEl);
			head.appendChild(currentEl);
			group.appendChild(head);

			var grid = document.createElement('div');
			grid.className = 'kaiko-pp-variant-grid';

			Array.from(select.options).forEach(function (opt) {
				if (!opt.value) return; // skip "Choose an option"
				var pill = document.createElement('button');
				pill.type = 'button';
				pill.className = 'kaiko-pp-variant-pill';
				pill.setAttribute('data-value', opt.value);

				var parts = splitOptionText(opt.textContent);
				var labelSpan = document.createElement('span');
				labelSpan.className = 'kaiko-pp-variant-pill__label';
				labelSpan.textContent = parts.label;
				pill.appendChild(labelSpan);
				if (parts.sub) {
					var subSpan = document.createElement('span');
					subSpan.className = 'kaiko-pp-variant-pill__sub';
					subSpan.textContent = parts.sub;
					pill.appendChild(subSpan);
				}

				pill.addEventListener('click', function () {
					if (select.value === opt.value) return;
					select.value = opt.value;
					if (window.jQuery) {
						jQuery(select).trigger('change').trigger('focusin');
					} else {
						select.dispatchEvent(new Event('change', { bubbles: true }));
					}
					syncPillsFromSelect(group, select);
				});

				grid.appendChild(pill);
			});

			group.appendChild(grid);

			// Insert above the hidden variations table
			var table = select.closest('table.variations, table.variations_table');
			if (table && table.parentNode) {
				table.parentNode.insertBefore(group, table);
			} else {
				// Fallback: prepend to form
				form.insertBefore(group, form.firstChild);
			}

			syncPillsFromSelect(group, select);
		});

		// After building, if only one attr has no selection, pick the first real option
		// so tier pricing reflects something immediately.
		selects.forEach(function (select) {
			if (!select.value) {
				for (var i = 0; i < select.options.length; i++) {
					if (select.options[i].value) {
						select.value = select.options[i].value;
						if (window.jQuery) jQuery(select).trigger('change');
						else select.dispatchEvent(new Event('change', { bubbles: true }));
						var g = document.querySelector('.kaiko-pp-variant-group[data-attr-name="' + (select.getAttribute('data-attribute_name') || select.name) + '"]');
						if (g) syncPillsFromSelect(g, select);
						break;
					}
				}
			}
		});
	}

	buildVariationPills();
	if (window.jQuery) {
		jQuery(document.body).on('woocommerce_update_variation_values wc_variation_form', function () {
			buildVariationPills();
			// Re-sync pills whenever WC re-evaluates available options
			document.querySelectorAll('.kaiko-pp-summary form.variations_form table.variations select').forEach(function (select) {
				var g = document.querySelector('.kaiko-pp-variant-group[data-attr-name="' + (select.getAttribute('data-attribute_name') || select.name) + '"]');
				if (g) syncPillsFromSelect(g, select);

				// Disable pills whose value is no longer available
				var enabledVals = Array.from(select.options).filter(function (o) { return o.value; }).map(function (o) { return o.value; });
				if (g) {
					g.querySelectorAll('.kaiko-pp-variant-pill').forEach(function (p) {
						var v = p.getAttribute('data-value');
						p.classList.toggle('is-disabled', enabledVals.indexOf(v) === -1);
					});
				}
			});
		});
		jQuery(document.body).on('reset_data', function () {
			document.querySelectorAll('.kaiko-pp-summary form.variations_form table.variations select').forEach(function (select) {
				var g = document.querySelector('.kaiko-pp-variant-group[data-attr-name="' + (select.getAttribute('data-attribute_name') || select.name) + '"]');
				if (g) syncPillsFromSelect(g, select);
			});
		});
	}
	// MutationObserver fallback for themes/plugins that re-render the form async
	var summaryRoot = document.querySelector('.kaiko-pp-summary');
	if (summaryRoot && !summaryRoot._kaikoPillObserver) {
		var mo = new MutationObserver(function () { buildVariationPills(); });
		mo.observe(summaryRoot, { childList: true, subtree: true });
		summaryRoot._kaikoPillObserver = mo;
	}
})();
</script>

<?php
get_footer();
