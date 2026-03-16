<?php
define('CABLING_SECRET', "35onoi2=-7#%g03kl");
define('CABLING_INVENTORY', "inventory");
define('CABLING_BACKLOG', "backlog");
define('CABLING_SHIPMENT', "shipment");
define('GI_BACKUP_RING', 8641);
define('GI_O_RING', 8627);

//add SVG to allowed file uploads
function add_file_types_to_uploads($file_types)
{

    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg';
    return array_merge($file_types, $new_filetypes);
}

add_action('upload_mimes', 'add_file_types_to_uploads');

function cabling_show_back_btn()
{
    echo '<a href="#" class="backbutton box-shadow">' . __('Back to: ', 'cabling') . '<span class="back-text"></span></a>';
}

function cabling_add_theme_popup()
{
    if (is_user_logged_in()) {
        get_template_part('template-parts/modal/popup', 'create-customer');
    }
    get_template_part('template-parts/loading');
    get_template_part('template-parts/sidebar', 'navigation');
    get_template_part('template-parts/modal/popup', 'customer');
    get_template_part('template-parts/modal/popup', 'email_share');
    get_template_part('template-parts/modal/popup', 'pdf');
    get_template_part('template-parts/modal/popup', 'success');
    get_template_part('template-parts/modal/popup', 'error');
    get_template_part('template-parts/modal/popup', 'errorvalidation');
    get_template_part('template-parts/modal/popup', 'message');
}

add_action('wp_footer', 'cabling_add_theme_popup');

function cabling_get_custom_email_html($link, $heading, $mailer, $type, $content = '')
{
    return wc_get_template_html($type, array(
        'link_verify' => $link,
        'email_heading' => $heading,
        'sent_to_admin' => false,
        'plain_text' => false,
        'email' => $mailer,
        'content' => $content,
    ));

}

// Add product variations ACF rule
add_filter('acf/location/rule_values/post_type', 'acf_location_rule_values_variation');
function acf_location_rule_values_variation($choices)
{
    $choices['product_variation'] = 'Product Variation';
    return $choices;
}

$GLOBALS['wc_loop_variation_id'] = null;

function is_field_group_for_variation($field_group, $variation_data, $variation_post)
{
    return (preg_match('/Variation/i', $field_group['title']) == true);
}

add_action('woocommerce_product_after_variable_attributes', function ($loop_index, $variation_data, $variation_post) {
    $GLOBALS['wc_loop_variation_id'] = $variation_post->ID;

    foreach (acf_get_field_groups() as $field_group) {
        if (is_field_group_for_variation($field_group, $variation_data, $variation_post)) {
            acf_render_fields($variation_post->ID, acf_get_fields($field_group));
        }
    }

    $GLOBALS['wc_loop_variation_id'] = null;
}, 10, 3);

add_filter('acf/prepare_field', function ($field) {
    if (!$GLOBALS['wc_loop_variation_id']) {
        return $field;
    }

    $field['name'] = preg_replace('/^acf\[/', 'acf_variation[' . $GLOBALS['wc_loop_variation_id'] . '][', $field['name']);

    return $field;
}, 10, 1);

function rdv__after__render_field($field)
{
    echo "<script>
            (function($) {
                acf.doAction('append', $('#post'));
            })(jQuery);
          </script>";
}

add_action('acf/render_field/type=repeater', 'rdv__after__render_field', 10, 1);

add_action('woocommerce_save_product_variation', function ($variation_id, $loop_index) {
    if (!isset($_POST['acf_variation'][$variation_id])) {
        return;
    }

    if (!empty($_POST['acf_variation'][$variation_id]) && is_array($fields = $_POST['acf_variation'][$variation_id])) {
        foreach ($fields as $key => $val) {
            update_field($key, $val, $variation_id);
        }
    }

}, 10, 2);

add_filter('acf/validate_post_id', 'caner_custom_option_key', 10, 2);
function caner_custom_option_key($post_id, $_post_id)
{
    if ($_post_id == 'options_language') {
        $post_id = 'options';
    }
    return $post_id;
}

