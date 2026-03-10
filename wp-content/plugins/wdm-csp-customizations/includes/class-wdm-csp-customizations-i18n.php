<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://www.wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wdm_Csp_Customizations
 * @subpackage Wdm_Csp_Customizations/includes
 * @author     WisdmLabs <info@wisdmlabs.com>
 */
class Wdm_Csp_Customizations_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wdm-csp-customizations',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
