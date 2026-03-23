<?php
/*
GT-77: We need log on activity

Fields:
- created_at: Date (Date created)
- action_name: Lead Type (RFQ/KMI/Contact US/Account Creation)
- action_value: Action (Engagment|Confirmation)
- phase: Phase (When RFQ, if modal open, it’s phase 1. If continue button was clicked is phase 2)
- identifier: Identifier
*/

// Register javascript
function enqueue_log_user_activity_search_script() {
    wp_enqueue_script('sys-log_user_activity', get_template_directory_uri() . '/assets/js/sys-log_user_activity.js', array('jquery'), null, false);
    wp_localize_script('sys-log_user_activity', 'log_user_activity', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_log_user_activity_search_script');

// Using session
function gi_session_init() {
    if (!session_id()) {
        session_start();
    }
}
add_action( 'init', 'gi_session_init' );

// Create table
function wp_create_activity_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'activity_log';

    // Use CREATE TABLE IF NOT EXISTS to create the table only if it doesn't exist
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NULL,
        page_name varchar(255) NULL,
        action_name varchar(255) NULL,
        action_value varchar(255) NULL,
        ip varchar(100) NULL,
        phase varchar(100) NULL,
        identifier mediumint(9) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'wp_create_activity_log_table');


// Add to log activity
function wp_log_user_activity($page_name = '',$action_name = '',$action_value = '',$phase = '',$identifier = 0) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'activity_log';
    $user_id = get_current_user_id() ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $created_at = current_time('mysql');
    $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'page_name' => $page_name,
            'action_name' => $action_name,
            'action_value' => $action_value,
            'ip' => $ip,
            'phase' => $phase,
            'identifier' => $identifier,
            'created_at' => $created_at
        ]
    );
}

// Log page visits
function wp_log_page_visit() {
    if (wp_doing_ajax()) {
        return;
    }
    $page_name = get_the_permalink();
    $page_title = get_the_title();
    $session_key = 'page_visited_' . md5($page_name);
    if (empty($_SESSION[$session_key])) {
        wp_log_user_activity($page_name,'page_visit',$page_title);
        $_SESSION[$session_key] = true;
    }
}
add_action('template_redirect', 'wp_log_page_visit');

// Log button clicks
function save_wp_log_user_activity() {
    $page_name = isset($_POST['page_name']) ? sanitize_text_field($_POST['page_name']) : '';
    $action_name = isset($_POST['action_name']) ? sanitize_text_field($_POST['action_name']) : '';
    $action_value = isset($_POST['action_value']) ? sanitize_text_field($_POST['action_value']) : '';
    $phase = isset($_POST['phase']) ? sanitize_text_field($_POST['phase']) : '';
    $identifier = isset($_POST['identifier']) ? intval($_POST['identifier']) : 0;
    wp_log_user_activity($page_name, $action_name, $action_value, $phase, $identifier);
    wp_send_json_success('Activity logged successfully.');
}
add_action('wp_ajax_save_wp_log_user_activity', 'save_wp_log_user_activity');
add_action('wp_ajax_nopriv_save_wp_log_user_activity', 'save_wp_log_user_activity');

// Manager in admin
// Add sub menu in admin
function wp_register_activity_log_page() {
    add_menu_page(
        'Activity Log',          // Page title
        'Activity Log',          // Menu title
        'manage_options',        // Capability
        'activity-log',          // Menu slug
        'wp_activity_log_page',  // Function to display content
        'dashicons-admin-users', // Icon
        6                        // Position
    );
}
add_action('admin_menu', 'wp_register_activity_log_page');

//Add css js
function wp_enqueue_admin_scripts($hook) {
    // Only enqueue on our custom admin page
    if ($hook != 'toplevel_page_activity-log') {
        return;
    }

    // Enqueue DataTables CSS
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');

    // Enqueue DataTables JS
    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), null, true);

    // Enqueue DataTables Buttons extension CSS and JS
    wp_enqueue_style('datatables-buttons-css', 'https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css');
    wp_enqueue_script('datatables-buttons-js', 'https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js', array('datatables-js'), null, true);
    wp_enqueue_script('jszip-js', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('datatables-buttons-js'), null, true);
    wp_enqueue_script('pdfmake-js', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js', array('datatables-buttons-js'), null, true);
    wp_enqueue_script('vfs_fonts-js', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js', array('pdfmake-js'), null, true);
    wp_enqueue_script('buttons-html5-js', 'https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js', array('jszip-js', 'pdfmake-js'), null, true);
    wp_enqueue_script('buttons-print-js', 'https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js', array('buttons-html5-js'), null, true);

    // Enqueue custom script to initialize DataTables with export buttons
}
add_action('admin_enqueue_scripts', 'wp_enqueue_admin_scripts');


function wp_activity_log_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'activity_log';

    // Get the first day of the current month
    $start_date = date('Y-m-01');

    // Get the date three months ago from the first day of the current month
    $three_months_ago = date('Y-m-d', strtotime('-2 months', strtotime($start_date)));

    // Fetch data from the activity log table within the last 3 months
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE created_at >= %s
        AND action_name != %s
        ",
        $three_months_ago,
        'page_visit'
    ), ARRAY_A);
    ?>
    <div class="wrap">
        <h1>Activity Log</h1>
        <table id="activity-log-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>User ID</th>
                    <!-- <th>Page Name</th> -->
                    <th>Action Name</th>
                    <th>Action Value</th>
                    <th>IP Address</th>
                    <th>Phase</th>
                    <th>Identifier</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row['user_id']); ?></td>
                        <!-- <td><?php echo esc_html($row['page_name']); ?></td> -->
                        <td><?php echo esc_html($row['action_name']); ?></td>
                        <td><?php echo esc_html($row['action_value']); ?></td>
                        <td><?php echo esc_html($row['ip']); ?></td>
                        <td><?php echo esc_html($row['phase']); ?></td>
                        <td><?php echo esc_html($row['identifier']); ?></td>
                        <td><?php echo esc_html($row['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- DataTables Script -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#activity-log-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: 'Activity Log Export'
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Activity Log Export'
                    }
                ]
            });
        });
    </script>
    <?php
}

