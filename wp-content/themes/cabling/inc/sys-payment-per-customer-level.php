<?php
/*
For lvl 1 customers only payment via credit card is available
For lvl 2 customers, payment can be by credit card or direct transfer
*/
add_filter('woocommerce_available_payment_gateways', 'custom_payment_gateway_based_on_level');
function custom_payment_gateway_based_on_level($available_gateways) {
    $user_id = get_current_user_id();
    $customer_level = get_customer_level($user_id);
    if( $customer_level == 1 ){
        $available_gateways = [
            'chase_paymentech' => $available_gateways['chase_paymentech']
        ];
    }
    return $available_gateways;
}