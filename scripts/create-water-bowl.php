<?php
/**
 * Kaiko — One-shot migration: create the "Reptile Water and Food Bowl Rock"
 * variable product (KAIKO-RWFB) with 15 variations, sideloaded images, and
 * ACF tier rows mirrored from the Escape-Proof Dubia Roach Dish.
 *
 * NOT auto-loaded by the theme. Run via either:
 *   wp eval-file scripts/create-water-bowl.php
 *   /wp-admin/?kaiko_run_migration=create-water-bowl&_wpnonce=…
 *     (handler in inc/admin/migrations.php; manage_options-gated)
 *
 * Idempotent: if a product with SKU KAIKO-RWFB already exists, exits early.
 *
 * Delete this file (and inc/admin/migrations.php) once the run is verified
 * on production — both are one-shots, not part of the theme's runtime.
 *
 * @package KaikoChild
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'kaiko_create_water_bowl_run' ) ) :

/**
 * Run the migration. Returns an array of status lines for the caller to
 * print / log. Does not echo directly so it works under both WP-CLI and
 * the admin URL trigger.
 *
 * @return string[]
 */
function kaiko_create_water_bowl_run() {
	$log = array();
	$log[] = '[kaiko-water-bowl] starting…';

	if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		$log[] = 'ERROR: WooCommerce not active.';
		return $log;
	}

	$existing = wc_get_product_id_by_sku( 'KAIKO-RWFB' );
	if ( $existing ) {
		$log[] = "already done — product #{$existing} (SKU KAIKO-RWFB) exists. Exiting.";
		return $log;
	}

	// Pull in the WP admin helpers we need for sideloading images.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$csv_path = __DIR__ . '/kaiko-reptile-water-bowl-import.csv';
	if ( ! is_readable( $csv_path ) ) {
		$log[] = "ERROR: CSV not readable at {$csv_path}";
		return $log;
	}

	$rows = kaiko_water_bowl_read_csv( $csv_path );
	if ( count( $rows ) < 16 ) {
		$log[] = 'ERROR: CSV must contain a parent row plus 15 variation rows; got ' . count( $rows ) . '.';
		return $log;
	}

	$parent_row     = $rows[0];
	$variation_rows = array_slice( $rows, 1, 15 );

	if ( 'variable' !== ( $parent_row['Type'] ?? '' ) || 'KAIKO-RWFB' !== ( $parent_row['SKU'] ?? '' ) ) {
		$log[] = 'ERROR: first CSV data row must be the variable parent with SKU KAIKO-RWFB.';
		return $log;
	}

	// 1. Categories — ensure Decor > Bowls exists.
	$decor_term_id = kaiko_water_bowl_ensure_term( 'Decor', 'product_cat', 0 );
	$bowls_term_id = kaiko_water_bowl_ensure_term( 'Bowls', 'product_cat', $decor_term_id );
	$log[] = "categories: Decor #{$decor_term_id} > Bowls #{$bowls_term_id}";

	// 2. Global attributes — pa_size + pa_colour, plus terms.
	$size_attr_id   = kaiko_water_bowl_ensure_attribute_taxonomy( 'Size', 'size' );
	$colour_attr_id = kaiko_water_bowl_ensure_attribute_taxonomy( 'Colour', 'colour' );

	$size_terms = array();
	foreach ( array( 'Standard', 'Large', 'XL' ) as $name ) {
		$size_terms[ $name ] = kaiko_water_bowl_ensure_term( $name, 'pa_size', 0 );
	}
	$colour_terms = array();
	foreach ( array( 'Rock', 'Rainbow', 'Reptile Green', 'Brown Rock', 'Volcanic Red' ) as $name ) {
		$colour_terms[ $name ] = kaiko_water_bowl_ensure_term( $name, 'pa_colour', 0 );
	}
	$log[] = 'attributes: pa_size #' . $size_attr_id . ' (' . count( $size_terms ) . ' terms), pa_colour #' . $colour_attr_id . ' (' . count( $colour_terms ) . ' terms)';

	// 3. Sideload parent images. First = featured, rest = gallery.
	$image_urls = array_filter( array_map( 'trim', explode( ',', (string) ( $parent_row['Images'] ?? '' ) ) ) );
	$image_urls = array_values( array_unique( $image_urls ) );

	$url_to_attachment = array();
	$attach_ids        = array();
	foreach ( $image_urls as $url ) {
		$att_id = media_sideload_image( $url, 0, null, 'id' );
		if ( is_wp_error( $att_id ) || ! $att_id ) {
			$log[] = "WARN: failed to sideload {$url}: " . ( is_wp_error( $att_id ) ? $att_id->get_error_message() : 'unknown error' );
			continue;
		}
		$url_to_attachment[ $url ] = (int) $att_id;
		$attach_ids[]              = (int) $att_id;
	}
	$log[] = 'sideloaded ' . count( $attach_ids ) . ' images: ' . implode( ',', $attach_ids );

	$featured_id = $attach_ids ? array_shift( $attach_ids ) : 0;
	$gallery_ids = $attach_ids;

	// 4. Build parent product.
	$parent = new WC_Product_Variable();
	$parent->set_name( (string) $parent_row['Name'] );
	$parent->set_status( 'publish' );
	$parent->set_catalog_visibility( 'visible' );
	$parent->set_description( (string) $parent_row['Description'] );
	$parent->set_short_description( (string) $parent_row['Short description'] );
	$parent->set_sku( 'KAIKO-RWFB' );
	$parent->set_tax_status( 'taxable' );
	$parent->set_reviews_allowed( true );
	$parent->set_category_ids( array_filter( array( $bowls_term_id ) ) );
	if ( $featured_id ) {
		$parent->set_image_id( $featured_id );
	}
	if ( ! empty( $gallery_ids ) ) {
		$parent->set_gallery_image_ids( $gallery_ids );
	}

	// Attributes.
	$attr_size = new WC_Product_Attribute();
	$attr_size->set_id( $size_attr_id );
	$attr_size->set_name( 'pa_size' );
	$attr_size->set_options( array_values( $size_terms ) );
	$attr_size->set_position( 0 );
	$attr_size->set_visible( true );
	$attr_size->set_variation( true );

	$attr_colour = new WC_Product_Attribute();
	$attr_colour->set_id( $colour_attr_id );
	$attr_colour->set_name( 'pa_colour' );
	$attr_colour->set_options( array_values( $colour_terms ) );
	$attr_colour->set_position( 1 );
	$attr_colour->set_visible( true );
	$attr_colour->set_variation( true );

	$parent->set_attributes( array( $attr_size, $attr_colour ) );

	$parent_id = $parent->save();
	if ( ! $parent_id ) {
		$log[] = 'ERROR: failed to save parent product.';
		return $log;
	}
	$log[] = "parent saved: #{$parent_id}";

	// Re-attach the sideloaded images to the new product (post_parent).
	foreach ( $url_to_attachment as $att_id ) {
		wp_update_post( array( 'ID' => $att_id, 'post_parent' => $parent_id ) );
	}

	// 5. Create variations.
	$created = 0;
	foreach ( $variation_rows as $i => $row ) {
		if ( 'variation' !== ( $row['Type'] ?? '' ) ) {
			continue;
		}
		$size_name   = trim( (string) ( $row['Attribute 1 value(s)'] ?? '' ) );
		$colour_name = trim( (string) ( $row['Attribute 2 value(s)'] ?? '' ) );
		if ( ! isset( $size_terms[ $size_name ] ) || ! isset( $colour_terms[ $colour_name ] ) ) {
			$log[] = "WARN: skipping variation row " . ( $i + 1 ) . " — unknown attribute combo ({$size_name} / {$colour_name})";
			continue;
		}

		$var_image_url = trim( (string) ( $row['Images'] ?? '' ) );
		$var_image_id  = 0;
		if ( $var_image_url ) {
			if ( isset( $url_to_attachment[ $var_image_url ] ) ) {
				$var_image_id = $url_to_attachment[ $var_image_url ];
			} else {
				$sideloaded = media_sideload_image( $var_image_url, $parent_id, null, 'id' );
				if ( ! is_wp_error( $sideloaded ) && $sideloaded ) {
					$var_image_id                       = (int) $sideloaded;
					$url_to_attachment[ $var_image_url ] = $var_image_id;
				}
			}
		}

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent_id );
		$variation->set_attributes( array(
			'pa_size'   => get_term( $size_terms[ $size_name ], 'pa_size' )->slug,
			'pa_colour' => get_term( $colour_terms[ $colour_name ], 'pa_colour' )->slug,
		) );
		$variation->set_sku( (string) $row['SKU'] );
		$variation->set_status( 'publish' );
		$variation->set_regular_price( (string) $row['Regular price'] );
		$variation->set_tax_status( 'taxable' );
		$variation->set_manage_stock( false );
		$variation->set_stock_status( 'instock' );
		// CSV ships dimensions as Length=11, Width=blank, Height=17 (cm). Weight blank.
		$variation->set_weight( '' );
		$variation->set_length( (string) ( $row['Length (cm)'] ?? '' ) );
		$variation->set_width( (string) ( $row['Width (cm)'] ?? '' ) );
		$variation->set_height( (string) ( $row['Height (cm)'] ?? '' ) );
		if ( $var_image_id ) {
			$variation->set_image_id( $var_image_id );
		}
		$variation->save();
		$created++;
	}
	$log[] = "variations created: {$created}/15";

	// Resync parent (price range, variation cache, etc.).
	WC_Product_Variable::sync( $parent_id );

	// 6. Mirror ACF tier rows from Escape-Proof.
	$acf_count = kaiko_water_bowl_mirror_tiers_from_escape_proof( $parent_id, $log );

	$log[] = sprintf(
		'DONE: product #%d, %d variations, %d image attachments, %d ACF tier rows',
		$parent_id,
		$created,
		count( $url_to_attachment ),
		$acf_count
	);

	return $log;
}

