<?php
/**
 * Kaiko — Shop / Category Archive
 *
 * Override of WooCommerce archive-product.php.
 * Matches the design at kaiko-previews/shop.html:
 *   - Full-width hero band
 *   - Sticky sidebar (desktop) / drawer (mobile) with filters
 *   - Animated product grid
 *
 * @package KaikoChild
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

do_action( 'woocommerce_before_main_content' );

// --- Hero data -------------------------------------------------------------
$kaiko_is_cat       = is_product_category();
$kaiko_queried_term = $kaiko_is_cat ? get_queried_object() : null;
$kaiko_eyebrow      = $kaiko_is_cat ? 'Category' : 'Our Collection';

ob_start();
woocommerce_page_title();
$kaiko_page_title = trim( wp_strip_all_tags( ob_get_clean() ) );
if ( '' === $kaiko_page_title ) {
	$kaiko_page_title = 'All Products';
}

$kaiko_description = '';
if ( $kaiko_is_cat && $kaiko_queried_term && ! empty( $kaiko_queried_term->description ) ) {
	$kaiko_description = wp_strip_all_tags( $kaiko_queried_term->description );
} else {
	$kaiko_description = 'Handcrafted habitat equipment, curated by reptile enthusiasts for the UK\'s independent reptile retailers.';
}

$kaiko_total = (int) wc_get_loop_prop( 'total' );

// --- Filter state (read from URL so server-rendered results match AJAX) ---
$kaiko_active = array(
	'category'   => sanitize_text_field( $_GET['category']   ?? ( $kaiko_is_cat && $kaiko_queried_term ? $kaiko_queried_term->slug : '' ) ),
	'species'    => sanitize_text_field( $_GET['species']    ?? '' ),
	'difficulty' => sanitize_text_field( $_GET['difficulty'] ?? '' ),
	'min_price'  => sanitize_text_field( $_GET['min_price']  ?? '' ),
	'max_price'  => sanitize_text_field( $_GET['max_price']  ?? '' ),
	'orderby'    => sanitize_text_field( $_GET['orderby']    ?? 'date' ),
);

$kaiko_categories = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'parent'     => 0,
) );
if ( is_wp_error( $kaiko_categories ) ) {
	$kaiko_categories = array();
}

$kaiko_species_options = array(
	'Bearded Dragon',
	'Leopard Gecko',
	'Crested Gecko',
	'Ball Python',
	'Corn Snake',
	'Blue Tongue Skink',
	'Chameleon',
	'Tortoise',
);
?>

<!-- Shop Hero -->
<section class="kaiko-shop-hero kaiko-reveal" aria-labelledby="kaiko-shop-title">
	<div class="kaiko-shop-hero__inner">
		<span class="kaiko-shop-hero__eyebrow"><?php echo esc_html( $kaiko_eyebrow ); ?></span>
		<h1 id="kaiko-shop-title" class="kaiko-shop-hero__title"><?php echo esc_html( $kaiko_page_title ); ?></h1>
		<p class="kaiko-shop-hero__subtitle"><?php echo esc_html( $kaiko_description ); ?></p>
		<div class="kaiko-shop-hero__count">
			<span id="kaiko-product-count"><?php echo esc_html( $kaiko_total ); ?></span>
			<?php esc_html_e( 'products', 'kaiko-child' ); ?>
		</div>
	</div>
</section>

<!-- Shop Layout -->
<div class="kaiko-shop-layout">

	<!-- Mobile filter toggle -->
	<div class="kaiko-shop-topbar">
		<button type="button" class="kaiko-shop-filter-toggle" id="kaiko-filter-toggle" aria-controls="kaiko-shop-filters" aria-expanded="false">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="6" x2="20" y2="6"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/></svg>
			<?php esc_html_e( 'Filters', 'kaiko-child' ); ?>
		</button>
		<div class="kaiko-shop-topbar__count">
			<span id="kaiko-product-count-inline"><?php echo esc_html( $kaiko_total ); ?></span>
			<?php esc_html_e( 'products', 'kaiko-child' ); ?>
		</div>
	</div>

	<!-- Sidebar Filters -->
	<aside class="kaiko-shop-sidebar" id="kaiko-shop-filters">
		<div class="kaiko-shop-sidebar__header">
			<h2 class="kaiko-shop-sidebar__title"><?php esc_html_e( 'Filters', 'kaiko-child' ); ?></h2>
			<button type="button" class="kaiko-shop-sidebar__close" id="kaiko-filter-close" aria-label="<?php esc_attr_e( 'Close filters', 'kaiko-child' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>

		<form class="kaiko-shop-filters" id="kaiko-shop-filters-form" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" method="get" aria-label="<?php esc_attr_e( 'Shop filters', 'kaiko-child' ); ?>">

			<!-- Category -->
			<section class="kaiko-filter-section">
				<h3 class="kaiko-filter-section__title"><?php esc_html_e( 'Category', 'kaiko-child' ); ?></h3>
				<div class="kaiko-filter-options" role="radiogroup" aria-labelledby="kaiko-filter-cat-label">
					<label class="kaiko-filter-option">
						<input type="radio" name="category" value="" <?php checked( '' === $kaiko_active['category'] ); ?>>
						<span><?php esc_html_e( 'All Products', 'kaiko-child' ); ?></span>
					</label>
					<?php foreach ( $kaiko_categories as $cat ) : ?>
						<label class="kaiko-filter-option">
							<input type="radio" name="category" value="<?php echo esc_attr( $cat->slug ); ?>" <?php checked( $kaiko_active['category'], $cat->slug ); ?>>
							<span><?php echo esc_html( $cat->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</section>

			<!-- Species -->
			<section class="kaiko-filter-section">
				<h3 class="kaiko-filter-section__title"><?php esc_html_e( 'Species', 'kaiko-child' ); ?></h3>
				<div class="kaiko-filter-options">
					<?php foreach ( $kaiko_species_options as $species ) : ?>
						<label class="kaiko-filter-option">
							<input type="radio" name="species" value="<?php echo esc_attr( $species ); ?>" <?php checked( $kaiko_active['species'], $species ); ?>>
							<span><?php echo esc_html( $species ); ?></span>
						</label>
					<?php endforeach; ?>
					<label class="kaiko-filter-option">
						<input type="radio" name="species" value="" <?php checked( '' === $kaiko_active['species'] ); ?>>
						<span><?php esc_html_e( 'Any species', 'kaiko-child' ); ?></span>
					</label>
				</div>
			</section>

			<!-- Difficulty -->
			<section class="kaiko-filter-section">
				<h3 class="kaiko-filter-section__title"><?php esc_html_e( 'Difficulty', 'kaiko-child' ); ?></h3>
				<div class="kaiko-filter-options">
					<?php
					$difficulties = array(
						''             => __( 'All levels', 'kaiko-child' ),
						'beginner'     => __( 'Beginner', 'kaiko-child' ),
						'intermediate' => __( 'Intermediate', 'kaiko-child' ),
						'advanced'     => __( 'Advanced', 'kaiko-child' ),
					);
					foreach ( $difficulties as $value => $label ) : ?>
						<label class="kaiko-filter-option">
							<input type="radio" name="difficulty" value="<?php echo esc_attr( $value ); ?>" <?php checked( $kaiko_active['difficulty'], $value ); ?>>
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</section>

			<!-- Price -->
			<section class="kaiko-filter-section">
				<h3 class="kaiko-filter-section__title"><?php esc_html_e( 'Price (£)', 'kaiko-child' ); ?></h3>
				<div class="kaiko-filter-price">
					<label class="kaiko-filter-price__field">
						<span class="kaiko-sr-only"><?php esc_html_e( 'Minimum price', 'kaiko-child' ); ?></span>
						<input type="number" name="min_price" min="0" step="1" inputmode="numeric" placeholder="<?php esc_attr_e( 'Min', 'kaiko-child' ); ?>" value="<?php echo esc_attr( $kaiko_active['min_price'] ); ?>">
					</label>
					<span class="kaiko-filter-price__sep" aria-hidden="true">–</span>
					<label class="kaiko-filter-price__field">
						<span class="kaiko-sr-only"><?php esc_html_e( 'Maximum price', 'kaiko-child' ); ?></span>
						<input type="number" name="max_price" min="0" step="1" inputmode="numeric" placeholder="<?php esc_attr_e( 'Max', 'kaiko-child' ); ?>" value="<?php echo esc_attr( $kaiko_active['max_price'] ); ?>">
					</label>
				</div>
			</section>

			<!-- Sort -->
			<section class="kaiko-filter-section">
				<h3 class="kaiko-filter-section__title"><?php esc_html_e( 'Sort by', 'kaiko-child' ); ?></h3>
				<label class="kaiko-sr-only" for="kaiko-filter-sort"><?php esc_html_e( 'Sort by', 'kaiko-child' ); ?></label>
				<select id="kaiko-filter-sort" name="orderby" class="kaiko-filter-select">
					<option value="date"       <?php selected( $kaiko_active['orderby'], 'date' ); ?>><?php esc_html_e( 'Newest', 'kaiko-child' ); ?></option>
					<option value="title"      <?php selected( $kaiko_active['orderby'], 'title' ); ?>><?php esc_html_e( 'Name A–Z', 'kaiko-child' ); ?></option>
					<option value="price"      <?php selected( $kaiko_active['orderby'], 'price' ); ?>><?php esc_html_e( 'Price: Low to High', 'kaiko-child' ); ?></option>
					<option value="price-desc" <?php selected( $kaiko_active['orderby'], 'price-desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'kaiko-child' ); ?></option>
				</select>
			</section>

			<div class="kaiko-filter-actions">
				<button type="submit" class="kaiko-btn kaiko-btn-primary kaiko-btn-block kaiko-filter-apply"><?php esc_html_e( 'Apply filters', 'kaiko-child' ); ?></button>
				<button type="button" class="kaiko-btn kaiko-btn-ghost kaiko-filter-reset" id="kaiko-filter-reset"><?php esc_html_e( 'Clear filters', 'kaiko-child' ); ?></button>
			</div>
		</form>
	</aside>

	<div class="kaiko-shop-sidebar-backdrop" id="kaiko-shop-sidebar-backdrop" aria-hidden="true"></div>

	<!-- Products -->
	<div class="kaiko-shop-main">
		<div class="kaiko-product-grid" id="kaiko-product-grid">
			<?php
			if ( woocommerce_product_loop() ) {
				do_action( 'woocommerce_before_shop_loop' );
				woocommerce_product_loop_start();

				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();
						do_action( 'woocommerce_shop_loop' );
						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();
				do_action( 'woocommerce_after_shop_loop' );
			} else {
				?>
				<div class="kaiko-no-products kaiko-reveal">
					<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
					<h3><?php esc_html_e( 'No products match your filters', 'kaiko-child' ); ?></h3>
					<p><?php esc_html_e( 'Try adjusting or clearing your filters to see more products.', 'kaiko-child' ); ?></p>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="kaiko-btn kaiko-btn-primary"><?php esc_html_e( 'Clear filters', 'kaiko-child' ); ?></a>
				</div>
				<?php
			}
			?>
		</div>
	</div>

</div>

<?php
do_action( 'woocommerce_after_main_content' );

get_footer();
