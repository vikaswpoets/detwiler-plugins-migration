<?php
function cabling_delete_webshop_user_ajax_callback(): void
{
    $email = $_REQUEST['data'];
    $user = get_user_by('email', $email);
    if ($user) {
        wp_delete_user($user->ID);
    }

    $response = array(
        'success' => true,
    );
    wp_send_json($response);
}

add_action('wp_ajax_cabling_delete_webshop_user_ajax', 'cabling_delete_webshop_user_ajax_callback');

function updateUserSAPDetailsOnLogin($current_user_id=0)
{
    if($current_user_id==0){
        $current_user_id = get_current_user_id();
    }
    //$current_user_id = get_current_user_id();
    $sap_no = get_user_meta($current_user_id, 'sap_customer', true);
    $user_plant = get_user_meta($current_user_id, 'sales_org', true);
    $AccountID = get_user_meta($current_user_id, 'AccountID', true);
    $salesorglst = get_user_meta($current_user_id, 'sales_org_lst', true);

	sap_customer_address($current_user_id);

    $crm = new CRMController();
    if (!$user_plant) {
        if (!$AccountID) {
            $contact = $crm->getContactByUserEmail($user->data->user_email);
            $AccountID = $contact->AccountID;
            if ($AccountID) {
                update_user_meta($current_user_id, 'AccountID', $AccountID);
            }
        }
        if ($AccountID) {
            $user_plant = $crm->getSalesOrganization($AccountID);
            if ($user_plant) {
                update_user_meta($current_user_id, 'sales_org', $user_plant);
            }
        }
    }
    if($AccountID && empty($salesorglst))
    {
        // retrieve list of sales org. for customer
        $salesorglst=$crm->getMultipleSalesOrganization($AccountID);
        //$salesorglst=$crmxx->getMultipleSalesOrganization(1004628);
        if($salesorglst){
            update_user_meta($current_user_id, 'sales_org_lst', $salesorglst);
        }
    }
    $pricelist=get_user_meta($current_user_id, 'crm_price_details', true);
    $customergroupcode=get_user_meta($current_user_id, 'crm_customergroupcode', true);
    if($AccountID) // && empty($pricelist))
    {
        $pricelistarray=$crm->getCustomerPriceDetails($AccountID);
        if(isset($pricelistarray['lstpricelistperSalesOrg'])){
            update_user_meta($current_user_id, 'crm_price_details_per_org', json_encode($pricelistarray['lstpricelistperSalesOrg']));
        }


        //$pricelistarray=$crm->getCustomerPriceDetails($AccountID);
        if(isset($pricelistarray['PriceListCode'])){
            update_user_meta($current_user_id, 'crm_price_details', json_encode($pricelistarray['PriceListCode']));
        }

        if($pricelistarray['CustomerGroupCode']){
            update_user_meta($current_user_id, 'crm_customergroupcode', json_encode($pricelistarray['CustomerGroupCode']));
        }
    }
}
function cabling_login_ajax_callback()
{
    parse_str($_REQUEST['data'], $data);

    $verify_recaptcha = cabling_verify_recaptcha($data['g-recaptcha-response']);

    $err = false;
    $redirect_to = '';
    if ($verify_recaptcha) {
        if (empty($data['log']) || empty($data['pwd'])) {
            $err = true;
            $mess = '<div class="woo-notice alert alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>' . __('Please check your Email or Password.', 'cabling') . '</div>';
        } else {
            $creds = array(
                'user_login' => $data['log'],
                'user_password' => $data['pwd'],
                'remember' => $data['rememberme']
            );

            $user = wp_signon($creds, is_ssl());

            if (is_wp_error($user)) {
                if ($user->get_error_code() === 'invalid_email') {
                    $error = __('Unknown email address. Please check again!', 'cabling');
                } else {
                    $error = $user->get_error_message();
                }
                $err = true;
                $mess = '<div class="alert woo-notice alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>' . $error . '</div>';
            } else {
                updateUserSAPDetailsOnLogin(isset($user->ID)?$user->ID:0);
                if (isset($data['_wp_http_referer']) && strpos($data['_wp_http_referer'], 'checkout') !== false) {
                    $data['_wp_http_referer'] = str_replace('checkout', 'cart', $data['_wp_http_referer']);
                }
                $redirect_to = $data['_wp_http_referer'] ?? wc_get_account_endpoint_url('');
                $mess = '<div class="alert woo-notice alert-success" role="alert"><i class="fa-solid fa-circle-check me-2"></i>' . __('Success! Redirecting...', 'cabling') . '</div>';
            }
        }
    } else {
        $err = true;
        $mess = '<div class="alert woo-notice alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>' . __('reCAPTCHA verification failed. Please try again!', 'cabling') . '</div>';
    }

    $response = array(
        'redirect' => $redirect_to,
        'error' => $err,
        'mess' => $mess
    );
    wp_send_json($response);
}

