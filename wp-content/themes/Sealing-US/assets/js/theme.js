(function ($) {

    const elem = document.querySelector('.main-slider');
    if (elem) {
        new Flickity(elem, {
            // options
            cellAlign: 'left',
            contain: true,
            autoPlay: 5000,
            pageDots: false
        });
    }

    const elemListSlide = document.querySelector('.news-list-slider');
    if (elemListSlide) {
        new Flickity(elemListSlide, {
            // options
            cellAlign: 'left',
            contain: false,
            pageDots: false,
            wrapAround: true,
        });
    }

    let ajaxSearch = null;

    $(document).on('keyup', '.search-field', function () {
        cabling_search_ajax(ajaxSearch);
    })

    $(document).on('click', '.search-filter-ajax', function () {
        cabling_search_ajax(ajaxSearch);
    })

    $(document).on('click', '.filter-pagination', function (e) {
        e.preventDefault();

        const paged = $(this).attr('data-action');
        cabling_search_ajax(ajaxSearch, paged);
    })

    $('.header-search').on('click', '.close', function (e) {
        e.preventDefault();
        $('.search-ajax').hide();
        $('.header-search .close').hide();
    });

    $(document).on('click', '.close-search', function (e) {
        const search_element = $('.search-ajax');
        search_element.empty();
        search_element.hide();

        $('.search-form input[type="text"]').val('');

        $('.header-search').hide()
    });

    //sign in ajax function
    $(document).on('submit', 'form[name=cabling_login_form]', function (event) {
        showLoading();

        event.preventDefault();

        const form = $(this);
        form.find('.woo-notice').remove();
        form.find('input[type="submit"]').prop('disabled', true);

        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cabling_login_ajax',
                data: form.serialize(),

            },
            success: function (data) {
                form.find('input[type="submit"]').prop('disabled', false);
                form.find('.login-username').prepend(data.mess);
                grecaptcha.reset();
                if (!data.error) {
                    if ($('input[name=is_reload]').length){
                        window.location.reload();
                    }
                    else {
                        window.location.href = data.redirect;
                    }
                }
                hideLoading();
            },
        })
            .fail(function () {
                grecaptcha.reset();
                form.find('input[type="submit"]').prop('disabled', false);
                hideLoading();
            });


        return false;
    });

    $(document).on('submit', 'form.lost_reset_password', function (event) {
        const form = $(this);
        form.find('.woo-notice').remove();
        if (form.find('input[name=woocommerce-lost-password-nonce]').length) {
            const response = form.find('[name=g-recaptcha-response]').val();

            if (response == '') {
                form.prepend('<div class="alert alert-danger d-flex align-items-center woo-notice" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i><span>Please verify the Captcha.</span></div>');
                grecaptcha.reset();
                return false;
            }
        }
        return true;
    });

    //woocommerce check out
    //cabling_move_shipping_btn();
    $('body').on('change', '#ship-to-different-address-checkbox', function (e) {
        //console.log( $(this).is(":checked") );
        if ($(this).is(":checked"))
            $('.woocommerce-billing-fields').addClass('has_shipping_address');
        else
            $('.woocommerce-billing-fields').removeClass('has_shipping_address');
    });

    //trigger product category widget
    $('body').find('.product-categories .cat-parent').append('<span class="show_more"></span>');

    $('body').on('click', '.show_more', function (e) {
        var parent = $(this).closest('.cat-parent');
        parent.toggleClass('active');
        parent.find('> .children').slideToggle();
    });

    $('.top-header').on('click', '.show-language', function (e) {
        $(e.delegateTarget).find('.cabling_language_list').toggle();
    });

    $('.more-if a[href^="www"]').each(function () {
        const oldUrl = $(this).attr("href");
        const newUrl = oldUrl.replace('www', 'https://www');
        $(this).attr("href", newUrl);
    });

})(jQuery);

function cabling_move_shipping_btn() {
    var j = jQuery.noConflict();

    if (j('#ship-to-different-address').length) {
        var button = j('#ship-to-different-address').clone();

        j('#ship-to-different-address').remove();
        j('#billing_address_1_field').after(button);
    }
}

