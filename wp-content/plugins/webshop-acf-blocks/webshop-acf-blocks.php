<?php
/**
* Plugin Name: Webshop ACF Blocks
* Description: All custom Gutenberg blocks using ACF.
* Version: 1.0.0
*/


if (!defined('ABSPATH')) {
    die();
}

define('WEBSHOP_ACF_DIR_PATH', plugin_dir_path(__FILE__));
define('WEBSHOP_ACF_DIR_URL', plugin_dir_url(__FILE__));

require_once WEBSHOP_ACF_DIR_PATH . 'includes/functions.php';
require_once WEBSHOP_ACF_DIR_PATH . 'includes/ajax.php';

add_action('wp_enqueue_scripts', 'webshop_plugins_script');
function webshop_plugins_script() {
    wp_enqueue_style( 'webshop_plugin_style', WEBSHOP_ACF_DIR_URL . '/assets/css/webshop-style.css');

    wp_enqueue_script( 'webshop_plugin_script', WEBSHOP_ACF_DIR_URL . '/assets/js/webshop-script.js', array(), null, true );
}

function webshop_register_acf_block_types()
{
    $block_icon = '';
	acf_register_block_type([
        'name' => 'webshop_media_text',
        'title' => __('GI Media & Text', 'cabling'),
        'description' => __('', 'cabling'),
        'render_template' => WEBSHOP_ACF_DIR_PATH . '/block-templates/media_text/media_text.php',
        'enqueue_assets' => function () {
            wp_enqueue_style(
                'media_text',
                WEBSHOP_ACF_DIR_URL . '/block-templates/media_text/media_text.css',
                '',
                '1.0'
            );
        },
        'category' => 'webshop_blocks',
        'mode' => 'preview',
        'icon' => $block_icon,
        'keywords' => array('media_text', 'cabling'),
        'post_type' => [
            'page',
        ],
        'supports' => [
            'align' => ['full'],
            'anchor' => true,
            'customClassName' => true,
        ]
    ]);
	acf_register_block_type([
        'name' => 'webshop_customer_story_text',
        'title' => __('GI Customer Story', 'cabling'),
        'description' => __('GI Customer Story Block', 'cabling'),
        'render_template' => WEBSHOP_ACF_DIR_PATH . '/block-templates/webshop_customer_story/webshop_customer_story.php',
        'enqueue_assets' => function () {
            wp_enqueue_style(
                'webshop_customer_story',
                WEBSHOP_ACF_DIR_URL . '/block-templates/webshop_customer_story/webshop_customer_story.css',
                '',
                '1.0'
            );
        },
        'category' => 'webshop_blocks',
        'mode' => 'preview',
        'icon' => $block_icon,
        'keywords' => array('webshop_customer_story', 'cabling'),
        'post_type' => [
            'page',
        ],
        'supports' => [
            'align' => ['full'],
            'anchor' => true,
            'customClassName' => true,
        ]
    ]);
	acf_register_block_type([
        'name' => 'webshop_request_a_quote',
        'title' => __('GI Request A Quote', 'cabling'),
        'description' => __('GI Request A Quote Block', 'cabling'),
        'render_template' => WEBSHOP_ACF_DIR_PATH . '/block-templates/webshop_request_a_quote/webshop_request_a_quote.php',
        'enqueue_assets' => function () {
            wp_enqueue_style(
                'webshop_request_a_quote',
                WEBSHOP_ACF_DIR_URL . '/block-templates/webshop_request_a_quote/webshop_request_a_quote.css',
                '',
                '1.0'
            );
        },
        'category' => 'webshop_blocks',
        'mode' => 'preview',
        'icon' => $block_icon,
        'keywords' => array('quote', 'cabling'),
        'post_type' => [
            'page',
        ],
        'supports' => [
            'align' => ['full'],
            'anchor' => true,
            'customClassName' => true,
        ]
    ]);
	acf_register_block_type([
        'name' => 'webshop_request_a_quote_button',
        'title' => __('GI Request A Quote Button', 'cabling'),
        'description' => __('GI Request A Quote Button Block', 'cabling'),
        'render_template' => WEBSHOP_ACF_DIR_PATH . '/block-templates/webshop_request_a_quote_button/webshop_request_a_quote_button.php',
        'category' => 'webshop_blocks',
        'mode' => 'preview',
        'icon' => $block_icon,
        'keywords' => array('quote', 'cabling'),
        'post_type' => [
            'page',
        ],
        'supports' => [
            'align' => ['full'],
            'anchor' => true,
            'customClassName' => true,
        ]
    ]);

	acf_register_block_type([
        'name' => 'webshop_content_section',
        'title' => __('GI Content Section', 'cabling'),
        'description' => __('GI Content Section', 'cabling'),
        'render_template' => WEBSHOP_ACF_DIR_PATH . '/block-templates/webshop_content_section/webshop_content_section.php',
        'category' => 'webshop_blocks',
        'mode' => 'preview',
        'icon' => $block_icon,
        'keywords' => array('webshop_content_section', 'cabling'),
        'post_type' => [
            'page',
        ],
        'supports' => [
            'align' => ['full'],
            'anchor' => true,
            'customClassName' => true,
        ]
    ]);

	acf_register_block_type([
        'name' => 'gi_download_block',
        'title' => __('GI Download', 'cabling'),
        'description' => __('GI Download', 'cabling'),
        'render_template' => WEBSHOP_ACF_DIR_PATH . '/block-templates/gi_download_block/gi_download_block.php',
        'category' => 'webshop_blocks',
        'mode' => 'preview',
        'icon' => $block_icon,
        'keywords' => array('download', 'gi'),
        'post_type' => [
            'page',
        ],
        'supports' => [
            'align' => ['full'],
            'anchor' => true,
            'customClassName' => true,
        ]
    ]);

	acf_register_block_type([
	    'name'              => 'webshop_child_pages_columns',
	    'title'             => __('GI Child Pages Columns', 'cabling'),
	    'description'       => __('Display child pages in multiple columns with heading and description', 'cabling'),
	    'render_template'   => WEBSHOP_ACF_DIR_PATH . '/block-templates/child_pages_columns/child_pages_columns.php',
	    'enqueue_assets'    => function () {
	        wp_enqueue_style(
	            'child_pages_columns',
	            WEBSHOP_ACF_DIR_URL . '/block-templates/child_pages_columns/child_pages_columns.css',
	            '',
	            '1.0'
	        );
	    },
	    'category'          => 'webshop_blocks',
	    'mode'              => 'preview',
	    'icon'              => $block_icon,
	    'keywords'          => array('child pages', 'columns', 'cabling'),
	    'post_type'         => [
	        'page',
	    ],
	    'supports'          => [
	        'align'             => ['full', 'wide'],
	        'anchor'            => true,
	        'customClassName'   => true,
	    ]
	]);

	acf_register_block_type([
	    'name'              => 'webshop_upcoming_events',
	    'title'             => __('GI Upcoming Events', 'cabling'),
	    'description'       => __('Display upcoming events in a slider format', 'cabling'),
	    'render_template'   => WEBSHOP_ACF_DIR_PATH . '/block-templates/upcoming_events/upcoming_events.php',
	    'enqueue_assets'    => function () {

	        // Enqueue block specific styles
	        wp_enqueue_style(
	            'upcoming-events',
	            WEBSHOP_ACF_DIR_URL . '/block-templates/upcoming_events/upcoming_events.css',
	            '',
	            '1.0'
	        );

	        // Enqueue block specific scripts
	        wp_enqueue_script(
	            'upcoming-events',
	            WEBSHOP_ACF_DIR_URL . '/block-templates/upcoming_events/upcoming_events.js',
	            array('jquery'),
	            '1.0',
	            true
	        );

			// Localize the script with the necessary data
	        wp_localize_script('upcoming-events', 'gi_ajax', array(
	            'ajax_url' => admin_url('admin-ajax.php'),
	            'nonce' => wp_create_nonce('webshop_ajax_nonce'),
	        ));

	    },
	    'category'          => 'webshop_blocks',
	    'mode'              => 'preview',
	    'icon'              => $block_icon,
	    'keywords'          => array('events', 'slider', 'calendar'),
	    'post_type'         => [
	        'page',
	    ],
	    'supports'          => [
	        'align'             => ['full', 'wide'],
	        'anchor'            => true,
	        'customClassName'   => true,
	    ]
	]);

	acf_register_block_type([
	    'name'              => 'webshop_gi_contact',
	    'title'             => __('GI Contact', 'cabling'),
	    'description'       => __('Contact section with text, button, and customizable colors', 'cabling'),
	    'render_template'   => WEBSHOP_ACF_DIR_PATH . '/block-templates/gi_contact/gi_contact.php',
	    'enqueue_assets'    => function () {
	        // Enqueue block specific styles
	        wp_enqueue_style(
	            'gi-contact',
	            WEBSHOP_ACF_DIR_URL . '/block-templates/gi_contact/gi_contact.css',
	            '',
	            '1.0'
	        );
	    },
	    'category'          => 'webshop_blocks',
	    'mode'              => 'preview',
	    'icon'              => $block_icon,
	    'keywords'          => array('contact', 'cta', 'button'),
	    'post_type'         => [
	        'page',
	    ],
	    'supports'          => [
	        'align'             => ['full', 'wide'],
	        'anchor'            => true,
	        'customClassName'   => true,
	    ]
	]);

	acf_register_block_type([
	    'name'              => 'webshop_product_list',
	    'title'             => __('Product List', 'cabling'),
	    'description'       => __('Display a list of products with image, heading, description, and link', 'cabling'),
	    'render_template'   => WEBSHOP_ACF_DIR_PATH . '/block-templates/product_list/product_list.php',
	    'enqueue_assets'    => function () {
	        // Enqueue block specific styles
	        wp_enqueue_style(
	            'product-list-css',
	            WEBSHOP_ACF_DIR_URL . '/block-templates/product_list/product_list.css',
	            [],
	            '1.0'
	        );
	    },
	    'category'          => 'webshop_blocks',
	    'mode'              => 'preview',
	    'icon'              => $block_icon,
	    'keywords'          => ['products', 'list', 'items', 'showcase'],
	    'post_type'         => [
	        'page',
	    ],
	    'supports'          => [
	        'align'             => ['full', 'wide'],
	        'anchor'            => true,
	        'customClassName'   => true,
	    ]
	]);
	acf_register_block_type([
	    'name'              => 'webshop_production_equipment',
	    'title'             => __('Production Equipment', 'cabling'),
	    'description'       => __('Display a list of products with image, heading, description, and link', 'cabling'),
	    'render_template'   => WEBSHOP_ACF_DIR_PATH . '/block-templates/production_equipment/production_equipment.php',
	    'enqueue_assets'    => function () {
	        // Enqueue block specific styles
	        wp_enqueue_style(
	            'production_equipment-css',
	            WEBSHOP_ACF_DIR_URL . '/block-templates/production_equipment/production_equipment.css',
	            [],
	            '1.0'
	        );
	    },
	    'category'          => 'webshop_blocks',
	    'mode'              => 'preview',
	    'icon'              => $block_icon,
	    'keywords'          => ['products', 'list', 'items', 'showcase'],
	    'post_type'         => [
	        'page',
	    ],
	    'supports'          => [
	        'align'             => ['full', 'wide'],
	        'anchor'            => true,
	        'customClassName'   => true,
	    ]
	]);
}

if (function_exists('acf_register_block_type')) {
    add_action('acf/init', 'webshop_register_acf_block_types');
}

/**
 * add custom block category for ACF blocks
 * @link https://developer.wordpress.org/block-editor/developers/filters/block-filters/#managing-block-categories
 */
function webshop_block_category($categories, $post)
{
    return array_merge(
        $categories,
        [
            [
                'slug' => 'webshop_blocks',
                'title' => __('GI Blocks', 'cabling'),
            ],
        ]
    );
}

add_filter('block_categories_all', 'webshop_block_category', 10, 2);

add_theme_support( 'align-wide' );
