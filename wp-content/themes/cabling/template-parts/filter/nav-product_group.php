<?php
$product_cats = ProductsFilterHelper::getProductLineCategory('product_group', 'family_category', ['8626']);
$product_group = $args['product_group'] ?? 0;
?>
<div class="accordion-item filter-checkbox">
    <h2 class="accordion-header" id="panelsStayOpen-headingCat">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#panelsStayOpen-collapseCat" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseCat">
			<?php _e( 'Product', 'cabling' ) ?>
        </button>
    </h2>
    <div id="panelsStayOpen-collapseCat" class="accordion-collapse collapse show"
         aria-labelledby="panelsStayOpen-headingCat">
        <div class="accordion-body">
			<?php foreach ( $product_cats as $category ): ?>
                <div class="form-check filter-category" data-meta-key-none="group-type"
                     data-value="<?php echo $category->term_id; ?>">
                    <input class="form-check-input" type="radio" data-name="group-type" name="group-type"
                           value="<?php echo $category->term_id; ?>"
                           title="<?php echo $category->name; ?>"
						<?php echo ( $category->term_id == $product_group ) ? 'checked=checked' : ''; ?>
                           id="category-<?php echo $category->slug; ?>">
                    <label class="form-check-label" for="category-<?php echo $category->slug; ?>">
						<?php echo $category->name; ?>
                        <i class="fa-regular fa-check"></i>
                    </label>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
</div>
