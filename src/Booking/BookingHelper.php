<?php

namespace FF_Booking\Booking;

use \FluentForm\Framework\Helpers\ArrayHelper;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class BookingHelper
{

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

    public static function getTimeSlotsHtml($data)
    {
        $slots = self::getTimeSlotArray($data);
        if (empty($slots)) {
            return 'No slots on this date';
        }
        $slot_html = '';
        foreach ($slots as $s) {
            $slot_html .= '<label class="ff-radio-inline">
						    <input type="radio" class="ff-btn  ff-btn-sm ff-booking-time" name="ff_time_slot" value="' . $s . '">  ' . $s . '
						</label>';
        }

        return $slot_html;
    }

    public static function formatTimetoHourMin($time)
    {
        $temp = explode(':', $time);
        $hour = intval($temp[0]);
        $min = intval($temp[1]);
        $minutes = '';
        if ($min != 0) {
            $minutes = $min . 'minutes';
        }
        $hours = '';
        if ($hour != 0) {
            $hours = $hour . 'hours';
        }
        return $hours . ' ' . $minutes;
    }

    public static function getTimeSlotArray($data)
    {
        $serviceId = $data['service_id'];
        $slots = self::getRegularSlots($serviceId);

        $bookedSlots = self::getBookedSlotByDate($data['service_id'], $data['date'], $data['form_id']);

        //remove booked slots
        $slots = array_diff($slots, $bookedSlots);
        return $slots;
    }


    public static function getBookedSlotByDate($serviceId, $date, $formId, $status = "booked")
    {
        $date = self::regularDateToMysql($date);

        $bookedSlots = wpFluent()
            ->table('alex_booking_entries')
            ->where('form_id', $formId)
            ->where('service_id', $serviceId)
            ->where('date', $date)
            ->where('booking_status', $status)
            ->get();

        $bookedSlots = (array)$bookedSlots;
        return array_column($bookedSlots, 'time');
    }

    public static function getBookedSlotsbyService($serviceId, $formId, $status = "booked")
    {
        //get all booked date for two months
        // disable full booked dates
        $future_timestamp = strtotime("+2 month");
        $date = date('Y-m-d', $future_timestamp);
        $bookedSlots = wpFluent()
            ->table('alex_booking_entries')
            ->select(array('date', wpFluent()->raw('COUNT(id) as total_booking')))
            ->groupBy('date')
            ->where('form_id', $formId)
            ->where('service_id', $serviceId)
            ->where('date', '<', $future_timestamp)
            ->where('status', $status)
            ->get();
        return $bookedSlots;
    }

    public static function getFullBookedDate($serviceId, $formId)
    {
        //get full booked dates upto two months
        $booked = self::getBookedSlotsbyService($serviceId, $formId);

        $regularSlots = count(self::getRegularSlots($serviceId));
        $fullBookedDate = [];

        foreach ($booked as $booking) {
            if ($booking->total_booking >= $regularSlots) {
                //then the date is full booked
                array_push($fullBookedDate, self::mysqlToRegularDate($booking->date));
            }
        }
        array_push($fullBookedDate, '26-02-2021');
        return $fullBookedDate;
    }

    // d-m-y => y-m-d
    public static function regularDateToMysql($date)
    {
        return date('Y-m-d', strtotime($date));
    }

    // y-m-d => d-m-y
    public static function mysqlToRegularDate($date)
    {
        return date('d-m-Y', strtotime($date));
    }

    /**
     * @param $serviceId
     * @return array Regular time slots by service ID
     */
    private static function getRegularSlots($serviceId)
    {
        $serviceData = (array)self::getService($serviceId);

        $startTime = !empty($serviceData['start_time']) ? $serviceData['start_time'] : '09:00';
        $endTime = !empty($serviceData['end_time']) ? $serviceData['end_time'] : '17:00';
        $duration = !empty($serviceData['duration']) ? $serviceData['duration'] : '01:00';

        $start_time = strtotime($startTime);
        $end_time = strtotime($endTime);
        $time_gap = self::formatTimetoHourMin($duration);
        $time_slots = [];
        for ($i = $start_time; $i <= $end_time; $i = strtotime('+' . $time_gap, $i)) {
            $time_slots[] = date("G:i", $i);
        }
        return $time_slots;
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
