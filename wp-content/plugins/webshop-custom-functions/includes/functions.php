<?php
function wishlist_totals_subtotal_html($wishlist_products)
{
    $totals = 0;
    if (!empty($wishlist_products)) {
        foreach ($wishlist_products as $wishlist => $qty) {
            $product = wc_get_product($wishlist);
            $totals += (float)$product->get_price() * intval($qty);
        }
    }

    return wc_price($totals);
}

function get_user_wishlist( $user_id = 0) {
	$wishlist_products = array();
	if (!empty($user_id) || is_user_logged_in() ) {
		$wishlist_products = isset( $_COOKIE['wishlist_products'] ) ? @json_decode( stripslashes($_COOKIE['wishlist_products']) , true) : [];
	}
	return $wishlist_products;
}
