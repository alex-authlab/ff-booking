jQuery(document).ready(function ($) {
    // show confirm prompt on status change
    $('.ffs_booking_status').on('change', function (e) {
        let booking_id = $(this).data('booking_id');
        let $confirm = $(this).closest('.ff_booking').find('.ffs_booking_action_confirmation');
        $confirm.slideDown()
        $confirm.find('.ffs_confirm').attr('data-booking_id', booking_id)
        $confirm.find('.ffs_confirm').attr('data-booking_status', $(this).val())
    })
    // toggle details on click
    let open = true;
    $(document).on("click", ".ffs_booking_btns .ffs_details", function () {
        const $dom = $(this).closest('.ff_booking').find('.ffs_booking_details_text');
        if (open) {
            $(this).find('.ffs_details_icon').text('-');

        } else {
            $(this).find('.ffs_details_icon').text('+');

        }
        open = !open;
        $dom.slideToggle()
    })
    // update status on confirm
    $(document).on("click", ".ffs_booking_btns .ffs_confirm", function () {
        let bookingId = $(this).data('booking_id');
        let status = $(this).data('booking_status');
        const $responseDom = $(this).closest('.ffs_booking_action_confirmation').find('.ffs_message_notices');
        $responseDom.html('Please wait...');

        updateBooking(bookingId, status, $(this), (message, status) => {

            if (status != 'success') {
                $responseDom.addClass('ffb_error')
            }
            $responseDom.html(message);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    })
    //close confirm prompt
    $('.ffs_booking_btns .ffs_close').on('click', function (e) {
        let $confirm = $(this).closest('.ff_booking').find('.ffs_booking_action_confirmation');
        $confirm.find('.ffs_confirm').removeAttr('data-booking_id')
        $confirm.find('.ffs_confirm').removeAttr('data-booking_status')
        $(this).closest('.ffs_booking_action_confirmation').slideUp();
    })
    //send update req
    function updateBooking(bookingId, status, $elm, callback) {

        $elm.prop("disabled", true);
        jQuery.post(window.ffs_provider_vars.ajaxUrl, {
            booking_id: bookingId,
            status: status,
            route: 'update_provider_booking',
            action: 'handle_booking_frontend_endpoint',
            ffs_booking_public_nonce: window.ffs_provider_vars.nonce
        })
            .then(response => {
                callback(response.data.message, 'success');
            })
            .catch(errors => {
                if (!errors.responseJSON) {
                    callback(errors.responseText);
                } else if (errors.responseJSON.data) {
                    callback(errors.responseJSON.data.message);
                } else {
                    callback('Error. Please try again');
                }
            })
            .always(() => {
                $elm.prop("disabled", false);
            });
    }

})
