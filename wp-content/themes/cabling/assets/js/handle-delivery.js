jQuery(document).ready( function(){
    let parentObj = jQuery('#shipping-step');
    parentObj.find('.address-item').on('click',function(){
        let address = jQuery(this).data('address');
        if( typeof address.shipping_country != "undefined" ){
            if(  address.shipping_country == 'US'){
                jQuery('.can_not_continue_order').hide();
                jQuery('.can_continue_order').show();
            }else{
                jQuery('.can_not_continue_order').show();
                jQuery('.can_continue_order').hide();
            }
        } 
        
    })
})