add_action('wp_ajax_nopriv_cabling_login_ajax', 'cabling_login_ajax_callback');
function cabling_confirm_recaptcha_ajax_callback()
{
    $verify_recaptcha = cabling_verify_recaptcha($_REQUEST['recaptcha']);

    if (empty($verify_recaptcha)) {
        $message = '<div class="alert woo-notice alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>' . __('reCAPTCHA verification failed. Please try again!', 'cabling') . '</div>';
        wp_send_json_error($message);
    } else {
        wp_send_json_success();
    }
    wp_die();
}

add_action('wp_ajax_nopriv_cabling_confirm_recaptcha_ajax', 'cabling_confirm_recaptcha_ajax_callback');

function cabling_verify_user_ajax()
{
    $user_id = (int)$_REQUEST['data'];

    $user = get_user_by('id', $user_id);
    $err = false;
    if ($user) {
        update_user_meta($user->ID, 'has_approve', 'true');
        update_user_meta($user->ID, 'customer_level', '2');
        update_user_meta($user->ID, 'has_approve_date', current_time('mysql'));
        send_email_verified_success($user->ID);
        $mess = 'Verify successfully!';
    } else {
        $err = true;
        $mess = 'Something went wrong! Please try again.';
    }

    $response = array(
        'error' => $err,
        'mess' => $mess
    );
    wp_send_json($response);
}

add_action('wp_ajax_cabling_verify_user_ajax', 'cabling_verify_user_ajax');

function cabling_share_page_email_ajax_callback()
{
    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'cabling-ajax-nonce')) {
        try {
            parse_str($_REQUEST['data'], $data);

            $verify_recaptcha = cabling_verify_recaptcha($data['g-recaptcha-response']);

            if (empty($verify_recaptcha)) {
                wp_send_json_error('Please verify the Captcha.');
            }

            $mailer = WC()->mailer();
            $mailer->recipient = $data['to'];
            $type = 'emails/share-this-page.php';
            $content = cabling_get_custom_email_html('', $data['subject'], $mailer, $type, $data['message_content']);
            $headers = "Content-Type: text/html\r\n";

            $mailer->send($data['to'], $data['subject'], $content, $headers);

            $message = '<div class="woocommerce-message woo-notice" role="alert">' . __('Share successful!', 'cabling') . '</div>';

            wp_send_json_success(array(
                'data' => $message,
            ));
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    } else {
        // Nonce is invalid; handle the error or exit
        wp_send_json_error('Invalid nonce.');
    }
    wp_die();
}

