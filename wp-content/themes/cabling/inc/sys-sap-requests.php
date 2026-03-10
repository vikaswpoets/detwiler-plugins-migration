<?php

//returns list of available alternate skus
function getCompatibleSKUList($matchkey){
    global $wpdb;
    // this adds the prefix which is set by the user upon instillation of wordpress
    //$table_name = $wpdb->prefix . "wpex_programma";
    // this will get the data from your table
    $retrieve_data = $wpdb->get_results( "SELECT sku,preference FROM map_view where primarysku=".$matchkey." order by preference asc;" );
    $skulst=[];
    if(count($retrieve_data)>0) 
    {
        foreach($retrieve_data as $item)
        {
            $skulst[]=$item->sku;
        }
    }else{
		$skulst[]=$matchkey;
	}
    return $skulst;
}

//get the best sku to order
function getBestSKU($matchkey,$targetQty)
{
    $skulst=[];
        
    //$skulst=getCompatibleSKUList($matchkey);
    // changed for lvl2 not be able to submit other skus in orders
    
    $user_id    = get_current_user_id();
    $sap_no     = get_user_meta($user_id, 'sap_customer', true);
    $sap_no     = $sap_no ? $sap_no : '1024495';
    if($sap_no=='1024495')
    {
        $skulst=getCompatibleSKUList($matchkey);    
    }else
    {
        $skulst[]=$matchkey;
    }

    $result=checkStockSKUlist($skulst);
    $availableskus=[];


    foreach($result as $item)
    {
        if($item['stock']>=$targetQty)
        {
            $availableskus[]= $item['sku'];
        }
    }
    if(count($availableskus)==0)
    {
        $retval=array(
            'sku' => $result[0]['sku'], //$retrieve_data[0]->sku,
            'in_stock' =>false,
        );
        //return $retrieve_data[0]->sku; // if no stock return first sku by preference order
        return $retval;
    }

    foreach($result as $item)
    {
        if(in_array($item['sku'],$availableskus))
        {
            $retval=array(
                'sku' => $item['sku'],
                'in_stock' =>true,
            );

            return $retval;
        } // returns first sku found
    }
    
    // nothing found, return original sku
    $retval=array(
        'sku' => $matchkey,
        'in_stock' =>false
    );
    return $retval;
}

function getStockResponseForSKUList($skulist,$user_plant="")
{
        try {

            $webServices = new GIWebServices();
            if(empty($user_plant)){
                $user_plant = get_user_meta(get_current_user_id(), 'sales_org', true);
            }
            $stockParams = [];
            if (is_array($skulist)){
                for($i=0;$i<count($skulist);$i++)
                //foreach($skulist as $sku)
                {
                    $stockParams[]=array(
                        'Field' => $i==0?'(Material':'Material',
                        "Sign" => "eq",
                        'Value' => $skulist[$i],
                        'Operator' => $i==count($skulist)-1?') and':'or',
                    );
                }
            }else
            {
                $stockParams[]=array(
                        'Field' => 'Material',
                        "Sign" => "eq",
                        'Value' => $skulist,
                        'Operator' => 'and',
                    );
            }
            $stockParams[] = array(
                        "Field" => "Plant",
                        "Sign" => "eq",
//                        "Value" => "2130",
"Value" => "2141",
                        "Operator" => "and"
                    );
            $stockParams[]=array(
                        "Field" => "SalesOrganization",
                        "Sign" => "eq",
                        //"Value" => "2130",
//                        'Value' => empty($user_plant) ? '2130' : $user_plant,
'Value' => empty($user_plant) ? '2141' : $user_plant,
                        "Operator" => ""
                    );
            $apiStockEndpoint = 'GET_DATA_MaterialStockReqr';
            $responseStock = $webServices->makeApiRequest($apiStockEndpoint, $stockParams);

            $dataStock = $webServices->getDataResponse($responseStock, 'ZDD_I_SD_PIM_MaterialStockReqr', 'ZDD_I_SD_PIM_MaterialStockReqrType');
            return $dataStock;
        } catch (Exception $e) {
            return [];
        }

    wp_die();
}

function checkStockSKUlist($skulist)
{
    $dataStock=getStockResponseForSKUList($skulist);
    $data=[];
    foreach($dataStock as $item)
    {
        $data[] = array(
            'stock' => $item['StockQuantity'] ?? 0,
            'sku' => $item['Material']
        );    
    }
    return $data;
}

add_filter('show_custom_stock_message', 'fn_show_custom_stock_message',10, 1);
function fn_show_custom_stock_message($cart_item){
    if ($cart_item['in_stock']){
        return 'In Stock: We estimate to have the products ready for shipping in the next 24 hours.';
    } else {
        return 'Out of Stock: We estimate to have the products ready for shipping in the next 10-14 days.';
    }
}
