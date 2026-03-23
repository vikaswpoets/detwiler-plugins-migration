<?php
function enqueue_custom_gtag_script() {
    wp_enqueue_script( 'custom-gtag-script', get_template_directory_uri() . '/assets/js/custom-gtag.js', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_gtag_script' );

function create_ga_crm_post_type() {
    $labels = array(
        'name'               => 'GA Tracking',
        'singular_name'      => 'GA Tracking',
        'menu_name'          => 'GA Tracking',
        'name_admin_bar'     => 'GA Tracking',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New GA Tracking',
        'new_item'           => 'New GA Tracking',
        'edit_item'          => 'Edit GA Tracking',
        'view_item'          => 'View GA Tracking',
        'all_items'          => 'All GA Tracking',
        'search_items'       => 'Search GA Tracking',
        'parent_item_colon'  => 'Parent GA Tracking:',
        'not_found'          => 'No GA Tracking found.',
        'not_found_in_trash' => 'No GA Tracking found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'menu_icon'          => 'dashicons-businessman',
        'supports'           => array( 'title'),
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'has_archive'        => false,
        'query_var'          => false,
        'rewrite'            => false,
    );

    register_post_type( 'ga_crm', $args );
}
add_action( 'init', 'create_ga_crm_post_type' );

function save_utm_to_database() {
    //JM changed condition to create cookie even if it misses any parameter
    if ( isset( $_GET['utm_source'] ) || isset( $_GET['utm_medium'] ) || isset( $_GET['utm_campaign'] ) || isset( $_GET['utm_term'] ) || isset( $_GET['utm_content'] ) || isset( $_GET['utm_id'] ) ) {
        $utm = array(
            'utm_source'   => sanitize_text_field( $_GET['utm_source'] ),
            'utm_medium'   => sanitize_text_field( $_GET['utm_medium'] ),
            'utm_campaign' => sanitize_text_field( $_GET['utm_campaign'] ),
            'utm_term'     => sanitize_text_field( $_GET['utm_term'] ),
            'utm_content'  => sanitize_text_field( $_GET['utm_content'] ),
            'utm_id'  => sanitize_text_field( @$_GET['utm_id'] ),
        );
        if ( ! isset( $_SESSION['saved_utm'] ) ) {
            $args = array(
                'post_type'    => 'ga_crm',
                'post_status'  => 'publish',
                'post_title'   => 'GA Tracking '.date('Y-m-d H:i:s'),
            );
            $post_id = wp_insert_post( $args );
            //$cookie_expire = time() + (10 * 365 * 24 * 60 * 60);
            $cookie_expire = time() + (10 * 24 * 60 * 60);
            if ( $post_id ) {
                foreach ( $utm as $key => $value ) {
                    // Store into database
                    update_post_meta( $post_id, $key, $value );
                    // Store into cookie
                    setcookie($key, $value, $cookie_expire, "/");
                }
                // Logic to send to CRM

                // Make sure to send 1 time
                $_SESSION['saved_utm'] = true;
            }
        }
    }
}
add_action( 'template_redirect', 'save_utm_to_database' );


function company_name_field()
{
    $departments = CRMConstant::FUNCTION_FIELD;

    asort($departments);

    if (isset($_REQUEST['company-sector'])) {
        $company = $_REQUEST['company-sector'];
    } elseif (is_user_logged_in()) {
        $company = esc_attr(get_user_meta(get_current_user_id(), 'company-sector', true));
    } else {
        $company = '';
    }
    return show_product_field('company-sector', array(
        'options' => $departments,
        'label' => __('Company Sector', 'woocommerce'),
        'default' => $company,
        'class' => ' form-group has-focus mb-3',
        'required' => true
    ));
}

function get_name_title($value = null)
{
    $titles = CRMConstant::TITLE;
    if (!empty($value)) {
        return array_search($value, $titles);
    }
    return $titles;
}

function get_product_of_interests($value = null)
{
    $product_of_interests = CRMConstant::PRODUCT;
    if (!empty($value)) {
        $id = array_search($value, $product_of_interests);

        return (string)$id ?? '';
    }
    return $product_of_interests;
}

function get_desired_applications($value = null)
{
    $desired_applications = CRMConstant::COMPOUND;
    if (!empty($value)) {
        return in_array($value, $desired_applications) ? $value : '';
    }
    return $desired_applications;
}

function product_of_interest_field($value = '')
{
    $product_of_interests = get_product_of_interests();

    $field = '';
    $options = '<option value="">' . __('Choose an option', 'woocommerce') . '</option>';
    foreach ($product_of_interests as $option_text) {
        $options .= '<option value="' . esc_attr($option_text) . '" ' . selected($value, $option_text, false) . '>' . esc_html($option_text) . '</option>';
    }

    $field .= '<select name="product-of-interest" id="product-of-interest" class="select form-select" required>' . $options . '</select>';

    echo '<p class="form-row w-100"><label for="product-of-interest">' . __('What Datwyler product are you most interested in?', 'woocommerce') . '<span class="required">*</span></label>' . $field . '</p>';
}

function product_desired_application_field($value = '')
{
    echo show_product_field('o_ring[desired-application]', array(
        'options' => CRMConstant::COMPOUND,
        'label' => __('Desired Application', 'woocommerce'),
        'default' => $value
    ));
}

function product_material_field($value = '')
{
    echo show_product_field('o_ring[material]', array(
        'options' => CRMConstant::MATERIAL,
        'label' => __('Material', 'woocommerce'),
        'default' => $value
    ));
}

function product_harness_field($value = '')
{
    echo show_product_field('o_ring[hardness]', array(
        'options' => CRMConstant::HARDNESS,
        'label' => __('Hardness', 'woocommerce'),
        'default' => $value
    ));
}

function show_product_field($name, $options = array()): string
{
    $default = $options['default'] ?? '';
    $id = $options['id'] ?? $name;
    $required = empty($options['required']) ? '' : 'required';
    $requiredLabel = empty($options['required']) ? '' : '<span class="required">*</span>';
    $option = '<option value="">' . __('Choose an option', 'woocommerce') . '</option>';
    foreach ($options['options'] as $key => $option_text) {
        $selectKey = empty($options['key']) ? $option_text : $key;
        $option .= '<option value="' . esc_attr($selectKey) . '" ' . selected($default, $selectKey, false) . '>' . esc_html($option_text) . '</option>';
    }

    $field = '<select name="' . $name . '" id="' . $id . '" class="select form-select" ' . $required . '>' . $option . '</select>';

    return '<div class="w-100 form-group has-focus' . ($options['class'] ?? '') . '">' . $field . '<label for="' . $name . '">' . $options['label'] . $requiredLabel . '</label></div>';
}

function show_input_field($name, $options = array())
{
    $type = $options['type'] ?? 'text';
    switch ($type) {
        case 'country':
        case 'state':
            break;
        case 'hidden';
            $options['return'] = true;
            $field = woocommerce_form_field($name, $options);
            break;
        default:
            $value = empty($options['value']) ? '' : $options['value'];
            $required = empty($options['required']) ? '' : 'required';
            $requiredLabel = empty($options['required']) ? '' : '<span class="required">*</span>';
            $class = is_array($options['class']) ? implode(' ', $options['class']) : ' ';

            $input = '<input type="' . $type . '" name="' . $name . '" id="' . $name . '"" value="' . $value . '" class="form-control" ' . $required . '/>';

            $field = '<div class="form-group mb-3 ' . $class . '">' . $input . '<label for="' . $name . '">' . $options['label'] . '&nbsp;' . $requiredLabel . '</label></div>';
            break;
    }
    if (empty($options['return'])) {
        echo $field;
    } else {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return $field;
    }
}

function get_cumulative_quantity($stock, float $quantity): string
{
    if (empty($stock)) {
        $stock = 0;
    }

	return $stock + $quantity;
}

function show_value_from_api($key, $value)
{
    if (empty($value)) {
        return '-';
    }

    if ($key === 'ScaleTo' && $value == '999999.00') {
        return '-';
    }

    $numberKeys = array(
        'OpenConfdDelivQtyInBaseUnit',
        'StockQuantity',
        'ScaleFrom',
        'ScaleTo',
        'OrderQuantity',
        'OpenConfdDelivQtyInBaseUnit',
    );

    if (in_array($key, $numberKeys)) {
        return number_format($value, 0, '.', ' ');
    }

    if ($key === 'RemainingValue') {
        return number_format($value, 2, '.', ' ');
    }

    if (str_contains($key, 'CureDate')) {
        return $value;
    }

    if (str_contains($key, 'Date')) {
        $dateTime = new DateTime($value);

        return $dateTime->format("m/d/Y");
    }

    $priceKeys = array(
        'NetPriceAmount',
        'ScalePrice',
        'MinPrice',
    );

    if (in_array($key, $priceKeys)) {
        return '$' . number_format($value, 2, '.', ' ');
    }

    return $value;
}