add_action('wp_ajax_cabling_share_page_email_ajax', 'cabling_share_page_email_ajax_callback');
add_action('wp_ajax_nopriv_cabling_share_page_email_ajax', 'cabling_share_page_email_ajax_callback');
function cabling_load_blog_ajax_callback()
{
    check_ajax_referer('cabling-ajax-nonce', 'nonce');
    try {
        parse_str($_REQUEST['data'], $data);

        $page = (int)$_REQUEST['paged'];
        $posts_per_page = $data['posts_per_page'] ?? get_option('posts_per_page');
        $paged = $_REQUEST['load_more'] === 'false' ? 1 : ++$page;
        $total_posts = $posts_per_page * $paged;
        $post_type = $data['post_type'] ?: 'post';
        $filter_params = [];

        if ($data['order'] === 'newest')
            $order = 'desc';
        else
            $order = 'asc';

        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'post_status' => 'publish',
            'order' => $order,
            'tax_query' => array(
                'relation' => 'AND'
            ),
        );
        if ($post_type === 'post') {
            $args['category_name'] = 'blog';
        }
        if (!empty($data['from-date']) && !empty($data['to-date'])) {
            $date = explode(' to ', $data['from-date']);

            $args['date_query'] = array(
                array(
                    'after' => $date[0],
                    'before' => $date[1],
                    'inclusive' => true,
                ),
            );
            $from_date = DateTime::createFromFormat('Y-m-d', $date[0]);
            $to_date = DateTime::createFromFormat('Y-m-d', $date[1]);
            $filter_params[] = '<div class="item item-date me-2 mb-2" data-action="8028">' . $from_date->format('Y') . ' - ' . $to_date->format('Y') . '<span class="clear ms-1"><i class="fa-thin fa-circle-xmark"></i></span></div>';
        }

        if (!empty($data['category'])) {
            $args['cat'] = implode(',', $data['category']);
            $categories = get_terms([
                'taxonomy' => 'category',
                'include' => $data['category']
            ]);
            foreach ($categories as $category) {
                $filter_params[] = '<div class="item item-cat me-2 mb-2" data-action="' . $category->term_id . '">' . ucfirst($category->name) . '<span class="clear ms-1"><i class="fa-thin fa-circle-xmark"></i></span></div>';
            }
        }

        if (!empty($data['news-category'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'news-category',
                'field' => 'term_id',
                'terms' => $data['news-category'],

            );
            $news_cat = get_terms([
                'taxonomy' => 'news-category',
                'include' => $data['news-category']
            ]);
            foreach ($news_cat as $cat) {
                $filter_params[] = '<div class="item item-cat me-2 mb-2" data-action="' . $cat->term_id . '">' . ucfirst($cat->name) . '<span class="clear ms-1"><i class="fa-thin fa-circle-xmark"></i></span></div>';
            }
        }

        if (!empty($data['news_tag'])) {
            $args['tax_query'][] =
                array(
                    'taxonomy' => 'news_tag',
                    'field' => 'term_id',
                    'terms' => $data['news_tag'],
                );
            $news_tags = get_terms([
                'taxonomy' => 'news_tag',
                'include' => $data['news_tag']
            ]);
            foreach ($news_tags as $tagn) {
                $filter_params[] = '<div class="item item-cat me-2 mb-2" data-action="' . $tagn->term_id . '">' . ucfirst($tagn->name) . '<span class="clear ms-1"><i class="fa-thin fa-circle-xmark"></i></span></div>';
            }
        }

        if (!empty($data['tag'])) {
            $args['tag__in'] = $data['tag'];
            $tags = get_terms([
                'taxonomy' => 'post_tag',
                'include' => $data['tag']
            ]);
            foreach ($tags as $tag) {
                $filter_params[] = '<div class="item item-cat me-2 mb-2" data-action="' . $tag->term_id . '">' . ucfirst($tag->name) . '<span class="clear ms-1"><i class="fa-thin fa-circle-xmark"></i></span></div>';
            }
        }

        if ($filter_params && count($filter_params)) {
            $filter_clear = '<div class="clear-item me-2 mb-2">' . sprintf(__('Applied filters (%d)', 'cabling'), count($filter_params)) . '<a class="ms-1" href="javascript:void(0)">' . __('Clear all', 'cabling') . '</a></div>';
            $filter_params = $filter_clear . implode('', $filter_params);
        } else {
            $filter_params = '';
        }

        $query = new WP_Query($args);
        ob_start();
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                get_template_part('template-parts/ajax/content', $post_type);
            endwhile;
        else :
            echo '<div class="woocommerce-no-products-found">
                            <div class="woocommerce-info">
                                ' . __('No blog was found matching your selection.', 'cabling') . '
                            </div>
                        </div>';
        endif;
        wp_reset_postdata();
        $posts = ob_get_clean();

        if ($paged === 1) {
            $found_posts = $query->post_count;
        } else if ($query->max_num_pages == $paged) {
            $found_posts = $query->found_posts;
        } else {
            $found_posts = $total_posts;
        }

        wp_send_json_success(array(
            'posts' => $posts,
            'paged' => $paged,
            'found_posts' => $query->found_posts,
            'filter_params' => $filter_params,
            'load_more.' => $_REQUEST['load_more'],
            'number_posts' => sprintf(__('Showing %s of %s Articles', 'cabling'), $found_posts, $query->found_posts),
            'last_paged' => $query->max_num_pages == $paged,
        ));
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

add_action('wp_ajax_cabling_load_blog_ajax', 'cabling_load_blog_ajax_callback');
add_action('wp_ajax_nopriv_cabling_load_blog_ajax', 'cabling_load_blog_ajax_callback');

function cabling_reset_password_ajax_callback()
{
    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'cabling-ajax-nonce')) {
        try {
            parse_str($_REQUEST['data'], $data);
            $current_user = wp_get_current_user();


            // Check the old password
            $user = wp_authenticate($current_user->user_email, $data['old-password']);

            if (is_wp_error($user)) {
                $message = '<div class="alert woo-notice alert-danger d-flex align-items-center" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <div>
                        ' . __('Old password is incorrect.', 'cabling') . '
                    </div>
                </div>';
                wp_send_json_error($message . $user->get_error_message());
            }

            // Update the password
            wp_set_password($data['new-password'], get_current_user_id());

            wp_set_auth_cookie($current_user->ID);

            $message = '<div class="alert woo-notice alert-success d-flex align-items-center" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>
                            <div>
                                ' . __('Password updated successfully!', 'cabling') . '
                            </div>
                        </div>';
            wp_send_json_success($message);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    } else {
        // Nonce is invalid; handle the error or exit
        wp_send_json_error('Invalid nonce.');
    }
    wp_die();
}

add_action('wp_ajax_cabling_reset_password_ajax', 'cabling_reset_password_ajax_callback');

function cabling_get_api_ajax_callback()
{
    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'cabling-ajax-nonce')) {
        try {
            parse_str($_REQUEST['data'], $data);
            //DEV address
            $oauthTokenUrl = 'https://oauthasservices-a4b9bd800.hana.ondemand.com/oauth2/api/v1/token';
            $apiEndpointBasic = 'https://e2515-iflmap.hcisbt.eu1.hana.ondemand.com/http/GICHANNELS/';
            //Production address
            //$oauthTokenUrl = 'https://oauthasservices-a3c9ce896.hana.ondemand.com/oauth2/api/v1/token';
            //$apiEndpointBasic = 'https://l2515-iflmap.hcisbp.eu1.hana.ondemand.com/http/GICHANNELS/';
            $clientId = 'e27dfb2c-9961-3756-9720-32c99ec819ac';
            $clientSecret = '9ad9a0c8-02ef-3253-993b-8faa20d6965b';
            $webServices = new GIWebServices($oauthTokenUrl, $clientId, $clientSecret);

            if (empty($data['api_service'])) {
                wp_send_json_error('Missing API Service');
            }
            $user = wp_get_current_user();
            $current_user_id = $user->ID;
            $sap_no = get_user_meta($current_user_id, 'sap_customer', true);
            $user_plant = get_user_meta($current_user_id, 'sales_org', true);
            $AccountID = get_user_meta($current_user_id, 'AccountID', true);

            $salesorglst = get_user_meta($current_user_id, 'sales_org_lst', true);


            if (!$user_plant) {
                $crm = new CRMController();
                if (!$AccountID) {
                    $contact = $crm->getContactByUserEmail($user->data->user_email);
                    $AccountID = $contact->AccountID;
                    if ($AccountID) {
                        update_user_meta($current_user_id, 'AccountID', $AccountID);
                    }
                }
                if ($AccountID) {
                    $user_plant = $crm->getSalesOrganization($AccountID);
                    if ($user_plant) {
                        update_user_meta($current_user_id, 'sales_org', $user_plant);
                    }
                }
            }
            if($AccountID && empty($salesorglst))
            {

                // retrieve list of sales org. for customer
                $crmxx = new CRMController();
                $salesorglst=$crmxx->getMultipleSalesOrganization($AccountID);
                //$salesorglst=$crmxx->getMultipleSalesOrganization(1004628);


                if($salesorglst){
                    update_user_meta($current_user_id, 'sales_org_lst', $salesorglst);
                }
            }

            $data['api']['SoldToParty'] = $sap_no;

            // Add show_ponumber
            // if( $data['api_page'] == 'backlog' && !empty( $data['show_ponumber'] ) ){
            //     $data['api']['PurchaseOrderByCustomer'] = $data['show_ponumber'];
            // }

            $bodyParams = array();
            foreach ($data['api'] as $name => $value) {
                if (empty($value)) {
                    continue;
                }
                if ($name == 'OldMaterialNumber') {
                    $value = str_pad(str_replace('-', '', $value), 7, '0568', STR_PAD_LEFT);
                }
                $bodyParams[] = array(
                    'Field' => $name,
                    'Value' => $value,
                    'Operator' => 'and',
                );
            }

            $type = 'ZDD_I_SD_PIM_MaterialBacklog';
            $type_level_2 = 'ZDD_I_SD_PIM_MaterialBacklogType';
            switch ($data['api_page']) {
                case 'inventory':
                    $apiEndpoint = 'GET_DATA_PRICE_CDS';
                    $apiStockEndpoint = 'GET_DATA_MaterialStockReqr';
                    // GID-1095 Change Endpoint address to get Stocks
                    $template = $data['api_page'] . '-item.php';
                    $oldMaterialNumber = $data['api']['MaterialOldNumber'];
                    $oldMaterialNumber = str_replace('-', '', $oldMaterialNumber);
                    //JM 20240606 allow search by dashnumber for 3 and 4 chars srting length
                    if (strlen($oldMaterialNumber) == 4) {
                        $oldMaterialNumber = str_pad($oldMaterialNumber, 8, '0568', STR_PAD_LEFT);
                    } else {
                        $oldMaterialNumber = str_pad($oldMaterialNumber, 7, '0568', STR_PAD_LEFT);
                    }
                    //$oldMaterialNumber=str_pad(str_replace('-','',$oldMaterialNumber),7,'0568',STR_PAD_LEFT);
                    $material = $data['api']['Material'];
                    $basicMaterial = $data['api']['BasicMaterial'];


                    $salesorgtouse=$data['api']['salesorg'];

                    $user_plant=empty($salesorgtouse)?(empty($user_plant) ? '2141' : $user_plant):$salesorgtouse;


                if(!empty($material) && (str_starts_with($material,'49'))){
                    $stockParams = array(
                        array(
                            'Field' => 'Plant',
                            'Sign' => 'eq',
                            'Value' => "2141",
                            'Operator' => 'and',
                        ),
                        array(
                            'Field' => 'SalesOrganization',
                            //'Value' => '2130',
                            'Value' => empty($user_plant) ? '2141' : $user_plant,
                            'Operator' => 'and',
                        ),
                    );
                }else
                {
                    //$salesorgtouse=$data['api']['salesorg'];

                    //$user_plant=empty($salesorgtouse)?(empty($user_plant) ? '2130' : $user_plant):$salesorgtouse;


                    $stockParams = array(
                        array(
                            'Field' => '(Plant',
                            'Sign' => 'eq',
                            'Value' => "2141",
                            'Operator' => 'or',
                        ),
                        array(
                            'Field' => 'Plant',
                            'Sign' => 'eq',
                            'Value' => "2142",
                            'Operator' => ')and',
                        ),/*
                        array(
                            'Field' => 'Plant',
                            'Sign' => 'eq',
                            'Value' => "2130",
                            'Operator' => ')and',
                        ),
						*/
                    /*
                        $stockParams = array(
                        array(
                            'Field' => 'Plant',
                            'Sign' => 'eq',
                            'Value' => "2130",
                            'Operator' => 'and',
                        ),*/
                        array(
                            'Field' => 'SalesOrganization',
                            'Value' => empty($user_plant) ? '2141' : $user_plant,
                            //'Value' => empty($user_plant) ? '2130' : $user_plant,
                            'Operator' => 'and',
                        ),
                    );
                }

                    $priceParams = array(
                        array(
                            'Field' => 'SalesOrganization',
                            'Value' => empty($user_plant) ? '2141' : $user_plant,
                            //'Value' => empty($user_plant) ? '2130' : $user_plant,
                            'Operator' => 'and',
                        )
                    );

                    if (!empty($oldMaterialNumber) && !empty($basicMaterial)) {
                        $priceParams[] = array(
                            'Field' => 'MaterialOldNumber',
                            'Value' => $oldMaterialNumber,
                            'Operator' => '',
                        );
                        $priceParams[] = array(
                            'Field' => 'BasicMaterial',
                            'Value' => $basicMaterial,
                            'Operator' => '',
                        );
                        $priceParams[] = array(
                            'Field' => '(Customer',
                            'Sign' => 'eq',
                            'Value' => $sap_no,
                            'Operator' => 'or',
                        );
                        $priceParams[] = array(
                            'Field' => 'Customer',
                            'Sign' => 'eq',
                            'Value' => "",
                            'Operator' => ')',
                        );

                        $stockParams[] = array(
                            'Field' => 'OldMaterialNumber',
                            'Value' => $oldMaterialNumber,
                            'Operator' => '',
                        );
                        $stockParams[] = array(
                            'Field' => 'BasicMaterial',
                            'Value' => $basicMaterial,
                            'Operator' => '',
                        );
                    } elseif (!empty($material)) {
                        $priceParams[] = array(
                            'Field' => 'Material',
                            'Value' => $material,
                            'Operator' => '',
                        );
                        $priceParams[] = array(
                            'Field' => '(Customer',
                            'Sign' => 'eq',
                            'Value' => $sap_no,
                            'Operator' => 'or',
                        );
                        $priceParams[] = array(
                            'Field' => 'Customer',
                            'Sign' => 'eq',
                            'Value' => "",
                            'Operator' => ')',
                        );
                        $stockParams[] = array(
                            'Field' => 'Material',
                            'Value' => $material,
                            'Operator' => '',
                        );
                    }
                    $responsePrice = $webServices->makeApiRequest($apiEndpoint, $priceParams);



                    if(!empty($material) && (str_starts_with($material,'49'))){
                        $skulst=[];
                        $skulst=getCompatibleSKUList($material);

                        $baseid=getPostIdBySKU($material); // get


                        $dataStock=getStockResponseForSKUList($skulst,$user_plant);
                        $items=[];
                        //define base sku to filter on front end
                        foreach($dataStock as $item)
                        {
                            $item['basesku']=$material;
                            $item['baseid']=$baseid;
                            $items[]=$item;
                        }
                        $dataStock=$items;
                    }else
                    {

                        $responseStock = $webServices->makeApiRequest($apiStockEndpoint, $stockParams);
                        $dataStock = $webServices->getDataResponse($responseStock, 'ZDD_I_SD_PIM_MaterialStockReqr', 'ZDD_I_SD_PIM_MaterialStockReqrType');
                    }

                    $dataPrice = $webServices->getDataResponse($responsePrice, 'ZDD_I_SD_PIM_MaterialPrice', 'ZDD_I_SD_PIM_MaterialPriceType');

                    $sourcetbl="";
                    $pricelist=[];
                    // JM Filter just the first price list in the response
                    foreach($dataPrice as $priceitem)
                    {
                        if($sourcetbl=='')
                        {
                            $sourcetbl=$priceitem['SourceTable'];
                        }
                        if($priceitem['SourceTable']==$sourcetbl)
                        {
                            $pricelist[]=$priceitem;
                        }
                    }
$dataPrice=$pricelist;
//print_r($sourcetbl);

                    $responseData = array(
                        'price' => $dataPrice,
                        'stock' => $dataStock,
                        'data' => [
                            $priceParams,
                            $stockParams,
                        ],
                    );

                    break;
                default:
                    $apiEndpoint = 'GET_DATA_BACKLOG_CDS';
                    $template = $data['api_page'] . '-item.php';
                    $response = $webServices->makeApiRequest($apiEndpoint, $bodyParams);

                    if ($response['error']) {
                        wp_send_json_error('API error: ' . $response['error']);
                    }

                    $responseData = $webServices->getDataResponse($response, $type, $type_level_2);
                    break;
            }

            ob_start();
            wc_get_template('myaccount/api/' . $template, ['data' => $responseData]);
            $result = ob_get_clean();

            wp_send_json_success([
                'data' => $result,
                'raw' => $responseData,
            ]);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    } else {
        // Nonce is invalid; handle the error or exit
        wp_send_json_error('Invalid nonce.');
    }
    wp_die();
}

