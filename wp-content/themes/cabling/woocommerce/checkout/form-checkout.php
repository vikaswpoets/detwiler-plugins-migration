<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be Sign in to checkout.', 'woocommerce'));
    return;
}
$user_id = get_current_user_id();
if (!$user_id) return;

$step = !empty($_GET['step']) ? $_GET['step'] : 'shipping';

$customer_level = get_customer_level($user_id);
$user_wp9_form = get_user_meta($user_id, 'user_wp9_form', true);
$user_certificate_form  = get_user_meta($user_id,'user_certificate_form',true);
$check_remove_tax_by_file = $user_wp9_form && $user_certificate_form ? true : false;

$transportation_companies = get_field('transportation_companies', 'option');
$user_plant = get_user_meta($user_id, 'sales_org', true);
$user_plant = $user_plant ? $user_plant : 2141;

$carriers = [];
foreach ($transportation_companies as $transportation_companie) {
    $carriers[$transportation_companie['transportation']['carrier_id']] = $transportation_companie['transportation']['carrier_name'];
}

$fedex_method = '';
$free_shipping = '';
$package = WC()->shipping->get_packages();
if ($package) {
    foreach ($package as $key => $pkg) {
        foreach ($pkg['rates'] as $rate_key => $rate) {
            $rate_id = $rate->get_id();

            if (strpos($rate_id, 'wf_fedex_woocommerce_shipping') !== false) {
                $fedex_method = $rate_id;
            } else {
                $free_shipping = $rate_id;
            }

            if (strpos($rate_id, 'free_shipping') !== false) {
                $free_shipping = $rate_id;
            }
        }
    }
}
WC()->session->set('chosen_shipping_methods', array($fedex_method));

$customer_id = $user_id;
$address_type = "shipping";
$addresses = THMAF_Utils::get_custom_addresses($customer_id, $address_type);
$firstIndex = '';
if (is_array($addresses) && count($addresses)) {
    $firstIndex = @array_keys($addresses)[0];
}
$default_shipping = $firstIndex;
$custom_address = get_user_meta($customer_id, THMAF_Utils::ADDRESS_KEY);
if (is_array($custom_address) && count($custom_address)) {
    foreach ($custom_address as $custom_addres) {
        if (isset($custom_addres['default_shipping'])) {
            $default_shipping = $custom_addres['default_shipping'];
        }
    }
}
$customer_level = get_customer_level($customer_id);
$default_shipping_addrerss = isset($addresses[$default_shipping]) ? $addresses[$default_shipping] : [];
$shipping_country = isset($default_shipping_addrerss['shipping_country']) ? $default_shipping_addrerss['shipping_country'] : '';
$can_continue = $shipping_country == 'US' ? true : false;
?>
<script>
    // Ref GID-1050 - Handle carrier
    var fedex_method = '<?= $fedex_method; ?>';
    var free_shipping = '<?= $free_shipping; ?>';
    localStorage.setItem('cabling_get_api_ajax_checkout',0);
</script>
<style>
    select.form-select {
        border-width: 1px !important;
        border-color: #ccc;
        box-shadow: unset;
        border-radius: 4px;
        width: 100%;
    }

    .ml-2 {
        margin-left: 10px;
    }

    .multisteps-form__panel {
        height: 0;
        opacity: 1;
        visibility: visible;
        overflow: hidden;
    }

    <?php if($customer_level == 2): ?>
    div#accordionAddress {
        display: none;
    }

    <?php endif;?>
