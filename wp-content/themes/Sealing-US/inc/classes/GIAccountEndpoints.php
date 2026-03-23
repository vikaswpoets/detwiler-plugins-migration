<?php

/**
 * Handles WooCommerce My Account endpoints registration and content
 */
class GIAccountEndpoints {
	/**
	 * @var array List of endpoints to register
	 */
	private array $endpoints = [
		'users-management',
		'setting-account',
		'products',
		'quotations',
		'messages',
		'customer-service',
		'sales-backlog',
		'inventory',
		'shipment'
	];

	public function __construct() {
		add_action( 'init', [ $this, 'registerEndpoints' ] );
		add_action( 'woocommerce_after_my_account', [ $this, 'resetPasswordModal' ], 99 );
		add_filter( 'woocommerce_account_menu_items', [ $this, 'accountMenuItems' ], 999, 1 );
		$this->registerEndpointHandlers();
	}

	public function registerEndpoints(): void {
		foreach ( $this->endpoints as $endpoint ) {
			add_rewrite_endpoint( $endpoint, EP_PAGES );
		}
	}

	/**
	 * Register all endpoint content handlers
	 */
	private function registerEndpointHandlers(): void {
		add_action( 'woocommerce_account_users-management_endpoint', [ $this, 'customerEndpointContent' ] );
		add_action( 'woocommerce_account_sales-backlog_endpoint', [ $this, 'backlogEndpointContent' ] );
		add_action( 'woocommerce_account_inventory_endpoint', [ $this, 'inventoryEndpointContent' ] );
		add_action( 'woocommerce_account_shipment_endpoint', [ $this, 'shipmentEndpointContent' ] );
		add_action( 'woocommerce_account_products_endpoint', [ $this, 'productsEndpointContent' ] );
		add_action( 'woocommerce_account_quotations_endpoint', [ $this, 'quotationsEndpointContent' ] );
		add_action( 'woocommerce_account_messages_endpoint', [ $this, 'messagesEndpointContent' ] );
		add_action( 'woocommerce_account_customer-service_endpoint', [ $this, 'customerServiceEndpointContent' ] );
	}

	/**
	 * Users management content
	 */
	public function customerEndpointContent(): void {
		$user_id        = get_current_user_id();
		$customer_level = get_customer_level( $user_id );

		if ( $customer_level === 2 ) {
			wc_get_template( 'myaccount/customer.php' );
		}
	}

	/**
	 * Sales backlog content
	 */
	public function backlogEndpointContent(): void {
		wc_get_template( 'myaccount/sales-backlog.php' );
	}

	/**
	 * Inventory content
	 */
	public function inventoryEndpointContent(): void {
		wc_get_template( 'myaccount/inventory.php' );
	}

	/**
	 * Shipment content
	 */
	public function shipmentEndpointContent(): void {
		wc_get_template( 'myaccount/shipment.php' );
	}

	/**
	 * Products content
	 */
	public function productsEndpointContent(): void {
		wc_get_template( 'myaccount/products.php' );
	}

	/**
	 * Quotations content
	 */
	public function quotationsEndpointContent(): void {
		$user = wp_get_current_user();
		$data = RequestProductQuote::get( [ 'email' => $user->user_email, 'order' => 'desc' ] );
		wc_get_template( 'myaccount/quotations.php', [ 'data' => $data ] );
	}

	/**
	 * Messages content
	 */
	public function messagesEndpointContent(): void {
		wc_get_template( 'myaccount/messages.php' );
	}

	/**
	 * Customer service content
	 */
	public function customerServiceEndpointContent(): void {
		wc_get_template( 'myaccount/customer-service.php' );
	}


	/**
	 * Account menu
	 *
	 */
	public function accountMenuItems() {
		$user_id        = get_current_user_id();
		$customer_level = get_customer_level( $user_id );
		$customer_type  = get_customer_type( $user_id );
		$sap_customer   = get_user_meta( $user_id, 'sap_customer', true );

		$new_items = array(
			'dashboard'       => __( 'Datwyler My Account', 'cabling' ),
			'edit-account'    => __( 'Account Information', 'cabling' ),
			'edit-address'    => __( 'Billing/Shipping Address', 'cabling' ),
			'setting-account' => __( 'Keep Me Informed', 'cabling' ),
		);

		// JM 20230913 restricted menu to master account only
		if ( $customer_level === 2 && $customer_type === MASTER_ACCOUNT ) {
			$new_items['users-management'] = __( 'User Management', 'cabling' );
		}

		if ( ! empty( $sap_customer ) ) {
			$new_items['sales-backlog'] = __( 'Purchase Orders', 'cabling' );
			$new_items['inventory']     = __( 'Inventory, Lead Time and Pricing', 'cabling' );
			$new_items['shipment']      = __( 'Shipments Last 12 Months ', 'cabling' );
		}

		$new_items['orders']          = __( 'My Orders', 'cabling' );
		$new_items['quotations']      = __( 'My Quotes', 'cabling' );
		$new_items['buy-o-rings']     = __( 'Select / Buy O-rings', 'cabling' );
		$new_items['request-a-quote'] = __( 'REQUEST A QUOTE', 'cabling' );
		$new_items['contact-form']    = __( 'Help & Contact', 'cabling' );

		// JM 20230913 added logout button
		$new_items['customer-logout'] = __( 'Log out', 'cabling' );

		return $new_items;
	}

	/**
	 * Add reset password modal
	 */
	public function resetPasswordModal() {
		wc_get_template( 'myaccount/popup/reset-password.php' );
	}
}

new GIAccountEndpoints();
