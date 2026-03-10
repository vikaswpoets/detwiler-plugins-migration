<?php
/*
When user login , if customer level 2 we get address and save to shipping address
GID-1081
$user_plant = 2141;
$sap_no     = 1016168;
*/ 
add_action('wp_login','sap_customer_address',11,2);
function sap_customer_address($username, $user){
    $user_id = $user->ID;
    $customer_level = get_customer_level($user_id);
    if( $customer_level == 2 ){
        // Call SAP API get getCustomerAdresses
        $GIWebServices = new GIWebServices();
        $user_plant = get_user_meta($user_id, 'sales_org', true);
        $user_plant = $user_plant ? $user_plant : '2141';
        $sap_no     = get_user_meta($user_id, 'sap_customer', true);
        $filters = array(
            array(
                'Field' => 'SalesOrganization',
                'Sign' => 'eq',
                "Value" => $user_plant,
                'Operator' => 'and',
            ),
            array(
                'Field' => 'Customer',
                'Sign' => 'eq',
                'Value' => $sap_no,
                'Operator' => '',
            )
        );
        $sap_addresses = $GIWebServices->makeApiRequest('GET_DATA_CustomerPartners_CDS', $filters);
        // End call SAP API 
        
        $addresses = [];
        if( !empty($sap_addresses['ZDD_I_SD_PIM_CustomerPartners']['ZDD_I_SD_PIM_CustomerPartnersType']) ){
            $addresses = $sap_addresses['ZDD_I_SD_PIM_CustomerPartners']['ZDD_I_SD_PIM_CustomerPartnersType'];
        }
        $data = [];
        if( count($addresses) ){
            $data['shipping'] = [];
            $i = 1;
            $j = 1;
            foreach( $addresses as $key => $addresse ){
                if( $addresse['CustomerName'] && $addresse['PartnerFunction'] == 'WE' ){
                    $data_address = [
                        'shipping_first_name' => $addresse['CustomerName'],
                        'shipping_last_name' => $addresse['CustomerName'],
                        'shipping_company' => $addresse['OrganizationBPName1'],
                        'shipping_country' => $addresse['Country'],
                        'shipping_address_1' => $addresse['StreetName'],
                        'shipping_address_2' => '',
                        'shipping_city' => $addresse['CityName'],
                        'shipping_state' => $addresse['Region'],
                        'shipping_postcode' => $addresse['PostalCode'],
                    ];
                    $data['shipping']['address_'.$i] = $data_address;
                    // Set default shipping
                    if( $i == 1 ){
                        foreach( $data_address as $add_key => $add_val ){
                            update_user_meta($user_id,$add_key,$add_val);
                        }
                    }
                    $i++;
                }
                if( $addresse['CustomerName'] && $addresse['PartnerFunction'] == 'RE' ){
                    $data_address = [
                        'billing_first_name' => $addresse['CustomerName'],
                        'billing_last_name' => $addresse['CustomerName'],
                        'billing_company' => $addresse['OrganizationBPName1'],
                        'billing_country' => $addresse['Country'],
                        'billing_address_1' => $addresse['StreetName'],
                        'billing_address_2' => '',
                        'billing_city' => $addresse['CityName'],
                        'billing_state' => $addresse['Region'],
                        'billing_postcode' => $addresse['PostalCode'],
                    ];
                    // $data['billing']['address_'.$j] = $data_address;
                    // Set default billing
                    if( $j == 1 ){
                        foreach( $data_address as $add_key => $add_val ){
                            update_user_meta($user_id,$add_key,$add_val);
                        }
                    }
                    $j++;
                }
            }
            $data['default_shipping'] = 'address_1';
            if( $i > 1 ){
                update_user_meta($user_id,THMAF_Utils::ADDRESS_KEY,$data);
            }
            update_user_meta($user_id,'sap_customer_address',$addresses);
        }
    }
}