function my_mce_buttons_2($buttons)
{
    /**
     * Add in a core button that's disabled by default
     */
    $buttons[] = 'subscript';
    $buttons[] = 'superscript';

    return $buttons;
}

add_filter('mce_buttons_2', 'my_mce_buttons_2');

function custom_searchwp_query($query)
{
    $post__not_in = get_query_var('post__not_in');
    if ($post__not_in && is_array($post__not_in))
        $query['where'][] = 'swpwcposts.ID NOT IN (' . implode(',', $post__not_in) . ')';

    return $query;
}

add_filter('searchwp\query', 'custom_searchwp_query', 10, 1);

function new_row_list_table($columns)
{
    $columns["title_tag"] = "Title tag";
    return $columns;
}

add_filter('manage_edit-post_columns', 'new_row_list_table');
add_filter('manage_edit-page_columns', 'new_row_list_table');
add_filter('manage_edit-company_news_columns', 'new_row_list_table');
add_filter('manage_edit-company_press_columns', 'new_row_list_table');
add_filter('manage_edit-service_columns', 'new_row_list_table');
add_filter('manage_edit-application_columns', 'new_row_list_table');
add_filter('manage_edit-download_columns', 'new_row_list_table');
add_filter('manage_edit-download-file_columns', 'new_row_list_table');
add_filter('manage_edit-reference_columns', 'new_row_list_table');
add_filter('manage_edit-event_columns', 'new_row_list_table');
add_filter('manage_edit-training_columns', 'new_row_list_table');


function value_new_row_list_table($colname, $cptid)
{
    if ($colname == 'title_tag')
        echo get_field('tag_title', $cptid);
}

add_action('manage_post_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_page_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_company_news_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_company_press_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_service_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_application_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_download_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_download-file_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_reference_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_event_posts_custom_column', 'value_new_row_list_table', 10, 2);
add_action('manage_training_posts_custom_column', 'value_new_row_list_table', 10, 2);

function add_post_object_date($title, $post, $field, $post_id)
{
    $tag = " - [" . get_field('tag_title', $post->ID) . "]";
    if (!empty(get_field('tag_title', $post->ID))) {
        $title = "$title $tag";
    } else {
        $title = "$title";
    }

    return $title;
}

add_filter('acf/fields/post_object/result', 'add_post_object_date', 10, 4);

function send_email_verified_success($user_id)
{
    $user_data = get_user_by('ID', $user_id);

    if ($user_data) {
        $mailer = WC()->mailer();
        $mailer->recipient = $user_data->user_email;
        $type = 'emails/verified-level-2-account.php';
        $blogname = get_bloginfo('name');
        $subject = get_field('subject_email_verified', 'options');
        $subject = str_replace('!!site_name!!', $blogname, $subject);
        $my_account_link = wc_get_account_endpoint_url('dashboard');
        $content = cabling_get_custom_email_html($my_account_link, $subject, $mailer, $type);
        $headers = "Content-Type: text/html\r\n";

        $mailer->send($user_data->user_email, $subject, $content, $headers);
    }
}

function cabling_verify_recaptcha($recaptcha_response = '')
{
    $recaptcha_secret = get_field('gcapcha_secret', 'option');

    $verification_url = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response";
    $verification_response = json_decode(file_get_contents($verification_url));

    return $verification_response->success;
}

/**
 * @param int $post_id
 * @param string $custom_post_type
 * @param string $taxonomy
 * @param string $tag_taxonomy
 * @return array|null
 */
