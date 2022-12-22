<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Addons\GoogleCalendarController;
use FF_Booking\Booking\Models\BookingModel;
use FF_Booking\Booking\Models\ProviderModel;
use FluentForm\Framework\Helpers\ArrayHelper;

class FrontEndAjaxHandler
{
    public function init()
    {
        $route = sanitize_text_field($_REQUEST['route']);

        $this->handleEndpoint($route);
    }

    public function handleEndpoint($route)
    {
        $validRoutes = [
            'get_service_provider'            => 'getServiceProvider',
            'get_dates'                       => 'getDates',
            'get_time_slots'                  => 'getTimeSlots',
            'get_time_slots_booking_page'     => 'getTimeSlotsBookingPage',
            'reschedule_booking'              => 'rescheduleBooking',
            'cancel_booking'                  => 'cancelBooking',
            'update_provider_booking'         => 'updateProviderBooking',
            'update_provider_note'            => 'updateProviderNote',
            'save_google_calendar_code'       => 'saveGoogleCalendarCode',
            'disconnect_google_calendar_code' => 'disconnectGoogleCalendarCode',

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
            'service_id'  => 'required',
            'provider_id' => 'required',
            'form_id'     => 'required',
        ]);

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors'  => $errors,
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
            'service_id'  => 'required',
            'provider_id' => 'required',
            'form_id'     => 'required',
            'date'        => 'required',
        ], [
            'service_id.required'  => 'Service is required',
            'provider_id.required' => 'Provider is required',
            'form_id.required'     => 'Form missing',
            'date.required'        => 'Date missing',
        ]);

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors'  => $errors,
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
        BookingHelper::verifyRequest('ffs_booking_public_nonce');
        $bookingHash = sanitize_text_field($_REQUEST['bookingHash']);
        $reason = sanitize_textarea_field($_REQUEST['reason']);
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

        $ifProviderLoggedIn = $this->isProviderLoggedIn($bookingData);
        $actionBy = ($ifProviderLoggedIn) ? 'provider' : 'user';
        $isValid = $this->ifUserCanReschedule($actionBy, $bookingData);
        if (!$isValid) {
            wp_send_json(['message' => __('Permission Error', 'ff-simple-booking')]);
            return;
        }
        $rescheduleData = $this->getRescheduleData($bookingData, $actionBy, $reason);
        $updateData = [
            'booking_date'    => $bookingDate,
            'booking_time'    => $bookingTime,
            'reschedule_data' => \json_encode($rescheduleData)
        ];
        (new BookingModel())->update($bookingId, $updateData);

        do_action('ff_booking_status_changing', $bookingId, $entryId, 'rescheduled');


        wp_send_json_success([
            'message' => __('Booking has been updated succefully', 'ff-simple-booking')
        ]);
    }

    public function cancelBooking()
    {
        BookingHelper::verifyRequest('ffs_booking_public_nonce');

        $bookingHash = sanitize_text_field($_REQUEST['bookingHash']);
        $bookingData = (new BookingModel())->getBooking(['booking_hash' => $bookingHash]);
        $bookinEntryId = (int)ArrayHelper::get($bookingData, 'id');
        $entryId = (int)ArrayHelper::get($bookingData, 'entry_id');
        $updateData = [
            'booking_status' => 'canceled',
        ];

        do_action('ff_booking_status_changing', $bookinEntryId, $entryId, 'canceled');

        (new BookingModel())->update($bookinEntryId, $updateData);

        wp_send_json_success([
            'message' => __('Booking has been canceled succefully', 'ff-simple-booking')
        ]);
    }

    public function updateProviderBooking()
    {
        BookingHelper::verifyRequest('ffs_booking_public_nonce');

        $bookinEntryId = intval($_REQUEST['booking_id']);
        $status = sanitize_text_field($_REQUEST['status']);
        $bookingData = (new BookingModel())->getBooking(['id' => $bookinEntryId]);

        do_action('ff_booking_status_changing', $bookinEntryId, $bookingData['entry_id'], $status);

        $data['booking_status'] = $status;
        (new BookingModel())->update($bookinEntryId, $data);
        wp_send_json_success([
            'message' => __('Booking has been updated successfully,refreshing in 2 sec', 'ff-simple-booking')
        ]);
    }

    public function updateProviderNote()
    {
        BookingHelper::verifyRequest('ffs_booking_public_nonce');

        $bookingId = intval($_REQUEST['booking_id']);
        $notes = sanitize_textarea_field($_REQUEST['notes']);

        do_action('ff_booking_status_note_update', $bookingId, $notes);

        $data['notes'] = $notes;
        (new BookingModel())->update($bookingId, $data);
        wp_send_json_success([
            'message' => __('Note has been updated succefully, refreshing in 2 sec', 'ff-simple-booking')
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

    /**
     * @return bool
     */
    private function isProviderLoggedIn($data)
    {
        return get_current_user_id() == $data['assigned_user'];
    }

    private function ifUserCanReschedule($actionBy, $data)
    {
        if ($actionBy == 'user' && ArrayHelper::get($data, 'allow_user_reschedule') != 'yes') {
            return false;
        } elseif ($actionBy == 'provider' && BookingHelper::getSettingsByKey('allow_provider_reschedule')) {
            return false;
        }
        return true;
    }

    /**
     * @param $bookingData
     * @param string $actionBy
     * @param string $reason
     * @return mixed|null
     */
    private function getRescheduleData($bookingData, string $actionBy, string $reason)
    {
        $rescheduleData = \json_decode($bookingData['reschedule_data']);
        $rescheduleData[] = array(
            'action_by'        => $actionBy,
            'reason'           => $reason,
            'previous_booking' => BookingHelper::formatTime(
                $bookingData['booking_time']
            ) . ' ' . BookingHelper::formatDate($bookingData['booking_date']),
            'updated_at'       => date('Y-m-d')
        );
        return $rescheduleData;
    }

    public function saveGoogleCalendarCode()
    {
        BookingHelper::verifyRequest('ffs_booking_public_nonce');
        $data = [
            'access_code' => sanitize_text_field($_REQUEST['access_code']),
        ];
        (new GoogleCalendarController())->saveSettings($data);
    }
    public function disconnectGoogleCalendarCode()
    {
        BookingHelper::verifyRequest('ffs_booking_public_nonce');
        $data = [
            'access_code' => '',
        ];
        (new GoogleCalendarController())->saveSettings($data);
    }
}
