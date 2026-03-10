<?php
/*
* GID 1121
* As a general rule – all images we show under the following areas (Wishlist, My Cart, etc.) should have O-Ring images used…as shown below
*/
define('PRODUCT_IMAGE_ORING','/wp-content/uploads/2024/01/o-rings.jpg');
add_filter('woocommerce_cart_item_thumbnail', 'gi_filter_cart_product_image', 10, 3);
add_filter('woocommerce_product_get_image', 'gi_filter_product_image', 10, 2);

function gi_filter_product_image($image_html, $product){

    //For Wishlist
    if( !is_page('my-wishlist') ){
        return $image_html;
    }
    $product_id = $product->get_id();
    $categories = get_the_terms($product_id, 'product_cat');
    $category = $categories[0];
    if(@$category->name == 'O-Rings'){
        $new_product_image = '<img src="' .PRODUCT_IMAGE_ORING . '" alt="' . esc_attr($product->get_name()) . '" />';
        return $new_product_image;
    }
    return $image_html;
}
function gi_filter_cart_product_image($product_image, $cart_item, $cart_item_key){
    $product_id = $cart_item['product_id'];
    $categories = get_the_terms($product_id, 'product_cat');
    $category = $categories[0];
    if(@$category->name == 'O-Rings'){
        $product = wc_get_product($product_id);
        $new_product_image = '<img src="' .PRODUCT_IMAGE_ORING . '" alt="' . esc_attr($product->get_name()) . '" />';
        return $new_product_image;
    }
    return $product_image;
}
