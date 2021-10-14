<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FF_Booking\Booking\Models\ProviderModel;
use FF_Booking\Booking\Models\ServiceModel;
use FluentForm\Framework\Helpers\ArrayHelper;

class DateTimeHandler
{
    private $serviceId;
    private $providerId;
    private $formId;
    public $date;


    public function __construct($serviceId, $providerId, $formId, $date = '')
    {
        $this->serviceId = $serviceId;
        $this->providerId = $providerId;
        $this->formId = $formId;
        $this->date = $date;
    }

    public function getDatesData()
    {
        $data = [];
        $provider = $this->getProviderData();
        $service = $this->getServiceData();

        if (!$provider || !$service) {
            $data['success'] = false;
            $data['message'] = "Provider or Service Not Found";
            return $data;
        }
        list($startDate, $endDate) = $this->allowedDateRange($service);
        $dateFormat = BookingHelper::getBookingFieldDateFormat($this->formId);
        $data['min_date'] = date($dateFormat, strtotime($startDate));
        $data['max_date'] = date($dateFormat, strtotime($endDate));

        $weekOffDays = ArrayHelper::get($provider, 'weekend_days');
        $weekOffDaysFormatted = [];
        if ($weekOffDays) {
            foreach ($weekOffDays as $day) {
                $weekOffDaysFormatted[] = date('w', strtotime($day));
            }
        }
        $data['weekend_days'] = $weekOffDaysFormatted;

        $holidays = ArrayHelper::get($provider, 'holiday_dates');
        $formattedHoliday = [];
        if (is_array($holidays)) {
            foreach ($holidays as $day) {
                $formattedHoliday[] = date($dateFormat, strtotime($day));
            }
        }

        $data['disabled_dates'] = array_merge($formattedHoliday, $this->getFullBookedDate());
        return $data;
    }

    public function getFullBookedDate($formatted = true)
    {
        //get full booked dates upto max allowed date
        $provider = $this->getProviderData();
        $service = $this->getServiceData();
        if (!$provider || !$service) {
            $data['success'] = false;
            $data['message'] = "Provider or Service Not Found";
            return $data;
        }
        $regularSlots = $this->getRegularSlots($provider, $service);

        list($minDate, $maxDate) = $this->allowedDateRange($service);

        $dateFormat = BookingHelper::getBookingFieldDateFormat($this->formId);
        $bookedDates = (new BookingModel())->bookedSlotGroupByDate(
            $this->serviceId,
            $this->providerId,
            $this->formId,
            $minDate,
            $maxDate
        );
        $fullBookedDate = [];
        $max = count($regularSlots);
        $maxBookings = intval($service['max_bookings']);
        if ($maxBookings > 1) {
            $max = $maxBookings;
        }
        foreach ($bookedDates as $date) {
            if ($date->total_booked >= $max) {
                if ($formatted) {
                    $date = date($dateFormat, strtotime($date->booking_date));
                } else {
                    $date = $date->booking_date;
                }
                $fullBookedDate[] = $date;
            }
        }
        return $fullBookedDate;
    }

    public function getProviderData()
    {
        if ($provider = (new ProviderModel())->getProvider($this->providerId)) {
            return (array)$provider;
        }
        return false;
    }

    public function getServiceData()
    {
        if ($service = (new ServiceModel())->getService($this->serviceId)) {
            return (array)$service;
        }
        return false;
    }

    /**
     * @param $service
     * @return array
     */
    private function allowedDateRange($service)
    {
        $futureDays = ArrayHelper::get($service, 'allowed_future_days');
        $startDate = date('Y-m-d', strtotime('+1 days'));
        $endDate = date('Y-m-d', strtotime('+' . $futureDays));
        return array($startDate, $endDate);
    }

