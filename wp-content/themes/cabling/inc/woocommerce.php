<?php

/**
 * WooCommerce Compatibility File
 *
 * @link https://woocommerce.com/
 *
 * @package cabling
 */

use GeoIp2\Database\Reader;

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
 * Product gallery thumnbail columns.
 *
 * @return integer number of columns.
 */
function cabling_woocommerce_thumbnail_columns()
{
    return 4;
}

add_filter('woocommerce_product_thumbnails_columns', 'cabling_woocommerce_thumbnail_columns');

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
        'is_backup_ring'       => $is_backup_ring_group,
        'is_surface_equipment' => $product_group && $product_group->term_id == $surface_equipment_id,
    ));
} ?>
    <main id="main" class="site-main" role="main">
    <div class="container">
    <?php
}

add_action('woocommerce_before_main_content', 'cabling_woocommerce_wrapper_before');
function cabling_woocommerce_after_main_content()
{
    if (is_tax('product_cat') || is_tax('compound_cat') ) {
        cabling_add_quote_section();
    }
}

add_action('woocommerce_after_shop_loop', 'gi_related_complementary_section');
function gi_related_complementary_section()
{
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

/**
 * Sample implementation of the WooCommerce Mini Cart.
 *
 * You can add the WooCommerce Mini Cart to header.php like so ...
 *
 * <?php
 * if ( function_exists( 'cabling_woocommerce_header_cart' ) ) {
 * cabling_woocommerce_header_cart();
 * }
 * ?>
 */

if (!function_exists('cabling_woocommerce_cart_link_fragment')) {
    /**
     * Cart Fragments.
     *
     * Ensure cart contents update when products are added to the cart via AJAX.
     *
     * @param array $fragments Fragments to refresh via AJAX.
     * @return array Fragments to refresh via AJAX.
     */
    function cabling_woocommerce_cart_link_fragment($fragments)
    {
        ob_start();
        cabling_woocommerce_cart_link();
        $fragments['a.cart-contents'] = ob_get_clean();

        return $fragments;
    }
}
add_filter('woocommerce_add_to_cart_fragments', 'cabling_woocommerce_cart_link_fragment');

if (!function_exists('cabling_woocommerce_cart_link')) {
    /**
     * Cart Link.
     *
     * Displayed a link to the cart including the number of items present and the cart total.
     *
     * @return void
     */
    function cabling_woocommerce_cart_link()
    {
        ?>
        <a class="cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>"
           title="<?php esc_attr_e('View your shopping cart', 'cabling'); ?>">
            <?php
            $item_count_text = sprintf(
            /* translators: number of items in the mini cart. */
                _n('%d item', '%d items', WC()->cart->get_cart_contents_count(), 'cabling'),
                WC()->cart->get_cart_contents_count()
            );
            ?>
            <span class="amount"><?php echo wp_kses_data(WC()->cart->get_cart_subtotal()); ?></span> <span
                    class="count"><?php echo esc_html($item_count_text); ?></span>
        </a>
        <?php
    }
}

if (!function_exists('cabling_woocommerce_header_cart')) {
    /**
     * Display Header Cart.
     *
     * @return void
     */
    function cabling_woocommerce_header_cart()
    {
        if (is_cart()) {
            $class = 'current-menu-item';
        } else {
            $class = '';
        }
        ?>
        <ul id="site-header-cart" class="site-header-cart">
            <li class="<?php echo esc_attr($class); ?>">
                <?php cabling_woocommerce_cart_link(); ?>
            </li>
            <li>
                <?php
                $instance = array(
                    'title' => '',
                );

                the_widget('WC_Widget_Cart', $instance);
                ?>
            </li>
        </ul>
        <?php
    }
}

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

function cabling_woocommerce_breadcrumb_back_button()
{
    $breadcrumbs = new WC_Breadcrumb();
    $breadcrumb = $breadcrumbs->generate();
    $count = count($breadcrumb);

    echo '<div class="back-btn-wrap w-100">';
    if ($count > 1 && !empty($breadcrumb[$count - 2])) {
        echo '<a href="' . $breadcrumb[$count - 2][1] . '" class="backbutton box-shadow">' . __('Back to: ', 'cabling') . $breadcrumb[$count - 2][0] . '</a>';
    } else if (is_product() || is_product_category()) {
        echo '<a href="' . home_url('/products-and-services/') . '" class="backbutton box-shadow">' . __('Back to: Products & Services', 'cabling') . '</a>';
    }
    echo '</div>';
}


// Function get_customer_level() moved to inc/gi-customer.php

// Function get_master_account_id() moved to inc/gi-customer.php

// Function get_customer_type() moved to inc/gi-customer.php

// Function get_customer_type_label() moved to inc/gi-customer.php

/**
 * Account menu Customer
 *
 */
function cabling_account_menu_items()
{
    $user_id = get_current_user_id();
    $customer_level = get_customer_level($user_id);
    $customer_type = get_customer_type($user_id);
    $sap_customer = get_user_meta($user_id, 'sap_customer', true);

    $new_items = array(
        'dashboard' => __('Datwyler My Account', 'cabling'),
        'edit-account' => __('Account Information', 'cabling'),
        'edit-address' => __('Billing/Shipping Address', 'cabling'),
        'setting-account' => __('Keep Me Informed', 'cabling'),
    );

    // JM 20230913 restricted menu to master account only
    if ($customer_level === 2 && $customer_type === MASTER_ACCOUNT) {
        $new_items['users-management'] = __('User Management', 'cabling');
    }

    if (!empty($sap_customer)) {
        $new_items['sales-backlog'] = __('Purchase Orders', 'cabling');
        $new_items['inventory'] = __('Inventory, Lead Time and Pricing', 'cabling');
        $new_items['shipment'] = __('Shipments Last 12 Months ', 'cabling');
    }
    $new_items['orders'] = __('My Orders', 'cabling');
    //$new_items['products'] = __('Purchases', 'cabling');

    $new_items['quotations'] = __('My Quotes', 'cabling');

    //$new_items['messages'] = __('Messages', 'cabling');
    $new_items['buy-o-rings'] = __('Select / Buy O-rings', 'cabling');
    $new_items['request-a-quote'] = __('REQUEST A QUOTE', 'cabling');
    $new_items['contact-form'] = __('Help & Contact', 'cabling');

    // JM 20230913 added logout button
    $new_items['customer-logout'] = __('Log out', 'cabling');

    return $new_items;
}

add_filter('woocommerce_account_menu_items', 'cabling_account_menu_items', 999, 1);

function endArray($array)
{
    return end($array);
}

// Function get_cumulative_quantity() moved to inc/ga_crm.php

// Function show_value_from_api() moved to inc/ga_crm.php

/**
 * Add Customer endpoint
 */
function cabling_add_my_account_endpoint()
{
    add_rewrite_endpoint('users-management', EP_PAGES);
    add_rewrite_endpoint('setting-account', EP_PAGES);
    add_rewrite_endpoint('products', EP_PAGES);
    add_rewrite_endpoint('quotations', EP_PAGES);
    add_rewrite_endpoint('messages', EP_PAGES);
    add_rewrite_endpoint('customer-service', EP_PAGES);

    $sap_customer = get_user_meta(get_current_user_id(), 'sap_customer', true);

    // if (!empty($sap_customer)) {
    add_rewrite_endpoint('sales-backlog', EP_PAGES);
    add_rewrite_endpoint('inventory', EP_PAGES);
    add_rewrite_endpoint('shipment', EP_PAGES);
    // }
}

add_action('init', 'cabling_add_my_account_endpoint');

/**
 * Users management content
 */
function cabling_customer_endpoint_content()
{
    $user_id = get_current_user_id();
    $customer_level = get_customer_level($user_id);

    if ($customer_level === 2) {
        wc_get_template('myaccount/customer.php');
    }
}

add_action('woocommerce_account_users-management_endpoint', 'cabling_customer_endpoint_content');

/**
 * Users sales-backlog content
 */
function cabling_backlog_endpoint_content()
{
    wc_get_template('myaccount/sales-backlog.php');
}

add_action('woocommerce_account_sales-backlog_endpoint', 'cabling_backlog_endpoint_content');
/**
 * Users inventory content
 */
function cabling_inventory_endpoint_content()
{
    wc_get_template('myaccount/inventory.php');
}

add_action('woocommerce_account_inventory_endpoint', 'cabling_inventory_endpoint_content');
/**
 * Users shipment content
 */
function cabling_shipment_endpoint_content()
{
    wc_get_template('myaccount/shipment.php');
}

add_action('woocommerce_account_shipment_endpoint', 'cabling_shipment_endpoint_content');

/**
 * Products content
 */
function cabling_products_endpoint_content()
{
    wc_get_template('myaccount/products.php');
}

add_action('woocommerce_account_products_endpoint', 'cabling_products_endpoint_content');
/**
 * quotations content
 */
function cabling_quotations_endpoint_content()
{
    $user = wp_get_current_user();
    $data = RequestProductQuote::get(['email' => $user->user_email, 'order' => 'desc']);
    wc_get_template('myaccount/quotations.php', ['data' => $data]);
}

add_action('woocommerce_account_quotations_endpoint', 'cabling_quotations_endpoint_content');
/**
 * Messages contents
 */
function cabling_messages_endpoint_content()
{
    wc_get_template('myaccount/messages.php');
}

add_action('woocommerce_account_messages_endpoint', 'cabling_messages_endpoint_content');
/**
 * customer-service content
 */
function cabling_customer_service_endpoint_content()
{
    wc_get_template('myaccount/customer-service.php');
}

add_action('woocommerce_account_customer-service_endpoint', 'cabling_customer_service_endpoint_content');


// Function cabling_get_user_by_customer() moved to inc/gi-customer.php

//Custom check-out field
// Function cabling_custom_override_checkout_fields() moved to inc/product/checkout.php

//add Company Responsible Full Name field to billing address
// Function cabling_woocommerce_billing_fields() moved to inc/product/checkout.php

// Function cabling_woocommerce_shipping_fields() moved to inc/product/checkout.php

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

/**
 * Dynamically pre-populate Woocommerce checkout fields with exact named meta field
 * Eg. field 'shipping_first_name' will check for that exact field and will not fallback to any other field eg 'first_name'
 *
 * @author Joe Mottershaw | https://cloudeight.co
 */
add_filter('woocommerce_checkout_get_value', function ($input, $key) {

    global $current_user;

    // Return the user property if it exists, false otherwise
    return ($current_user->$key
        ? $current_user->$key
        : false
    );
}, 10, 2);

add_filter('woocommerce_get_catalog_ordering_args', 'am_woocommerce_catalog_orderby');
function am_woocommerce_catalog_orderby($args)
{
    //$args['meta_key'] = '_price';
    $args['orderby'] = 'date';
    $args['order'] = 'desc';
    return $args;
}

//custom woocommerce page
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', 10);


add_action('woocommerce_shop_loop_item_title', 'cabling_product_description', 15);
//add_action('woocommerce_before_shop_loop', 'cabling_product_category_heading');
add_action('woocommerce_after_my_account', 'cabling_woocommerce_after_my_account_modal', 99);

function cabling_get_product_attributes($product_id = 0): array
{
    $attributes = array(
        '_sku' => 'SKU'
    );
    $product_attributes = get_post_meta($product_id, '_product_attributes', true);
    if ($product_attributes) {
        foreach ($product_attributes as $attribute) {
            $attribute_name = str_replace('pa_', '', $attribute['name']);
            $attribute_label = str_replace('-', ' ', $attribute_name);
            $attributes[$attribute['name']] = ucwords($attribute_label);
        }
    }

    return $attributes;
}

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

    if (is_tax('product_custom_type')) {
        $product_group = get_product_group_of_type(get_queried_object_id());
        $surface_equipment_id = get_surface_equipment_id();

        if ($product_group && $surface_equipment_id === $product_group->term_id) {
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
            $is_backup_ring_group = $product_group && is_backup_ring_group($product_group->term_id);

            if ($is_backup_ring_group) {
                $list_fields = array(
                    'product_dash_number_backup_rings' => __('Dash Number', 'cabling'),
                    'inches_id_backup-ring' => __('ID', 'cabling'),
                    'inches_t_backup-ring' => __('T', 'cabling'),
                    'inches_width_backup-ring' => __('Width', 'cabling'),
                    '_sku' => __('SKU', 'cabling'),
                    'product_specifications_met' => __('Specifications Met', 'cabling'),
                    'product_operating_temp' => __('Temperature Range, °F', 'cabling'),
                    'product_colour' => __('Colour', 'cabling'),
                );
            }
        }
    }

    return $list_fields;
}

