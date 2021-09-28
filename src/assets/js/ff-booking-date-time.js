jQuery(document).ready(function ($) {
    $('.ff-booking-container').each((index, item) => {
        const $container = $(item);
        const elementId = $container.find(".ff-booking-date-time").attr('id');
        const input = document.getElementById(elementId);


        class FF_booking_handler {
            constructor(element, container) {
                this.element = element
                this.container = container
                this.init()
                this.generateTimeSlot()
            }

            init() {
                $('#' + this.element).on('change', (e) => {
                    e.preventDefault();
                    this.generateTimeSlot()
                    console.log('ok')
                });
            }

            getData() {

            }

            generateTimeSlot() {
                let $slot = this.container.find(".ff-time-slot-container")
                if (!$slot.length) {
                    $('<div/>', {
                        class: 'ff-time-slot-container'
                    }).appendTo(this.container);
                    $slot = $('.ff-time-slot-container');

                }
                jQuery.post(window.ff_booking_date_time_vars.ajaxUrl, {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'get_slots',
                    service_id: '',
                    date: $('#' + this.element).val(),
                    form_id: window.ff_booking_date_time_vars.formId
                })
                .then(response => {
                    if(response.success == true){
                        $slot.html(response.data.html);
                    }
                })
                .always(() =>{
                        jQuery('.booking-loader').remove()
                    }
                );
            }
        }

        new FF_booking_handler(elementId, $container);


        console.log(elementId)

    });
}(jQuery));
