<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://www.wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/admin
 * @author     WisdmLabs <info@wisdmlabs.com>
 */
class Wdm_Csp_Customizations_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->init_hooks();

	}

	public function init_hooks(){
		// Add a custom tab to the product data tabs only for simple products
		add_filter('woocommerce_product_data_tabs', array($this, 'add_minimum_order_settings'), 10, 1);
	
		// Add content to the minimum order settings tab only for simple products
		add_action('woocommerce_product_data_panels', array($this, 'add_content_to_minimum_order_settings'));

		// Save the minimum order settings values
		add_action('woocommerce_process_product_meta', array($this, 'save_minimum_order_settings_data'));

		
	}


	/**
	 * Adds a custom tab to the WooCommerce product edit page for minimum order settings.
	 *
	 * This function adds a new tab to the product edit page in WooCommerce, specifically for simple products. 
	 * The tab is used to display and manage minimum order settings for the product.
	 *
	 * @param array $tabs Existing product tabs.
	 * 
	 * @return array Modified product tabs with the new 'Minimum Order Setting' tab added.
	 */
	public function add_minimum_order_settings($tabs) {
		global $post;
		// Check if we are on the product edit page and the post type is 'product'
		if (isset($post->post_type) && $post->post_type === 'product') {
			// Get the product object
			$product = wc_get_product($post->ID);
			
			// Check if the product exists and is a simple product
			if ($product && $product->is_type('simple')) {
				// Add a new tab for minimum order settings
				$tabs['minimum_order_setting'] = array(
					'label'    => __('Minimum Order Setting', 'woocommerce'), // Tab label
					'target'   => 'minimum_order_setting_data', // ID of the tab content
					'class'    => array('show_if_simple'), // Class for conditional visibility
					'priority' => 50, // Tab priority for positioning
				);
			}
		}

		return $tabs; // Return the modified tabs array
	}


	/**
	 * Adds content to the custom 'Minimum Order Setting' tab on the WooCommerce product edit page.
	 *
	 * This function outputs the HTML content for the custom tab added to the WooCommerce product edit page,
	 * specifically for simple products. It includes fields for configuring minimum order customization settings.
	 * 
	 * @return void
	 */
	public function add_content_to_minimum_order_settings() {
		global $post;

		// Get the product object
		$product = wc_get_product($post->ID);

		// Check if the product exists and is a simple product
		if ($product && $product->is_type('simple')) {
			// Retrieve existing meta values or set defaults
			$custom_checkbox = get_post_meta($post->ID, '_moq_setting_checkbox', true);
			$custom_number = get_post_meta($post->ID, '_moq_setting_value_for_all_users', true);

			// Set default values if meta values are empty
			if ($custom_checkbox === '') {
				$custom_checkbox = 'yes'; // Checkbox is enabled by default
			}
			if ($custom_number === '') {
				$custom_number = '1'; // Default minimum order quantity value
			}
			?>
			<div id='minimum_order_setting_data' class='panel woocommerce_options_panel'>
				<div class='options_group'>
					<?php
					// Checkbox field for enabling/disabling minimum order customization
					woocommerce_wp_checkbox(
						array(
							'id'            => '_moq_setting_checkbox',
							'label'         => __('Minimum Order Customization', 'woocommerce'),
							'description'   => __('Check this box to enable the Minimum Order Customization.', 'woocommerce'),
							'value'         => $custom_checkbox
						)
					);

					// Number input field for specifying the minimum order quantity
					woocommerce_wp_text_input(
						array(
							'id'            => '_moq_setting_value_for_all_users',
							'label'         => __('Minimum Order Quantity', 'woocommerce'),
							'description'   => __('Minimum Order Quantity number for all users.', 'woocommerce'),
							'type'          => 'number',
							'custom_attributes' => array('min' => '0', 'step' => '1'), // Only allow integers
							'value'         => $custom_number
						)
					);
					?>
				</div>
			</div>
			<?php
		}
	}


	/**
	 * Saves the minimum order settings for a product when the product is updated.
	 *
	 * This function handles saving the values for minimum order settings when a product is saved. It updates
	 * the meta data associated with the product to store the state of the minimum order customization checkbox
	 * and the minimum order quantity.
	 * 
	 * @param int $post_id The ID of the post (product) being saved.
	 * 
	 * @return void
	 */
	public function save_minimum_order_settings_data($post_id) {
		// Get the product object
		$product = wc_get_product($post_id);

		// Check if the product exists and is a simple product
		if ($product && $product->is_type('simple')) {
			// Retrieve and sanitize the checkbox value
			$custom_checkbox = isset($_POST['_moq_setting_checkbox']) ? 'yes' : 'no';
			// Update the checkbox meta value
			update_post_meta($post_id, '_moq_setting_checkbox', $custom_checkbox);

			// Retrieve and sanitize the number value
			$custom_number = isset($_POST['_moq_setting_value_for_all_users']) ? sanitize_text_field($_POST['_moq_setting_value_for_all_users']) : '';
			// Update the number meta value
			update_post_meta($post_id, '_moq_setting_value_for_all_users', $custom_number);
		}
	}

	

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wdm_Csp_Customizations_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wdm_Csp_Customizations_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wdm-csp-customizations-admin.css', array(), $this->version, 'all' );
		
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wdm_Csp_Customizations_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wdm_Csp_Customizations_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wdm-csp-customizations-admin.js', array( 'jquery' ), $this->version, false );
		
	}

}