// cabling_get_product_single_attributes moved to inc/product/single.php
/*
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

*/

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

// cabling_woocommerce_description moved to inc/product/single.php

// custom_woocommerce_product_add_to_cart_text moved to inc/product/cart.php

// Function cabling_add_quote_button() moved to inc/product/quote.php

// cabling_add_quote_on_product, cabling_additional_information, cabling_related_complementary_section,
// cabling_woocommerce_pdf_document moved to inc/product/single.php

function cabling_add_quote_section()
{
    wc_get_template('single-product/product-add-quote.php');
}

function cabling_woocommerce_after_my_account_modal()
{
    wc_get_template('myaccount/popup/reset-password.php');
}

// cabling_woocommerce_find_a_stockist, cabling_get_brand_product moved to inc/product/single.php

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

add_filter('password_hint', function ($hint) {
    return __('The password should be at least 8 characters long. Use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).');
});

// Function cabling_custom_edit_account_required_fields() moved to inc/gi-customer.php

// Save the custom field when the user updates their account details
// Function save_custom_field_my_account_edit() moved to inc/gi-customer.php


function cabling_save_verify_cookie()
{
    if (!empty($_REQUEST['custom_action']) && base64_decode($_REQUEST['custom_action']) === 'verify_customer_cabling') {

        $expiration_time = time() + 30 * 60; // 30 minutes in seconds
        setcookie('verify_customer_cabling_' . $_REQUEST['id'], $_REQUEST['key'], $expiration_time);
    }
}

