<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Addons\GoogleCalendarController;
use FluentForm\Framework\Helpers\ArrayHelper;

class ProviderPage
{
    public function init()
    {
        add_shortcode('ff_simple_booking', array($this, 'registerShortcode'));
        (new GoogleCalendarController())->init();
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
            'nonce'   => wp_create_nonce('ffs_booking_public_nonce')
        ]);

        return $this->renderHtml($userId);
    }

    private function getViewConfig()
    {
        global $post;
        $pageId = $post->ID;

        $urlBase = false;
        if ($pageId) {
            $urlBase = get_permalink($pageId);
        }

        if (!$urlBase) {
            $urlBase = add_query_arg([
                'ff_booking' => 'view',
                'route'              => 'bookings'
            ], site_url('index.php'));
        }

        if (!strpos($urlBase, '?')) {
            $urlBase .= '?';
        } else {
            $urlBase .= '&';
        }
        $wpDateTimeFormat = get_option('time_format') . ' ' . get_option('date_format');
        return apply_filters('ffs_provider_view_config', [
            'view_text'        => __('View', 'ff-simple-booking'),
            'base_url'         => $urlBase,
            'time_format'      => get_option('time_format'),
            'date_format'      => get_option('date_format'),
            'date_time_format' => $wpDateTimeFormat,
            'booking_title'    => __('Bookings List', 'ff-simple-booking'),
            'confirm_heading'  => __('Are you sure you change this booking status? ', 'ff-simple-booking'),
            'confirm_btn'      => __('Yes', 'ff-simple-booking'),
            'reschulde_btn'    => __('Reschedule', 'ff-simple-booking'),
            'close'            => __('Cancel', 'ff-simple-booking'),
            'get_filters'      => array(
                'next'    => __('Next Upcoming', 'ff-simple-booking'),
                'pending' => __('Pending', 'ff-simple-booking'),
                'past'    => __('Past', 'ff-simple-booking'),
                'all'     => __('All', 'ff-simple-booking'),

            ),
            'booking_status'   => BookingHelper::bookingStatuses(),
        ]);
    }

    private function renderHtml($userId)
    {
        $viewConfig = $this->getViewConfig();
        $filterStatus = 'next';
        $activeTab = 'bookings';
        if (isset($_REQUEST['status'])) {
            $filterStatus = sanitize_text_field($_REQUEST['status']);
        }
        if (isset($_REQUEST['route'])) {
            $activeTab = sanitize_text_field($_REQUEST['route']);
        }
        $bookings = \FF_Booking\Booking\Models\BookingModel::getBookingsByProvider($userId, ['status' => $filterStatus]);
        $viewConfig['filterStatus'] = $filterStatus;
        $viewConfig['activeTab'] = $activeTab;
        return \FF_Booking\Booking\BookingHelper::loadView('providers_bookings', [
            'bookings' => $this->groupBy('formatted_date', $bookings),
            'config'   => $viewConfig,
        ]);
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
