<?php
/*
When order create, we create order on SAP
#GID-1102
Endpoint: CREATE/SALESORDER

create order on SAP when order status Pending, Processing, On-hold, Complete
- Payment Method: Direct bank transfer
- Payment Method: Chase => Status Processing
*/

add_action('woocommerce_cart_loaded_from_session', 'restore_old_cart');
function restore_old_cart() {
    $old_cart = WC()->session->get('old_cart');
    if (!empty($old_cart)) {
        WC()->cart->empty_cart();
        foreach ($old_cart as $cart_item_key => $cart_item) {
            WC()->cart->add_to_cart(
                $cart_item['product_id'],
                $cart_item['quantity'],
                isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0,
                isset($cart_item['variation']) ? $cart_item['variation'] : array(),
                isset($cart_item['cart_item_data']) ? $cart_item['cart_item_data'] : array()
            );
        }
        WC()->session->set('old_cart', null);
    }
}

add_action('woocommerce_checkout_order_processed', 'capture_order_id_before_cart_emptied', 10, 1);
function capture_order_id_before_cart_emptied($order_id) {
    $cart_contents = WC()->cart->get_cart();
    WC()->session->set('old_cart', $cart_contents);
}
add_action('woocommerce_thankyou', 'sap_create_order_on_sap_once', 10);
function sap_create_order_on_sap_once($order_id){
    // Make sure we just create order 1 time
    $order = wc_get_order( $order_id );
    $order_status = $order->get_status();
    $target_statuses = array( 'pending', 'processing', 'on-hold', 'completed' );
    if ( ! get_post_meta( $order_id, '_sap_order_created', true ) ) {
        // If order statuses is allow
        if ( in_array( $order_status, $target_statuses ) ) {
            sap_create_order_on_sap($order_id, $order);
            update_post_meta( $order_id, '_sap_order_created',1);
        }
    }

    // Save tax_rate_applied into order info
    $tax_rate = 0;
    foreach ( $order->get_items('tax') as $item_id => $item ) {
        $tax_rate_id = $item->get_rate_id();
        $rate = WC_Tax::get_rate_percent( $tax_rate_id );
        if ( !empty( $rate ) ) {
            $tax_rate = $rate;
            break;
        }
    }
    update_post_meta( $order_id, 'tax_rate_applied',$tax_rate);
}
function sap_create_order_on_sap($order_id,$order){
    //$carrier_id = isset($_SESSION['carrier_id']) ? $_SESSION['carrier_id'] : '';
    $user_id    = get_current_user_id();
    $user_plant = get_user_meta($user_id, 'sales_org', true);
    //$user_plant = $user_plant ? $user_plant : '2130';
	$user_plant = $user_plant ? $user_plant : '2141';
    //$user_plant = '2130';
    $sap_no     = get_user_meta($user_id, 'sap_customer', true);
    $sap_no = $sap_no ? $sap_no : '1024495';
    if ($sap_no=='1024495'){
        $carrier_id='1017279';
    }else{
        $carrier_id = isset($_SESSION['carrier_id']) ? $_SESSION['carrier_id'] : '';
    }

    $customer_level = get_customer_level($user_id);

    $GIWebServices = new GIWebServices();
    $ORDER_PARTNERS = [
        array(
            'PARTN_ROLE' => 'AG',
            'PARTN_NUMB' => $sap_no
        ),
        array(
            'PARTN_ROLE' => 'WE',
            'PARTN_NUMB' => $sap_no
        ),
        array(
            'PARTN_ROLE' => 'RE',
            'PARTN_NUMB' => $sap_no
        ),
        array(
            'PARTN_ROLE' => 'RG',
            'PARTN_NUMB' => $sap_no
        )
    ];
    if($customer_level == 1){
        $NAME = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        $STREET = $order->get_shipping_address_1();
        $COUNTRY = $order->get_shipping_country();
        $POSTL_CODE = $order->get_shipping_postcode();
        $CITY = $order->get_shipping_city();
        $REGION = $order->get_shipping_state();

        foreach( $ORDER_PARTNERS as $p_key => $ORDER_PARTNER ){
            $ORDER_PARTNERS[$p_key]["NAME"] = $NAME;
            $ORDER_PARTNERS[$p_key]["STREET"] = $STREET;
            $ORDER_PARTNERS[$p_key]["COUNTRY"] = $COUNTRY;
            $ORDER_PARTNERS[$p_key]["POSTL_CODE"] = $POSTL_CODE;
            $ORDER_PARTNERS[$p_key]["CITY"] = $CITY;
            $ORDER_PARTNERS[$p_key]["REGION"] = $REGION;
            $ORDER_PARTNERS[$p_key]["TRANSPZONE"] = "US".$REGION;
            $ORDER_PARTNERS[$p_key]["TAXJURCODE"] = $REGION."0000000";
            $ORDER_PARTNERS[$p_key]["LANGU"] = "EN";
        }
        $ORDER_PARTNERS[] = [
            "PARTN_ROLE" => "SP",
            "PARTN_NUMB" => $carrier_id
        ];
    }

    $rates = array();
    $rateapplied=0;
    foreach ( $order->get_items('tax') as $item_id => $item ) {
        $tax_rate_id = $item->get_rate_id();
        $rates = WC_Tax::get_rate_percent_value( $tax_rate_id );
        if(!empty($rates)){
            $rateapplied=1;
            break;
        }
    }

    $po_number = get_post_meta($order_id, '_po_number', true);
    $PURCH_NO_C = $order_id;
    if($po_number){
        $PURCH_NO_C = $order_id." // " . $po_number;
    }
    $payload = array(
        'root' => array(
            'ORDER_HEADER_IN' => array(
                'DOC_TYPE' => 'TA',
                'SALES_ORG' => $user_plant,
                'DISTR_CHAN' => '10',
                'DIVISION' => '01',
                'CUST_GROUP' => '02',
                'PO_METHOD' => 'WEB',
                'PURCH_NO_C' => $PURCH_NO_C,
                "PURCH_DATE" => date('Ymd'),
                "ALTTAX_CLS" => $rateapplied,
                "BILL_BLOCK" => $rateapplied==1?"03":""
            ),
            'ORDER_ITEMS_IN' => [],
            'ORDER_SCHEDULES_IN' => [],
            'ORDER_PARTNERS' => $ORDER_PARTNERS
        )
    );


    //if($customer_level == 1){
        //Send email and language
        $current_user = wp_get_current_user();

        $payload['root']['ORDER_TEXT'] = [
            [
                "ITM_NUMBER" => "00000",
                "TEXT_ID"=> "TX01",
                "LANGU" => "EN",
                "TEXT_LINE"=> $current_user->user_email
            ]
        ];
    //}
    // add Customer Reference with order number in our system

    $i = 1;
    $order_items = [];
    $productId = 0;
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if (empty($productId)){
            $productId = $product->get_id();
        }
        $payload['root']['ORDER_ITEMS_IN'][] = [
            "ITM_NUMBER" => sprintf("%06d", $i*10),
            //"MATERIAL" => $product->get_sku(),
            "MATERIAL" => $item['alternate_sku'],
            'TARGET_QTY' => $item->get_quantity(),// * 1000,
            //'CUST_MAT35' => '', //CUST MATERIAL
            //'CUST_MAT35' =>$product->get_sku(),
            'CUST_MAT35' =>$item['alternate_sku'],
        ];
        $payload['root']['ORDER_SCHEDULES_IN'][] = [
            "ITM_NUMBER" => sprintf("%06d", $i*10),
            'REQ_QTY' => $item->get_quantity(),// * 1000,
        ];
        $i++;
    }

    //When the product selected is from double E, the requests to SAP must go with the sales organization 2142
	if(gi_product_has_surface_equipment($productId)){
		$payload["root"]["ORDER_HEADER_IN"]["SALES_ORG"] = "2142";
	}

    //Add Material handling fee
    if ($_SESSION['handling_fee'] ){
    // Last one is for handling fee
    $payload['root']['ORDER_ITEMS_IN'][] = [
            "ITM_NUMBER" => sprintf("%06d", $i*10),
            "MATERIAL" => "61000170",
            'TARGET_QTY' => 1,
            'CUST_MAT35' =>"61000170",
        ];
        $payload['root']['ORDER_SCHEDULES_IN'][] = [
            "ITM_NUMBER" => sprintf("%06d", $i*10),
            'REQ_QTY' => 1,
        ];
}
    // If only 1 item, not use multi array
    if( count($order->get_items()) == 1 && (!$_SESSION['handling_fee'] || empty($_SESSION['handling_fee'])) ){
        $payload['root']['ORDER_ITEMS_IN'] = $payload['root']['ORDER_ITEMS_IN'][0];
        $payload['root']['ORDER_SCHEDULES_IN'] = $payload['root']['ORDER_SCHEDULES_IN'][0];
    }

    // Shipping fee
    $shipping_fee = $order->get_shipping_total();
    //Freight cost value (system is multiplying the value 10x, so please send the value/10)
    $payload['root']['ORDER_CONDITIONS_IN'] = [
        [
            "ITM_NUMBER" => "0010",
            "COND_TYPE" => "YBHD",
            "COND_VALUE" => $shipping_fee ? strval(round($shipping_fee / 10,6)) : 0
        ]
    ];
    // add handling fee
    if ($_SESSION['handling_fee']){
        $payload['root']['ORDER_CONDITIONS_IN'][] = [
            "ITM_NUMBER" => sprintf("%06d", $i*10),
            "COND_TYPE" => "PR00",
            "COND_VALUE" => $_SESSION['handling_fee'] ? strval(round($_SESSION['handling_fee']/10,6)) : 0
        ];
    }
    //JM added tax rate
    /* SAP not ready to receive tax rate
    $rates = array();
    foreach ( $order->get_items('tax') as $item_id => $item ) {
        $tax_rate_id = $item->get_rate_id();
        $rates[] = WC_Tax::get_rate_percent_value( $tax_rate_id );
    }
    if (count($rates)>0)
    {
        $rateapplied=$rates[0]/10;
        if ($_SESSION['handling_fee']){$i=$i+1;}
        $payload['root']['ORDER_CONDITIONS_IN'][] = [
            "ITM_NUMBER" => sprintf("%06d", $i*10),
            "COND_TYPE" => "JR1",
            "COND_VALUE" => $rateapplied ? $rateapplied : 0
        ];
    }
    */
    //print_r(json_encode($payload));

    $response = $GIWebServices->makeApiRequest('CREATE/SALESORDER', [],$payload);
    //print_r(json_encode($response));

    if (is_array($response) && count($response) > 0) {
        $response = array_values($response);
        if (isset($response[0]['SALESDOCUMENT']) && $response[0]['SALESDOCUMENT']>0) {
            $sap_order_id = $response[0]['SALESDOCUMENT'];
            update_post_meta($order_id,'sap_order_id',$sap_order_id);
        }else
        {
            send_notify_error('Datwyler Sealing: Failed to Create Order on SAP from website. No Document Id', json_encode($payload),'sap');
        }
    }else
    {
        send_notify_error('Datwyler Sealing: Failed to Create Order on SAP from website', json_encode($payload),'sap');
    }
}

