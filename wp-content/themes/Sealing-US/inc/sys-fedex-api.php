<?php
/*
Authen: https://developer.fedex.com/api/en-us/catalog/authorization/v1/docs.html
Rate: https://developer.fedex.com/api/en-us/catalog/rate/v1/docs.html
Tracking: https://developer.fedex.com/api/en-us/catalog/track/v1/docs.html
*/
class SysFedExApi {
    private $show_debug             = true;
    private $rateservice_version    = 31;
    private $api_url                = 'https://apis-sandbox.fedex.com'; // https://apis.fedex.com
    private $production             = false;
    private $client_id              = '';
    private $client_secret          = '';
    private $accountNumber          = '';
    private $carrierCodes           = ['FDXG']; // FedEx Ground
    private $serviceType            = 'FEDEX_GROUND';
    private $shippingQuoteType      = 'NET_FEDEX_CHARGE'; // BASE_CHARGE, NET_FEDEX_CHARGE, NET_CHARGE, DUTIES_AND_TAXES
    private $fallback_rate_price    = 20;
    private $shipper = [
        'postcode'    => '65247'
    ];
    private $recipient = [];
    public function __construct() {
        // Fetch WooCommerce FedEx settings using the wf_fedex_woocommerce_shipping method
        $settings = $this->wf_fedex_woocommerce_shipping();
$tmp_cart=WC()->cart->get_cart();
$tmp_first_el=$tmp_cart[array_key_first($tmp_cart)];
$is_ee=gi_product_has_surface_equipment($tmp_first_el['product_id']);

// $is_ee identifies if it should use Parco FEDEX or Double E fedex. Also address setup is needed
if($is_ee)
{

        // Map settings to the class properties
        $this->show_debug           = !empty($settings['show_debug']) ? $settings['show_debug'] : false;
        $this->production           = !empty($settings['production']) ? $settings['production'] : false;
        $this->client_id            = !empty($settings['client_id_dd']) ? $settings['client_id_dd'] : '';
        $this->client_secret        = !empty($settings['client_secret_dd']) ? $settings['client_secret_dd'] : '';
        $this->accountNumber        = !empty($settings['accountNumber_dd']) ? $settings['accountNumber_dd'] : '';
        $this->carrierCodes         = !empty($settings['carrierCodes_dd']) ? $settings['carrierCodes_dd'] : $this->carrierCodes;
        $this->serviceType          = !empty($settings['serviceType_dd']) ? $settings['serviceType_dd'] : $this->serviceType;
        $this->shippingQuoteType    = !empty($settings['shippingQuoteType_dd']) ? $settings['shippingQuoteType_dd'] : $this->shippingQuoteType;
        $this->fallback_rate_price  = !empty($settings['fallback_rate_price_dd']) ? (float) $settings['fallback_rate_price_dd'] : $this->fallback_rate_price;

        $this->shipper['postcode']  = !empty($settings['shipper_postcode_dd']) ? $settings['shipper_postcode_dd'] : $this->shipper['postcode'];
        $this->shipper['address']   = !empty($settings['shipper_address_dd']) ? $settings['shipper_address_dd'] : '';
        $this->shipper['city']      = !empty($settings['shipper_city_dd']) ? $settings['shipper_city_dd'] : '';
        $this->shipper['state']     = !empty($settings['shipper_state_dd']) ? $settings['shipper_state_dd'] : '';
        //$this->shipper['state']   ="TX";
}else
{

        // Map settings to the class properties
        $this->show_debug           = !empty($settings['show_debug']) ? $settings['show_debug'] : false;
        $this->production           = !empty($settings['production']) ? $settings['production'] : false;
        $this->client_id            = !empty($settings['client_id']) ? $settings['client_id'] : '';
        $this->client_secret        = !empty($settings['client_secret']) ? $settings['client_secret'] : '';
        $this->accountNumber        = !empty($settings['accountNumber']) ? $settings['accountNumber'] : '';
        $this->carrierCodes         = !empty($settings['carrierCodes']) ? $settings['carrierCodes'] : $this->carrierCodes;
        $this->serviceType          = !empty($settings['serviceType']) ? $settings['serviceType'] : $this->serviceType;
        $this->shippingQuoteType    = !empty($settings['shippingQuoteType']) ? $settings['shippingQuoteType'] : $this->shippingQuoteType;
        $this->fallback_rate_price  = !empty($settings['fallback_rate_price']) ? (float) $settings['fallback_rate_price'] : $this->fallback_rate_price;

        $this->shipper['postcode']  = !empty($settings['shipper_postcode']) ? $settings['shipper_postcode'] : $this->shipper['postcode'];
        $this->shipper['address']   = !empty($settings['shipper_address']) ? $settings['shipper_address'] : '';
        $this->shipper['city']      = !empty($settings['shipper_city']) ? $settings['shipper_city'] : '';
        $this->shipper['state']     = !empty($settings['shipper_state']) ? $settings['shipper_state'] : '';
}
        // Switch API URL if in production mode
//print_r($is_ee);die();
// wc_add_notice( 'AKI', 1);
        if ($this->production) {
            $this->api_url = 'https://apis.fedex.com';
        }
if($is_ee)
 {
$this->api_url = 'https://apis-sandbox.fedex.com';
 }

    }

