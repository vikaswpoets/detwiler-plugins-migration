<?php
function my_custom_login_recaptcha() {
    ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <div class="g-recaptcha" data-sitekey="<?php echo get_field('gcapcha_sitekey_v2', 'option'); ?>" data-size="normal"></div>
    <?php
}
//add_action('login_form', 'my_custom_login_recaptcha');

function verify_recaptcha_response($user, $password) {
    if (isset($_POST['g-recaptcha-response'])) {
        $verify_recaptcha = cabling_verify_recaptcha($_POST['g-recaptcha-response']);
        if(empty($verify_recaptcha)){
            return new WP_Error('recaptcha_error', __('<strong>ERROR</strong>: reCAPTCHA verification failed.'));
        }
    }
    return $user;
}
add_filter('authenticate', 'verify_recaptcha_response', 21, 2);

function my_custom_login_styles() {
    ?>
    <style>
        .login .g-recaptcha {
            transform: scale(0.89);
            transform-origin: 0 0;
        }
        .login .g-recaptcha iframe {
            max-width: 100% !important;
        }
    </style>
    <?php
}
add_action('login_head', 'my_custom_login_styles');
function cabling_modify_user_table($column)
{
    unset($column['posts']);
    $column['sap_customer'] = 'SAP No.';
    $column['customer_level'] = 'Level';
    $column['customer_type'] = 'Account Type';
    $column['has_approve'] = 'Verify';

    return $column;
}

add_filter('manage_users_columns', 'cabling_modify_user_table');

function cabling_modify_user_table_row($val, $column_name, $user_id)
{
    $customer_level = get_customer_level($user_id);
    switch ($column_name) {
        case 'customer_level' :
            return "<strong>$customer_level</strong>";
        case 'customer_type' :
            $customer_type = get_customer_type_label($user_id);

            return "<strong>$customer_type</strong>";
        case 'sap_customer' :
            $sap_no = get_user_meta($user_id, 'sap_customer', true);

            return $sap_no ?? '-';
        case 'has_approve' :
            $has_approve_val = get_user_meta($user_id, 'has_approve', true);

            if ('true' !== $has_approve_val) {
                $content = '<a onclick="cabling_verify_user(this);return false;" data-user="' . $user_id . '" class="cabling_verify button-primary">Verify</a><br>';
            } else {
                $content = '<strong>Verified</strong>';
            }

            return $content;
        default:
    }
    return $val;
}

add_filter('manage_users_custom_column', 'cabling_modify_user_table_row', 10, 3);

/**
 * Searching Meta Data in Admin
 */
add_action('pre_user_query', 'cabling_pre_user_search');
function cabling_pre_user_search($user_search)
{
    global $wpdb;
    if (!isset($_GET['s'])) return;

    $search_array = array("sap_customer", "first_name", "last_name");

    $user_search->query_from .= " INNER JOIN {$wpdb->usermeta} ON {$wpdb->users}.ID={$wpdb->usermeta}.user_id AND (";
    for ($i = 0; $i < count($search_array); $i++) {
        if ($i > 0) $user_search->query_from .= " OR ";
        $user_search->query_from .= "{$wpdb->usermeta}.meta_key='" . $search_array[$i] . "'";
    }
    $user_search->query_from .= ")";
    $custom_where = $wpdb->prepare("{$wpdb->usermeta}.meta_value LIKE '%s'", "%" . $_GET['s'] . "%");
    $user_search->query_where = str_replace('WHERE 1=1 AND (', "WHERE 1=1 AND ({$custom_where} OR ", $user_search->query_where);

}


/**
 * Add a "Password Change Log" section to the user profile.
 *
 * @param WP_User $user The user object.
 */
function add_password_change_log_section($user)
{
    // Check if the current user can edit this user's profile.
    if (current_user_can('edit_user', $user->ID)) {

        // Retrieve password change logs for this user.
        $logs = CustomerLog::get($user->ID);

        if (!empty($logs)) {
            echo '<h3>Password Change Log</h3>';
            echo '<table class="form-table">';
            foreach ($logs as $key => $log) {
                echo '<tr>';
                echo '<th>' . ($key + 1) . '.</th>';
                echo '<td>by UserId ' . $log->change_by_id . ' at ' . esc_html($log->change_date) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}

add_action('show_user_profile', 'add_password_change_log_section');
function custom_user_filter($query)
{
    if (is_admin() && !empty($_GET['customer_level'])) {
        $filter_value = sanitize_text_field($_GET['customer_level']);
        if ($filter_value === '2') {
            $query->set('meta_query', array(
                'relation' => 'OR',
                array(
                    'key' => 'customer_level',
                    'value' => '2',
                    'compare' => '=',
                ),
                array(
                    'key' => 'has_approve',
                    'value' => 'true',
                    'compare' => '=',
                ),
            ));
        } else {
            $query->set('meta_query', array(
                array(
                    'key' => 'customer_level',
                    'value' => '2',
                    'compare' => '!=',
                ),
            ));
        }
    }
}

add_action('pre_get_users', 'custom_user_filter');

add_filter("views_users", function ($view) {
    $level = $_GET['customer_level'] ?? '';
    $view['customer_level_1'] = "<a href='users.php?customer_level=1' class='" . ($level === '1' ? 'current' : '') . "'>Customer Level 1</a>";
    $view['customer_level_2'] = "<a href='users.php?customer_level=2' class='" . ($level === '2' ? 'current' : '') . "''>Customer Level 2</a>";

    return $view;
}, 10, 1);
