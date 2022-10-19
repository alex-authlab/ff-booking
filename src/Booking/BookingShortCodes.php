<?php

namespace FF_Booking\Booking;

if (!defined('ABSPATH')) {
    exit;
}

use FF_Booking\Booking\Components\BookingFields;
use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Form\FormFieldsParser;

class BookingShortCodes
{
    public function init()
    {
        $this->addShortCodes();
    }


    private function addShortCodes()
    {
        //{ff_booking_info}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_info', function ($value, $parser) {
            $entry = $parser::getEntry();
            return (new BookingInfo($entry->id))->bookingInfoHtml();
        }, 10, 2);
//        {ff_booking_info_page_link}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_info_page_link', function ($value, $parser) {
            $entry = $parser::getEntry();
            $data = (new BookingInfo($entry->id))->getBookingInfoData();
            $hash = ArrayHelper::get($data, 'bookingData.booking_hash');
            return get_site_url() . '?ff_simple_booking=' . $hash;
        }, 10, 2);

//        {ff_booking_date_time}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_date_time', function ($value, $parser) {
            $entry = $parser::getEntry();
            $data = (new BookingInfo($entry->id))->getBookingInfoData();
            $date = ArrayHelper::get($data, 'bookingData.booking_date');
            $time = ArrayHelper::get($data, 'bookingData.booking_time');
            if (ArrayHelper::get($data, 'bookingData.booking_type') == 'date_slot') {
                $time = '';
            }
            return $time . ' ' . $date;
        }, 10, 2);
//        {ff_booking_service}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_service', function ($value, $parser) {
            $entry = $parser::getEntry();
            $data = (new BookingInfo($entry->id))->getBookingInfoData();
            return ArrayHelper::get($data, 'bookingData.service');
        }, 10, 2);
//        {ff_booking_provider}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_provider', function ($value, $parser) {
            $entry = $parser::getEntry();
            $data = (new BookingInfo($entry->id))->getBookingInfoData();
            return ArrayHelper::get($data, 'bookingData.provider');
        }, 10, 2);
//        {ff_booking_user_email}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_user_email', function ($value, $parser) {
            $entry = $parser::getEntry();
            $data = (new BookingInfo($entry->id))->getBookingInfoData();
            return ArrayHelper::get($data, 'userData.email');
        }, 10, 2);
//        {ff_booking_user_name}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_user_name', function ($value, $parser) {
            $entry = $parser::getEntry();
            $data = (new BookingInfo($entry->id))->getBookingInfoData();
            return ArrayHelper::get($data, 'userData.name');
        }, 10, 2);
    }
}
