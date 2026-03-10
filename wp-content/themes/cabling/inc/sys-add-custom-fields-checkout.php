<?php
// Add PO Number field before the order review section
add_action('woocommerce_review_order_before_payment', 'add_po_number_note');
add_action('woocommerce_review_order_before_payment', 'add_po_number_field_before_order_review');

function add_po_number_note() {
    echo '<p>A note If you have ordered a mix of items that are in and out of stock: to save you on costs, shipping will be done in one shipment once all items are ready to ship. If you need in stock items immediately, best to place a separate order for those items.</p>';
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
