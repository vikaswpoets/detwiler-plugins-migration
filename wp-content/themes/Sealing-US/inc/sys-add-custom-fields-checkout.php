<?php
// Add PO Number field before the order review section
add_action('woocommerce_review_order_before_payment', 'add_po_number_note');
add_action('woocommerce_review_order_before_payment', 'add_po_number_field_before_order_review');

function add_po_number_note() {
	echo '<p>To help you save on shipping costs, if you’ve ordered a mix of items that are in and out of stock, we’ll send all items together once all are available to ship. If you need one of the items right away, we recommend to place a separate order for it.</p>';
	$isDoubleEProduct = false;
	$hasMinimumFee = false;

	if ( has_surface_equipment_on_cart() ) {
		$isDoubleEProduct = true;
	}
	foreach ( WC()->cart->get_fees() as $fee ){
		if ( $fee->id == 'minimum_fee' ) {
			$hasMinimumFee = true;
		}
	}
	if ($isDoubleEProduct && $hasMinimumFee){
        echo '<p class="order-fee-notice mb-3">';
        echo __('The minimum order amount per individual item is $15. For any individual item totalling below $15, the difference between the price and $15 will be included.', 'cabling');
        echo '</p>';
	}
}
function add_po_number_field_before_order_review() {
    woocommerce_form_field('po_number', array(
        'type'        => 'text',
        'class'       => array('po-number form-row-wide'),
        'label'       => __('Enter your PO Number'),
        'placeholder' => __('PO Number'),
    ), '');
}

// Save PO Number field to order meta
add_action('woocommerce_checkout_update_order_meta', 'save_po_number_to_order_meta');

function save_po_number_to_order_meta($order_id) {
    if (!empty($_POST['po_number'])) {
        update_post_meta($order_id, '_po_number', sanitize_text_field($_POST['po_number']));
    }
}

// Display PO Number in the order admin panel
add_action('woocommerce_admin_order_data_after_billing_address', 'display_po_number_in_admin_order', 10, 1);

function display_po_number_in_admin_order($order) {
    $po_number = get_post_meta($order->get_id(), '_po_number', true);
    if (!empty($po_number)) {
        echo '<p><strong>' . __('PO Number') . ':</strong> ' . $po_number . '</p>';
    }
}