    public function wf_fedex_woocommerce_shipping() {
        $settings = get_option('woocommerce_wf_fedex_woocommerce_shipping_settings');
        return array(
            'production'           => $settings['production'] === 'yes',
            'show_debug'           => $settings['show_debug'] === 'yes',
            'client_id'            => $settings['client_id'],
            'client_secret'        => $settings['client_secret'],
            'accountNumber'        => $settings['accountNumber'],
            'carrierCodes'         => $settings['carrierCodes'],
            'serviceType'          => $settings['serviceType'],
            'shippingQuoteType'    => $settings['shippingQuoteType'],
            'fallback_rate_price'  => $settings['fallback_rate_price'],

            'client_id_dd'            => $settings['client_id_dd'],
            'client_secret_dd'        => $settings['client_secret_dd'],
            'accountNumber_dd'        => $settings['accountNumber_dd'],
            'carrierCodes_dd'         => $settings['carrierCodes_dd'],
            'serviceType_dd'          => $settings['serviceType_dd'],
            'shippingQuoteType_dd'    => $settings['shippingQuoteType_dd'],
            'fallback_rate_price_dd'  => $settings['fallback_rate_price_dd'],

            'shipper_postcode_dd' => $settings['shipper_postcode_dd'],
                'shipper_address_dd' => $settings['shipper_address_dd'],
                'shipper_city_dd' =>$settings['shipper_city_dd'],
                'shipper_state_dd'=>$settings['shipper_state_dd'],
                'shipper_postcode' => $settings['shipper_postcode'],
                'shipper_address' => $settings['shipper_address'],
                'shipper_city' =>$settings['shipper_city'],
                'shipper_state'=>$settings['shipper_state'],
/*
            'shipper' => array(
                'postcode' => $settings['shipper_postcode_dd'],
                'address' => $settings['shipper_address_dd'],
                'city' =>$settings['shipper_city_dd'],
                'state'=>$settings['shipper_state_dd'],
                'postcode' => $settings['shipper_postcode_dd'],
                'address' => $settings['shipper_address_dd'],
                'city' =>$settings['shipper_city_dd'],
                'state'=>$settings['shipper_state_dd'],
        )
        */
        );
    }

	private function calculatePackages($woo_packages = [])
	{
		$fedex_requests['packages']=[];
		foreach ( $woo_packages['contents'] as $item_id => $values ) {
            $product = $values['data'];
			$totalweight=floatval($product->get_weight())  * $values['quantity'];
			while ($totalweight>0)
			{
				$val=[
                'weight' => [
                    'units' => 'KG',
                    'value' => $totalweight>68?68:$totalweight
					]
                ];
				$totalweight-=68;
				array_push($fedex_requests['packages'],$val);
			}
        }
		//echo '<pre>';echo 'total:';print_r($fedex_requests['packages']);die();
		return $fedex_requests['packages'];
	}


