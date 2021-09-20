<?php

namespace FF_Booking\Booking;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

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
            'enable_booking'               => 'enableBookingModule',
            'disable_booking'              => 'disableBookingModule',
            'update_service'               => 'updateService',
            'delete_service'               => 'deleteService',
            'get_service'                  => 'getService',
            'get_slots'                    => 'getSlots',
            'get_full_booked'              => 'getFullBooked',
            'save_payment_method_settings' => 'savePaymentMethodSettings',
            'get_form_settings'            => 'getFormSettings',
            'save_form_settings'           => 'saveFormSettings',
            'update_transaction'           => 'updateTransaction'
        ];
        
        if (isset($validRoutes[$route])) {
            $this->{$validRoutes[$route]}();
        }
        
        die();
    }
    public function enableBookingModule()
    {
        $this->upgradeDb();
        // Update settings
        $settings = '';
        // send response to reload the page
        
        wp_send_json_success([
            'message'  => __('Booking Module successfully enabled!', 'fluentformpro'),
            'settings' => $settings,
            'reload'   => 'yes'
        ]);
    }
    
    public function disableBookingModule()
    {
        update_option ('_ff_booking_status','0',false);
    
        // Update settings
        $settings = '';
        // send response to reload the page
    
        wp_send_json_success([
            'message'  => __('Booking Module successfully enabled!', 'fluentformpro'),
            'settings' => $settings,
            'reload'   => 'yes'
        ]);
    }
    
    private function upgradeDB()
    {
        update_option ('_ff_booking_status','yes');
    
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
    
    public function updateService()
    {
        $data = wp_unslash($_REQUEST['data']);
        
        if($data['id']){
                wpFluent()->table('_alex_booking_services')
              ->where('id', $data['id'])
              ->update([
                  'name' => $data['name'],
                  'duration' => $data['duration'],
                  'details' => $data['details'],
                  'start_time' => $data['startTime'],
                  'end_time' => $data['endTime'],
                  'updated_at' => current_time('mysql')
              ]);
        } else{
            
            wpFluent()->table('_alex_booking_services')->insert([
                'name' => $data['name'],
                'duration' => $data['duration'],
                'details' => $data['details'],
                'start_time' => $data['startTime'],
                'end_time' => $data['endTime'],
                'updated_at' => current_time('mysql'),
                'created_at' => current_time('mysql')
            ]);
            
        }
        
        wp_send_json_success([
            'message'  => __('Service successfully updated!', 'fluentformpro'),
        ]);
        
    }
    
    public function getService()
    {
        $serviceData = [];
        $id = isset($_REQUEST['id'])?$_REQUEST['id'] : '' ;
        $serviceData = BookingHelper::getService ($id);
        wp_send_json_success([
            'data'  => $serviceData,
        ]);
        
       
    }
    
    public function getSlots()
    {
        $data = $_REQUEST;
        
        if(empty($data['service_id'])){
            wp_send_json_success([
                'html'  => 'Please select a service first !',
            ]);
            return;
        }
    
        
        $slot_html =  BookingHelper::getTimeSlotsHtml($data);
        wp_send_json_success([
            'html'  => $slot_html,
        ]);
        
    }
    
    public function getFullBooked()
    {
        $data = $_REQUEST;
      
        $dates =  BookingHelper::getFullBookedDate ($data['service_id'],$data['form_id']);
        wp_send_json_success([
            'dates'        =>  ($dates),
            'selected_date' =>$data['selected_date']
        ], 200);
    }

    public function deleteService(){

        $id = $_REQUEST['data']['id'];
        if($id){

            wpFluent()->table('_alex_booking_services')
            ->where('id', $id)
            ->delete();

             wp_send_json_success([
                'deleted'        =>  true,
            ], 200);
        }
        wp_send_json_success([
            'deleted'        =>  false,
        ], 200);
        
    }
    
  
    
  
    
  
}
