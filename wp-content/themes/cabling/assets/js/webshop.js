(function ($) {
    add_phone_validate('#mobile-phone');
    add_phone_validate('#user_telephone');
    add_phone_validate('#contact-phone');
    checkMyAccountNavigation();
    sortList('download-list', 'data-order', 'asc');

    $(document).on('change', 'input[name="existing-customer"]', function () {
        const numberField = $('.client-number-field');
        if ($(this).is(':checked')) {
            numberField.show();
        } else {
            numberField.hide();
        }
    })

    /*$('form.wpcf7-form').on('submit', function() {
        showLoading();
        return true;
    });*/

    $(document).on('change', '.wpcf7-form-control', function () {
        if ($(this).val() === '') {
            $(this).closest('p').removeClass('has-focus');
        } else {
            $(this).closest('p').addClass('has-focus');
        }
    })

    $(document).on('change', '.form-group input', function () {
        if ($(this).val() === '') {
            $(this).closest('.form-group').removeClass('has-focus');
        } else {
            $(this).closest('.form-group').addClass('has-focus');
        }
    })

    $(document).on('keyup', '#mobile-phone', function () {
        if ($(this).val() === '') {
            $(this).closest('.form-group').removeClass('has-focus');
        } else {
            $(this).closest('.form-group').addClass('has-focus');
        }
    })

    $(document).find('.form-group input').each(function () {
        if ($(this).val() === '') {
            $(this).closest('.form-group').removeClass('has-focus');
        } else {
            $(this).closest('.form-group').addClass('has-focus');
        }
    })
    $(document).find('.contact-form-input input').each(function () {
        if ($(this).val() === '') {
            $(this).closest('p').removeClass('has-focus');
        } else {
            $(this).closest('p').addClass('has-focus');
        }
    })

    $(document).on('click', '.contact-form-input label', function () {
        $(this).closest('p').find('input').trigger('focus')
    })

    $(document).on('click', '.child-customer .edit-child', function () {
        const customer = $(this).data('action');

        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cabling_get_customer_ajax',
                data: customer,
                nonce: CABLING.nonce,
            },
            success: function (response) {
                if (response.success) {
                    const customerModal = $('#customer_modal');
                    customerModal.find('.modal-content').html(response.data);
                    const mobile_phone = add_phone_validate('#mobile_phone_edit');
                    const user_phone = add_phone_validate('#user_telephone_edit');

                    if (!mobile_phone.isValidNumber() || !user_phone.isValidNumber()) {
                        customerModal.find('.woo-notice').show().html('Please check your phone number.');
                        customerModal.find('button[type="submit"]').prop('disabled', true);
                    }

                    //customerModal.modal();
                    new bootstrap.Modal('#customer_modal').show();
                } else {
                    alert('Something went wrong');
                }
            },
            beforeSend: function () {
                showLoading();
            }
        })
            .done(function () {
                hideLoading();
            })
            .error(function () {
                hideLoading();
                alert('Something went wrong');
            });
    })

    $(document).on('submit', 'form[name=update-customer-lv1]', function () {

        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cabling_update_customer_ajax',
                data: $(this).serialize(),
                nonce: CABLING.nonce,
            },
            success: function (response) {
                if (response.success) {
                } else {
                    alert('Something went wrong');
                }
                $("#customer_modal").modal('hide');
            },
            beforeSend: function () {
                showLoading();
            }
        })
            .done(function () {
                hideLoading();
            })
            .error(function () {
                hideLoading();
                alert('Something went wrong');
            });
        return false;
    })

    $(document).on('click', '.resend-verify_email', function () {

        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cabling_resend_verify_email_ajax',
                data: $(this).attr('data-action'),
                email: $(this).attr('data-email'),
                nonce: CABLING.nonce,
            },
            success: function (response) {
                if (response.success) {
                    $('.woocommerce-notices-wrapper').html(response.data);
                    setTimeout(function () {
                        $('.woocommerce-notices-wrapper').empty();
                    }, 3000)
                } else {
                    alert('Something went wrong');
                }
            },
            beforeSend: function () {
                showLoading();
            }
        })
            .done(function () {
                hideLoading();
            })
            .error(function () {
                hideLoading();
                alert('Something went wrong');
            });
        return false;
    })

    $(document).on('click', '#load-post-ajax', function () {
        blog_filter_ajax(true);
    })

    $('#blog-filter').on('change', 'select[name="order"],input[type=checkbox]', function () {
        blog_filter_ajax();
    })

    $('#blog-from-date').flatpickr({
        altFormat: "Y/m",
        dateFormat: "Y-m-d",
        altInput: true,
        "plugins": [new rangePlugin({input: "#blog-to-date"})],
        onClose: function (selectedDates, dateStr, instance) {
            if (selectedDates.length > 1) {
                blog_filter_ajax();
            }
        }
    });

    $('.date-picker').flatpickr({
        dateFormat: "m/d/y",
    });

    $(document).on('submit', 'form.woocommerce-EditAccountForm', function () {
        return confirm('Are you sure you want to update your account information?');
    })

    $(document).on('submit', '#reset-account-password', function () {
        const password = $('#new-password');
        const confirm_password = $('#confirm-password');
        const btn_submit = $(this).find('button[type="submit"]');
        $('.woo-notice').remove();
        $('.form-group.invalid').removeClass('invalid');
        if (!checkPasswordStrength(password.val())) {
            password.closest('div').addClass('invalid');
            return false;
        }
        if (password.val() !== confirm_password.val()) {
            confirm_password.closest('div').addClass('invalid');
            return false;
        }

        btn_submit.prop('disabled', true);

        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cabling_reset_password_ajax',
                data: $(this).serialize(),
                nonce: CABLING.nonce,
            },
            success: function (response) {
                hideLoading();
                password.closest('form').prepend(response.data);
                if (response.success) {
                    window.location.reload();
                }

                btn_submit.prop('disabled', false);
            },
            beforeSend: function () {
                showLoading();
            }
        })
            .error(function () {
                hideLoading();
                alert('Something went wrong');
                btn_submit.prop('disabled', false);
            });

        return false;
    })

    $(document).on('click', 'a', function () {
        const href = $(this).attr('href');
        if (href && href.includes('my-account') && href.includes('customer-logout')) {
            return confirm('Are you sure you want to logout?');
        }
        /*if ($(this).closest('table').length) {
            const href = $(this).attr("href");
            if (href && href.toLowerCase().endsWith(".pdf")) {
                const title = $(this).text();
                $('#pdfModal').find('.modal-title').html(title);
                PDFObject.embed(href, "#pdfContent");
                new bootstrap.Modal('#pdfModal').show();

                return false;
            }
        }*/
        return true;
    })

    $(document).on('click', '.search-item', function (e) {
        $(e.delegateTarget).find('.header-search').toggle();
        $(e.delegateTarget).find('.search-ajax').hide();
    })
    $(document).on('click', '.toggle-product-sidebar', function () {
        const product_nav = $(this).closest('.product-services-nav');

        product_nav.find('.back-main-nav').show();
        product_nav.find('.product-cat-nav').show();
        product_nav.find('.product-nav').hide();
    })
    $(document).on('click', '.back-main-nav', function () {
        const product_nav = $(this).closest('.product-services-nav');

        product_nav.find('.back-main-nav').hide();
        product_nav.find('.product-cat-nav').hide();
        product_nav.find('.product-nav').show();
    });

    $('#filter-heading-product').on('click', '.item', function () {
        if ($(this).hasClass('clear-all')) {
            showLoading();
            window.location.href = CABLING.product_page;
        } else {
            const id = $(this).attr("data-action");
            $(document).find(`input[value='${id}']`).prop('checked', false).trigger('change');
        }
    });

    const filter_blog = $('.filter-heading-blog');

    filter_blog.on('click', '.item-cat', function () {
        const id = $(this).attr("data-action");
        $(document).find(`input[value=${id}]`).prop('checked', false).trigger('change');
    });

    filter_blog.on('click', '.clear-item a', function () {
        $('#panelsStayOpen-collapseOne').find('input').val('');
        $('.filter-blog').find(`input[type=checkbox]`).prop('checked', false);
        blog_filter_ajax();
    });

    filter_blog.on('click', '.item-date', function () {
        $('#panelsStayOpen-collapseOne').find('input').val('');
        blog_filter_ajax();
    });

    $('.product-variable-filter').on('change', 'input[type=checkbox]', function () {
        showLoading();
        $('input[name=paged]').val(1);
        $(this).closest('form').submit();
    });

    $(document).on('change', '#filter-blog-order', function () {
        $(this).closest('form').submit();
    });

    $(document).on('click', '.forums-discover a.nav-link', function (e) {
        e.preventDefault();
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
        let filter = $(this).attr('data-action');
        if ($(this).hasClass('forums-category')) {
            filter = $(this).val();
        }
        $('input[name=filter]').val(filter);
        $(this).closest('form').submit();
    });
    $(document).on('change', '.forums-discover select', function () {
        $(this).closest('form').submit();
    });
    $(document).on('click', '.woocommerce-product-gallery__image a', function () {
        return false;
    });
    $(document).on('click', '.menu-item-1033162', function () {
        $(document).find('.cky-btn-revisit').trigger('click');
        return false;
    });
    $(document).on('change', 'select[name=product_group]', function () {
        if ($(this).val() !== '') {
            $('input[name=search-product]').prop('checked', true);
        }
    });
    $(document).on('click', '.accordion-item', function () {
        if ($(this).hasClass('filter-inch')) {
            $('#custom-size-width').attr('name', 'attributes[inches_width_custom]');
            $('#custom-size-id').attr('name', 'attributes[inches_od_custom]');
            $('#custom-size-od').attr('name', 'attributes[inches_id_custom]');
        } else if ($(this).hasClass('filter-millimeter')) {
            $('#custom-size-width').attr('name', 'attributes[milimeters_width_custom]');
            $('#custom-size-id').attr('name', 'attributes[milimeters_id_custom]');
            $('#custom-size-od').attr('name', 'attributes[milimeters_od_custom]');
        }
    });
    $(document).on('submit', '#webservice-api-form', function () {
        const sapMaterial = $('#sapMaterial');
        const parcoCompound = $('#parcocompound');
        const parcoMaterial = $('#parcomaterial');
        const show_ponumber = $('input[name="show_ponumber"]').val();

        if ($('input[name=api_page]').val() === 'inventory' && parcoMaterial.val() === '' && sapMaterial.val() === '' && parcoCompound.val() === '') {
            $('.form-error-text').show();
            return false;
        } else {
            $('.form-error-text').hide();
        }
        if ((sapMaterial.val() !== '' && parcoCompound.val() === '') || sapMaterial.val() === '' && parcoCompound.val() !== '') {
            $('.parcocompound-text').show();
            return false;
        } else {
            $('.parcocompound-text').hide();
        }

        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cabling_get_api_ajax',
                data: $(this).serialize(),
                nonce: CABLING.nonce,
            },
            success: function (response) {
                hideLoading();
                if (response.success) {
                    // Show only hightlight selected
                    $('#api-results').html(response.data.data);
                    if (typeof show_ponumber != 'undefined' && show_ponumber !== '') {
                        showSingleTable(show_ponumber);
                    }
                } else {
                    $('#api-results').html("Nothing to show");
                }

                $('[name="show_ponumber"]').val('');
            },
            beforeSend: function () {
                showLoading();
            }
        })
            .error(function () {
                hideLoading();
                $('#api-results').html("Something wrong !");
            });

        return false;
    });

    $('#api-results').on('click', 'td.PurchaseOrderByCustomer', function () {
        $('#sapMaterial1').val('');
        $('#ponumber1').val('');
        $('#parcomaterial1').val('');
        $('#parcocompound1').val('');
        $('[name="show_ponumber"]').val($(this).attr('data-PurchaseOrderByCustomer'));

        $('#webservice-api-form').submit();
    });

    $(document).on('change', '[name=filter-by]', function () {
        if ($(this).val() === 'name-desc') {
            sortList('download-list', 'data-name', 'asc');
            return false;
        } else if ($(this).val() === 'name-asc') {
            sortList('download-list', 'data-name', 'desc');
            return false;
        } else {
            $(this).closest('form').submit();
        }
    })
    if ($("#infomation-form").length) {
        $("#infomation-form").validate({
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function (form) {
                $(form).find('.woo-notice').remove();
                $('.confirm-notice').empty();

                const password = $(form).find('input[name=password]');
                if (!checkPasswordStrength(password.val())) {
                    $('.confirm-notice').html(`<div class="alert woo-notice alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Your password must have at least: 8 characters long with at least 1 uppercase and 1 lowercase character, numbers and symbols
            </div>`);
                    // Use animate to smoothly scroll to the target element
                    $('html, body').animate({
                        scrollTop: $('#registerStep').offset().top - 200
                    }, 'slow');
                    return false;
                }

                let formData = $(form).serialize();
                $.ajax({
                    url: CABLING.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'cabling_register_new_account_ajax',
                        data: formData,
                        nonce: CABLING.nonce,
                    },
                    success: function (response) {
                        gtag('event', 'Account_Creation_Completed');
                        hideLoading();
                        if (response.success) {
                            $(form).html(response.data.message);
                            if (typeof response.data.redirect != "undefined") {
                                window.location.href = response.data.redirect;
                            }
                        } else {
                            $(form).prepend(response.data.message);
                        }
                    },
                    beforeSend: function () {
                        showLoading();
                    }
                })
                    .error(function () {
                        hideLoading();
                        alert('Something went wrong');
                    });

                return false;
            }
        });
    }


    const wpcf7Elm = document.querySelector('.wpcf7');
    // Check if contac page
    if ($('body').hasClass('page-id-129043')) {
        gtag('event', 'Contact_Engagement');
    }
    // GE-224 Duplicate Leads
    if (wpcf7Elm) {
        wpcf7Elm.addEventListener('wpcf7mailsent', function (event) {
            const submitButton = wpcf7Elm.querySelector('.wpcf7-submit');
            if (submitButton) {
                submitButton.disabled = false;
            }
            var redir = "/thank-you/";
            var eventName = "Contact_Completed"; // default

            if (window.location.href.includes('/contact-us-oil-gas-sealing-solutions-machined-metal')) {
                redir = "/thank-you-oilgas-machined-metal/";
                eventName = "OG_MM_Contact_Completed";
            } else if (window.location.href.includes('/contact-us-oil-gas-sealing-solutions-seals')) {
                redir = "/thank-you-oilgas-seals/";
                eventName = "OG_Seals_Contact_Completed";
            } else if (window.location.href.includes('/contact-us-oil-gas-sealing-solutions')) {
                redir = "/thank-you-oilgas/";
                eventName = "OG_Contact_Completed";
            } else if (window.location.href.includes('/contact-us-aerospace-sealing-solutions')) {
                redir = "/thank-you-aerospace/";
                eventName = "Aerospace_Contact_Completed";
            }
             /*
            else
            {
                    redir="/thank-you-all/";
            }
            */
            gtag('event', eventName);

            if (redir!="/thank-you/"){
                window.location.href = redir;
                //redirectToUrl(redirectUrl);
            } else {
                if (event?.detail?.apiResponse?.ignore_confirm) {
                    const message = event?.detail?.apiResponse?.message ?? '';
                    $('#modalMessage .message-content').html(message);
                    openModal('modalMessage');
                } else {
                    openModal('modalSuccess');
                }
            }
        }, false);
        wpcf7Elm.addEventListener('wpcf7spam', function (event) {
            const submitButton = wpcf7Elm.querySelector('.wpcf7-submit');
            if (submitButton) {
                submitButton.disabled = false;
            }
            openModal('modalError');
        }, false);
        wpcf7Elm.addEventListener('wpcf7invalid', function (event) {
            const submitButton = wpcf7Elm.querySelector('.wpcf7-submit');
            if (submitButton) {
                submitButton.disabled = false;
            }
            openModal('modalErrorValidation');
        }, false);
        wpcf7Elm.addEventListener('wpcf7mailfailed', function (event) {
            const submitButton = wpcf7Elm.querySelector('.wpcf7-submit');
            if (submitButton) {
                submitButton.disabled = false;
            }
            openModal('modalError');
        }, false);
        wpcf7Elm.addEventListener('wpcf7submit', function (event) {
            const submitButton = wpcf7Elm.querySelector('.wpcf7-submit');
            if (submitButton) {
                submitButton.disabled = false;
            }
            if (event.detail.status === 'wpcf7invalid') {
                openModal('modalError');
            }
            gtag('event', 'Contact_Engagement');
        }, false);

        wpcf7Elm.addEventListener('wpcf7beforesubmit', function(event) {
            const submitButton = wpcf7Elm.querySelector('.wpcf7-submit');
            if (submitButton) {
                submitButton.disabled = true;
            }
        }, false);
    }

    $(document).on('change', 'input[name=select-shipping-address]', function () {
        const elm = $(this);
        thmaf_address.populate_selected_address(elm, elm.attr('data-type'), elm.attr('data-id'));
    })

    // at carrier-step back to shipping-step
    $(document).on('click', '.back-shipping-step', function () {
        $('#carrier-step').removeClass('js-active');
        $('#carrier-step-progress').removeClass('js-active');
        $('#shipping-step').addClass('js-active');
        $('#shipping-step-progress').addClass('js-active');
    })
    // at billing-step back to carrier-step
    $(document).on('click', '.back-carrier-step', function () {
        $('#billing-step').removeClass('js-active');
        $('#billing-step-progress').removeClass('js-active');
        $('#carrier-step').addClass('js-active');
        $('#carrier-step-progress').addClass('js-active');
    })
    // at order_review-step back to billing-step
    $(document).on('click', '.back-billing-step', function () {
        $('#order_review-step').removeClass('js-active');
        $('#order_review-step-progress').removeClass('js-active');

        $('#user_wp9_form-step').removeClass('js-active');
        $('#user_wp9_form-step-progress').removeClass('js-active');

        $('#billing-step').addClass('js-active');
        $('#billing-step-progress').addClass('js-active');
    })

    $(document).on('click', '.submit-billing-step', function () {
        $('.multisteps-form').find('.woo-notice').remove();
        if ($('input[name=select-shipping-address]:checked').length) {
            show_checkout_billing();
        } else {
            $('.multisteps-form').prepend('<div class="alert alert-danger d-flex align-items-center woo-notice" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i><span>Please select Shipping Address.</span></div>');
        }
        return false;
    })

    $(document).on('click', '.submit-carrier-step', function () {
        if ($('#thmaf-cart-shipping-form-section').find('#cart_ship_form_action').length > 0) {
            $('.error-save-the-new-address').show();
            $('html, body').animate({
                scrollTop: $('#thmaf-cart-shipping-form-section').offset().top - 200
            }, 'fast');
        } else {
            $('.multisteps-form').find('.woo-notice').remove();
            $('.multisteps-form__panel').removeClass('js-active');
            $('.multisteps-form__progress-btn').removeClass('js-active');
            $('#carrier-step-progress').addClass('js-active');
            $('#carrier-step').addClass('js-active');
        }
        return false;
    })
    $(document).on('click', '#carrier_type_fedex', function () {
        updateShippingMethod(fedex_method);
    })
    $(document).on('click', '#carrier_type_free', function () {
        updateShippingMethod(free_shipping);
    })
    $(document).on('change', 'select[name="carrier_id"]', function () {
        updateShippingMethod(free_shipping);
    })
    // End GID-1050

    // Handle billing-step next
    $(document).on('click', '.continue-to-order', function () {
        $('.woocommerce-billing-details').find('.woo-notice').remove();

        if (!$('#same-shipping-address').is(':checked') && !$('#diff-shipping-address').is(':checked')) {
            $('.woocommerce-billing-details').prepend('<div class="alert alert-danger d-flex align-items-center woo-notice" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i><span>Please select Billing Address.</span></div>');
            return;
        }
        let shipping_first_name = '';
        let shipping_last_name = '';
        let shipping_company = '';
        let shipping_country = '';
        let shipping_address_1 = '';
        let shipping_address_2 = '';
        let shipping_city = '';
        let shipping_state = '';
        let shipping_postcode = '';

        if ($('#same-shipping-address').is(':checked')) {
            shipping_first_name = $('#shipping_first_name').val();
            shipping_last_name = $('#shipping_last_name').val();
            shipping_company = $('#shipping_company').val();
            shipping_country = $('#shipping_country').val();
            shipping_address_1 = $('#shipping_address_1').val();
            shipping_address_2 = $('#shipping_address_2').val();
            shipping_city = $('#shipping_city').val();
            shipping_state = $('#shipping_state').val();
            shipping_postcode = $('#shipping_postcode').val();

            $('input[name=billing_first_name]').val(shipping_first_name);
            $('input[name=billing_last_name]').val(shipping_last_name);
            $('input[name=billing_company]').val(shipping_company);
            $('select[name=billing_country]').val(shipping_country);
            $('input[name=billing_address_1]').val(shipping_address_1);
            $('input[name=billing_address_2]').val(shipping_address_2);
            $('input[name=billing_city]').val(shipping_city);
            $('select[name=billing_state]').val(shipping_state);
            $('input[name=billing_postcode]').val(shipping_postcode);
        }

        if ($(this).hasClass('new-address')) {
            let isCompleteBilling = true;
            $('#accordionAddress').find('.form-control').each(function () {
                if ($(this).attr('required') && $(this).val() == '') {
                    isCompleteBilling = false;
                }
            })

            if (!isCompleteBilling) {
                $('.woocommerce-billing-details').prepend('<div class="alert alert-danger d-flex align-items-center woo-notice" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i><span>Please complete Billing Address.</span></div>');
                return false;
            }
        }
        show_checkout_shipping();
        let can_call = localStorage.getItem('cabling_get_api_ajax_checkout');
        can_call = can_call ? true : false;
        //console.log('can_call',can_call);
        if( can_call ){
            //console.log('calling cabling_get_api_ajax_checkout');
            $.ajax({
                url: CABLING.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'cabling_get_api_ajax_checkout',
                    data: 'api_service=GET_DATA_PRICE&api_page=inventory',
                    nonce: CABLING.nonce,
                },
                success: function (response) {
                    hideLoading();
                    if (response.success) {
                        // SET selected shipping method
                        var selectedShipping = $('input[name="carrier_type"]:checked').val();
                        $('body').find('.shipping_method').prop('checked', false);
                        $('body').find('.shipping_method[value="' + selectedShipping + '"]').prop('checked', true);
                        $('body').trigger('update_checkout');

                        localStorage.setItem('cabling_get_api_ajax_checkout',1);
                    }
                },
                beforeSend: function () {
                    showLoading();
                }
            })
        }

        $('.woocommerce-shipping-totals.shipping td').attr('colspan', '2');
    })
    const myCollapsible = document.getElementById('collapseAddNew');

    if (myCollapsible) {
        myCollapsible.addEventListener('shown.bs.collapse', function () {
            $('#same-shipping-address').prop('checked', false);
            $('#diff-shipping-address').prop('checked', true);
        });
    }
    $(document).on('change', '#same-shipping-address', function () {
        if ($(this).is(':checked')) {
            $('#diff-shipping-address').prop('checked', false);
        }
    })

    $(document).on('click', '.continue-to-summary', function () {
        if ($('#formFileW9').length && $('#formFileCertificate').length && $('#formFileW9')[0].files[0] && $('#formFileCertificate')[0].files[0]){

            $('.woocommerce #payment #place_order, .woocommerce-page #payment #place_order').addClass('disable');
            const formData = new FormData();

            const fileInput = $('#formFileW9')[0].files[0];
            const formFileCertificate = $('#formFileCertificate')[0].files[0];

            formData.append('formFileW9', fileInput);
            formData.append('formFileCertificate', formFileCertificate);

            formData.append('action', 'w9_form_ajax');
            $.ajax({
                url: CABLING.ajax_url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success == true) {
                        $('.woocommerce #payment #place_order, .woocommerce-page #payment #place_order').addClass('place-order-upload');

                        showCheckoutPayment();
                    }else
                    {
                        alert('Invalid File Type! Only pdf, jpg, gif, png files are allowed.');
                        return false;
                    }
                    $('.woocommerce #payment #place_order, .woocommerce-page #payment #place_order').removeClass('disable');
                },
                error: function (response) {
                    alert('File upload error. Please try again!');
                    return false;
                }
            });
        } else {
            showCheckoutPayment();
        }
    })
    $(document).on('click', '.user-edit-account-upload-wp_form_9', function () {
        var fileInput = $('#formFileW9')[0].files[0];
        var formData = new FormData();
        formData.append('formFileW9', fileInput);
        formData.append('action', 'w9_form_ajax');
        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success == true) {
                    alert('Uploaded');
                    window.location.reload();
                }
            },
            error: function (response) {

            }
        });
    })

    //Already upload w9 file so not need do this: ref to GID-1072
    /*
    $(document).on('click', '.place-order-upload', function (e) {
        e.stopPropagation();
        e.preventDefault();

        var fileInput = $('#formFile')[0].files[0];
        var formData = new FormData();
        formData.append('file', fileInput);
        formData.append('action', 'w9_file_upload_ajax');
        $('.woocommerce #payment #place_order, .woocommerce-page #payment #place_order').removeClass('place-order-upload');
        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success == true) {
                    $('.woocommerce #payment #place_order, .woocommerce-page #payment #place_order').trigger('click');
                }
            },
            error: function (response) {

            }
        });
    })
    */

})(jQuery);

