<?php
/*
At the end of check out, before creating the order, we need to create a simulation before final confirmation.
$sap_no     = 1016168;
#GID-1093
{
    "HEADER": {
        "DOC_TYPE": "TA", (fixed)
        "SALES_ORG": "2141", (user sales organization)
        "DISTR_CHAN": "10", (fixed)
        "DIVISION": "01", (fixed)
        "PURCH_NO": "", (fixed)
        "PURCH_DATE": "20240516" (date in this format)
    },
    "ITEM": [
        {
            "ITM_NUMBER": "000010", (each item increases by 10, leading zeros are mandatory)
            "MATERIAL": "000000000049000167", (sku number, leading zeros are mandatory)
            "TARGET_QTY": "0000005700000", (Item qty, leading zeros are mandatory)
            "REQ_QTY": "0000005700000" (Item qty, leading zeros are mandatory)
        },
        {
            "ITM_NUMBER": "000020",
            "MATERIAL": "000000000049000167",
            "TARGET_QTY": "0000005700000",
            "REQ_QTY": "0000005700000"
        }
    ],
    "Partner": [
        {
            "PARTN_ROLE": "AG", (Sold-To)
            "PARTN_NUMB": "0001016168" (Sold-To Id retrieved from addresses service)
        },
        {
            "PARTN_ROLE": "WE", (Ship-To)
            "PARTN_NUMB": "0001016168" (Ship-To Id retrieved from addresses service, and selected by customer)
        },
        {
            "PARTN_ROLE": "RE", (Bill-To)
            "PARTN_NUMB": "0001016168" (Bill-To Id retrieved from addresses service, and selected by customer)
        },
        {
            "PARTN_ROLE": "SP", (Carrier)
            "PARTN_NUMB": "" (Carrier-Id, selected by customer. The Selection of carriers (FEDEX/DHL, must have the correct Ids))
        },
        {
            "PARTN_ROLE": "RG", (Payer)
            "PARTN_NUMB": "0001016168" (Payer-To Id retrieved from addresses service)
        }
    ]
}
*/

function _sap_create_order( $order_data, $data ) {
    $carrier_id = isset($_REQUEST['carrier_id']) ? $_REQUEST['carrier_id'] : '';
	$_SESSION['carrier_id'] = $carrier_id;

	$user_id    = get_current_user_id();
	$user_plant = get_user_meta( $user_id, 'sales_org', true );
    //$user_plant = $user_plant ? $user_plant : '2130';
	$user_plant = $user_plant ? $user_plant : '2141';

	$sap_no        = get_user_meta( $user_id, 'sap_customer', true );
    $sap_no     = $sap_no ? $sap_no : '1024495';
	$sap_no        = $sap_no ? str_pad( $sap_no, 10, '0', STR_PAD_LEFT ) : '';
	$GIWebServices = new GIWebServices();

	$payload = [
		"HEADER"  => [
			"DOC_TYPE"   => "TA",
			"SALES_ORG"  => $user_plant,
			"DISTR_CHAN" => "10",
			"DIVISION"   => "01",
			"PURCH_NO"   => "",
			"PURCH_DATE" => date( 'Ymd' )
		],
		"Partner" => [
			[
				"PARTN_ROLE" => "AG",
				"PARTN_NUMB" => $sap_no
			],
			[
				"PARTN_ROLE" => "WE",
				"PARTN_NUMB" => $sap_no
			],
			[
				"PARTN_ROLE" => "RE",
				"PARTN_NUMB" => $sap_no
			],
			[
				"PARTN_ROLE" => "SP",
				"PARTN_NUMB" => $carrier_id
			],
			[
				"PARTN_ROLE" => "RG",
				"PARTN_NUMB" => $sap_no
			]
		]
	];
	$i = 1;

	$productId = 0;
	foreach ( $order_data as $item_id => $item ) {
		if (empty($productId)) {
			$productId = $item['product_id'];
		}
		$sku      = get_post_meta( $item['product_id'], '_sku', true );
		$quantity = $item['quantity'];
		//find match sku
		$bestmatch    = getBestSKU( $sku, $quantity );
		$alternatesku = $bestmatch['sku'];
		if(empty($alternatesku)){$alternatesku=$sku;}
		//JM define new property alternate SKU in cart
		WC()->cart->cart_contents[ $item_id ]['alternate_sku'] = $alternatesku;
		WC()->cart->cart_contents[ $item_id ]['in_stock']      = $bestmatch['in_stock'];
		WC()->cart->cart_contents[ $item_id ]['sku']           = $sku;
		WC()->cart->set_session();


		// Add item data to ITEM array in payload
		$payload['ITEM'][] = [
			"ITM_NUMBER" => sprintf( "%06d", $i * 10 ),
			"MATERIAL"   => sprintf( "%018d", $alternatesku ),
			"TARGET_QTY" => $quantity * 1000,
			"REQ_QTY"    => $quantity * 1000
		];
		$i ++;
	}

	//When the product selected is from double E, the requests to SAP must go with the sales organization 2142
	if(gi_product_has_surface_equipment($productId)){
		$payload["HEADER"]["SALES_ORG"] = "2142";
	}
	$response = $GIWebServices->makeApiRequest( 'GET/SALESORDER/SIMULATE', [], $payload );
	//wp_mail('jose.martins@infolabix.com', 'Simulate Response for SKU:'.$sku.' alt:'.$alternatesku, json_encode($payload).' ---------- '.json_encode($response));
	//print_r(json_encode($response));
	return $response;
}



