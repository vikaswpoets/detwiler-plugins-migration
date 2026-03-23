<?php
/*
User registration Addresses  - invoicing and shipping, by default are the same
*/
add_action('gi_created_new_customer','gi_created_new_customer_add_address',10);
function gi_created_new_customer_add_address($post_data){
    $user_id = $post_data['customer_id'];
    $address = array(
        'shipping_first_name' => $post_data['first-name'],
        'shipping_last_name' => $post_data['last-name'],
        'shipping_phone' => $post_data['billing_phone'],
        'shipping_company' => $post_data['company-name'],
        'shipping_address_1' => $post_data['billing_address_1'],
        'shipping_country' => $post_data['billing_country'],
        'shipping_city' => $post_data['billing_city'],
        'shipping_state' => $post_data['billing_state'],
        'shipping_postcode' => $post_data['billing_postcode'],
    );
    $type = 'shipping';
    $custom_addresses = get_user_meta($user_id, THMAF_Utils::ADDRESS_KEY, true);
    $saved_address = THMAF_Utils::get_custom_addresses($user_id, $type);
    if(!is_array($saved_address)) {
        if(!is_array($custom_addresses)) {
            $custom_addresses = array();
        }
        $custom_address = array();
        $default_address = THMAF_Utils::get_default_address($user_id, $type);
        $custom_address['address_0'] = $default_address;
        $custom_address['address_1'] = $address;
        $custom_addresses[$type] = $custom_address;
    }else {
        if(is_array($saved_address)) {
            if(isset($custom_addresses[$type])) {
                $exist_custom = $custom_addresses[$type];
                $new_key_id = THMAF_Utils::get_new_custom_id($user_id, $type);
                $new_key = 'address_'.esc_attr($new_key_id);
                $custom_address[$new_key] = $address;
                $custom_addresses[$type] = array_merge($exist_custom, $custom_address);
            }
        }
    }
    update_user_meta($user_id, THMAF_Utils::ADDRESS_KEY, $custom_addresses);
}

/*
Validate zipcode when add new address
*/
add_action( 'woocommerce_after_save_address_validation', 'gi_validate_zipcode', 9, 3 );
function gi_validate_zipcode( $user_id, $load_address, $address ) {
    $address_arr = [
        'shipping_country' => @$_REQUEST['shipping_country'],
        'shipping_address_1' => @$_REQUEST['shipping_address_1'],
        'shipping_city' => @$_REQUEST['shipping_city'],
        'shipping_state' => @$_REQUEST['shipping_state'],
        'shipping_postcode' => @$_REQUEST['shipping_postcode'],
    ];
    $do_check = true;
    foreach( $address_arr as $add_key => $add_val ){
        if($add_key != 'shipping_address_1'){
            if($add_val == ''){
                $do_check = false;
            }
        }
    }
    if($do_check){
        $api_key = get_field('google_api_key', 'options');
        if(!$api_key){
            $SysFedExApi = new SysFedExApi();
            $res = $SysFedExApi->validate_address($address_arr);
            if( !isset($res['postal_code']) ){
                wc_add_notice( __( 'Please enter a valid address.', 'woocommerce' ), 'error' );
            }
        }
    }
    
}
