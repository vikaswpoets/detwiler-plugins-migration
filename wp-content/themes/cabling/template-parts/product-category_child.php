<?php
$cat  = $args['category'];
$type = $args['type'];
$typeLabel = ProductsFilterHelper::getSkuTypeLabel( $type);
$typeAcfName = strtolower("sku_{$type}_image");
$featuredImageId = get_field( $typeAcfName, $cat );
?>
<?php if ( $cat ):
    $short_description = get_field( 'short_description', $cat );
	if ( ! empty( $featuredImageId ) ) {
        $thumbnail    = wp_get_attachment_image( $featuredImageId, 'medium' );
	} else {
		$thumbnail_id = get_field( 'taxonomy_image', $cat );
		$thumbnail_id = empty( $thumbnail_id ) ? 1032601 : $thumbnail_id;
		$thumbnail    = wp_get_attachment_image( $thumbnail_id, 'medium' );
	}
    $heading = "$cat->name - $typeLabel";
    ?>
    <div class="cat-item col-sm-6 col-md-4 <?php printf( 'product-%d', $cat->term_id ) ?>"
         data-category="<?php echo $cat->term_id ?>"
         data-type="<?php echo $type ?>"
    >
        <a href="javascript:void(0)"
           title="<?php echo $heading ?>">
			<?php echo $thumbnail; ?>
        </a>
        <h5>
            <a href="javascript:void(0)"
               title="<?php echo $heading ?>">
				<?php echo $heading ?>
            </a>
        </h5>
        <div class="cat-desc mb-2">
			<?php echo str_replace( "''", "'", $short_description ) ?? '' ?>
        </div>
        <a href="javascript:void(0)" class="block-button btn-red">
            <span><?php _e( 'FIND OUT MORE', 'cabling' ) ?></span>
        </a>
    </div>
<?php endif ?>
