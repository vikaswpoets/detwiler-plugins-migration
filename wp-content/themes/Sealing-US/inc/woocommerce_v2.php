<?php

/**
 * WooCommerce Compatibility File
 *
 * @link https://woocommerce.com/
 *
 * @package cabling
 */

add_action('init', function () {
    add_post_type_support('product', 'page-attributes');
});

function cabling_woocommerce_setup()
{
    add_theme_support('woocommerce');
    add_theme_support( 'wc-product-gallery-slider' );
}

add_action('after_setup_theme', 'cabling_woocommerce_setup');

/**
 * WooCommerce specific scripts & stylesheets.
 *
 * @return void
 */
function cabling_woocommerce_scripts()
{
    wp_enqueue_style('cabling-woocommerce-style', get_template_directory_uri() . '/woocommerce.css');

    $font_path = WC()->plugin_url() . '/assets/fonts/';
    $inline_font = '@font-face {
			font-family: "star";
			src: url("' . $font_path . 'star.eot");
			src: url("' . $font_path . 'star.eot?#iefix") format("embedded-opentype"),
				url("' . $font_path . 'star.woff") format("woff"),
				url("' . $font_path . 'star.ttf") format("truetype"),
				url("' . $font_path . 'star.svg#star") format("svg");
			font-weight: normal;
			font-style: normal;
		}';

    wp_add_inline_style('cabling-woocommerce-style', $inline_font);
}

add_action('wp_enqueue_scripts', 'cabling_woocommerce_scripts');

/**
 * Disable the default WooCommerce stylesheet.
 *
 * Removing the default WooCommerce stylesheet and enqueing your own will
 * protect you during WooCommerce core updates.
 *
 * @link https://docs.woocommerce.com/document/disable-the-default-stylesheet/
 */
//add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Add 'woocommerce-active' class to the body tag.
 *
 * @param array $classes CSS classes applied to the body tag.
 * @return array $classes modified to include 'woocommerce-active' class.
 */
function cabling_woocommerce_active_body_class($classes)
{
    $classes[] = 'woocommerce-active';

    return $classes;
}

add_filter('body_class', 'cabling_woocommerce_active_body_class');

/**
 * Products per page.
 *
 * @return integer number of products.
 */
function cabling_woocommerce_products_per_page()
{
    return 12;
}

add_filter('loop_shop_per_page', 'cabling_woocommerce_products_per_page');

/**
 * Default loop columns on product archives.
 *
 * @return integer products per row.
 */
function cabling_woocommerce_loop_columns()
{
    return is_product_category() ? 1 : 3;
}

add_filter('loop_shop_columns', 'cabling_woocommerce_loop_columns');

/**
 * Related Products Args.
 *
 * @param array $args related products args.
 * @return array $args related products args.
 */
function cabling_woocommerce_related_products_args($args)
{
    $defaults = array(
        'posts_per_page' => 3,
        'columns' => 3,
    );

    $args = wp_parse_args($defaults, $args);

    return $args;
}

add_filter('woocommerce_output_related_products_args', 'cabling_woocommerce_related_products_args');

if (!function_exists('cabling_woocommerce_product_columns_wrapper')) {
    /**
     * Product columns wrapper.
     *
     * @return  void
     */
    function cabling_woocommerce_product_columns_wrapper()
    {
        $columns = cabling_woocommerce_loop_columns();
        echo '<div class="columns-' . absint($columns) . '">';
    }
}
add_action('woocommerce_before_shop_loop', 'cabling_woocommerce_product_columns_wrapper', 40);

if (!function_exists('cabling_woocommerce_product_columns_wrapper_close')) {
    /**
     * Product columns wrapper close.
     *
     * @return  void
     */
    function cabling_woocommerce_product_columns_wrapper_close()
    {
        echo '</div>';
    }
}
add_action('woocommerce_after_shop_loop', 'cabling_woocommerce_product_columns_wrapper_close', 40);