add_action('init', 'cabling_save_verify_cookie');

// Function cabling_password_reset_handle() moved to inc/gi-customer.php


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

	$skuType = get_query_var('skuType');
	$typeLabel = '';
	if (!empty($skuType)) {
		$typeLabel = sprintf(' - %s', ProductsFilterHelper::getSkuTypeLabel( $skuType));
        $skuTypeImageId = get_field(strtolower("sku_{$skuType}_image"), $cat);
        if ($skuTypeImageId){
            $thumbnail_id = $skuTypeImageId;
        }
	}

    get_template_part('template-parts/filter_heading', 'product', [ 'hideWelcome' => true]);
    echo wp_get_attachment_image($thumbnail_id, 'full', false, ['class' => 'my-3']);
    echo '<h1 class="woocommerce-products-header__title page-title">' . woocommerce_page_title(false) . $typeLabel .'</h1>';
}

add_action('woocommerce_archive_description', 'custom_text_before_product_listing', 5);


/**
 * @param $user_email
 * @param $customer_id
 * @return void
 */
function send_verify_email($user_email, $customer_id): void
{
    $mailer = WC()->mailer();

    $mailer->recipient = $user_email;

    //$verify_link = get_verification_user_link($customer_id);
    $verify_link = get_reset_password_user_link($customer_id);
    $type = 'emails/verify-child-account.php';
    $subject = __("Hi! Please verify your account!", 'cabling');
    $content = cabling_get_custom_email_html($verify_link, $subject, $mailer, $type);
    $headers = "Content-Type: text/html\r\n";

    $mailer->send($user_email, $subject, $content, $headers);
}

