<?php if ( empty( $event ) ) {
	return;
} ?>
<div class="event-wrapper event-ajax d-flex gap-3">
    <div class="event-meta w-50">
	    <?php webshop_acf_load_template( 'event-details.php', array( 'event' => $event ) ); ?>
        <form class="mt-5">
            <div class="mb-3">
                <label for="emailInput" class="form-label hidden">Email address</label>
                <input type="email" class="form-control" id="emailInput" placeholder="<?php _e('Email address', 'cabling') ?>" required>
            </div>
            <div class="form-submit text-center">
                <button type="submit" class="block-button button-blue"><span><?php _e('Register', 'cabling') ?></span></button>
            </div>
        </form>
    </div>
    <div class="event-content w-50">
        <?php echo apply_filters('the_content', $event['post_content']); ?>
    </div>
</div>
