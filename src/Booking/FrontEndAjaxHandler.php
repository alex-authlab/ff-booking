<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FF_Booking\Booking\Models\ProviderModel;
use \FluentForm\Framework\Helpers\ArrayHelper;

class FrontEndAjaxHandler
{

    public function init()
    {
        $route = sanitize_text_field($_REQUEST['route']);
        //nonce verify : todo
        $this->handleEndpoint($route);
    }

    public function handleEndpoint($route)
    {
        $validRoutes = [
            'get_service_provider' => 'getServiceProvider',
            'get_dates' => 'getDates',
            'get_time_slots' => 'getTimeSlots',
            'get_time_slots_booking_page' => 'getTimeSlotsBookingPage',
            'reschedule_booking' => 'rescheduleBooking',
            'cancel_booking' => 'cancelBooking',
            'update_provider_booking'=>'updateProviderBooking'

        ];
        if (isset($validRoutes[$route])) {
            $this->{$validRoutes[$route]}();
        }
        die();
    }

    public function getServiceProvider()
    {
        $serviceId = intval($_REQUEST['service_id']);
        $formId = intval($_REQUEST['form_id']);
        $providers = (new ProviderModel())->getServiceProvider($serviceId, $formId);
        $formattedProviders = [];
        foreach ($providers as $provider) {
            $formattedProviders[$provider->id] = $provider->title;
        }
        $data['providers'] = $formattedProviders;
        wp_send_json_success($data);
    }

    public function getDates()
    {
        $serviceId = intval($_REQUEST['service_id']);
        $providerId = intval($_REQUEST['provider_id']);
        $formId = intval($_REQUEST['form_id']);
        $validator = fluentValidator($_REQUEST, [
            'service_id' => 'required',
            'provider_id' => 'required',
            'form_id' => 'required',
        ]);

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors' => $errors,
                'message' => 'Please fill up all the required fields'
            ], 423);
        }
        $dates = (new DateTimeHandler($serviceId, $providerId, $formId))->getDatesData();
        wp_send_json_success([
            'dates_data' => $dates
        ]);
    }

    public function getTimeSlots()
    {
        $serviceId = intval($_REQUEST['service_id']);
        $providerId = intval($_REQUEST['provider_id']);
        $date = sanitize_text_field($_REQUEST['date']);
        $formId = intval($_REQUEST['form_id']);
        $validator = fluentValidator($_REQUEST, [
            'service_id' => 'required',
            'provider_id' => 'required',
            'form_id' => 'required',
            'date' => 'required',
        ], [
            'service_id.required' => 'Service is required',
            'provider_id.required' => 'Provider is required',
            'form_id.required' => 'Form missing',
            'date.required' => 'Date missing',
        ]);

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors' => $errors,
                'message' => 'Please fill up all the required fields'
            ]);
        }

        $returnData = (new DateTimeHandler($serviceId, $providerId, $formId, $date))->getTimeSlots();
        if ($returnData['success'] == true) {
            wp_send_json_success([
                'time_slots' => $returnData['slots']
            ]);
            return;
        }

        wp_send_json($returnData);
    }


    public function getTimeSlotsBookingPage()
    {
        $date = sanitize_text_field($_REQUEST['selectedDate']);
        $bookingHash = sanitize_text_field($_REQUEST['bookingHash']);

        $data = (new BookingModel())->getBooking(['booking_hash' => $bookingHash]);
        $returnData = (new DateTimeHandler(
            ArrayHelper::get($data, 'service_id'),
            ArrayHelper::get($data, 'provider_id'),
            ArrayHelper::get($data, 'form_id'),
            $date
        ))->getTimeSlots();
        if ($returnData['success'] == true) {
            wp_send_json_success([
                'time_slots' => $returnData['slots']
            ]);
            return;
        }
        wp_send_json($returnData);
    }

    public function rescheduleBooking()
    {
        $bookingHash = sanitize_text_field($_REQUEST['bookingHash']);
        $dateTime = sanitize_text_field($_REQUEST['dateTime']);
        $dateTime = BookingActions::getFormattedDateTime($dateTime, []);

        list($bookingData, $bookingId, $entryId, $bookingDate, $bookingTime, $response) = $this->validate(
            $bookingHash,
            $dateTime
        );
        if ($response['status'] == false) {
            wp_send_json($response);
            return;
        }
        $updateData = [
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
        ];
        $update = (new BookingModel())->update($bookingId, $updateData);

        do_action('ff_booking_updated', $bookingId, $entryId, $bookingData, $updateData);

        wp_send_json_success([
            'message' => __('Booking has been updated succefully', FF_BOOKING_SLUG)
        ]);
    }

    public function cancelBooking()
    {
        $bookingHash = sanitize_text_field($_REQUEST['bookingHash']);

        $bookingData = (new BookingModel())->getBooking(['booking_hash' => $bookingHash]);
        $bookingId = (int)ArrayHelper::get($bookingData, 'id');
        $entryId = (int)ArrayHelper::get($bookingData, 'entry_id');
        $updateData = [
            'booking_status' => 'canceled',
        ];
        (new BookingModel())->update($bookingId, $updateData);
        do_action('ff_booking_updated', $bookingId, $entryId, $bookingData, $updateData);

        wp_send_json_success([
            'message' => __('Booking has been canceled succefully', FF_BOOKING_SLUG)
        ]);
    }

    public function updateProviderBooking()
    {
        $bookingId = sanitize_text_field($_REQUEST['booking_id']);
        $status = sanitize_text_field($_REQUEST['status']);

        do_action('ff_booking_status_changing', $bookingId, $status);
        $data['booking_status'] = $status;
        (new BookingModel())->update($bookingId, $data);
        wp_send_json_success([
            'message' => __('Booking has been updated succefully', FF_BOOKING_SLUG)
        ]);

    }

    /**
     * @param $bookingHash
     * @param array $dateTime
     * @return array
     */
    private function validate($bookingHash, array $dateTime): array
    {
        $bookingData = (new BookingModel())->getBooking(['booking_hash' => $bookingHash]);
        $bookingId = (int)ArrayHelper::get($bookingData, 'id');
        $entryId = ArrayHelper::get($bookingData, 'entry_id');
        $serviceId = ArrayHelper::get($bookingData, 'service_id');
        $providerId = ArrayHelper::get($bookingData, 'provider_id');
        $formId = ArrayHelper::get($bookingData, 'form_id');
        $bookingDate = ArrayHelper::get($dateTime, 'booking_date');
        $bookingTime = ArrayHelper::get($dateTime, 'booking_time');

        $response = (new DateTimeHandler($serviceId, $providerId, $formId, $bookingDate))->isValidSlot(
            $bookingTime
        );
        return array($bookingData, $bookingId, $entryId, $bookingDate, $bookingTime, $response);
    }
}
