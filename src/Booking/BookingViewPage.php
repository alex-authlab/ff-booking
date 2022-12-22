<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FluentForm\Framework\Helpers\ArrayHelper;

class BookingViewPage
{
    public function init()
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
        $data = $this->getBookingDatabyHash($bookingHash);
        if (!$data) {
            die('Sorry! Invalid URL');
        }
        $this->enqueueAsset($data);

        $pageSettings = [
            'status'           => 'yes',
            'logo'             => '',
            'title'            => 'FF Simple Booking',
            'description'      => '',
            'color_schema'     => '#4286c4',
            'custom_color'     => 'none',
            'featured_image'   => '',
            'background_image' => '',
            'design_style'=>'modern'
        ];

        $pageVars = apply_filters('ff_booking_view_vars', [
            'title'           => __('FF Simple Booking', 'ff-simple-booking'),
            'settings'        => $pageSettings,
            'bg_color'        => $pageSettings['custom_color'],
            'has_header'      => false,
            'data'             => $data,
        ]);

        $this->loadBookingView($pageVars);
    }
    public function loadBookingView($pageVars)
    {
        add_filter('pre_get_document_title', function ($title) use ($pageVars) {
            $separator = apply_filters('document_title_separator', '-');
            return $pageVars['title'] . ' ' . $separator . ' ' . get_bloginfo('name', 'display');
        });

        echo BookingHelper::loadView('booking_page_view', $pageVars);
        exit(200);
    }


    /**
     * @param $bookingHash
     * @return array|false
     */
    public function getBookingDatabyHash($bookingHash)
    {
        $data = (new BookingModel())->getBooking(['booking_hash' => $bookingHash]);
        if (!$data) {
            return false;
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
            'booking_type',
            'assigned_user'
        ]);

        $returnData['allowProviderReschedule'] = BookingHelper::getSettingsByKey('allow_provider_reschedule');
        $returnData['isProviderLoggedin'] = get_current_user_id() == $returnData['assigned_user'];
        $returnData['allowUserCancel'] = ArrayHelper::get($returnData, 'allow_user_cancel') == 'yes';
        $returnData['allowUserReschedule'] = ArrayHelper::get($returnData, 'allow_user_reschedule') == 'yes';
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
        add_action('wp_enqueue_scripts', function () use ($app, $data) {
            wp_enqueue_style('ffs_booking_view', FF_BOOKING_DIR_URL . 'public/css/ffs_booking_page.css', [], FF_BOOKING_VER);
            wp_enqueue_style('flatpickr', $app->publicUrl('libs/flatpickr/flatpickr.min.css'));
            wp_enqueue_script('flatpickr', $app->publicUrl('libs/flatpickr/flatpickr.js'), [], false, true);
            wp_enqueue_script('ff-booking-public-view');
            $datesData = $this->getDatesData($data);

            $bookingViewVars = [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'dates_data' => $datesData,
                'booking_hash' => sanitize_text_field($_REQUEST['ff_simple_booking']),
                'nonce' => wp_create_nonce('ffs_booking_public_nonce'),
            ];
            wp_localize_script('ff-booking-public-view', 'ff_booking_page_vars', $bookingViewVars);
        });
    }
}
