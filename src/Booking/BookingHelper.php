<?php

namespace FF_Booking\Booking;

use FluentForm\App\Modules\Form\FormFieldsParser;
use \FluentForm\Framework\Helpers\ArrayHelper;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class BookingHelper
{
    public static function convertTime($convertTo = '24', $time ='')
    {
        if(!$time){
            return '';
        }
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
        return get_option('time_format');
    }

    /**
     * @return string time zone
     */
    public static function getTimeZone()
    {
        $timezone = wp_timezone_string(); //wp timezone

        if (!in_array($timezone, timezone_identifiers_list())) {
            
            //its in offset , convert to timezone string
            list($hours, $minutes) = explode(':', $timezone);
            $seconds = $hours * 60 * 60 + $minutes * 60;
            $tz = timezone_name_from_abbr('', $seconds, 1);
            if($tz === false) {
                $tz = timezone_name_from_abbr('', $seconds, 0);
            }
    
             $timezone = $tz;
        }
        return $timezone;
    }

    /**
     * @return string first day of week
     */
    public static function firstWeekDay()
    {
        return get_option('start_of_week');
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
            'booked'   => __('Booked', FF_BOOKING_SLUG),
            'canceled' => __('Cancel', FF_BOOKING_SLUG),
            'rejected' => __('Rejected', FF_BOOKING_SLUG),
            'pending'  => __('Pending', FF_BOOKING_SLUG),
            'complete' => __('Complete', FF_BOOKING_SLUG),
            'draft'    => __('Draft', FF_BOOKING_SLUG),
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
    //@todo add filters
    public static function formatTime($value)
    {
        return date_i18n(get_option('time_format'), strtotime($value));
    }
    public static function formatDate($value , $format='')
    {
        if(!$format){
            $format = get_option('date_format');
        }
        return date_i18n($format, strtotime($value));
    }

    public static function getUserName($formData, $form = false)
    {
        $user = get_user_by('ID', get_current_user_id());
        if ($user) {
            $userName = trim($user->first_name . ' ' . $user->last_name);
            if (!$userName) {
                $userName = $user->display_name;
            }
            if ($userName) {
                return $userName;
            }
        }

        if (!$form) {
            return '';
        }
        FormFieldsParser::resetData();
        $nameFields = \FluentForm\App\Modules\Form\FormFieldsParser::getInputsByElementTypes($form, ['input_name'], ['attributes']);

        $fieldName = false;
        foreach ($nameFields as $field) {
            if ($field['element'] === 'input_name') {
                $fieldName = $field['attributes']['name'];
                break;
            }
        }

        $name = '';
        if ($fieldName) {
            if (!empty(ArrayHelper::get($formData,$fieldName))) {
                $names = array_filter($formData[$fieldName]);
                return trim(implode(' ', $names));
            }
        }

        return $name;
    }

    public static function getUserEmail($formData, $form = false)
    {

        $user = get_user_by('ID', get_current_user_id());

        if ($user) {
            return $user->user_email;
        }

        if (!$form) {
            return '';
        }
        FormFieldsParser::resetData();
        $emailFields =  FormFieldsParser::getInputsByElementTypes($form, ['input_email'], ['attributes']);

        foreach ($emailFields as $field) {
            $fieldName = $field['attributes']['name'];
            if (!empty(ArrayHelper::get($formData,$fieldName))) {
                return $formData[$fieldName];
            }
        }
        return '';

    }

}
