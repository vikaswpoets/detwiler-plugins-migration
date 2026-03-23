<?php
//define('DNB_API_KEY', 'f1dfadd7094745b9ad90b90f00e2bb684a2d4461538749709bc10ba8e3141e4c');
//define('DNB_API_SECRET', '9cca9799bd114fdd8cba1aff57c644ba57663b1f7e69419794c7d91c4cd4ee9e');

define('DNB_API_KEY', '4ddabe28f9044cf288183490c2af1b90d7f3595d011145e79dcfc5fd5d083b60');
define('DNB_API_SECRET', '6da16fe621814c34afdbf9f2ab6f26aa922e85aeec29456d81b817741e12d7f7');

function get_dnb_access_token(){
    $token_info = get_transient('dnb_access_token_info');

    if ($token_info && isset($token_info['expires_at']) && time() < $token_info['expires_at']) {
        return $token_info['access_token'];
    }

    $encodedKey = base64_encode(DNB_API_KEY . ':' . DNB_API_SECRET);
    $url = "https://plus.dnb.com/v3/token";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $encodedKey",
        "Content-Type: application/x-www-form-urlencoded",
        "Cache-Control: no-cache"
    ]);
    $postFields = "grant_type=client_credentials";
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    $response = curl_exec($ch);
    $response_data = json_decode($response, true);

    if (isset($response_data['access_token']) && isset($response_data['expires_in'])) {
        $token_info = [
            'access_token' => $response_data['access_token'],
            'expires_at' => time() + $response_data['expires_in'],
        ];
        set_transient('dnb_access_token_info', $token_info, $response_data['expires_in']);
        return $response_data['access_token'];
    } 
    
    curl_close($ch);
}

function enqueue_dnb_search_script() {
    wp_enqueue_style('jquery-ui-css', get_template_directory_uri() . '/assets/css/jquery-ui.css');
    wp_enqueue_style('dnb-search', get_template_directory_uri() . '/assets/css/dnb-search.css');
    wp_enqueue_script('dnb-search', get_template_directory_uri() . '/assets/js/dnb-search.js', array('jquery'), null, true);
    wp_localize_script('dnb-search', 'dnbSearch', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'countries' => CRMCountry::getCountries(),
        'nonce'    => wp_create_nonce('dnb_search_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_dnb_search_script');

function dnb_search() {
    check_ajax_referer('dnb_search_nonce', 'nonce');

    $country_code = sanitize_text_field($_POST['country_code']);
    $search_term = sanitize_text_field($_POST['search_term']);
    $postalCode = sanitize_text_field($_POST['postalCode']);

    // Get the access token
    $access_token = get_dnb_access_token(); // Function to get the access token, as defined earlier

    $url = 'https://plus.dnb.com/v1/search/companyList';
    $url = 'https://plus.dnb.com/v1/search/typeahead';
    $body = [
        'searchTerm' => $search_term
    ];
    if($country_code){
        $body['countryISOAlpha2Code'] = $country_code;
    }
    // if($postalCode){
    //     $body['postalCode'] = $postalCode;
    // }

    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    $query = http_build_query($body);
    $request_url = $url . '?' . $query;
    $response = wp_remote_get($request_url, [
        'headers' => $headers,
    ]);

    if (is_wp_error($response)) {
        // Handle error
        $error_message = $response->get_error_message();
        wp_send_json_error($error_message);
    } else {
        // Handle successful response
        $response_body = wp_remote_retrieve_body($response);
        wp_send_json_success(json_decode($response_body, true));
    }
}

add_action('wp_ajax_dnb_search', 'dnb_search');
add_action('wp_ajax_nopriv_dnb_search', 'dnb_search');

// Add country list to contact form 7
add_filter('wpcf7_form_tag', 'add_country_list_select_tag', 10, 2);
function add_country_list_select_tag($tag, $unused) {
    if ($tag['name'] != 'country_list') {
        return $tag;
    }
    $countries = CRMCountry::getCountries();
    $tag['raw_values'] = array_values($countries);
    $tag['values'] = array_keys($countries);
    return $tag;
}
