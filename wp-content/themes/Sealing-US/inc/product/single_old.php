<?php
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

add_action('woocommerce_before_main_content', 'cabling_woocommerce_breadcrumb', 1);
add_action('woocommerce_before_single_product_summary', 'cabling_get_brand_product', 4);
add_action('woocommerce_single_product_summary', 'cabling_additional_information', 90);
add_action('woocommerce_after_single_product_summary', 'cabling_surface_information', 90);

function cabling_get_brand_product($product_id)
{
    global $product;
    if (empty($product_id))
        $product_id = $product->get_id();
    $data = [];
    $brands = get_the_terms($product_id, 'product-brand');
    if ($brands) {
        foreach ($brands as $brand) {
            $brand_image = get_field('taxonomy_image', $brand);
            if ($brand_image)
                $data[] = sprintf('<a href="%s">%s</a>', get_term_link($brand), wp_get_attachment_image($brand_image, 'full'));
        }
    }
    echo '<div class="product-brands">';
    echo implode('', $data);
    echo '</div>';
}

function cabling_add_quote_on_product()
{
    global $product;

    // Check if it's a product page and if price is empty
    if (is_product() && '' === $product->get_price()) {
        cabling_add_quote_button();
    }
}
function cabling_additional_information()
{
    global $product;
    if ($product && $product->is_type('variable'))
        return;

    $is_surface_equipment = gi_product_has_surface_equipment($product->get_id());

    if ($is_surface_equipment){
        $data = get_field('surface_key_features', $product->get_id());
        wc_get_template('single-product/product-surface-key_features.php', array(
            'data' => $data,
            'is_purchasable' => $product->is_purchasable(),
            'product_id' => $product->get_id(),
        ));
    } else {
        if (is_product() && '' === $product->get_price()) {
            cabling_add_quote_button();
        }

        ob_start();
        cabling_woocommerce_pdf_document($product);
        echo ob_get_clean();
    }
}

function cabling_related_complementary_section()
{
    wc_get_template('product-complementary-section.php');
}

function cabling_woocommerce_pdf_document($product)
{
    $pdfs = get_field('pdp_document');
    $product_dynamic_fields = get_field('product_dynamic_fields', $product->get_id());
    $icon = get_template_directory_uri() . '/assets/img/%image%.png';

    $product_attributes = [];
    if ($product->has_weight()) {
        $product_attributes['weight'] = array(
            'label' => __('Weight', 'woocommerce'),
            'value' => wc_format_weight($product->get_weight()),
        );
    }

    if ($product->has_dimensions()) {
        $product_attributes['dimensions'] = array(
            'label' => __('Dimensions', 'woocommerce'),
            'value' => wc_format_dimensions($product->get_dimensions(false)),
        );
    }

    $custom_fields = cabling_get_product_single_attributes($product_dynamic_fields, $product->get_id());

    wc_get_template('single-product/product-document.php', array(
        'pdfs' => $pdfs,
        'icon' => $icon,
        'product_attributes' => $product_attributes,
        'custom_fields' => $custom_fields,
        'dynamic_fields' => $product_dynamic_fields,
        'product_id' => $product->get_id(),
    ));
}
function cabling_woocommerce_find_a_stockist()
{
    wc_get_template('single-product/product-stockist.php');
}

/**
 * Remove product data tabs
 */
add_filter('woocommerce_product_tabs', 'cabling_woo_remove_product_tabs', 98);
function cabling_woo_remove_product_tabs($tabs)
{
    unset($tabs['description']);
    unset($tabs['additional_information']);

    return $tabs;
}

function cabling_woocommerce_description()
{
    global $product;
    ob_start();
	$key_benefits                = get_field( 'key_benefits', $product->get_id() );
	$use_with                    = get_field( 'use_with', $product->get_id() );
	$do_not_use_with             = get_field( 'do_not_use_with', $product->get_id() );
	$typical_values_for_compound = get_field( 'typical_values_for_compound', $product->get_id() );

	wc_get_template( 'single-product/product-simple.php', [
		'key_benefits'                => $key_benefits,
		'use_with'                    => $use_with,
		'do_not_use_with'             => $do_not_use_with,
		'typical_values_for_compound' => $typical_values_for_compound,
	] );
    $product_data = ob_get_clean();

    $heading = __('Product Description', 'cabling');
    ob_start(); ?>
    <div class="product-description mb-5">
        <div class="main-description mb-5">
            <h4><?php echo $heading ?></h4>
            <?php
            echo apply_filters('the_content', get_the_content());
            ?>
        </div>
        <?php echo $product_data; ?>
    </div>
    <?php
	return ob_get_clean();
}

