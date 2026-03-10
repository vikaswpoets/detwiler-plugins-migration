<?php
$meta_keys = [
	'inches_id_backup-ring',
	'inches_t_backup-ring',
	'inches_width_backup-ring',
	'inches_id',
	'inches_width',
	'inches_od',
	'milimeters_id',
	'milimeters_od',
	'milimeters_width'
];

$all_values = get_filter_sizes_values( $meta_keys );

$milimeters_id_choices    = $all_values['milimeters_id'] ?? [];
$milimeters_od_choices    = $all_values['milimeters_od'] ?? [];
$milimeters_width_choices = $all_values['milimeters_width'] ?? [];

$inches_id_backup_ring_choices    = $all_values['inches_id_backup-ring'] ?? [];
$inches_t_backup_ring_choices     = $all_values['inches_t_backup-ring'] ?? [];
$inches_width_backup_ring_choices = $all_values['inches_width_backup-ring'] ?? [];

$inches_id_choices    = $all_values['inches_id'] ?? [];
$inches_width_choices = $all_values['inches_width'] ?? [];
$inches_od_choices    = $all_values['inches_od'] ?? [];

?>
<div class="accordion-item filter-checkbox filter-attribute filter-size">
    <h2 class="accordion-header" id="panelsStayOpen-heading-sizeFT">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#panelsStayOpen-collapse-sizeFT"
                aria-expanded="true"
                aria-controls="panelsStayOpen-collapse-sizeFT">
			<?php echo __( 'Size', 'cabling' ) ?>
        </button>
    </h2>
    <div id="panelsStayOpen-collapse-sizeFT"
         class="accordion-collapse collapse show filter-size"
         aria-labelledby="panelsStayOpen-heading-sizeFT">
        <div class="accordion-body disabled-default size-oring">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <div class="accordion-item filter-inch">
                    <h2 class="accordion-header" id="flush-headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseOne" aria-expanded="false"
                                aria-controls="flush-collapseOne">
							<?php echo __( 'Inches', 'cabling' ) ?>
                        </button>
                    </h2>
                    <div id="flush-collapseOne" class="accordion-collapse collapse filter-size-inner"
                         aria-labelledby="flush-headingOne"
                         data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="accordion-body">
                                <div class="accordion-item filter-checkbox filter-attribute">
                                    <h2 class="accordion-header" id="panelsStayOpen-heading-InchesID">
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
                                                    <input class="form-check-input" type="checkbox"
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
                                <div class="accordion-item filter-checkbox filter-attribute">
                                    <h2 class="accordion-header" id="panelsStayOpen-heading-inches_od">
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
                                                    <input class="form-check-input" type="checkbox"
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
                                <div class="accordion-item filter-checkbox filter-attribute">
                                    <h2 class="accordion-header" id="panelsStayOpen-heading-InchesWidth">
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
                                                    <input class="form-check-input" type="checkbox"
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
                <div class="accordion-item filter-millimeter">
                    <h2 class="accordion-header" id="flush-headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseTwo" aria-expanded="false"
                                aria-controls="flush-collapseTwo">
							<?php echo __( 'Millimeters', 'cabling' ) ?>
                        </button>
                    </h2>
                    <div id="flush-collapseTwo" class="accordion-collapse collapse filter-size-inner"
                         aria-labelledby="flush-headingTwo"
                         data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="accordion-body">
                                <div class="accordion-item filter-checkbox filter-attribute">
                                    <h2 class="accordion-header"
                                        id="panelsStayOpen-heading-MillimetersID">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#panelsStayOpen-collapse-MillimetersID"
                                                aria-expanded="true"
                                                aria-controls="panelsStayOpen-collapse-MillimetersID">
											<?php echo __( 'ID', 'cabling' ) ?>
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapse-MillimetersID"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="panelsStayOpen-heading-MillimetersID">
                                        <div class="accordion-body <?php echo count( $milimeters_id_choices ) > 4 ? 'filter-scroll' : '' ?>">
											<?php foreach ( $milimeters_id_choices as $milimeters_id ): if ( empty( $milimeters_id ) ) {
												continue;
											} ?>
                                                <div class="form-check filter-category"
                                                     data-meta-key="milimeters_id"
                                                     data-value="<?php echo $milimeters_id; ?>">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="attributes[milimeters_id][]"
                                                           value="<?php echo $milimeters_id; ?>"
                                                           title="<?php echo $milimeters_id; ?>"
                                                           id="category-<?php echo sanitize_title( 'milimeters_id' . $milimeters_id ); ?>">
                                                    <label class="form-check-label"
                                                           for="category-<?php echo sanitize_title( 'milimeters_id' . $milimeters_id ); ?>">
														<?php echo $milimeters_id; ?>
                                                        <i class="fa-regular fa-check"></i>
                                                    </label>
                                                </div>
											<?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item filter-checkbox filter-attribute">
                                    <h2 class="accordion-header"
                                        id="panelsStayOpen-heading-MillimetersOD">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#panelsStayOpen-collapse-MillimetersOD"
                                                aria-expanded="false"
                                                aria-controls="panelsStayOpen-collapse-MillimetersOD">
											<?php echo __( 'OD', 'cabling' ) ?>
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapse-MillimetersOD"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="panelsStayOpen-heading-MillimetersOD">
                                        <div class="accordion-body <?php echo count( $milimeters_od_choices ) > 4 ? 'filter-scroll' : '' ?>">
											<?php foreach ( $milimeters_od_choices as $milimeters_od ): if ( empty( $milimeters_od ) ) {
												continue;
											} ?>
                                                <div class="form-check filter-category"
                                                     data-meta-key="milimeters_od"
                                                     data-value="<?php echo $milimeters_od; ?>">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="attributes[milimeters_od][]"
                                                           value="<?php echo $milimeters_od; ?>"
                                                           title="<?php echo $milimeters_od; ?>"
                                                           id="category-<?php echo sanitize_title( 'milimeters_od' . $milimeters_od ); ?>">
                                                    <label class="form-check-label"
                                                           for="category-<?php echo sanitize_title( 'milimeters_od' . $milimeters_od ); ?>">
														<?php echo $milimeters_od; ?>
                                                        <i class="fa-regular fa-check"></i>
                                                    </label>
                                                </div>
											<?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item filter-checkbox filter-attribute">
                                    <h2 class="accordion-header"
                                        id="panelsStayOpen-heading-MillimetersWidth">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#panelsStayOpen-collapse-MillimetersWidth"
                                                aria-expanded="true"
                                                aria-controls="panelsStayOpen-collapse-MillimetersWidth">
											<?php echo __( 'Width', 'cabling' ) ?>
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapse-MillimetersWidth"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="panelsStayOpen-heading-MillimetersWidth">
                                        <div class="accordion-body <?php echo count( $milimeters_width_choices ) > 4 ? 'filter-scroll' : '' ?>">
											<?php foreach ( $milimeters_width_choices as $milimeters_width ): if ( empty( $milimeters_width ) ) {
												continue;
											} ?>
                                                <div class="form-check filter-category"
                                                     data-meta-key="milimeters_width"
                                                     data-value="<?php echo $milimeters_width; ?>">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="attributes[milimeters_width][]"
                                                           value="<?php echo $milimeters_width; ?>"
                                                           title="<?php echo $milimeters_width; ?>"
                                                           id="category-<?php echo sanitize_title( 'milimeters_width' . $milimeters_width ); ?>">
                                                    <label class="form-check-label"
                                                           for="category-<?php echo sanitize_title( 'milimeters_width' . $milimeters_width ); ?>">
														<?php echo $milimeters_width; ?>
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
            <!--<div class="accordion accordion-flush">
                            <div class="accordion-item filter-checkbox filter-custom-size">
                                <h2 class="accordion-header"
                                    id="panelsStayOpen-heading-customSize">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#panelsStayOpen-collapse-customSize"
                                            aria-expanded="false"
                                            aria-controls="panelsStayOpen-collapse-customSize">
                                        <?php /*echo __('Custom', 'cabling') */ ?>
                                    </button>
                                </h2>
                                <div id="panelsStayOpen-collapse-customSize"
                                     class="accordion-collapse collapse"
                                     aria-labelledby="panelsStayOpen-heading-customSize">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group">
                                                    <input type="text" id="custom-size-width" class="custom-size" placeholder="Width:"
                                                           name="attributes[nominal_size_width]">
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group">
                                                    <input type="text" id="custom-size-id" class="custom-size" placeholder="ID:"
                                                           name="attributes[nominal_size_id]">
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group">
                                                    <input type="text" id="custom-size-od" class="custom-size" placeholder="OD:"
                                                           name="attributes[nominal_size_od]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>-->
            <!-- JM 20240313 Changed display -->
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

        <div class="accordion-body disabled-default size-backup-ring">
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
</div>