function showCheckoutPayment() {
    const $j = jQuery;

    const shippingRadio = $j('input[name="select-shipping-address"]:checked');

    let addressObject;
    if (shippingRadio.length) {
        const dataAddressStr = shippingRadio.closest('.address-item').attr('data-address');
        addressObject = JSON.parse(dataAddressStr);
    }

    $j('#order_review-step').find('.woocommerce-error').remove();

    $j.ajax({
        url: CABLING.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'gi_calculate_tax',
            data: addressObject,
        },
        beforeSend: function () {
            showLoading();
        },
        success: function (response) {
            if (!response.success && response.data) {

                $j('#order_review').before(
                    '<div class="woocommerce-error" role="alert">' +
                        response.data +
                    '</div>'
                );
            }
        }
    }).then(function () {
        $j('body').trigger('update_checkout');

        $j('.multisteps-form__progress-btn').removeClass('js-active');
        $j('.multisteps-form__panel').removeClass('js-active');
        $j('#order_review-step-progress').addClass('js-active');
        $j('#order_review-step').addClass('js-active');

        hideLoading();
    });
}

function show_checkout_shipping() {
    const $ = jQuery.noConflict();
    $('.multisteps-form').find('.woo-notice').remove();
    $('.multisteps-form__progress-btn').removeClass('js-active');
    $('.multisteps-form__panel').removeClass('js-active');

    if ($('#user_wp9_form-step').length) {
        $('#user_wp9_form-step-progress').addClass('js-active');
        $('#user_wp9_form-step').addClass('js-active');
    } else {
        showCheckoutPayment();
        // $('#order_review-step-progress').addClass('js-active');
        // $('#order_review-step').addClass('js-active');
    }
}

