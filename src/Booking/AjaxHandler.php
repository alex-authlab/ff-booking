<?php

namespace FF_Booking\Booking;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FF_Booking\Booking\Components\Service;
use FF_Booking\Booking\Models\BookingModel;
use FF_Booking\Booking\Models\ProviderModel;
use FF_Booking\Booking\Models\ServiceModel;
use FluentForm\App\Databases\Migrations\FormSubmissions;
use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;
use FF_Booking\Booking\Migrations\Migration;

class AjaxHandler
{
    public function init()
    {
        $route = sanitize_text_field($_REQUEST['route']);
        BookingHelper::verifyRequest();

        $this->handleEndpoint($route);
    }


    public function handleEndpoint($route)
    {
        $validRoutes = [
            'enable_booking'               => 'enableBookingModule',
            'disable_booking'              => 'disableBookingModule',
            'toggle_booking'               => 'toggleBookingModule',
            'save_service'                 => 'saveService',
            'delete_service'               => 'deleteService',
            'get_service'                  => 'getService',
            'get_services'                 => 'getServices',
            'save_payment_method_settings' => 'savePaymentMethodSettings',
            'get_form_settings'            => 'getFormSettings',
            'save_form_settings'           => 'saveFormSettings',
            'get_bookings'                 => 'getBookings',
            'get_booking_info'             => 'getBookingInfo',
            'update_booking'               => 'updateBooking',
            'get_providers'                => 'getProviders',
            'save_providers'               => 'saveProviders',
            'delete_provider'              => 'deleteProvider',
            'change_status_booking'        => 'changeStatusBooking',
            'update_user_notify_stat'      => 'updateUserNotifyStat',
            'save_settings'                => 'saveSettings',
            'get_settings'                 => 'getSettings',

        ];

        if (isset($validRoutes[$route])) {
            $this->{$validRoutes[$route]}();
        }

        die();
    }

    public function enableBookingModule()
    {
        $this->enable();

        wp_send_json_success([
            'message'  => __('Booking Module successfully enabled!', FF_BOOKING_SLUG),
            'settings' => '',
            'reload'   => 'yes'
        ]);
    }

    public function toggleBookingModule()
    {
        $status = sanitize_text_field($_REQUEST['status']);
        if ($status) {
            $this->enableBookingModule();
        } else {
            $this->disableBookingModule();
        }
    }

    public function disableBookingModule()
    {
        update_option('_ff_booking_status', '0', false);

        wp_send_json_success([
            'message'  => __('Booking Module successfully disabled!', FF_BOOKING_SLUG),
            'settings' => '',
            'reload'   => 'yes'
        ]);
    }

    private function enable()
    {
        update_option('_ff_booking_status', 'yes');

        global $wpdb;
        $table = $wpdb->prefix . 'ff_booking_entries';
        $cols = $wpdb->get_col("DESC {$table}", 0);

        if ($cols && in_array('id', $cols)) {
            // check db version
        } else {
            Migration::run();
        }
    }

