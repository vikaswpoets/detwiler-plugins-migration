<?php
/*
#GID-1060
Steps of implementation:
1 - Customer places Order. Return status will be wating for shippment (text might change)
2 - Customer checks My Orders: ( CRON check order daily )
3 - We check Order status against SAP
4 - SAP returns order status with Carrier with or without tracking number
5 - If carrier is not FEDEX from Datwyler, we update order status and end process.
6 - If carrier is fedex from Datwyler
7 - If status is delivered, we update order status and end process.
8 - if status is waiting shippment (not sure about the text coming) and no tracking number we update order status and end process
9 - if status is not delivered and we have tracking number, we check tracking number against Fedex and update status according to fedex.
*/
//#2 Customer checks My Orders: ( CRON check order daily )
if ( ! wp_next_scheduled( 'sap_order_daily_cron_hook' ) ) {
    wp_schedule_event( time(), 'daily', 'sap_order_daily_cron_hook' );
}
add_action('sap_order_daily_cron_hook', 'check_orders_and_update_tracking');
// Or call by ajax
add_action('wp_ajax_check_orders_and_update_tracking', 'check_orders_and_update_tracking');
add_action('wp_ajax_nopriv_check_orders_and_update_tracking', 'check_orders_and_update_tracking');

function check_orders_and_update_tracking() {
    if( isset($_REQUEST['debug_capture']) ){
        $order_id = $_REQUEST['order_id'];
        $order = wc_get_order($order_id);
        gi_chase_do_capture($order);
        die();
    }
    $args = array(
        'status' => array('processing', 'on-hold'), 
        'limit'  => -1,
    );
    $orders = wc_get_orders($args);
    foreach ($orders as $order) {
        $order_id = $order->get_id();
        $customer_id = $order->get_customer_id();
        //#3 We check Order status against SAP
        $res = get_order_from_sap($order_id,$customer_id);
    }
}

function get_order_from_sap($order_id = '',$customer_id = 0) {
    $order = wc_get_order($order_id);
    $sap_order_id = get_post_meta($order_id,'sap_order_id',true);
    $sap_order_id = intval($sap_order_id);
    if(!$sap_order_id){
        return;
    }
    $apiEndpoint = 'GET_DATA_BACKLOG_CDS';
    $GIWebServices = new GIWebServices();

    //$user_plant = get_user_meta($customer_id, 'sales_org', true);//Can use 2130 for testing
    $bodyParams = [
        array(
            'Field' => 'SalesDocument',
            'Value' => strval($sap_order_id),
            'Operator' => '',
        )
    ];
    
    //#4 SAP returns order status with Carrier with or without tracking number
    $response = $GIWebServices->makeApiRequest($apiEndpoint,$bodyParams);
    if( isset( $response['ZDD_I_SD_PIM_MaterialBacklog'] ) ){
        if( isset( $response['ZDD_I_SD_PIM_MaterialBacklog']['ZDD_I_SD_PIM_MaterialBacklogType'] ) ){
            $responseData = $response['ZDD_I_SD_PIM_MaterialBacklog']['ZDD_I_SD_PIM_MaterialBacklogType'];
            //$ShippingMethod = $responseData['ShippingMethod'];
            if(!isset($responseData["DeliveryDocument"]) && count($responseData)>0)
            {
                $responseData=$responseData[0];
            }

            //first if delivery document, we capture chase payment, as order is under process
            //if( !empty($responseData['DeliveryDocument']) && $order->get_status()=="on-hold"){
            if( isset($responseData['DeliveryDocument']) && $order->get_status()=="on-hold"){
                // Do capture chase
                try {
                    //if payment is by Credit card then capture
                    if ($order->get_payment_method()==="chase_paymentech" ){
                        gi_chase_do_capture($order);
                    }
                    $order->update_status('processing','Updated via SAP');
                } catch ( Exception $e ) {}
            }
            //if we get a FAtracking then we put order as completed
            //if( !empty($responseData['DeliveryDocument']) || !empty($responseData['FATrackingID']) ){
            if( !empty($responseData['DeliveryDocument']) ){
                $order->update_status('completed','Updated via SAP');
                }
            if(isset($responseData['FATrackingID']) && !empty($responseData['FATrackingID']) ){
                try{
                update_post_meta($order_id,'fa_tracking_number',$responseData['FATrackingID']);
                }catch(Exception $ex){}
            }
        }
    }
}