// Function get_product_category moved to inc/product-filter/filter-helper.php

function get_product_line_category(string $taxonomy = '', string $meta_key = '', array $meta_values = array(), bool $returnId = false, array $includes = [])
{
    $args = array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'include' => $includes,
        'meta_query' => array(
            //'relation' => 'OR',
            array(
                'key' => $meta_key,
                'value' => $meta_values,
                'compare' => 'IN',
            ),
        ),
    );

    $terms = get_terms($args);

    return $returnId ? wp_list_pluck($terms, 'term_id') : $terms;
}

function get_product_custom_type(array $meta_query, $returnId = false, $includes = [])
{
    $args = array(
        'taxonomy' => 'product_custom_type',
        'hide_empty' => false,
        'include' => $includes,
        'meta_query' => array(),
    );

    if (!empty($meta_query)){
        foreach ($meta_query as $query){
            if (empty($query)){
                continue;
            }
            foreach ($query as $meta_key => $meta_values){
                $args['meta_query'][] = array(
                    'key' => $meta_key,
                    'value' => $meta_values['value'],
                    'compare' => $meta_values['compare'],
                );
            }
        }
    }

    $terms = get_terms($args);

    return $returnId ? wp_list_pluck($terms, 'term_id') : $terms;
}

function get_product_type_category(string $meta_value = '')
{
    global $wpdb;
    $meta_key = 'product_line';
    $taxonomy = 'product_custom_type';

    // Custom SQL query to retrieve terms with specific metadata
    $terms = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT t.*, tt.* FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            INNER JOIN {$wpdb->termmeta} tm ON t.term_id = tm.term_id
            WHERE tt.taxonomy = %s AND tm.meta_key = %s AND tm.meta_value = %s",
            $taxonomy,
            $meta_key,
            $meta_value
        )
    );

    return $terms;
}

