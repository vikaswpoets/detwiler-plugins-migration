<?php
/**
 * sealing functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package sealing
 */

define( 'MASTER_ACCOUNT', 'master_account' );
define( 'CHILD_ACCOUNT', 'child_account' );
define( 'LOG_DB_NAME', 'customer_change_logs' );


if ( ! function_exists( 'cabling_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function cabling_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on cabling, use a find and replace
		 * to change 'cabling' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'cabling', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1'           => esc_html__( 'Primary', 'cabling' ),
			'top-header'       => esc_html__( 'Top Header', 'cabling' ),
			'footer-copyright' => esc_html__( 'Footer Copyright', 'cabling' ),
			'footer-link'      => esc_html__( 'Footer Links', 'cabling' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'cabling_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );
	}
endif;
add_action( 'after_setup_theme', 'cabling_setup' );

add_action( 'init', 'start_session', 1 );
function start_session() {
	if ( ! session_id() ) {
		session_start();
	}
}

add_action( 'wp_logout', 'end_session' );
add_action( 'wp_login', 'end_session' );
function end_session() {
	session_destroy();
}

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function cabling_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'cabling_content_width', 640 );
}

add_action( 'after_setup_theme', 'cabling_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function cabling_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'cabling' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'cabling' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Brands', 'cabling' ),
		'id'            => 'footer-brand',
		'description'   => esc_html__( 'Add widgets here.', 'cabling' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Links', 'cabling' ),
		'id'            => 'footer-2',
		'description'   => esc_html__( 'Add widgets here.', 'cabling' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Copyright', 'cabling' ),
		'id'            => 'footer-copyright',
		'description'   => esc_html__( 'Add widgets here.', 'cabling' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Blog Sidebar', 'cabling' ),
		'id'            => 'blog-sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'cabling' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Forum Sidebar', 'cabling' ),
		'id'            => 'forum-sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'cabling' ),
		'before_widget' => '<div id="%1$s" class="header-widget %2$s">',
		'after_widget'  => '</div>',
	) );
}

add_action( 'widgets_init', 'cabling_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function cabling_scripts() {
    $version = '16.03.2026';
	wp_enqueue_style( 'cabling-style', get_stylesheet_uri() );
	wp_enqueue_style( 'flickity', get_template_directory_uri() . '/assets/js/flickity/flickity.min.css', array(), $version );
	wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css' , array(), $version );
	wp_enqueue_style( 'flatpickr', get_template_directory_uri() . '/assets/js/flatpickr/flatpickr.min.css' , array(), $version );
	wp_enqueue_style( 'cabling-font-awesome', get_template_directory_uri() . '/assets/css/Font-Awesome-6.4.0/css/all.css' , array(), $version );
	wp_enqueue_style( 'intlTelInput', get_template_directory_uri() . '/assets/intl-tel-input-17.0.0/css/intlTelInput.min.css' , array(), $version );
	wp_enqueue_style( 'cabling-theme', get_template_directory_uri() . '/assets/css/theme.css' , array(), $version );
	wp_enqueue_style( 'cabling-responsive', get_template_directory_uri() . '/assets/css/responsive.css' , array(), $version );
	wp_enqueue_style( 'dataTables', get_template_directory_uri() . '/assets/css/dataTables.dataTables.min.css' , array(), $version );
	wp_enqueue_style( 'jquery-ui-css', '//code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css' , array(), $version );
	if ( is_page_template( 'templates/register.php' ) ) {
		wp_enqueue_script( 'jquery.validate', get_template_directory_uri() . '/assets/js/jquery.validate.min.js', array(), $version, true );
	}

	wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', array(), $version, true );
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js', array(), $version, true );
	wp_enqueue_script( 'intlTelInput', get_template_directory_uri() . '/assets/intl-tel-input-17.0.0/js/intlTelInput.min.js', array(), $version, true );
	wp_enqueue_script( 'flatpickr', get_template_directory_uri() . '/assets/js/flatpickr/flatpickr.min.js', array(), $version, true );
	wp_enqueue_script( 'flatpickr-rangePlugin', get_template_directory_uri() . '/assets/js/flatpickr/plugins/rangePlugin.js', array(), $version, true );
	wp_enqueue_script( 'flickity', get_template_directory_uri() . '/assets/js/flickity/flickity.pkgd.min.js', array(), $version, true );
	wp_enqueue_script( 'cabling-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), $version, true );
	wp_enqueue_script( 'cabling-webshop', get_template_directory_uri() . '/assets/js/webshop.js', array(), $version, true );
	wp_enqueue_script( 'dataTables', get_template_directory_uri() . '/assets/js/dataTables.min.js', array(), $version, true );

	$cabling_nonce = wp_create_nonce( 'cabling-ajax-nonce' );
	wp_localize_script( 'cabling-theme', 'CABLING', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => $cabling_nonce,
	) );
	wp_localize_script( 'cabling-webshop', 'CABLING', array(
		'ajax_url'      => admin_url( 'admin-ajax.php' ),
		'nonce'         => $cabling_nonce,
		'crm'           => get_the_ID(),
		'recaptcha_key' => get_field( 'gcapcha_sitekey_v2', 'option' ),
		'product_page'  => is_tax( 'product_custom_type' ) ? get_term_link( get_queried_object() ) : home_url( '/products-and-services' ),
	) );

	wp_enqueue_script( 'cabling-skip-link-focus-fix', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	wp_dequeue_script( 'wc-lost-password' );
}

add_action( 'wp_enqueue_scripts', 'cabling_scripts' );


/**
 * Enqueue a script in the WordPress admin, excluding edit.php.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
function cabling_enqueue_admin_script( $hook ) {
	/* if ( 'edit.php' != $hook ) {
		 return;
	 }*/
	wp_enqueue_script( 'cabling-script', get_template_directory_uri() . '/assets/js/admin.js', array(), '1.0' );
}

