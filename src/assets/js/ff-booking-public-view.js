jQuery(document).ready(function ($) {
    let datepickerElm = jQuery('#ffb_view_picker');
    let targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;
    const data = window.ff_booking_page_vars;


    function initBookingControl() {
        let booking_type = data.dates_data.booking_type;
        targetFp.config.dateFormat = data.dates_data.date_format

        // disable full booked
        let disabledDates = data.dates_data.disabled_dates
        if (!disabledDates) {
            disabledDates = []
        }
        targetFp.config.disable = disabledDates;

        //disabled week off days
        let offDays = data.dates_data.weekend_days;
        var formatDays = offDays.map(function (x) {
            return parseInt(x);
        });
        if (!formatDays) {
            formatDays = [];
        }

        function disabledWeekDays(date) {
            return (formatDays.includes(date.getDay()));
        }

        targetFp.config.disable.push(disabledWeekDays);

        //disable max future days
        targetFp.set('minDate', data.dates_data.min_date);
        targetFp.set('maxDate', data.dates_data.max_date);

        targetFp.jumpToDate(data.dates_data.start_date);
    }

    function setDateListener() {

        let that = this;
        targetFp.config.onChange.push(function (selectedDates, dateStr, instance) {
            setInput();
            if (data.dates_data.booking_type == 'date_slot') {
                //set input value date
                let formattedDate = targetFp.formatDate(targetFp.selectedDates[0], "Y-m-d")
                datepickerElm.val(formattedDate + ' ' + '00:00:00')
            } else if (data.dates_data.booking_type == 'time_slot') {
                let selectedDate = targetFp.formatDate(selectedDates[0], "Y-m-d")
                getTimeSlots(targetFp, selectedDate)
            }

        });

    }

    function getTimeSlots(targetFp, selectedDate) {

        let $slot = jQuery(".ff-time-slot-container")

        let that = this;
        jQuery.ajaxSetup({
            data: {
                ffs_booking_public_nonce: window.ff_booking_page_vars.nonce
            },
            beforeSend: function () {
                let loader = '<div class="ff-booking-loader"></div>'
                let $details = jQuery(".ff-time-slot-details");
                $details.html(loader)
            },
        });
        jQuery.post(window.ff_booking_page_vars.ajaxUrl, {
            action: 'handle_booking_frontend_endpoint',
            route: 'get_time_slots_booking_page',
            selectedDate: selectedDate,
            bookingHash: window.ff_booking_page_vars.booking_hash

        })
            .then(response => {
                if (response.success == true) {
                    generateTimeSlots(response)
                    return;
                }
                $slot.addClass('ffb_error');

                $slot.html(response.message)
            })
            .catch(errors => {
                $slot.addClass('ffb_error');
                if (errors.responseJSON) {
                    $slot.html(errors.responseJSON.message)

                } else if (errors.responseJSON.data) {
                    $slot.html(errors.responseJSON.data.message)

                } else {
                    $slot.html('Error. Please try again')
                }
            })
            .always(() => {
                jQuery('.ff-booking-loader').remove();
            });
    }

    function generateTimeSlots(res) {

        let $slot = jQuery(".ff-time-slot-container")

        let slots = '';
        jQuery.each(res.data.time_slots, function (index, slot) {
            let booked = '';
            let input = '';
            let remainingSlot = '';
            if (slot.remaining_slot) {
                remainingSlot = `<div class ='ff-booking-slot-rem'> ${slot.remaining_slot} </div>`;
            }
            if (slot.booked && slot.booked === true) {
                booked = ' ff-booked-slot ff-el-booking-tootltip';
            } else {
                input = '<input type="radio" name="input_radio" class="ff-el-form-check-input ff-el-booking-slot " value="' + slot.value + '" id="input_radio_ff_booking_' + index + '"> ';
            }
            slots += '<div data-content="Slot Booked" class=" ff-el-form-check ff-el-form-check-   ' + booked + ' ">' +
                '<label class="ff-el-form-check-label" for="input_radio_ff_booking_' + index + '">' +
                '<span>' + slot.label + remainingSlot + '</span>' +
                input +
                '</label>' +
                '</div>';
        });

        let slotHtml = '<div class="ff-el-group  ff_list_buttons">' +
            '<div class="ff-el-input--label ">' +
            '<label>Select Time</label> ' +
            '</div>' +
            '<div class="ff-el-input--content">' +
            slots +
            '</div>' +
            '</div>';
        $slot.html(slotHtml);

        // this.setInputDateTime();


    }

    function setInput() {
        let $details = jQuery(".ff-time-slot-details");
        $details.html('');
        jQuery(document).on('change', '.ff-time-slot-container input', (e) => {

            const $element = jQuery(e.target);
            let time = $element.val();

            let date = targetFp.formatDate(targetFp.selectedDates[0], "l d M,Y")

            let html = `<span> ${time}</b>  on <b>${date}</b></span>`;
            $details.html(html)
            //set input value date+time
            let formattedDate = targetFp.formatDate(targetFp.selectedDates[0], "Y-m-d")
            datepickerElm.val(formattedDate + ' ' + time)
        });
    }

    function initCheckables() {
        $(document).on('change', '.ff-el-form-check input[type=radio]', function () {
            if ($(this).is(':checked')) {
                $(this).closest('.ff-el-input--content').find('.ff-el-form-check').removeClass('ff_item_selected');
                $(this).closest('.ff-el-form-check').addClass('ff_item_selected');
            }
        });
    }

    function validate() {
        let $details = jQuery(".ff-time-slot-details");

        if (!datepickerElm.val()) {
            $details.html('<span class="ffb_error">Please select a Date</span> ');
            return false;
        }
        if (data.dates_data.booking_type == 'time_slot' && !$('.ff-el-booking-slot').is(':checked')) {
            $details.html('<span class="ffb_error">Please select a time</span> ');
            return false;
        }

        return true;
    }

    function submit() {
        $('.ffb-submit-bttn').on('click', function (e) {
            e.preventDefault();
            if (!validate()) {
                return;
            }
            jQuery.post(window.ff_booking_page_vars.ajaxUrl, {
                action: 'handle_booking_frontend_endpoint',
                route: 'reschedule_booking',
                dateTime: datepickerElm.val(),
                reason: $('#ffs-reason-text').val(),
                bookingHash: data.booking_hash

            })
                .then(response => {
                    let $details = $(".ff-time-slot-details");
                    $details.html('');
                    if (response.success == true) {
                        let $response = $(".ff_booking_content.ffb_form");
                        $response.html('<span class="ffb_sucess">' + response.data.message + '</span> ');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                        return;
                    }
                    $details.html('<span class="ffb_sucess">' + response.message + '</span> ');


                })
                .fail((errors) => {
                    console.log(errors)
                })
                .always(() => {
                    $('.ff-booking-loader').remove();
                });
        });
    }

    initBookingControl();
    setDateListener();
    initCheckables();
    setInput();
    submit();
    //show form
    $('.ffb_bttns.resched-bttn').on('click', function (e) {
        e.preventDefault();
        $('.ff_booking_content.ffb_form').slideDown().css('display', 'flex');
        $('.user_cancel_confirm').hide();
    })
    //show cancel confirm
    $('.ffb_bttns.cancel-bttn').on('click', function (e) {
        e.preventDefault();
        $('.user_cancel_confirm').show();
        $('.ff_booking_content.ffb_form').hide();
    })
    //close cancel prompt
    $('.ffb_bttns.close-confirm').on('click', function (e) {
        e.preventDefault();
        $('.user_cancel_confirm').hide();
    })
    //confirm cancel request
    $(document).on('click', '.cancel-confirm', (e) => {
        e.preventDefault();
        let loader = '<div class="ff-booking-loader"></div>'
        $(this).html(loader)

        jQuery.post(window.ff_booking_page_vars.ajaxUrl, {
            action: 'handle_booking_frontend_endpoint',
            route: 'cancel_booking',
            bookingHash: data.booking_hash,
            ffs_booking_public_nonce: window.ff_booking_page_vars.nonce
        })
            .then(response => {
                if (response.success == true) {
                    $('.user_cancel_confirm').remove();
                    $('.ff_booking_info.with-border').append('<div class="ffb_sucess">' + response.data.message + '</div> ');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else
                    $('.ff_booking_info.with-border').append('<span class=" ffb_error">' + response.message + '</span> ');
            })
            .fail((errors) => {
                console.log(errors)
            })
            .always(() => {
                $('.ff-booking-loader').remove();
            });
    })


});