</style>
<form name="checkout" method="post" class="checkout woocommerce-checkout"
      action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <?php if ($checkout->get_checkout_fields()) : ?>

        <?php do_action('woocommerce_checkout_before_customer_details'); ?>
        <div class="multisteps-form">
            <!--progress bar-->
            <div class="row">
                <div class="col-12">
                    <div class="multisteps-form__progress">
                        <div id="shipping-step-progress"
                             class="multisteps-form__progress-btn <?= $step == 'shipping' ? 'js-active' : ''; ?>"
                             type="button" title="<?php _e('Shipping Details', 'cabling') ?>">
                            <span><?php _e('Delivery Details', 'cabling') ?></span>
                            <p class="note text-danger"><?php _e('Please note: Delivery only available to the USA', 'cabling') ?></p>
                        </div>
                        <div id="carrier-step-progress"
                             class="multisteps-form__progress-btn <?= $step == 'carrier' ? 'js-active' : ''; ?>"
                             type="button"
                             title="<?php _e('Shipping', 'cabling') ?>"><?php _e('Shipping', 'cabling') ?></div>
                        <div id="billing-step-progress"
                             class="multisteps-form__progress-btn <?= $step == 'billing' ? 'js-active' : ''; ?>"
                             type="button" title="<?php _e('Billing', 'cabling') ?>">
                            <span><?php _e('Billing', 'cabling') ?></span>
                            <p class="note text-danger"><?php _e('Please note: Delivery only available to the USA', 'cabling') ?></p>
                        </div>
                        <?php if ($customer_level == 1 && !$check_remove_tax_by_file): ?>
                            <div id="user_wp9_form-step-progress"
                                 class="multisteps-form__progress-btn <?= $step == 'user_wp9_form' ? 'js-active' : ''; ?>"
                                 type="button"
                                 title="<?php _e('W9 Form and Resale Certificate', 'cabling') ?>"><span><?php _e('W9 Form and Resale Certificate', 'cabling') ?></span>
                                <p class="note text-danger">To complete your purchase, you’ll need to upload your company’s resale certificate and W-9. This ensures that there is no tax assessed on your order.</p></div>
                        <?php endif; ?>
                        <div id="order_review-step-progress"
                             class="multisteps-form__progress-btn <?= $step == 'order_review' ? 'js-active' : ''; ?>"
                             type="button"
                             title="<?php _e('Order Summary', 'cabling') ?>"><?php _e('Order Summary', 'cabling') ?></div>
                    </div>
                </div>
            </div>
            <!--form panels-->
            <div class="row">
                <div class="col-12">
                    <div class="multisteps-form__form" id="customer_details">
                        <!--single form panel-->
                        <div id="shipping-step"
                             class="multisteps-form__panel <?= $step == 'shipping' ? 'js-active' : ''; ?>"
                             data-animation="scaleIn">
                            <div class="multisteps-form__content shipping-address-content">
                                <div class="row">
                                    <div class="col-12">
                                        <?php if ($customer_level == 1): ?>
                                            <a class="add-new-address-checkout"
                                               onclick="thmaf_add_new_shipping_address(event, this,'shipping')">Add a
                                                new
                                                address</a>
                                        <?php endif; ?>
                                        <?php do_action('woocommerce_checkout_shipping'); ?>
                                        <?php wc_get_template_part('checkout/deliver', 'detail'); ?>
                                    </div>
                                </div>
                                <div class="alert alert-danger error-save-the-new-address text-center" role="alert" style="display:none;">
                                    Please save the new Address!
                                </div>
                                <div class="button-row wp-block-button block-button-black d-flex mt-4">
                                    <a class="ml-auto js-btn-prev wp-element-button" href="<?= wc_get_cart_url(); ?>"
                                       title="Back">Back</a>
                                    <?php if (is_array($addresses) && count($addresses)): ?>
                                        <button <?= $can_continue ? '' : 'style="display:none;"'; ?>
                                                class="ml-auto js-btn-next submit-carrier-step wp-element-button can_continue_order"
                                                type="button"
                                                title="<?php _e('Continue', 'cabling') ?>"><?php _e('Continue', 'cabling') ?></button>

                                        <p class="center can_not_continue_order text-danger" <?= $can_continue ? 'style="display:none;"' : ''; ?>>
                                            Delivery only available to the USA</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!--single form panel-->
                        <div id="carrier-step"
                             class="multisteps-form__panel <?= $step == 'carrier' ? 'js-active' : ''; ?>"
                             data-animation="scaleIn">
                            <div class="multisteps-form__content">
                                <div class="woocommerce-carrier-details">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="ml-2 mb-3">
                                                <!--
                                                    <h4>Transportation company supplier by Datwyler</h4>
                                                    -->
                                                <h4>Shipper – Standard Terms</h4>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="carrier_type"
                                                           id="carrier_type_fedex" value="<?= $fedex_method; ?>" checked
                                                           style="border: 2px solid black ;">
                                                    <label class="form-check-label" for="carrier_type_fedex">
                                                        FEDEX
                                                    </label>
                                                </div>
                                            </div>
                                            <?php if ($customer_level == 2): ?>
                                                <div class="ml-2 mb-3">
                                                    <!--
													<h4>Transportation company provided by me</h4>
													-->
                                                    <h4>Shipper provided by me</h4>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="carrier_type"
                                                               id="carrier_type_free" value="<?= $free_shipping; ?>"
                                                               style="border: 2px solid black ;">
                                                        <label class="form-check-label" for="carrier_type_free"
                                                               style="width:auto;min-width: 200px;">
                                                            <select class="form-select mt-1" name="carrier_id"
                                                                    style="padding-right:30px;"
                                                                    onchange="document.getElementById('carrier_type_free').checked=true;">
                                                                <?php foreach ($transportation_companies as $transportation):
                                                                    $carrier_id = $transportation['transportation']['carrier_id'];
                                                                    $carrier_name = $transportation['transportation']['carrier_name'];
                                                                    $organizations = $transportation['transportation']['organizations'];
                                                                    if (!in_array($user_plant, $organizations)) {
                                                                        continue;
                                                                    }
                                                                    ?>
                                                                    <option value="<?= $carrier_id; ?>"><?= $carrier_name; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="button-row wp-block-button block-button-black d-flex mt-4">
                                <button class="ml-auto js-btn-prev back-shipping-step wp-element-button" type="button"
                                        title="Back">Back
                                </button>
                                <button class="ml-auto js-btn-next submit-billing-step wp-element-button" type="button"
                                        title="Continue">Continue
                                </button>
                            </div>
                        </div>

                        <!--single form panel-->
                        <div id="billing-step"
                             class="multisteps-form__panel <?= $step == 'billing' ? 'js-active' : ''; ?>"
                             data-animation="scaleIn">
                            <div class="multisteps-form__content">
                                <div class="woocommerce-billing-details">
                                    <?php if ($customer_level == 2): ?>
                                        <?php wc_get_template_part('checkout/form-billing-sap'); ?>
                                    <?php else: ?>
                                        <?php do_action('woocommerce_checkout_billing'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($customer_level == 1 && !$check_remove_tax_by_file): ?>
                            <!--single form panel-->
                            <div id="user_wp9_form-step"
                                 class="multisteps-form__panel <?= $step == 'user_wp9_form' ? 'js-active' : ''; ?>"
                                 data-animation="scaleIn">
                                
                                <?php if( !$user_wp9_form ):?> 
                                <div class="multisteps-form__content">
                                    <?php
                                    $gi_wp_form_9 = apply_filters('woocommerce_checkout_gi_add_wp_form_9', null);
                                    echo $gi_wp_form_9;
                                    ?>
                                </div>
                                <?php endif; ?>

                                <?php if( !$user_certificate_form ):?> 
                                <div class="multisteps-form__content mt-5">
                                    <?php wc_get_template_part('checkout/customer-resale-certificate'); ?>
                                </div>
                                <?php endif; ?>

                                <div class="wp-block-button button-row block-button-black d-flex">
                                    <button class="ml-auto js-btn-prev back-billing-step wp-element-button"
                                            type="button" title="Back">Back
                                    </button>
                                    <button class="wp-element-button ml-auto continue-to-summary" type="button"
                                            title="Continue">Continue
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!--single form panel-->
                        <div id="order_review-step"
                             class="multisteps-form__panel <?= $step == 'order_review' ? 'js-active' : ''; ?>"
                             data-animation="scaleIn">
                            <div class="multisteps-form__content">
                                <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

                                <?php do_action('woocommerce_checkout_before_order_review'); ?>

                                <div id="order_review" class="woocommerce-checkout-review-order">
                                    <?php do_action('woocommerce_checkout_order_review'); ?>
                                </div>

                                <?php do_action('woocommerce_checkout_after_order_review'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php do_action('woocommerce_checkout_after_customer_details'); ?>

    <?php endif; ?>


</form>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
