<?php
/**
 * Show the list of post by date
 * @param $atts
 * @return false|string
 */
function webshop_show_categories_shortcode($atts)
{
    extract(shortcode_atts(array(
        'taxonomy' => '',
        'custom_template' => 'no',
        'meta_filter' => ''
    ), $atts));

    $taxonomySlug = $atts['taxonomy'];
    if ($taxonomySlug === 'production_equipment_cat'){
        //if the taxonomy is product equipment, we will get the product line of surface equipment
        $args = array(
            'taxonomy' => 'product_line',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => 'group_category',
                    'value' => get_surface_equipment_id(),
                    'compare' => '=',
                )
            ),
            'orderby' => 'term_order',
        );
    } else {
        $args = array(
            'taxonomy' => $taxonomySlug,
            'hide_empty' => false,
            'parent' => 0,
            'exclude' => [7889],
            'orderby' => 'term_order',
        );
        if (!empty($atts['meta_filter'])) {
            $args['meta_query'] = array(
                array(
                    'key' => 'product_type',
                    'value' => $atts['meta_filter'],
                    'compare' => '=',
                ),
            );
        }
    }
    $taxonomies = get_terms($args);

    ob_start();
    if (!empty($taxonomies)): ?>
        <div class="taxonomies-list mt-4 category-block <?php echo 'list-' . $taxonomySlug; ?>">
            <div class="container">
                <?php if (isset($atts['custom_template']) && $atts['custom_template'] === 'yes'): ?>
                    <?php include get_template_directory() . '/template-parts/shortcode/list-' . $atts['taxonomy'] . '.php' ?>
                <?php else: ?>
                    <?php if ($atts['use_slider']): ?>
                        <div class="taxonomy-slider"
                             data-flickity='{ "cellAlign": "left", "contain": true, "prevNextButtons": false, "pageDots": false }'>
                            <?php foreach ($taxonomies as $taxonomy): ?>
                                <?php
                                if ($atts['taxonomy'] === 'product_cat') {
                                    $thumbnail_id = get_term_meta($taxonomy->term_id, 'thumbnail_id', true);
                                } else {
                                    $thumbnail_id = get_field('taxonomy_image', $taxonomy);
                                }
                                $thumbnail_id = empty($thumbnail_id) ? 1032601 : $thumbnail_id;
                                $thumbnail = wp_get_attachment_image($thumbnail_id, 'full');
                                ?>
                                <div class="carousel-cell wp-block-image size-full" style="position: relative; ">
                                    <span class="wp-element-caption"><?php echo $taxonomy->name; ?></span>
                                    <?php echo $thumbnail ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <?php include get_template_directory() . '/template-parts/shortcode/list-' . $atts['taxonomy'] . '.php' ?>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    <?php endif;
    return ob_get_clean();
}

add_shortcode('webshop_show_categories', 'webshop_show_categories_shortcode');
/**
 * Show the list of post by date
 * @param $atts
 * @return false|string
 */
function webshop_show_posts_shortcode($atts)
{
    $args = array(
        'post_type' => $atts['post_type'] ?? 'post',
        'posts_per_page' => 10,
        'fields' => 'ids',
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );

    if (!empty($atts['taxonomy'])) {
        $term = get_term($atts['taxonomy']);
        if ($term) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,

                )
            );
        }
    }

    $posts = get_posts($args);

    ob_start();
    if (!empty($posts)): ?>
        <div class="posts-list category-block <?php echo 'list-' . $atts['post_type'] ?>">
            <div class="container">
                <?php if (isset($atts['custom_template']) && $atts['custom_template'] === 'yes'): ?>
                    <?php include get_template_directory() . '/template-parts/shortcode/list-' . $atts['post_type'] . '.php' ?>
                <?php else: ?>
                    <?php if ($atts['use_slider']): ?>
                        <div class="taxonomy-slider"
                             data-flickity='{ "cellAlign": "left", "contain": true, "prevNextButtons": false, "pageDots": false }'>
                            <?php foreach ($posts as $postId): ?>
                                <div class="slide-item">
                                    <?php include get_template_directory() . '/template-parts/shortcode/list-' . $atts['post_type'] . '.php' ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <?php include get_template_directory() . '/template-parts/shortcode/list-' . $atts['post_type'] . '.php' ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif;
    return ob_get_clean();
}

add_shortcode('webshop_show_posts', 'webshop_show_posts_shortcode');

add_shortcode('gi_checkout_alert', 'gi_checkout_alert_callback');
function gi_checkout_alert_callback( $atts = array() ){

    $atts = shortcode_atts(
            array(
                    'contact_url'    => home_url( '/contact-form' ),
                    'back_label'     => __( 'Go back', 'gi' ),
                    'contact_label'  => __( 'Contact us', 'gi' ),
            ),
            $atts,
            'gi_checkout_alert'
    );

    $message     = __( 'Our apologies — we’ve unfortunately encountered an error on our side with your order.', 'gi' );
    $instruction = __( 'Please press the back button to resubmit — or contact our sales team directly.', 'gi' );

    ob_start();
    ?>
    <div class="gi-checkout-alert text-center mb-5">
        <p><?php echo esc_html( $message ); ?></p>
        <p><?php echo esc_html( $instruction ); ?></p>
        <div class="gi-actions mt-5">
            <a href="#" class="add-to-cart-button gi-back me-3" onclick="history.back(); return false;">
                <?php echo esc_html( $atts['back_label'] ); ?>
            </a>
            <a href="<?php echo esc_url( $atts['contact_url'] ); ?>" class="add-to-cart-button gi-contact">
                <?php echo esc_html( $atts['contact_label'] ); ?>
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
