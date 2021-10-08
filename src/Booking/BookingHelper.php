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
        if($convertTo == '24'){
            return date("H:i", strtotime($time));
        }elseif($convertTo == '12'){
            return  date("h:i a", strtotime($time));
        }
    }
    public static function getBookingFieldDateFormat($formId)
    {
        $form = wpFluent()->table('fluentform_forms')->find($formId);
        $fields = json_decode($form->form_fields, true);
        foreach ($fields['fields'] as $field){
            if($field['element'] == 'booking_datetime'){
                return  ArrayHelper::get($field, 'settings.date_format');
            }
        }
        return 'd/m/y';
    }
    public static function getService($id = '')
    {
        $serviceData = [];
        if (!empty($id)) {
            $serviceData = wpFluent()->table('_alex_booking_services')
                ->where('id', $id)
                ->first();
        } else {
            $serviceData = wpFluent()->table('_alex_booking_services')
                ->get();
        }
        return $serviceData;
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
}
