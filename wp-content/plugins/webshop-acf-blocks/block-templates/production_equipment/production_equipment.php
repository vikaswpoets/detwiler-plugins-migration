<?php
/**
 * Product Equipment Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 */

// Create id attribute allowing for custom "anchor" value
$id = 'production_equipment-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values
$className = 'production_equipment-block';
if ( ! empty( $block['className'] ) ) {
	$className .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$className .= ' align' . $block['align'];
}

// Load values and assign defaults
$section_heading     = get_field( 'section_heading' ) ?: '';
$section_description = get_field( 'section_description' ) ?: '';


$repeater_products = get_field( 'product_items' );
if ( ! empty( $repeater_products ) && is_array( $repeater_products ) ) {
	foreach ( $repeater_products as $product ) {
		$products[] = [
			'image'       => $product['image'] ? wp_get_attachment_image( $product['image'], 'large', false, [ 'class' => 'img-fluid' ] ) : null,
			'heading'     => $product['heading'] ?? '',
			'description' => $product['description'] ?? '',
			'link'        => $product['link'] ?? null,
			'buy_now'     => $product['buy_now'] ?? null,
		];
	}
}
?>

<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>">
    <div class="container">
		<?php if ( $section_heading ) : ?>
            <div class="product-list-header">
                <div class="pre-heading heading-center text-uppercase"><?php echo esc_html( $section_heading ); ?></div>

				<?php if ( $section_description ) : ?>
                    <div class="description text-center">
						<?php echo wp_kses_post( $section_description ); ?>
                    </div>
				<?php endif; ?>
            </div>
		<?php endif; ?>

		<?php if ( ! empty( $products ) ) : ?>
            <div class="mt-4 category-block">
                <div class="taxonomy-row row g-5">
					<?php foreach ( $products as $product ) : ?>
					<?php
					$image       = $product['image'] ?? null;
					$heading     = $product['heading'] ?? '';
					$description = $product['description'] ?? '';
					$link        = $product['link'] ?? null;
					$buy_now     = $product['buy_now'] ?? null;

					$link_url    = $link['url'] ?? '';
					$link_target = $link['target'] ?? '_self';
					?>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="tax-item wp-block-image size-full">
							<?php if ( $image ) : ?>
                                <a class="d-block" style="color: inherit" href="javascript:void(0)"
                                   target="<?php echo esc_attr( $link_target ); ?>">
									<?php echo $image; ?>
                                </a>
							<?php endif; ?>
							<?php if ( $heading ) : ?>
                                <h4 class="wp-caption my-3">
									<?php echo esc_html( $heading ); ?>
                                </h4>
							<?php endif; ?>
                            <div class="cat-description mb-3">
								<?php if ( $description ) : ?>
                                    <div class="product-list-description mb-3">
										<?php echo wp_kses_post( $description ); ?>
                                    </div>
								<?php endif; ?>

                                <div class="cat-actions d-flex align-items-center gap-3 flex-wrap">
									<?php if ( ! empty( $link['url'] ) ): ?>
                                        <a href="<?php echo esc_url( $link['url'] ); ?>"
                                           class="block-button mt-0"><?php echo $link['title']; ?></a>
									<?php endif ?>

									<?php if ( ! empty( $buy_now['url'] ) ): ?>
                                        <div class="wp-block-button rubber-quote-button">
                                            <a class="wp-block-button__link has-text-align-center wp-element-button"
                                               href="<?php echo esc_url( $buy_now['url'] ); ?>">
												<?php echo $buy_now['title']; ?>
                                            </a>
                                        </div>
									<?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
		<?php endif; ?>
    </div>
