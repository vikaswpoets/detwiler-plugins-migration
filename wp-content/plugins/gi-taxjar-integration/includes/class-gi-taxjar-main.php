
<?php
/**
 * Main plugin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class GI_TaxJar_Main {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize components
        add_action('init', array($this, 'init_components'));
        
        // Load textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'register_settings'));
        }
    }
    
    private function load_dependencies() {
        // Load Composer autoload if available
        if (file_exists(GI_TAXJAR_PLUGIN_DIR . 'vendor/autoload.php')) {
            require_once GI_TAXJAR_PLUGIN_DIR . 'vendor/autoload.php';
        }
        
        // Load classes
        require_once GI_TAXJAR_PLUGIN_DIR . 'includes/class-gi-taxjar-integration.php';
        require_once GI_TAXJAR_PLUGIN_DIR . 'includes/class-gi-taxjar-admin.php';
        require_once GI_TAXJAR_PLUGIN_DIR . 'includes/class-gi-taxjar-ajax.php';
    }
    
    public function init_components() {
        // Initialize the main integration class
        new GI_TaxJar_Integration();
        new GI_TaxJar_Ajax();
        
        if (is_admin()) {
            new GI_TaxJar_Admin();
        }
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('gi-taxjar', false, dirname(plugin_basename(GI_TAXJAR_PLUGIN_FILE)) . '/languages');
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('TaxJar Settings', 'gi-taxjar'),
            __('TaxJar', 'gi-taxjar'),
            'manage_options',
            'gi-taxjar-settings',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('gi_taxjar_settings', 'gi_taxjar_options');
        
        add_settings_section(
            'gi_taxjar_api_section',
            __('API Settings', 'gi-taxjar'),
            array($this, 'api_section_callback'),
            'gi-taxjar-settings'
        );
        
        add_settings_field(
            'api_key',
            __('TaxJar API Key', 'gi-taxjar'),
            array($this, 'api_key_callback'),
            'gi-taxjar-settings',
            'gi_taxjar_api_section'
        );
        
        add_settings_field(
            'sandbox_mode',
            __('Sandbox Mode', 'gi-taxjar'),
            array($this, 'sandbox_mode_callback'),
            'gi-taxjar-settings',
            'gi_taxjar_api_section'
        );
        
        add_settings_field(
            'enabled',
            __('Enable TaxJar', 'gi-taxjar'),
            array($this, 'enabled_callback'),
            'gi-taxjar-settings',
            'gi_taxjar_api_section'
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('gi_taxjar_settings');
                do_settings_sections('gi-taxjar-settings');
                submit_button();
                ?>
            </form>
            
            <div class="card" style="margin-top: 20px;">
                <h2><?php _e('Test Connection', 'gi-taxjar'); ?></h2>
                <p><?php _e('Click the button below to test your TaxJar API connection.', 'gi-taxjar'); ?></p>
                <button type="button" class="button" id="test-taxjar-connection">
                    <?php _e('Test Connection', 'gi-taxjar'); ?>
                </button>
                <div id="test-result"></div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-taxjar-connection').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gi_taxjar_test_connection',
                        nonce: '<?php echo wp_create_nonce('gi_taxjar_test'); ?>'
                    },
                    success: function(response) {
                        $('#test-result').html(response.data.message);
                        button.prop('disabled', false).text('Test Connection');
                    },
                    error: function() {
                        $('#test-result').html('<div class="notice notice-error"><p>Connection test failed.</p></div>');
                        button.prop('disabled', false).text('Test Connection');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function api_section_callback() {
        echo '<p>' . __('Configure your TaxJar API settings below.', 'gi-taxjar') . '</p>';
    }
    
    public function api_key_callback() {
        $options = get_option('gi_taxjar_options');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        echo '<input type="text" id="api_key" name="gi_taxjar_options[api_key]" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your TaxJar API key. You can find this in your TaxJar dashboard.', 'gi-taxjar') . '</p>';
    }
    
    public function sandbox_mode_callback() {
        $options = get_option('gi_taxjar_options');
        $sandbox = isset($options['sandbox_mode']) ? $options['sandbox_mode'] : '';
        echo '<input type="checkbox" id="sandbox_mode" name="gi_taxjar_options[sandbox_mode]" value="1" ' . checked(1, $sandbox, false) . ' />';
        echo '<label for="sandbox_mode">' . __('Enable sandbox mode for testing', 'gi-taxjar') . '</label>';
    }
    
    public function enabled_callback() {
        $options = get_option('gi_taxjar_options');
        $enabled = isset($options['enabled']) ? $options['enabled'] : '';
        echo '<input type="checkbox" id="enabled" name="gi_taxjar_options[enabled]" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="enabled">' . __('Enable TaxJar tax calculation', 'gi-taxjar') . '</label>';
    }
}