// Apply at order review
add_action('woocommerce_before_calculate_totals', 'sap_create_order_simulation_handle_woo', 10, 1);
function sap_create_order_simulation_handle_woo($cart){
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    if ( WC()->cart->is_empty() ) {
        return;
    }
    if ( !is_checkout() ) {
        return;
    }

    // GE-267
    $checkout_redirect = false;

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $price = floatval( $cart_item['data']->get_price() );
        if ( $price <= 0 ) {
            $checkout_redirect = true;
        }
    }

    if($checkout_redirect){
        wp_safe_redirect( add_query_arg( 'alert', 'price', home_url( 'checkout-alert' ) ) );
        exit;
    }
    // END GE-267

    $user_id = get_current_user_id();
    $user_wp9_form          = get_user_meta($user_id,'user_wp9_form',true);
    $user_certificate_form  = get_user_meta($user_id,'user_certificate_form',true);
    $check_remove_tax_by_file = $user_wp9_form && $user_certificate_form ? true : false;
    $customer_level = get_customer_level($user_id);
    $must_redirect = false;
    // Make sure we call 1 time
    if( !WC()->session->get('sap_created_order_simulation') || true ){
		$response = _sap_create_order($cart->get_cart(),$_REQUEST);
        if( empty($response['RETURN']['MESSAGE']) ){
            $order_items = $response['ORDER_ITEMS_OUT']['item'];
            $new_cart_total = 0;
            $min_price = get_field('min_price','option');
            $handling_fee = 0;
            $tax_percentage = 0;
            foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
                $product_id = $cart_item['product_id'];
                $tax_class = get_post_meta( $product_id, '_tax_class', true );
                $tax_rates = WC_Tax::get_rates( $tax_class );

                if($tax_rates){
                    $tax_rate = reset( $tax_rates );
                    $tax_percentage = $tax_rate['rate'];
                }

                $sku = get_post_meta($cart_item['product_id'], '_sku', true);
                if( isset($order_items[0]) ){
                    foreach ($order_items as $order_item) {
                        if (is_array($order_item) && isset($order_item['MATERIAL'])) {
                            $material = ltrim($order_item['MATERIAL'], '0');
                            if ($material == $sku) {
                                $sap_subtotal = $order_item['NET_VALUE1'];
                                if( $sap_subtotal < $min_price ){
                                    $handling_fee += $min_price - $sap_subtotal;
                                }
                                $sap_quantity = intval($order_item['REQ_QTY']);
                                $woo_qty = $cart_item['quantity'];
                                $new_price = $sap_subtotal ? $sap_subtotal / $woo_qty : 0;
                                if( $new_price == 0 ){
                                    $must_redirect = true;
                                }
                                $cart_item['data']->set_price($new_price);
                                //SET VAT
                                if($customer_level == 2 || $check_remove_tax_by_file ){
                                    $cart_item['data']->set_tax_class( 'zero-rate' );
                                }

                                // $cart_item['data']->save();
                            }
                        }
                    }
                }else{
                    $order_item = $order_items;
                    $material = ltrim($order_item['MATERIAL'], '0');
                    if ($material == $sku) {
                        $sap_subtotal = $order_item['NET_VALUE1'];
                        if( $sap_subtotal < $min_price ){
                            $handling_fee += $min_price - $sap_subtotal;
                        }
                        $sap_quantity = intval($order_item['REQ_QTY']);
                        $woo_qty = $cart_item['quantity'];
                        $new_price = $sap_subtotal ? $sap_subtotal / $woo_qty : 0;
                        if( $new_price == 0 ){
                            $must_redirect = true;
                        }
                        $cart_item['data']->set_price($new_price);

                        //SET VAT
                        if($customer_level == 2 || $check_remove_tax_by_file ){
                            $cart_item['data']->set_tax_class( 'zero-rate' );
                        }
                        // $cart_item['data']->save();
                    }
                }
            }
            // Apply TAX for handling_fee
	        if (!has_surface_equipment_on_cart($cart->get_cart())) {
		        if ( $customer_level == 1 && ! $check_remove_tax_by_file ) {
			        $handling_fee_tax             = $handling_fee && $tax_percentage ? ( $handling_fee * $tax_percentage ) / 100 : 0;
			        $_SESSION['handling_fee_tax'] = $handling_fee_tax;
		        }
		        $_SESSION['handling_fee'] = $handling_fee;
	        }
        }
        else
        {
            //print_r($response['RETURN']['MESSAGE']);
            //die();
            $must_redirect=true;
        }
    }

	$tax_data = WC()->session->get( 'gi_taxjar_tax_data' );
	if ( ! empty( $tax_data['amount'] ) ) {
		$fees = WC()->cart->get_fees();
        foreach ($fees as $fee_key => $fee) {
            if ($fee->name === __('Sales Tax', 'gi-taxjar')) {
				unset( WC()->cart->fees_api()->get_fees()[ $fee_key ] );
            }
        }
		// Remove default WooCommerce taxes
	    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	        $cart_item['data']->set_tax_class( 'zero-rate' );
	    }

		WC()->cart->add_fee(
			__( 'Sales Tax', 'gi-taxjar' ),
			$tax_data['amount'],
			false
		);
	}

    if($must_redirect){
        wp_safe_redirect( home_url( 'checkout-alert' ) );
    }
    WC()->session->set('sap_created_order_simulation', 1);
}

// GE-267
function custom_checkout_alert_page_content( $content ) {
    if ( is_page( 'checkout-alert' ) ) {
        $alert_type = isset($_GET['alert']) ? sanitize_text_field($_GET['alert']) : '';
        if ( $alert_type === 'price' ) {
            $content = '<p class="has-text-align-center">Due to a pricing error, we cannot continue.</p>
            <p class="has-text-align-center">Please try again later or contact our sales team through <a href="mailto:suso.ont.sales@datwyler.com"> suso.ont.sales@datwyler.com.</a></p>';
        }
    }
    return $content;
}
add_filter( 'the_content', 'custom_checkout_alert_page_content' );
