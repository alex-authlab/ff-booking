<?php

namespace FF_Booking\Booking;

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
//        if date is not selected return full booked dates & weekend & holiday dates & max allowed future dates
//     if date selected generate & reuturn timeslots
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
        ],[
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
        if ( $returnData['success'] == true) {
            if ( $returnData['slots'] = 'date') {
                wp_send_json_success([
                    'time_slots' => 'date'
                ]);
            }
            else if ( is_array($returnData['slots']) && count($returnData['slots']) > 0 ) {
                wp_send_json_success([
                    'time_slots' => $returnData['slots']
                ]);
            }
        }
        else
        wp_send_json([
            'message' => 'No Slot Found'
        ], 201);
    }
}
