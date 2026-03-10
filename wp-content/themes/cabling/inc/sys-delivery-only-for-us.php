<?php
function enqueue_handle_delivery_script() {
    wp_enqueue_script('handle-delivery', get_template_directory_uri() . '/assets/js/handle-delivery.js', array('jquery'), null, true);
    wp_localize_script('handle-delivery', 'handle_delivery', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_handle_delivery_script');