// JM changed logic to the replacement method
function get_order_from_sap_original($order_id = '',$customer_id = 0) {
    $order = wc_get_order($order_id);
    $sap_order_id = get_post_meta($order_id,'sap_order_id',true);
    $sap_order_id = intval($sap_order_id);//0000035805 => 35805, can use 1194238 for testing
    if(!$sap_order_id){
        return;
    }
    $apiEndpoint = 'GET_DATA_BACKLOG_CDS';
    $GIWebServices = new GIWebServices();

    $user_plant = get_user_meta($customer_id, 'sales_org', true);//Can use 2130 for testing
    $bodyParams = [
        array('Field' => 'SalesOrganization',
            'Value' => empty($user_plant) ? '2141' : strval($user_plant),
            'Operator' => 'and',
        ),
        array(
//            'Field' => 'PurchaseOrderByCustomer',
            'Field' => 'SalesDocument',
            'Value' => strval($sap_order_id),
            'Operator' => '',
        )
    ];
    
    //#4 SAP returns order status with Carrier with or without tracking number
    $response = $GIWebServices->makeApiRequest($apiEndpoint,$bodyParams);
    if( isset( $response['ZDD_I_SD_PIM_MaterialBacklog'] ) ){
        if( isset( $response['ZDD_I_SD_PIM_MaterialBacklog']['ZDD_I_SD_PIM_MaterialBacklogType'] ) ){
            $responseData = $response['ZDD_I_SD_PIM_MaterialBacklog']['ZDD_I_SD_PIM_MaterialBacklogType'];
            $ShippingMethod = $responseData['ShippingMethod'];
            //#5 - If carrier is not FEDEX from Datwyler, we update order status and end process.
            //If we have a "DeliveryDocument": "80122191" or a "ShippingMethod": "UPS Ground" or a "FATrackingID": "1z9299310366755876"
            if( !empty($responseData['DeliveryDocument']) || !empty($responseData['ShippingMethod']) || !empty($responseData['FATrackingID']) ){
                // If carrier is not FEDEX from Datwyler
                if (stripos($ShippingMethod, 'FEDEX') === false) {
                    //we update order status and end process.
                    $order->update_status('completed','Updated via SAP');
                }else{
                    //6 - If carrier is fedex from Datwyler
                    /*
                    7 - If status is delivered, we update order status and end process.
                    8 - if status is waiting shippment (not sure about the text coming) and no tracking number we update order status and end process
                    9 - if status is not delivered and we have tracking number, we check tracking number against Fedex and update status according to fedex.
                    */

                    //8 - if status is waiting shippment (not sure about the text coming) and no tracking number we update order status and end process
                    if( empty($responseData['FATrackingID']) ){
                        $order->update_status('completed','Updated via SAP');
                    }

                    //9 - if status is not delivered and we have tracking number, we check tracking number against Fedex and update status according to fedex.
                    if( !empty($responseData['FATrackingID']) ){
                        // Update tracking number for user
                        //tracking_fedex_shipment_ids
                        // Check tracking number against Fedex
                        $shipment_tracking = gi_ph_fedex_shipment_tracking($order_id,$responseData['FATrackingID']);
                        $tracking_status = $shipment_tracking['tracking_status'];
                        // tracking_status: "DL" (for Delivered), "IT" (for In Transit), "PU" (for Picked Up)
                        if($tracking_status == 'DL'){
                            $order->update_status('completed','Updated via SAP');
                        }
                    }
                }
            }
            if( !empty($responseData['DeliveryDocument']) ){
                // Do capture chase
                try {
                    gi_chase_do_capture($order);
                } catch ( Exception $e ) {
                   
                }
            }
        }
    }
}


// Function to get fedex tracking
function gi_ph_fedex_shipment_tracking($order_id,$shipment_id = ''){
    if(!$order_id){ return ''; }

    if(!$shipment_id){
        $order			= wc_get_order($order_id);
        $shipmentIds 	= PH_WC_Fedex_Storage_Handler::ph_get_meta_data($order_id, 'wf_woo_fedex_shipmentId', false);
        $shipment_ids 	= PH_WC_Fedex_Storage_Handler::ph_get_meta_data($order_id, 'ph_woo_fedex_shipmentIds');
        $wc_meta_data_handler = new PH_WC_Fedex_Storage_Handler($order);
        if( is_array($shipmentIds) && is_array($shipment_ids) ){
            $shipmentIds  		= array_unique(array_merge($shipmentIds,$shipment_ids));
        }
        $shipment_id = isset($shipmentIds[0]) ? $shipmentIds[0] : 0;
        if(!$shipment_id){ return ''; }
    }

    $file_ph_fedex_api_shipment_tracking = WP_PLUGIN_DIR.'/fedex-woocommerce-shipping/includes/class-ph-fedex-shipment-tracking.php';
    if( !file_exists($file_ph_fedex_api_shipment_tracking) ){ return ''; }
    include_once $file_ph_fedex_api_shipment_tracking;
    $ph_fedex_api_shipment_tracking = new ph_fedex_api_shipment_tracking();
    $wc_meta_data_handler->ph_update_meta_data($order_id, '_ph_fedex_tracking_status' . $shipment_id, '' );
    $wc_meta_data_handler->ph_update_meta_data($order_id, '_ph_fedex_tracking_status_error' . $shipment_id, '' );

    $tracking_status = '';
    $response 	= $ph_fedex_api_shipment_tracking->ph_get_response( $ph_fedex_api_shipment_tracking->ph_get_fedex_tracking_request( $shipment_id ) );
    $result 	= $ph_fedex_api_shipment_tracking->ph_get_result_text($response);
    if ( isset($result['success']) && !empty($result['success']) ) {
        $message = $tracking_status = $result['success']['livestatus'];
        $wc_meta_data_handler->ph_update_meta_data($order_id, '_ph_fedex_tracking_status' . $shipment_id, $result['success'] );
    } else if ( isset($result['error']) && !empty($result['error']) ) {
        $message = $result['error']['description'];
    } else{
        $message = 'Tracking Details Unavailable Please Try Later';
    }
    return [
        'tracking_status'   => $tracking_status,
        'message'           => $message
    ];
}

// Function chase capture
function gi_chase_do_capture($order){
    $payment_method_id = $order->get_payment_method();
    if( isset( $_REQUEST['debug_capture'] ) ){
         echo '<pre>'; print_r('$payment_method_id: '.$payment_method_id); echo '</pre>'; 
    }
    if( $payment_method_id != 'chase_paymentech' ){
        return;
    }

    $gateway = wc_chase_paymentech()->get_gateway( 'chase_paymentech' );
    $amount_captured = (float) $gateway->get_order_meta( $order, 'capture_total' );
    $amount = $order->get_total();
    $result = $gateway->get_capture_handler()->perform_capture( $order, $amount );
    if( isset( $_REQUEST['debug_capture'] ) ){
        echo '<pre>'; print_r($result); die(); 
    }
    if ( !empty( $result['success'] ) ) {
        $order->update_status('completed','Updated via SAP');
    }
    return $order;
}