add_action( 'admin_enqueue_scripts', 'cabling_enqueue_admin_script' );

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
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}


/**
 * Load compatibility file.
 */

require_once get_template_directory() . '/inc/gi-functions.php';
require_once get_template_directory() . '/inc/gi-bbpress.php';
require_once get_template_directory() . '/inc/gi-search.php';
require_once get_template_directory() . '/inc/ajax.php';
require_once get_template_directory() . '/inc/shortcode.php';
require_once get_template_directory() . '/inc/classes/GIWebServices.php';
require_once get_template_directory() . '/inc/classes/GIAccountEndpoints.php';
require_once get_template_directory() . '/inc/classes/CustomerLog.php';

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}

/*
 * Load product function files
 */
require_once get_template_directory() . '/inc/product-filter/filter-helper.php';
require_once get_template_directory() . '/inc/product-filter/ProductsFilterHandler.php';
require_once get_template_directory() . '/inc/product-filter/ProductsFilterHelper.php';
require_once get_template_directory() . '/inc/product/w9.php';
require_once get_template_directory() . '/inc/product/quote.php';
require_once get_template_directory() . '/inc/product/single.php';
require_once get_template_directory() . '/inc/product/cart.php';
require_once get_template_directory() . '/inc/product/checkout.php';

/*
 * Load account functions
 */
require_once get_template_directory() . '/inc/account/UserRegistrationHandler.php';
require_once get_template_directory() . '/inc/gi-customer.php';
require_once get_template_directory() . '/inc/gi-customer-admin.php';

/*
 * Prod-only includes (CPT registration, search logging, variation meta)
 */
if ( file_exists( get_template_directory() . '/inc/icode-custom_postype.php' ) ) {
	require_once get_template_directory() . '/inc/icode-custom_postype.php';
}
if ( file_exists( get_template_directory() . '/inc/SearchLog.php' ) ) {
	require_once get_template_directory() . '/inc/SearchLog.php';
}
if ( file_exists( get_template_directory() . '/inc/variation-meta.php' ) ) {
	require_once get_template_directory() . '/inc/variation-meta.php';
}

if ( file_exists( get_template_directory() . '/inc/ga_crm.php' ) ) {
	require_once get_template_directory() . '/inc/ga_crm.php';
}
if ( file_exists( get_template_directory() . '/inc/dnb.php' ) ) {
	require_once get_template_directory() . '/inc/dnb.php';
}
if ( file_exists( get_template_directory() . '/inc/sap_customer_address.php' ) ) {
	require_once get_template_directory() . '/inc/sap_customer_address.php';
}
if ( file_exists( get_template_directory() . '/inc/sap_create_order.php' ) ) {
	require_once get_template_directory() . '/inc/sap_create_order.php';
}
if ( file_exists( get_template_directory() . '/inc/sap_create_order_on_sap.php' ) ) {
	require_once get_template_directory() . '/inc/sap_create_order_on_sap.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-payment-per-customer-level.php' ) ) {
	require_once get_template_directory() . '/inc/sys-payment-per-customer-level.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-add-extra-info-to-cart.php' ) ) {
	require_once get_template_directory() . '/inc/sys-add-extra-info-to-cart.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-update-cart-from-detail-view-cart.php' ) ) {
	require_once get_template_directory() . '/inc/sys-update-cart-from-detail-view-cart.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-sap-backlog-oder.php' ) ) {
	require_once get_template_directory() . '/inc/sys-sap-backlog-oder.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-filter-image-product-oring.php' ) ) {
	require_once get_template_directory() . '/inc/sys-filter-image-product-oring.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-delivery-only-for-us.php' ) ) {
	require_once get_template_directory() . '/inc/sys-delivery-only-for-us.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-add-shipping-address-when-register.php' ) ) {
	require_once get_template_directory() . '/inc/sys-add-shipping-address-when-register.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-fedex-api.php' ) ) {
	require_once get_template_directory() . '/inc/sys-fedex-api.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-log_user_activity.php' ) ) {
	require_once get_template_directory() . '/inc/sys-log_user_activity.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-add-custom-fields-checkout.php' ) ) {
	require_once get_template_directory() . '/inc/sys-add-custom-fields-checkout.php';
}
if ( file_exists( get_template_directory() . '/inc/sys-sap-requests.php' ) ) {
	require_once get_template_directory() . '/inc/sys-sap-requests.php';
}


