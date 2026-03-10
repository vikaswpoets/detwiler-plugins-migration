<?php
/**
 * AJAX Handler Class
 */

if (!defined('ABSPATH')) {
	exit;
}

class GI_TaxJar_Ajax {

	public function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		// AJAX actions for both logged in and guest users
		add_action('wp_ajax_gi_taxjar_calculate_tax', array($this, 'ajax_calculate_tax'));
		add_action('wp_ajax_nopriv_gi_taxjar_calculate_tax', array($this, 'ajax_calculate_tax'));

		// Admin AJAX actions
		add_action('wp_ajax_gi_taxjar_test_connection', array($this, 'ajax_test_connection'));
	}

	public function ajax_calculate_tax() {
		check_ajax_referer('gi_taxjar_nonce', 'nonce');

		$address_data = isset($_POST['address']) ? $_POST['address'] : array();

		if (empty($address_data['country']) || $address_data['country'] !== 'US') {
			wp_send_json_success(array(
				'tax_amount' => 0,
				'tax_rate' => 0,
				'message' => __('No tax required for this location.', 'gi-taxjar')
			));
			return;
		}

		$options = get_option('gi_taxjar_options', array());
		$api_key = !empty($options['api_key']) ? $options['api_key'] : '';

		if (empty($api_key)) {
			wp_send_json_error(array('message' => __('TaxJar not configured.', 'gi-taxjar')));
			return;
		}

		try {
			// Initialize TaxJar client
			$config = array();
			if (!empty($options['sandbox_mode'])) {
				$config['api_url'] = 'https://api.sandbox.taxjar.com';
			}

			$client = TaxJar\Client::withApiKey($api_key, $config);

			$cart_data = $this->prepare_address_data($address_data);
			$tax_response = $client->taxForOrder($cart_data);

			wp_send_json_success(array(
				'tax_amount' => $tax_response->amount_to_collect ?? 0,
				'tax_rate' => $tax_response->rate ?? 0,
				'has_nexus' => $tax_response->has_nexus ?? false,
				'message' => __('Tax calculated successfully.', 'gi-taxjar')
			));

		} catch (Exception $e) {
			error_log('TaxJar AJAX Error: ' . $e->getMessage());
			wp_send_json_error(array('message' => __('Unable to calculate tax at this time.', 'gi-taxjar')));
		}
	}

	private function prepare_address_data($address_data) {
		$base_location = wc_get_base_location();

		$store_settings = array(
			'from_country' => $base_location['country'],
			'from_zip' => WC()->countries->get_base_postcode(),
			'from_state' => $base_location['state'],
			'from_city' => WC()->countries->get_base_city(),
			'from_street' => WC()->countries->get_base_address()
		);

		$line_items = array();
		if (WC()->cart && !WC()->cart->is_empty()) {
			foreach (WC()->cart->get_cart() as $cart_item) {
				$product = $cart_item['data'];
				$line_items[] = array(
					'id' => $product->get_id(),
					'quantity' => $cart_item['quantity'],
					'product_identifier' => $product->get_sku() ?: $product->get_id(),
					'description' => $product->get_name(),
					'unit_price' => floatval($product->get_price()),
					'discount' => 0,
					'sales_tax' => 0
				);
			}
		}

		return array_merge($store_settings, array(
			'to_country' => $address_data['country'],
			'to_zip' => $address_data['postcode'] ?? '',
			'to_state' => $address_data['state'] ?? '',
			'to_city' => $address_data['city'] ?? '',
			'to_street' => $address_data['address_1'] ?? '',
			'amount' => WC()->cart ? WC()->cart->get_subtotal() : 0,
			'shipping' => WC()->cart ? WC()->cart->get_shipping_total() : 0,
			'line_items' => $line_items
		));
	}

	public function ajax_test_connection() {
		check_ajax_referer('gi_taxjar_test', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('Unauthorized access.', 'gi-taxjar')));
			return;
		}

		$integration = new GI_TaxJar_Integration();
		$result = $integration->test_connection();

		$css_class = $result['success'] ? 'notice-success' : 'notice-error';
		$message = '<div class="notice ' . $css_class . '"><p>' . esc_html($result['message']) . '</p></div>';

		wp_send_json_success(array('message' => $message));
	}
}