/**
 * Remove default WooCommerce wrapper.
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

function cabling_woocommerce_wrapper_before()
{
    $showFilter = false;
    if (is_tax('product_cat') || is_tax('compound_cat') || is_tax('product_custom_type')) {
        $showFilter = true;
    }
    ?>
    <div id="primary" class="content-area <?php echo $showFilter ? 'has_sidebar container ' : '' ?>">
    <?php if ($showFilter) {
    $product_group = get_product_group_of_type(get_queried_object_id());
    $is_backup_ring_group = $product_group && is_backup_ring_group($product_group->term_id);
    $surface_equipment_id = get_surface_equipment_id();

    get_template_part('template-parts/filter', 'product_category', array(
        'is_backup_ring' => $is_backup_ring_group,
        'is_surface_equipment' => $product_group->term_id == $surface_equipment_id,
    ));
} ?>
    <main id="main" class="site-main" role="main">
    <div class="container">
    <?php
}

add_action('woocommerce_before_main_content', 'cabling_woocommerce_wrapper_before');
function cabling_woocommerce_after_main_content()
{
    if (is_tax('product_cat') || is_tax('compound_cat') || is_product()) {
        cabling_add_quote_section();
    }

    if (is_tax('product_custom_type')) {
        cabling_related_complementary_section();
    }
}

add_action('woocommerce_sidebar', 'cabling_woocommerce_after_main_content');

if (!function_exists('cabling_woocommerce_wrapper_after')) {
    /**
     * After Content.
     *
     * Closes the wrapping divs.
     *
     * @return void
     */
    function cabling_woocommerce_wrapper_after()
    {
        ?>
        </div>
        </main><!-- #main -->
        </div><!-- #primary -->
        <?php
    }
}
add_action('woocommerce_after_main_content', 'cabling_woocommerce_wrapper_after');

function cabling_woocommerce_breadcrumb()
{
    if (is_shop()) return;
    $link = home_url('/products-and-services/');
    if (is_product() && isset($_REQUEST['data-history'])) {
        if (isset($_REQUEST['product-type'])) {
            $product_type = $_REQUEST['product-type'];
            $link = add_query_arg('data-filter', $_REQUEST['data-history'], get_term_link($product_type, 'product_custom_type'));
        } else {
            $link = add_query_arg('data-history', $_REQUEST['data-history'], $link);
        }
    } elseif (is_tax('product_custom_type')) {
        $link = get_product_filter_link(true);
    }
    echo '<div class="container mb-3">';
    echo '<div class="woo-breadcrumbs d-flex align-items-center">';
    echo '<a href="' . $link . '" class="back-button"><i class="fa-light fa-arrow-left"></i>' . __('Back to Results', 'cabling') . '</a>';
    woocommerce_breadcrumb(
        array(
            'delimiter' => ' / ',
            'wrap_before' => '<nav class="woocommerce-breadcrumb"><span>' . __('<span>Products & Services / </span>', 'cabling') . '</span>',
            'home' => ''
        )
    );
    echo '</div>';
    echo '</div>';
}

function endArray($array)
{
    return end($array);
}

