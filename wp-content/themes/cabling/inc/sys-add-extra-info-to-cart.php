<?php
add_filter('woocommerce_add_cart_item_data', 'add_custom_fields_to_cart_item', 10, 3);
function add_custom_fields_to_cart_item($cart_item_data, $product_id, $variation_id) {
    $dynamic_fields = get_field('product_dynamic_fields', $product_id);
    //o-ring standard
    $o_ring_standard_value = '';
    if (!empty($dynamic_fields)) {
        $o_ring_standard_index = array_search('O-RING SIZE STANDARD', array_column($dynamic_fields, 'label'));
        if ($o_ring_standard_index !== false) {
            $o_ring_standard_value = $dynamic_fields[$o_ring_standard_index]['value'];
        }
    }

    $product_material = get_post_meta($product_id,'product_material',true);
    $product_material = $product_material ? get_post_field( 'post_title', $product_material ) : '';

    $product_dash_number = get_post_meta($product_id,'product_dash_number',true);
    $product_dash_number = str_replace('AS', '', $product_dash_number);
    $product_dash_number = $o_ring_standard_value ? $o_ring_standard_value.'-'.$product_dash_number : $product_dash_number;

    $product_hardness = get_post_meta($product_id,'product_hardness',true);
    $product_sku = get_post_meta($product_id,'_sku',true);
    // Example: Adding custom fields
    $custom_meta = array(
        'custom_field_1' => $product_material,
        //'custom_field_2' => $product_sku,
        'custom_field_3' => $product_hardness,
        'custom_field_4' => $product_dash_number,
    );

    // Merging custom fields with existing cart item data
    $cart_item_data = array_merge($cart_item_data, $custom_meta);

    return $cart_item_data;
}