// GID-1190
// Display custom field in the order details page
add_action('woocommerce_admin_order_data_after_order_details', 'display_sap_order_id_in_admin_order_details');
function display_sap_order_id_in_admin_order_details($order) {
    $sap_order_id = get_post_meta($order->get_id(), 'sap_order_id', true);
    if ($sap_order_id) {
        echo '<p><strong>' . __('SAP Order Number') . ':</strong> ' . esc_html($sap_order_id) . '</p>';
    }
}

// For customer order page:
add_action('woocommerce_order_details_after_order_table', 'display_sap_order_id_on_order_page');
function display_sap_order_id_on_order_page($order) {
    $sap_order_id = get_post_meta($order->get_id(), 'sap_order_id', true);
    if ($sap_order_id) {
        echo '<p><strong>' . __('SAP Order Number') . ':</strong> ' . esc_html($sap_order_id) . '</p>';
    }
    $invoices = get_field('invoices',$order->get_id());
    if( $invoices ){
        ob_start();
        ?>
        <label for=""><h2>Invoices</h2></label>
        <p>If you have placed an order where a Minimum Order Fee has been applied, this will show up as a separate invoice.</p>
        <ul>
            <?php foreach( $invoices as $invoice ):?>
            <li><a target="_blank" href="<?= $invoice['invoice_file']['url']; ?>"><?= $invoice['invoice_file']['filename']; ?></a></li>
            <?php endforeach;?>
        </ul>
        <?php
        echo ob_get_clean();
    }
}

// For order emails:
//add_action('woocommerce_email_order_meta', 'display_sap_order_id_in_emails', 20, 4);
function display_sap_order_id_in_emails($order, $sent_to_admin, $plain_text, $email) {
    $sap_order_id = get_post_meta($order->get_id(), 'sap_order_id', true);

    if ($sap_order_id) {
        echo '<p><strong>' . __('SAP Order Number') . ':</strong> ' . esc_html($sap_order_id) . '</p>';
    }
}
