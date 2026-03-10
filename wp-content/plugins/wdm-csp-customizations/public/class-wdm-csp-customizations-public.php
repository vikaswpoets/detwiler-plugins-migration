<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://www.wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/public
 * @author     WisdmLabs <info@wisdmlabs.com>
 */
class Wdm_Csp_Customizations_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->init_hooks();

	}

	public function init_hooks(){
		//set custom minimum order quantity.
		add_filter('woocommerce_quantity_input_args', array($this,'custom_minimum_order_quantity'), 10, 2);

		//prevent user from adding items to cart from shop page
		// add_filter('woocommerce_add_to_cart_validation', array($this,'custom_minimum_limit_for_add_to_cart'), 10, 3);
		add_action( 'woocommerce_add_to_cart', array($this,'adjust_quantity_based_on_moq'), 10, 3 );

		//calculates the appropriate total when the product page loads
		add_filter('wdm_csp_price_html', array($this , 'modify_product_total'), 10, 2);
	}

	/**
	 * Adjusts the quantity of a product in the cart based on the Minimum Order Quantity (MOQ) settings.
	 *
	 * This function checks whether the quantity of a simple product being added to the cart meets the required MOQ.
	 * It retrieves the MOQ settings from the product meta or user-specific database records.
	 * If the quantity in the cart is below the required MOQ, it updates the cart quantity to the minimum value
	 * and displays a notice to the user.
	 *
	 * @param string $cart_item_key Unique key for the cart item.
	 * @param int $product_id ID of the product being added to the cart.
	 * @param int $quantity Quantity of the product being added.
	 */
	public function adjust_quantity_based_on_moq($cart_item_key, $product_id, $quantity) {
		
		// Retrieve the WooCommerce product object using the product ID.
		$product = wc_get_product($product_id);

		// Check if the product is of type 'simple'.
		// If the product is not simple, return without making any adjustments.
		if (!$product || !$product->is_type('simple')) {
			return;
		}

		// Retrieve the custom setting that indicates if the MOQ customization is enabled for this product.
		$setting_enabled = get_post_meta($product_id, '_moq_setting_checkbox', true);

		// If the setting indicates that MOQ is not enabled ('no'), return without modifying the cart quantity.
		if ($setting_enabled && $setting_enabled == 'no') {
			return;
		}

		// Retrieve user-specific pricing data from the database using a custom method.
		$row = $this->get_usp_data_from_database($product);

		// Initialize the variable for storing the minimum order quantity.
		$min_order_quantity = '';

		// If user-specific data is found, use the minimum quantity from the retrieved row.
		if ($row) {
			$min_order_quantity = esc_html($row->min_qty);
		}

		// If no user-specific minimum quantity is found, use the default MOQ value for all users.
		// If the default is not set, fallback to a minimum quantity of 1.
		if (!$min_order_quantity) {
			$min_order_quantity = get_post_meta($product_id, '_moq_setting_value_for_all_users', true);
			$min_order_quantity = $min_order_quantity ? $min_order_quantity : 1;
		}

		// Retrieve the WooCommerce cart object and initialize the existing quantity.
		$cart = WC()->cart->get_cart();
		$existing_quantity = $quantity;

		// Loop through the cart to find the current quantity of the specified product.
		foreach ($cart as $cart_item_key => $cart_item) {
			if ($cart_item['product_id'] == $product_id) {
				$existing_quantity = $cart_item['quantity'];
			}
		}

		// Check if the minimum order quantity is greater than 1 and if the requested quantity is less than the MOQ.
		// If the existing quantity in the cart is also less than the MOQ, update the cart quantity to the MOQ.
		if (($min_order_quantity > 1) && $quantity < $min_order_quantity && $existing_quantity < $min_order_quantity) {
			wc_add_notice(sprintf('Minimum purchase quantity for the following product/products is changed: %s', $product->get_name()), 'notice');
			WC()->cart->set_quantity($cart_item_key, $min_order_quantity ? $min_order_quantity : 1, true);
		}
	}


	/**
	 * Sets the minimum order quantity for a product based on custom settings.
	 *
	 * This function checks if the minimum order quantity is enabled for a specific product. If enabled, it retrieves
	 * the minimum order quantity for the logged-in user from a custom database table. If no user-specific setting is found,
	 * it falls back to the default minimum order quantity for the product.
	 *
	 * @param array $args The arguments for the product, including any existing minimum order settings.
	 * @param WC_Product $product The WooCommerce product object.
	 * 
	 * @return array $args Modified arguments including the minimum order quantity if applicable.
	 */
	public function custom_minimum_order_quantity($args, $product) {
		
		// Check if the product is of type 'simple'
		if (!$product->is_type('simple')) {
			return $args; // Return without modifying if not a simple product
		}

		$product_id = $product->get_id();

		// Retrieve the setting to check if the minimum order quantity customization is enabled for this product
		$setting_enabled = get_post_meta($product_id, '_moq_setting_checkbox', true);

		if($setting_enabled && $setting_enabled == 'no'){
			return $args;
		}

		// Retrieve user-specific pricing data from the custom database
		$row = $this->get_usp_data_from_database($product);

		// Initialize minimum order quantity
		$min_order_quantity = '';

		if ($row) {
			// Extract minimum order quantity from the database row
			$min_order_quantity = esc_html($row->min_qty);
		}

		// If a user-specific minimum order quantity is found, set it in the arguments
		if ($min_order_quantity) {
			$args['min_value'] = $min_order_quantity;
		} else {
			// Fallback to the default minimum order quantity if no user-specific value is found
			$minimum_order_number = get_post_meta($product_id, '_moq_setting_value_for_all_users', true);
			$args['min_value'] = $minimum_order_number;
		}

		// Return the modified arguments with the minimum order quantity
		return $args;
	}



	/**
	 * Enforces a minimum order quantity for adding products to the cart.
	 *
	 * This function checks if a user is trying to add a product to the cart in a quantity lower than the minimum allowed.
	 * If so, it prevents the product from being added and displays an error message. 
	 *
	 * @param bool $passed Whether the product can be added to the cart. Defaults to true.
	 * @param int $product_id The ID of the product being added to the cart.
	 * @param int $quantity The quantity of the product being added to the cart.
	 *
	 * @return bool $passed Whether the product can be added to the cart (false if the minimum quantity condition is not met).
	 */
	public function custom_minimum_limit_for_add_to_cart($passed, $product_id, $quantity) {

		$product = wc_get_product($product_id);

		// Check if the product is of type 'simple'
		if (!$product || !$product->is_type('simple')) {
			return $passed; // Return without modifying if not a simple product
		}

		// Retrieve the setting to check if the minimum order quantity customization is enabled for this product
		$setting_enabled = get_post_meta($product_id, '_moq_setting_checkbox', true);

		if($setting_enabled && $setting_enabled == 'no'){
			return $passed;
		}

		// Retrieve user-specific pricing data
		$row = $this->get_usp_data_from_database($product);

		// Initialize minimum order quantity
		$min_order_quantity = '';

		if ($row) {
			// Extract minimum order quantity from the database row
			$min_order_quantity = esc_html($row->min_qty);
		}

		// If no user-specific minimum quantity is found, fall back to the default value for all users
		if (!$min_order_quantity) {
			$min_order_quantity = get_post_meta($product_id, '_moq_setting_value_for_all_users', true);
			$min_order_quantity = $min_order_quantity ? $min_order_quantity : 1;
		}
		// Check if the quantity being added to the cart is below the minimum order quantity
		if ($quantity < $min_order_quantity) {
			// Add a notice to WooCommerce stating the minimum quantity required
			wc_add_notice(sprintf(__('A minimum of %d units is required to add this product to your cart.', 'wdm-csp-customizations'), $min_order_quantity), 'error');
			// Prevent the product from being added to the cart and exit early
			
			return false;
		}

		// Return whether the product can be added to the cart (false if the minimum order condition is not met)
		return $passed;
	}


	/**
	 * Retrieves user-specific pricing data from the database for a given product.
	 *
	 * This function checks if the minimum order customization is enabled for the product and, if so,
	 * fetches user-specific pricing information from a custom database table. It retrieves the lowest 
	 * minimum order quantity and associated pricing details for the current logged-in user.
	 *
	 * @param WC_Product $product The WooCommerce product object for which to retrieve pricing data.
	 *
	 * @return object|false An object containing the user-specific pricing data if available, or false if not found or settings are not enabled.
	 */
	public function get_usp_data_from_database($product) {
		// Get the product ID
		$product_id = $product->get_id();

		// Retrieve the setting to check if the minimum order quantity customization is enabled for this product
		$setting_enabled = get_post_meta($product_id, '_moq_setting_checkbox', true);

		// Proceed only if the minimum order customization is enabled for this product
		if (!$setting_enabled || ($setting_enabled && $setting_enabled == 'yes')) {
			global $wpdb;

			// Get the current logged-in user's ID
			$user_id = get_current_user_id();

			// Check if both product ID and user ID are valid
			if ($product_id > 0 && $user_id > 0) {
				// Define the table name where user-specific pricing is stored
				$table_name = $wpdb->prefix . 'wusp_user_pricing_mapping';

				// Check if the custom table exists in the database
				if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
					// Prepare a query to get the lowest minimum quantity for the product for the current user
					$query = $wpdb->prepare("
						SELECT * 
						FROM $table_name
						WHERE product_id = %d AND user_id = %d
						ORDER BY min_qty ASC
						LIMIT 1
					", $product_id, $user_id);

					// Execute the query and retrieve the row
					$row = $wpdb->get_row($query);

					// Check if the query executed successfully and if the row exists
					if (!is_wp_error($row) && $row) {
						// Return the row object containing the user-specific pricing data
						return $row;
					}
				}
			}
		}

		// Return false if no data is found or settings are not enabled
		return false;
	}



	/**
	 * Modifies the product total price displayed on the frontend based on user-specific pricing.
	 *
	 * This function customizes the displayed total price for a product based on the minimum order quantity and user-specific
	 * pricing details stored in a custom database table. The total price is adjusted according to a discount or flat price if
	 * applicable. The modified price replaces the original price displayed in the product's HTML.
	 *
	 * @param string $priceTotalHtml The original HTML content displaying the product total price.
	 * @param WC_Product $product The WooCommerce product object.
	 *
	 * @return string $customHtml The modified HTML content with the adjusted product total price.
	 */
	public function modify_product_total($priceTotalHtml, $product) {
		// Check if the product is of type 'simple'
		if (!$product->is_type('simple')) {
			return $priceTotalHtml; // Return without modifying if not a simple product
		}

		// Retrieve the original product price
		$lowest_price = $product->get_price();
		$product_id = $product->get_id();

		// Retrieve the setting to check if the minimum order quantity customization is enabled for this product
		$setting_enabled = get_post_meta($product_id, '_moq_setting_checkbox', true);

		if($setting_enabled && $setting_enabled == 'no'){
			return $priceTotalHtml;
		}

		// Fetch user-specific pricing data from the database
		$row = $this->get_usp_data_from_database($product);

		// Initialize minimum order quantity and pricing variables
		$min_order_quantity = '';
		$discount_type = '';

		if ($row) {
			// Extract data from the retrieved row
			$min_order_quantity = esc_html($row->min_qty);
			$discount_type = $row->flat_or_discount_price;

			if ($discount_type == 1) {
				// Apply flat price discount
				$lowest_price = $row->price;
			} else if ($discount_type == 2) {
				// Apply percentage discount
				$lowest_price -= number_format($lowest_price * ($row->price / 100), 2);
			}
		}

		
    	// If no user-specific minimum quantity is found, fall back to the default value for all users
		if (!$min_order_quantity) {
			$min_order_quantity = (int) get_post_meta($product_id, '_moq_setting_value_for_all_users', true);
			$min_order_quantity = $min_order_quantity ? $min_order_quantity : 1;
		}
	
		// Ensure lowest_price is a float for accurate calculations
		$lowest_price = (float) $lowest_price;
	
		// Calculate the raw price based on minimum order quantity and adjusted price
		$raw_price = $min_order_quantity * $lowest_price;
		$price = number_format($raw_price, 2);
	
		// Use regular expression to replace the original price in the HTML with the new price
		$pattern = '/<\/span>(.*?)<\/bdi>/';
		$customHtml = preg_replace($pattern, '</span>' . $price . '</bdi>', $priceTotalHtml);
	
		// Return the modified HTML with the adjusted price
		return $customHtml;
	}



	
	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wdm-csp-customizations-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wdm-csp-customizations-public.js', array( 'jquery' ), $this->version, false );
		
	}

}