add_filter( 'allow_empty_comment', '__return_true' );

add_filter( 'acf/settings/load_json', 'my_acf_json_load_point' );
function my_acf_json_load_point( $paths ) {
	// Remove the default path (optional)
	unset( $paths[0] );

	// Add your custom path
	$paths[] = get_stylesheet_directory() . '/acf-json';

	return $paths;
}

function my_acf_json_save_point( $path ) {
	return get_stylesheet_directory() . '/acf-json';
}

add_filter( 'acf/settings/save_json', 'my_acf_json_save_point' );

//add Google Tag Manager or Google Analytics code to header
function add_google_tag() {
	//$tag_manager_id = 'G-DXNM0L4ME8';
	$tag_manager_id = get_field( 'tag_manager_id', 'option' );
	if ( ! empty( $tag_manager_id ) ) { ?>
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
	} else {
		?>
        <script>
            function gtag(key = '', value = '') {
                //console.log(key, value);
            }
        </script>
		<?php
	}

}

add_action( 'wp_head', 'add_google_tag' );

if ( ! function_exists( 'cabling_site_icon_meta_tags' ) ) :

	add_action( 'wp_head', 'cabling_site_icon_meta_tags' );

	function cabling_site_icon_meta_tags() {
		if ( ! has_site_icon() && ! is_customize_preview() ) {
			return;
		}

		$meta_tags = array();

		if ( $icon_16 = get_site_icon_url( 16 ) ) {
			$meta_tags[] = sprintf( '<link rel="icon" href="%s" sizes="16x16" type="image/png"/>', esc_url( $icon_16 ) );
			$meta_tags[] = sprintf( '<link rel="shortcut icon" href="%s" type="image/png" />', esc_url( $icon_16 ) );
		}

		$meta_tags = apply_filters( 'site_icon_meta_tags', $meta_tags );
		$meta_tags = array_filter( $meta_tags );

		foreach ( $meta_tags as $meta_tag ) {
			echo "$meta_tag\n";
		}
	}
endif;

// Add page id into session brand
function set_brand_id_session() {
	$page_id = 0;
	if ( is_page() ) {
		$page_id = get_queried_object_id();
	}
	if ( ! empty( $_GET['brandid'] ) ) {
		$_GET['brandId'] = $_GET['brandid'];
	}
	if ( ! empty( $_GET['brandId'] ) ) {
		$page_id             = $_GET['brandId'];
		$_SESSION['brandId'] = $_GET['brandId'];
	}
}

add_action( 'wp', 'set_brand_id_session' );

function custom_template_redirect() {
	if ( is_post_type_archive( 'forum' ) ) {
		wp_redirect( home_url() );
		exit;
	}
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'register' ) {
		wp_redirect( home_url() ); // Redirect to home page
		exit;
	}
}

add_action( 'template_redirect', 'custom_template_redirect' );
add_action( 'login_init', 'custom_template_redirect' );

if ( isset( $_GET['test_send'] ) ) {
	send_notify_error( 'test sap', 'test sap', 'sap' );
	send_notify_error( 'test crm', 'test crm', 'crm' );
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
add_action( 'wp_footer', function () {
	if ( ! is_admin() ) {
		ob_end_flush();
	}
}, 100 );

function add_xframe_options_header()
{
    header("X-Frame-Options: SAMEORIGIN");
}

add_action('send_headers', 'add_xframe_options_header');

add_filter('woocommerce_is_checkout', 'gi_woocommerce_is_checkout');
function gi_woocommerce_is_checkout($is_checkout) {
    if (is_wc_endpoint_url('order-received') || is_wc_endpoint_url('order-pay')){
        return true;
    }
    return $is_checkout;
}
