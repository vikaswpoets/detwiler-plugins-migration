<?php

// Log password changes
function execute_on_profile_password_reset_event( $user, $new_pass ) {
	$data = json_encode( array(
		'user_pass' => true
	) );
	CustomerLog::log( $user->ID, $user->ID, $data );
}

add_action( "password_reset", "execute_on_profile_password_reset_event", 10, 2 );
function execute_on_profile_check_passwords_event( $bool, $user ) {
	$user_id = get_current_user_id() ?: $user['ID'];
	$data    = json_encode( array(
		'user_pass' => true
	) );

	//log_customer_change( $user_id, $user['ID'], $data );

	return $bool;
}

add_filter( "send_password_change_email", "execute_on_profile_check_passwords_event", 10, 2 );

add_filter( 'login_form_middle', 'cabling_login_form_middle' );
function cabling_login_form_middle( $content ) {
	return '<p class="form-group">
				<div class="g-recaptcha" data-sitekey="' . get_field( 'gcapcha_sitekey_v2', 'option' ) . '"></div>
			</p>';
}

function cabling_reset_form_middle() {
	echo '<p class="form-group">
				<div id="recaptcha" class="g-recaptcha" data-sitekey="' . get_field( 'gcapcha_sitekey_v2', 'option' ) . '"></div>
			</p>';
}

add_filter( 'woocommerce_lostpassword_form', 'cabling_reset_form_middle' );
add_action( 'woocommerce_login_form', 'cabling_reset_form_middle' );

function custom_reset_password_redirect( $redirect_to, $request, $user ) {
	// Check if the user has reset their password successfully
	if ( ! is_wp_error( $user ) && isset( $_POST['action'] ) && $_POST['action'] == 'resetpass' ) {

		// Redirect the user to a custom page after a successful password reset
		$redirect_to = wc_get_account_endpoint_url( 'dashboard' );
	}

	return $redirect_to;
}

add_filter( 'login_redirect', 'custom_reset_password_redirect', 10, 3 );

// Adjust the password reset link expiration time to 30 minutes (in seconds)
// // JM 20230913 removed user_id as it was giving an error
function custom_password_reset_expiration( $expiration ) { //, $user_id) {
	return 1800; // 30 minutes in seconds
}

add_filter( 'password_reset_expiration', 'custom_password_reset_expiration', 10, 2 );
function get_verification_user_link( $user_id ): ?string {
	$verification_key = md5( uniqid() );
	update_user_meta( $user_id, 'verification_key', $verification_key );

	return add_query_arg( array(
		'verify-customer' => true,
		'key'             => $verification_key,
		'data'            => $user_id,
	), home_url( '' ) );
}

function get_reset_password_user_link( $user_id ): ?string {
	$user_data = get_user_by( 'ID', $user_id );

	$reset_key           = get_password_reset_key( $user_data );
	$reset_password_link = esc_url( add_query_arg(
		array(
			'key'           => $reset_key,
			'id'            => $user_id,
			'custom_action' => base64_encode( 'verify_customer_cabling' ),
		),
		wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) )
	) );

	return $reset_password_link ?? '';
}

function password_change_email_admin( $email, $user, $blogname ) {
	$sr_search  = array( "!!user_name!!" );
	$sr_replace = array( $user->display_name );
	$newcontent = get_field( 'message_changepw', 'option' );

	$subject           = get_field( 'subject_email_changepw', 'option' );
	$sr_searchsubject  = array( "!!site_name!!" );
	$sr_replacesubject = array( "Datwyler" );

	$email['subject'] = str_replace( $sr_searchsubject, $sr_replacesubject, $subject );
	$email['message'] = str_replace( $sr_search, $sr_replace, $newcontent );

	return $email;
}

add_filter( 'wp_password_change_notification_email', 'password_change_email_admin', 10, 3 );

