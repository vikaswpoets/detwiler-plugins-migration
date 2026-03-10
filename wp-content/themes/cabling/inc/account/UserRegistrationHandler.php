<?php

/**
 * Handles user registration processes, including account creation, verification, and login.
 */
class UserRegistrationHandler {
	private const DAY_IN_SECONDS = 86400;
	private const REDIRECT_PATH = '/my-account/';
	private const REGISTER_PATH = '/register/';
	private const SCRIPT_HANDLE = 'user-registration-handler';
	private const SCRIPT_VERSION = '1.0.0';

	public function __construct() {
		$this->registerHooks();
	}

	protected function registerHooks(): void {
		add_action( 'template_redirect', [ $this, 'verifyRegistrationCode' ] );

		add_action( 'wp_ajax_nopriv_gi_handle_registration_request', [ $this, 'handleRegistrationRequestCallback' ] );
		add_action( 'wp_ajax_nopriv_gi_handle_new_account_registration', [ $this, 'handleNewAccountRegistrationCallback' ] );
		add_action( 'wp_ajax_gi_handle_new_account_registration', [ $this, 'handleNewAccountRegistrationCallback' ] );
		add_action( 'wp_ajax_gi_get_customer_child_data', [ $this, 'getCustomerChildDataCallback' ] );
		add_action( 'wp_ajax_gi_update_customer_child', [ $this, 'updateCustomerChildCallback' ] );
		add_action( 'wp_ajax_gi_create_customer_child', [ $this, 'createCustomerChildCallback' ] );
		add_action( 'wp_ajax_gi_resend_verify_child_email', [ $this, 'resendVerifyEmailCallback' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
	}

	public function enqueueScripts(): void {
		if ( ! is_page_template( 'templates/register.php' ) && ! is_account_page() ) {
			return;
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			get_template_directory_uri() . '/assets/js/user-registration-handler.js',
			[ 'jquery', 'recaptcha', 'cabling-webshop' ],
			self::SCRIPT_VERSION,
			true
		);

		wp_localize_script(
			self::SCRIPT_HANDLE,
			'accountRegistrationData',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'account_registration_nonce' ),
				'message' => [
					'passwordError' => __( 'Your password must have at least: 8 characters long with at least 1 uppercase and 1 lowercase character, numbers and symbols', 'cabling' ),
				],
			]
		);
	}

	public function verifyRegistrationCode(): void {
		if ( is_page_template( 'templates/register.php' ) && isset( $_GET['verify'] ) ) {
			$data  = json_decode( base64_decode( $_GET['verify'] ) );
			$email = urldecode( $data->email );

			if ( empty( $email ) || empty( get_transient( $email ) ) || ( $data->code != get_transient( $email ) ) ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( 404 );
				exit();
			}
		}
	}

	/**
	 * Handles the callback for processing a user registration request - on my account page.
	 */
	public function handleRegistrationRequestCallback(): void {
		parse_str( $_REQUEST['data'], $data );

		if ( ! $this->verifyRecaptcha( $data['g-recaptcha-response'] ) ) {
			$this->sendErrorResponse( __( 'reCAPTCHA verification failed. Please try again!', 'cabling' ) );
		}

		if ( ! $this->verifyNonce() ) {
			$this->sendErrorResponse( __( 'Security check failed. Please refresh the page and try again.', 'cabling' ) );
		}

		$recipient = $data['register_email'];
		if ( email_exists( $recipient ) ) {
			$this->sendErrorResponse(
				sprintf( __( 'We already have an account registered under %s . Please log in with the password linked to this account.', 'cabling' ),
					$recipient )
			);
		}

		$this->processSendConfirmEmail( $recipient, $data );
	}

	/**
	 * Handles the callback for new account registration.
	 */
	public function handleNewAccountRegistrationCallback(): void {
		parse_str( $_REQUEST['data'], $data );

		if ( ! $this->verifyRecaptcha( $data['g-recaptcha-response'] ) ) {
			$this->sendErrorResponse( __( 'reCAPTCHA verification failed. Please try again!', 'cabling' ) );
		}

		if ( ! $this->verifyNonce() ) {
			$this->sendErrorResponse( __( 'Security check failed. Please refresh the page and try again.', 'cabling' ) );
		}

		$userData   = $this->prepareUserData( $data );
		$customerId = $this->createCustomer( $data, $userData );

		if ( ! $customerId ) {
			$this->sendErrorResponse( __( 'Something went wrong. Please try again!', 'cabling' ) );
		}

		$this->finalizeRegistration( $customerId, $data );
	}

	private function verifyRecaptcha( string $response ): bool {
		return ! empty( cabling_verify_recaptcha( $response ) );
	}

