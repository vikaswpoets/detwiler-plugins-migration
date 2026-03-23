function gi_log_validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
function gi_log_setSesion(name, value) {
    document.cookie = name + "=" + (value || "") + "; path=/";
}
function gi_log_deleteCookie(name) {   
    document.cookie = name + '=; Max-Age=-99999999;';  
}
function gi_log_getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}
function wp_log_user_activity(page_name = '',action_name = '',action_value = '',phase = '',identifier = 0,deleteSS = true){
    let obj_data = {
        action: 'save_wp_log_user_activity',
        page_name: page_name,
        action_name: action_name,
        action_value: action_value,
        phase: phase,
        identifier: identifier,
        deleteSS: deleteSS,
    }
    console.log('obj_data',obj_data);
    jQuery.ajax({
        url: log_user_activity.ajax_url,
        type: 'POST',
        data: obj_data,
        success: function(response) {
            if(deleteSS){
                gi_log_setSesion('user_activity_session',null);
            }
            if (response.success) {
                console.log('Activity logged successfully');
            } else {
                console.log('Failed to log activity');
            }
        },
        error: function() {
            console.log('AJAX request failed');
        }
    });
}
// gi_handle_click_double
function gi_handle_click_double(){
    // Handle RFQ Open
    /* JM remove as it was preventing opening rfq if modal closed
    jQuery('.show-product-quote').on('click',function(){
        let can_do = gi_handle_click_handle_elm_class(jQuery(this),'click');
        if(!can_do){
            event.preventDefault();
            return false;
        }
    });
    */
    // Handle RFQ Close
    jQuery('#quote-product-content .button-close').on('click',function(){
        jQuery('.show-product-quote').removeClass('gi-clicked');
    });
}
function gi_handle_click_handle_elm_class(obj,type = 'click'){
    if( obj.hasClass('gi-clicked') ){
        return false;
    }else{
        obj.addClass('gi-clicked');
        return true;
    }
}
function gi_re_save_log(){
    let user_activity_session = gi_log_getCookie('user_activity_session');
    if(user_activity_session){
        user_activity_session = JSON.parse(user_activity_session);
        wp_log_user_activity(
            user_activity_session.page_name,
            user_activity_session.action_name,
            user_activity_session.action_value,
            user_activity_session.phase,
            user_activity_session.identifie
        )
    }
    
}
// RFQ handle
function gi_log_handle_rfq(){
    // Open form
    jQuery('.show-product-quote').on('click',function(){
        wp_log_user_activity('','RFQ','Engagement','Phase 1',1);
    });
    // Validate form
    jQuery('body').on('click','#form-request-quote .btn-submit',function(){
        let email = jQuery('#email').val();
        if(!email || !gi_log_validateEmail(email)){
            wp_log_user_activity('','RFQ','Validation Request','',5);
        }else{
            wp_log_user_activity('','RFQ','Email Validated','',5);
        }
    });
    // Submit
    jQuery('#form-request-quote').on('submit',function(){
        wp_log_user_activity('','RFQ','Engagement','Phase 2',1);
    });
    //Completed
    //plugins/webshop-custom-functions/assets/js/webshop.js => 248
}
//Account Creation
function gi_log_handle_account_creation(){
    // Submit at my-account
    jQuery('#register-form').on('submit',function(){
        wp_log_user_activity('my-account','Account_Creation','Engagement','',1);
    });
    // Submit at register
    jQuery('#infomation-form').on('submit',function(){
        wp_log_user_activity('register','Account_Creation','Engagement','',2);
    });
    //Completed
    //themes/cabling/assets/js/webshop.js => 559
}
//KMI
function gi_log_handle_kmi(){
    //Open form
    jQuery('#keep-informed-modal').on('submit',function(){
        wp_log_user_activity('','KMI','Engagement','',1);
    });
    // Validate form
    jQuery('body').on('click','#keep-informed-form .wp-element-button',function(){
        let email = jQuery('#channel-email').val();
        if(!email || !gi_log_validateEmail(email)){
            wp_log_user_activity('','KMI','Validation Request','',5);
        }else{
            wp_log_user_activity('','KMI','Email Validated','',5);
        }
    });
    // Submit
    jQuery('#keep-informed-form').on('submit',function(){
        wp_log_user_activity('','KMI','Engagement','',2);
    });
    //Completed
    //plugins/webshop-custom-functions/assets/js/webshop.js => 194
    
}
//Contact Request
function gi_log_handle_contact(){
    let page_class = 'page-id-129043';
    // Open page
    if( jQuery('body').hasClass(page_class) ){
        wp_log_user_activity('','Contact Request','Engagement','',1,false);
    }
    // Submit
    const wpcf7Elm = document.querySelector('.wpcf7');
    if(wpcf7Elm){
        jQuery('.wpcf7-submit').on('click',function(){
            console.log('wpcf7submit');
            let email = jQuery('input[name="your-email"]').val();
            let action_value = '';
            if(!email || !gi_log_validateEmail(email)){
                action_value = 'Validation Request';
            }else{
                action_value = 'Email Validated';
            }
            let obj_data = {
                page_name: 'contact-form',
                action_name: 'Contact Request',
                action_value: action_value,
                phase: '',
                identifier: 7
            }
            gi_log_setSesion('user_activity_session',JSON.stringify(obj_data));
        })
    }
}
jQuery( document ).ready( function(){
    gi_handle_click_double();
    gi_re_save_log();
    gi_log_handle_rfq();
    gi_log_handle_account_creation();
    gi_log_handle_kmi();
    gi_log_handle_contact();
});
