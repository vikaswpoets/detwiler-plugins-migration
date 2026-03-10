<?php
/**
 * GI Contact Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 */

// Create id attribute allowing for custom "anchor" value
$id = 'gi-contact-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values
$className = 'gi-contact-block webshop-block text-center';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults
$contact_text = get_field('contact_text') ?: '';
$button_link = get_field('button_link') ?: array();
$button_text = $button_link['text'] ?? 'Contact Us';
$button_url = $button_link['page'] ?? '#';

// Get style options
$style_options = get_field('style_options') ?: array();
$background_color = $style_options['background_color'] ?? '#ff0000';
$text_color = $style_options['text_color'] ?? '#ffffff';


$button_class = 'gi-contact-button block-button mt-0 button-white';
$block_style = 'background-color: ' . esc_attr($background_color) . '; color: ' . esc_attr($text_color) . ';';
?>

<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>" style="<?php echo $block_style; ?>">
    <div class="container">
        <div class="gi-contact-inner">
            <?php if ($contact_text) : ?>
                <h2 class="gi-contact-text">
                    <?php echo wpautop(esc_html($contact_text)); ?>
                </h2>
            <?php endif; ?>

            <?php if ($button_url && $button_text) : ?>
                <div class="gi-contact-button-wrapper">
                    <a href="<?php echo esc_url($button_url); ?>" class="<?php echo esc_attr($button_class); ?>">
                        <span><?php echo esc_html($button_text); ?></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