    public function calculate_shipping_price($woo_packages = []){
        $fedex_requests = [];

        // Set recipient
        $destination = $woo_packages['destination'];
        $this->recipient['postcode'] = $destination['postcode'];
        $this->recipient['state'] = $destination['state'];
        $this->recipient['city'] = $destination['city'];
        $this->recipient['address'] = $destination['address'];

        $fedex_requests['packages'] = [];
        // Make package request
        foreach ( $woo_packages['contents'] as $item_id => $values ) {
            $product = $values['data'];
			$fedex_requests['packages'][] = [
                'weight' => [
                    //'units' => 'LB',
                    'units' => 'KG',
                    'value' => floatval($product->get_weight())  * $values['quantity']
                ]
            ];
        }

		$fedex_requests['packages']= $this->calculatePackages($woo_packages);
        if( !count($fedex_requests['packages']) ){
            return $this->fallback_rate_price;
        }
        return $this->run_package_request($fedex_requests, 'normal_rates');
    }
    public function validate_address($address){
        /*
        $address: array('shipping_postcode','shipping_state','shipping_country);
        */
        $fedex_access_token = $this->getAcessToken();
        return $this->process_address_result( $this->get_result( $address, $fedex_access_token, 'address-validation' ) );
    }
    private function run_package_request( $request, $type = '' ) {
        try {
            $fedex_access_token = $this->getAcessToken();
            return $this->process_rate_result( $this->get_result( $request, $fedex_access_token, $type ) );
		} catch ( Exception $e ) {
			$this->debug( $e->getMessage() , 'error' );
			return $this->fallback_rate_price;
		}
	}
    private function debug( $msg, $type = 'success' ){
        if( $this->show_debug){
            wc_add_notice( $msg, $type);
        }
    }
    private function process_address_result($result){
        if( isset($result->errors) ){
            $this->debug($result->errors[0]->code.': '.$result->errors[0]->message,'error');
            return false;
        }
        if( isset($result->output->resolvedAddresses[0]) ){
            $resolvedAddresses = @$result->output->resolvedAddresses[0];
            if (isset($resolvedAddresses->attributes->Matched) && $resolvedAddresses->attributes->Matched) {
                return [
                    'postal_code' => $resolvedAddresses->parsedPostalCode->base,
                    'state' => $resolvedAddresses->stateOrProvinceCode,
                ];
            }
        }else{
            return false;
        }
    }
    private function process_rate_result($result){
//return $result;
        if( isset($result->errors) ){
            $this->debug($result->errors[0]->code.': '.$result->errors[0]->message,'error');
//print_r($result->errors[0]->code.': '.$result->errors[0]->message);
            return $this->fallback_rate_price;
        }
        $rateReplyDetails = $result->output->rateReplyDetails;
        $totalBaseCharge = 0;
        $totalNetCharge = 0;
        $totalNetFedExCharge = 0;
        $serviceName = 'FedEx';
        if( is_array($rateReplyDetails) && count($rateReplyDetails) ){
            foreach( $rateReplyDetails as $rateReplyDetail ){
                $serviceName = $rateReplyDetail->serviceName;
                if( $rateReplyDetail->serviceType = $this->serviceType ){
                    foreach( $rateReplyDetail->ratedShipmentDetails as $ratedShipmentDetail ){
                        $totalBaseCharge        = $ratedShipmentDetail->totalBaseCharge;
                        $totalNetCharge         = $ratedShipmentDetail->totalNetCharge;
                        $totalNetFedExCharge    = $ratedShipmentDetail->totalNetFedExCharge;
                        break;
                    }
                    break;
                }
            }
        }
        switch ($this->shippingQuoteType) {
            case 'BASE_CHARGE':
                $rate_price = $totalBaseCharge;
                break;
            case 'NET_FEDEX_CHARGE':
                $rate_price = $totalNetFedExCharge;
                break;
            case 'NET_CHARGE':
                $rate_price = $totalNetCharge;
                break;
            default:
                $rate_price = $totalBaseCharge;
                break;
        }
        $rate_price = $rate_price ? $rate_price : $this->fallback_rate_price;
        return $rate_price;
    }
    private function get_result( $request, $fedex_access_token = [], $type = '' ) {
        $params = $this->getParamsForApiCall($request, $type);
        $args = array(
            'body' => json_encode($params['request_body']),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$fedex_access_token['access_token']
            ),
            'method' => 'POST',
            'timeout' => 45
        );
        
