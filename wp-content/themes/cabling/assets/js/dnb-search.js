jQuery(document).ready(function ($) {
    // Request qoute
    $(document).ajaxComplete(function () {
        setTimeout(() => {
            if ($('#company-name').length) {
                companydnb_autocomplete(
                    '#company-name',
                    '#billing_country',//country_selector
                    '#company-postcode',//postcode_selector
                    '#billing_state',//fill_to_state_selector
                    '#company-street',//fill_to_address_selector
                    '#company-street-number',//fill_to_address_number_selector
                    '#company-city'//fill_to_city_selector
                )
            }
        }, 500);
    });
    // Contact page
    if( jQuery('.wpcf7-form input[name="your-company-sector"]').length ){
        companydnb_autocomplete(
            '.wpcf7-form input[name="your-company-sector"]',
            '.wpcf7-form select[name="country_list"]'
        )
    }
    // At register page
    if( jQuery('#infomation-form').length ){
        companydnb_autocomplete(
            '#infomation-form #company-name',
            '#infomation-form #billing_country',//country_selector
            '#infomation-form #company-postcode',//postcode_selector
            '#infomation-form #billing_state',//fill_to_state_selector
            '#infomation-form #company-street',//fill_to_address_selector
            '',//fill_to_address_number_selector
            '#infomation-form #company-city'//fill_to_city_selector
        );
    }

    // Convert country code to name at contact form 7 field country_list
    $('select[name="country_list"] option').each(function() {
        const value = $(this).val();
        const countries = dnbSearch.countries;
        if (countries[value]) {
            $(this).text(countries[value]);
        }
    });

    function companydnb_autocomplete(
        obj,
        country_selector = '',
        postcode_selector = '',
        fill_to_state_selector = '',
        fill_to_address_selector = '',
        fill_to_address_number_selector = '',
        fill_to_city_selector = ''
    ){
        $(obj).autocomplete({
            source: function (request, response) {
                var country_code = $(country_selector).val();
                var postalCode = $(postcode_selector).val();

                var data = {
                    'action': 'dnb_search',
                    'nonce': dnbSearch.nonce,
                    'country_code': country_code,
                    'search_term': request.term,  // use request.term to get the current input value
                    'postalCode': postalCode,
                };

                $.ajax({
                    url: dnbSearch.ajax_url,
                    type: 'POST',
                    data: data,
                    success: function (data) {
                        if (data.success) {
                            var formattedData = data.data.searchCandidates.map(function (item) {
                                return {
                                    id: item.displaySequence,
                                    label: item.organization.primaryName,
                                    value: item.organization.primaryName,
                                    postalCode: item.organization.primaryAddress.postalCode,
                                    streetAddress: item.organization.primaryAddress.streetAddress.line1,
                                    addressCountry: item.organization.primaryAddress.addressCountry?.isoAlpha2Code || '',
                                    addressLocality: item.organization.primaryAddress.addressLocality?.name || '',
                                    addressRegion: item.organization.primaryAddress.addressRegion?.name || '',
                                    usSicV4: item.organization.primaryIndustryCodes[0]?.usSicV4 || '',
                                    usSicV4Description: item.organization.primaryIndustryCodes[0]?.usSicV4Description || '',
                                };

                            });
                            response(formattedData);
                        } else {
                            response([]);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log('AJAX call failed:', textStatus, errorThrown);
                        response([]);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                /*
                ui.item.postalCode => postcode_selector
                ui.item.streetAddress => fill_to_address_selector
                ui.item.addressCountry =>
                ui.item.addressLocality => fill_to_city_selector
                ui.item.addressRegion => fill_to_state_selector
                */
                //set usSicV4(siccode) and usSicV4Description(sictext)
                let ck_name,ck_value = '';
                ck_name = 'siccode';
                ck_value = ui.item.usSicV4;
                document.cookie = `${ck_name}=${ck_value}; path=/`;

                ck_name = 'sictext';
                ck_value = ui.item.usSicV4Description;
                document.cookie = `${ck_name}=${ck_value}; path=/`;

                // console.log('Selected:', ui.item);
                if(postcode_selector){
                    jQuery( postcode_selector ).val(ui.item.postalCode).trigger('change');
                }
                if(fill_to_address_selector){
                    jQuery( fill_to_address_selector ).val(ui.item.streetAddress).trigger('change');
                }
                if(fill_to_city_selector){
                    jQuery( fill_to_city_selector ).val(ui.item.addressLocality).trigger('change');
                }
                if(fill_to_state_selector){
                    let stateName = ui.item.addressRegion;
                    let state_code = '';
                    jQuery(fill_to_state_selector).find('option').each(function() {
                        if ($(this).text() == stateName) {
                            $(this).prop('selected', true);
                            state_code = $(this).val();
                        }
                    });
                    jQuery( fill_to_state_selector ).val(state_code).trigger('change');
                }
            }
        });
    }
});
