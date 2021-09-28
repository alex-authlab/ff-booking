<?php

namespace FF_Booking\Booking;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FF_Booking\Booking\Components\Service;
use FF_Booking\Booking\Models\BookingModel;
use FF_Booking\Booking\Models\ProviderModel;
use FF_Booking\Booking\Models\ServiceModel;
use \FluentForm\App\Databases\Migrations\FormSubmissions;
use \FluentForm\App\Helpers\Helper;
use \FluentForm\Framework\Helpers\ArrayHelper;
use FF_Booking\Booking\Migrations\Migration;

class AjaxHandler
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
            'enable_booking' => 'enableBookingModule',
            'disable_booking' => 'disableBookingModule',
            'toggle_booking' => 'toggleBookingModule',
            'save_service' => 'saveService',
            'delete_service' => 'deleteService',
            'get_services' => 'getServices',
            'delete_service' => 'deleteService',
            'get_slots' => 'getSlots',
            'get_full_booked' => 'getFullBooked',
            'save_payment_method_settings' => 'savePaymentMethodSettings',
            'get_form_settings' => 'getFormSettings',
            'save_form_settings' => 'saveFormSettings',
            'update_transaction' => 'updateTransaction',
            'get_bookings' => 'getBookings',
            'get_providers' => 'getProviders',
            'save_providers' => 'saveProviders',
            'delete_provider' => 'deleteProvider',

        ];

        if (isset($validRoutes[$route])) {
            $this->{$validRoutes[$route]}();
        }

        die();
    }

    public function enableBookingModule()
    {
        $this->enable();
        // Update settings
        $settings = '';
        // send response to reload the page

        wp_send_json_success([
            'message' => __('Booking Module successfully enabled!', 'fluentformpro'),
            'settings' => $settings,
            'reload' => 'yes'
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

        // Update settings
        $settings = '';
        // send response to reload the page

        wp_send_json_success([
            'message' => __('Booking Module successfully enabled!', 'fluentformpro'),
            'settings' => $settings,
            'reload' => 'yes'
        ]);
    }

    private function enable()
    {
        update_option('_ff_booking_status', 'yes');

        global $wpdb;
        $table = $wpdb->prefix . '_alex_booking_services';
        $cols = $wpdb->get_col("DESC {$table}", 0);

        if ($cols && in_array('id', $cols)) {
            // We are good

        } else {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
            Migration::migrate();
            // Migrate the database
        }
    }

    public function saveService()
    {
        $service = wp_unslash($_REQUEST['service']);

        $validator = fluentValidator($service, [
            'title' => 'required',
            'show_booked_time' => 'required',
            'show_end_time' => 'required',
            'max_bookings' => 'required',
            'duration' => 'required',
            'gap_time' => 'required',
            'slot_capacity' => 'required',
            'status' => 'required'
        ]);

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors' => $errors,
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
            'message' => 'Service has been updated successfully',
            'service_id' => $serviceId
        ], 200);
    }

    public function getServices()
    {

        $data['available_forms'] = BookingHelper::getAvailableForms();
        $data['service'] = (new ServiceModel())->getServices(true);
        wp_send_json_success($data);
    }


    public function getSlots()
    {
        $data = $_REQUEST;

        if (empty($data['service_id'])) {
            wp_send_json_success([
                'html' => 'Please select a service first !',
            ]);
            return;
        }


        $slot_html = BookingHelper::getTimeSlotsHtml($data);
        wp_send_json_success([
            'html' => $slot_html,
        ]);
    }

    public function getFullBooked()
    {
        $data = $_REQUEST;

        $dates = BookingHelper::getFullBookedDate($data['service_id'], $data['form_id']);
        wp_send_json_success([
            'dates' => ($dates),
            'selected_date' => $data['selected_date']
        ], 200);
    }

    public function getBookings()
    {
        $req = $_REQUEST;
        $data = (new BookingModel())->bookings($req);
        wp_send_json_success($data, 200);
    }

    public function getProviders()
    {
        $providerModel = new ProviderModel();

        ob_start();
        $providers = $providerModel->getProviders(true);
        $errors = ob_get_clean();

        if ($errors) {
            (new ProviderModel())->migrate();
            $providers = $providerModel->getProviders(true);
        }

        $data = [
            'providers' => $providers
        ];

        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 1) {

            $forms = wpFluent()->table('fluentform_forms')
                ->select(['id', 'title'])
                ->get();
            $formattedForms = [];
            foreach ($forms as $form) {
                $formattedForms[$form->id] = $form->title;
            }
            $data['available_forms'] = $formattedForms;

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
        $validator = fluentValidator($provider,[
            'title'=>'required',
            'assigned_user'=>'required',
            'assigned_services'=>'required',
            'weekend_days'=>'required',
            'status'=>'required',
            'start_time'=>'required',
        ]);

        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
            wp_send_json([
                'errors' => $errors,
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
            'message' => 'Provider has been updated successfully',
            'provider_id' => $providerId
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

}
