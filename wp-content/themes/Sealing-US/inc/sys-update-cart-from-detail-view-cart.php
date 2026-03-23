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

        // GE-252
        $current_url = '';
        if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
            $current_url = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
        } elseif ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
            $current_url = home_url( add_query_arg( [], wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
        }

        if ( $current_url ) {
            $parsed = wp_parse_url( $current_url );
            $query  = [];
            if ( ! empty( $parsed['query'] ) ) {
                parse_str( $parsed['query'], $query );
            }

            $query['detailed-view'] = $quantity;

            $new_url  = $parsed['scheme'] . '://' . $parsed['host'];
            if ( ! empty( $parsed['port'] ) ) {
                $new_url .= ':' . $parsed['port'];
            }
            if ( ! empty( $parsed['path'] ) ) {
                $new_url .= $parsed['path'];
            }
            if ( ! empty( $query ) ) {
                $new_url .= '?' . http_build_query( $query );
            }
            if ( ! empty( $parsed['fragment'] ) ) {
                $new_url .= '#' . $parsed['fragment'];
            }

            $current_url = $new_url;
        }

        if ( $current_url && strpos( $current_url, wc_get_cart_url() ) === false && WC()->session ) {
            WC()->session->set( 'dw_continue_shopping_url_last', '' );
            WC()->session->set( 'dw_continue_shopping_url', $current_url );
        }
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

