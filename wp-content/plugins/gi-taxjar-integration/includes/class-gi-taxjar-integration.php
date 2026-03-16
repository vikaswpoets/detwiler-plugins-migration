<?php
/**
 * TaxJar Integration Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class GI_TaxJar_Integration {

    private $client;
    private $options;

    public function __construct() {
        $this->options = get_option('gi_taxjar_options', array());

        // Only initialize if enabled and API key is set
        if ($this->is_enabled() && $this->get_api_key()) {
            $this->init_client();
            $this->init_hooks();
        }
    }

    private function is_enabled() {
        return !empty($this->options['enabled']);
    }

    private function get_api_key() {
        return !empty($this->options['api_key']) ? $this->options['api_key'] : '';
    }

    private function is_sandbox() {
        return !empty($this->options['sandbox_mode']);
    }

    private function init_client() {
        try {
            if (class_exists('TaxJar\Client')) {
                $config = array();
                if ($this->is_sandbox()) {
                    $config['api_url'] = 'https://api.sandbox.taxjar.com';
                }

                $this->client = TaxJar\Client::withApiKey($this->get_api_key(), $config);
            }
        } catch (Exception $e) {
            error_log('TaxJar Client Error: ' . $e->getMessage());
        }
    }

    private function init_hooks() {
        if (!$this->client) {
            return;
        }

        // Cart and checkout hooks
        add_action('woocommerce_checkout_create_order', array($this, 'save_tax_data_to_order'));

        // Remove WooCommerce default tax calculation for US
        add_filter('woocommerce_find_rates', array($this, 'maybe_remove_default_tax'), 10, 2);

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Admin order display
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_order_tax_data'));

		add_action('wp_ajax_gi_calculate_tax', array($this, 'gi_calculate_tax_callback'));
    }

	public function gi_calculate_tax_callback() {
		WC()->session->__unset('gi_taxjar_tax_data');

		$address = $_REQUEST['data'];

		$customerAddress = $this->get_customer_address($address);
	    // Don't calculate if the state exists in the ignore list
	    if ($this->is_ignored_state($customerAddress['state'])) {
			$this->remove_existing_tax_fee();
			wp_send_json_error(__( 'Please note that any tax payable on this purchase must be paid by the buyer to the applicable tax authority.', 'cabling' ));
	    }

		$this->calculate_taxes( $customerAddress );

		wp_send_json_success();
	}

    public function calculate_taxes(array $address) {
        if (!$this->should_calculate_taxes()) {
            return;
        }

        $cart_data = $this->prepare_cart_data($address);
        if (!$cart_data) {
            return;
        }

        try {
            $tax_response = $this->client->taxForOrder($cart_data);
			$this->send_log_email(
			    'TaxJar Cart Data Debug',
			    array(
					'cart_data' => $cart_data,
			        'tax_response' => $tax_response
			    )
			);
            if ($tax_response && $tax_response->amount_to_collect > 0) {
                // Store tax data in session
                WC()->session->set('gi_taxjar_tax_data', array(
                    'amount' => $tax_response->amount_to_collect,
                    'rate' => $tax_response->rate,
                    'jurisdictions' => isset($tax_response->jurisdictions) ? (array) $tax_response->jurisdictions : array(),
                    'breakdown' => isset($tax_response->breakdown) ? (array) $tax_response->breakdown : array(),
                    'has_nexus' => $tax_response->has_nexus ?? false
                ));
            } else {
				// Clear session data
                WC()->session->__unset('gi_taxjar_tax_data');
            }
        } catch (Exception $e) {
            error_log('TaxJar Tax Calculation Error: ' . $e->getMessage());
			$this->send_log_email(
			    'TaxJar Tax Calculation Error',
			    array(
			        'Error' => $e->getMessage()
			    )
			);
        }
    }

    private function should_calculate_taxes() {

        // Don't calculate if customer does not upload w9 form
	    $user_id = get_current_user_id();
	    if ( empty( $user_id ) ) {
		    return true;
	    }
	    $user_wp9_form         = get_user_meta( $user_id, 'user_wp9_form', true );
	    $user_certificate_form = get_user_meta( $user_id, 'user_certificate_form', true );

		return empty( $user_wp9_form ) || empty( $user_certificate_form );
    }

    private function prepare_cart_data(array $shippingAddress) {
	    $country  = $shippingAddress['country'];
	    $state    = $shippingAddress['state'];
	    $postcode = $shippingAddress['postcode'];
	    $city     = $shippingAddress['city'];
	    $address  = $shippingAddress['street'];

        // Only calculate for US addresses
        if ($country !== 'US') {
            return false;
        }

        // Need at least state and zip for calculation
        if (empty($state) || empty($postcode)) {
            return false;
        }

        $store_settings = $this->get_store_settings();
        $line_items = $this->get_cart_line_items();

		// add store nexus on destination
	    $store_settings['nexus_addresses'][] = array(
		    'id'      => $state . ' nexus',
		    'country' => $country,
		    'zip'     => $postcode,
		    'state'   => $state,
		    'city'    => $city,
		    'street'  => $address,
	    );

        $order_data = array_merge($store_settings, array(
            'to_country' => $country,
            'to_zip' => $postcode,
            'to_state' => $state,
            'to_city' => ucwords(strtolower($city)),
            'to_street' => ucwords(strtolower($address)),
            'amount' => WC()->cart->get_subtotal(),
            'shipping' => WC()->cart->get_shipping_total(),
            'line_items' => $line_items
        ));

        return $order_data;
    }

    private function get_store_settings() {
        $base_location = wc_get_base_location();
        $store_address = array(
            'from_country' => $base_location['country'],
            'from_zip' => WC()->countries->get_base_postcode(),
            'from_state' => $base_location['state'],
            'from_city' => WC()->countries->get_base_city(),
            'from_street' => WC()->countries->get_base_address()
        );
	    $warehouse_location = $this->get_warehouse_location();
	    if ( ! empty( $warehouse_location['address']['state'] ) && ! empty( $warehouse_location['address']['zip'] && ! empty( $warehouse_location['address']['city'] ) ) ) {
		    $store_address = array(
			    'from_country' => $warehouse_location['address']['country'],
			    'from_zip'     => $warehouse_location['address']['zip'],
			    'from_state'   => $warehouse_location['address']['state'],
			    'from_city'    => $warehouse_location['address']['city'],
			    'from_street'  => $warehouse_location['address']['street']
		    );
	    }

		return $store_address;
    }

    private function get_cart_line_items() {
        $line_items = array();

	    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
		    $product = $cart_item['data'];
		    $line_total = $cart_item['line_total'];
		    $line_subtotal = $cart_item['line_subtotal'];
		    $quantity = $cart_item['quantity'];

		    $line_items[] = array(
			    'id' => $product->get_id(),
			    'quantity' => $quantity,
			    'product_identifier' => $product->get_sku() ?: $product->get_id(),
			    'description' => $product->get_name(),
			    'unit_price' => $quantity > 0 ? floatval($line_total / $quantity) : 0,
			    'discount' => $quantity > 0 ? floatval(($line_subtotal - $line_total) / $quantity) : 0,
			    'sales_tax' => floatval($cart_item['line_tax'])
		    );
	    }

        return $line_items;
    }

    private function remove_existing_tax_fee() {
		$fees = WC()->cart->get_fees();
        foreach ($fees as $fee_key => $fee) {
            if ($fee->name === __('Sales Tax', 'gi-taxjar')) {
				unset( WC()->cart->fees_api()->get_fees()[ $fee_key ] );
            }
        }
		// Remove default WooCommerce taxes
	    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	        $cart_item['data']->set_tax_class( 'zero-rate' );
	    }

		WC()->session->__unset('gi_taxjar_tax_data');
    }

    public function maybe_remove_default_tax($rates, $args) {
        // Remove default WooCommerce tax rates for US when TaxJar is handling it
        if (!empty($args['country']) && $args['country'] === 'US' && WC()->session->get('gi_taxjar_tax_data')) {
            return array();
        }

        return $rates;
    }

    public function save_tax_data_to_order($order) {
        $tax_data = WC()->session->get('gi_taxjar_tax_data');

        if ($tax_data) {
            $order->update_meta_data('_gi_taxjar_tax_amount', $tax_data['amount']);
            $order->update_meta_data('_gi_taxjar_tax_rate', $tax_data['rate']);
            $order->update_meta_data('_gi_taxjar_jurisdictions', $tax_data['jurisdictions']);
            $order->update_meta_data('_gi_taxjar_breakdown', $tax_data['breakdown']);
            $order->update_meta_data('_gi_taxjar_has_nexus', $tax_data['has_nexus']);
            $order->update_meta_data('_gi_taxjar_calculated', true);
        }

        // Clear session data
        WC()->session->__unset('gi_taxjar_tax_data');
    }

    public function display_order_tax_data($order) {
        if ($order->get_meta('_gi_taxjar_calculated')) {
            $tax_amount = $order->get_meta('_gi_taxjar_tax_amount');
            $tax_rate = $order->get_meta('_gi_taxjar_tax_rate');
            $jurisdictions = $order->get_meta('_gi_taxjar_jurisdictions');

            echo '<div class="gi-taxjar-order-data" style="margin-top: 20px;">';
            echo '<h3>' . __('TaxJar Tax Information', 'gi-taxjar') . '</h3>';
            echo '<p><strong>' . __('Tax Amount:', 'gi-taxjar') . '</strong> ' . wc_price($tax_amount) . '</p>';
            echo '<p><strong>' . __('Tax Rate:', 'gi-taxjar') . '</strong> ' . round($tax_rate * 100, 4) . '%</p>';

            if (!empty($jurisdictions)) {
                echo '<p><strong>' . __('Jurisdictions:', 'gi-taxjar') . '</strong></p>';
                echo '<ul>';
                foreach ($jurisdictions as $key => $value) {
                    if (!empty($value)) {
                        echo '<li>' . esc_html(ucfirst(str_replace('_', ' ', $key))) . ': ' . esc_html($value) . '</li>';
                    }
                }
                echo '</ul>';
            }
            echo '</div>';
        }
    }

    public function enqueue_scripts() {
        if (is_checkout()) {
            wp_enqueue_script(
                'gi-taxjar-checkout',
                GI_TAXJAR_PLUGIN_URL . 'assets/js/taxjar-checkout.js',
                array('jquery', 'wc-checkout'),
                GI_TAXJAR_VERSION,
                true
            );

            wp_localize_script('gi-taxjar-checkout', 'gi_taxjar', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gi_taxjar_nonce'),
                'calculating_text' => __('Calculating taxes...', 'gi-taxjar')
            ));
        }
    }

    // Public method for testing connection
    public function test_connection() {
        if (!$this->client) {
            return array(
                'success' => false,
                'message' => __('TaxJar client not initialized. Check your API key.', 'gi-taxjar')
            );
        }

        try {
            // Test with a simple rate lookup
            $response = $this->client->ratesForLocation('90210');

            if ($response) {
                return array(
                    'success' => true,
                    'message' => __('Connection successful! TaxJar API is working.', 'gi-taxjar')
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Connection failed. No response from TaxJar API.', 'gi-taxjar')
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'gi-taxjar'), $e->getMessage())
            );
        }
    }

	private function get_customer_address(array $shippingAddress){
		if (empty($shippingAddress['shipping_state']) && empty($shippingAddress['shipping_postcode'] && empty($shippingAddress['shipping_city']))) {
			$customer = WC()->customer;
			// Determine which address to use
			$country  = $customer->get_shipping_country() ?: $customer->get_billing_country();
			$state    = $customer->get_shipping_state() ?: $customer->get_billing_state();
			$postcode = $customer->get_shipping_postcode() ?: $customer->get_billing_postcode();
			$city     = $customer->get_shipping_city() ?: $customer->get_billing_city();
			$address  = $customer->get_shipping_address() ?: $customer->get_billing_address();
		} else {
			$country  = $shippingAddress['shipping_country'];
			$state    = $shippingAddress['shipping_state'];
			$postcode = $shippingAddress['shipping_postcode'];
			$city     = $shippingAddress['shipping_city'];
			$address  = $shippingAddress['shipping_address_1'];
		}

		return array(
			'country'  => $country,
			'state'    => $state,
			'postcode' => $postcode,
			'city'     => $city,
			'street'   => $address
		);
	}

	private function is_ignored_state($state = ''): bool {
		if (function_exists('has_surface_equipment_on_cart')){
			if (has_surface_equipment_on_cart()){
				$states = get_field( 'tax_nexus_states_doublee', 'option' );
			} else {
				$states = get_field( 'tax_nexus_states_oring', 'option' );
			}
		} else {
			$cart_items = WC()->cart->get_cart();

			if ( empty( $cart_items ) ) {
				return false;
			}

			$cart_item = reset( $cart_items );
			$product   = $cart_item['data'];

			if ( gi_product_has_surface_equipment( $product->get_id() ) ) {
				$states = get_field( 'tax_nexus_states_doublee', 'option' );
			} else {
				$states = get_field( 'tax_nexus_states_oring', 'option' );
			}
		}


		if (!empty($states) && is_array($states)) {
			$state_codes = array_column($states, 'code');
			if (in_array($state, $state_codes)) {
		        return true;
		    }
		}
		return false;
	}

	private function get_warehouse_location() {
		try {
			$cart_items = WC()->cart->get_cart();
			$cart_item  = reset( $cart_items );
			$product    = $cart_item['data'];

			return get_product_warehouse_info( $product->get_id() );
		} catch ( Exception $e ) {
			return null;
		}
	}
	private function send_log_email($subject, $log_data, $to = '') {
	    if (empty($to)) {
	        $to = get_option('admin_email');
	    }
	    $body = '<pre>' . esc_html(print_r($log_data, true)) . '</pre>';
	    $headers = array('Content-Type: text/html; charset=UTF-8');
	    wp_mail($to, $subject, $body, $headers);
	}


}
