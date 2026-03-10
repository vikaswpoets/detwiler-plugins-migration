<?php
/**
 * Product List Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 */

// Create id attribute allowing for custom "anchor" value
$id = 'product-list-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values
$className = 'product-list-block';
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
			'image'       => $product['image'] ? wp_get_attachment_image($product['image'], 'large', false, ['class' => 'img-fluid']) : null,
			'heading'     => $product['heading'] ?? '',
			'description' => $product['description'] ?? '',
			'link'        => $product['link'] ?? null,
			'buy_now'        => $product['buy_now'] ?? null,
		];
	}
}
?>

<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>">
    <div class="container">
		<?php if ( $section_heading ) : ?>
            <div class="product-list-header">
                <h2 class="product-list-heading"><?php echo esc_html( $section_heading ); ?></h2>

				<?php if ( $section_description ) : ?>
                    <div class="product-list-description">
						<?php echo wp_kses_post( $section_description ); ?>
                    </div>
				<?php endif; ?>
            </div>
		<?php endif; ?>

		<?php if ( ! empty( $products ) ) : ?>
            <div class="product-list-items">
                <table>
                    <tbody>
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

                        <tr class="product-item" style="background: #cfe6e9; border-bottom: 30px solid #fff">
                            <td class="product-image">
								<?php if ( $image ) : ?>
                                    <a href="<?php echo esc_url( $link_url ); ?>"
                                       target="<?php echo esc_attr( $link_target ); ?>">
                                        <?php echo $image; ?>
                                    </a>
								<?php endif; ?>
                            </td>

                            <td class="product-content">
								<?php if ( $heading ) : ?>
                                    <h4 class="product-heading">
										<?php if ( $link_url ) : ?>
                                            <a href="<?php echo esc_url( $link_url ); ?>"
                                               target="<?php echo esc_attr( $link_target ); ?>">
												<?php echo esc_html( $heading ); ?>
                                            </a>
										<?php else : ?>
											<?php echo esc_html( $heading ); ?>
										<?php endif; ?>
                                    </h4>
								<?php endif; ?>

								<?php if ( $description ) : ?>
                                    <div class="product-list-description">
										<?php echo wp_kses_post( $description ); ?>
                                    </div>
								<?php endif; ?>

                            </td>
                            <td>
                                <div class="product-link mx-3">
                                    <a href="<?php echo esc_url( $link_url ); ?>" class="block-button"
                                       target="<?php echo esc_attr( $link_target ); ?>">
										<?php echo $link['title'] ?: __('Find More Out', 'cabling'); ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="product-link buy-now-link mx-3">
                                    <a href="<?php echo esc_url( $buy_now['url'] ); ?>" class="block-button button-blue"
                                       target="<?php echo esc_attr( $buy_now['target'] ); ?>">
										<?php echo $buy_now['title']; ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
		<?php endif; ?>
    </div>
</div>
