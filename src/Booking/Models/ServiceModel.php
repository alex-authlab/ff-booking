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
        foreach ($services['data'] as $service) {
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

    public function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . $this->table;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT,
				title varchar(192),
				duration tinytext(10),
				gap_time tinytext(10) NULL,
				slot_capacity int(11) NULL,
				time_format tinytext(10) NULL,
				show_end_time tinytext(10) NULL,
				show_booked_time tinytext(10) NULL,
				booking_type tinytext(15) DEFAULT 'time_slot',
				max_bookings int(11) NULL,
				allowed_form_ids varchar (255) NULL,
				allowed_future_days varchar (20) NULL,
				status tinytext(10) DEFAULT 'active',
				calc_value int(11) NULL,
				created_by int(11) NULL,
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id)
			  ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);
        }
    }

}
