<?php
/**
 * Child Pages Columns Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'child-pages-columns-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'child-pages-columns webshop-block mb-5';
if ( ! empty( $block['className'] ) ) {
	$className .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$heading        = get_field( 'heading' );
$description    = get_field( 'description' );
$columns        = get_field( 'columns' ) ?: '3';
$child_pages   = get_field( 'children_pages' ) ?? [];
$label_position = get_field( 'label_position' );

$label_class = 'child-page-title ';
$label_class .= $label_position === 'top' ? 'top-0' : 'bottom-0';

// Calculate Bootstrap column class based on selected columns
$column_class = 'col-lg-' . ( 12 / intval( $columns ) );
?>

<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>">
    <div class="container">
        <?php if ( $heading || $description ) : ?>
        <div class="heading text-center mb-5">
            <div class="row">
                <div class="col-12">
					<?php if ( $heading ) : ?>
                        <h2 class="child-pages-heading pre-heading heading-center"><?php echo esc_html( $heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $description ) : ?>
                        <div class="child-pages-description">
							<?php echo wp_kses_post( $description ); ?>
                        </div>
					<?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

		<?php if ( $child_pages && is_array( $child_pages ) && count( $child_pages ) > 0 ) : ?>
            <div class="row gx-5 gy-4 child-pages-row">
				<?php foreach ( $child_pages as $child ): ?>

				    <?php $heading = $child['child_heading'] ?: get_the_title($child['child_page']) ?>
                    <div class="<?php echo esc_attr( $column_class ); ?> col-md-3 col-sm-6 col-12 child-page-col">
                        <div class="child-page-item">
                            <div class="child-page-image">
		                        <?php echo get_page_thumbnail( $child['child_page'], 'full'); ?>
                                <h3 class="<?php echo $label_class; ?>">
			                        <?php echo $heading; ?>
                                </h3>
                            </div>

                            <div class="child-page-link d-flex flex-column justify-content-between">
                                <h5 class="title text-uppercase">
									<?php echo $child['child_heading_hover'] ?: $heading; ?>
                                </h5>
                                <div class="child-page-excerpt">
									<?php echo $child['child_description'] ?>
                                </div>
                                <div class="read-more text-end">
                                    <a href="<?php echo get_the_permalink($child['child_page']); ?>" class="block-button ">
                                        <span><?php _e( 'FIND OUT MORE', 'cabling' ); ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
    </div>
</div>
