<?php
/**
 * Plugin Name: GI TaxJar Integration
 * Plugin URI: https://yoursite.com
 * Description: Custom TaxJar integration for WooCommerce with real-time tax calculation
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * Text Domain: gi-taxjar
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GI_TAXJAR_VERSION', '1.0.0');
define('GI_TAXJAR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GI_TAXJAR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GI_TAXJAR_PLUGIN_FILE', __FILE__);

// Check if WooCommerce is active
add_action('plugins_loaded', 'gi_taxjar_init');

function gi_taxjar_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'gi_taxjar_woocommerce_missing_notice');
        return;
    }

    // Load the main plugin class
    require_once GI_TAXJAR_PLUGIN_DIR . 'includes/class-gi-taxjar-main.php';

    // Initialize the plugin
    GI_TaxJar_Main::instance();
}

function gi_taxjar_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>GI TaxJar Integration</strong> requires WooCommerce to be installed and active.</p></div>';
}

// Plugin activation hook
register_activation_hook(__FILE__, 'gi_taxjar_activate');
function gi_taxjar_activate() {
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('This plugin requires PHP version 7.4 or higher.');
    }
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'gi_taxjar_deactivate');
function gi_taxjar_deactivate() {
    // Clean up any temporary data
    wp_clear_scheduled_hook('gi_taxjar_cleanup');
}
