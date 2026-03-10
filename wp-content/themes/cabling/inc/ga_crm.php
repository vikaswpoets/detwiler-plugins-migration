<?php
function enqueue_custom_gtag_script() {
    wp_enqueue_script( 'custom-gtag-script', get_template_directory_uri() . '/assets/js/custom-gtag.js', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_gtag_script' );

function create_ga_crm_post_type() {
    $labels = array(
        'name'               => 'GA Tracking',
        'singular_name'      => 'GA Tracking',
        'menu_name'          => 'GA Tracking',
        'name_admin_bar'     => 'GA Tracking',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New GA Tracking',
        'new_item'           => 'New GA Tracking',
        'edit_item'          => 'Edit GA Tracking',
        'view_item'          => 'View GA Tracking',
        'all_items'          => 'All GA Tracking',
        'search_items'       => 'Search GA Tracking',
        'parent_item_colon'  => 'Parent GA Tracking:',
        'not_found'          => 'No GA Tracking found.',
        'not_found_in_trash' => 'No GA Tracking found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'menu_icon'          => 'dashicons-businessman',
        'supports'           => array( 'title'),
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'has_archive'        => false,
        'query_var'          => false,
        'rewrite'            => false,
    );

    register_post_type( 'ga_crm', $args );
}
add_action( 'init', 'create_ga_crm_post_type' );

function save_utm_to_database() {
    //JM changed condition to create cookie even if it misses any parameter
    if ( isset( $_GET['utm_source'] ) || isset( $_GET['utm_medium'] ) || isset( $_GET['utm_campaign'] ) || isset( $_GET['utm_term'] ) || isset( $_GET['utm_content'] ) || isset( $_GET['utm_id'] ) ) {
        $utm = array(
            'utm_source'   => sanitize_text_field( $_GET['utm_source'] ),
            'utm_medium'   => sanitize_text_field( $_GET['utm_medium'] ),
            'utm_campaign' => sanitize_text_field( $_GET['utm_campaign'] ),
            'utm_term'     => sanitize_text_field( $_GET['utm_term'] ),
            'utm_content'  => sanitize_text_field( $_GET['utm_content'] ),
            'utm_id'  => sanitize_text_field( @$_GET['utm_id'] ),
        );
        if ( ! isset( $_SESSION['saved_utm'] ) ) {
            $args = array(
                'post_type'    => 'ga_crm',
                'post_status'  => 'publish',
                'post_title'   => 'GA Tracking '.date('Y-m-d H:i:s'),
            );
            $post_id = wp_insert_post( $args );
            //$cookie_expire = time() + (10 * 365 * 24 * 60 * 60);
            $cookie_expire = time() + (10 * 24 * 60 * 60);
            if ( $post_id ) {
                foreach ( $utm as $key => $value ) {
                    // Store into database
                    update_post_meta( $post_id, $key, $value );
                    // Store into cookie
                    setcookie($key, $value, $cookie_expire, "/");
                }
                // Logic to send to CRM

                // Make sure to send 1 time
                $_SESSION['saved_utm'] = true;
            }
        }
    }
}
add_action( 'template_redirect', 'save_utm_to_database' );