    public function getTimeSlots()
    {
        $provider = $this->getProviderData();
        $service = $this->getServiceData();
        if (!$provider || !$service) {
            $data['success'] = false;
            $data['message'] = "Provider or Service Not Found";
            return $data;
        }
        return $this->getCalculatedSlots($provider, $service);
    }

//    todo : add time format & zone settings
    public function generateTimeSlot($interval, $start_time, $end_time, $gapTime = '00:00', $with_end_time = false)
    {
        $timeZone = $this->getTimeZone();
        $timeFormat = $this->getTimeFormat();

        $gapTime = $this->timeDurationLength($gapTime, $timeZone);
        $addTime = $this->timeDurationLength($interval, $timeZone);
        $start = new \DateTime($start_time);
        $end = new \DateTime($end_time);

        $startTime = $start->format($timeFormat);
//        create datetime with timezone
//        $now = new \DateTime();
//        $now->setTimezone(new \DateTimeZone( $timeZone));
//        vd($now->format('d-m-Y h:i'));


        $endTime = $end->format($timeFormat);
        $i = 0;
        $time = [];
        while (strtotime($startTime) <= strtotime($endTime)) {
            $start = $startTime;
            $end = date($timeFormat, strtotime($addTime, strtotime($startTime)));
            $startTime = date($timeFormat, strtotime($addTime, strtotime($startTime)));
            $startTime = date($timeFormat, strtotime($gapTime, strtotime($startTime)));

            if (strtotime($startTime) <= strtotime($endTime)) {
                $time[$i]['label'] = $start;
                if ($with_end_time) {
                    $time[$i]['label'] = $start . ' - ' . $end;
                }
                $time[$i]['value'] = $start;
            }
            $i++;
        }
        return $time;
    }

    function getTimeZone()
    {
        $settings = get_option('__ff_booking_general_settings');
        $timezone = ArrayHelper::get($settings, 'time_zone');
        if (!$timezone) {
            $timezone = get_option('timezone_string'); //wp timezone
        }

        if (!in_array($timezone, timezone_identifiers_list())) {
            $timezone = 'America/New_York';
        }
        return $timezone;
    }

    /**
     * @param $interval
     * @param $timeZone
     * @return string
     * @throws \Exception
     */
    private function timeDurationLength($interval, $timeZone)
    {
        $addTime = '';
        $fraction = explode(':', $interval);
        $interval = new \DateTime($interval, new \DateTimeZone($timeZone));

        if ($fraction[0] != '00') {
            $addTimeHour = '+' . $interval->format('g') . ' Hour';
            $addTime = $addTimeHour;
        }
        $addTimeMin = '+' . $interval->format('i') . ' minutes';
        $addTime .= $addTimeMin;
        return $addTime;
    }

    private function getBookedSlots()
    {
        $service = $this->getServiceData();
        $range = $service['allowed_future_days'];
        $slots = (new BookingModel())->bookedSlots(
            $this->serviceId,
            $this->providerId,
            $this->formId,
            $range,
            $this->date
        );
        $timeFormat = $this->getTimeFormat();
        $formattedSlots = [];
        foreach ($slots as $slot) {
            $formattedSlots[] = date($timeFormat, strtotime($slot->booking_time));
        }
        return $formattedSlots;
    }

    /**
     * @param $provider
     * @param $service
     * @return array
     */
    private function getCalculatedSlots($provider, $service)
    {
        $regularSlots = $this->getRegularSlots($provider, $service);
        $showBookedTime = ArrayHelper::get($service, 'show_booked_time') == 'show';
        $slotCapacity = intval($service['slot_capacity']);
        $bookedSlots = $this->getBookedSlots();

        $formattedSlots = [];
        foreach ($regularSlots as $slot) {
            if (in_array($slot['value'], $bookedSlots)) {
                $booked = $this->checkSlotCapacity($slot, $bookedSlots, $slotCapacity);
                if ($showBookedTime) {
                    $formattedSlots[] = [
                        'label' => $slot['label'],
                        'value' => $slot['value'],
                        'booked' => $booked,
                    ];
                }
                continue;
            }
            $formattedSlots[] = [
                'label' => $slot['label'],
                'value' => $slot['value'],
            ];
        }
        return $formattedSlots;
    }

    /**
     * @return string
     */
    private function getTimeFormat()
    {
        $settings = get_option('__ff_booking_general_settings');
        if (ArrayHelper::get($settings, 'time_format') == '12') {
            $timeFormat = 'h:i a';
        } else {
            $timeFormat = 'H:i';
        }
        return $timeFormat;
    }

