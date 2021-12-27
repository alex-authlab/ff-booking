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

        $data['booking_type'] = ArrayHelper::get($service, 'booking_type');
        return $data;
    }

    public function getFullBookedDate($formatted = true)
    {
        //get full booked dates upto max allowed date
        $provider = $this->getProviderData();
        $service = $this->getServiceData();
        if (!$provider || !$service) {
            return false;
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
        if ($service['capacity_type'] == 'multiple') {
            $maxCapacity = intval($service['slot_capacity']) * count($regularSlots);
        } elseif ($service['capacity_type'] == 'single') {
            $maxCapacity = count($regularSlots);
        }
        if ($service['booking_type'] == 'date_slot'){
            $maxCapacity = intval($service['slot_capacity']) ;
        }
        $maxBookings = intval($service['max_bookings']);

        foreach ($bookedDates as $date) {
            if ($date->total_booked >= $maxBookings || $date->total_booked >= $maxCapacity) {
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
        $rangeType = ArrayHelper::get($service, 'range_type');
        $startDate = date('Y-m-d');

        if ($rangeType == 'days') {
            $futureDays = ArrayHelper::get($service, 'allowed_future_days');
            $endDate = date('Y-m-d', strtotime('+' . $futureDays));
        } elseif ($rangeType == 'date_range') {
            $ranges = ArrayHelper::get($service, 'allowed_future_date_range');
            $startDate = $ranges[0];
            $endDate = $ranges[1];
        }
        //check disabled booking before day settings
        if ($startDate <= date('Y-m-d')) {
            $startDate = date('Y-m-d', strtotime(ArrayHelper::get($service, 'disable_booking_before')));
        }
        return array($startDate, $endDate);
    }

    public function getTimeSlots()
    {
        $provider = $this->getProviderData();
        $service = $this->getServiceData();
        if (!$provider || !$service) {
            return [
                'success' => false,
                'message' => "Provider or Service Not Found"
            ];
        }
        $slots = $this->getCalculatedSlots($provider, $service);
        if (is_array($slots) && (count($slots) > 0)) {
            return [
                'success' => true,
                'slots' => $slots
            ];
        }
        return [
            'success' => false,
            'message' => "No Slot Found"
        ];
    }

    public function generateTimeSlot(
        $interval,
        $start_time,
        $end_time,
        $gapTimeAfter = '00:30',
        $with_end_time = false
    ) {
        $timeZone = BookingHelper::getTimeZone();
        $timeFormat = BookingHelper::getTimeFormat();
        $gapTime = BookingHelper::timeDurationLength($gapTimeAfter);
        $duration = BookingHelper::timeDurationLength($interval);

        $start = new \DateTime($start_time, new \DateTimeZone($timeZone));
        $end = new \DateTime($end_time, new \DateTimeZone($timeZone));

        $i = 0;
        $time = [];
        while ($start->format('U') <= $end->format('U')) {
            $time[$i]['value'] = $start->format('H:i'); //save 24 hour format in database
            $time[$i]['label'] = $start->format($timeFormat);;

            //add duration
            $slotEnd = $start->modify($duration)->format($timeFormat);
            if ($with_end_time) {
                $time[$i]['label'] .= ' - ' . BookingHelper::formatTime($slotEnd);
            }

            //add gap time
            $start->modify($gapTime);
            $i++;
        }
        return $time;
    }



    private function getBookedSlots()
    {
        $service = $this->getServiceData();
        $ranges = $this->allowedDateRange($service);
        $slots = (new BookingModel())->bookedSlots(
            $this->serviceId,
            $this->providerId,
            $this->formId,
            $ranges,
            $this->date
        );
        $timeFormat = BookingHelper::getTimeFormat();
        $formattedSlots = [];
        foreach ($slots as $slot) {
            $formattedSlots[] = date($timeFormat, strtotime($slot->booking_time));
        }
        return $formattedSlots;
    }

    /**
     * Calculate regular slot with booked slot and additional settings
     * Return formatted values for user
     * @param $provider
     * @param $service
     * @return array
     */
    private function getCalculatedSlots($provider, $service)
    {
        $regularSlots = $this->getRegularSlots($provider, $service);
        $bookedSlots = $this->getBookedSlots();
        $showBookedTime = ArrayHelper::get($service, 'show_booked_time') == 'show';
        $showRemainingSlot = ArrayHelper::get($service, 'show_remaining_slot') == 'show' && ArrayHelper::get(
                $service,
                'capacity_type'
            ) == 'multiple';

        $formattedSlots = [];
        foreach ($regularSlots as $slot) {
            $data = [
                'label' => $slot['label'],
                'value' => $slot['value'],
            ];

            if (in_array($slot['value'], $bookedSlots)) {
                $booked = $this->isBooked($service, $slot, $bookedSlots);
                if ($booked && $showBookedTime) {
                    $data['booked'] = true;
                }
                if ($showRemainingSlot) {
                    $remainingSlot = $this->getRemainingSlot($service, $slot, $bookedSlots);
                    $data['remaining_slot'] = sprintf(__('%d slot remaining', FF_BOOKING_SLUG), $remainingSlot);
                }
                $formattedSlots[] = $data;
                continue;
            }

            if ($showRemainingSlot) {
                $remainingSlot = ArrayHelper::get($service, 'slot_capacity');
                $data['remaining_slot'] = sprintf(
                    __('%d slot remaining', FF_BOOKING_SLUG),
                    $remainingSlot
                );
            }
            $formattedSlots[] = $data;
        }

        return $formattedSlots;
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
        $gapTimeAfter = ArrayHelper::get($service, 'gap_time_after');
        $show_end_time = ArrayHelper::get($service, 'show_end_time') == 'show';
        return $this->generateTimeSlot($duration, $startTime, $endTime, $gapTimeAfter, $show_end_time);
    }

    /**
     * Validate slot
     * Save time as 24 hour in backend
     * When bookinID passed ignnore current slot for updating purpose
     * @param $provider
     * @param $service
     * @return array
     */

//    @todo 1. disbale before time, timezone, adjust booking entry & validation ,emails
    public function isValidSlot($time, $bookingId = false)
    {
        $provider = $this->getProviderData();
        $service = $this->getServiceData();
    
        if (!$provider || !$service) {
            return [
                'status' => false,
                'message' => __('Invalid Data, try again',FF_BOOKING_SLUG)
            ];
        }

        if (!in_array($this->serviceId, $provider['assigned_services'])) {
            return [
                'status' => false,
                'message' => __('Invalid Service',FF_BOOKING_SLUG)
            ];
        }
        if ($formIds = ArrayHelper::get($provider, 'allowed_form_ids')) {
            if (!in_array($this->formId, $formIds)) {
                return [
                    'status' => false,
                    'message' => __('Invalid Provider Form',FF_BOOKING_SLUG)
                ];
            }
        }
        if ($formIds = ArrayHelper::get($service, 'allowed_form_ids')) {
            if (!in_array($this->formId, $formIds)) {
                return [
                    'status' => false,
                    'message' => __('Invalid Service Form',FF_BOOKING_SLUG)
                ];
            }
        }
        list($minDate, $maxDate) = $this->allowedDateRange($service);
        if ($this->date < $minDate || $this->date > $maxDate) {
            return [
                'status' => false,
                'message' => __('Selected date is not in valid range',FF_BOOKING_SLUG)
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
                'message' => __('Invalid Date Selected',FF_BOOKING_SLUG)
            ];
        }
        $weekOffDays = ArrayHelper::get($provider, 'weekend_days');
        $selectedWeekDay = date('l', strtotime($this->date));
        if (is_array($weekOffDays) && in_array($selectedWeekDay, $weekOffDays)) {
            return [
                'status' => false,
                'message' => 'Invalid Day of week selected'
            ];
        }
        $validSlots = $this->getRegularSlots($provider, $service);
        $validSlots = array_column($validSlots, 'value');

        $format = BookingHelper::getTimeFormat();
    
        //ignore current booking time slot when updating
        $bookedSlotsByTime = (new BookingModel())->getBookingsOfSingleDay(
            $this->serviceId,
            $this->providerId,
            $this->formId,
            $this->date,
            $time,
            $bookingId
        );
    
        $bookedSlotsByDate = (new BookingModel())->getBookingsOfSingleDay(
            $this->serviceId,
            $this->providerId,
            $this->formId,
            $this->date,
            null
        );
    
    
        //booking type timeslot
        if ($service['booking_type'] == 'time_slot') {
            if (!in_array($time, $validSlots)) {
                return [
                    'status' => false,
                    'message' => __('Invalid Time slot selected',FF_BOOKING_SLUG)
                ];
            }
            //single booking validation
            if ($bookedSlotsByTime->total >= 1 && $service['capacity_type'] == 'single') {
                return [
                    'status' => false,
                    'message' => __('This Slot is Booked', FF_BOOKING_SLUG)
                ];
            } //multiple booking
            else {
                if ($bookedSlotsByTime->total >= intval(
                        $service['slot_capacity']
                    ) && $service['capacity_type'] == 'multiple') {
                    return [
                        'status' => false,
                        'message' => __('No more Slot is left on this time', FF_BOOKING_SLUG)
                    ];
                }
            }
        } else {
            //booking type dateslot
            if ($service['booking_type'] == 'date_slot') {
                //if slot type is single
                if ($service['capacity_type'] == 'single' && $bookedSlotsByDate->total >= 1) {
                    return [
                        'status' => false,
                        'message' => __('This date is already booked', FF_BOOKING_SLUG)
                    ];
                } //if slot type is multiple
                else {
                    if ($service['capacity_type'] == 'multiple' && $bookedSlotsByDate->total >= intval(
                            $service['slot_capacity']
                        )) {
                        return [
                            'status' => false,
                            'message' => __('No more Slot is left on this date', FF_BOOKING_SLUG)
                        ];
                    }
                }
            }
        }


        //total slot capacity on a single a day
        if ($bookedSlotsByDate->total >= intval($service['max_bookings'])) {
            return [
                'status' => false,
                'message' => 'No more slot remaining on this date'
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


    /**
     * @param $service
     * @param $slot
     * @param $bookedSlots
     * @return bool
     */
    private function isBooked($service, $slot, $bookedSlots)
    {
        $capacityType = ArrayHelper::get($service, 'capacity_type');
        $capacityValue = ArrayHelper::get($service, 'slot_capacity');
        if ($capacityType == 'single') {
            return true;
        } else {
            if ($capacityType == 'multiple') {
                $bookedSlotCount = array_count_values($bookedSlots);
                $slotVal = $slot['value'];

                if ($bookedSlotCount[$slotVal] < $capacityValue) {
                    return false;
                }
                return true;
            }
        }
    }

    private function getRemainingSlot($service, $slot, $bookedSlots)
    {
        $capacityValue = ArrayHelper::get($service, 'slot_capacity');
        $bookedSlotCount = array_count_values($bookedSlots);
        $slotVal = $slot['value'];
        return abs($capacityValue - $bookedSlotCount[$slotVal]);
    }


    /**
     * Apply Disable booking before min & hour settings here
     * Skip If date is not today to apply only for time variables
     * @param $slot
     * @param $service
     * @return array
     */
    private function inDisabledTimeRange($time, $service)
    {
        if (date('Y-m-d') != $this->date) {
            return false;
        }
        return false;
        $disableBefore = ArrayHelper::get($service, 'disable_booking_before');

        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone(BookingHelper::getTimeZone()));
        $endTime = $now->modify('+' . $disableBefore)->format('U');

        $currentTime = new \DateTime("now", new \DateTimeZone(BookingHelper::getTimeZone()));
        $currentTime = $currentTime->format('U');

        $slotTime = strtotime($time);

        if ($currentTime >= $slotTime && $slotTime <= $endTime) {
            return true;
        }
        return false;
    }


}


