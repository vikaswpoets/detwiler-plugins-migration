
<?php
/**
 * Admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class GI_TaxJar_Admin {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename(GI_TAXJAR_PLUGIN_FILE), array($this, 'add_settings_link'));
        
        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Add order meta box
        add_action('add_meta_boxes', array($this, 'add_order_meta_box'));
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=gi-taxjar-settings') . '">' . __('Settings', 'gi-taxjar') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function admin_notices() {
        $options = get_option('gi_taxjar_options', array());
        
        // Show notice if plugin is enabled but no API key is set
        if (!empty($options['enabled']) && empty($options['api_key'])) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . sprintf(
                __('TaxJar is enabled but no API key is configured. <a href="%s">Configure now</a>', 'gi-taxjar'),
                admin_url('options-general.php?page=gi-taxjar-settings')
            ) . '</p>';
            echo '</div>';
        }
        
        // Show notice if TaxJar PHP SDK is not installed
        if (!class_exists('TaxJar\Client')) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . __('TaxJar PHP SDK is not installed. Please run "composer require taxjar/taxjar-php" in your plugin directory.', 'gi-taxjar') . '</p>';
            echo '</div>';
        }
    }
    
    public function add_order_meta_box() {
        add_meta_box(
            'gi-taxjar-order-data',
            __('TaxJar Tax Information', 'gi-taxjar'),
            array($this, 'order_meta_box_content'),
            'shop_order',
            'side',
            'default'
        );
    }
    
    public function order_meta_box_content($post) {
        $order = wc_get_order($post->ID);
        
        if (!$order->get_meta('_gi_taxjar_calculated')) {
            echo '<p>' . __('This order was not calculated using TaxJar.', 'gi-taxjar') . '</p>';
            return;
        }
        
        $tax_amount = $order->get_meta('_gi_taxjar_tax_amount');
        $tax_rate = $order->get_meta('_gi_taxjar_tax_rate');
        $jurisdictions = $order->get_meta('_gi_taxjar_jurisdictions');
        $has_nexus = $order->get_meta('_gi_taxjar_has_nexus');
        
        echo '<table class="widefat">';
        echo '<tr><td><strong>' . __('Tax Amount:', 'gi-taxjar') . '</strong></td><td>' . wc_price($tax_amount) . '</td></tr>';
        echo '<tr><td><strong>' . __('Tax Rate:', 'gi-taxjar') . '</strong></td><td>' . round($tax_rate * 100, 4) . '%</td></tr>';
        echo '<tr><td><strong>' . __('Has Nexus:', 'gi-taxjar') . '</strong></td><td>' . ($has_nexus ? __('Yes', 'gi-taxjar') : __('No', 'gi-taxjar')) . '</td></tr>';
        echo '</table>';
        
        if (!empty($jurisdictions) && is_array($jurisdictions)) {
            echo '<h4>' . __('Tax Jurisdictions:', 'gi-taxjar') . '</h4>';
            echo '<ul>';
            foreach ($jurisdictions as $key => $value) {
                if (!empty($value)) {
                    echo '<li><strong>' . esc_html(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ' . esc_html($value) . '</li>';
                }
            }
            echo '</ul>';
        }
    }
}