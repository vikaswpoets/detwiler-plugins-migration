<?php

#ref GID-1044
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);

add_filter( 'woocommerce_checkout_fields', 'custom_override_default_address_fields' );
function custom_override_default_address_fields( $fields ) {
	$fields['billing']['billing_postcode']['validate'] = array();

	return $fields;
}

// remove vat
function remove_vat_for_specific_users( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	$user_id                  = get_current_user_id();
	$user_wp9_form            = get_user_meta( $user_id, 'user_wp9_form', true );
	$user_certificate_form    = get_user_meta( $user_id, 'user_certificate_form', true );
	$check_remove_tax_by_file = $user_wp9_form && $user_certificate_form ? true : false;
	//JM 20240910 added customer lvl check to remove tax
	if ( $check_remove_tax_by_file || get_customer_level( $user_id ) == 2 ) {
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$cart_item['data']->set_tax_class( 'zero-rate' );
			// $cart_item['data']->save();
		}
	}
}

add_action( 'woocommerce_before_calculate_totals', 'remove_vat_for_specific_users', 99, 1 );

if ( ! is_user_logged_in() ) {
	add_filter( 'woocommerce_get_price_html', 'showHTMLPriceGuest', 1, 2 );
}
//Custom check-out field
add_filter('woocommerce_checkout_fields', 'cabling_custom_override_checkout_fields');
function cabling_custom_override_checkout_fields($fields)
{
    $fields['billing']['billing_address_1']['label'] = __('Address Line 1', 'cabling');
    $fields['billing']['billing_address_1']['class'] = array('form-row-first');

    $fields['billing']['billing_address_2']['class'] = array('form-row-last');
    $fields['billing']['billing_address_2']['label'] = __('Address Line 2', 'cabling');

    $fields['billing']['billing_city']['label'] = __('City', 'cabling');
    $fields['billing']['billing_city']['class'] = array('form-row-last');

    $fields['billing']['billing_postcode']['label'] = __('Postcode', 'cabling');
    $fields['billing']['billing_postcode']['required'] = true;
    $fields['billing']['billing_postcode']['class'] = array('form-row-first');

    $fields['billing']['billing_company']['label'] = __('Company', 'cabling');
    $fields['billing']['billing_company']['required'] = true;
    $fields['billing']['billing_company']['class'] = array('form-row-first');

    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_email']);
    unset($fields['billing']['billing_phone']);

    return $fields;
}

//add Company Responsible Full Name field to billing address
add_filter('woocommerce_billing_fields', 'cabling_woocommerce_billing_fields');
function cabling_woocommerce_billing_fields($fields)
{
    $fields['company_vat'] = array(
        'label' => __('VAT Number', 'cabling'),
        'placeholder' => _x('VAT Number', 'placeholder', 'cabling'),
        'required' => false,
        'clear' => false,
        'type' => 'text',
        'class' => array('form-row-last'),
        'priority' => 36
    );

    return $fields;
}

add_filter('woocommerce_shipping_fields', 'cabling_woocommerce_shipping_fields');
function cabling_woocommerce_shipping_fields($fields)
{
    //var_dump($fields);
    $fields['shipping_address_1']['label'] = __('Address Line 1', 'cabling');
    $fields['shipping_address_2']['label'] = __('Address Line 2', 'cabling');

    $fields['shipping_city']['label'] = __('City', 'cabling');

    $fields['shipping_postcode']['label'] = __('Postcode', 'cabling');
    //$fields['shipping_postcode']['required'] = true;

    $fields['shipping_company']['label'] = __('Company', 'cabling');
    $fields['shipping_company']['required'] = true;

    return $fields;
}

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


add_filter('woocommerce_get_terms_and_conditions_checkbox_text', 'gi_woocommerce_get_terms_and_conditions_checkbox_text');
function gi_woocommerce_get_terms_and_conditions_checkbox_text($text)
{
    $text = __('I confirm all details are correct', 'cabling');
    return $text;
}


// attach id cabling order
function attach_id_cabling_order( $order_id, $order ) {
	$attach_id_cabling = isset( $_SESSION['attach_id_cabling'] ) ? $_SESSION['attach_id_cabling'] : '';

	if ( $order_id && ! empty( $attach_id_cabling ) ) {
		update_post_meta( $order_id, 'attach_id_cabling', $attach_id_cabling );
		if ( get_current_user_id() ) {
			update_user_meta( get_current_user_id(), 'attach_id_cabling', $attach_id_cabling );
		}
		unset( $_SESSION['attach_id_cabling'] );
	}
}

add_action( 'woocommerce_new_order', 'attach_id_cabling_order', 10, 2 );