    /**
     * @param $provider
     * @param $service
     * @return array
     */
    private function getRegularSlots($provider, $service)
    {
        $startTime = ArrayHelper::get($provider, 'start_time');
        $endTime = ArrayHelper::get($provider, 'end_time');
        $duration = ArrayHelper::get($service, 'duration');
        $gapTime = ArrayHelper::get($service, 'gap_time');
        $show_end_time = ArrayHelper::get($service, 'show_end_time') == 'show';
        return $this->generateTimeSlot($duration, $startTime, $endTime, $gapTime, $show_end_time);
    }

    /**
     * Validate slot
     * Save time as 24 hour in backend
     * When bookinID passed ingnore current slot for updating pupose
     * @param $provider
     * @param $service
     * @return array
     */

    public function isValidData($time, $bookingId = false)
    {
        $provider = $this->getProviderData();
        $service = $this->getServiceData();

        if (!in_array($this->serviceId, $provider['assigned_services'])) {
            return [
                'status' => false,
                'message' => 'Invalid Service'
            ];
        }
        if ($formIds = ArrayHelper::get($provider, 'allowed_form_ids')) {
            if (!in_array($this->formId, $formIds)) {
                return [
                    'status' => false,
                    'message' => 'Invalid Provider Form'
                ];
            }
        }
        if ($formIds = ArrayHelper::get($service, 'allowed_form_ids')) {
            if (!in_array($this->formId, $formIds)) {
                return [
                    'status' => false,
                    'message' => 'Invalid Service Form'
                ];
            }
        }
        list($minDate, $maxDate) = $this->allowedDateRange($service);
        if ($this->date < $minDate || $this->date > $maxDate) {
            return [
                'status' => false,
                'message' => 'Selected date is not in valid range'
            ];
        }


        $holidays = ArrayHelper::get($provider, 'holiday_dates');
        if (!is_array($holidays)) {
            $holidays = [];
        }
        $disabledDates = array_merge($holidays, $this->getFullBookedDate($formatted = false));
        if (in_array($this->date, $disabledDates)) {
            return [
                'status' => false,
                'message' => 'Invalid Date Selected'
            ];
        }
        $weekOffDays = ArrayHelper::get($provider, 'weekend_days');
        $selectedWeekDay = date('l', strtotime($this->date));
        if (in_array($selectedWeekDay, $weekOffDays)) {
            return [
                'status' => false,
                'message' => 'Invalid Day of week selected'
            ];
        }
        $validSlots = $this->getRegularSlots($provider, $service);
        $validSlots = array_column($validSlots, 'value');

        $format = $this->getTimeFormat();
        $selectedTime = BookingHelper::convertTime($format, $time);

        if (!in_array($selectedTime, $validSlots)) {
            return [
                'status' => false,
                'message' => 'Invalid Time slot selected'
            ];
        }
        //ignore current booking time slot when updating
        $bookedSlotsByTime = (new BookingModel())->getBookingsOfSingleDay(
            $this->serviceId,
            $this->providerId,
            $this->formId,
            $this->date,
            $time,
            $bookingId
        );
        //total slot capacity on a single timeslot
        if ($bookedSlotsByTime->total >= intval($service['slot_capacity'])) {
            return [
                'status' => false,
                'message' => 'This Slot is Booked'
            ];
        }
        $bookedSlotsByDate = (new BookingModel())->getBookingsOfSingleDay(
            $this->serviceId,
            $this->providerId,
            $this->formId,
            $this->date,
            $time = null
        );
        //total slot capacity on a single a day
        if ($bookedSlotsByDate->total >= intval($service['max_bookings'])) {
            return [
                'status' => false,
                'message' => 'No more Slots on this date'
            ];
        }
        $d = \DateTime::createFromFormat('Y-m-d', $this->date);
        $validDate = $d && ($d->format('Y-m-d') == $this->date);

        if (!$validDate) {
            return [
                'status' => false,
                'message' => 'Invalid Date format'
            ];
        }
        return [
            'status' => true,
        ];
    }

    /*
     * Check slot capacity if capacity is 1 then its booked or check with bookedslot count
     */
    private function checkSlotCapacity($slot, $bookedSlots, $slotCapacity)
    {
        $bookedSlotCount = array_count_values($bookedSlots);
        $time = $slot['value'];

        if ($slotCapacity > 1 && $bookedSlotCount[$time] < $slotCapacity) {
            return false;
        }
        return true;
    }

}
