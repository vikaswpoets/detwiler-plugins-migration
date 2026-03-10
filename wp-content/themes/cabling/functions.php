<?php
/**
 * sealing functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package sealing
 */

define('MASTER_ACCOUNT', 'master_account');
define('CHILD_ACCOUNT', 'child_account');
define('LOG_DB_NAME', 'customer_change_logs');


if (!function_exists('cabling_setup')) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function cabling_setup()
    {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on cabling, use a find and replace
         * to change 'cabling' to the name of your theme in all the template files.
         */
        load_theme_textdomain('cabling', get_template_directory() . '/languages');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(array(
            'menu-1' => esc_html__('Primary', 'cabling'),
            'top-header' => esc_html__('Top Header', 'cabling'),
            'footer-copyright' => esc_html__('Footer Copyright', 'cabling'),
            'footer-link' => esc_html__('Footer Links', 'cabling'),
        ));

        /*
         * Switch default core markup for search form, comment form, and comments
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        // Set up the WordPress core custom background feature.
        add_theme_support('custom-background', apply_filters('cabling_custom_background_args', array(
            'default-color' => 'ffffff',
            'default-image' => '',
        )));

        // Add theme support for selective refresh for widgets.
        add_theme_support('customize-selective-refresh-widgets');

        /**
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        add_theme_support('custom-logo', array(
            'height' => 250,
            'width' => 250,
            'flex-width' => true,
            'flex-height' => true,
        ));
    }
endif;
add_action('after_setup_theme', 'cabling_setup');

add_action('init', 'start_session', 1);
function start_session()
{
    if (!session_id()) {
        session_start();
    }
}

add_action('wp_logout', 'end_session');
add_action('wp_login', 'end_session');
function end_session()
{
    session_destroy();
}

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function cabling_content_width()
{
    // This variable is intended to be overruled from themes.
    // Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $GLOBALS['content_width'] = apply_filters('cabling_content_width', 640);
}

add_action('after_setup_theme', 'cabling_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function cabling_widgets_init()
{
    register_sidebar(array(
        'name' => esc_html__('Sidebar', 'cabling'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Add widgets here.', 'cabling'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Footer Brands', 'cabling'),
        'id' => 'footer-brand',
        'description' => esc_html__('Add widgets here.', 'cabling'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Footer Links', 'cabling'),
        'id' => 'footer-2',
        'description' => esc_html__('Add widgets here.', 'cabling'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Footer Copyright', 'cabling'),
        'id' => 'footer-copyright',
        'description' => esc_html__('Add widgets here.', 'cabling'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Blog Sidebar', 'cabling'),
        'id' => 'blog-sidebar',
        'description' => esc_html__('Add widgets here.', 'cabling'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Forum Sidebar', 'cabling'),
        'id' => 'forum-sidebar',
        'description' => esc_html__('Add widgets here.', 'cabling'),
        'before_widget' => '<div id="%1$s" class="header-widget %2$s">',
        'after_widget' => '</div>',
    ));
}

add_action('widgets_init', 'cabling_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function cabling_scripts()
{
	wp_enqueue_style('cabling-style', get_stylesheet_uri());
	wp_enqueue_style('flickity', get_template_directory_uri() . '/assets/js/flickity/flickity.min.css');
	wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css');
	wp_enqueue_style('flatpickr', get_template_directory_uri() . '/assets/js/flatpickr/flatpickr.min.css');
	wp_enqueue_style('cabling-font-awesome', get_template_directory_uri() . '/assets/css/Font-Awesome-6.4.0/css/all.css');
	wp_enqueue_style('intlTelInput', get_template_directory_uri() . '/assets/intl-tel-input-17.0.0/css/intlTelInput.min.css');
	wp_enqueue_style('cabling-theme', get_template_directory_uri() . '/assets/css/theme.css');
	wp_enqueue_style('cabling-responsive', get_template_directory_uri() . '/assets/css/responsive.css');
	wp_enqueue_style('dataTables', get_template_directory_uri() . '/assets/css/dataTables.dataTables.min.css');
	wp_enqueue_style('jquery-ui-css', '//code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css');
	if (is_page_template('templates/register.php')) {
		wp_enqueue_script('jquery.validate', get_template_directory_uri() . '/assets/js/jquery.validate.min.js', array(), null, true);
	}

	wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
	wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js', array(), null, true);
	wp_enqueue_script('intlTelInput', get_template_directory_uri() . '/assets/intl-tel-input-17.0.0/js/intlTelInput.min.js', array(), null, true);
	wp_enqueue_script('flatpickr', get_template_directory_uri() . '/assets/js/flatpickr/flatpickr.min.js', array(), null, true);
	wp_enqueue_script('flatpickr-rangePlugin', get_template_directory_uri() . '/assets/js/flatpickr/plugins/rangePlugin.js', array(), null, true);
	wp_enqueue_script('flickity', get_template_directory_uri() . '/assets/js/flickity/flickity.pkgd.min.js', array(), null, true);
	wp_enqueue_script('cabling-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), null, true);
	wp_enqueue_script('cabling-webshop', get_template_directory_uri() . '/assets/js/webshop.js', array(), null, true);
	//wp_enqueue_script('fancyTable', get_template_directory_uri() . '/assets/js/fancyTable.min.js', array(), null, true);
	wp_enqueue_script('dataTables', get_template_directory_uri() . '/assets/js/dataTables.min.js', array(), null, true);
	//wp_enqueue_script('html2pdf', get_template_directory_uri() . '/assets/js/html2pdf.bundle.min.js', array(), null, true);

	$cabling_nonce = wp_create_nonce('cabling-ajax-nonce');
	wp_localize_script('cabling-theme', 'CABLING', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'   => $cabling_nonce,
	));
	wp_localize_script('cabling-webshop', 'CABLING', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'   => $cabling_nonce,
		'crm' => get_the_ID(),
		'recaptcha_key' => get_field('gcapcha_sitekey_v2', 'option'),
		'product_page'   => is_tax('product_custom_type') ? get_term_link(get_queried_object()) : home_url('/products-and-services'),
	));

	wp_enqueue_script('cabling-skip-link-focus-fix', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.js', array(), '20151215', true);

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
	wp_dequeue_script('wc-lost-password');
}

add_action('wp_enqueue_scripts', 'cabling_scripts');


/**
 * Enqueue a script in the WordPress admin, excluding edit.php.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
function cabling_enqueue_admin_script($hook)
{
    /* if ( 'edit.php' != $hook ) {
         return;
     }*/
    wp_enqueue_script('cabling-script', get_template_directory_uri() . '/assets/js/admin.js', array(), '1.0');
}

