<?php
/*
When user login , if customer level 2 we get address and save to shipping address
GID-1081
$user_plant = 2141;
$sap_no     = 1016168;
*/
function sap_customer_address( int $user_id ) {
	try {
		if ( empty( $user_id ) ) {
			return;
		}
		$customer_level = get_customer_level( $user_id );
		if ( $customer_level == 2 ) {
			// Call SAP API get getCustomerAdresses
			$GIWebServices = new GIWebServices();
			$user_plant    = get_user_meta( $user_id, 'sales_org', true );
			$user_plant    = $user_plant ?: '2141';
			$sap_no        = get_user_meta( $user_id, 'sap_customer', true );
			$filters       = array(
				array(
					'Field'    => 'SalesOrganization',
					'Sign'     => 'eq',
					"Value"    => $user_plant,
					'Operator' => 'and',
				),
				array(
					'Field'    => 'Customer',
					'Sign'     => 'eq',
					'Value'    => $sap_no,
					'Operator' => '',
				)
			);
			$sap_addresses = $GIWebServices->makeApiRequest( 'GET_DATA_CustomerPartners_CDS', $filters );
			// End call SAP API

			$addresses = [];
			if ( ! empty( $sap_addresses['ZDD_I_SD_PIM_CustomerPartners']['ZDD_I_SD_PIM_CustomerPartnersType'] ) ) {
				$addresses = $sap_addresses['ZDD_I_SD_PIM_CustomerPartners']['ZDD_I_SD_PIM_CustomerPartnersType'];
			}

			save_sap_addresses_to_user( $user_id, is_array( $addresses ) ? $addresses : [] );
		}
	} catch ( Exception $e ) {
		error_log( 'sap_customer_address: ' . $e->getMessage() );
	}
}

function build_sap_address( array $src, string $type ): array {
	$prefix = $type . '_';

	return [
		$prefix . 'heading'    => trim( (string) ( $src['OrganizationBPName1'] ?? '' ) ) . ' ' . __( 'Address', 'cabling' ),
		$prefix . 'first_name' => trim( (string) ( $src['CustomerName'] ?? '' ) ),
		$prefix . 'last_name'  => trim( (string) ( $src['CustomerName'] ?? '' ) ),
		$prefix . 'company'    => trim( (string) ( $src['OrganizationBPName1'] ?? '' ) ),
		$prefix . 'country'    => strtoupper( trim( (string) ( $src['Country'] ?? '' ) ) ),
		$prefix . 'address_1'  => trim( (string) ( $src['StreetName'] ?? '' ) ),
		$prefix . 'address_2'  => '',
		$prefix . 'phone'      => '',
		$prefix . 'city'       => trim( (string) ( $src['CityName'] ?? '' ) ),
		$prefix . 'state'      => trim( (string) ( $src['Region'] ?? '' ) ),
		$prefix . 'postcode'   => trim( (string) ( $src['PostalCode'] ?? '' ) ),
	];
}

function update_user_meta_group( int $user_id, array $meta ): void {
	foreach ( $meta as $key => $value ) {
		update_user_meta( $user_id, $key, $value );
	}
}

/**
 * @param int $user_id
 * @param array $addresses SAP partners array
 */
function save_sap_addresses_to_user( int $user_id, array $addresses ): void {
	if ( empty( $addresses ) ) {
		return;
	}

	$data          = [];
	$shippingIndex = 1;

	$firstShipping = null;
	$firstBilling  = null;

	foreach ( $addresses as $row ) {
		$customerName    = $row['CustomerName'] ?? '';
		$partnerFunction = $row['PartnerFunction'] ?? '';

		if ( $customerName === '' ) {
			continue;
		}

		// Shipping (WE)
		if ( $partnerFunction === 'WE' ) {
			$addr = build_sap_address( $row, 'shipping' );

			if ( $addr['shipping_address_1'] !== '' && $addr['shipping_country'] !== '' ) {
				$data['shipping'][ 'address_' . $shippingIndex ] = $addr;

				if ( $firstShipping === null ) {
					$firstShipping = $addr;
				}
				$shippingIndex ++;
			}
			continue;
		}

		// Billing (RE)
		if ( $partnerFunction === 'RE' ) {
			$addr = build_sap_address( $row, 'billing' );

			if ( $firstBilling === null ) {
				$firstBilling = $addr;
			}
			continue;
		}
	}

	if ( $firstShipping !== null ) {
		update_user_meta_group( $user_id, $firstShipping );
		$data['default_shipping'] = 'address_1';
		if ( ! empty( $data['shipping'] ) && count( $data['shipping'] ) >= 1 ) {
			update_user_meta( $user_id, THMAF_Utils::ADDRESS_KEY, $data );
		}
	}

	if ( $firstBilling !== null ) {
		update_user_meta_group( $user_id, $firstBilling );
	}

	update_user_meta( $user_id, 'sap_customer_address', $addresses );
}


/**
 * Fix for users without address created in THMAF fields
 * JM 20250929
 */
add_action('wp_login','gi_update_customer_address_if_empty',9000,2);
function gi_update_customer_address_if_empty($username,$user){

    $user_id = $user->ID;

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
        $custom_addresses[$type] = $custom_address;

        update_user_meta($user_id, THMAF_Utils::ADDRESS_KEY, $custom_addresses);
    }
}