function cabling_get_post_related(int $post_id = 0, string $custom_post_type = 'post', string $taxonomy = 'category', string $tag_taxonomy = 'post_tag'): ?array
{
    $related_args = array(
        'post_type' => $custom_post_type,
        'post__not_in' => array($post_id),
        'tax_query' => array(
            'relation' => 'OR',
        ),
        'posts_per_page' => 4,
        'orderby' => 'rand',
    );
    $terms = get_the_terms($post_id, $taxonomy);
    $tags = get_the_terms($post_id, $tag_taxonomy);

    if ($terms) {
        $term_ids = wp_list_pluck($terms, 'term_id');
        $related_args['tax_query'][] = array(
            'taxonomy' => $taxonomy,
            'field' => 'id',
            'terms' => $term_ids,
        );
    }

    if ($tags) {
        $tag_ids = wp_list_pluck($tags, 'term_id');
        $related_args['tax_query'][] = array(
            'taxonomy' => $tag_taxonomy,
            'field' => 'id',
            'terms' => $tag_ids,
        );
    }

    $related_query = get_posts($related_args);

    return $related_query ?? null;
}


/**
 * @param bool|int $post_id
 * @param string $taxonomy
 * @return string
 */
function getPostCategory(bool|int $post_id, string $taxonomy = 'category'): string
{
    $categories = get_the_terms($post_id, $taxonomy);
    $cat = '';

    if ($categories) {
        $list = array();
        foreach ($categories as $category) {
            $list[] = $category->name;
        }
        $cat = implode(', ', $list);
    }
    return $cat;
}

function custom_pre_get_posts($query): void
{
    if ($query->is_main_query()) {
        if ($query->is_home()) {
            $query->set('category_name', 'blog');
            $query->set('posts_per_page', 6);
            if (isset($_GET['order'])) {
                $order = $_GET['order'] === 'oldest' ? 'asc' : 'desc';
                $query->set('order', $order);
            }
        }
    }
}

add_action('pre_get_posts', 'custom_pre_get_posts');

function get_post_type_name(string $post_type): ?string
{
    return match ($post_type) {
        'post' => __('Blog', 'cabling'),
        'gi_learn' => __('Learn', 'cabling'),
        'company_news' => __('News', 'cabling'),
        'compound' => __('Compounds', 'cabling'),
        default => __('Post', 'cabling'),
    };
}

function get_filter_arg_forums(): array
{
    $args = array();
    if (!empty($_GET['filter'])) {
        switch ($_GET['filter']) {
            case 'alphabetical':
                $args['orderby'] = 'title';
                $args['order'] = 'asc';
                break;
            case 'popular':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_bbp_total_topic_count';
                $args['order'] = 'desc';
                break;
            case 'latest':
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = '_bbp_last_active_time';
                $args['order'] = 'desc';
                break;
            case 'featured':
                $args['meta_key'] = 'is_featured';
                $args['meta_value'] = 'yes';
                break;
        }
    }
    if (!empty($_GET['category'])) {
        $args['p'] = $_GET['category'];
    }

    return $args;
}

function get_compound_product($term_ids): array
{
    $args = array(
        'post_type' => 'compound',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'compound_certification',
                'field' => 'id',
                'terms' => is_array($term_ids) ? $term_ids : [$term_ids],
            )
        ),
    );
    return get_posts($args);
}

/**
 * José Martins 2024-02-14
 * Retrieves post by slug
 */
function get_post_id_by_slug($slug, $post_type = "post")
{
    $query = new WP_Query(
        array(
            'name' => $slug,
            'post_type' => $post_type,
            'numberposts' => 1,
            'fields' => 'ids',
        ));
    $posts = $query->get_posts();
    return array_shift($posts);
}


function contact_form_autofill_data($scanned_tag, $replace)
{
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();

        $phone_code = get_user_meta($current_user->ID, 'billing_phone_code', true);
        $phone = get_user_meta($current_user->ID, 'billing_phone', true);
        $phoneFull = sprintf('+%s%s', $phone_code, remove_zero_number($phone));

        switch ($scanned_tag['name']) {
            case 'first-name':
                $scanned_tag['values'] = [$current_user->first_name];
                break;
            case 'last-name':
                $scanned_tag['values'] = [$current_user->last_name];
                break;
            case 'your-email':
                $scanned_tag['values'] = [$current_user->user_email];
                break;
            case 'your-company-sector':
                $scanned_tag['values'] = [get_user_meta($current_user->ID, 'billing_company', true)];
                break;
            case 'your-phone':
                $scanned_tag['values'] = [$phoneFull];
                break;
            case 'user_telephone':
                $scanned_tag['values'] = [$phone];
                break;
            case 'user_telephone_code':
                $scanned_tag['values'] = [$phone_code];
                break;
        }
    }

    switch ($scanned_tag['name']) {
        case 'function':
            $scanned_tag['raw_values'] = CRMConstant::FUNCTION_CONTACT;
            $scanned_tag['values'] = CRMConstant::FUNCTION_CONTACT;
            break;
        case 'your-product':
            $scanned_tag['raw_values'] = CRMConstant::PRODUCT;
            break;
    }

    return $scanned_tag;
}