function show_checkout_billing() {
    const $ = jQuery.noConflict();
    $('.multisteps-form__progress-btn').removeClass('js-active');
    $('.multisteps-form__panel').removeClass('js-active');

    $('#billing-step-progress').addClass('js-active');
    $('#billing-step').addClass('js-active');
}

function sortList(element, name, order) {
    const $ = jQuery.noConflict();
    const myList = $(`#${element}`);
    if (myList.length) {
        const listItems = myList.children('div').get();

        listItems.sort(function (a, b) {
            const nameA = $(a).find('.download-item').attr(name).toLowerCase();
            const nameB = $(b).find('.download-item').attr(name).toLowerCase();

            if (order === 'asc') {
                return (nameA < nameB) ? -1 : (nameA > nameB) ? 1 : 0;
            } else if (order === 'desc') {
                return (nameA > nameB) ? -1 : (nameA < nameB) ? 1 : 0;
            }
        });

        $.each(listItems, function (index, item) {
            myList.append(item);
        });
    }
}


function showSingleTable(order) {
    const $ = jQuery.noConflict();
    // const tableContent = $(`tr.single-${order}`);
    const tableContent = $('.backlog-row-single[data-order="' + order + '"]');
    const tablePODetails = $('#table-order-detail');

    $(`.backlog-row`).removeClass('table-warning').show();
    // $(`.row-${order}`).addClass('table-warning');
    $('.backlog-row[data-order="' + order + '"]').addClass('table-warning');

    tablePODetails.find('.table-heading span').html(order);
    tablePODetails.find('tbody').empty();
    tableContent.each(function () {
        tablePODetails.find('tbody').append(`<tr>${$(this).html()}</tr>`);
    })
    tablePODetails.removeClass('hidden');


    $('html, body').animate({
        scrollTop: tablePODetails.offset().top - 200
    }, 'fast');
}

