<?php
function gi_get_event_details() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'webshop_ajax_nonce')) {
        wp_send_json_error(['message' => __('Security check failed', 'cabling')]);
    }

	$event_id = intval($_POST['event_id']) ?? 0;

    // Get event data
    $event = get_post($event_id);
    if (!$event || $event->post_type !== 'event') {
        wp_send_json_error(['message' => __('Event not found', 'cabling')]);
    }

    // Get event meta
    $event_location = get_post_meta($event_id, 'event_location', true);
    $event_start_time = get_post_meta($event_id, 'event_start_time', true);
    $event_end_time = get_post_meta($event_id, 'event_end_time', true);
    $event_date = get_post_meta($event_id, 'event_date', true);

	$eventData['ID']           = $event->ID;
	$eventData['post_title']   = $event->post_title;
	$eventData['post_excerpt'] = $event->post_excerpt;
	$eventData['post_content'] = $event->post_content;
	$eventData['permalink']    = get_permalink( $event->ID );
	$eventData['event_location']    = $event_location;

	// formatted date
	$eventData['display_date'] = ! empty( $event_date ) ?
		date_i18n( 'd M', strtotime( $event_date ) ) : '';

	// formatted display times
	$eventData['display_time'] = '';
	if ( ! empty( $event_start_time ) ) {
		$eventData['display_time'] = date_i18n( 'h:i a', strtotime( $event_start_time ) );

		if ( ! empty( $event_end_time ) ) {
			$eventData['display_time'] .= ' - ' . date_i18n( 'h:i a', strtotime( $event_end_time ) );
		}
	}

	// Add event types
	$event_type_terms                   = wp_get_post_terms( $event->ID, 'event-type' );
	$eventData['event_types'] = $event_type_terms;

	// Add featured image if any
	$thumbnail_id = get_post_thumbnail_id( $event->ID );
	if ( $thumbnail_id ) {
		$eventData['thumbnail']    = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
		$eventData['thumbnail_id'] = $thumbnail_id;
	} else {
		$eventData['thumbnail']    = '';
		$eventData['thumbnail_id'] = 0;
	}

	ob_start();
	webshop_acf_load_template( 'event-modal-ajax.php', array( 'event' => $eventData ));
	$data = ob_get_clean();

    wp_send_json_success($data);
}
add_action('wp_ajax_gi_get_event_details', 'gi_get_event_details' );
add_action('wp_ajax_nopriv_gi_get_event_details', 'gi_get_event_details' );
