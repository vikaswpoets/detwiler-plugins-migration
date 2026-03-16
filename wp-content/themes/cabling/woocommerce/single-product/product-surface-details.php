<div class="product-surface-details clear">
    <div class="container my-5">
		<?php
		if ( ! empty( $mainProducts ) && is_array( $mainProducts ) ): ?>
            <div class="wp-block-group popular-information">
                <div class="wp-block-group__inner-container is-layout-constrained wp-block-group-is-layout-constrained">
                    <div class="contact-link">
                        <p>
                            <?php echo __('Not seeing what you need? Then drop us a <a class="text-black fw-bolder" href="/contact-form/">line</a>. We can provide almost any solution. Below, you’ll find some additional solutions as well.', 'cabling') ?>
                        </p>
                    </div>

                    <div class="wp-block-group">
						<?php foreach ( $mainProducts as $products ): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered product-variation-table">
                                    <thead>
                                    <tr>
										<?php foreach ( $attributes as $attribute ): ?>
                                            <th class="has-text-align-center"
                                                data-align="center"><?php echo $attribute ?></th>
										<?php endforeach ?>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php render_doublee_sku_table( $attributes, $products ); ?>
                                    </tbody>
                                </table>
                            </div>
						<?php endforeach; ?>
                    </div>
                </div>
            </div>
		<?php endif ?>
		<?php if ( ! empty( $componentProducts ) && is_array( $componentProducts ) ): ?>
            <div id="componentsdiv" class="wp-block-group py-5 px-4 has-background"
                 style="background-color:#fafafa">
                <div class="wp-block-group__inner-container is-layout-constrained wp-block-group-is-layout-constrained">
                    <h2 class="wp-block-heading pre-heading heading-center"><?php __( 'Replacement Parts', 'cabling' ) ?></h2>
                    <h2 class="wp-block-heading has-text-align-center page-heading"><?php echo $replacementPartHeading ?? '' ?></h2>
					<?php if ( ! empty( $replacementPartImage ) ): ?>
                        <div class="wp-block-image text-center">
							<?php echo wp_get_attachment_image( $replacementPartImage, 'full' ) ?>
                        </div>
					<?php endif ?>
                    <div class="wp-block-group my-3">
                        <?php foreach ( $componentProducts as $type => $components ): ?>
                            <div class="wp-block-group__inner-container is-layout-constrained wp-block-group-is-layout-constrained">
                                <details class="wp-block-mamaduka-toggles wp-block-toggles toggle-table" open="">
                                       <summary><?php echo ProductsFilterHelper::getSkuTypeLabel($type); ?></summary>
                                    <div class="wp-block-toggles__content">
                                        <div class="table-responsive">
                                            <table class="table table-bordered product-variation-table">
                                                <thead>
                                                <tr>
								                    <?php foreach ( $attributes as $key => $attribute ): ?>
                                                        <th class="has-text-align-center" data-align="center"
                                                            data-filter="<?php echo $key ?>"><?php echo $attribute ?></th>
								                    <?php endforeach ?>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php render_doublee_sku_table( $attributes, $components ); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </details>
                            </div>
	                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
	    <?php endif ?>
    </div>
</div>
