<?php
$oring_attributes = get_filter_lists();
$surface_equipment_attributes = get_filter_lists('group_67dd20c1a7fd0');
$attributes = array_merge($oring_attributes, $surface_equipment_attributes);

if ( ! empty( $attributes ) ): ?>
	<?php foreach ( $attributes as $slug => $attribute ): ?>
		<?php
		if ( $slug === 'product_complance' || $slug === 'product_type' || $slug === 'sellable' ) {
			continue;
		}
        $is_surface_equipment = str_contains($slug, 'surface_');
        $is_oring = !$is_surface_equipment;
		?>
		<?php if ( $attribute['field_type'] === 'message' ): ?>
            <h3 class="filter-heading filter-attribute-heading <?php echo 'heading-'.sanitize_key($attribute['label'])?>"><?php echo $attribute['label'] ?></h3>
		<?php else: ?>
            <div class="accordion-item filter-checkbox filter-attribute
                            <?php echo 'filter-' . $slug ?>
                            <?php //echo ( $slug === 'product_dash_number' || $slug === 'product_dash_number_backup_rings' ) ? ' disabled-default ' : '' ?>
                            <?php echo ( $slug === 'product_dash_number' ) ? ' size-oring ' : '' ?>
                            <?php echo ( $slug === 'product_dash_number_backup_rings' ) ? ' size-backup-ring ' : '' ?>
                            <?php echo ( $is_surface_equipment ) ? ' type-surface ' : '' ?>
                            <?php echo ( $is_oring ) ? ' type-oring ' : '' ?>"
                 data-meta-key="<?php echo $slug ?>"
            >
                <h2 class="accordion-header" id="panelsStayOpen-heading<?php echo $slug ?>">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#panelsStayOpen-collapse<?php echo $slug ?>"
                            aria-expanded="true"
                            aria-controls="panelsStayOpen-collapse<?php echo $slug ?>">
						<?php echo $attribute['label'] ?? '---' ?>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapse<?php echo $slug ?>"
                     class="accordion-collapse collapse show"
                     aria-labelledby="panelsStayOpen-heading<?php echo $slug ?>">
                    <div class="accordion-body <?php echo count( $attribute['choices'] ) > 4 ? 'filter-scroll' : '' ?>">
						<?php foreach ( $attribute['choices'] as $key => $value ): if ( empty( $value ) ) {
							continue;
						} ?>
                            <div class="form-check filter-category" data-meta-key="<?php echo $slug ?>"
                                 data-value="<?php echo $attribute['valueType'] === 'key' ? $key : $value; ?>">
                                <input class="form-check-input"
                                       type="checkbox"
									<?php echo show_product_filter_input_name( $slug, $attribute ) ?>
                                       value="<?php echo $attribute['valueType'] === 'key' ? $key : $value; ?>"
                                       title="<?php echo $value; ?>"
                                       id="category-<?php echo sanitize_title( $slug . $value ); ?>">
                                <label class="form-check-label"
                                       for="category-<?php echo sanitize_title( $slug . $value ); ?>">
									<?php echo show_product_filter_input_value( $slug, $value ); ?>
                                    <i class="fa-regular fa-check"></i>
                                </label>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>
            </div>
		<?php endif ?>
	<?php endforeach; ?>
<?php endif ?>