function cabling_search_ajax(ajaxSearch, paged = 1) {
    const $ = jQuery.noConflict();
    const form = $('#search-ajax-form');
    setTimeout(function () {
        const key_search = $('.search-field').val();
        if (key_search.length < 3) {
            return false;
        } else if (key_search.length > 100) {
            alert('Maximum characters is 100!');
            return;
        }

        const search_element = $('.search-ajax');
        const search_result = $('#ajax-results');

        search_element.find('.search-text span').text(key_search);

        ajaxSearch = $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'search_ajax',
                key_search: key_search,
                paged: paged,
                data: form.serialize(),
            },
            beforeSend: function () {
                if (ajaxSearch != null) {
                    ajaxSearch.abort();
                }
                search_element.removeClass('no');
                //search_result.html('Searching...');
                search_element.show();
                showLoading();
            },
            success: function (response) {
                search_element.removeClass('no');
                search_result.html(response.data);
                search_result.append(response.pagination);
                search_element.show();
                $('.header-search .close').show();
                if (response.search_query == '') {
                    search_element.hide();
                }

                save_search_log(key_search);
                hideLoading();
            },
            error: function (err) {
                search_element.addClass('no');
                search_result.html('No results found, please search again.');
                search_element.show();
                hideLoading();
            }
        });
    }, 500);
}


// Default Billing Disable Fields
(function ($) {

    // JM 20230914
    $(document).on('input', 'input[name=password_2]', function () {
        const btnSubmit1 = $(this).closest('form').find('button[type="submit"]');
        const password = $(this).val();
        let strength = 0;
        btnSubmit1.prop('disabled', true);

        $(this).css('border-color', '#dc3545');
        // Check the length of the password
        if (password.length >= 8) {
            strength += 1;
        }

        // Check for both uppercase and lowercase characters
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
            strength += 1;
        }

        // Check for at least one digit
        if (/\d/.test(password)) {
            strength += 1;
        }

        // Check for at least one special character
        if (/[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/.test(password)) {
            strength += 1;
        }

        // Display the password strength
        let strengthText = "";
        switch (strength) {
            case 0:
            case 1:
                strengthText = "Weak";
                break;
            case 2:
                strengthText = "Moderate";
                break;
            case 3:
                strengthText = "Strong";
                break;
            case 4:
                strengthText = "Very Strong";
                btnSubmit1.prop('disabled', false);
                $(this).css('border-color', '#28a745');
                break;
        }

        if ($(this).is('input[name="password_2"]')) {
            if ($(this).val() === $('input[name="password_1"]').val()) {
                $(this).css('border-color', '#28a745');
                btnSubmit1.prop('disabled', false);
            } else {
                $(this).css('border-color', '#dc3545');
                btnSubmit1.prop('disabled', true);
            }
        }
    })

    $(document).on('submit', '#share-email-form', function (event) {
        event.preventDefault();

        //const response = grecaptcha.getResponse();

        const form = $(this);
        form.find('.woo-notice').remove();
        form.find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: CABLING.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cabling_share_page_email_ajax',
                data: form.serialize(),
                nonce: CABLING.nonce,
            },
            success: function (response) {
                gtag('event', 'Share_Page_Completed');
                if (response.success) {
                    form.find('button[type="submit"]').prop('disabled', false);
                    form.prepend(response.data.data);

                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);

                } else {
                    form.prepend(`<div class="woocommerce-error woo-notice" role="alert">${response.data}</div>`);
                    grecaptcha.reset();
                    form.find('input[type="submit"]').prop('disabled', false);
                    return false;
                }

            },
        })
            .fail(function () {
                console.log("error");
            });


        return false;
    });

    $(document).on('click', '.show-email-share .email-share', function () {
        gtag('event', 'Share_Page_Engagement');
        $('#emailShareModal').modal('show');
        return false;
    })

    $(".product-variation-table").each(function () {
        const $table = $(this);
        const $rows = $table.find("tr");
        if ($rows.length === 0) return;

        const columnCount = $rows.first().find("td, th").length;

        for (let colIndex = columnCount - 1; colIndex >= 0; colIndex--) {
            let shouldHide = true;

            $rows.slice(1).each(function () { // Skip header row
                const $cell = $(this).find("td, th").eq(colIndex);
                const text = $cell.text().trim();
                if (text && text !== "*") {
                    shouldHide = false;
                    return false; // break out of .each()
                }
            });

            if (shouldHide) {
                $rows.each(function () {
                    const $cell = $(this).find("td, th").eq(colIndex);
                    $cell.hide();
                });
            }
        }
    });

})(jQuery);

//navigation
(function ($) {
    add_mobile_class_to_navigation();

    $(window).resize(function () {
        add_mobile_class_to_navigation();
    });

})(jQuery);

function add_mobile_class_to_navigation() {
    if (jQuery(window).width() < 992) {
        jQuery("body").find('.main-navigation').addClass('collapse');
    }
}

function save_search_log(text) {
    jQuery.ajax({
        url: CABLING.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'cabling_save_search_log_ajax',
            data: text
        }
    });
}

function generateCaptchaElement(id) {
    const recaptcha_element = jQuery(`#${id}`);
    const sitekey = recaptcha_element.attr('data-sitekey');
    grecaptcha.render(id, {
        'sitekey': sitekey,
    });
}