	private function verifyNonce(): bool {
		return check_ajax_referer( 'account_registration_nonce', 'nonce', false );
	}

	private function processSendConfirmEmail( string $recipient, array $data ): void {
		$isRedirectToStep2 = ! empty( $_COOKIE['utm_id'] );
		$registerUrl       = home_url( self::REGISTER_PATH );

		if ( $isRedirectToStep2 ) {
			$redirectLink = add_query_arg( [ 'email' => $recipient ], $registerUrl );
			wp_send_json_success( [ 'redirect' => $redirectLink ] );
		}

		$hash = md5( $recipient . CABLING_SECRET );
		set_transient( $recipient, $hash, self::DAY_IN_SECONDS );
		$_SESSION['register_redirect'] = $data['_wp_http_referer'];
		$arg                           = json_encode( array( 'email' => urldecode($recipient), 'code' => $hash ) );
		$verifyLink                    = add_query_arg( [ 'verify' => base64_encode( $arg ) ], $registerUrl );

		$this->sendConfirmationEmail( $recipient, $verifyLink );
	}

	private function sendConfirmationEmail( string $recipient, string $verifyLink ): void {
		$mailer            = WC()->mailer();
		$mailer->recipient = $recipient;
		$subject           = __( "Datwyler Sealing Solutions: Confirming Your My Account Opening Request", 'cabling' );
		$content           = cabling_get_custom_email_html( $verifyLink, $subject, $mailer, 'emails/confirm_create_account.php' );

		$mailer->send( $recipient, $subject, $content );

		$this->sendSuccessResponse( __( 'Thanks for requesting access to Datwyler My Account. We follow tough standards in how we manage your data at Datwyler. That\'s why you\'ll now receive an e-mail from us to confirm your request. If you don\'t receive a message, please check your junk folder.', 'cabling' ) );
	}

	private function prepareUserData( array $data ): array {
		$userData = array(
			'has_approve'         => 'false',
			'customer_level'      => '1',
			'first_name'          => $data['first-name'],
			'last_name'           => $data['last-name'],
			'billing_first_name'  => $data['first-name'],
			'shipping_first_name' => $data['first-name'],
			'billing_last_name'   => $data['last-name'],
			'shipping_last_name'  => $data['last-name'],
			'billing_phone'       => $data['billing_phone'],
			'billing_phone_code'  => $data['billing_phone_code'],
			'billing_company'     => $data['company-name'],
			'shipping_company'    => $data['company-name'],
			'billing_address_1'   => $data['billing_address_1'],
			'shipping_address_1'  => $data['billing_address_1'],
			'billing_country'     => $data['billing_country'],
			'shipping_country'    => $data['billing_country'],
			'billing_city'        => $data['billing_city'],
			'shipping_city'       => $data['billing_city'],
			'billing_state'       => $data['billing_state'],
			'shipping_state'      => $data['billing_state'],
			'billing_postcode'    => $data['billing_postcode'],
			'shipping_postcode'   => $data['billing_postcode'],
			'function'            => $data['function'],
			'billing_vat'         => $data['billing_vat'],
			'job_title'           => $data['job-title'],
			// JM 20230914
			'display_name'        => $data['first-name'] . ' ' . $data['last-name'],
			'nickname'            => $data['first-name'] . ' ' . $data['last-name'],
		);

		if ( ! empty( $data['existing-customer'] ) && ! empty( $data['client-number'] ) ) {
			$userData['client-number'] = $data['client-number'];
		}

		return $userData;
	}

	private function createCustomer( array $data, array $userData ): int {
		return wc_create_new_customer(
			$data['user_email'],
			$data['user_email'],
			$data['password'],
			[ 'meta_input' => $userData ]
		);
	}

	private function finalizeRegistration( int $customerId, array $data ): void {
		if (empty($data['customer_id'])) {
			$data['customer_id'] = $customerId;
		}
		do_action( 'gi_created_new_customer', $data );
		wp_update_user( [
			'ID'           => $customerId,
			'display_name' => $data['first-name'] . ' ' . $data['last-name']
		] );

		delete_transient( urldecode( $data['user_email'] ) );

		if ( isset( $data['verify-nounce'] ) ) {
			$this->notifyNewCustomerToAdmin( $data['user_email'] );
		}

		$this->loginAndRedirect( $customerId );
	}

	private function notifyNewCustomerToAdmin( string $userEmail ): void {
		$mailer            = WC()->mailer();
		$mailer->recipient = $userEmail;
		$subject           = __( "New account need to verify!", 'cabling' );
		$content           = cabling_get_custom_email_html( '', $subject, $mailer, 'emails/register-verify.php' );

		$mailer->send( get_option( 'admin_email' ), $subject, $content );
	}