function checkMyAccountNavigation() {
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is required but not loaded');
        return;
    }
    const $ = jQuery;

    const $dashboard = $('.woocommerce-MyAccount-navigation-link--dashboard');
    if (!$dashboard.length) {
        return;
    }

    const navigationElements = {
        'edit-account': 'Update your details and preferences',
        'edit-address': 'Billing/Shipping Address',
        'setting-account': 'Update your preferences for receiving news and updates about Datwyler Industrial Sealing',
        'sales-backlog': 'See details about past and open purchase orders',
        'inventory': 'See item inventory, pricing and lead times for ordering',
        'users-management': 'Add new user associate to this account',
        'shipment': 'See the items shipped in the last 12 months'
    };

     const $navigationItems = Object.keys(navigationElements)
        .map(key => $(`.woocommerce-MyAccount-navigation-link--${key}`))
        .filter($el => $el.length > 0);

    const isDashboardActive = $dashboard.hasClass('is-active');
    const hasActiveNavElement = Object.keys(navigationElements).some(key =>
        $(`.woocommerce-MyAccount-navigation-link--${key}`).hasClass('is-active')
    );

    if (isDashboardActive || hasActiveNavElement) {
        $navigationItems.forEach($el => $el.show());
    } else {
        $navigationItems.forEach($el => $el.hide());
    }

    // Setup tooltips
    Object.entries(navigationElements).forEach(([key, tooltip]) => {
        const $element = $(`.woocommerce-MyAccount-navigation-link--${key}`);
        if ($element.length) {
            $element
                .addClass('has-tooltip')
                .find('a')
                .attr('data-title', tooltip);
        }
    });
}

