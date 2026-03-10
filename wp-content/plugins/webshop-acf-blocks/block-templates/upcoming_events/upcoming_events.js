(function ($) {
    if ($('.main-carousel').length) {
        const $carousel = $('.main-carousel').flickity({
            cellAlign: 'left',
            draggable: true,
            pageDots: false,
            freeScroll: true,
            wrapAround: true,
            autoPlay: true,
            prevNextButtons: false,
        });

        // Previous button
        $('.custom-prev-button').on('click', function () {
            $carousel.flickity('previous');
        });

        // Next button
        $('.custom-next-button').on('click', function () {
            $carousel.flickity('next');
        });
    }

    // Event modal handling
    $(document).on('click', '.event-card-item', function(e) {
        e.preventDefault();

        const eventId = $(this).data('event-id');

        $('#event-modal-content').hide();
        $('#loading-spinner').show();

        $.ajax({
            url: gi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'gi_get_event_details',
                event_id: eventId,
                nonce: gi_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Hide loading spinner and show content
                    $('#loading-spinner').hide();
                    $('#event-modal-content').html(response.data).show();
                } else {
                    // Show error message
                    $('#loading-spinner').hide();
                    $('#event-modal-content').html(`<div class="alert alert-danger">${response.data.message}</div>`).show();
                }
            },
            error: function() {
                // Show error message
                $('#loading-spinner').hide();
                $('#event-modal-content').html(`<div class="alert alert-danger">${gi_ajax.i18n.error}</div>`).show();
            }
        });
    });

})(jQuery);
