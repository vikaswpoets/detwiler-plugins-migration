<?php


// w9 form ajax
function w9_form_handle_upload( $file_info, $user_id, $meta_key = 'user_wp9_form' ) {
	if ( $file_info['error'] === 0 ) {
		$extension = pathinfo( $file_info['name'], PATHINFO_EXTENSION );
		if ( ( strtolower( $extension ) != "pdf" )
		     //&& (strtolower($extension) != "doc")
		     //&& (strtolower($extension) != "docx")
		     && ( strtolower( $extension ) != "jpg" )
		     && ( strtolower( $extension ) != "png" )
		     && ( strtolower( $extension ) != "gif" )
		     && ( strtolower( $extension ) != "jpeg" )
		) {
			return false;
		}


		$milliseconds      = floor( microtime( true ) * 1000 ) . rand();
		$file_info['name'] = $user_id . '-' . $milliseconds . '-fw9.' . $extension;
		$uploaded_file     = wp_handle_upload( $file_info, array( 'test_form' => false ) );
		if ( $uploaded_file && ! isset( $uploaded_file['error'] ) ) {
			$attachment                    = array(
				'guid'           => $uploaded_file['url'],
				'post_mime_type' => $uploaded_file['type'],
				'post_title'     => sanitize_file_name( $file_info['name'] ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);
			$attachment_id                 = wp_insert_attachment( $attachment, $uploaded_file['file'] );
			$_SESSION['attach_id_cabling'] = $attachment_id;
			update_user_meta( $user_id, $meta_key, $attachment_id );
		}
	}

	return true;
}

function w9_form_ajax() {
	$file_name                = $_FILES['formFileW9']['name'];
	$formFileCertificate_name = $_FILES['formFileCertificate']['name'];
	$user_id                  = get_current_user_id();
	$customer_level           = get_customer_level( $user_id );
	$vat_remove               = false;
	if ( ! empty( $file_name ) ) {
		$file_info = $_FILES['formFileW9'];
		if ( ! w9_form_handle_upload( $file_info, $user_id ) ) {
			$return = array(
				'error' => "Invalid file type" . strtolower( pathinfo( $file_info['name'], PATHINFO_EXTENSION ) )
			);
			wp_send_json( $return );
		}
	}
	if ( ! empty( $formFileCertificate_name ) ) {
		$file_info = $_FILES['formFileCertificate'];
		if ( ! w9_form_handle_upload( $file_info, $user_id, 'user_certificate_form' ) ) {
			$return = array(
				'error' => "Invalid file type" . strtolower( pathinfo( $file_info['name'], PATHINFO_EXTENSION ) )
			);
			wp_send_json( $return );
		}
	}

	$user_wp9_form            = get_user_meta( $user_id, 'user_wp9_form', true );
	$user_certificate_form    = get_user_meta( $user_id, 'user_certificate_form', true );
	$check_remove_tax_by_file = $user_wp9_form && $user_certificate_form ? true : false;

	if ( $customer_level == 2 || $check_remove_tax_by_file ) {
		$vat_remove = true;
	}
	$_SESSION['vat_remove'] = $vat_remove;
	$return                 = array(
		'success' => $_SESSION['vat_remove']
	);
	wp_send_json( $return );
}

add_action( 'wp_ajax_w9_form_ajax', 'w9_form_ajax' );
add_action( 'wp_ajax_nopriv_w9_form_ajax', 'w9_form_ajax' );
