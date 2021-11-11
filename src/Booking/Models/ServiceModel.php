<?php

namespace FF_Booking\Booking\Models;

use FluentForm\Framework\Helpers\ArrayHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ServiceModel
{
    private $table = 'ff_booking_services';

    public function getServices($paginate = false)
    {
        $query = wpFluent()->table($this->table);
        if ($paginate) {
            $services = $query->paginate();
            foreach ($services['data'] as $service) {
                $service->allowed_form_ids = maybe_unserialize($service->allowed_form_ids);
                $service->allowed_future_date_range = maybe_unserialize($service->allowed_future_date_range);
                $service->required_fields = maybe_unserialize($service->required_fields);
                if(isset($service->notifications)){
                    $service->notifications  = json_decode($service->notifications);
                }
            }
            return $services;
        }
        $services = $query->get();
        foreach ($services as $service) {
            $service->allowed_form_ids = maybe_unserialize($service->allowed_form_ids);
            $service->allowed_future_date_range = maybe_unserialize($service->allowed_future_date_range);
            $service->required_fields = maybe_unserialize($service->required_fields);
            $service->notifications = json_decode($service->notifications);
        }

        return $services;
    }

    public function insert($data)
    {
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        $data['created_by'] = get_current_user_id();
        if(isset($data['allowed_form_ids'])) {
            $data['allowed_form_ids'] = maybe_serialize($data['allowed_form_ids']);
        }
        if(isset($data['allowed_future_date_range'])) {
            $data['allowed_future_date_range'] = maybe_serialize($data['allowed_future_date_range']);
        }
        if(isset($data['required_fields'])) {
            $data['required_fields'] = maybe_serialize($data['required_fields']);
        }
        if(isset($data['notifications'])) {
            $data['notifications'] = json_encode($data['notifications']);
        }

        return wpFluent()->table($this->table)
            ->insert($data);
    }

    public function update($id, $data)
    {
        $data['updated_at'] = current_time('mysql');
        if(isset($data['allowed_form_ids'])) {
            $data['allowed_form_ids'] = maybe_serialize($data['allowed_form_ids']);
        }else{
            $data['allowed_form_ids']='';
        }
        if(isset($data['allowed_future_date_range'])) {
            $data['allowed_future_date_range'] = maybe_serialize($data['allowed_future_date_range']);
        }
        if(isset($data['required_fields'])) {
            $data['required_fields'] = maybe_serialize($data['required_fields']);
        }
        if(isset($data['notifications'])) {
            $data['notifications'] = json_encode($data['notifications']);
        }
        return wpFluent()->table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    public function delete($id)
    {
        return wpFluent()->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    public function getService($serviceId)
    {
        $query = wpFluent()->table($this->table);
        $query->where('id',$serviceId);
        $query->where('status','active');
        $service = $query->first();
        if(!$service){
            return false;
        }
        $service->allowed_form_ids = maybe_unserialize($service->allowed_form_ids);
        $service->allowed_future_date_range = maybe_unserialize($service->allowed_future_date_range);
        $service->required_fields = maybe_unserialize($service->required_fields);
        $service->notifications = json_decode($service->notifications);



        return $service;
    }


    public function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . $this->table;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
				       id int NOT NULL AUTO_INCREMENT,
                      title varchar(255) DEFAULT NULL,
                      service_type varchar(100) DEFAULT NULL,
                      booking_type varchar(100) DEFAULT NULL,
                      capacity_type varchar(100) DEFAULT NULL,
                      range_type varchar(100) DEFAULT NULL,
                      slot_capacity int(11) DEFAULT NULL,
                      max_bookings int(11) DEFAULT NULL,
                      duration varchar (100) DEFAULT NULL,
                      append_info varchar(10) DEFAULT NULL,
                      gap_time_after varchar(255) DEFAULT NULL,
                      show_end_time varchar(10) DEFAULT NULL,
                      show_remaining_slot varchar(10) DEFAULT NULL,
                      show_booked_time varchar(10) DEFAULT NULL,
                      status varchar(192) DEFAULT 'active',
                      default_booking_status varchar(100)  DEFAULT NULL,
                      allowed_form_ids varchar(100)  DEFAULT NULL,
                      allowed_future_days varchar(100)  DEFAULT NULL,
                      allowed_future_date_range varchar(100)  DEFAULT NULL,
                      disable_booking_before varchar(100)  DEFAULT NULL,
                      in_person_location varchar(255)  DEFAULT NULL,
                      description varchar(255)  DEFAULT NULL,
                      color varchar(100)  DEFAULT NULL,
                      allow_user_reschedule varchar(255)  DEFAULT NULL,
                      allow_user_cancel varchar(255)  DEFAULT NULL,
                      required_fields varchar(255)  DEFAULT NULL,
                      notifications LONGTEXT  DEFAULT NULL,
                      policy  TEXT  DEFAULT NULL,
                      created_by int DEFAULT NULL,
                      calc_value int DEFAULT NULL,
                      created_at timestamp NULL,
                      updated_at timestamp NULL,
				      PRIMARY  KEY  (id)
			  ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }


}