add_action('admin_enqueue_scripts', 'cabling_enqueue_admin_script');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
    require get_template_directory() . '/inc/jetpack.php';
}


/**
 * Load Icode compatibility file.
 */

require get_template_directory() . '/inc/icode-functions.php';
require get_template_directory() . '/inc/ajax.php';
require get_template_directory() . '/inc/shortcode.php';
require get_template_directory() . '/inc/GIWebServices.php';

// New directories migrated from dev
require_once get_template_directory() . '/inc/product-filter/ProductsFilterHelper.php';
require_once get_template_directory() . '/inc/product-filter/ProductsFilterHandler.php';
require_once get_template_directory() . '/inc/classes/CustomerLog.php';
require_once get_template_directory() . '/inc/classes/GIAccountEndpoints.php';
require_once get_template_directory() . '/inc/account/UserRegistrationHandler.php';

/**
 * Load WooCommerce compatibility file.
 */
if (class_exists('WooCommerce')) {
    require get_template_directory() . '/inc/woocommerce.php';
}

global $wpdb;

$table_name = $wpdb->prefix . LOG_DB_NAME;

// Check if the table already exists
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        change_by_id mediumint(9) NOT NULL,
        user_id mediumint(9) NOT NULL,
        data text NOT NULL,
        change_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function log_customer_change($user_by, $user_id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . LOG_DB_NAME;

    $wpdb->insert(
        $table_name,
        array(
            'change_by_id' => $user_by,
            'user_id' => $user_id,
            'data' => $data,
            'change_date' => current_time('mysql'),
        )
    );
}