function checkPasswordStrength(password) {
    let strength = 0;

    // Check for minimum length
    if (password.length >= 8) {
        strength += 1;
    }

    // Check for at least one uppercase letter
    if (/[A-Z]/.test(password)) {
        strength += 1;
    }
    // Check for at least one uppercase letter
    if (/[a-z]/.test(password)) {
        strength += 1;
    }

    // Check for at least one special character
    if (/[\W_]/.test(password)) {
        strength += 1;
    }

    // Check for at least one number
    if (/\d/.test(password)) {
        strength += 1;
    }

    return strength === 5;
}

function blog_filter_ajax(load_more = false) {
    const $ = jQuery.noConflict();

    const filter_form = $('#blog-filter');
    const data = filter_form.serialize();
    const paged = $('input[name=paged]').val();
    $.ajax({
        url: CABLING.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'cabling_load_blog_ajax',
            data: data,
            paged: paged,
            load_more: load_more,
            nonce: CABLING.nonce,
        },
        success: function (response) {
            if (response.success) {
                $('.filter-params').html(response.data.filter_params);
                if (load_more) {
                    $('.post-wrapper').append(response.data.posts);
                } else {
                    $('.post-wrapper').html(response.data.posts);
                }

                $('.total-posts > span').html(response.data.found_posts);
                filter_form.find('input[name=paged]').val(response.data.paged);

                if (response.data.found_posts === 0) {
                    $('.number-posts').hide();
                } else {
                    $('.number-posts').show().html(response.data.number_posts);
                }

                if (response.data.last_paged) {
                    $('#load-post-ajax').hide();
                } else {
                    $('#load-post-ajax').show();
                }

                hideLoading();
            }
        },
        beforeSend: function () {
            showLoading();
        }
    })
        .error(function () {
            hideLoading();
            alert('Something went wrong');
        });
}

