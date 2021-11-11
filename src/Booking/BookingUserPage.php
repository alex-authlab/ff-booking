<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FluentForm\Framework\Helpers\ArrayHelper;

class BookingUserPage
{

    public function __construct()
    {
        add_action('wp', [$this, 'render'], 100);
    }

    public function render()
    {
        $paramKey = 'ff_simple_booking';
        if ((isset($_GET[$paramKey])) && !wp_doing_ajax()) {
            $bookingHash = sanitize_text_field(ArrayHelper::get($_REQUEST, $paramKey));
            $this->renderPage($bookingHash);
        }
    }

    private function renderPage($bookingHash)
    {
        $data = $this->getData($bookingHash);
        $allowUserCancel = ArrayHelper::get($data, 'allow_user_cancel') == 'yes';
        $allowUserReschedule = ArrayHelper::get($data, 'allow_user_reschedule') == 'yes';
        $this->enqueueAsset($data);

        extract($data);
        ob_start();

        include_once(FF_BOOKINGDIR_PATH . 'src/Booking/view/user-booking-view.php');

        $renderOutput = ltrim(ob_get_clean());

        echo $renderOutput;
        exit(200);
    }

    /**
     * @param $bookingHash
     * @return array|false
     */
    private function getData($bookingHash)
    {
        $data = (new BookingModel())->getBooking(['booking_hash' => $bookingHash]);
        if (!$data) {
            wp_redirect(home_url());
            exit();
        }
        $returnData = ArrayHelper::only($data, [
            'id',
            'service',
            'provider',
            'booking_date',
            'booking_time',
            'booking_status',
            'duration',
            'service_id',
            'provider_id',
            'form_id',
            'policy',
            'description',
            'allow_user_cancel',
            'allow_user_reschedule',
            'booking_type'
        ]);

//        vdd($returnData);
        return $returnData;
    }

    public function getDatesData($data)
    {
        $datesData = (new DateTimeHandler(
            ArrayHelper::get($data, 'service_id'),
            ArrayHelper::get($data, 'provider_id'),
            ArrayHelper::get($data, 'form_id'),
        ))->getDatesData();
        $dateFormat = BookingHelper::getBookingFieldDateFormat(ArrayHelper::get($data, 'form_id'));

        if (!$dateFormat) {
            $dateFormat = 'm/d/Y';
        }
        $datesData['date_format'] = $dateFormat;
        return $datesData;
    }

    private function enqueueAsset($data)
    {
        $app = wpFluentForm();
        wp_enqueue_script("jquery");

        wp_enqueue_style('flatpickr', $app->publicUrl('libs/flatpickr/flatpickr.min.css'));
        wp_enqueue_script('flatpickr', $app->publicUrl('libs/flatpickr/flatpickr.js'), [], false, true);

        $datesData = $this->getDatesData($data);

        $bookingViewVars = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'dates_data' => $datesData,
            'booking_hash' => sanitize_text_field($_REQUEST['ff_simple_booking'])
        ];
        wp_enqueue_script('ff-booking-public-view');
        wp_localize_script('ff-booking-public-view', 'ff_booking_page_vars', $bookingViewVars);
    }

}