function get_customer_log($user_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . LOG_DB_NAME;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY change_date DESC",
            $user_id
        )
    );
}

function password_change_email_admin($email, $user, $blogname)
{
	$sr_search = array("!!user_name!!");
	$sr_replace = array($user->display_name);
	$newcontent = get_field('message_changepw', 'option');

	$subject = get_field('subject_email_changepw', 'option');
	$sr_searchsubject = array("!!site_name!!");
	$sr_replacesubject = array("Datwyler");

	$email['subject'] = str_replace($sr_searchsubject, $sr_replacesubject, $subject);
	$email['message'] = str_replace($sr_search, $sr_replace, $newcontent);
	return $email;
}

add_filter('wp_password_change_notification_email', 'password_change_email_admin', 10, 3);


function my_new_user_notification_email_admin($wp_new_user_notification_email_admin, $user, $blogname)
{
	$sr_search = array("!!user_name!!", "!!email!!");
	$sr_replace = array($user->display_name, $user->user_email);
	$newcontent = get_field('message_newuser', 'option');

	$subject = get_field('subject_email_newuser', 'option');
	$sr_searchsubject = array("!!site_name!!");
	$sr_replacesubject = array("Datwyler");

	$wp_new_user_notification_email_admin['subject'] = str_replace($sr_searchsubject, $sr_replacesubject, $subject);
	$wp_new_user_notification_email_admin['message'] = str_replace($sr_search, $sr_replace, $newcontent);

	return $wp_new_user_notification_email_admin;
}

add_filter('wp_new_user_notification_email_admin', 'my_new_user_notification_email_admin', 10, 3);

add_filter('allow_empty_comment', '__return_true');

add_action('template_redirect', 'verify_register_code');
function verify_register_code(): void
{
	if (is_page_template('templates/register.php') && isset($_GET['code'])) {
		$data = json_decode(base64_decode($_GET['code']));
		$email = urldecode($data->email);

		if (empty($email) || empty(get_transient($email)) || ($data->code != get_transient($email))) {
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
			get_template_part(404);
			exit();
		}
	}
}
// Force enable SearchWP's alternate indexer.
add_filter('searchwp\indexer\alternate', '__return_true');

add_filter('acf/settings/load_json', 'my_acf_json_load_point');
function my_acf_json_load_point($paths)
{
	// Remove the default path (optional)
	unset($paths[0]);

	// Add your custom path
	$paths[] = get_stylesheet_directory() . '/acf-json';

	return $paths;
}

function my_acf_json_save_point($path)
{
	return get_stylesheet_directory() . '/acf-json';
}
add_filter('acf/settings/save_json', 'my_acf_json_save_point');

//add Google Tag Manager or Google Analytics code to header
function add_google_tag()
{
	//return;
	//$tag_manager_id = 'G-DXNM0L4ME8';
	$tag_manager_id = get_field('tag_manager_id', 'option');
	if (!empty($tag_manager_id)) { ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $tag_manager_id ?>"></script>
        <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', '<?php echo $tag_manager_id ?>');
        </script>
        <?php
	}else{
        ?>
        <script>
            function gtag(key = '',value = ''){
                console.log(key,value);
            }
        </script>
        <?php
    }

}
add_action('wp_head', 'add_google_tag');

if (!function_exists('cabling_site_icon_meta_tags')) :

	add_action('wp_head', 'cabling_site_icon_meta_tags');

	function cabling_site_icon_meta_tags()
	{
		if (!has_site_icon() && !is_customize_preview()) {
			return;
		}

		$meta_tags = array();

		if ($icon_16 = get_site_icon_url(16)) {
			$meta_tags[] = sprintf('<link rel="icon" href="%s" sizes="16x16" type="image/png"/>', esc_url($icon_16));
			$meta_tags[] = sprintf('<link rel="shortcut icon" href="%s" type="image/png" />', esc_url($icon_16));
		}

		$meta_tags = apply_filters('site_icon_meta_tags', $meta_tags);
		$meta_tags = array_filter($meta_tags);

		foreach ($meta_tags as $meta_tag) {
			echo "$meta_tag\n";
		}
	}