/**
 * Read the CSV into an array of associative rows keyed by header name.
 */
function kaiko_water_bowl_read_csv( $path ) {
	$fh = fopen( $path, 'r' );
	if ( ! $fh ) {
		return array();
	}
	$header = fgetcsv( $fh );
	if ( ! $header ) {
		fclose( $fh );
		return array();
	}
	$rows = array();
	while ( ( $cells = fgetcsv( $fh ) ) !== false ) {
		// Skip blank lines.
		if ( count( $cells ) === 1 && trim( (string) $cells[0] ) === '' ) {
			continue;
		}
		// Pad / trim to header length.
		$cells = array_pad( $cells, count( $header ), '' );
		$cells = array_slice( $cells, 0, count( $header ) );
		$rows[] = array_combine( $header, $cells );
	}
	fclose( $fh );
	return $rows;
}

/**
 * Ensure a term exists in a taxonomy (creating if needed). Returns term_id.
 */
function kaiko_water_bowl_ensure_term( $name, $taxonomy, $parent_id = 0 ) {
	$existing = term_exists( $name, $taxonomy, $parent_id ?: null );
	if ( is_array( $existing ) && ! empty( $existing['term_id'] ) ) {
		return (int) $existing['term_id'];
	}
	if ( is_numeric( $existing ) ) {
		return (int) $existing;
	}
	$args = array();
	if ( $parent_id ) {
		$args['parent'] = (int) $parent_id;
	}
	$inserted = wp_insert_term( $name, $taxonomy, $args );
	if ( is_wp_error( $inserted ) ) {
		// Race: another process may have just created it.
		$existing = term_exists( $name, $taxonomy, $parent_id ?: null );
		if ( is_array( $existing ) && ! empty( $existing['term_id'] ) ) {
			return (int) $existing['term_id'];
		}
		return 0;
	}
	return (int) $inserted['term_id'];
}

