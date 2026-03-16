<?php

// Configuration constants
//define('DEFAULT_PLANT', '2130');
define('DEFAULT_PLANT', '2141');
define('DEFAULT_SAP_CUSTOMER', '1024495');

//returns list of available alternate skus
function getCompatibleSKUList($matchkey){
    global $wpdb;
    // this adds the prefix which is set by the user upon instillation of wordpress
    // this will get the data from your table
    $retrieve_data = $wpdb->get_results($wpdb->prepare(
        "SELECT sku,preference FROM map_view WHERE primarysku = %s ORDER BY preference ASC",
        $matchkey
    ));
    $skulst = [];
    if(count($retrieve_data) > 0) 
    {
        foreach($retrieve_data as $item)
        {
            $skulst[] = $item->sku;
        }
    } else {
        $skulst[] = $matchkey;
    }
    return $skulst;
}

//get the best sku to order
function getBestSKU($matchkey, $targetQty)
{
    $skulst = [];
    //$matchkey='000021334';
    $user_id    = get_current_user_id();
    $sap_no     = get_user_meta($user_id, 'sap_customer', true);
    $sap_no     = $sap_no ? $sap_no : DEFAULT_SAP_CUSTOMER;
    if($sap_no == DEFAULT_SAP_CUSTOMER)
    {
        $skulst = getCompatibleSKUList($matchkey);    
    } else
    {
        $skulst[] = $matchkey;
    }
    //$skulst=getCompatibleSKUList($matchkey);
    $result = checkStockSKUlist($skulst);    
        
    $availableskus = [];

    foreach($result as $item)
    {
        if($item['stock'] >= $targetQty)
        {
            $availableskus[] = $item['sku'];
        }
    }

    if(count($availableskus) == 0)
    {
        // Check if result array has elements before accessing first element
        $firstSku = !empty($result) ? $result[0]['sku'] : $matchkey;
        $retval = array(
            'sku' => $firstSku,
            'in_stock' => false,
        );
        //return $retrieve_data[0]->sku; // if no stock return first sku by preference order

        return $retval;
    }

    foreach($result as $item)
    {
        if(in_array($item['sku'], $availableskus))
        {
            $retval = array(
                'sku' => $item['sku'],
                'in_stock' => true,
            );

            return $retval;
        } // returns first sku found
    }
    
    // nothing found, return original sku
    $retval = array(
        'sku' => $matchkey,
        'in_stock' => false
    );
    return $retval;
}

function getStockResponseForSKUList($skulist, $user_plant = "")
{
        try {

            $webServices = new GIWebServices();
            if(empty($user_plant)){
                $user_plant = get_user_meta(get_current_user_id(), 'sales_org', true);
            }
            $stockParams = [];
            if (is_array($skulist)){
                for($i = 0; $i < count($skulist); $i++)
                //foreach($skulist as $sku)
                {
                    $stockParams[] = array(
                        'Field' => $i == 0 ? '(Material' : 'Material',
                        "Sign" => "eq",
                        'Value' => $skulist[$i],
                        'Operator' => $i == count($skulist) - 1 ? ') and' : 'or',
                    );
                }
            } else
            {
                $stockParams[] = array(
                        'Field' => 'Material',
                        "Sign" => "eq",
                        'Value' => $skulist,
                        'Operator' => 'and',
                    );
            }
            $stockParams[] = array(
                        "Field" => "Plant",
                        "Sign" => "eq",
                        'Value' => empty($user_plant) ? DEFAULT_PLANT : $user_plant,
                        "Operator" => "and"
                    );
            $stockParams[] = array(
                        "Field" => "SalesOrganization",
                        "Sign" => "eq",
                        'Value' => empty($user_plant) ? DEFAULT_PLANT : $user_plant,
                        "Operator" => ""
                    );
            $apiStockEndpoint = 'GET_DATA_MaterialStockReqr';
            $responseStock = $webServices->makeApiRequest($apiStockEndpoint, $stockParams);

            $dataStock = $webServices->getDataResponse($responseStock, 'ZDD_I_SD_PIM_MaterialStockReqr', 'ZDD_I_SD_PIM_MaterialStockReqrType');
            return $dataStock;
        } catch (Exception $e) {
            // Log error for debugging
            error_log('SAP API Error: ' . $e->getMessage());
            return [];
        }

}

function checkStockSKUlist($skulist)
{
    $dataStock = getStockResponseForSKUList($skulist);
    $data = [];
    foreach($dataStock as $item)
    {
        $data[] = array(
            'stock' => $item['StockQuantity'] ?? 0,
            'sku' => $item['Material']
        );    
    }
    
    if(count($data) > 1){
        return sortArray($data, $skulist);
    }
    if(count($data) == 0){
        // Check if skulist has elements before accessing first element
        $firstSku = !empty($skulist) ? $skulist[0] : '';
        $data[] = array(
            'stock' => 0,
            'sku' => $firstSku
        );
    }
    return $data;
}

add_filter('show_custom_stock_message', 'fn_show_custom_stock_message', 10, 1);
function fn_show_custom_stock_message($cart_item){

    $product_id = $cart_item['product_id'] ?? 0;
    if ( ! $product_id && ! empty( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
        $product_id = $cart_item['data']->get_id();
    }

    $is_production_equipment = $product_id && has_term( 'Production Equipment', 'product_cat', $product_id );

    if ( $is_production_equipment ) {
        return $cart_item['in_stock']
            ? 'In Stock: We estimate to have the products ready for shipping within 48 hours.'
            : 'Out of Stock: We estimate to have the products ready for shipping within 3 weeks.';
    }
    return $cart_item['in_stock']
        ? 'In Stock: We estimate to have the products ready for shipping in the next 24 hours.'
        : 'Out of Stock: We estimate to have the products ready for shipping in the next 10-14 days.';
    
}

//returns list of available alternate skus
function getPostIdBySKU($sku){
    global $wpdb;
    // this adds the prefix which is set by the user upon instillation of wordpress
    //$table_name = $wpdb->prefix . "wpex_programma";
    // this will get the data from your table
    $retrieve_data = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key='_sku' AND meta_value=%s LIMIT 1;",
        $sku
    ));
    if(count($retrieve_data) > 0) 
    {
        foreach($retrieve_data as $item)
        {
            return $item->post_id;
        }
    }
    return "";
}


//used to sort results returned from sap to our order
function sortArray($arraytosort, $initialorder)
{
    $ret = [];
    $initialorder_copy = $initialorder; // Create a copy to avoid modifying original array
    
    // Create a lookup map for faster access
    $lookup = [];
    foreach($arraytosort as $item) {
        $lookup[$item['sku']] = $item;
    }
    
    // Sort according to initial order
    foreach($initialorder_copy as $sku) {
        if(isset($lookup[$sku])) {
            $ret[] = $lookup[$sku];
        }
    }
    
    return $ret;
}

