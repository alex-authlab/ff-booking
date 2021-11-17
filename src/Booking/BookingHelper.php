<?php

namespace FF_Booking\Booking;

use \FluentForm\Framework\Helpers\ArrayHelper;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class BookingHelper
{
    public static function convertTime($convertTo = '24', $time)
    {
        if ($convertTo == '24') {
            return date("H:i", strtotime($time));
        } elseif ($convertTo == '12') {
            return date("h:i a", strtotime($time));
        }
    }

    /**
     * @return string time format
     */
    public static function getTimeFormat()
    {
        $settings = json_decode(get_option('__ff_booking_general_settings'), true);
        $timeFormat = 'h:i a';
        if (ArrayHelper::get($settings, 'time_format') == '12') {
            $timeFormat = 'h:i a';
        } else {
            $timeFormat = 'H:i';
        }
        return $timeFormat;
    }

    /**
     * @return string time period
     */
    public static function getTimePeriod()
    {
        $settings = json_decode(get_option('__ff_booking_general_settings'), true);
        $timePeriod = '12';
        if ($value = ArrayHelper::get($settings, 'time_format')) {
            $timePeriod = $value;
        }
        return $timePeriod;
    }

    /**
     * @return string time zone
     */
    public static function getTimeZone()
    {
        $settings = json_decode(get_option('__ff_booking_general_settings'), true);
        $timezone = ArrayHelper::get($settings, 'time_zone');
        if (!$timezone) {
            $timezone = get_option('timezone_string'); //wp timezone
        }
        if (!in_array($timezone, timezone_identifiers_list())) {
            $timezone = 'America/New_York'; //add filter
        }
        return $timezone;
    }

    /**
     * @return string first day of week
     */
    public static function firstWeekDay()
    {
        $settings = get_option('__ff_booking_general_settings');
        $weekDay = ArrayHelper::get($settings, 'week_start', 0);
        return date('N', strtotime($weekDay));
    }

    public static function getBookingFieldDateFormat($formId)
    {
        $form = wpFluent()->table('fluentform_forms')->find($formId);
        $fields = json_decode($form->form_fields, true);
        foreach ($fields['fields'] as $field) {
            if ($field['element'] == 'booking_datetime') {
                return ArrayHelper::get($field, 'settings.date_format');
            }
        }
        return 'd/m/y';
    }

    public static function getAvailableForms()
    {
        $forms = wpFluent()->table('fluentform_forms')
            ->select(['id', 'title'])
            ->get();
        $formattedForms = [];
        foreach ($forms as $form) {
            $formattedForms[$form->id] = $form->title;
        }
        return $formattedForms;
    }

    public static function verifyRequest($key = 'ff_booking_admin_nonce')
    {
        if (!wp_doing_ajax()) {
            return;
        }
        $nonce = ArrayHelper::get($_REQUEST, $key);
        if (!wp_verify_nonce($nonce, $key)) {
            $message = 'Nonce verification failed, please try again.';
            wp_send_json([
                'message' => $message
            ], 422);
        }
    }

    public static function loadView($fileName, $data)
    {
        $basePath = FF_BOOKINGDIR_PATH . 'src/Booking/view/';
        $filePath = $basePath . $fileName . '.php';
        extract($data);
        ob_start();
        include $filePath;
        return ob_get_clean();
    }

    public static function bookingStatuses()
    {
        return array(
            'booked' => __('Booked',FF_BOOKING_SLUG),
            'canceled' => __('Cancel',FF_BOOKING_SLUG),
            'pending' => __('Pending',FF_BOOKING_SLUG),
            'complete' => __('Complete',FF_BOOKING_SLUG),
            'draft' => __('Draft',FF_BOOKING_SLUG),
        );
    }

    /**
     * To modify time : 01:30 => 1 Hour 30 minutes
     *
     * @param $interval
     * @param $timeZone
     * @return string
     * @throws \Exception
     */
    public static function timeDurationLength($interval ,$isHtml = false)
    {
        $addTime = '';
        $fraction = explode(':', $interval);
        $interval = new \DateTime($interval);
        $append = ' +';
        if($isHtml){
            $append = '';
        }
        if ($fraction[0] != '00') {
            $addTimeHour =  $append . $interval->format('g') . ' Hour';
            $addTime = $addTimeHour;
        }
        if ($fraction[1] != '00') {
            $addTimeMin =  $append . $interval->format('i') . ' minutes';
            $addTime .= $addTimeMin;
        }

        return $addTime;
    }
    public static function getSettingsByKey($key)
    {
        $settings = json_decode(get_option('__ff_booking_general_settings'), true);
        return ArrayHelper::get($settings, $key);
    }

    public static function formatTime($value)
    {
        return date_i18n(get_option('time_format'), strtotime($value));
    }
    public static function formatDate($value)
    {
        return date_i18n(get_option('date_format'), strtotime($value));
    }
}
