<div class="rubber-descriptions text-center pb-5">
    <?php the_content(); ?>
</div>
<div class="rubber-to-metal-sections">
    <?php
    if (have_rows('page_section_rubber')) :
        $count = 0;
        while (have_rows('page_section_rubber')) : the_row();

            $image_id = get_sub_field('image');
            $title = get_sub_field('title');
            $content = get_sub_field('content');

            ?>
            <div class="rubber-section card h-100 border-0 mb-5">
                <div class="row gx-5">
                    <div class="col-12 col-md-6">
                        <?php if ($image_id) : ?>
                            <div class="section-image">
                                <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 'card-img-top']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card-body px-0 pt-0">
                            <?php if ($title) : ?>
                                <h3 class="card-title mb-0"><?php echo $title; ?></h3>
                                <div class="wp-block-button show-product-quote rubber-quote-button my-3">
                                    <a class="wp-block-button__link has-text-align-center wp-element-button"
                                       href="/request-a-quote/">REQUEST A QUOTE</a>
                                </div>
                            <?php endif; ?>
                            <?php if ($content) : ?>
                                <div class="card-text">
                                    <?php echo wpautop(esc_html($content)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        endwhile;
    endif;
    ?>
</div>
