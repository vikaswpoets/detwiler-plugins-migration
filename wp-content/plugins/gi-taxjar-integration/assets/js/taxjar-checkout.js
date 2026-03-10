jQuery(document).ready(function($) {
    let taxCalculationTimeout;
    let isCalculating = false;
    
    // Monitor address field changes
    $(document.body).on('change input', 'input[name^="billing_"], input[name^="shipping_"], select[name^="billing_"], select[name^="shipping_"]', function() {
        clearTimeout(taxCalculationTimeout);
        taxCalculationTimeout = setTimeout(function() {
            if (!isCalculating) {
                calculateTaxes();
            }
        }, 1000);
    });
    
    // Monitor shipping method changes
    $(document.body).on('change', 'input[name^="shipping_method"]', function() {
        clearTimeout(taxCalculationTimeout);
        taxCalculationTimeout = setTimeout(function() {
            if (!isCalculating) {
                $('body').trigger('update_checkout');
            }
        }, 500);
    });
    
    function calculateTaxes() {
        isCalculating = true;
        
        let useShipping = $('#ship-to-different-address-checkbox').is(':checked');
        let addressData = {};
        
        if (useShipping) {
            addressData = {
                country: $('select[name="shipping_country"]').val(),
                state: $('input[name="shipping_state"], select[name="shipping_state"]').val(),
                city: $('input[name="shipping_city"]').val(),
                postcode: $('input[name="shipping_postcode"]').val(),
                address_1: $('input[name="shipping_address_1"]').val()
            };
        } else {
            addressData = {
                country: $('select[name="billing_country"]').val(),
                state: $('input[name="billing_state"], select[name="billing_state"]').val(),
                city: $('input[name="billing_city"]').val(),
                postcode: $('input[name="billing_postcode"]').val(),
                address_1: $('input[name="billing_address_1"]').val()
            };
        }
        
        // Only calculate for complete US addresses
        if (addressData.country === 'US' && addressData.state && addressData.postcode) {
            // Show calculating indicator
            showTaxCalculatingIndicator();
            
            $.ajax({
                url: gi_taxjar.ajax_url,
                type: 'POST',
                data: {
                    action: 'gi_taxjar_calculate_tax',
                    nonce: gi_taxjar.nonce,
                    address: addressData
                },
                success: function(response) {
                    if (response.success) {
                        // Trigger checkout update to apply new tax
                        $('body').trigger('update_checkout');
                    }
                },
                error: function() {
                    console.log('TaxJar calculation failed');
                },
                complete: function() {
                    isCalculating = false;
                    hideTaxCalculatingIndicator();
                }
            });
        } else {
            isCalculating = false;
        }
    }
    
    function showTaxCalculatingIndicator() {
        // Add calculating class to order review
        $('.woocommerce-checkout-review-order').addClass('calculating-tax');
        
        // Find tax row and add indicator
        $('.shop_table').find('tr').each(function() {
            if ($(this).find('th').text().toLowerCase().includes('tax')) {
                $(this).addClass('calculating').find('td').append(' <small>(' + gi_taxjar.calculating_text + ')</small>');
            }
        });
    }
    
    function hideTaxCalculatingIndicator() {
        $('.woocommerce-checkout-review-order').removeClass('calculating-tax');
        $('.shop_table').find('tr.calculating').removeClass('calculating').find('small').remove();
    }
    
    // Handle checkout updates
    $(document.body).on('updated_checkout', function() {
        isCalculating = false;
        hideTaxCalculatingIndicator();
    });
});