function add_phone_validate(phone_element) {
    const phoneCodeElement = document.querySelector(phone_element);
    if (!phoneCodeElement) return null;

    if (typeof window.intlTelInput === 'undefined') {
        console.error('intlTelInput library not loaded');
        return null;
    }

    const thisForm = phoneCodeElement.closest('form');
    if (!thisForm) return null;

    const buttonElement = thisForm.querySelector('.btn-submit');

    let parentElement;
    if (phone_element === '#contact-phone'){
        parentElement = phoneCodeElement.closest('.phone-wrapper');
    } else {
        parentElement = phoneCodeElement.parentNode;
    }
    const errorMsg = parentElement.querySelector('.input-error');
    const phoneNumberInput = parentElement.querySelector('.phone_number');
    const phoneCodeInput = parentElement.querySelector('.phone_code');

    if (!errorMsg || !phoneNumberInput || !phoneCodeInput) {
        console.error('Required elements not found');
        return null;
    }

    const resetPhoneError = () => {
        phoneCodeElement.classList.remove("error");
        errorMsg.textContent = "";
        errorMsg.classList.add("hidden");
        if (buttonElement) {
            buttonElement.disabled = false;
        }
    };

    const iti = window.intlTelInput(phoneCodeElement, {
        initialCountry: "us",
        separateDialCode: true,
        preferredCountries: ['us', 'ca'],
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        customPlaceholder: () => ""
    });

    const handlePhoneChange = () => {
        resetPhoneError();
        const value = phoneCodeElement.value.trim();
        if (!value) return;

        if (iti.isValidNumber()) {
            phoneNumberInput.value = value;
            if (buttonElement) {
                buttonElement.disabled = false;
            }
        } else {
            phoneCodeElement.classList.add("error");
            errorMsg.textContent = 'Invalid number';
            errorMsg.classList.remove("hidden");
            if (buttonElement) {
                buttonElement.disabled = true;
            }
        }
        phoneCodeElement.closest('.form-group').classList.add('has-focus');
    };

    const handleCountryChange = () => {
        const countryData = iti.getSelectedCountryData();
        if (countryData?.dialCode) {
            phoneCodeInput.value = countryData.dialCode;
        }
        phoneCodeElement.dispatchEvent(new Event("change"));
    };

    phoneCodeElement.addEventListener("change", handlePhoneChange);
    phoneCodeElement.addEventListener("countrychange", handleCountryChange);

    phoneCodeElement.style.paddingLeft = "75px";

    return iti;
}