function my_new_user_notification_email_admin( $wp_new_user_notification_email_admin, $user, $blogname ) {
	$sr_search  = array( "!!user_name!!", "!!email!!" );
	$sr_replace = array( $user->display_name, $user->user_email );
	$newcontent = get_field( 'message_newuser', 'option' );

	$subject           = get_field( 'subject_email_newuser', 'option' );
	$sr_searchsubject  = array( "!!site_name!!" );
	$sr_replacesubject = array( "Datwyler" );

	$wp_new_user_notification_email_admin['subject'] = str_replace( $sr_searchsubject, $sr_replacesubject, $subject );
	$wp_new_user_notification_email_admin['message'] = str_replace( $sr_search, $sr_replace, $newcontent );

	return $wp_new_user_notification_email_admin;
}

add_filter( 'wp_new_user_notification_email_admin', 'my_new_user_notification_email_admin', 10, 3 );

function get_customer_level( $userId ): int {
	$level          = 1;
	$has_approved   = get_user_meta( $userId, 'has_approve', true );
	$customer_level = get_user_meta( $userId, 'customer_level', true );
	$sap_customer   = get_user_meta( $userId, 'sap_customer', true );
	if ( ! empty( $sap_customer ) || ( 'true' == $has_approved || $customer_level === '2' ) ) {
		$level = 2;
	}

	return $level;
}

function get_master_account_id( $userId ): string {
	$customer_parent = get_user_meta( $userId, 'customer_parent', true );

	return $customer_parent ?: $userId;
}

function get_customer_type( $userId ): string {
	$type            = CHILD_ACCOUNT;
	$customer_parent = get_user_meta( $userId, 'customer_parent', true );
	if ( empty( $customer_parent ) ) {
		$type = MASTER_ACCOUNT;
	}

	return $type;
}

function get_customer_type_label( $user_id ): string {
	$customer_type = get_customer_type( $user_id );

	return $customer_type === MASTER_ACCOUNT ? 'Master Account' : 'Child Account';
}

function cabling_get_user_by_customer( $user_id ) {
	$args = array(
		'role'         => 'customer',
		'meta_key'     => 'customer_parent',
		'meta_value'   => $user_id,
		'meta_compare' => '=',
	);

	return get_users( $args );
}

add_filter( 'woocommerce_add_error', 'woocommerce_add_error_callback' );
function woocommerce_add_error_callback( $message ) {
	if ( $message === 'Invalid username or email.' ) {
		$message = __( 'Invalid email. Please try again!', 'woocommerce' );
	}

	return $message;
}

add_action( 'lostpassword_post', 'gi_retrieve_password_callback', 10, 2 );
function gi_retrieve_password_callback( $errors, $user_data ) {
	if ( isset( $user_data->user_login ) ) {
		$key   = sanitize_key( $user_data->user_login . '_limit_password_reset' );
		$limit = get_transient( $key );
		if ( $limit ) {
			$link = esc_url( add_query_arg( array( 'error' => 'request_too_much' ), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) ) );

			wp_redirect( $link );
			exit();
		}
		set_transient( $key, true, 120 );
	}
}

add_filter( 'woocommerce_email_subject_customer_reset_password', 'gi_custom_reset_password_heading' );
add_filter( 'woocommerce_email_heading_customer_reset_password', 'gi_custom_reset_password_heading' );
function gi_custom_reset_password_heading( $title ) {
	$title = __( 'Datwyler Sealing Solutions: Password Reset Request', 'cabling' );

	return $title;
}

// GID-1219
add_action( 'woocommerce_before_my_account', 'remove_session_notices_from_account_page' );
function remove_session_notices_from_account_page() {
	if ( WC()->session ) {
		WC()->session->set( 'wc_notices', array() );
	}
}

add_filter( 'password_hint', function ( $hint ) {
	return __( 'The password should be at least 8 characters long. Use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).' );
} );

add_filter( 'woocommerce_save_account_details_required_fields', 'cabling_custom_edit_account_required_fields' );
function cabling_custom_edit_account_required_fields( $fields ) {
	return array(
		'account_first_name' => __( 'First name', 'woocommerce' ),
		'account_last_name'  => __( 'Last name', 'woocommerce' ),
	);
}