add_action('wp_ajax_cabling_get_api_ajax', 'cabling_get_api_ajax_callback');
add_action('wp_ajax_nopriv_cabling_get_api_ajax', 'cabling_get_api_ajax_callback');


function cabling_get_api_ajax_callback_checkout()
{
    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'cabling-ajax-nonce')) {
        try {
            parse_str($_REQUEST['data'], $data);

            $webServices = new GIWebServices();

            if (empty($data['api_service'])) {
                wp_send_json_error('Missing API Service');
            }

            $user_plant = get_user_meta(get_current_user_id(), 'sales_org', true);

            $data = [];
            foreach (WC()->cart->get_cart() as $cart_item) {
                $sku = get_post_meta($cart_item['product_id'], '_sku', true);
                $quantity=$cart_item['quantity'];
                $bestmatch=getBestSKU($sku,$quantity);
                $alternatesku=$bestmatch['sku'];
                //JM define new property alternate SKU in cart
                //WC()->cart->cart_contents[$cart_item]['alternate_sku'] = $alternatesku;
                //WC()->cart->cart_contents[$cart_item]['in_stock'] = $bestmatch['in_stock'];
                //WC()->cart->cart_contents[$cart_item]['sku'] = $sku;

                $data[] = $bestmatch;
            }

             //WC()->cart->calculate_totals();

            wp_send_json_success($data);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    } else {
        wp_send_json_error('Invalid nonce.');
    }
    wp_die();
}