// Function get_product_category_list moved to inc/product-filter/filter-helper.php

// Function get_product_ids_by_category moved to inc/product-filter/filter-helper.php

if (!function_exists('custom_compare')) {
    function custom_compare($a, $b)
    {
        if (!is_string($a) || !is_string($b)) {
            return 0;
        }

        $pattern = '/-?\d+/';
        preg_match_all($pattern, $a, $matches_a);
        preg_match_all($pattern, $b, $matches_b);

        if (empty($matches_a[0]) || empty($matches_b[0])) {
            return 0;
        }

        $max_a = max($matches_a[0]);
        $max_b = max($matches_b[0]);

        return $max_a <=> $max_b;
    }
}


// Function get_filter_lists moved to inc/product-filter/filter-helper.php

// Function clear_filter_lists_cache and add_action('acf/save_post', 'clear_filter_lists_cache') moved to inc/product-filter/filter-helper.php

// Function get_all_meta_values_cached moved to inc/product-filter/filter-helper.php

// Function get_acf_post_options moved to inc/product-filter/filter-helper.php

// Function get_acf_taxonomy_options moved to inc/product-filter/filter-helper.php

function get_term_ids_by_attributes(array $product_ids, string $taxonomy = 'product_group'): array
{

    //$product_ids = search_product_by_meta($metas);

    if (empty($product_ids)) {
        return [];
    }

    $terms = get_terms_by_product($taxonomy, $product_ids);
    /*if ($taxonomy == 'compound_certification') {
var_dump($product_ids);
}*/
    return $terms;
}

/**
 * @param string $taxonomy
 * @param array $product_ids
 * @return array
 */