	private function loginAndRedirect( int $customerId ): void {
		$user = get_user_by( 'ID', $customerId );
		wp_set_current_user( $customerId, $user->user_login );
		wp_set_auth_cookie( $customerId, true );

		$redirect = ! empty( $_SESSION['register_redirect'] )
			? home_url( $_SESSION['register_redirect'] )
			: home_url( self::REDIRECT_PATH );

		$message = sprintf(
			__( 'Thanks for signing up to My Account – just click on the <a href="%s">link</a> to log in and explore.', 'cabling' ),
			$redirect
		);

		wp_send_json_success( [
			'message'  => $this->formatSuccessMessage( $message ),
			'redirect' => $redirect
		] );
	}

	private function sendErrorResponse( string $message ): void {
		wp_send_json_error( $this->formatErrorMessage( $message ) );
	}

	private function sendSuccessResponse( string $message ): void {
		wp_send_json_success( $this->formatSuccessMessage( $message ) );
	}

	private function formatSuccessMessage( string $message ): string {
		return sprintf(
			'<div class="alert woo-notice alert-success" role="alert"><i class="fa-solid fa-circle-check me-2"></i>%s</div>',
			$message
		);
	}

	private function formatErrorMessage( string $message ): string {
		return sprintf(
			'<div class="alert woo-notice alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>%s</div>',
			$message
		);
	}

	/**
	 * Retrieving and preparing customer child data.
	 */
	public function getCustomerChildDataCallback() {
		if ( ! $this->verifyNonce() ) {
			$this->sendErrorResponse( __( 'Security check failed. Please refresh the page and try again.', 'cabling' ) );
		}
		$customer_email = sanitize_email($_REQUEST['data']);
	    if (!is_email($customer_email)) {
	        $this->sendErrorResponse(__('Invalid email address.', 'cabling'));
	    }

		$customer       = get_user_by( 'email', $customer_email );
		$customer_meta  = get_user_meta( $customer->ID );

		$excluded_keys = [
            'customer_parent',
	        'rich_editing',
	        'show_admin_bar_front',
	        'use_ssl',
	        'verification_key',
	        'ws_capabilities',
	        'ws_user_level',
	        'comment_shortcuts'
	    ];
	    $excluded_keys = array_flip($excluded_keys);

		$data = [];
	    foreach ($customer_meta as $key => $value) {
	        if (isset($excluded_keys[$key])) {
	            continue;
	        }
	        $data[$key] = $value[0];
	    }

		ob_start();
		include_once get_template_directory() . "/woocommerce/myaccount/popup/edit-customer.php";
		$modal_content = ob_get_clean();

		wp_send_json_success( $modal_content );
	}

	/**
	 * Updates child customer meta information and user data based on the provided request data.
	 */
	function updateCustomerChildCallback() {
		if ( ! $this->verifyNonce() ) {
			$this->sendErrorResponse( __( 'Security check failed. Please refresh the page and try again.', 'cabling' ) );
		}
		parse_str( $_REQUEST['data'], $data );

		$customer_id = absint($data['customer_id']);
		try {
	        $meta_fields = [
	            'user_title' => sanitize_text_field($data['user_title'] ?? ''),
	            'first_name' => sanitize_text_field($data['billing_first_name']),
	            'billing_first_name' => sanitize_text_field($data['billing_first_name']),
	            'last_name' => sanitize_text_field($data['billing_last_name']),
	            'billing_last_name' => sanitize_text_field($data['billing_last_name']),
	            'job_title' => sanitize_text_field($data['job_title'] ?? ''),
	            'user_department' => sanitize_text_field($data['user_department'] ?? ''),
	            'user_telephone' => str_replace(' ', '', sanitize_text_field($data['user_telephone'] ?? '')),
	            'user_telephone_code' => sanitize_text_field($data['user_telephone_code'] ?? ''),
	            'billing_phone' => str_replace(' ', '', sanitize_text_field($data['billing_phone'] ?? '')),
	            'billing_phone_code' => sanitize_text_field($data['billing_phone_code'] ?? '')
	        ];

	        foreach ($meta_fields as $key => $value) {
	            update_user_meta($customer_id, $key, $value);
	        }

	        $user_data = [
	            'ID' => $customer_id,
	            'display_name' => sprintf('%s %s',
	                sanitize_text_field($data['billing_first_name']),
	                sanitize_text_field($data['billing_last_name'])
	            )
	        ];

	        $result = wp_update_user($user_data);
	        if (is_wp_error($result)) {
	            $this->sendErrorResponse(__('Something went wrong. Try again!', 'cabling'));
	        }

	        wp_send_json_success($data);
	    } catch (Exception $e) {
	        $this->sendErrorResponse(__('Something went wrong. Try again!', 'cabling'));
	    }
	}

