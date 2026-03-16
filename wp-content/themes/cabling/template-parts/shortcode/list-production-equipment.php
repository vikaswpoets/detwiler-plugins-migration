<div class="taxonomy-row ca">
    <table class="table border">
        <?php foreach ($posts as $postId): ?>
            <?php
            $post = get_post($postId);
            if (has_post_thumbnail($postId)) {
                $thumbnail_id = get_post_thumbnail_id($postId);
            } else {
                $thumbnail_id = 1032601;
            }
            $link = get_the_permalink($post);
            $buyNow = false;
            $terms = get_the_terms($postId, 'production_equipment_cat');
            if ($terms && !is_wp_error($terms)){
                $term = $terms[0];
                $buyNow = (bool)get_field('buy_now', $term);
            }
            ?>
            <tr class="tax-item pb-5">
                <td class="p-0">
                    <?php echo wp_get_attachment_image($thumbnail_id, 'large', false, array('class' => 'taxonomy-featured')) ?>
                </td>
                <td>
                    <h3 class="wp-caption my-3">
                        <?php echo get_the_title($post) ?>
                    </h3>
                    <div class="description"><?php echo apply_filters('the_content', $post->post_excerpt) ?></div>
                </td>
                <td>
                    <?php if ($buyNow): ?>
                        <div class="wp-block-button">
                            <a class="wp-block-button__link has-text-align-center wp-element-button"
                               href="<?php echo esc_url($link) ?>"><?php echo __('Buy Now', 'cabling'); ?></a>
                        </div>
                    <?php else: ?>
                        <div class="wp-block-button show-product-quote rubber-quote-button">
                            <a class="wp-block-button__link has-text-align-center wp-element-button"
                               href="<?php echo esc_url($link) ?>"><?php echo __('REQUEST A QUOTE', 'cabling'); ?></a>
                        </div>
                    <?php endif ?>
                </td>
                <td>
                    <div class="wp-block-button">
                        <a class="wp-block-button__link has-text-align-center wp-element-button"
                           href="<?php echo esc_url($link) ?>"><?php echo __('Spec sheet', 'cabling'); ?></a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