endif;

// w9 form ajax
function w9_form_handle_upload($file_info,$user_id,$meta_key = 'user_wp9_form'){
	if ($file_info['error'] === 0) {
		$extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        if ((strtolower($extension) != "pdf")
        //&& (strtolower($extension) != "doc")
        //&& (strtolower($extension) != "docx")
        && (strtolower($extension) != "jpg")
        && (strtolower($extension) != "png")
        && (strtolower($extension) != "gif")
        && (strtolower($extension) != "jpeg")
        )
        {
            return false;
        }

		$milliseconds = floor(microtime(true) * 1000).rand();
        $file_info['name'] = $user_id.'-'.$milliseconds.'-fw9.'.$extension;
		$uploaded_file = wp_handle_upload($file_info, array('test_form' => false));
		if ($uploaded_file && !isset($uploaded_file['error'])) {
			$attachment = array(
				'guid'           => $uploaded_file['url'],
				'post_mime_type' => $uploaded_file['type'],
				'post_title'     => sanitize_file_name($file_info['name']),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);
			$attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);
			$_SESSION['attach_id_cabling'] = $attachment_id;
			update_user_meta($user_id,$meta_key,$attachment_id);
		}
        return true;
    }
    return false;
}
function w9_form_ajax() {
	$file_name                 = $_FILES['formFileW9']['name'];
	$formFileCertificate_name  = $_FILES['formFileCertificate']['name'];
	$user_id = get_current_user_id();
    $customer_level             = get_customer_level($user_id);
    $vat_remove = false;
	if(!empty($file_name)){
		$file_info = $_FILES['formFileW9'];
		if(!w9_form_handle_upload($file_info,$user_id))
        {
            $return = array(
                'error' => "Invalid file type".strtolower( pathinfo($file_info['name'], PATHINFO_EXTENSION))
            );
            wp_send_json($return);
        }
	}
    if(!empty($formFileCertificate_name)){
        $file_info = $_FILES['formFileCertificate'];
		if(!w9_form_handle_upload($file_info,$user_id,'user_certificate_form'))
        {
            $return = array(
                'error' => "Invalid file type".strtolower( pathinfo($file_info['name'], PATHINFO_EXTENSION))
            );
            wp_send_json($return);
        }
	}

    $user_wp9_form          = get_user_meta($user_id,'user_wp9_form',true);
    $user_certificate_form  = get_user_meta($user_id,'user_certificate_form',true);
    $check_remove_tax_by_file = $user_wp9_form && $user_certificate_form ? true : false;

    if(  $customer_level == 2 || $check_remove_tax_by_file ){
        $vat_remove = true;
    }
    $_SESSION['vat_remove'] = $vat_remove;
	$return = array(
	    'success' => $_SESSION['vat_remove']
	);
	wp_send_json($return);
}
add_action( 'wp_ajax_w9_form_ajax', 'w9_form_ajax' );
add_action( 'wp_ajax_nopriv_w9_form_ajax', 'w9_form_ajax' );

// remove vat
function remove_vat_for_specific_users( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
	$user_id = get_current_user_id();
	$user_wp9_form = get_user_meta($user_id,'user_wp9_form',true);
    $user_certificate_form  = get_user_meta($user_id,'user_certificate_form',true);
    $check_remove_tax_by_file = $user_wp9_form && $user_certificate_form ? true : false;
	//JM 20240910 added customer lvl check to remove tax
	if($check_remove_tax_by_file || get_customer_level($user_id) == 2 ){
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$cart_item['data']->set_tax_class( 'zero-rate' );
			// $cart_item['data']->save();
		}
	}
}
add_action( 'woocommerce_before_calculate_totals', 'remove_vat_for_specific_users',99,1 );