    public function saveService()
    {
        $service = wp_unslash($_REQUEST['service']);
        $validator = fluentValidator($service, [
            'title'                  => 'required',
            'service_type'           => 'required',
            'booking_type'           => 'required',
            'capacity_type'          => 'required',
            'default_booking_status' => 'required',
            'range_type'             => 'required',
            'max_bookings'           => 'required',
            'status'                 => 'required'
        ], [
            'title.required'                  => 'The Service Title field is required.',
            'service_type.required'           => 'Service Type field is required.',
            'booking_type.required'           => 'Booking Type field is required.',
            'capacity_type.required'          => 'Booking Capacity Type is required.',
            'status.required'                 => 'Service Status is required.',
            'max_bookings.required'           => 'Max Bookings is required.',
            'default_booking_status.required' => 'Default Booking Status is required.',
        ]);
        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors'  => $errors,
                'message' => 'Please fill up all the required fields'
            ], 423);
        }

        $serviceId = false;
        if (isset($service['id'])) {
            $serviceId = $service['id'];
            unset($service['id']);
        }

        if ($serviceId) {
            (new ServiceModel())->update($serviceId, $service);
        } else {
            $serviceId = (new ServiceModel())->insert($service);
        }

        wp_send_json_success([
            'message'    => 'Service has been updated successfully',
            'service_id' => $serviceId
        ], 200);
    }

    public function getServices()
    {
        $serviceModel = new ServiceModel();
        ob_start();
        $services = $serviceModel->getServices(true);
        $errors = ob_get_clean();

        if ($errors) {
            $serviceModel->migrate();
            $services = $serviceModel->getServices(true);
        }
        $data['available_forms'] = BookingHelper::getAvailableForms();
        $data['service'] = $services;

        wp_send_json_success($data);
    }

    public function getService()
    {
        $serviceId = intval($_REQUEST['service_id']);
        $today = date("Y-m-d");

        $serviceData = [
            'title'                     => '',
            'status'                    => 'active',
            'service_type'              => 'in_person',
            'range_type'                => 'days',
            'booking_type'              => 'time_slot',
            'duration'                  => '01:00',
            'gap_time_after'            => '00:30',
            'capacity_type'             => 'single',
            'show_end_time'             => 'show',
            'show_booked_time'          => 'show',
            'default_booking_status'    => 'pending',
            'append_info'               => 'yes',
            'allowed_future_days'       => '1 Month',
            'show_remaining_slot'       => 'hide',
            'slot_capacity'             => 1,
            'allowed_future_date_range' => $today . '-' . date('Y-m-d', strtotime('+1 month', strtotime($today))),
            'color'                     => '#BAE0F1',
            'disable_booking_before'    => '1 Day',
            'allow_user_cancel'         => 'no',
            'allow_user_reschedule'     => 'no',
            'max_bookings'              => 20,
            'notifications'             => $this->defaultEmails()
        ];

        if ($serviceId) {
            $serviceModel = new ServiceModel();
            $data = (array)$serviceModel->getService($serviceId);
            if (!$data) {
                wp_send_json([
                    'message' => 'Invalid Service ID'
                ], 423);
            }
            $serviceData = wp_parse_args($data, $serviceData);
        }
        $data['available_forms'] = BookingHelper::getAvailableForms();
        $data['service'] = $serviceData;
        wp_send_json_success($data);
    }


    public function getBookings()
    {
        $data['bookings'] = (new BookingModel())->getBookings(true);
        wp_send_json_success($data, 200);
    }

    public function getProviders()
    {
        $providerModel = new ProviderModel();
        ob_start();
        $providers = $providerModel->getProviders(true);
        $errors = ob_get_clean();

        if ($errors) {
            $providerModel->migrate();
            $providers = $providerModel->getProviders(true);
        }

        $data = [
            'providers' => $providers
        ];

        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 1) {
            $data['available_forms'] = BookingHelper::getAvailableForms();

            $users = get_users(array('fields' => array('ID', 'display_name')));
            $formattedUsers = [];
            foreach ($users as $user) {
                $formattedUsers[$user->ID] = $user->display_name;
            }
            $data['users'] = $formattedUsers;

            $services = (new ServiceModel())->getServices();
            $formattedServices = [];
            foreach ($services as $service) {
                $formattedServices[$service->id] = $service->title;
            }
            $data['services'] = $formattedServices;
        }

        wp_send_json_success($data, 200);
    }

    public function saveProviders()
    {
        $provider = wp_unslash($_REQUEST['provider']);
        $validator = fluentValidator($provider, [
            'title'             => 'required',
            'assigned_user'     => 'required',
            'assigned_services' => 'required',
            'status'            => 'required',
            'start_time'        => 'required',
        ]);

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors'  => $errors,
                'message' => 'Please fill up all the required fields'
            ], 423);
        }
        $providerId = false;

        if (isset($provider['id'])) {
            $providerId = $provider['id'];
            unset($provider['id']);
        }

        if ($providerId) {
            (new ProviderModel())->update($providerId, $provider);
        } else {
            $providerId = (new ProviderModel())->insert($provider);
        }

        wp_send_json_success([
            'message'     => 'Provider has been updated successfully',
            'provider_id' => $providerId
        ], 200);
    }

    public function getBookingInfo()
    {
        $booking_id = intval($_REQUEST['booking_id']);
        $booking_info = (new BookingModel())->getBookings(false, ['id' => $booking_id]);
        if (!is_array($booking_info) || count($booking_info) != 1) {
            wp_send_json_error([
                'message' => 'No Data Found',
            ], 200);

            return;
        }
        $booking_info = (array)array_shift($booking_info);
        $booking_info['booking_time'] = BookingHelper::convertTime(
            '12',
            ArrayHelper::get($booking_info, 'booking_time')
        );

        $providers = (new ProviderModel())->getProviders();
        $formattedProviders = [];
        foreach ($providers as $provider) {
            $formattedProviders[$provider->id] = $provider->title;
        }
        $services = (new ServiceModel())->getServices();
        $formattedService = [];
        foreach ($services as $service) {
            $formattedService[$service->id] = $service->title;
        }
        $data['provider_list'] = $formattedProviders;
        $data['service_list'] = $formattedService;
        $data['booking_info'] = $booking_info;
        wp_send_json_success($data, 200);
    }

    public function updateBooking()
    {
        $info = fluentFormSanitizer($_REQUEST['booking_info']);
        $bookingId = ArrayHelper::get($info, 'id');
        $entryId = ArrayHelper::get($info, 'entry_id');
        $serviceId = ArrayHelper::get($info, 'service_id');
        $providerId = ArrayHelper::get($info, 'provider_id');
        $bookingDate = ArrayHelper::get($info, 'booking_date');
        $bookingTime = ArrayHelper::get($info, 'booking_time');
        $info['booking_time'] = $bookingTime = BookingHelper::convertTime('24', $bookingTime);
        $formId = ArrayHelper::get($info, 'form_id');

        $validator = fluentValidator($info, [
            'id'           => 'required',
            'service_id'   => 'required',
            'provider_id'  => 'required',
            'booking_date' => 'required',
            'booking_time' => 'required',
            'form_id'      => 'required',
        ]);
        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors'  => $errors,
                'message' => 'Please fill up all the required fields'
            ], 423);
        }
        //validate date time slot
        $response = (new DateTimeHandler($serviceId, $providerId, $formId, $bookingDate))->isValidSlot(
            $bookingTime,
            $bookingId
        );
        if ($response['status'] != true) {
            wp_send_json([
                'message' => $response['message']
            ], 423);
            return;
        }
        do_action('ff_booking_status_changing', $bookingId, $entryId, 'rescheduled');

        $bookingId = (new BookingModel())->update($bookingId, $info);

        wp_send_json_success([
            'message'    => 'Booking info has been updated successfully',
            'booking_id' => $bookingId
        ], 200);
    }

    // to do validate
    public function changeStatusBooking()
    {
        $bookinEntryId = intval($_REQUEST['booking_id']);
        $insertId = intval($_REQUEST['entry_id']);
        $bookingStatus = sanitize_text_field($_REQUEST['booking_Status']);
        do_action("ff_booking_{$bookingStatus}", $bookinEntryId, $insertId);
        do_action('ff_booking_status_changing', $bookinEntryId, $insertId, $bookingStatus);
        $data['booking_status'] = $bookingStatus;
        (new BookingModel())->update($bookinEntryId, $data);
        wp_send_json_success([
            'message' => 'Booking Status has been updated succesfully',
        ], 200);
    }

    public function updateUserNotifyStat()
    {
        $data = wp_unslash($_REQUEST['data']);
        $data = json_decode($data, true);
        $bookingId = intval($data['booking_id']);
        $bookingStatus = sanitize_text_field($data['send_notification']);
        $updateData['send_notification'] = $bookingStatus;

        (new BookingModel())->update($bookingId, $updateData);
        wp_send_json_success([
            'message' => __('Send User notification status has been updated successfully', FF_BOOKING_SLUG)
        ], 200);
    }

    public function deleteService()
    {
        $serviceId = intval($_REQUEST['service_id']);
        (new ServiceModel())->delete($serviceId);
        wp_send_json_success([
            'message'   => 'Service has been successfully deleted',
            'coupon_id' => $serviceId
        ], 200);
    }

    public function deleteProvider()
    {
        $providerId = intval($_REQUEST['provider_id']);
        (new ProviderModel())->delete($providerId);
        wp_send_json_success([
            'message'   => 'Provider has been successfully deleted',
            'coupon_id' => $providerId
        ], 200);
    }

    public function saveSettings()
    {
        $settings = wp_unslash($_REQUEST['settings_data']);
        update_option('__ff_booking_general_settings', wp_unslash($settings));
        wp_send_json_success([
            'message' => 'Settings has been Updated Succesfully',
        ], 200);
    }


    public function getSettings()
    {
        $settings = get_option('__ff_booking_general_settings');
        wp_send_json_success([
            'settings_data' => $settings ? json_decode($settings) : false
        ], 200);
    }

    /**
     * @return array[]
     * notification for all type of status using keys
     */
    private function defaultEmails(): array
    {
        return [
            'user'     => [
                'booked'      => [
                    'body'    => 'Hello {ff_booking_user_name},
<p>You have successfully booked <b>{ff_booking_service}</b> appointment with <b>{ff_booking_provider}</b> on <b>{ff_booking_date_time}</b>. </p>
<p>Thank you </p>'
                    ,
                    'status'  => 'yes',
                    'subject' => 'Booking Confirmed'
                ],
                'pending'     => [
                    'body'    => 'Hello {ff_booking_user_name},
<p>You have a pending <b>{ff_booking_service}</b> appointment with <b>{ff_booking_provider}</b> on <b>{ff_booking_date_time}</b>. </p>
<p>Thank you </p>',
                    'status'  => 'yes',
                    'subject' => 'Booking Pending'
                ],
                'canceled'    => (object)[],
                'rescheduled' => (object)[],

            ],
            'provider' => [
                'booked'      => [
                    'body'    => 'Hello {ff_booking_provider},
<p>You have a new booked appointment for {ff_booking_service} on {ff_booking_date_time}. </p>
<p>Thank you </p>',
                    'status'  => 'yes',
                    'subject' => 'Booking Confirmed'

                ],
                'pending'     => [
                    'body'    => 'Hello {ff_booking_provider},
<p>You have a new pending appointment for <b>{ff_booking_service}</b> on <b>{ff_booking_date_time}</b>. </p>
<p>Thank you </p>',
                    'status'  => 'yes',
                    'subject' => 'Booking Pending'
                ],
                'canceled'    => (object)[],
                'rescheduled' => (object)[],
            ],
        ];
    }
}
