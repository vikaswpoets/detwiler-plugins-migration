<?php
    $is_in_cart = count(WC()->cart->get_cart());
?>
<?php if( !$is_in_cart ):?>
<div class="product-quote-section">
    <div class="product-quote-inner">
        <!--
        <h2><?php _e('Know what you need?', 'cabling'); ?></h2>
        <p><?php _e('Chat to one of our advisors today', 'cabling'); ?></p>
        <div class="wp-block-buttons">
            <div class="wp-block-button block-button-black">
                <a class="wp-element-button show-product-quote" data-action="<?php echo is_product() ? get_the_ID() : 0 ?>"
                href="<?php echo home_url('/request-a-quote/') ?>"><?php _e('Request a quote', 'cabling'); ?></a>
            </div>
        </div>
    -->
    </div>
</div>
<?php else: ?>
<div class="product-quote-section">
    <div class="product-quote-inner">
        <div class="wp-block-buttons">
            <div class="wp-block-button block-button-black">
                <a class="wp-element-button" data-action="<?php echo is_product() ? get_the_ID() : 0 ?>"
            href="<?php echo wc_get_checkout_url() ?>"><?php _e('Proceed to Check-Out', 'cabling'); ?></a>
            </div>
        </div>
    </div>
</div>  
<?php endif; ?>