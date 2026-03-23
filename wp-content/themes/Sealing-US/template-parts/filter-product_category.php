<?php
$is_backup_ring = isset($args) && $args['is_backup_ring'];
$is_surface_equipment = isset($args) && $args['is_surface_equipment'];
$metas = get_query_var('custom_filter');
$terms = get_queried_object();
$product_ids = get_product_ids_by_category($terms->taxonomy, [$terms->term_id], $metas);

if (empty($is_surface_equipment)) {
	if ( $is_backup_ring ) {
		$inches_id_backup_ring_choices    = get_all_meta_values_cached( 'inches_id_backup-ring', $product_ids );
		$inches_t_backup_ring_choices     = get_all_meta_values_cached( 'inches_t_backup-ring', $product_ids );
		$inches_width_backup_ring_choices = get_all_meta_values_cached( 'inches_width_backup-ring', $product_ids );
	} else {
		$inches_id_choices        = get_all_meta_values_cached( 'inches_id', $product_ids );
		$inches_width_choices     = get_all_meta_values_cached( 'inches_width', $product_ids );
		$milimeters_id_choices    = get_all_meta_values_cached( 'milimeters_id', $product_ids );
		$milimeters_width_choices = get_all_meta_values_cached( 'milimeters_width', $product_ids );
		$inches_od_choices        = get_all_meta_values_cached( 'inches_od', $product_ids );
		$milimeters_od_choices    = get_all_meta_values_cached( 'milimeters_od', $product_ids );
	}
}

