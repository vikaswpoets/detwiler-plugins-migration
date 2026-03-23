<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://woocommerce.com/document/template-structure/
 * @package    WooCommerce\Templates
 * @version    1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

$is_surface_equipment = $is_surface_equipment = gi_product_has_surface_equipment($product->get_id());

if ( $is_surface_equipment ) {
	$lineName = '';
	$productTypes= get_the_terms( $product->get_id(), 'product_custom_type' );
	if (!empty($productTypes[0])){
		$lineName = $productTypes[0]->name;
	}
	the_title( '<h1 class="product_title fw-bold">' . $lineName . ' - ', '</h1>' );

	cabling_woocommerce_show_surface_equipment_meta($product->get_id());
} else {
	the_title( '<h1 class="product_title entry-title">', '</h1>' );
}