	/**
	 * Create child customer
	 */
	function createCustomerChildCallback(): void
	{
		if ( ! $this->verifyNonce() ) {
			$this->sendErrorResponse( __( 'Security check failed. Please refresh the page and try again.', 'cabling' ) );
		}

	    parse_str($_REQUEST['data'], $data);

	    $err = false;
	    $message = '';
	    if (email_exists($data['user_email'])) {
	        $err = true;
	        $message = '<div class="woocommerce-error woo-notice" role="alert">' . sprintf(__('We already have an account registered under %s . Please log in with the password linked to this  account.', 'cabling'), $data['user_email']) . '</div>';

	    } else {
	        $parent_id = get_current_user_id();
	        $sap_no = get_user_meta($parent_id, 'sap_no', true);
	        $group = get_user_meta($parent_id, 'wcb2b_group', true);
	        $password = wp_generate_password();
	        $user_data = [
	            'customer_parent'      => $parent_id,
	            'customer_level'       => '1',
	            'sap_no'               => $sap_no,
	            'wcb2b_group'          => $group,
	            'has_approve'          => false,
	            'first_name'           => sanitize_text_field( $data['first_name'] ),
	            'last_name'            => sanitize_text_field( $data['last_name'] ),
	            'billing_first_name'   => sanitize_text_field( $data['first_name'] ),
	            'billing_last_name'    => sanitize_text_field( $data['last_name'] ),
	            'billing_phone'        => sanitize_text_field( $data['billing_phone'] ),
	            'billing_phone_code'   => sanitize_text_field( $data['billing_phone_code'] ),
	            'job_title'            => sanitize_text_field( $data['job-title'] ),
	            'user_department'      => sanitize_text_field( $data['user-department'] ),
	            'user_title'           => sanitize_text_field( $data['user-title'] ),
	            'user_telephone'       => sanitize_text_field( $data['user_telephone'] ),
	            'user_telephone_code'  => sanitize_text_field( $data['user_telephone_code'] ),
	            'display_name'         => sanitize_text_field( $data['first_name'] ) . ' ' . sanitize_text_field( $data['last_name'] ),
	            'nickname'             => sanitize_text_field( $data['first_name'] ) . ' ' . sanitize_text_field( $data['last_name'] ),
	        ];

	        $customer_id = wc_create_new_customer($data['user_email'], $data['user_email'], $password, ['meta_input' => $user_data]);

			if ( is_wp_error( $customer_id ) ) {
                $err = true;
	            $message = '<div class="woocommerce-error woo-notice" role="alert">' . __( 'Registration failed. Please try again.', 'cabling' ) . '</div>';
	        } else {
	            $this->sendVerifyEmailToUser( sanitize_email($data['user_email']), $customer_id );
	            $message = '<div class="woocommerce-message woo-notice" role="alert">' . __( 'Registration successful!', 'cabling' ) . '</div>';
	        }
	    }

	    $response = array(
	        'error' => $err,
	        'message' => $message,
	    );
	    wp_send_json($response);
		wp_die();
	}

	function resendVerifyEmailCallback() {
		if ( ! $this->verifyNonce() ) {
			$this->sendErrorResponse( __( 'Security check failed. Please refresh the page and try again.', 'cabling' ) );
		}
		try {
			parse_str( $_REQUEST['data'], $data );

			$user_id    = $_REQUEST['data'];
			$user_email = $_REQUEST['email'];

			$this->sendVerifyEmailToUser( sanitize_email($user_email), $user_id );

			$message = '<div class="woocommerce-message woo-notice" role="alert">' . __( 'Resend successfully!', 'cabling' ) . '</div>';

			wp_send_json_success( $message );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}

	}
	private function sendVerifyEmailToUser($user_email, $customer_id): void
	{
	    $mailer = WC()->mailer();

	    $mailer->recipient = $user_email;

	    $verify_link = get_reset_password_user_link($customer_id);
	    $type = 'emails/verify-child-account.php';
	    $subject = __("Hi! Please verify your account!", 'cabling');
	    $content = cabling_get_custom_email_html($verify_link, $subject, $mailer, $type);
	    $headers = "Content-Type: text/html\r\n";

	    $mailer->send($user_email, $subject, $content, $headers);
	}
}

// Initialize the handler
new UserRegistrationHandler();
