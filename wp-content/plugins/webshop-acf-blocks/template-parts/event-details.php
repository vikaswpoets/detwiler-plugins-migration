<?php if (empty($event)) return; ?>
<div class="event-card">
    <div class="event-content d-flex event-card-item"
         <?php if ( ! empty( $event['show_modal'] ) ) : ?>
         data-bs-toggle="modal"
         data-bs-target="#event-details-modal"
         data-event-id="<?php echo esc_attr( $event['ID'] ); ?>"
         style="cursor: pointer;
         <?php endif; ?>
        ">
		<?php if ( ! empty( $event['display_date'] ) ) : ?>
			<?php $date = explode( ' ', $event['display_date'] ) ?>
            <div class="event-date d-flex flex-column justify-content-center align-items-center">
                <span><?php echo $date[0] ?? '' ?></span>
                <span><?php echo $date[1] ?? '' ?></span>
            </div>
		<?php endif; ?>
        <div class="event-details">
			<?php if ( ! empty( $event['event_types'] ) ) : ?>
                <div class="event-types">
					<?php foreach ( $event['event_types'] as $type ) : ?>
                        <span class="event-type"><?php echo esc_html( $type->name ); ?></span>
					<?php endforeach; ?>
                </div>
			<?php endif; ?>

            <h3 class="event-title">
                <?php echo esc_html( $event['post_title'] ); ?>
            </h3>

			<?php if ( ! empty( $event['event_location'] ) ) : ?>
                <div class="event-location">
					<?php echo esc_html( $event['event_location'] ); ?>
                </div>
			<?php endif; ?>

			<?php if ( ! empty( $event['display_time'] ) ) : ?>
                <div class="event-time">
					<?php echo esc_html( $event['display_time'] ); ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
