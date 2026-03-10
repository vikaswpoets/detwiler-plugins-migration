<?php

function cabling_add_quote_button($product_id = 0)
{
    $product_id = is_product() ? get_the_ID() : $product_id;
    echo '<div class="d-flex align-items-center">';
    echo '<div data-action="' . $product_id . '" class="product-request-button show-product-quote">';
    echo '<a class="btn btn-primary" href="#">' . __('Request a quote – buy via our sales team', 'cabling') . '</a>';
    echo '</div>';

    if (is_product()) {
        $user_id = get_current_user_id();
        $wishlist_products = get_user_wishlist($user_id);
        $class = '';

        if (is_array($wishlist_products) && in_array($product_id, $wishlist_products)) {
            $class = 'has-wishlist';
        }

        ob_start(); ?>
        <a href="#" class="add-to-cart-button add-to-wishlist ms-2 <?php echo $class ?>"
           data-product="<?php echo esc_attr($product_id); ?>">
            <i class="fa-light fa-heart me-2"></i>
            <span><?php echo __('Add to wishlist', 'cabling'); ?></span>
        </a>
        <?php
        echo ob_get_clean();
    }
    echo '</div>';
}

add_action('woocommerce_no_products_found', 'woocommerce_no_products_quote', 99);
function woocommerce_no_products_quote()
{
    cabling_add_quote_button();
}