        $response = wp_remote_post($params['endpoint'], $args);

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
            return false;
        } else {
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body);
            return $result;
        }
    }
    private function getAcessToken(){
        $fedex_access_token = get_transient('fedex_access_token');
        if( $fedex_access_token ){
            return $fedex_access_token;
        }
        $data = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret
        ];
        $response = wp_remote_post($this->api_url.'/oauth/token', [
            'body' => $data,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        $body = wp_remote_retrieve_body($response);
        $body = json_decode($body,true);
        if( isset($body['access_token']) ){
            set_transient('fedex_access_token', $body, $body['expires_in']);
            return $body;
        }
        throw new Exception("Fedex Access Token Error");
        return false;
    }
    private function getParamsForApiCall($request = [], $type = 'normal_rates'){
        switch ($type) {
            case 'normal_rates':
                $endpoint = $this->api_url.'/rate/v1/rates/quotes';
                $request_body = array(
                    'accountNumber' => array(
                        'value' => $this->accountNumber,
                    ),
                    'requestedShipment' => array(
                        'shipper' => $this->getShipper(),
                        'recipient' => $this->getRecipient(),
                        'serviceType' => $this->serviceType,
                        'preferredCurrency' => 'USD',
                        'rateRequestType' => array(
                            'ACCOUNT'
                        ),
                        //'shipDateStamp' => '2019-09-05',//shipment date
                        //'pickupType' => 'DROPOFF_AT_FEDEX_LOCATION',
                        'pickupType' => 'CONTACT_FEDEX_TO_SCHEDULE',                        
                        'requestedPackageLineItems' => $request['packages'],
                        'totalPackageCount' => count($request['packages']),
                        'totalWeight' => $this->getTotalWeight($request['packages'])
                    ),
                    'carrierCodes' => array('FDXG'),
                );
                break;
            case 'address-validation':
                $endpoint = $this->api_url.'/address/v1/addresses/resolve';
                $request_body = [
                    'inEffectAsOfTimestamp' => date('Y-m-d'),
                    'validateAddressControlParameters' => [
                        'includeResolutionTokens' => true
                    ],
                    'addressesToValidate' => [
                        [
                            'address' => [
                                'streetLines' => [
                                    $request['shipping_address_1']
                                ],
                                'city' => $request['shipping_city'],
                                'stateOrProvinceCode' => $request['shipping_state'],
                                'postalCode' => $request['shipping_postcode'],
                                'countryCode' => $request['shipping_country']
                            ],
                            'clientReferenceId' => 'None'
                        ]
                    ]
                ];
                break;
            default:
                # code...
                break;
        }
        $return = [
            'endpoint' => $endpoint,
            'request_body' => $request_body,
        ];

        return $return;
    }

    // shipper
    private function getShipper(){
        $shipper = array(
            'address' => array(
                'streetLines' => array(
                    $this->shipper['address']
                ),
                'city' => $this->shipper['city'],
                'stateOrProvinceCode' => $this->shipper['state'],
                'postalCode' => $this->shipper['postcode'],
                'countryCode' => 'US',
                // 'residential' => false,
            )
        );
        return $shipper;
    }
    // recipient
    private function getRecipient(){
        $recipient = array(
            'address' => array(
                'streetLines' => array(
                    $this->recipient['address']
                ),
                'city' => $this->recipient['city'],
                'stateOrProvinceCode' => $this->recipient['state'],
                'postalCode' => $this->recipient['postcode'],
                'countryCode' => 'US',
                // 'residential' => false,
            )
        );
        return $recipient;
    }
    // getTotalWeight
    private function getTotalWeight($packages){
        $totalWeight = 0;
        foreach ($packages as $package) {
            if (isset($package['weight']['value'])) {
                $totalWeight += (float) $package['weight']['value'];
            }
        }
        return $totalWeight;
    }
}

