<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://www.wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/includes
 * @author     WisdmLabs <info@wisdmlabs.com>
 */
class Wdm_Csp_Customizations_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if(!is_plugin_active('customer-specific-pricing-for-woocommerce/customer-specific-pricing-for-woocommerce.php')){
			deactivate_plugins(plugin_basename(__FILE__));			
			wp_die(__('Sorry, but this plugin requires the "Customer Specific Pricing for WooCommerce" to be installed and active.', 'my-plugin-textdomain'));

		}
		
		
	}
	

}