function hideLoading() {
    jQuery('.loading-wrap').fadeOut('fast');
    jQuery('body').removeClass('has-loading');
}

function showLoading() {
    jQuery('.loading-wrap').fadeIn();
    jQuery('body').addClass('has-loading');
}

function openModal(modalId) {
    const modalElement = document.getElementById(modalId);
    new bootstrap.Modal(modalElement).show();
}

// #ref GT-38
if (jQuery('.wpcf7-form-control-wrap[data-name="contact_marketing_agreed"]').length) {
    let contact_marketing_agreed_html = jQuery('.contact_marketing_agreed_html p').html();
    jQuery('.contact_marketing_agreed_html').remove();
    jQuery('.wpcf7-form-control-wrap[data-name="contact_marketing_agreed"] .wpcf7-list-item-label').html(contact_marketing_agreed_html);
}

// Trigger AJAX update of shipping method
function updateShippingMethod(methodId) {
    const shippingMethod = jQuery('#shipping_method');
    shippingMethod.find('[name="shipping_method[0]"]').prop('checked', false);
    shippingMethod.find('[name="shipping_method[0]"][value="' + methodId + '"]').prop('checked', true);
    shippingMethod.find('[name="shipping_method[0]"]').trigger('change')
}

// GT-1090
jQuery(document).ready(function () {
    const data_parcocompound = [
        "1200-70, 70-durometer silicone",
        "1933-70, 70-durometer fluorosilicone, meets M25988/1",
        "4067-70, 70-durometer nitrile, meets MS28775",
        "4200-70, 70-durometer nitrile, general-purpose",
        "4368-70, 70-durometer nitrile, meets AMS P 83461",
        "4457-65, 65-durometer nitrile, meets AMS P 5315",
        "4900-70, 70-durometer nitrile, general-purpose",
        "4900-90, 90-durometer nitrile, general-purpose",
        "5323-70, 70-durometer EPDM, NSF-61 listed",
        "5600-70, 70-durometer EPDM, general-purpose",
        "5601-70, 70-durometer EPDM, FDA-conforming",
        "5778-70, 70-durometer EPDM, NSF-61 listed",
        "9005-75, 75-durometer brown FKM, 91761 | (909)",
        "9009-90, 90-durometer, black FKM, meets AMS7259",
        "9021-95, 95-durometer, black FKM",
        "9266-75, 75-durometer black FKM, meets AMS7276",
        "9500-75, 75-durometer black FKM, general-purpose",
        "9505-75, 75-durometer brown FKM, general-purpose"
    ];
    const formattedData = data_parcocompound.map(item => {
        const [valuePart, ...labelParts] = item.split(', ');
        const orgValue = valuePart;
        const value = valuePart.replace('-', '');
        const label = labelParts.join(', ');
        return {value, label, orgValue};
    });
    //Custom cat autocomplete
    jQuery.widget("custom.catcomplete", jQuery.ui.autocomplete, {
        _create: function () {
            this._super();
            this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
        },
        _renderMenu: function (ul, items) {
            var that = this,
                currentCategory = "";
            jQuery.each(items, function (index, item) {
                var li;
                if (item.category != currentCategory) {
                    ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                    currentCategory = item.category;
                }
                li = that._renderItemData(ul, item);
                if (item.category) {
                    li.attr("aria-label", item.category + " : " + item.label);
                }
            });
        }
    });

    autocomplete_compound_number("#parcocompound");
    autocomplete_compound_number("#parcocompound1");

    function autocomplete_compound_css() {
        // Add scroll
        setTimeout(() => {
            try {
                const $autocomplete = jQuery(".ui-autocomplete");
                const $categories = jQuery("li.ui-autocomplete-category");

                if ($autocomplete.length) {
                    $autocomplete.css({
                        "max-height": "200px",
                        "overflow-y": "auto",
                        "overflow-x": "hidden"
                    });
                }

                if ($categories.length) {
                    $categories.css({
                        "font-weight": "bold",
                        "padding-left": "5px"
                    });
                }
            } catch (error) {
                console.error('Error applying autocomplete styles:', error);
            }
        }, 10);
    }

    function autocomplete_compound_number(obj) {
        jQuery(obj).catcomplete({
            source: function (request, response) {
                const fixedTerm = " ";
                request.term = fixedTerm;
                response(jQuery.ui.autocomplete.filter(formattedData.map(item => ({
                    label: item.orgValue + ', ' + item.label,
                    value: item.value,
                    category: "Write the Compound Number, or select one of the popular compounds below"
                })), fixedTerm));
                autocomplete_compound_css();
            },
            select: function(event, ui) {
                jQuery(this).closest('.form-group').addClass('has-focus');
            }
        });
        // Show all items when focus
        jQuery(obj).on("focus input", function () {
            jQuery(this).catcomplete("search", " ");
        });
    }
});
// END GT-1090

function redirectToUrl(url) {
    if (url && typeof url === 'string') {
        window.location.href = url;
    }
}