function get_terms_by_product(string $taxonomy, array $product_ids): array
{
    global $wpdb;

    $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));

    $sql = $wpdb->prepare("
        SELECT DISTINCT tt.term_id
        FROM {$wpdb->term_taxonomy} AS tt
        LEFT JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE tt.taxonomy = %s AND tr.object_id IN ({$placeholders})
    ", $taxonomy, ...$product_ids);

    $results = $wpdb->get_results($sql);
    /*if ($taxonomy == 'compound_certification') {
var_dump($sql);
}*/
    if ($wpdb->last_error) {
        error_log('Database error: ' . $wpdb->last_error);
        return [];
    }

    return $results ? wp_list_pluck($results, 'term_id') : [];
}


function search_product_by_meta(array $metas, $group): array
{
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => array(
            'relation' => 'AND',
        )
    );

    if (!empty($group)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_group',
                'field' =>'term_id',
                'terms' => $group,
            )
        );
    }

    foreach ($metas as $meta_key => $meta_values) {
        if ($meta_key === 'compound_certification') {
            continue;
        }
        if (empty($meta_values)) {
            continue;
        }
        if (is_array($meta_values) && sizeof($meta_values)) {
            $meta_array = array(
                'relation' => 'OR',
            );
            if ($meta_key === 'product_compound' || $meta_key === 'product_dash_number' || $meta_key === 'product_dash_number_backup_rings') {
                $meta_array[] = array(
                    'key' => $meta_key,
                    'value' => $meta_values,
                    'compare' => 'IN',
                );
            } else {
                foreach ($meta_values as $value) {
                    $meta_array[] = array(
                        'key' => $meta_key,
                        'value' => $value,
                        'compare' => 'LIKE'
                    );
                }
            }

            $args['meta_query'][] = $meta_array;
        } else {
            $args['meta_query'][] = array(
                'key' => $meta_key,
                'value' => $meta_values,
                'compare' => '=',
            );
        }
    }
    //wp_send_json_success($args);
    $posts = get_posts($args);
    return $posts;
}

// Function redirect_on_product_type and add_action('template_redirect', 'redirect_on_product_type') moved to inc/product-filter/filter-helper.php

// Function cabling_change_product_query and add_action('woocommerce_product_query', 'cabling_change_product_query') moved to inc/product-filter/filter-helper.php

// Function get_meta_query_from_attributes moved to inc/product-filter/filter-helper.php

// Function selected_filter moved to inc/product-filter/filter-helper.php

// Function show_product_filter_input_name moved to inc/product-filter/filter-helper.php

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

add_action('wp_enqueue_scripts', 'remove_woocommerce_gallery_scripts', 99);
function remove_woocommerce_gallery_scripts()
{
    if (function_exists('is_product') && is_product()) {
        wp_dequeue_style('photoswipe');
        wp_dequeue_style('wc-photoswipe');
        wp_dequeue_style('photoswipe-default-skin');
        wp_dequeue_script('photoswipe');
        wp_dequeue_script('wc-gallery');
        wp_dequeue_script('photoswipe-ui-default');
    }
}

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

// Function woocommerce_no_products_quote() moved to inc/product/quote.php


/**
 * @param array $data
 * @param array $termFilters
 * @return array|null
 */
