<?php if ( isset( $is_purchasable ) && ! $is_purchasable ): ?>
    <div class="wp-block-button show-product-quote mb-3" data-action="<?php echo $product_id ?? 0 ?>">
        <a class="wp-block-button__link has-text-align-center wp-element-button"
           href="#"><?php echo __( 'REQUEST A QUOTE', 'cabling' ); ?></a>
    </div>
<?php endif ?>

<?php if ( ! empty( $key_features ) ): ?>
    <div class="product-overview ">
        <div class="pdp-attribute mb-3">
			<?php echo $key_features ?>
        </div>
    </div>
<?php endif ?>
