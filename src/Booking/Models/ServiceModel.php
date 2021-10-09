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
            }
            return $services;
        }
        $services = $query->get();
        foreach ($services as $service) {
            $service->allowed_form_ids = maybe_unserialize($service->allowed_form_ids);
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

        return wpFluent()->table($this->table)
            ->insert($data);
    }

    public function update($id, $data)
    {
        $data['updated_at'] = current_time('mysql');
        if(isset($data['allowed_form_ids'])) {
            $data['allowed_form_ids'] = maybe_serialize($data['allowed_form_ids']);
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
        if($service->allowed_form_ids == ''){
            $service->allowed_form_ids= [];
        }
        $service->allowed_form_ids = maybe_unserialize($service->allowed_form_ids);
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
                      title varchar(192) DEFAULT NULL,
                      duration varchar (20) DEFAULT NULL,
                      gap_time varchar(255) DEFAULT NULL,
                      slot_capacity varchar(10) DEFAULT NULL,
                      time_format varchar(255) DEFAULT NULL,
                      booking_type varchar(100)  DEFAULT 'time_slot',
                      show_end_time varchar(10) DEFAULT NULL,
                      show_booked_time varchar(10) DEFAULT NULL,
                      max_bookings int DEFAULT NULL,
                      status varchar(192) DEFAULT 'active',
                      default_booking_status varchar(100)  DEFAULT NULL,
                      allowed_form_ids varchar(100)  DEFAULT NULL,
                      allowed_future_days varchar(100)  DEFAULT NULL,
                      created_by int DEFAULT NULL,
                      calc_value int DEFAULT NULL,
                      created_at timestamp DEFAULT NULL,
                      updated_at timestamp DEFAULT NULL,
				      PRIMARY  KEY  (id)
			  ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }


}