add_filter('woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2);
function display_custom_fields_in_cart($item_data, $cart_item) {
	if (gi_product_has_surface_equipment($cart_item['product_id'])) {
		if (!empty( $cart_item['short_description'] )) {
			$item_data[] = array(
				'name'  => 'Description',
				'value' => $cart_item['short_description'],
			);
		}
	} else {

	    if (isset($cart_item['custom_field_1'])) {
	        $item_data[] = array(
	            'name' => 'Material',
	            'value' => is_array($cart_item['custom_field_1']) ? implode(', ',$cart_item['custom_field_1']) : $cart_item['custom_field_1'],
	        );
	    }
		/*
	    if (isset($cart_item['custom_field_2'])) {
	        $item_data[] = array(
	            'name' => 'SKU',
	            'value' => is_array($cart_item['custom_field_2']) ? implode(', ',$cart_item['custom_field_2']) : $cart_item['custom_field_2'],
	        );
	    }
		*/
	    if (isset($cart_item['custom_field_3'])) {
	        $item_data[] = array(
	            'name' => 'Product Hardness',
	            'value' => is_array($cart_item['custom_field_3']) ? implode(', ',$cart_item['custom_field_3']) : $cart_item['custom_field_3'],
	        );
	    }

	    if (isset($cart_item['custom_field_4'])) {
	        $item_data[] = array(
	            'name' => 'Dash Number',
	            'value' => is_array($cart_item['custom_field_4']) ? implode(', ',$cart_item['custom_field_4']) : $cart_item['custom_field_4'],
	        );
	    }
	}

    return $item_data;
}

add_action('woocommerce_add_order_item_meta', 'save_custom_fields_to_order', 10, 2);
function save_custom_fields_to_order($item_id, $cart_item) {
    if (isset($cart_item['custom_field_1'])) {
        wc_add_order_item_meta($item_id, '_custom_field_1', $cart_item['custom_field_1']);
    }
    if (isset($cart_item['custom_field_2'])) {
        wc_add_order_item_meta($item_id, '_custom_field_2', $cart_item['custom_field_2']);
    }
    if (isset($cart_item['custom_field_3'])) {
        wc_add_order_item_meta($item_id, '_custom_field_3', $cart_item['custom_field_3']);
    }
    if (isset($cart_item['custom_field_4'])) {
        wc_add_order_item_meta($item_id, '_custom_field_4', $cart_item['custom_field_4']);
    }

    if (isset($cart_item['alternate_sku'])) {
        wc_add_order_item_meta($item_id, 'alternate_sku', $cart_item['alternate_sku']);
    }
}

add_filter('woocommerce_order_item_display_meta_key', 'display_custom_fields_in_order', 10, 2);
function display_custom_fields_in_order($display_key, $meta) {
    if ($meta->key == '_custom_field_1') {
        $display_key = 'Material';
    }
    if ($meta->key == '_custom_field_2') {
        //$display_key = 'SKU';
        $display_key="";
    }
    if ($meta->key == '_custom_field_3') {
        $display_key = 'Product Hardness';
    }
    if ($meta->key == '_custom_field_4') {
        $display_key = 'Dash Number';
    }
    return $display_key;
}

add_filter( 'woocommerce_cart_item_name', 'custom_cart_item_name', 10, 3 );
function custom_cart_item_name( $product_name, $cart_item, $cart_item_key ) {
    $product_id = $cart_item['product_id'];

	if (gi_product_has_surface_equipment($product_id)) {
		$product_title = get_the_title( $product_id );
		$productTypes= get_the_terms( $product_id, 'product_custom_type' );
		$custom_name = sprintf(__('%s - %s', 'cabling'), $product_title, $productTypes[0]->name ?? '');
	} else {
		$dynamic_fields = get_field( 'product_dynamic_fields', $product_id );
		//o-ring standard
		$o_ring_standard_value = '';
		if ( ! empty( $dynamic_fields ) ) {
			$o_ring_standard_index = array_search( 'O-RING SIZE STANDARD', array_column( $dynamic_fields, 'label' ) );
			if ( $o_ring_standard_index !== false ) {
				$o_ring_standard_value = $dynamic_fields[ $o_ring_standard_index ]['value'];
			}
		}

		$product_material = get_post_meta( $product_id, 'product_material', true );
		$product_material = $product_material ? get_post_field( 'post_title', $product_material ) : '';

		$product_dash_number = get_post_meta( $product_id, 'product_dash_number', true );
		$product_dash_number = str_replace( 'AS', '', $product_dash_number );
		$product_dash_number = $o_ring_standard_value ? $o_ring_standard_value . '-' . $product_dash_number : $product_dash_number;

		$product_hardness = get_post_meta( $product_id, 'product_hardness', true );
		//$product_sku      = get_post_meta( $product_id, '_sku', true );
		// Example: Adding custom fields
		$custom_meta = array(
			'custom_field_1' => $product_material,
			//'custom_field_2' => $product_sku,
			'custom_field_3' => $product_hardness,
			'custom_field_4' => $product_dash_number,
		);
		$custom_name = implode( ' ', $custom_meta );
	}

    // Return the modified product name
    return $custom_name;
}

// Order again
add_filter('woocommerce_add_order_again_cart_item', 'add_custom_fields_order_again', 10, 2);
function add_custom_fields_order_again($cart_item, $cart_id) {
    $product_id = $cart_item['product_id'];

    $dynamic_fields = get_field('product_dynamic_fields', $product_id);
    //o-ring standard
    $o_ring_standard_value = '';
    if (!empty($dynamic_fields)) {
        $o_ring_standard_index = array_search('O-RING SIZE STANDARD', array_column($dynamic_fields, 'label'));
        if ($o_ring_standard_index !== false) {
            $o_ring_standard_value = $dynamic_fields[$o_ring_standard_index]['value'];
        }
    }

    $product_material = get_post_meta($product_id,'product_material',true);
    $product_material = $product_material ? get_post_field( 'post_title', $product_material ) : '';

    $product_dash_number = get_post_meta($product_id,'product_dash_number',true);
    $product_dash_number = str_replace('AS', '', $product_dash_number);
    $product_dash_number = $o_ring_standard_value ? $o_ring_standard_value.'-'.$product_dash_number : $product_dash_number;

    $product_hardness = get_post_meta($product_id,'product_hardness',true);
    //$product_sku = get_post_meta($product_id,'_sku',true);
    $custom_meta = array(
        'custom_field_1' => $product_material,
        //'custom_field_2' => $product_sku,
        'custom_field_3' => $product_hardness,
        'custom_field_4' => $product_dash_number,
    );

    foreach($custom_meta as $custom_meta_k => $custom_meta_v){
        $cart_item[$custom_meta_k] = $custom_meta_v;
    }

    return $cart_item;
}
