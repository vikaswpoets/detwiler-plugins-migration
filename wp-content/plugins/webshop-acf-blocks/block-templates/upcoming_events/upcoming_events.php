<?php
/**
 * Upcoming Events Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 */

// Create id attribute allowing for custom "anchor" value
$id = 'upcoming-events-' . $block['id'];
if ( ! empty( $block['anchor'] ) ) {
	$id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values
$className = 'upcoming-events-slider mb-5';
if ( ! empty( $block['className'] ) ) {
	$className .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$className .= ' align' . $block['align'];
}

// Load values and assign defaults
$heading           = get_field( 'heading' ) ?: __( 'Upcoming Events', 'cabling' );
$events_count      = get_field( 'events_count' ) ?: 6;
$event_type_filter = get_field( 'event_type_filter' ) ?: array();

$cache_time = ( $is_preview ) ? 0 : 3600;

// Get events
$events = webshop_get_cached_upcoming_events( $events_count, $event_type_filter, false, $cache_time );

?>

<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>"
     style="background-color: #ececec">
    <div class="container">
		<?php if ( $heading ) : ?>
            <div class="events-heading-container">
                <h2 class="events-heading pre-heading heading-center"><?php echo esc_html( $heading ); ?></h2>
            </div>
		<?php endif; ?>

		<?php if ( ! empty( $events ) ) : ?>
            <div class="events-slider position-relative">
                <div class="main-carousel">
					<?php foreach ( $events as $event ) : ?>
                        <div class="event-slide carousel-cell">
                            <?php webshop_acf_load_template( 'event-details.php', array( 'event' => $event )); ?>
                        </div>
					<?php endforeach; ?>
                </div>
                <!-- Custom navigation buttons -->
                <div class="custom-nav event-nav">
                    <span class="custom-prev-button"><i class="fa-light fa-chevron-left"></i></span>
                    <span class="custom-next-button"><i class="fa-light fa-chevron-right"></i></span>
                </div>
            </div>
		<?php else : ?>
            <div class="no-events-message">
                <p><?php _e( 'No upcoming events scheduled at this time.', 'cabling' ); ?></p>
            </div>
		<?php endif; ?>
    </div>

    <!-- Single Event Modal -->
    <div class="modal fade" id="event-details-modal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: none;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mb-4">
                    <div class="text-center p-4" id="loading-spinner">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden"><?php _e( 'Loading...', 'cabling' ); ?></span>
                        </div>
                    </div>
                    <div id="event-modal-content" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