// Modify session variable on WooCommerce checkout page
function modify_session_on_woocommerce_checkout() {
    if (is_checkout()) {
        // $_SESSION['vat_remove'] = false;
		// unset($_SESSION['attach_id_cabling']);
    }
}
add_action('template_redirect', 'modify_session_on_woocommerce_checkout');

// w9 file upload ajax: We dont need it any more
/*
function w9_file_upload_ajax() {
	if (!empty($_FILES['file']['name'])) {
        $uploaded_file = wp_handle_upload($_FILES['file'], array('test_form' => false));

        if (isset($uploaded_file['file'])) {
            $file_name_and_location = $uploaded_file['file'];
            $file_title_for_media_library = sanitize_file_name(pathinfo($file_name_and_location, PATHINFO_FILENAME));

            $wp_upload_dir = wp_upload_dir();
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename($file_name_and_location),
                'post_mime_type' => $uploaded_file['type'],
                'post_title' => $file_title_for_media_library,
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $file_name_and_location);
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $attach_data = wp_generate_attachment_metadata($attach_id, $file_name_and_location);
            wp_update_attachment_metadata($attach_id, $attach_data);

			$success = true;
			$_SESSION['attach_id_cabling'] = $attach_id;
        } else {
            $success = false;
        }
    } else {
        $success = false;
    }

	$return = array(
	    'success' => $success
	);

	wp_send_json($return);
}
add_action( 'wp_ajax_w9_file_upload_ajax', 'w9_file_upload_ajax' );
add_action( 'wp_ajax_nopriv_w9_file_upload_ajax', 'w9_file_upload_ajax' );
*/
// attach id cabling order
function attach_id_cabling_order( $order_id, $order ){
    $attach_id_cabling = isset($_SESSION['attach_id_cabling']) ? $_SESSION['attach_id_cabling'] : '';

    if($order_id && !empty($attach_id_cabling)){
        update_post_meta( $order_id, 'attach_id_cabling', $attach_id_cabling );
		if(get_current_user_id()){
			update_user_meta( get_current_user_id(), 'attach_id_cabling', $attach_id_cabling );
		}
		unset($_SESSION['attach_id_cabling']);
    }
}
add_action( 'woocommerce_new_order', 'attach_id_cabling_order', 10, 2 );

// display the file w9 form in the order admin panel
/*
function display_file_w9_order_data_in_admin( $order ){
	$attach_id_cabling = get_post_meta( $order->id, 'attach_id_cabling', true );
	if(!empty($attach_id_cabling)){
		?>
		<div class="order_data_column">
			<h4><?php _e( 'File W9 Form' ); ?></h4>
			<a href="<?php echo wp_get_attachment_url( $attach_id_cabling ); ?>"><?php echo get_the_title( $attach_id_cabling ); ?></a>
		</div>
		<?php
	}
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'display_file_w9_order_data_in_admin' );
*/
// Function to add custom tax to cart
function add_custom_tax_fee() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    $cart = WC()->cart;
	if( is_cart() || is_checkout() ){
		// Remove Handling Fee
		foreach ( $cart->get_fees() as $key => $fee ) {
			if ( $fee->name === __('Minimum Order Fee', 'webstore') ) {
				unset( WC()->cart->fees_api()->get_fees()[ $key ] );
			}
            if ( $fee->name === __('Minimum Order Fee Tax', 'webstore') ) {
				unset( WC()->cart->fees_api()->get_fees()[ $key ] );
			}
		}
	}
	// if( is_cart() || is_checkout() ){
	if( is_checkout() ){
		if( !empty($_SESSION['handling_fee']) ){
			$cart->add_fee(__('Minimum Order Fee', 'webstore'), $_SESSION['handling_fee']);
		}
        if( !empty($_SESSION['handling_fee_tax']) ){
            $user_id = get_current_user_id();
            $user_wp9_form          = get_user_meta($user_id,'user_wp9_form',true);
            $user_certificate_form  = get_user_meta($user_id,'user_certificate_form',true);
            $check_remove_tax_by_file = $user_wp9_form && $user_certificate_form ? true : false;
            $customer_level = get_customer_level($user_id);

            if( $customer_level == 1 && !$check_remove_tax_by_file){
                $cart->add_fee(__('Minimum Order Fee Tax', 'webstore'), $_SESSION['handling_fee_tax']);
            }
		}
	}
    // Remove tax at cart page
    if (is_cart()) {
        WC()->customer->set_is_vat_exempt( true );
    }
    if (is_checkout()) {
        WC()->customer->set_is_vat_exempt( false );
    }
    /*
    if (is_cart()) {
        foreach ($cart->get_tax_totals() as $key => $tax) {
            unset($cart->taxes[$key]);
            unset($cart->tax_totals[$key]);
        }
        $cart->set_session();
    }
    */
	if( is_cart() || is_checkout()){
		$min_price = get_field('min_price','option');
		$handling_fee = 0;
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$sub_subtotal = $cart_item['line_total'];
			if( $sub_subtotal < $min_price ){
				$handling_fee += $min_price - $sub_subtotal;
			}
		}
		if($handling_fee){
			$cart->add_fee(__('Minimum Order Fee', 'webstore'),$handling_fee);
		}
	}
}
add_action('woocommerce_cart_calculate_fees', 'add_custom_tax_fee',999);