add_action('wp_ajax_cabling_get_api_ajax_checkout', 'cabling_get_api_ajax_callback_checkout');
add_action('wp_ajax_nopriv_cabling_get_api_ajax_checkout', 'cabling_get_api_ajax_callback_checkout');

function cabling_update_shipping_method()
{
    $shipping_method = sanitize_text_field($_POST['shipping_method']);
    if (!$shipping_method) {
        $shipping_method = WC()->session->get('chosen_shipping_methods');
        $shipping_method = $shipping_method[0];
    }
    WC()->session->set('chosen_shipping_methods', array($shipping_method));
    if (strpos($shipping_method, "fedex") !== false) {
        WC()->session->set('allow_fedex_calculate_shipping', 1);
        // $packages = WC()->cart->get_shipping_packages();
        // WC()->shipping->calculate_shipping($packages);
    } else {
        WC()->session->set('allow_fedex_calculate_shipping', 0);
    }
    // WC()->cart->calculate_totals();

    // Send a response back
    $response = array(
        'success' => true,
    );
    wp_send_json($response);
    wp_die();
}

add_action('wp_ajax_cabling_update_shipping_method', 'cabling_update_shipping_method');
add_action('wp_ajax_nopriv_cabling_update_shipping_method', 'cabling_update_shipping_method');