?>
<div class="product-variable-filter woo-sidebar <?php echo empty($is_surface_equipment) ? '' : 'filter-surface_equipment' ?>">
    <h2><?php echo __('Filter by', 'cabling') ?></h2>
    <div class="filter-blog">
        <form method="POST" id="form-filter-type" action="<?php echo esc_url(get_term_link($terms)); ?>">
            <?php if (!empty($metas)): ?>
                <?php foreach ($metas as $keyMeta => $meta): if (empty($meta)) {
                    continue;
                } ?>
                    <?php if ($keyMeta == 'product_dash_number' || $keyMeta == 'product_dash_number_backup_rings'): ?>
                    <input type="hidden" class="pre_filter" data-action="<?php echo $keyMeta; ?>"
                           value="<?php echo implode(',', $meta); ?>">
                    <?php else: ?>
                    <input type="hidden" class="pre_filter" data-action="<?php echo $keyMeta; ?>"
                           value="<?php echo $meta[0]; ?>">
                    <?php endif ?>
                <?php endforeach; ?>
            <?php endif ?>
            <input type="hidden" name="paged" value="<?php echo $_REQUEST['paged'] ?? '1'; ?>">
	        <input type="hidden" name="filer_taxonomy_type" value="<?php echo $terms->taxonomy; ?>">
	        <input type="hidden" name="filer_taxonomy_id" value="<?php echo $terms->term_id; ?>">
            <div class="accordion" id="accordionFilterBlog">

                <div class="accordion-item filter-checkbox filter-attribute filter-sellable">
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

	            <?php if (empty($is_surface_equipment)): ?>
                    <div class="accordion-item filter-checkbox filter-attribute filter-size">
                        <h2 class="accordion-header" id="panelsStayOpen-heading-sizeFT">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#panelsStayOpen-collapse-sizeFT"
                                    aria-expanded="true"
                                    aria-controls="panelsStayOpen-collapse-sizeFT">
					            <?php echo __( 'Size', 'cabling' ) ?>
                            </button>
                        </h2>
			            <?php if ( $is_backup_ring ): ?>
                            <div id="panelsStayOpen-collapse-sizeFT"
                                 class="accordion-collapse collapse show filter-size"
                                 aria-labelledby="panelsStayOpen-heading-sizeFT">
                                <div class="accordion-body">
                                    <div class="accordion-body">
                                        <div class="accordion-item filter-checkbox filter-attribute">
                                            <h2 class="accordion-header" id="panelsStayOpen-heading-InchesID_BK">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#panelsStayOpen-collapse-InchesID_BK"
                                                        aria-expanded="false"
                                                        aria-controls="panelsStayOpen-collapse-InchesID_BK">
										            <?php echo __( 'ID', 'cabling' ) ?>
                                                </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapse-InchesID_BK"
                                                 class="accordion-collapse collapse"
                                                 aria-labelledby="panelsStayOpen-heading-InchesID_BK">
                                                <div class="accordion-body <?php echo count( $inches_id_backup_ring_choices ) > 4 ? 'filter-scroll' : '' ?>">
										            <?php foreach ( $inches_id_backup_ring_choices as $inches_id_bkr ): if ( empty( $inches_id_bkr ) ) {
											            continue;
										            } ?>
                                                        <div class="form-check filter-category"
                                                             data-meta-key="inches_id"
                                                             data-value="<?php echo $inches_id_bkr; ?>">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="attributes[inches_id_backup-ring][]"
                                                                   value="<?php echo $inches_id_bkr; ?>"
                                                                   title="<?php echo $inches_id_bkr; ?>"
                                                                   id="category-<?php echo sanitize_title( 'inches_id_backup-ring' . $inches_id_bkr ); ?>">
                                                            <label class="form-check-label"
                                                                   for="category-<?php echo sanitize_title( 'inches_id_backup-ring' . $inches_id_bkr ); ?>">
													            <?php echo $inches_id_bkr; ?>
                                                                <i class="fa-regular fa-check"></i>
                                                            </label>
                                                        </div>
										            <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item filter-checkbox filter-attribute">
                                            <h2 class="accordion-header" id="panelsStayOpen-heading-inches_t_bk">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#panelsStayOpen-collapse-inches_t_bk"
                                                        aria-expanded="false"
                                                        aria-controls="panelsStayOpen-collapse-inches_t_bk">
										            <?php echo __( 'T', 'cabling' ) ?>
                                                </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapse-inches_t_bk"
                                                 class="accordion-collapse collapse"
                                                 aria-labelledby="panelsStayOpen-heading-inches_t_bk">
                                                <div class="accordion-body <?php echo count( $inches_t_backup_ring_choices ) > 4 ? 'filter-scroll' : '' ?>">
										            <?php foreach ( $inches_t_backup_ring_choices as $inches_t ): if ( empty( $inches_t ) ) {
											            continue;
										            } ?>
                                                        <div class="form-check filter-category"
                                                             data-meta-key="inches_t_backup-ring"
                                                             data-value="<?php echo $inches_t; ?>">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="attributes[inches_t_backup-ring][]"
                                                                   value="<?php echo $inches_t; ?>"
                                                                   title="<?php echo $inches_t; ?>"
                                                                   id="category-<?php echo sanitize_title( 'inches_t_backup-ring' . $inches_t ); ?>">
                                                            <label class="form-check-label"
                                                                   for="category-<?php echo sanitize_title( 'inches_t_backup-ring' . $inches_t ); ?>">
													            <?php echo $inches_t; ?>
                                                                <i class="fa-regular fa-check"></i>
                                                            </label>
                                                        </div>
										            <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item filter-checkbox filter-attribute">
                                            <h2 class="accordion-header" id="panelsStayOpen-heading-InchesWidth_BK">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#panelsStayOpen-collapse-InchesWidth_BK"
                                                        aria-expanded="false"
                                                        aria-controls="panelsStayOpen-collapse-InchesWidth_BK">
										            <?php echo __( 'Width', 'cabling' ) ?>
                                                </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapse-InchesWidth_BK"
                                                 class="accordion-collapse collapse"
                                                 aria-labelledby="panelsStayOpen-heading-InchesWidth_BK">
                                                <div class="accordion-body <?php echo count( $inches_width_backup_ring_choices ) > 4 ? 'filter-scroll' : '' ?>">
										            <?php foreach ( $inches_width_backup_ring_choices as $inches_width_bk ): if ( empty( $inches_width_bk ) ) {
											            continue;
										            } ?>
                                                        <div class="form-check filter-category"
                                                             data-meta-key="inches_width"
                                                             data-value="<?php echo $inches_width_bk; ?>">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="attributes[inches_width][]"
                                                                   value="<?php echo $inches_width_bk; ?>"
                                                                   title="<?php echo $inches_width_bk; ?>"
                                                                   id="category-<?php echo sanitize_title( 'inches_width' . $inches_width_bk ); ?>">
                                                            <label class="form-check-label"
                                                                   for="category-<?php echo sanitize_title( 'inches_width' . $inches_width_bk ); ?>">
													            <?php echo $inches_width_bk; ?>
                                                                <i class="fa-regular fa-check"></i>
                                                            </label>
                                                        </div>
										            <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
			            <?php else: ?>
                            <div id="panelsStayOpen-collapse-sizeFT"
                                 class="accordion-collapse collapse show filter-size"
                                 aria-labelledby="panelsStayOpen-heading-sizeFT">
                                <div class="accordion-body">
                                    <div class="accordion accordion-flush" id="accordionFlushExample">
                                        <div class="accordion-item filter-inch">
                                            <h2 class="accordion-header" id="flush-headingOne">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#flush-collapseOne" aria-expanded="false"
                                                        aria-controls="flush-collapseOne">
										            <?php echo __( 'Inches', 'cabling' ) ?>
                                                </button>
                                            </h2>
                                            <div id="flush-collapseOne"
                                                 class="accordion-collapse collapse filter-size-inner"
                                                 aria-labelledby="flush-headingOne"
                                                 data-bs-parent="#accordionFlushExample">
                                                <div class="accordion-body">
                                                    <div class="accordion-body">
                                                        <div class="accordion-item filter-checkbox filter-inches_id filter-attribute">
                                                            <h2 class="accordion-header"
                                                                id="panelsStayOpen-heading-InchesID">
                                                                <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#panelsStayOpen-collapse-InchesID"
                                                                        aria-expanded="false"
                                                                        aria-controls="panelsStayOpen-collapse-InchesID">
														            <?php echo __( 'ID', 'cabling' ) ?>
                                                                </button>
                                                            </h2>
                                                            <div id="panelsStayOpen-collapse-InchesID"
                                                                 class="accordion-collapse collapse"
                                                                 aria-labelledby="panelsStayOpen-heading-InchesID">
                                                                <div class="accordion-body <?php echo count( $inches_id_choices ) > 4 ? 'filter-scroll' : '' ?>">
														            <?php foreach ( $inches_id_choices as $inches_id ): if ( empty( $inches_id ) ) {
															            continue;
														            } ?>
                                                                        <div class="form-check filter-category"
                                                                             data-meta-key="inches_id"
                                                                             data-value="<?php echo $inches_id; ?>">
                                                                            <input class="form-check-input"
                                                                                   type="checkbox"
                                                                                   name="attributes[inches_id][]"
                                                                                   value="<?php echo $inches_id; ?>"
                                                                                   title="<?php echo $inches_id; ?>"
                                                                                   id="category-<?php echo sanitize_title( 'inches_id' . $inches_id ); ?>">
                                                                            <label class="form-check-label"
                                                                                   for="category-<?php echo sanitize_title( 'inches_id' . $inches_id ); ?>">
																	            <?php echo $inches_id; ?>
                                                                                <i class="fa-regular fa-check"></i>
                                                                            </label>
                                                                        </div>
														            <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="accordion-item filter-checkbox filter-inches_od filter-attribute">
                                                            <h2 class="accordion-header"
                                                                id="panelsStayOpen-heading-inches_od">
                                                                <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#panelsStayOpen-collapse-inches_od"
                                                                        aria-expanded="false"
                                                                        aria-controls="panelsStayOpen-collapse-inches_od">
														            <?php echo __( 'OD', 'cabling' ) ?>
                                                                </button>
                                                            </h2>
                                                            <div id="panelsStayOpen-collapse-inches_od"
                                                                 class="accordion-collapse collapse"
                                                                 aria-labelledby="panelsStayOpen-heading-inches_od">
                                                                <div class="accordion-body <?php echo count( $inches_od_choices ) > 4 ? 'filter-scroll' : '' ?>">
														            <?php foreach ( $inches_od_choices as $inches_od ): if ( empty( $inches_od ) ) {
															            continue;
														            } ?>
                                                                        <div class="form-check filter-category"
                                                                             data-meta-key="inches_od"
                                                                             data-value="<?php echo $inches_od; ?>">
                                                                            <input class="form-check-input"
                                                                                   type="checkbox"
                                                                                   name="attributes[inches_od][]"
                                                                                   value="<?php echo $inches_od; ?>"
                                                                                   title="<?php echo $inches_od; ?>"
                                                                                   id="category-<?php echo sanitize_title( 'inches_od' . $inches_od ); ?>">
                                                                            <label class="form-check-label"
                                                                                   for="category-<?php echo sanitize_title( 'inches_od' . $inches_od ); ?>">
																	            <?php echo $inches_od; ?>
                                                                                <i class="fa-regular fa-check"></i>
                                                                            </label>
                                                                        </div>
														            <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="accordion-item filter-checkbox filter-inches_width filter-attribute">
                                                            <h2 class="accordion-header"
                                                                id="panelsStayOpen-heading-InchesWidth">
                                                                <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#panelsStayOpen-collapse-InchesWidth"
                                                                        aria-expanded="false"
                                                                        aria-controls="panelsStayOpen-collapse-InchesWidth">
														            <?php echo __( 'Width', 'cabling' ) ?>
                                                                </button>
                                                            </h2>
                                                            <div id="panelsStayOpen-collapse-InchesWidth"
                                                                 class="accordion-collapse collapse"
                                                                 aria-labelledby="panelsStayOpen-heading-InchesWidth">
                                                                <div class="accordion-body <?php echo count( $inches_width_choices ) > 4 ? 'filter-scroll' : '' ?>">
														            <?php foreach ( $inches_width_choices as $inches_width ): if ( empty( $inches_width ) ) {
															            continue;
														            } ?>
                                                                        <div class="form-check filter-category"
                                                                             data-meta-key="inches_width"
                                                                             data-value="<?php echo $inches_width; ?>">
                                                                            <input class="form-check-input"
                                                                                   type="checkbox"
                                                                                   name="attributes[inches_width][]"
                                                                                   value="<?php echo $inches_width; ?>"
                                                                                   title="<?php echo $inches_width; ?>"
                                                                                   id="category-<?php echo sanitize_title( 'inches_width' . $inches_width ); ?>">
                                                                            <label class="form-check-label"
                                                                                   for="category-<?php echo sanitize_title( 'inches_width' . $inches_width ); ?>">
																	            <?php echo $inches_width; ?>
                                                                                <i class="fa-regular fa-check"></i>
                                                                            </label>
                                                                        </div>
														            <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion accordion-flush">
                                        <div class="accordion-item filter-checkbox filter-custom-size">
                                            <h2 class="accordion-header"
                                                id="panelsStayOpen-heading-customSize">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#panelsStayOpen-collapse-customSize"
                                                        aria-expanded="false"
                                                        aria-controls="panelsStayOpen-collapse-customSize">
										            <?php echo __( 'Custom', 'cabling' ) ?>
                                                </button>
                                            </h2>
                                            <div id="panelsStayOpen-collapse-customSize"
                                                 class="accordion-collapse collapse"
                                                 aria-labelledby="panelsStayOpen-heading-customSize">
                                                <div class="accordion-body">

                                                    <div class="custom-size-quote">
                                                        <div>Need a custom size? Just send us a quote request.</div>
											            <?php cabling_add_quote_button() ?>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
			            <?php endif ?>
                    </div>
	            <?php endif ?>
                <!--Product dynamic field options-->
	            <?php get_template_part( 'template-parts/filter/nav', 'attributes', array(
		            'is_surface_equipment' => $is_surface_equipment,
	            ) ) ?>
                <?php if (empty($is_surface_equipment)): ?>
                <!-- JM 20240313 Changed display -->
                <div class="accordion accordion-flush">
                    <div class="accordion-item filter-checkbox filter-custom-hardness">
                        <h2 class="accordion-header"
                            id="panelsStayOpen-heading-customHardness">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#panelsStayOpen-collapse-customHardness"
                                    aria-expanded="false"
                                    aria-controls="panelsStayOpen-collapse-customHardness">
					            <?php echo __( 'Custom', 'cabling' ) ?>
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
                <?php endif ?>
            </div>
            <?php wp_nonce_field('product-category-filter') ?>
        </form>
    </div>
</div>
