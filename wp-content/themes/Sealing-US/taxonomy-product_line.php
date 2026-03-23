<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package cabling
 */

$category = get_queried_object();
$thumbnail_id = get_field('taxonomy_image', $category);
$thumbnail_id = empty($thumbnail_id) ? 1032601 : $thumbnail_id;
$thumbnail = wp_get_attachment_image($thumbnail_id, 'full', false, array('class' => 'wp-post-image'));

get_header();
?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <header class="page-header has-background">
                <h1 class="entry-title"><?php echo get_queried_object()->name ?></h1>
                <?php echo $thumbnail ?>
            </header><!-- .entry-header -->
            <div class="container">
                <div class="blog-wrap list-production-equipment">
                    <div class="archive-description pt-3 pb-5 text-center fs-5">
                        <?php echo apply_filters('the_content', $category->description) ?>
                    </div>
                    <?php if (have_posts()) : ?>

                        <div class="taxonomy-row">
                            <table class="table border">
                                <tbody>
                                <?php
                                /* Start the Loop */
                                while (have_posts()) :
                                    the_post();
                                    $product = wc_get_product(get_the_ID());
                                    if (has_post_thumbnail()) {
                                        $featured_image = get_the_post_thumbnail($product->get_id(), 'large', array('class' => 'taxonomy-featured'));
                                    } else {
                                        $thumbnail_id = 1032601;
                                        $featured_image = wp_get_attachment_image($thumbnail_id, 'large', false, array('class' => 'taxonomy-featured'));
                                    }
                                    $link = get_the_permalink($product->get_id());
                                    $buyNow = $product->is_purchasable();
                                    ?>
                                    <tr class="tax-item pb-5">
                                        <td class="p-0">
                                            <?php echo $featured_image ?>
                                        </td>
                                        <td>
                                            <h3 class="wp-caption my-3">
                                                <?php the_title() ?>
                                            </h3>
                                            <div class="description" style="white-space: break-spaces"><?php echo apply_filters('the_content', $post->post_excerpt) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($product->is_purchasable()): ?>
                                                <div class="wp-block-button">
                                                    <a class="wp-block-button__link has-text-align-center wp-element-button add_to_cart_button ajax_add_to_cart"
                                                       href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                                                       data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                                                       data-quantity="1"
                                                       aria-label="<?php echo esc_attr__('Add "' . $product->get_name() . '" to your cart', 'cabling'); ?>"
                                                       rel="nofollow"><?php echo __('Buy Now', 'cabling'); ?></a>
                                                </div>
                                            <?php else: ?>
                                                <div class="wp-block-button show-product-quote rubber-quote-button">
                                                    <a class="wp-block-button__link has-text-align-center wp-element-button"
                                                       href="#"><?php echo __('REQUEST A QUOTE', 'cabling'); ?></a>
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
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
                    else :

                        get_template_part('template-parts/content', 'none');

                    endif;
                    ?>
                </div>
        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();