//Archive Sidebar
function product_widgets_init()
{
    register_sidebar(array(
        'name' => __('Archive Sidebar', 'cabling'),
        'id' => 'archive-sidebar',
        'before_widget' => '<div>',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}

add_action('widgets_init', 'product_widgets_init');

add_filter('woocommerce_get_catalog_ordering_args', 'am_woocommerce_catalog_orderby');
function am_woocommerce_catalog_orderby($args)
{
    $args['orderby'] = 'date';
    $args['order'] = 'desc';
    return $args;
}

//custom woocommerce page
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

add_action('woocommerce_shop_loop_item_title', 'cabling_product_description', 15);
add_action('woocommerce_before_shop_loop', 'cabling_product_category_heading');

function cabling_get_product_table_attributes(): array
{
    $list_fields = array(
        'product_dash_number' => __('Dash Number', 'cabling'),
        'inches_id' => __('Inches I.D.', 'cabling'),
        'inches_width' => __('Inches CS', 'cabling'),
        'milimeters_id' => __('Millimeters I.D.', 'cabling'),
        'milimeters_width' => __('Millimeters CS', 'cabling'),
        'product_hardness' => __('Hardness', 'cabling'),
        '_sku' => __('SKU', 'cabling'), // JM 20240307 Changed column order
        'product_specifications_met' => __('Specifications Met', 'cabling'),
        'product_operating_temp' => __('Temperature Range, °F', 'cabling'),
        'product_colour' => __('Colour', 'cabling'),
    );

	if ( is_tax( 'product_custom_type' ) ) {
		$product_group = get_product_group_of_type( get_queried_object_id() );
		if ( get_surface_equipment_id() === $product_group->term_id ) {
			$list_fields = array(
				'surface_name' => __('Name', 'cabling'),
				'surface_rubber_type'         => __( 'Rubber Type', 'cabling' ),
				//'surface_assembly_kit'        => __( 'Assembly / Kit', 'cabling' ),
				'surface_thread_up'           => __( 'Thread Up', 'cabling' ),
				'surface_thread_down'         => __( 'Thread Down', 'cabling' ),
				'surface_pressure_rating'     => __( 'Pressure Rating', 'cabling' ),
				'surface_line_rod_size'       => __( 'Line/ Rod Size', 'cabling' ),
				'surface_material'            => __( 'Material', 'cabling' ),
				'surface_minimum_vertical_id' => __( 'Minimum vertical ID', 'cabling' ),
			);
		} else {
			if ( $product_group && is_backup_ring_group( $product_group->term_id ) ) {

				$list_fields = array(
					'product_dash_number_backup_rings' => __( 'Dash Number', 'cabling' ),
					'inches_id_backup-ring'            => __( 'ID', 'cabling' ),
					'inches_t_backup-ring'             => __( 'T', 'cabling' ),
					'inches_width_backup-ring'         => __( 'Width', 'cabling' ),
					'_sku'                             => __( 'SKU', 'cabling' ),
					'product_specifications_met'       => __( 'Specifications Met', 'cabling' ),
					'product_operating_temp'           => __( 'Temperature Range, °F', 'cabling' ),
					'product_colour'                   => __( 'Colour', 'cabling' ),
				);
			}
		}
	}

    return $list_fields;
}

function show_filter_value($fieldList, $product_id)
{
    $class = [];
    foreach ($fieldList as $key => $attribute) {
        $value = get_field($key, $product_id);
        if (is_array($value)) {
            foreach ($value as $vl) {
                $class[] = sanitize_title($key . $vl);
            }
        } else {
            $class[] = sanitize_title($key . $value);
        }
    }
    return $class;
}

function get_product_field($key, $product_id)
{
    $value = get_field($key, $product_id);
    if (empty($value) || $value == 'null') {
        //$value = 'N/A';
        $value = '*';
    } elseif (is_array($value)) {
        $value = implode(', ', $value);
    }
    if ($key === 'product_dash_number') {
        $value = str_replace('AS', '', $value);
    }
    return $value;
}

//Add client download to my account page
add_action('woocommerce_after_account_downloads', 'cabling_woocommerce_download_client', 10, 1);
function cabling_woocommerce_download_client()
{
    $user_id = get_current_user_id();
    $parent = get_user_meta($user_id, 'customer_parent', true);

    $id = !empty($parent) ? $parent : $user_id;

    $client_document = get_field('client_document', 'user_' . $id);

    if ($client_document) {
        echo '<div id="accordion">';
        foreach ($client_document as $key => $doc) { ?>
            <div class="card mb-2">
                <div class="card-header">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapse-<?php echo $key; ?>"
                            aria-expanded="true" aria-controls="collapse-<?php echo $key; ?>">
                        <?php echo $doc["file"]["title"]; ?>
                    </button>
                </div>
                <div id="collapse-<?php echo $key; ?>" class="collapse" data-parent="#accordion">
                    <div class="card-body">
                        <ul class="list-file-download">
                            <li><?php echo $doc["date"]; ?></li>
                            <li><?php echo $doc["description"]; ?></li>
                            <li><a href="<?php echo $doc["file"]["url"]; ?>"
                                   download><?php echo __('Download', 'cabling'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php }
        echo '</div>';
    } else {
        echo '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info" style="display:block;">' . __('No downloads available yet.', 'cabling') . '</div>';
    }
}

function cabling_save_verify_cookie()
{
    if (!empty($_REQUEST['custom_action']) && base64_decode($_REQUEST['custom_action']) === 'verify_customer_cabling') {

        $expiration_time = time() + 30 * 60; // 30 minutes in seconds
        setcookie('verify_customer_cabling_' . $_REQUEST['id'], $_REQUEST['key'], $expiration_time);
    }
}

add_action('init', 'cabling_save_verify_cookie');

add_action('thmaf_after_address_display', 'add_btn_add_shipping_address', 999);
function add_btn_add_shipping_address()
{
    $user_id = get_current_user_id();
    if (class_exists('THMAF_Utils') && (get_customer_type($user_id) === MASTER_ACCOUNT)) {
        $myaccount_page = get_permalink(get_option('woocommerce_myaccount_page_id'));
        $custom_address = THMAF_Utils::get_custom_addresses($user_id, 'shipping');

        if (empty($custom_address) || (sizeof($custom_address) <= 5)) {
            echo '<a href="' . $myaccount_page . 'edit-address/shipping/?atype=add-address" class="button primary is-outline">
               <i class="fa fa-plus"></i>
               Add new address
           </a>';
        }
    }
}

add_filter('woocommerce_show_page_title', '__return_false');
function custom_text_before_product_listing()
{
    $cat = get_queried_object();
    if ($cat->taxonomy === 'product_cat') {
        $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
    } else {
        $thumbnail_id = get_field('taxonomy_image', $cat);
    }
    $thumbnail_id = empty($thumbnail_id) ? 1032601 : $thumbnail_id;

    //$isDoublee = false;
	$skuType = get_query_var('skuType');
	$typeLabel = '';
	if (!empty($skuType)) {
        //$isDoublee = true;
		$typeLabel = sprintf(' - %s', ProductsFilterHelper::getSkuTypeLabel( $skuType));
	}

    get_template_part('template-parts/filter_heading', 'product', [ 'hideWelcome' => true]);
    echo wp_get_attachment_image($thumbnail_id, 'full', false, ['class' => 'my-3']);
    echo '<h1 class="woocommerce-products-header__title page-title">' . woocommerce_page_title(false) . $typeLabel .'</h1>';
}

add_action('woocommerce_archive_description', 'custom_text_before_product_listing', 5);

function order_woocommerce_countries($countries)
{
    $firstItems = [];
    if (isset($countries['US'])) {
        $firstItems['US'] = $countries['US'];
        unset($countries['US']);
    }
    if (isset($countries['CA'])) {
        $firstItems['CA'] = $countries['CA'];
        unset($countries['CA']);
    }
    return array_merge($firstItems, $countries);
}

add_filter('woocommerce_countries', 'order_woocommerce_countries');

add_filter('woocommerce_sort_countries', '__return_false');

/**
 * @param mixed $taxonomy
 * @return string
 */
function getTaxonomyThumbnail(mixed $taxonomy, string $class = ''): string
{
    $thumbnail_id = get_field('taxonomy_image', $taxonomy);
    $thumbnail_id = empty($thumbnail_id) ? 1032601 : $thumbnail_id;
    $thumbnail = wp_get_attachment_image($thumbnail_id, 'full', false, ['class' => $class]);
    return $thumbnail;
}
function debug_log($subject, $body)
{
    //wp_mail('michael.santos@infolabix.com,jose.martins@infolabix.com', $subject, $body);
}

add_filter('woocommerce_get_endpoint_url', 'gi_woocommerce_account_menu_item_link', 10, 4);
function gi_woocommerce_account_menu_item_link($url, $endpoint, $value, $permalink)
{
    if ($endpoint === 'buy-o-rings') {
        $buy_online_link = get_field('buy_online_link', 'option');
        $url = $buy_online_link;
    }
    return $url;
}

// GID-1251
add_action('template_redirect', 'redirect_if_url_is_products');
function redirect_if_url_is_products()
{
    $current_url_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($current_url_path === 'products') {
        wp_redirect(home_url('/products-and-services/'));
        exit;
    }
}
function cabling_add_quote_section()
{
    wc_get_template('single-product/product-add-quote.php');
}
function get_surface_equipment_id()
{
    $surface_equipment_id = get_field('surface_equipment_id', 'options');

    return intval($surface_equipment_id) ?? 0;
}

/**
 * Check if a taxonomy term has products with prices
 *
 * @param int    $term_id       The term ID to check
 * @return bool  True if the term has at least one product with price > 0, false otherwise
 */
function taxonomy_has_products_with_price($term_id) {
    global $wpdb;

    $query = "SELECT EXISTS (
        SELECT 1 
        FROM {$wpdb->posts} p
        JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND tt.term_id = %d
        AND pm.meta_key = '_price'
        AND pm.meta_value > 0
        LIMIT 1 
    )";

    $sql = $wpdb->prepare($query, $term_id);

    return (bool) $wpdb->get_var($sql);
}
function gi_product_has_surface_equipment( $productId = null ): bool {
    $id = $productId ?? get_the_ID();
	$surface_equipment_id = get_surface_equipment_id();

    return has_term( $surface_equipment_id, 'product_group', $id );
}

function get_product_warehouse_info($product_id) {

    if ( 'yes' !== get_post_meta( $product_id, 'prod_level_inven', true ) ) {
        return null;
    }

    $inventories = get_the_terms( $product_id, 'mli_location' );
    if ( empty( $inventories ) ) {
        return null;
    }

    // Use the first inventory entry
    $warehouse = reset( $inventories );

    $loc_id = $warehouse->term_id;

    $street   = get_term_meta( $loc_id, 'af_mli_tax_adress', true );
    $city     = get_term_meta( $loc_id, 'af_mli_tax_city', true );
    $state    = get_term_meta( $loc_id, 'af_mli_tax_state', true );
    $zip      = get_term_meta( $loc_id, 'af_mli_tax_zip_code', true );
    $country  = get_term_meta( $loc_id, 'af_mli_tax_country', true );

    if ( !empty( $state ) && ! empty( $zip ) && !empty( $country) && !empty( $city)) {
	    return [
		    'location_id'   => $loc_id,
		    'location_name' => $warehouse->name,
		    'address'       => [
			    'street'  => $street,
			    'city'    => $city,
			    'state'   => $state,
			    'zip'     => $zip,
			    'country' => $country,
		    ],
	    ];
    }
    return null;
}
