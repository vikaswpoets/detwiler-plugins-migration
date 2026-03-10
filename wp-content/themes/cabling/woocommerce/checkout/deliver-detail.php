<?php
/**
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();
$customer_level = get_customer_level($customer_id);
$address_type = "shipping";
$addresses = THMAF_Utils::get_custom_addresses($customer_id, $address_type);
$firstIndex = '';
if (is_array($addresses) && count($addresses)) {
    $firstIndex = @array_keys($addresses)[0];
    // Remove duplicate
    if( isset($addresses['address_0']) && isset($addresses['address_1']) ){
        if(
            $addresses['address_0']['shipping_first_name'] == $addresses['address_1']['shipping_first_name'] &&
            $addresses['address_0']['shipping_last_name'] == $addresses['address_1']['shipping_last_name'] &&
            $addresses['address_0']['shipping_postcode'] == $addresses['address_1']['shipping_postcode']
        ){
            unset($addresses['address_1']);
        }
    }
}
$default_shipping = $firstIndex;
$custom_address = get_user_meta($customer_id, THMAF_Utils::ADDRESS_KEY);
if( is_array($custom_address) && count($custom_address) ){
    foreach( $custom_address as $custom_addres ){
        if( isset( $custom_addres['default_shipping'] ) ){
            $default_shipping = $custom_addres['default_shipping'];
        }
    }
}
$customer_level = get_customer_level($customer_id);
?>
<div class="woocommerce-shipping-blocks mt-3">
    <?php if (!empty($addresses)): ?>
        <?php foreach($addresses as $address_key => $address): ?>
            <?php $address_key_param = "'".$address_key."'"; ?>
            <div class="address-item d-flex p-2 mb-3" data-address-key="<?php echo $address_key_param ?>" data-address='<?php echo wp_json_encode($address) ?>' >
                <input type="radio" 
                <?= ( $default_shipping == $address_key ) ? 'checked' : '' ?>
                value="<?php echo $address_key_param ?>" name="select-shipping-address" class="form-check" data-type="<?php echo $address_type ?>" data-id=<?php echo $address_key_param ?>>
                <div class="address-details ms-3">
                    <h4><?php echo $address['shipping_company']?></h4>
                    <ul>
                        <li><?php echo $address['shipping_address_1'] ?></li>
                        <?php if (!empty($address['shipping_address_2'])): ?>
                            <li><?php echo $address['shipping_address_2'] ?></li>
                        <?php endif ?>
                        <li><?php echo $address['shipping_city'] ?></li>
                        <li><?php echo ($address['shipping_state'] ?? '') . ', ' . $address['shipping_postcode'] ?></li>
                        <li><?php echo $address['shipping_country'] ?></li>
                    </ul>
                </div>
                <div class="address-actions">
                    <?php if($customer_level == 1):?>
                    <a class="edit-address" href="#" onclick="gi_edit_selected_address(this, '<?php echo $address_type ?>', <?php echo $address_key_param ?>)"><?php echo __('Edit', 'cabling') ?></a>
                    <a class="remove-address" href="#" onclick="thmaf_delete_selected_address(this, '<?php echo $address_type ?>', <?php echo $address_key_param ?>)"><?php echo __('Remove', 'cabling') ?></a>
                    <?php endif;?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">Please add your addresses to continue !</p>
    <?php endif; ?>
</div>
