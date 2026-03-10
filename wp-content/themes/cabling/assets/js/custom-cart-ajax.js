jQuery(document).ready(function($) {
    $('body').on('click', '.single_update_to_cart_button', function() {
        var quantity = $('input.input-text.qty').val();
        var product_id = $(this).val();
        var data = {
            action: 'update_cart_quantity_by_product_id',
            product_id: product_id,
            quantity: quantity
        };
        showLoading();
        $.post(ajax_params.ajax_url, data, function(response) {
            hideLoading();
            $('.woocommerce-notices-wrapper').html(`
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <div> Quantity updated successfully! </div>
            </div>    
            `);
        });
    });
});