// Save the custom field when the user updates their account details
function save_custom_field_my_account_edit( $user_id ) {
	if ( isset( $_POST['user_title'] ) ) {
		update_user_meta( $user_id, 'user_title', sanitize_text_field( $_POST['user_title'] ) );
	}
	if ( isset( $_POST['job_title'] ) ) {
		update_user_meta( $user_id, 'job_title', sanitize_text_field( $_POST['job_title'] ) );
	}
	if ( isset( $_POST['function'] ) ) {
		update_user_meta( $user_id, 'function', sanitize_text_field( $_POST['function'] ) );
	}
	if ( isset( $_POST['billing_company'] ) ) {
		update_user_meta( $user_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
	}
	if ( isset( $_POST['billing_address_1'] ) ) {
		update_user_meta( $user_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
	}
	if ( isset( $_POST['billing_country'] ) ) {
		update_user_meta( $user_id, 'billing_country', sanitize_text_field( $_POST['billing_country'] ) );
	}
	if ( isset( $_POST['billing_city'] ) ) {
		update_user_meta( $user_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );
	}
	if ( isset( $_POST['billing_postcode'] ) ) {
		update_user_meta( $user_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
	}
	if ( isset( $_POST['billing_state'] ) ) {
		update_user_meta( $user_id, 'billing_state', sanitize_text_field( $_POST['billing_state'] ) );
	}
	if ( isset( $_POST['company_name_responsible'] ) ) {
		update_user_meta( $user_id, 'company_name_responsible', sanitize_text_field( $_POST['company_name_responsible'] ) );
	}
	if ( isset( $_POST['company-sector'] ) ) {
		update_user_meta( $user_id, 'company-sector', sanitize_text_field( $_POST['company-sector'] ) );
	}
	if ( isset( $_POST['billing_vat'] ) ) {
		update_user_meta( $user_id, 'billing_vat', sanitize_text_field( $_POST['billing_vat'] ) );
	}
	if ( isset( $_POST['billing_phone'] ) ) {
		update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
	}
	if ( isset( $_POST['billing_phone_code'] ) ) {
		update_user_meta( $user_id, 'billing_phone_code', sanitize_text_field( $_POST['billing_phone_code'] ) );
	}
	if ( current_user_can( 'administrator' ) && isset( $_POST['sap_customer'] ) ) {
		update_user_meta( $user_id, 'sap_customer', sanitize_text_field( $_POST['sap_customer'] ) );
	}
	if ( isset( $_POST['account_first_name'] ) && isset( $_POST['account_last_name'] ) ) {
		wp_update_user( array(
			'ID'           => $user_id,
			'display_name' => $_POST['account_first_name'] . ' ' . $_POST['account_last_name']
		) );
	}
}

add_action( 'woocommerce_save_account_details', 'save_custom_field_my_account_edit' );
function cabling_password_reset_handle( $user ) {
	$key           = $_POST['reset_key'];
	$verify_cookie = $_COOKIE[ 'verify_customer_cabling_' . $user->ID ];
	if ( $verify_cookie === $key ) {
		//update child user
		update_user_meta( $user->ID, 'has_approve', 'true' );
		update_user_meta( $user->ID, 'customer_level', '2' );
		update_user_meta( $user->ID, 'has_approve_date', current_time( 'mysql' ) );
	}
}

add_action( 'password_reset', 'cabling_password_reset_handle' );
function remove_zero_number($inputString)
{
    if (str_starts_with($inputString, "0")) {
        $inputString = substr($inputString, 1);
    }
    return $inputString;
}

function get_user_telephone_number($user): string
{
    $phone_code = get_user_meta($user, 'user_telephone_code', true);
    $phone = get_user_meta($user, 'user_telephone', true);

    return sprintf('+%s%s', $phone_code, remove_zero_number($phone));
}

function get_user_phone_number($user): string
{
    $phone_code = get_user_meta($user, 'billing_phone_code', true);
    $phone = get_user_meta($user, 'billing_phone', true);

    return sprintf('+%s%s', $phone_code, remove_zero_number($phone));
}
