<?php
/**
 * Proceed to checkout button
 *
 * Contains the markup for the proceed to checkout button on the cart.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/proceed-to-checkout-button.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( has_surface_equipment_on_cart() ) {
	$buy_online_link = get_field('buy_online_link_doublee', 'option');
} else {
	$buy_online_link = get_field('buy_online_link', 'option');
}

// GE-252
if ( ! empty($_SERVER['HTTP_REFERER']) ) {
	$ref_url = esc_url_raw($_SERVER['HTTP_REFERER']);
	$post_id = url_to_postid($ref_url);

	if ( $post_id && get_post_type($post_id) === 'product' ) {
		WC()->session->set( 'dw_continue_shopping_url_last', $ref_url );
		$stored_last = WC()->session->get( 'dw_continue_shopping_url_last' );
	}elseif( strpos($ref_url, 'products-and-services') !== false || strpos($ref_url, 'product-type') !== false ) {
		WC()->session->set( 'dw_continue_shopping_url_last', $ref_url );
		$stored_last = WC()->session->get( 'dw_continue_shopping_url_last' );
	}
}

$stored      = WC()->session->get( 'dw_continue_shopping_url' );
$stored_last = WC()->session->get( 'dw_continue_shopping_url_last' );

if ( ! empty( $stored_last ) ) {
    $dv_last   = get_query_param_value_gi( $stored_last, 'detailed-view' );
    $dv_stored = get_query_param_value_gi( $stored, 'detailed-view' );
    if ( $dv_last !== null && $dv_stored !== null && $dv_last !== $dv_stored ) {
        $buy_online_link = $stored;
    } else {
        $buy_online_link = $stored_last;
    }
} elseif ( ! empty( $stored ) ) {
    $buy_online_link = $stored;
}
?>

<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button btn btn-primary alt wc-forward<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
	<?php esc_html_e( 'Checkout', 'woocommerce' ); ?>
</a>

<a href="<?= $buy_online_link; ?>" class="mt-2 checkout-button btn btn-primary alt wc-forward<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
	<?php esc_html_e( 'Continue Shopping', 'woocommerce' ); ?>
</a>