/**
 * Ensure a WooCommerce global attribute taxonomy exists. Returns the
 * attribute_id from wc_attribute_taxonomies.
 */
function kaiko_water_bowl_ensure_attribute_taxonomy( $label, $slug ) {
	$taxonomy = wc_attribute_taxonomy_name( $slug );
	$existing_id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( $existing_id ) {
		// Make sure the taxonomy is registered for this request.
		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, array( 'product' ), array(
				'hierarchical' => false,
				'show_ui'      => false,
				'query_var'    => true,
				'rewrite'      => false,
			) );
		}
		return (int) $existing_id;
	}
	$attribute_id = wc_create_attribute( array(
		'name'         => $label,
		'slug'         => $slug,
		'type'         => 'select',
		'order_by'     => 'menu_order',
		'has_archives' => false,
	) );
	if ( is_wp_error( $attribute_id ) ) {
		return 0;
	}
	// Register the new taxonomy for the current request so wp_insert_term works.
	register_taxonomy( $taxonomy, array( 'product' ), array(
		'hierarchical' => false,
		'show_ui'      => false,
		'query_var'    => true,
		'rewrite'      => false,
	) );
	delete_transient( 'wc_attribute_taxonomies' );
	return (int) $attribute_id;
}

/**
 * Copy kaiko_wholesale_tiers ACF rows from the Escape-Proof Dubia Roach
 * Dish onto the new product. Returns the count of rows copied.
 */
function kaiko_water_bowl_mirror_tiers_from_escape_proof( $new_product_id, &$log ) {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
		$log[] = 'WARN: ACF not active — skipping tier mirror.';
		return 0;
	}

	$source_id = 0;
	$by_slug   = get_page_by_path( 'escape-proof-dubia-roach-dish', OBJECT, 'product' );
	if ( $by_slug && 'product' === $by_slug->post_type ) {
		$source_id = (int) $by_slug->ID;
	}
	if ( ! $source_id ) {
		$query = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'title'          => 'Escape-Proof Dubia Roach Dish',
			'fields'         => 'ids',
		) );
		if ( ! empty( $query[0] ) ) {
			$source_id = (int) $query[0];
		}
	}
	if ( ! $source_id ) {
		$log[] = 'WARN: Escape-Proof Dubia Roach Dish not found — leaving new product without ACF tiers (default schedule will apply).';
		return 0;
	}

	$rows = get_field( 'kaiko_wholesale_tiers', $source_id );
	if ( ! is_array( $rows ) || empty( $rows ) ) {
		$log[] = "WARN: Escape-Proof (#{$source_id}) has no kaiko_wholesale_tiers rows — leaving new product without ACF tiers (default schedule will apply).";
		return 0;
	}
	update_field( 'kaiko_wholesale_tiers', $rows, $new_product_id );
	$log[] = "mirrored " . count( $rows ) . " ACF tier rows from Escape-Proof #{$source_id}";
	return count( $rows );
}

endif; // function_exists guard

/*
 * When invoked via `wp eval-file`, run the migration immediately. The admin
 * URL handler require_once's this file then calls the function explicitly,
 * so the WP_CLI guard avoids double-running there.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	$lines = kaiko_create_water_bowl_run();
	foreach ( $lines as $line ) {
		WP_CLI::log( $line );
	}
}
