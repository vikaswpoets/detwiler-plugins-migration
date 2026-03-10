<?php
$surface_equipment_id = get_surface_equipment_id();
if (empty($surface_equipment_id)){
    return;
}
$product_line = $_REQUEST['surface_line'] ?? '';
$product_cats = ProductsFilterHelper::getProductLineCategory('product_line', 'group_category', [$surface_equipment_id]);
?>
<div class="accordion-item filter-checkbox surface-equipment-box">
    <h2 class="accordion-header" id="panelsStayOpen-headingSurfaceEquipment">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#panelsStayOpen-collapseSurfaceEquipment" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseSurfaceEquipment">
			<?php _e( 'Production Equipment', 'cabling' ) ?>
        </button>
    </h2>
    <div id="panelsStayOpen-collapseSurfaceEquipment" class="accordion-collapse collapse show"
         aria-labelledby="panelsStayOpen-headingSurfaceEquipment">
        <div class="accordion-body">
			<?php foreach ( $product_cats as $category ): ?>
                <div class="form-check " data-meta-key-none="product_line"
                     data-value="<?php echo $category->term_id; ?>">
                    <input class="form-check-input" type="checkbox" data-name="product_line" name="product_line[]"
                           value="<?php echo $category->term_id; ?>"
                           title="<?php echo $category->name; ?>"
                           <?php checked($product_line, $category->slug )?>
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