function get_available_attributes(array $product_ids): ?array
{
    try {
        if (empty($product_ids)) {
            return null;
        }
        global $wpdb;

        $post_ids_placeholder = implode(',', array_fill(0, count($product_ids), '%d'));
        //get only values for product acf fields
        $meta_keys = array(
            'inches_id',
            'inches_width',
            'inches_od',
            'milimeters_id',
            'milimeters_od',
            'milimeters_width',
            'product_contact_media',
            'product_min',
            'product_max',
            //'product_dash_number',
            'product_colour',
            'product_compound',
            'product_complance',
            'product_material',
            'product_hardness',
            //'product_dash_number_backup_rings',
            'inches_id_backup-ring',
            'inches_t_backup-ring',
            'inches_width_backup-ring'
        );

        $meta_key_placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));

        $query = $wpdb->prepare(
            "SELECT meta_key, meta_value
                        FROM $wpdb->postmeta
                        WHERE post_id IN ($post_ids_placeholder)
                        AND meta_key IN ($meta_key_placeholders)
                        ORDER BY meta_value ASC",
            array_merge($product_ids, $meta_keys)
        );

        $meta_values = $wpdb->get_results($query, ARRAY_A);

        $resultMetas = array();

        foreach ($meta_values as $meta) {
            if (empty($meta['meta_value']) || $meta['meta_value'] == 'null') {
                continue;
            }

            if (!isset($resultMetas[$meta['meta_key']])) {
                $resultMetas[$meta['meta_key']] = array();
            }

            if (in_array($meta['meta_value'], $resultMetas[$meta['meta_key']])) {
                continue;
            }

            $unserializedData = unserialize($meta['meta_value']);

            if ($unserializedData === false) {
                $resultMetas[$meta['meta_key']][] = $meta['meta_value'];
            } else {
                foreach ($unserializedData as $val) {
                    if (in_array($val, $resultMetas[$meta['meta_key']])) {
                        continue;
                    }
                    $resultMetas[$meta['meta_key']][] = $val;
                }
            }
        }
        //we must get the certifications of compound
        //$resultMetas['product_compound'] = $data['compound_certification'];

        return $resultMetas;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
    return null;
}

// Function company_name_field() moved to inc/ga_crm.php

// Function get_name_title() moved to inc/ga_crm.php

// Function get_product_of_interests() moved to inc/ga_crm.php

// Function get_desired_applications() moved to inc/ga_crm.php

// Function product_of_interest_field() moved to inc/ga_crm.php

// Function product_desired_application_field() moved to inc/ga_crm.php

// Function product_material_field() moved to inc/ga_crm.php

// Function product_harness_field() moved to inc/ga_crm.php

function product_address_state_field()
{
    $option = '<option value="">' . __('Choose state', 'woocommerce') . '</option>';

    $field = '<select name="billing_state" id="billing_postcode" class="select form-select" required>' . $option . '</select>';

    return '<div class="w-100 form-group has-focus">' . $field . '<label for="billing_postcode">State<span                                             class="required">*</span></label></div>';
}

// Function show_product_field() moved to inc/ga_crm.php

// Function show_input_field() moved to inc/ga_crm.php

function debug_log($subject, $body)
{
    //wp_mail('michael.santos@infolabix.com,jose.martins@infolabix.com', $subject, $body);
}

// Function show_product_filter_input_value moved to inc/product-filter/filter-helper.php

// Function get_product_filter_link moved to inc/product-filter/filter-helper.php

// Function woocommerce_add_error_callback() moved to inc/gi-customer.php

// Function gi_retrieve_password_callback() moved to inc/gi-customer.php

// Function gi_custom_reset_password_heading() moved to inc/gi-customer.php

// Function gi_woocommerce_get_terms_and_conditions_checkbox_text() moved to inc/product/checkout.php

#ref GID-1044
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);

