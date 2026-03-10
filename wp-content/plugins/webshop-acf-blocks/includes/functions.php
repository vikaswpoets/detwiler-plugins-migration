<?php
/**
 * Get child pages using direct wpdb query
 *
 * @param array $parent_ids Parent page ID
 *
 * @return array Array of child page objects
 */
function webshop_get_child_pages( array $parent_ids ) {

	global $wpdb;

	$placeholders = implode( ',', array_fill( 0, count( $parent_ids ), '%d' ) );
	$query = $wpdb->prepare( "
		SELECT ID, post_title, post_excerpt, post_name, guid, post_date 
		FROM {$wpdb->posts} 
		WHERE ID IN ($placeholders) 
		AND post_type = 'page' 
		AND post_status = 'publish' 
		ORDER BY menu_order ASC, post_title ASC
	", ...$parent_ids );

	$child_pages = $wpdb->get_results( $query );

	if ( ! empty( $child_pages ) ) {
		foreach ( $child_pages as $index => $page ) {
			$child_pages[ $index ]->permalink = get_permalink( $page->ID );

			$thumbnail_id = get_post_thumbnail_id( $page->ID );
			if ( $thumbnail_id ) {
				$child_pages[ $index ]->thumbnail    = wp_get_attachment_image( $thumbnail_id, 'medium' );
				$child_pages[ $index ]->thumbnail_id = $thumbnail_id;
			} else {
				$child_pages[ $index ]->thumbnail    = '';
				$child_pages[ $index ]->thumbnail_id = 0;
			}
		}
	}
	return $child_pages;
}

/**
 * @param int $page_ID
 * @param string $size
 *
 * @return string|null
 */
function get_page_thumbnail( int $page_ID, string $size = 'medium' ): ?string {
	$thumbnail = null;
	$thumbnail_id = get_post_thumbnail_id( $page_ID );
	if ( $thumbnail_id ) {
		$thumbnail =  wp_get_attachment_image( $thumbnail_id, $size );
	}
	return $thumbnail;
}

/**
 * Get upcoming events with transient caching
 *
 * @param int $count Number of events to retrieve
 * @param array $event_types Array of event type term IDs to filter by
 * @param bool $include_past Whether to include past events
 * @param int $cache_time Cache time in seconds (default: 1 hour)
 *
 * @return array Array of event post objects with meta data
 */
function webshop_get_cached_upcoming_events( $count = 6, $event_types = array(), $include_past = false, $cache_time = 3600 ) {

	$cache_key = 'webshop_events_' . $count . '_' . md5( serialize( $event_types ) ) . '_' . ( $include_past ? '1' : '0' );

	$events = get_transient( $cache_key );

	if ( $events ) {
		//return $events;
	}
	global $wpdb;

	$today = date( 'Ymd' );

	$select  = "SELECT 
		p.ID, 
		p.post_title, 
		p.post_excerpt, 
		p.post_content, 
		event_date.meta_value as event_date,
		event_start_time.meta_value as event_start_time,
		event_end_time.meta_value as event_end_time,
		event_location.meta_value as event_location
	";
	$from    = "FROM {$wpdb->posts} p";
	$join    = "";
	$where   = "WHERE p.post_type = 'event' AND p.post_status = 'publish'";
	$orderby = "ORDER BY event_date.meta_value ASC, event_start_time.meta_value ASC";
	$limit   = $wpdb->prepare( "LIMIT %d", $count );

	// Join for event date
	$join .= " LEFT JOIN {$wpdb->postmeta} event_date ON p.ID = event_date.post_id AND event_date.meta_key = 'event_date'";

	// Join for event start time
	$join .= " LEFT JOIN {$wpdb->postmeta} event_start_time ON p.ID = event_start_time.post_id AND event_start_time.meta_key = 'event_start_time'";

	// Join for event location
	$join .= " LEFT JOIN {$wpdb->postmeta} event_location ON p.ID = event_location.post_id AND event_location.meta_key = 'event_location'";

	// Join for event end time
	$join .= " LEFT JOIN {$wpdb->postmeta} event_end_time ON p.ID = event_end_time.post_id AND event_end_time.meta_key = 'event_end_time'";

	// Filter by event date for upcoming events
	if ( ! $include_past ) {
		$where .= $wpdb->prepare( " AND (event_date.meta_value >= %s OR event_date.meta_value IS NULL)", $today );
	}

	// Filter by event types if specified
	if ( ! empty( $event_types ) ) {
		$join         .= " LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id";
		$placeholders = implode( ',', array_fill( 0, count( $event_types ), '%d' ) );
		$where        .= $wpdb->prepare( " AND tr.term_taxonomy_id IN ($placeholders)", $event_types );
	}

	$query = "$select $from $join $where $orderby $limit";

	$events = $wpdb->get_results( $query );

	$eventData = array();
	if ( ! empty( $events ) ) {
		foreach ( $events as $index => $event ) {
			$event_start_time = $event->event_start_time;
			$event_end_time = $event->event_end_time;
			$event_date = $event->event_date;

			$eventData[ $index ]['ID'] = $event->ID;
			$eventData[ $index ]['post_title'] = $event->post_title;
			$eventData[ $index ]['post_excerpt'] = $event->post_excerpt;
			$eventData[ $index ]['post_content'] = $event->post_content;
			$eventData[ $index ]['permalink'] = get_permalink( $event->ID );
			$eventData[ $index ]['show_modal'] = true;

			// formatted date
			$eventData[ $index ]['display_date'] = ! empty( $event_date ) ?
				date_i18n( 'd M', strtotime( $event_date ) ) : '';

			// formatted display times
			$eventData[ $index ]['display_time'] = '';
			if ( ! empty( $event_start_time ) ) {
				$eventData[ $index ]['display_time'] = date_i18n( 'h:i a', strtotime( $event_start_time ) );

				if ( ! empty( $event_end_time ) ) {
					$eventData[ $index ]['display_time'] .= ' - ' . date_i18n( 'h:i a', strtotime( $event_end_time ) );
				}
			}

			// Add event types
			$event_type_terms              = wp_get_post_terms( $event->ID, 'event-type' );
			$eventData[ $index ]['event_types'] = $event_type_terms;

			// Add featured image if any
			$thumbnail_id = get_post_thumbnail_id( $event->ID );
			if ( $thumbnail_id ) {
				$eventData[ $index ]['thumbnail']    = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
				$eventData[ $index ]['thumbnail_id'] = $thumbnail_id;
			} else {
				$eventData[ $index ]['thumbnail']    = '';
				$eventData[ $index ]['thumbnail_id'] = 0;
			}
		}
	}

	// Cache the result
	set_transient( $cache_key, $eventData, $cache_time );


	return $eventData;
}

/**
 * Clear events cache when an event is updated
 */
function webshop_clear_events_cache( $post_id, $post, $update ) {
	// Only process events
	if ( 'event' !== $post->post_type ) {
		return;
	}

	// Clear all event-related transients
	// Get all transients that start with our prefix
	global $wpdb;
	$transients = $wpdb->get_col(
		"SELECT option_name FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_webshop_events_%'"
	);

	// Delete each transient
	foreach ( $transients as $transient ) {
		$transient_name = str_replace( '_transient_', '', $transient );
		delete_transient( $transient_name );
	}
}

add_action( 'save_post', 'webshop_clear_events_cache', 10, 3 );

/**
 * Load a template from the plugin
 *
 * @param string $template_name Template name
 * @param array $args Arguments to pass to the template
 */
function webshop_acf_load_template($template_name, $args = array()) {
    if (!empty($args) && is_array($args)) {
        extract($args);
    }

    $template_path = WEBSHOP_ACF_DIR_PATH . '/template-parts/' . $template_name;

    if (file_exists($template_path)) {
        include $template_path;
    }
}
