<div class="taxonomy-row row g-5">
    <?php foreach ($taxonomies as $taxonomy): ?>
        <?php
        $custom_title = get_field('custom_title', $taxonomy);
        $thumbnail_id = get_field('taxonomy_image', $taxonomy);
        $thumbnail_id = empty($thumbnail_id) ? 1032601 : $thumbnail_id;
        $thumbnail = wp_get_attachment_image($thumbnail_id, 'full', false, array('class' => 'p-0'));
        $link = get_term_link($taxonomy);
        $surfaceEquipmentId = get_surface_equipment_id();
        $surfaceEquipment = get_term($surfaceEquipmentId, 'product_group');

	    $data = array(
		    'attributes' => array(
			    'sellable' => '1',
			    'product_line' => [$taxonomy->term_id],
		    )
	    );
	    $buyNowLink = add_query_arg( array(
		    'group' => $surfaceEquipment->slug,
		    'surface_line' => $taxonomy->slug,
		    'data-history' => base64_encode(json_encode($data)),
	    ), home_url( '/products-and-services' ) );
        $buyNow = taxonomy_has_products_with_price($taxonomy->term_id);
        $title = $taxonomy->name;
        if (!empty($custom_title)){
            $title = $custom_title;
        }
        ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="tax-item wp-block-image size-full">
                <a class="d-block" style="color: inherit" href="javascript:void(0)"><?php echo $thumbnail ?></a>
                <h4 class="wp-caption my-3">
                    <a style="color: inherit" href="javascript:void(0)"><?php echo $title; ?></a>
                </h4>
                <div class="cat-description mb-3">
                    <?php echo nl2br( wp_kses_post( $taxonomy->description ) ); ?>
                </div>
                <div class="cat-actions d-flex align-items-center gap-3 flex-wrap">
                    <a href="<?php echo esc_url($link) ?>"
                       class="block-button mt-0"><?php echo __('Find out more', 'cabling'); ?></a>
                    <?php if ($buyNow): ?>
                        <div class="wp-block-button rubber-quote-button">
                            <a class="wp-block-button__link has-text-align-center wp-element-button"
                               href="<?php echo esc_url($buyNowLink) ?>"><?php echo __('BUY NOW', 'cabling') ?></a>
                        </div>
                    <?php else: ?>
                        <div class="wp-block-button show-product-quote rubber-quote-button">
                            <a class="wp-block-button__link has-text-align-center wp-element-button"
                               href="/request-a-quote/"><?php echo __('REQUEST A QUOTE', 'cabling') ?></a>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