add_filter('wpcf7_form_tag', 'contact_form_autofill_data', 10, 2);

add_filter('woocommerce_email_footer_text', '__return_empty_string');

function get_product_group_of_type($product_type_id)
{
    try {
        global $wpdb;

        $product_line_id = $wpdb->get_var($wpdb->prepare("
            SELECT meta_value 
            FROM $wpdb->termmeta 
            WHERE term_id = %d 
            AND meta_key = 'product_line'
        ", $product_type_id));

        $product_group_id = $wpdb->get_var($wpdb->prepare("
            SELECT meta_value 
            FROM $wpdb->termmeta 
            WHERE term_id = %d 
            AND meta_key = 'group_category'
          ", $product_line_id));

        return get_term($product_group_id, 'product_group');
    } catch (Exception $e) {
        return null;
    }
}

function is_backup_ring_group($term_id = 0){
    $backup_ring_id = get_field('backup_ring_id', 'options');

    return $backup_ring_id && $backup_ring_id == $term_id;
}
function showHTMLPriceGuest( $price, $product ) {
	$currencySymbol = get_woocommerce_currency_symbol();
	$price          = $product->get_price();
	if ( empty( $price ) ) {
		return "";
	}
	$price = $price * 100;

	return $currencySymbol . $price;
}
function send_notify_error( $subject, $message, $type = 'sap' ) {
	$default_email       = 'jose.martins@infolabix.com';
	$email_notify_errors = get_field( 'email_notify_errors', 'option' );
	if ( $type == 'sap' ) {
		$email_notify_errors = get_field( 'email_notify_errors_sap', 'option' );
	}
	$emails = [];
	if ( count( $email_notify_errors ) ) {
		foreach ( $email_notify_errors as $email_notify_error ) {
			if ( ! empty( $email_notify_error['email'] ) ) {
				$emails[] = $email_notify_error['email'];
			}
		}
	}
	if ( count( $emails ) ) {
		wp_mail( $emails, $subject, $message );
	} else {
		wp_mail( $default_email, $subject, $message );
	}
}

// GE-252
add_action( 'woocommerce_add_to_cart', 'dw_store_return_to_url_in_session', 10, 6 );
function dw_store_return_to_url_in_session( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
    if ( isset( $_SERVER['REQUEST_URI'] ) ) {
        $scheme_host = wp_parse_url( home_url(), PHP_URL_SCHEME ) . '://' . wp_parse_url( home_url(), PHP_URL_HOST );

        $current_url = esc_url_raw( $scheme_host . wp_unslash( $_SERVER['REQUEST_URI'] ) );

        if ( strpos( $current_url, wc_get_cart_url() ) !== false ) {
            return;
        }

        $home_host = wp_parse_url( home_url(), PHP_URL_HOST );
        $url_host  = wp_parse_url( $current_url, PHP_URL_HOST );

        if ( $url_host === $home_host && WC()->session ) {
            WC()->session->set( 'dw_continue_shopping_url_last', '' );
            WC()->session->set( 'dw_continue_shopping_url', $current_url );
        }
    }
}

// GE-252
function get_query_param_value_gi( $url, $param ) {
    if ( empty( $url ) ) {
        return null;
    }
    $parsed = wp_parse_url( $url );
    if ( empty( $parsed['query'] ) ) {
        return null;
    }
    parse_str( $parsed['query'], $query );
    return isset( $query[$param] ) ? $query[$param] : null;
}
