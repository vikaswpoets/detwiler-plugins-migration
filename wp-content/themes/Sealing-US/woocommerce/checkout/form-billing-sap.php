<?php
$customer_id = get_current_user_id();
$custom_address = get_user_meta($customer_id, 'sap_customer_address',true);
$billing_address = [];
if( is_array($custom_address) && count($custom_address) ){
    foreach( $custom_address as $key => $addresse ){
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
            $billing_address[] = $data_address;
        }
    }
}
?>
<div class="same-shipping-address align-items-center justify-content-between">
    <label class="billing-label" for="same-shipping-address">
        <input class="d-none" type="checkbox" id="diff-shipping-address" value="1" checked>
        <input class="d-none" type="checkbox" id="same-shipping-address" value="1">
        <input type="hidden" name="same-as-shipping" value="1">
        <!-- <span>SAME AS DELIVERY ADDRESS</span> -->
    </label>
    <div class="sap_billing_address">
    <?php if( count($billing_address) ) : ?>
        <div class="sap_billing_address__items">
            <?php foreach($billing_address as $key => $billing_addres) : ?>
            <div class="address-item d-flex p-2 mb-3">
                <input name="billing_address_sap" type="radio" <?= $key < 1 ? 'checked': ''; ?> class="form-check sap-billing-address-item" data-type="billing" data-id="billing_address_1"
                data-billing_first_name="<?= $billing_addres['billing_first_name']; ?>"
                data-billing_last_name="<?= $billing_addres['billing_last_name']; ?>"
                data-billing_company="<?= $billing_addres['billing_company']; ?>"
                data-billing_address_1="<?= $billing_addres['billing_address_1']; ?>"
                data-billing_city="<?= $billing_addres['billing_city']; ?>"
                data-billing_state="<?= $billing_addres['billing_state']; ?>"
                data-billing_postcode="<?= $billing_addres['billing_postcode']; ?>"
                >
                <div class="address-details ms-3">
                    <h4><?= $billing_addres['billing_company'];?></h4>
                    <ul>
                        <li><?= $billing_addres['billing_address_1'];?></li>
                        <li><?= $billing_addres['billing_city'];?></li>
                        <li><?= $billing_addres['billing_state'];?>, <?= $billing_addres['billing_postcode'];?></li>
                        <li><?= $billing_addres['billing_country'];?></li>
                    </ul>
                </div>
            </div>
            <?php endforeach;?>
        </div>
        <div class="sap_billing_address__form">
            <input type="hidden" value="<?= $billing_address[0]['billing_first_name']; ?>" name="billing_first_name" id="sap_billing_first_name">         
            <input type="hidden" value="<?= $billing_address[0]['billing_last_name']; ?>" name="billing_last_name" id="sap_billing_last_name">         
            <input type="hidden" value="<?= $billing_address[0]['billing_company']; ?>" name="billing_company" id="sap_billing_company">         
            <input type="hidden" value="<?= $billing_address[0]['billing_address_1']; ?>" name="billing_address_1" id="sap_billing_address_1">         
            <input type="hidden" value="<?= $billing_address[0]['billing_city']; ?>" name="billing_city" id="sap_billing_city">         
            <input type="hidden" value="<?= $billing_address[0]['billing_state']; ?>" name="billing_state" id="sap_billing_state">         
            <input type="hidden" value="<?= $billing_address[0]['billing_postcode']; ?>" name="billing_postcode" id="sap_billing_postcode">         
        </div>
    <?php endif;?>
    </div>
    <?php if( count($billing_address) ) : ?>
    <div class="wp-block-button button-row block-button-black d-flex">
        <button class="ml-auto js-btn-prev back-carrier-step wp-element-button" type="button" title="Back">Back</button>
        <button class="wp-element-button ml-auto continue-to-order" type="button"
                title="<?php _e('Continue', 'cabling') ?>"><?php _e('Continue', 'cabling') ?></button>
    </div>
    <?php endif;?>
</div>

<script>
    jQuery( document ).ready( function(){
        jQuery('.sap-billing-address-item').on('click',function(){
            console.log( jQuery( '#sap_billing_address_1' ) );
            console.log( jQuery(this).data('billing_address_1') );
            
            jQuery( '#sap_billing_first_name' ).val( jQuery(this).data('billing_first_name') );
            jQuery( '#sap_billing_last_name' ).val( jQuery(this).data('billing_last_name') );
            jQuery( '#sap_billing_company' ).val( jQuery(this).data('billing_company') );
            jQuery( '#sap_billing_address_1' ).val( jQuery(this).data('billing_address_1') );
            jQuery( '#sap_billing_city' ).val( jQuery(this).data('billing_city') );
            jQuery( '#sap_billing_state' ).val( jQuery(this).data('billing_state') );
            jQuery( '#sap_billing_postcode' ).val( jQuery(this).data('billing_postcode') );
        });
    });
</script>

