<?php
function enqueue_custom_cart_ajax_script() {
    wp_enqueue_script('custom-cart-ajax-script', get_template_directory_uri() . '/assets/js/custom-cart-ajax.js', array('jquery'), null, true);
    wp_localize_script('custom-cart-ajax-script', 'ajax_params', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_cart_ajax_script');

add_action('wp_ajax_update_cart_quantity_by_product_id', 'update_cart_quantity_by_product_id');
add_action('wp_ajax_nopriv_update_cart_quantity_by_product_id', 'update_cart_quantity_by_product_id');

function update_cart_quantity_by_product_id() {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $cart_item_key = get_cart_item_key_by_product_id($product_id);
    if ($cart_item_key) {
        WC()->cart->set_quantity($cart_item_key, $quantity);
    }
    echo json_encode([
        'msg' => $msg
    ]);
    wp_die();
}

function get_cart_item_key_by_product_id($product_id) {
    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            return $cart_item_key; // Return the cart item key
        }
    }
    return false; // Return false if the product is not found in the cart
}