// Shipping Method Class
// Step 1: Create custom shipping method class
function gi_fedex_shipping_method_init() {
    if ( ! class_exists( 'WC_FedEx_Shipping_Method' ) ) {
        class WC_FedEx_Shipping_Method extends WC_Shipping_Method {

            public function __construct() {
                $this->id = 'wf_fedex_woocommerce_shipping'; // Unique ID for your shipping method
                //$this->method_title = __( 'DATWYLER FedEx Shipping' ); // Title for your method
                $this->method_title = __( 'FedEx Shipping' ); // Title for your method
                $this->method_description = __( 'Description of the FedEx shipping method' ); // Description
                $this->enabled = "yes"; // Enable the shipping method
                //$this->title = "DATWYLER FedEx Shipping"; // Method title
                $this->title = "FedEx Shipping"; // Method title
                $this->init();
            }

            // Initialize settings form fields and options
            public function init() {
                $this->init_form_fields(); // Define form fields for the admin
                $this->init_settings(); // Load the settings

                // Save settings
                add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
            }

            // Define settings fields
            public function init_form_fields() {
                $this->form_fields = [
                    'enabled' => [
                        'title' => __( 'Enable' ),
                        'type' => 'checkbox',
                        'description' => __( 'Enable this shipping method' ),
                        'default' => 'yes'
                    ],
                    'title' => [
                        'title' => __( 'Title' ),
                        'type' => 'text',
                        'description' => __( 'Title to be displayed during checkout' ),
                        'default' => __( 'FedEx Shipping' ),
                        'desc_tip' => true,
                    ],
                    'production' => array(
                        'title'       => __('Production Mode', 'woocommerce'),
                        'type'        => 'checkbox',
                        'label'       => __('Enable Production Mode', 'woocommerce'),
                        'default'     => 'no',
                        'description' => __('Check to enable production mode for FedEx integration.', 'woocommerce'),
                    ),
                    'show_debug' => array(
                        'title'       => __('Show Debug Information', 'woocommerce'),
                        'type'        => 'checkbox',
                        'label'       => __('Enable debug information in shipping process', 'woocommerce'),
                        'default'     => 'yes',
                        'description' => __('Check to enable showing debug information.', 'woocommerce'),
                    ),
                    'client_id' => array(
                        'title'       => __('Client ID', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter your FedEx client ID.', 'woocommerce'),
                        'default'     => 'l7c313e6b0b1d84fc8ad79a67b306f97a7',
                        'desc_tip'    => true,
                    ),
                    'client_secret' => array(
                        'title'       => __('Client Secret', 'woocommerce'),
                        'type'        => 'password',
                        'description' => __('Enter your FedEx client secret.', 'woocommerce'),
                        'default'     => '91f20638c26c4f6a83bb908cbe65af6f',
                        'desc_tip'    => true,
                    ),
                    'accountNumber' => array(
                        'title'       => __('Account Number', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter your FedEx account number.', 'woocommerce'),
                        'default'     => '740561073',
                        'desc_tip'    => true,
                    ),
                    'carrierCodes' => array(
                        'title'       => __('Carrier Codes', 'woocommerce'),
                        'type'        => 'multiselect',
                        'description' => __('Select the carrier codes for FedEx services.', 'woocommerce'),
                        'default'     => ['FDXG'],
                        'options'     => array(
                            'FDXG' => 'FedEx Ground',
                            'FDXE' => 'FedEx Express',
                            'FXSP' => 'FedEx SmartPost',
                            'FXCC' => 'FedEx Custom Critical',
                        ),
                        'desc_tip'    => true,
                    ),
                    'serviceType' => array(
                        'title'       => __('Service Type', 'woocommerce'),
                        'type'        => 'select',
                        'description' => __('Choose the FedEx service type.', 'woocommerce'),
                        'default'     => 'FEDEX_GROUND',
                        'options'     => array(
                            'FEDEX_GROUND' => 'FedEx Ground',
                            'FEDEX_2_DAY'  => 'FedEx 2 Day',
                            'FEDEX_2_DAY_AM'  => 'FedEx 2 Day AM',
                            'EXPRESS_SAVER'  => 'Express Saver',
                            'FEDEX_OVERNIGHT' => 'FedEx Overnight',
                            'PRIORITY_OVERNIGHT' => 'Priority Overnight',
                            'STANDARD_OVERNIGHT' => 'Standar Overnight',
                        ),
                        'desc_tip'    => true,
                    ),
                    'shippingQuoteType' => array(
                        'title'       => __('Shipping Quote Type', 'woocommerce'),
                        'type'        => 'select',
                        'description' => __('Choose the type of FedEx shipping quote to use.', 'woocommerce'),
                        'default'     => 'NET_FEDEX_CHARGE',
                        'options'     => array(
                            'BASE_CHARGE'        => 'Base Charge',
                            'NET_FEDEX_CHARGE'   => 'Net FedEx Charge',
                            'NET_CHARGE'         => 'Net Charge',
                            'DUTIES_AND_TAXES'   => 'Duties and Taxes',
                        ),
                        'desc_tip'    => true,
                    ),
                    'fallback_rate_price' => array(
                        'title'       => __('Fallback Rate Price', 'woocommerce'),
                        'type'        => 'number',
                        'description' => __('Enter a fallback rate price to be used if FedEx API is unavailable.', 'woocommerce'),
                        'default'     => 20,
                        'desc_tip'    => true,
                    ),
                    'shipper_postcode' => array(
                        'title'       => __('Shipper Postal Code', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the postal code for the shipper.', 'woocommerce'),
                        'default'     => '65247',
                        'desc_tip'    => true,
                    ),
                    'shipper_address' => array(
                        'title'       => __('Shipper Address', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the street address for the shipper.', 'woocommerce'),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'shipper_city' => array(
                        'title'       => __('Shipper City', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the city for the shipper.', 'woocommerce'),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'shipper_state' => array(
                        'title'       => __('Shipper State', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the state for the shipper.', 'woocommerce'),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),


/// HERE STARTS DOUBLE E
            
                    'client_id_dd' => array(
                        'title'       => __('Double E Client ID', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter your FedEx client ID for Double E.', 'woocommerce'),
                        'default'     => 'l7c313e6b0b1d84fc8ad79a67b306f97a7',
                        'desc_tip'    => true,
                    ),
                    'client_secret_dd' => array(
                        'title'       => __('Double E Client Secret', 'woocommerce'),
                        'type'        => 'password',
                        'description' => __('Enter your FedEx client secret for Double E.', 'woocommerce'),
                        'default'     => '91f20638c26c4f6a83bb908cbe65af6f',
                        'desc_tip'    => true,
                    ),
                    'accountNumber_dd' => array(
                        'title'       => __('Double E Account Number', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter your FedEx account number for Double E.', 'woocommerce'),
                        'default'     => '740561073',
                        'desc_tip'    => true,
                    ),
                    'carrierCodes_dd' => array(
                        'title'       => __('Double E Carrier Codes', 'woocommerce'),
                        'type'        => 'multiselect',
                        'description' => __('Select the carrier codes for  Double E FedEx services.', 'woocommerce'),
                        'default'     => ['FDXG'],
                        'options'     => array(
                            'FDXG' => 'FedEx Ground',
                            'FDXE' => 'FedEx Express',
                            'FXSP' => 'FedEx SmartPost',
                            'FXCC' => 'FedEx Custom Critical',
                        ),
                        'desc_tip'    => true,
                    ),
                    'serviceType_dd' => array(
                        'title'       => __('Double E Service Type', 'woocommerce'),
                        'type'        => 'select',
                        'description' => __('Choose the FedEx Double E service type.', 'woocommerce'),
                        'default'     => 'FEDEX_GROUND',
                        'options'     => array(
                            'FEDEX_GROUND' => 'FedEx Ground',
                            'FEDEX_2_DAY'  => 'FedEx 2 Day',
                            'FEDEX_2_DAY_AM'  => 'FedEx 2 Day AM',
                            'EXPRESS_SAVER'  => 'Express Saver',
                            'FEDEX_OVERNIGHT' => 'FedEx Overnight',
                            'PRIORITY_OVERNIGHT' => 'Priority Overnight',
                            'STANDARD_OVERNIGHT' => 'Standar Overnight',
                        ),
                        'desc_tip'    => true,
                    ),
                    'shippingQuoteType_dd' => array(
                        'title'       => __('Double E Shipping Quote Type', 'woocommerce'),
                        'type'        => 'select',
                        'description' => __('Choose the type of Double E FedEx shipping quote to use.', 'woocommerce'),
                        'default'     => 'NET_FEDEX_CHARGE',
                        'options'     => array(
                            'BASE_CHARGE'        => 'Base Charge',
                            'NET_FEDEX_CHARGE'   => 'Net FedEx Charge',
                            'NET_CHARGE'         => 'Net Charge',
                            'DUTIES_AND_TAXES'   => 'Duties and Taxes',
                        ),
                        'desc_tip'    => true,
                    ),
                    'fallback_rate_price_dd' => array(
                        'title'       => __('Double E Fallback Rate Price', 'woocommerce'),
                        'type'        => 'number',
                        'description' => __('Enter a fallback rate price to be used if FedEx API is unavailable.', 'woocommerce'),
                        'default'     => 20,
                        'desc_tip'    => true,
                    ),
                    'shipper_postcode_dd' => array(
                        'title'       => __('Double E Shipper Postal Code', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the postal code for the shipper.', 'woocommerce'),
                        'default'     => '65247',
                        'desc_tip'    => true,
                    ),
                    'shipper_address_dd' => array(
                        'title'       => __('Double E Shipper Address', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the street address for the shipper.', 'woocommerce'),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'shipper_city_dd' => array(
                        'title'       => __('Double E Shipper City', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the city for the shipper.', 'woocommerce'),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                    'shipper_state_dd' => array(
                        'title'       => __('Double E Shipper State', 'woocommerce'),
                        'type'        => 'text',
                        'description' => __('Enter the state for the shipper.', 'woocommerce'),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),                    




                ];
            }
            // Calculate shipping costs
            public function calculate_shipping( $package = [] ) {
                $SysFedExApi = new SysFedExApi();
                $cost = $SysFedExApi->calculate_shipping_price($package);
                $rate = [
                    'id' => $this->id,
                    'label' => $this->title,
                    'cost' => $cost,
                    'calc_tax' => ''
                ];
                // Register the rate
                $this->add_rate( $rate );
            }
        }
    }
}

// Step 2: Add the gi_fedex shipping method to WooCommerce
function gi_add_fedex_shipping_method( $methods ) {
    $methods['wf_fedex_woocommerce_shipping'] = 'WC_FedEx_Shipping_Method';
    return $methods;
}

// Step 3: Hook into WooCommerce
add_action( 'woocommerce_shipping_init', 'gi_fedex_shipping_method_init' );
add_filter( 'woocommerce_shipping_methods', 'gi_add_fedex_shipping_method' );