function cabling_product_category_heading()
{
    echo '<h4>' . __('Products Available', 'cabling') . '</h4>';
}

function cabling_product_description()
{
    echo '<div class="product-excerpt">';
    the_excerpt();
    echo '</div>';
}

function cabling_get_product_single_attributes($dynamic_fields, $product_id): array
{
    $list_fields = array();

    $product_id = empty($product_id) ? get_the_ID() : $product_id;

    $is_backup_ring = false;
    $product_groups = get_the_terms($product_id, 'product_group');
    if ($product_groups && !is_wp_error($product_groups)) {
        $product_group = reset($product_groups);
        $is_backup_ring = is_backup_ring_group($product_group->term_id);

        if ($product_group) {
            $list_fields['attributes']['product_cat'] = array(
                'label' => '',
                'value' => $product_group->name
            );
        }
    }

    //product_material
    $product_material = get_product_field('product_material', $product_id);
    if (!empty($product_material)) {
        $list_fields['attributes']['product_material'] = array(
            'label' => __('Material', 'cabling'),
            'value' => get_the_title($product_material)
        );
    }
    if ($is_backup_ring){
        //product_hardness
        $hardness = get_product_field('product_hardness', $product_id);
        if (!empty($hardness)) {
            $list_fields['attributes']['product_hardness'] = array(
                'label' => __('HARDNESS (SHORE A) +/-5', 'cabling'),
                'value' => $hardness
            );
        }
        //product_dash_number_backup_rings
        $product_dash_number = get_product_field('product_dash_number_backup_rings', $product_id);
        if (!empty($product_dash_number)) {
            $list_fields['attributes']['product_dash_number_backup_rings'] = array(
                'label' => __('Dash Number', 'cabling'),
                'value' => str_replace('AS', '', $product_dash_number)
            );
        }
        //inches_id_backup-ring
        $inches_id = get_product_field('inches_id_backup-ring', $product_id);
        if (!empty($inches_id)) {
            $list_fields['attributes']['inches_id_backup-ring'] = array(
                'label' => __('ID', 'cabling'),
                'value' => $inches_id
            );
        }
        //inches_t_backup-ring
        $t = get_product_field('inches_t_backup-ring', $product_id);
        if (!empty($t)) {
            $list_fields['attributes']['inches_t_backup-ring'] = array(
                'label' => __('T', 'cabling'),
                'value' => $t
            );
        }
        //inches_width_backup-ring
        $width = get_product_field('inches_width_backup-ring', $product_id);
        if (!empty($width)) {
            $list_fields['attributes']['inches_width_backup-ring'] = array(
                'label' => __('Width', 'cabling'),
                'value' => $width
            );
        }
        $list_fields['size']['--'] = array(
            'label' => '',
            'value' => ''
        );
    } else {
        //o-ring standard
        if (!empty($dynamic_fields)) {
            $o_ring_standard_index = array_search('O-RING SIZE STANDARD', array_column($dynamic_fields, 'label'));
            if ($o_ring_standard_index !== false) {
                $list_fields['attributes']['O-RING SIZE STANDARD'] = $dynamic_fields[$o_ring_standard_index];
            }
        }
        //product_dash_number
        $product_dash_number = get_product_field('product_dash_number', $product_id);
        if (!empty($product_dash_number)) {
            $list_fields['attributes']['product_dash_number'] = array(
                'label' => __('Dash Number', 'cabling'),
                'value' => str_replace('AS', '', $product_dash_number)
            );
        }

        //nominal_size_width
        $nominal_size_width = get_product_field('nominal_size_width', $product_id);
        if (!empty($nominal_size_width)) {
            $list_fields['size']['nominal_size_width'] = array(
                'label' => __('Nominal CS', 'cabling'),
                'value' => $nominal_size_width
            );
        }
        //inches_width
        $inches_width = get_product_field('inches_width', $product_id);
        if (!empty($inches_width)) {
            $list_fields['size']['inches_width'] = array(
                'label' => __('Inches CS', 'cabling'),
                'value' => $inches_width
            );
        }
        //Nominal Size O.D.
        $nominal_size_od = get_product_field('nominal_size_od', $product_id);
        if (!empty($nominal_size_od)) {
            $list_fields['size']['nominal_size_od'] = array(
                'label' => __('Nominal Size O.D.', 'cabling'),
                'value' => $nominal_size_od
            );
        }
        //inches_od
        $inches_od = get_product_field('inches_od', $product_id);
        if (!empty($inches_od)) {
            $list_fields['size']['inches_od'] = array(
                'label' => __('Inches O.D.', 'cabling'),
                'value' => $inches_od
            );
        }
        //Nominal Size I.D.
        $nominal_size_id = get_product_field('nominal_size_id', $product_id);
        if (!empty($nominal_size_id)) {
            $list_fields['size']['nominal_size_id'] = array(
                'label' => __('Nominal Size I.D.', 'cabling'),
                'value' => $nominal_size_id
            );
        }
        //inches_id
        $inches_id = get_product_field('inches_id', $product_id);
        if (!empty($inches_id)) {
            $list_fields['size']['inches_id'] = array(
                'label' => __('Inches I.D.', 'cabling'),
                'value' => $inches_id
            );
        }
        //inches_id_tol
        $list_fields['size']['--'] = array(
            'label' => '',
            'value' => ''
        );
        //inches_id_tol
        $inches_id_tol = get_product_field('inches_id_tol', $product_id);
        if (!empty($inches_id_tol)) {
            $list_fields['size']['inches_id_tol'] = array(
                'label' => __('Inches I.D. Tol.', 'cabling'),
                'value' => $inches_id_tol
            );
        }
        //inches_id_tol
        $list_fields['size']['---'] = array(
            'label' => '',
            'value' => ''
        );
        //inches_id_tol
        $inches_width_tol = get_product_field('inches_width_tol', $product_id);
        if (!empty($inches_width_tol)) {
            $list_fields['size']['inches_width_tol'] = array(
                'label' => __('Inches CS Tol.', 'cabling'),
                'value' => $inches_width_tol
            );
        }
    }

    return $list_fields;
}
function cabling_surface_information() {
    global $product;
    if (gi_product_has_surface_equipment($product->get_id())){
//        $line_name = get_the_title();
        $productLines = get_the_terms( $product->get_id(), 'product_line' );
        if (!empty($productLines[0])){
//            $line_name = $line_name . ' ' . $productLines[0]->name;
		$productTypes= get_the_terms( $product->get_id(), 'product_custom_type' );
            $popularHeading = sprintf(__('Popular %s', 'cabling'), $productLines[0]->name);
            $replacementPartHeading = sprintf(__('for %s %s Models', 'cabling'), $productTypes[0]->name ?? '', $productLines[0]->name);

            $product_attributes = array(
				'surface_rubber_type'         => __( 'Rubber Type', 'cabling' ),
				'surface_assembly_kit'        => __( 'Assembly / Kit', 'cabling' ),
				'surface_thread_up'           => __( 'Thread Up', 'cabling' ),
				'surface_thread_down'         => __( 'Thread Down', 'cabling' ),
				'surface_pressure_rating'     => __( 'Pressure Rating', 'cabling' ),
				'surface_line_rod_size'       => __( 'Line/ Rod Size', 'cabling' ),
				'surface_minimum_vertical_id' => __( 'Minimum vertical ID', 'cabling' ),
			);

	        $skuTypes = array_keys(ProductsFilterHelper::SKU_TYPES);
            $componentProducts = array();
            $mainProducts = array();
            foreach ($skuTypes as $skuType) {
                $list = get_component_products($productLines[0]->term_id, $skuType);
                if (!empty($list)) {
                    if ($skuType === 'PRODUCT'){
                        $mainProducts[ $skuType ] = $list;
                    } else {
	                    $componentProducts[ $skuType ] = $list;
                    }
                }
            }

	        $surface_popular_information = get_field('surface_popular_information', $product->get_id());
			$replacementPartImage = get_field('surface_replacement_part_image', $product->get_id());
			
	        wc_get_template('single-product/product-surface-details.php', array(
		        'popular_information' => $surface_popular_information,
//		        'line_name' => $line_name,
			'popularHeading' => $popularHeading,
		        'replacementPartHeading' => $replacementPartHeading,
		        'product' => $product,
		        'attributes' => $product_attributes,
		        'componentProducts' => $componentProducts,
		        'mainProducts' => $mainProducts,
		        'replacementPartImage' => $replacementPartImage,
	        ));
        }
    }
}

/**
 * Get all component products
 */
function get_component_products( int $product_line, string $SKU_TYPE_COMPONENT ) {
	$args = [
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => [
			[
				'key'   => 'sku_type',
				'value' => $SKU_TYPE_COMPONENT,
				'compare' => '='
			]
		]
	];

	if ( $product_line ) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_line',
                'field'    => 'term_id',
                'terms'    => $product_line,
            ]
        ];
    }

	$component_products = get_posts( $args );

	return $component_products;
}
