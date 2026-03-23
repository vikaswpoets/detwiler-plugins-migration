<?php
$product_group = $args['product_group']->term_id ?? 0;
$history = $args['history'] ?? [];
?>
<div class="filter-blog">
    <form method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <?php wp_nonce_field('product-category-filter') ?>
        <?php if (!empty($_REQUEST['material'])): ?>
            <input type="hidden" class="pre_filter" data-action="product_material"
                   value="<?php echo $_REQUEST['material']; ?>">
        <?php endif ?>
        <?php if (!empty($history['attributes'])): ?>
            <?php foreach ($history['attributes'] as $pre_key => $pre_value): ?>
                <?php if ($pre_key == 'product_dash_number' || $pre_key == 'product_dash_number_backup_rings'): ?>
                    <input type="hidden" class="pre_filter" data-action="<?php echo $pre_key; ?>"
                           value="<?php echo implode(',', $pre_value); ?>">
                    <?php else: ?>
                    <input type="hidden" class="pre_filter" data-action="<?php echo $pre_key; ?>"
                           value="<?php echo $pre_value[0]; ?>">
                    <?php endif ?>
            <?php endforeach; ?>
        <?php endif ?>
        <div class="accordion" id="accordionFilterBlog">

            <div class="accordion-item filter-checkbox filter-sellable">
                <div id="panelsStayOpen-collapsesellable" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingsellable" style="">
                    <div class="form-check filter-category filter-sellable" data-meta-key="sellable" data-value="1">
                        <input class="form-check-input" type="checkbox" name="attributes[sellable][]" value="1" title="Buy Online" id="category-product_sellable_yes">
                        <label class="form-check-label" for="category-product_sellable_yes">
                        Buy Online
                        </label>
                    </div>
                    <div class="form-check filter-category filter-sellable" data-meta-key="sellable" data-value="">
                        <input class="form-check-input" type="checkbox" name="attributes[sellable][]" value="" title="Buy via Datwyler sales" id="category-product_sellable_no">
                        <label class="form-check-label" for="category-product_sellable_no" onclick="window.location.reload()">
                            Buy via Datwyler sales
                        </label>
                    </div>
                </div>
            </div>

            <!--Product group options-->
            <?php get_template_part('template-parts/filter/nav', 'product_group', [
                    'product_group' => $product_group,
            ]) ?>
            <!--Product Surface Equipment type-->
            <?php get_template_part('template-parts/filter/nav', 'surface-equipment') ?>
            <!--Product size options-->
            <?php get_template_part('template-parts/filter/nav', 'size') ?>
            <!--Product dynamic field options-->
            <?php get_template_part('template-parts/filter/nav', 'attributes') ?>
            <!-- JM 20240313 Changed display -->
            <div class="accordion accordion-flush filter-custom-quote">
                <div class="accordion-item filter-checkbox filter-custom-hardness">
                    <h2 class="accordion-header"
                        id="panelsStayOpen-heading-customHardness">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#panelsStayOpen-collapse-customHardness"
                                aria-expanded="false"
                                aria-controls="panelsStayOpen-collapse-customHardness">
                            <?php echo __('Custom', 'cabling') ?>
                        </button>
                    </h2>
                    <div id="panelsStayOpen-collapse-customHardness"
                         class="accordion-collapse collapse"
                         aria-labelledby="panelsStayOpen-heading-customHardness">
                        <div class="accordion-body">

                            <div class="custom-size-quote">
                                <div>Need a hardness / durometer measurement not listed here? Just send us a quote
                                    request.
                                </div>
                                <?php cabling_add_quote_button() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
