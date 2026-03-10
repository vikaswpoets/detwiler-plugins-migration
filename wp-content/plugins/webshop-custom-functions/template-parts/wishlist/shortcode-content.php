<?php
if ( empty($wishlist_products) ) return;

$total_data = array();
?>
<div class="wishlist-products">
    <form action="">
        <div class="row">
            <div class="col-12 col-lg-9">
                <div class="all-wishlist">
                    <div class="row">
                        <?php foreach ($wishlist_products as $product_id): ?>
                            <?php
                            $product = wc_get_product( $product_id );
                            if ('' == $product->get_price()){
                                continue;
                            }
                            $is_surface_equipment = gi_product_has_surface_equipment($product_id);
                            if ($is_surface_equipment){
                                $product_title = get_the_title( $product_id );
                                $productTypes= get_the_terms( $product_id, 'product_custom_type' );
                                $product_name = sprintf(__('%s - %s', 'cabling'), $product_title, $productTypes[0]->name ?? '');
                                $surface_equipment_meta = get_surface_equipment_meta($product_id);
                                if (!empty($surface_equipment_meta)){
                                    $custom_meta = array();
                                    foreach ($surface_equipment_meta as $value){
                                        $custom_meta[$value['label']] = $value['value'];
                                    }
                                }
                            } else {
                                //#GID-1155: Custom fields
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
                                $product_sku      = get_post_meta( $product_id, '_sku', true );

                                $custom_meta  = array(
                                        'Material'         => $product_material,
                                    //'SKU' => $product_sku,
                                        'Product Hardness' => $product_hardness,
                                        'Dash Number'      => $product_dash_number,
                                );
                                $product_name = $product_material . ' ' . $product_hardness . ' ' . $product_dash_number;
                            }
                            ?>
                            <?php $total_data[$product->get_id()] = 1;  ?>
                            <div class="col-12 col-lg-4 col-md-4">
                                <div class="wishlist_item">
                                    <div class="product-image mb-2">
                                        <a href="<?php echo $product->get_permalink() ?>">
                                            <?php echo $product->get_image('large') ?>
                                        </a>
                                    </div>
                                    <h5 class="product-name my-2">
                                        <a href="<?php echo $product->get_permalink() ?>"><?php echo $product_name ?></a>
                                    </h5>
                                    <?php if (!empty( $custom_meta )): ?>
                                        <div class="mb-3">
                                            <ul class="wl-extra-fields">
                                                <?php foreach ( $custom_meta as $wl_f => $wl_v ): ?>
                                                    <li><strong><?= $wl_f; ?>: </strong><?= $wl_v; ?></li>
                                                <?php endforeach ?>
                                            </ul>
                                        </div>
                                    <?php endif ?>
                                    <p class="product-stock mb-3"><?php echo $product->is_in_stock() ? 'In Stock' : 'Out of Stock'; ?></p>
                                    <div class="form-group mb-1">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" class="form-control quantity-input" id="quantity" name="quantity[<?php echo esc_attr($product->get_id()); ?>]" value="1">
                                    </div>
                                    <div class="mb-3">
                                        <a class="quantity-update" href="#"><?php echo __('Recalculate', 'cabling'); ?></a>
                                    </div>
                                    <?php if (!empty( $product->get_price_html() )): ?>
                                        <div class="product-price amount mb-2" style="min-height: 36px">
                                            <?php echo $product->get_price_html()?>
                                        </div>
                                    <?php endif ?>
                                    <div class="d-flex action justify-content-between">
                                        <?php if ('' !== $product->get_price()): ?>
                                            <button type="button" data-href="<?php echo esc_url(wc_get_cart_url()); ?>?add-to-cart=<?php echo esc_attr($product->get_id()); ?>"
                                               class="add-to-cart-button">
                                                <i class="fa-light fa-shopping-cart me-2"></i>
                                                <span><?php echo __('Add to cart', 'cabling'); ?></span>
                                            </button>
                                        <?php endif ?>
                                        <div data-action="<?php echo esc_attr($product->get_id()); ?>" class="product-request-button show-product-quote mb-0">
                                            <a class="btn btn-primary" href="#"><?php echo __('Request a quote', 'cabling'); ?></a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between share-action my-3">
                                        <a href="#" class="remove-wishlist-product w-action" data-product="<?php echo esc_attr($product->get_id()); ?>">
                                            <i class="fa-light fa-solid fa-x me-1"></i>
                                            <span><?php echo __('Remove', 'cabling'); ?></span>
                                        </a>
                                        <!--<a href="#" class="share-wishlist-product w-action" data-product="<?php /*echo esc_attr($product->get_id()); */?>">
                                            <i class="fa-regular fa-arrow-up-from-bracket me-1"></i>
                                            <span><?php /*echo __('Share', 'cabling'); */?></span>
                                        </a>-->
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="wishlist-total woocommerce">
                    <?php wc_get_template('template-parts/wishlist/wishlist-totals.php', ['wishlist_products' => $total_data], '', WBC_PLUGIN_DIR); ?>
                </div>
            </div>
        </div>
    </form>
</div>


<script>
    jQuery('.add-to-cart-button').on('click',function(){
        let href = jQuery(this).data('href');
        let qty = jQuery(this).closest('.wishlist_item').find('.quantity-input').val();
        href += '&quantity='+qty
        window.location.href = href;
    })
</script>
