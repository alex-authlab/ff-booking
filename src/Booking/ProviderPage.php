<?php

namespace FF_Booking\Booking;

class ProviderPage
{

    public function init()
    {
        add_shortcode('ff_simple_booking', array($this, 'registerShortcode'));
    }

    public function registerShortcode()
    {
        $userId = get_current_user_id();
        if (!$userId) {
            return '';
        }
        wp_enqueue_script(
            'ffs_booking_provider_js',
            FF_BOOKING_DIR_URL . 'public/js/ffs_booking_provider.js',
            ['jquery'],
            FF_BOOKING_VER,
            true
        );
        wp_enqueue_style(
            'ffs_booking_provider_css',
            FF_BOOKING_DIR_URL . 'public/css/ffs_booking_provider.css',
            FF_BOOKING_VER
        );
        wp_localize_script('ffs_booking_provider_js', 'ffs_provider_vars', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ff_simple_booking')
        ]);

        return  $this->getBookingsHtml($userId);

    }

    private function getViewConfig()
    {
        $wpDateTimeFormat = get_option('time_format') . ' ' . get_option('date_format');
        return apply_filters('ffs_provider_view_config', [
            'new_tab' => false,
            'view_text' => __('View', FF_BOOKING_SLUG),
            'base_url' => site_url(),
            'time_format' => get_option('time_format'),
            'date_format' => get_option('date_format'),
            'date_time_format' => $wpDateTimeFormat,
            'booking_title' => __('Bookings List', FF_BOOKING_SLUG),
            'confirm_heading' => __('Are you sure you change this booking status? ', FF_BOOKING_SLUG),
            'confirm_btn' => __('Yes', FF_BOOKING_SLUG),
            'reschulde_btn' => __('Reschedule', FF_BOOKING_SLUG),
            'close' => __('Cancel', FF_BOOKING_SLUG),
            'get_filters' => array(
                'all' => __('All', FF_BOOKING_SLUG),
                'next' => __('Next Upcoming', FF_BOOKING_SLUG),
                'pending' => __('Pending', FF_BOOKING_SLUG),
                'past' => __('Past', FF_BOOKING_SLUG),
            ),
            'booking_status' => BookingHelper::bookingStatuses(),
        ]);
    }

    private function getBookingsHtml($userId)
    {
        $viewConfig = $this->getViewConfig();
        $filterStatus = 'all';
        if (isset($_REQUEST['status'])) {
            $filterStatus = sanitize_text_field($_REQUEST['status']);
        }
        $bookings = \FF_Booking\Booking\Models\BookingModel::getBookingsByProvider($userId, ['status' => $filterStatus]
        );
        $viewConfig['filterStatus'] = $filterStatus;
        $bookingHtml = \FF_Booking\Booking\BookingHelper::loadView('providers_bookings', [
            'bookings' => $this->groupBy('formatted_date', $bookings),
            'config' => $viewConfig,
        ]);
        $html = '<div class="ff_bookings_wrapper">';
        if (!empty($viewConfig['booking_title'])) {
            $html .= '<h3>' . $viewConfig['booking_title'] . '</h3>';
        }

        return $html . $bookingHtml . '</div>';
    }

    public function groupBy($key, $data)
    {
        $result = array();
        $data = (array)$data;
        foreach ($data as $val) {
            $val = (array)$val;
            if (array_key_exists($key, $val)) {
                $result[$val[$key]][] = $val;
            } else {
                $result[""][] = $val;
            }
        }
        return $result;
    }


}
