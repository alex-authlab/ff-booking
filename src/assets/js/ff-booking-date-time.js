jQuery(document).ready(function ($) {
    $('form.ff_has_booking').each(function () {
        const $form = $(this);
        $form.on('fluentform_init_single', function (event, instance) {
            const booking = new FF_booking_handler($form, instance);
            booking.init();
        });
    });

}(jQuery));


class FF_booking_handler {
    constructor($form, instance) {
        let formId = instance.settings.id;
        this.$form = $form;
        this.formInstance = instance;
        this.formId = formId;
        this.serviceElmClass = '.ff_booking_service';
        this.providerElmClass = '.ff_booking_provider';
        this.dateTimeElmClass = '.ff-booking-date-time';
        this.booking_type ='';
    }

    init() {
        this.$form.find(this.serviceElmClass).on('change', (event) => {
            const $element = jQuery(event.target);
            this.getProviderList($element.val());
        });
        this.initToolTip();


        this.setProviderListener();
        //initial datepicker listener
        setTimeout(() => {
            let datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));
            let targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;
            let that = this;
            targetFp.config.onChange.push(function () {

                let providerElm = jQuery(that.$form.find(that.providerElmClass));
                let serviceElm = jQuery(that.$form.find(that.serviceElmClass));
                if (!!serviceElm.val() === false || !!providerElm.val() === false) {
                    let $details = that.$form.find(".ff-time-slot-details")
                    if (!$details.length) {
                        jQuery('<div/>', {
                            class: 'ff-time-slot-details'
                        }).appendTo(that.$form.find('.ff-booking-container'));
                        $details = jQuery('.ff-time-slot-details');
                        let html = `<span>Please Select Service & Provider</span>`;
                        $details.html(html)
                    }
                }
            })

        }, 1000);

    }

    initToolTip() {
        jQuery(document).on('mouseenter', '.ff-el-booking-tootltip ', (e) => {

            const $element = jQuery(e.target);
            const content = $element.data('content');

            let $popContent = jQuery('.ff-el-pop-content');
            if (!$popContent.length) {
                jQuery('<div/>', {
                    class: 'ff-el-pop-content'
                }).appendTo($element);
                $popContent = jQuery('.ff-el-pop-content');
            }
            $popContent.css('bottom', '-70%')
            $popContent.html(content);
            setTimeout(() => {
                $popContent.remove()

            }, 1500);

        });

    }

    getProviderList(serviceId) {
        let providerElm = jQuery(this.$form.find(this.providerElmClass));
        providerElm.html(jQuery('<option>', {
            value: '',
            text: "Please wait Loading.."
        }));

        jQuery.post(window.ff_booking_date_time_vars.ajaxUrl, {
            action: 'handle_booking_frontend_endpoint',
            route: 'get_service_provider',
            service_id: serviceId,
            form_id: this.formId
        })
            .then(res => {
                this.setProvidersList(res, providerElm);
            })
            .fail((errors) => {
                console.log(errors)
            })
            .always(() => {
                this.setDateListener();
                this.resetDateTime();
            });
    }

    setProvidersList(res, providerElm) {
        let $details = this.$form.find(".ff-time-slot-details");
        $details.remove();
        providerElm.empty();
        if (res.success == true && Object.keys(res).length > 0) {
            jQuery.each(res.data.providers, function (id, title) {
                jQuery("<option/>", {
                    value: id,
                    text: title
                }).appendTo(providerElm);
            });
            providerElm.find("option:first").attr('selected', 'selected');
            this.getDateTime();
        } else {
            providerElm.html(jQuery('<option>', {
                value: '',
                text: "No service found !"
            }));
        }
    }

    setProviderListener() {
        this.$form.find(this.providerElmClass).on('change', (event) => {
            this.resetDateTime();
            let serviceElm = jQuery(this.$form.find(this.serviceElmClass));
            if (!!serviceElm.val() === false) {
                return;
            }
            this.getDateTime();
        });
    }

    getDateTime() {
        let datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));
        let targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;

        let providerElm = jQuery(this.$form.find(this.providerElmClass));
        let serviceElm = jQuery(this.$form.find(this.serviceElmClass));
        let that = this;

        let $maskLoader = that.$form.find(".ff-booking-loading-mask")
        if (!$maskLoader.length) {
            datepickerElm.parent().find('.flatpickr-innerContainer').append('<div class="ff-booking-loading-mask"></div>')
            $maskLoader = jQuery('.ff-booking-loading-mask');
            let loader = '<div class="ff-booking-loader"></div>'
            $maskLoader.html(loader);
        }
        jQuery.post(window.ff_booking_date_time_vars.ajaxUrl, {
            action: 'handle_booking_frontend_endpoint',
            route: 'get_dates',
            service_id: serviceElm.val(),
            provider_id: providerElm.val(),
            form_id: this.formId
        })
            .then(res => {
                let $maskLoader = that.$form.find(".ff-booking-loading-mask")
                $maskLoader.remove();
                if (res.success == true) {
                    that.booking_type = res.data.dates_data.booking_type;
                    // disable fullbooked
                    let disabledDates = res.data.dates_data.disabled_dates
                    if (!disabledDates) {
                        disabledDates = []
                    }

                    //disabled week off days
                    let offDays = res.data.dates_data.weekend_days;
                    var formatDays = offDays.map(function (x) {
                        return parseInt(x);
                    });
                    if (!formatDays) {
                        formatDays = [];
                    }

                    function disabledWeekDays(date) {
                        return (formatDays.includes(date.getDay()));
                    }

                    targetFp.config.disable = disabledDates;
                    targetFp.config.disable.push(disabledWeekDays);
                    //disable max future days
                    targetFp.set('minDate', res.data.dates_data.min_date);
                    targetFp.set('maxDate', res.data.dates_data.max_date);

                    targetFp.jumpToDate(res.data.dates_data.start_date);
                }
            })
            .fail((errors) => {
                console.log(errors)
            })
            .always(() => {
                let $maskLoader = that.$form.find(".ff-booking-loading-mask")
                $maskLoader.remove();
            });
    }


    setDateListener() {
        let datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));
        let targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;
        let that = this;
        targetFp.config.onChange.push(function (selectedDates, dateStr, instance) {
            if(that.booking_type == 'date_slot'){
                //set input value date
                let formattedDate = targetFp.formatDate(targetFp.selectedDates[0], "Y-m-d")
                datepickerElm.val(formattedDate + ' ' + '00:00:00')
            }else if(that.booking_type == 'time_slot'){
                let selectedDate = targetFp.formatDate(selectedDates[0], "Y-m-d")
                that.getTimeSlots(targetFp, selectedDate)
            }

        });

    }

    getTimeSlots(targetFp, selectedDate) {
        let that = this;
        jQuery.ajaxSetup({
            data: {
                //todo nonce
            },
            beforeSend: function () {
                let $slot = that.$form.find(".ff-time-slot-container")
                if (!$slot.length) {
                    jQuery('<div/>', {
                        class: 'ff-time-slot-container'
                    }).appendTo(that.$form.find('.ff-booking-container'));
                    $slot = jQuery('.ff-time-slot-container');
                    let loader = '<div class="ff-booking-loader"></div>'
                    $slot.html(loader);
                }
            },
        });
        let providerElm = jQuery(this.$form.find(this.providerElmClass));
        let serviceElm = jQuery(this.$form.find(this.serviceElmClass));
        jQuery.get(window.ff_booking_date_time_vars.ajaxUrl, {
            action: 'handle_booking_frontend_endpoint',
            route: 'get_time_slots',
            service_id: serviceElm.val(),
            provider_id: providerElm.val(),
            form_id: this.formId,
            date: selectedDate
        })
            .then(response => {
                if (response.success == true) {
                    this.generateTimeSlots(response)
                    return;
                }
                jQuery('.ff-booking-loader').remove();
                let $slot = this.$form.find(".ff-time-slot-container")
                if (!$slot.length) {
                    jQuery('<div/>', {
                        class: 'ff-time-slot-container '
                    }).appendTo(this.$form.find('.ff-booking-container'));
                    $slot = jQuery('.ff-time-slot-container');
                }
                $slot.addClass('error text-danger');
                $slot.html(response.message)
            })
            .fail((errors) => {
                console.log(errors)
            })
            .always(() => {
                jQuery('.ff-booking-loader').remove();
            });
    }

    generateTimeSlots(res) {

        let $slot = this.$form.find(".ff-time-slot-container")
        if (!$slot.length) {
            jQuery('<div/>', {
                class: 'ff-time-slot-container'
            }).appendTo(this.$form.find('.ff-booking-container'));
            $slot = jQuery('.ff-time-slot-container');
        }
        let slots = '';
        jQuery.each(res.data.time_slots, function (index, slot) {
            let booked = '';
            let input = '';
            let remainingSlot = '';
            if(slot.remaining_slot){
                remainingSlot = `<div class ='ff-booking-slot-rem'> ${slot.remaining_slot} </div>`;
            }
            if (slot.booked && slot.booked === true) {
                booked = ' ff-booked-slot ff-el-booking-tootltip';
            } else {
                input = '<input type="radio" name="input_radio" class="ff-el-form-check-input ff-el-booking-slot " value="' + slot.value + '" id="input_radio_ff_booking_' + index + '"> ';
            }
            slots += '<div data-content="Slot Booked" class=" ff-el-form-check ff-el-form-check-   ' + booked + ' ">' +
                '<label class="ff-el-form-check-label" for="input_radio_ff_booking_' + index + '">' +
                '<span>' + slot.label + remainingSlot  + '</span>' +
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

        this.setInputDateTime();


    }

    setInputDateTime() {
        let $details = this.$form.find(".ff-time-slot-details");
        if (!$details.length) {
            jQuery('<div/>', {
                class: 'ff-time-slot-details'
            }).appendTo(this.$form.find('.ff-booking-container'));
            $details = jQuery('.ff-time-slot-details');
        }
        jQuery(document).on('change', '.ff-time-slot-container input', (e) => {
            const $element = jQuery(e.target);
            let time = $element.val();

            let providerElm = jQuery(this.$form.find(this.providerElmClass));
            let temp = document.getElementById(providerElm.attr('id'));
            let provider = temp.options[temp.selectedIndex].innerHTML;


            let serviceElm = jQuery(this.$form.find(this.serviceElmClass));
            temp = document.getElementById(serviceElm.attr('id'));
            let service = temp.options[temp.selectedIndex].innerHTML;

            let datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));
            let targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;
            let date = targetFp.formatDate(targetFp.selectedDates[0], "l d M,Y")

            let html = `<span>Selected a booking for <b>${service}</b>  by <b>${provider}</b> at <b>${time}</b> am on <b>${date}</b></span>`;
            $details.html(html)
            //set input value date+time
            let formattedDate = targetFp.formatDate(targetFp.selectedDates[0], "Y-m-d")
            datepickerElm.val(formattedDate + ' ' + time)
        });
    }

    resetDateTime() {
        let $slot = this.$form.find(".ff-time-slot-container, .ff-booking-loading-mask")
        if ($slot.length) {
            $slot.remove()
        }
        let datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));
        let targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;
        if (!datepickerElm.val()) {
            return;
        }
        targetFp.clear();
    }
}


