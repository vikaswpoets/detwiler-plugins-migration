(function ($) {
    //validate register form
    $(document).on('submit', 'form[name=register-form]', function(e) {

        showLoading();

        e.preventDefault();

        const form = $(this);
        form.find('.woo-notice').remove();
        form.find('input[type="submit"]').prop('disabled', true);

        gtag('event', 'Account_Creation_Engagement');
        $.ajax({
            url: accountRegistrationData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'gi_handle_registration_request',
                nonce: accountRegistrationData.nonce,
                data: form.serialize(),

            },
            success: function (response) {
                grecaptcha.reset();
                if (response.success){
                    if ( response.data.redirect){
                        window.location.href = response.data.redirect;
                        return;
                    }
                }
                form.find('input[type="submit"]').prop('disabled', false);
                hideLoading();
                form.prepend(response.data);
            },
        })
            .error(function (response) {
                form.prepend(response.data);
                grecaptcha.reset();
                form.find('input[type="submit"]').prop('disabled', false);
                hideLoading();
            });


        return false;
    });

    if ($("#infomation-form").length) {
        $("#infomation-form").validate({
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function (form) {
                const thisForm = $(form);
                thisForm.find('.woo-notice').remove();
                $('.confirm-notice').empty();

                const password = thisForm.find('input[name=password]');
                if (!checkPasswordStrength(password.val())) {
                    $('.confirm-notice').html(`<div class="alert woo-notice alert-danger" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>
                    ${accountRegistrationData.message.passwordError}
            </div>`);
                    // Use animate to smoothly scroll to the target element
                    $('html, body').animate({
                        scrollTop: $('#registerStep').offset().top - 200
                    }, 'slow');
                    return false;
                }

                let formData = thisForm.serialize();
                $.ajax({
                    url: accountRegistrationData.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'gi_handle_new_account_registration',
                        data: formData,
                        nonce: accountRegistrationData.nonce,
                    },
                    success: function (response) {
                        gtag('event', 'Account_Creation_Completed');
                        hideLoading();
                        if (response.success) {
                            thisForm.html(response.data.message);
                            if (typeof response.data.redirect != "undefined") {
                                window.location.href = response.data.redirect;
                            }
                        } else {
                            thisForm.prepend(response.data);
                        }
                    },
                    beforeSend: function () {
                        showLoading();
                    }
                })
                    .error(function (response) {
                        thisForm.prepend(response.data);
                        hideLoading();
                    });

                return false;
            }
        });
    }

    //submit create the child user
    $(document).on('submit', 'form[name=create-user-form]', function (event) {
        event.preventDefault();

        const form = $(this);
        form.find('.woo-notice').empty();
        form.find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: accountRegistrationData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'gi_create_customer_child',
                data: form.serialize(),
                nonce: accountRegistrationData.nonce,
            },
            success: function (response) {
                form.find('.woo-notice').html(response.message).show();
                if (response.error === true) {
                    form.find('button[type="submit"]').prop('disabled', false);
                } else {
                    window.location.reload();
                }
            },
        })
            .fail(function () {
                console.log("error");
            });


        return false;
    });

    $(document).on('click', '.child-customer .delete-child', function (event) {
        event.preventDefault();

        if (confirm("This action cannot be undone, do you confirm the user deletion?")) {
            const id = $(this).attr('data-action');
            $.ajax({
                url: accountRegistrationData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'cabling_delete_webshop_user_ajax',
                    data: id
                },
                success: function (response) {
                    if (response.success === true) {
                        window.location.reload();
                    }
                },
            })
                .fail(function () {
                    console.log("error");
                });
        }
    });

    $(document).on('click', '.child-customer .edit-child', function () {
        const customer = $(this).data('action');

        $.ajax({
            url: accountRegistrationData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'gi_get_customer_child_data',
                data: customer,
                nonce: accountRegistrationData.nonce,
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
            url: accountRegistrationData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'gi_update_customer_child',
                data: $(this).serialize(),
                nonce: accountRegistrationData.nonce,
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
            url: accountRegistrationData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'gi_resend_verify_child_email',
                data: $(this).attr('data-action'),
                email: $(this).attr('data-email'),
                nonce: accountRegistrationData.nonce,
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

})(jQuery);