/*
We already use this on wp-content\themes\cabling\inc\sap_create_order.php
add_action( 'woocommerce_before_calculate_totals', 'custom_change_cart_item_prices', 10, 1 );
function custom_change_cart_item_prices( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    if ( WC()->cart->is_empty() ) {
        return;
    }

    if ( is_cart() ) {
        return;
    }

    $webServices = new GIWebServices();
    $user_plant = get_user_meta(get_current_user_id(), 'sales_org', true);
    $sap_no = get_user_meta(get_current_user_id(), 'sap_customer', true);

    foreach ( $cart->get_cart() as $cart_item ) {
        $priceParams = array(
            array(
                'Field' => 'SalesOrganization',
                'Value' => empty($user_plant) ? '2141' : $user_plant,
                'Operator' => 'and',
            ),
            array(
                'Field' => 'Material',
                'Value' => $cart_item['data']->get_sku(),
                'Operator' => '',
            ),
            array(
                'Field' => '(Customer',
                'Sign' => 'eq',
                'Value' => $sap_no,
                'Operator' => 'or',
            ),
            array(
                'Field' => 'Customer',
                'Sign' => 'eq',
                'Value' => "",
                'Operator' => ')',
            )
        );

        $responsePrice = $webServices->makeApiRequest('GET_DATA_PRICE_CDS', $priceParams);
        $dataPrice = $webServices->getDataResponse($responsePrice, 'ZDD_I_SD_PIM_MaterialPrice', 'ZDD_I_SD_PIM_MaterialPriceType');

        if ($dataPrice){
            $price = floatval($dataPrice[0]['Price100']) / 100;
            foreach ($dataPrice as $priceAPI){
                $scaleFrom = intval($priceAPI['ScaleFrom']);
                $scaleTo = intval($priceAPI['ScaleTo']);

                if(($cart_item['quantity'] <= $scaleTo) && ($cart_item['quantity'] >= $scaleFrom)){
                    $price = floatval($priceAPI['Price100']) / 100;
                }
            }

            $cart_item['data']->set_price( $price  );
        }
    }
}
*/


// add_action('woocommerce_checkout_update_order_review', 'cabling_woocommerce_checkout_update_order_review');
// function cabling_woocommerce_checkout_update_order_review($posted_data){
//     $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods');
//     $_POST['shipping_method'] = $chosen_shipping_methods;
// }

// gi_add_to_cart_redirect, add_price_suffix moved to inc/product/cart.php

add_filter('woocommerce_get_endpoint_url', 'gi_woocommerce_account_menu_item_link', 10, 4);
function gi_woocommerce_account_menu_item_link($url, $endpoint, $value, $permalink)
{
    if ($endpoint === 'buy-o-rings') {
        $buy_online_link = get_field('buy_online_link', 'option');
        $url = $buy_online_link;
    }
    return $url;
}

// GID-1219
// Function remove_session_notices_from_account_page() moved to inc/gi-customer.php

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

// custom_add_to_cart_message, gi_continue_shopping_redirect, remove_same_product_before_add_to_cart
// moved to inc/product/cart.php

// === Surface Equipment functions (migrated from dev) ===

function custom_text_heading_before_product_listing()
{
    $skuType = get_query_var('skuType');
    $heading = __('Products Available', 'cabling');
    if (!empty($skuType) && $skuType === ProductsFilterHelper::SKU_PRODUCT) {
        $heading = __('Assemblies Available', 'cabling');
    }

    echo '<h4>' . $heading . '</h4>';
}

add_action('woocommerce_archive_description', 'custom_text_heading_before_product_listing', 15);

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

add_action('acf/init', function() {
    if ( ! function_exists('acf_add_local_field_group') ) {
        return;
    }

    // Gather all SKU types (PRODUCT + replacements)
    $sku_types = [ ProductsFilterHelper::SKU_PRODUCT ];
    $sku_types = array_merge( $sku_types, array_values( ProductsFilterHelper::SKU_REPLACEMENTS ) );

    // Build one image field per SKU type
    $fields = [];
    foreach ( $sku_types as $sku ) {
        $label = ProductsFilterHelper::getSkuTypeLabel( $sku );

        $fields[] = [
            'key'               => "field_{$sku}_image",
            'label'             => "{$label} Image",
            'name'              => strtolower("sku_{$sku}_image"),
            'type'              => 'image',
            'instructions'      => sprintf( __('Upload the image for %s.', 'cabling'), $label ),
            'required'          => 0,
            'return_format'     => 'id',
            'preview_size'      => 'medium',
            'library'           => 'all',
        ];
    }

    // Register the ACF field group
    acf_add_local_field_group([
        'key'      => 'group_sku_type_images',
        'title'    => __('SKU Type Images', 'cabling'),
        'fields'   => $fields,
        'location' => [
            [
                [
                    'param'    => 'taxonomy',
                    'operator' => '==',
                    'value'    => 'product_custom_type',
                ],
            ],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => '',
    ]);
});