// Function to ensure the custom tax is applied to the order at checkout
function add_custom_tax_to_order($cart) {
	$min_price = get_field('min_price','option');
	if($cart->subtotal < $min_price){
		$custom_tax = $min_price - $cart->subtotal ;
		$cart->add_fee(__('Minimum Order Fee', 'webstore'), $custom_tax, true, 'standard');
	}
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_default_address_fields' );
function custom_override_default_address_fields( $fields ) {
    $fields['billing']['billing_postcode']['validate'] = array();
    return $fields;
}
// Add page id into session brand
function set_brand_id_session() {
    $page_id = 0;
    if ( is_page() ) {
        $page_id = get_queried_object_id();
    }
    if( !empty( $_GET['brandid'] ) ){
        $_GET['brandId'] = $_GET['brandid'];
    }
    if( !empty( $_GET['brandId'] ) ){
        $page_id = $_GET['brandId'];
        $_SESSION['brandId']=$_GET['brandId'];
    }
    /*
    if ( $page_id ) {
        $_SESSION['brandId'] = $page_id;
    }
    */
    //print_r('bid:'.$_SESSION['brandId']);
}
add_action('wp', 'set_brand_id_session');

if( file_exists(get_template_directory().'/inc/ga_crm.php') ){
    require_once get_template_directory().'/inc/ga_crm.php';
}
if( file_exists(get_template_directory().'/inc/dnb.php') ){
    require_once get_template_directory().'/inc/dnb.php';
}
if( file_exists(get_template_directory().'/inc/sap_customer_address.php') ){
    require_once get_template_directory().'/inc/sap_customer_address.php';
}
if( file_exists(get_template_directory().'/inc/sap_create_order.php') ){
    require_once get_template_directory().'/inc/sap_create_order.php';
}
if( file_exists(get_template_directory().'/inc/sap_create_order_on_sap.php') ){
    require_once get_template_directory().'/inc/sap_create_order_on_sap.php';
}
if( file_exists(get_template_directory().'/inc/sys-payment-per-customer-level.php') ){
    require_once get_template_directory().'/inc/sys-payment-per-customer-level.php';
}
if( file_exists(get_template_directory().'/inc/sys-add-extra-info-to-cart.php') ){
    require_once get_template_directory().'/inc/sys-add-extra-info-to-cart.php';
}
if( file_exists(get_template_directory().'/inc/sys-update-cart-from-detail-view-cart.php') ){
    require_once get_template_directory().'/inc/sys-update-cart-from-detail-view-cart.php';
}
if( file_exists(get_template_directory().'/inc/sys-sap-backlog-oder.php') ){
    require_once get_template_directory().'/inc/sys-sap-backlog-oder.php';
}
if( file_exists(get_template_directory().'/inc/sys-filter-image-product-oring.php') ){
    require_once get_template_directory().'/inc/sys-filter-image-product-oring.php';
}
if( file_exists(get_template_directory().'/inc/sys-delivery-only-for-us.php') ){
    require_once get_template_directory().'/inc/sys-delivery-only-for-us.php';
}
if( file_exists(get_template_directory().'/inc/sys-add-shipping-address-when-register.php') ){
    require_once get_template_directory().'/inc/sys-add-shipping-address-when-register.php';
}
if( file_exists(get_template_directory().'/inc/sys-fedex-api.php') ){
    require_once get_template_directory().'/inc/sys-fedex-api.php';
}
if( file_exists(get_template_directory().'/inc/sys-log_user_activity.php') ){
    require_once get_template_directory().'/inc/sys-log_user_activity.php';
}
if( file_exists(get_template_directory().'/inc/sys-add-custom-fields-checkout.php') ){
    require_once get_template_directory().'/inc/sys-add-custom-fields-checkout.php';
}
if( file_exists(get_template_directory().'/inc/sys-sap-requests.php') ){
    require_once get_template_directory().'/inc/sys-sap-requests.php';
}

if(!is_user_logged_in())
{
    add_filter('woocommerce_get_price_html', 'showHTMLPriceGuest', 1, 2);
}
function showHTMLPriceGuest($price,$product){
	$currencySymbol = get_woocommerce_currency_symbol();
    $price=$product->get_price();
    if(empty($price))
        {return "";}
    $price=$price*100;
    return $currencySymbol.$price;
}
function send_notify_error($subject, $message,$type = 'sap'){
    $default_email = 'jose.martins@infolabix.com';
    $email_notify_errors = get_field('email_notify_errors','option');
    if($type == 'sap'){
        $email_notify_errors = get_field('email_notify_errors_sap','option');
    }
    $emails = [];
    if( count($email_notify_errors) ){
        foreach($email_notify_errors as $email_notify_error){
            if(!empty($email_notify_error['email'])){
                $emails[] = $email_notify_error['email'];
            }
        }
    }
    if( count($emails) ){
        wp_mail($emails, $subject, $message);
    }else{
        wp_mail($default_email, $subject, $message);
    }
}

function custom_template_redirect() {
    if ( is_post_type_archive('forum') ) {
        wp_redirect(home_url());
        exit;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'register') {
        wp_redirect(home_url()); // Redirect to home page
        exit;
    }
}
add_action('template_redirect', 'custom_template_redirect');
add_action('login_init', 'custom_template_redirect');

if( isset( $_GET['test_send'] ) ){
    send_notify_error('test sap','test sap','sap');
    send_notify_error('test crm','test crm','crm');
    die();
}
function remove_link_modal_title( $buffer ) {
    $buffer = preg_replace( '/<h1 id="link-modal-title">.*?<\/h1>/', '', $buffer );
    return $buffer;
}
function start_output_buffer() {
    if ( ! is_admin() ) {
        ob_start( 'remove_link_modal_title' );
    }
}
add_action( 'wp_footer', 'start_output_buffer', 0 );
add_action( 'wp_footer', function() {
    if ( ! is_admin() ) {
        ob_end_flush();
    }
}, 100 );

//JM Action to display notice when cart qty is updated due to minimum restrictions
add_action('showUpdateQtyNotice','addQtyUpdatedNotice',10,1);
function addQtyUpdatedNotice($productlist){
    $titles=array();
    if ( ! is_array( $productlist ) ) {
        $productlist = array( $productlist => 1 );
    }
    foreach($productlist as $product)
    {
        $titles[]=getCustomProductDisplayName($product);
        //print_r('z>'.getCustomProductDisplayName($product));
    }
    wc_add_notice(__('Minimum purchase quantity for following product/products is changed : ' . wc_format_list_of_items( $titles )
        //implode(',', $titles)
        , 'customer-specific-pricing-for-woocommerce'), 'notice');
}

function getCustomProductDisplayName($product_id)
{
    $dynamic_fields = get_field('product_dynamic_fields', $product_id);
    //o-ring standard
    $o_ring_standard_value = '';
    if (!empty($dynamic_fields)) {
        $o_ring_standard_index = array_search('O-RING SIZE STANDARD', array_column($dynamic_fields, 'label'));
        if ($o_ring_standard_index !== false) {
            $o_ring_standard_value = $dynamic_fields[$o_ring_standard_index]['value'];
        }
    }
    $product_material = get_post_meta($product_id,'product_material',true);
    $product_material = $product_material ? get_post_field( 'post_title', $product_material ) : '';

    $product_dash_number = get_post_meta($product_id,'product_dash_number',true);
    $product_dash_number = str_replace('AS', '', $product_dash_number);
    $product_dash_number = $o_ring_standard_value ? $o_ring_standard_value.'-'.$product_dash_number : $product_dash_number;

    $product_hardness = get_post_meta($product_id,'product_hardness',true);
    $product_sku = get_post_meta($product_id,'_sku',true);

    $custom_meta = array(
        'custom_field_1' => $product_material,
        //'custom_field_2' => $product_sku,
        'custom_field_3' => $product_hardness,
        'custom_field_4' => $product_dash_number,
    );
    $customname= $product_material." ".$product_hardness." ".$product_dash_number;
    return $customname;
}

//JM changed woocimerce filter to display a different product name when adding to cart
add_filter( 'wc_add_to_cart_message_html', 'wc_add_to_cart_message_custom',10,2);
function wc_add_to_cart_message_custom( $message, $products ) {
    $titles = array();
    $count  = 0;
    $show_qty =true;

    if ( ! is_array( $products ) ) {
        $products = array( $products => 1 );
        $show_qty = false;
    }

    if ( ! $show_qty ) {
        $products = array_fill_keys( array_keys( $products ), 1 );
    }

    $product_id = null;
    foreach ( $products as $product_id => $qty ) {
        /* translators: %s: product name */
        //$titles[] = apply_filters( 'woocommerce_add_to_cart_qty_html', ( $qty > 1 ? absint( $qty ) . ' &times; ' : '' ), $product_id ) . apply_filters( 'woocommerce_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce' ), strip_tags( get_the_title( $product_id ) ) ), $product_id );
        $titles[]=getCustomProductDisplayName($product_id);
        $count   += $qty;
    }


    $titles = array_filter( $titles );
    /* translators: %s: product name */
    $added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'woocommerce' ),
        //apply_filters('woocommerce_cart_item_name', $products, json_decode(wc_get_product($product_id)), null));
        wc_format_list_of_items( $titles )
         );
    return $added_text;

};
//JM change woocommerce filter for product display name when removing an item from cart
add_filter( 'woocommerce_cart_item_removed_title', 'removed_from_cart_title', 10, 2);
function removed_from_cart_title( $message, $cart_item ) {
    $message=getCustomProductDisplayName($cart_item['product_id']);
    return $message;
}

/* Redefined in sys SAP requests
add_filter('show_custom_stock_message', 'fn_show_custom_stock_message',10, 1);
function fn_show_custom_stock_message($cart_item){
$sapno = get_user_meta(get_current_user_id(), 'sap_customer', true);
    if ($cart_item['in_stock']){
        return 'In Stock: We estimate to have the products ready for shipping in the next 24 hours.';
    } else {
		if($sapno){ return "Unfortunately we’re out of stock for this item. Please contact your Datwyler Sales Representative, who will be pleased to help you find an alternative solution to meet your requirements.";}
        return 'Out of Stock: We estimate to have the products ready for shipping in the next 10-14 days.';
    }
}
*/

/************* REMOVE SHIPPING FROM CART ***********************/
function disable_shipping_calc_on_cart( $show_shipping ) {
    if( !is_checkout()){// is_cart() ) {
        return false;
    }
    return $show_shipping;
}
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );


add_filter('woocommerce_is_checkout', 'gi_woocommerce_is_checkout');
function gi_woocommerce_is_checkout($is_checkout) {
    if (is_wc_endpoint_url('order-received') || is_wc_endpoint_url('order-pay')){
        return true;
    }
    return $is_checkout;
}
