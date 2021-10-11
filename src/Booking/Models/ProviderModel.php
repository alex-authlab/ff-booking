<?php

namespace FF_Booking\Booking\Models;

use FluentForm\Framework\Helpers\ArrayHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ProviderModel
{
    private $table = 'ff_booking_providers';

    public function getProviders($paginate = false)
    {
        $query = wpFluent()->table($this->table);
        if ($paginate) {
            $providers = $query->paginate();
            foreach ($providers['data'] as $provider) {
                $provider->allowed_form_ids = maybe_unserialize($provider->allowed_form_ids);
                $provider->assigned_services = maybe_unserialize($provider->assigned_services);
                $provider->weekend_days = maybe_unserialize($provider->weekend_days);
                $provider->holiday_dates = maybe_unserialize($provider->holiday_dates);
            }
            return $providers;
        }
        $providers = $query->get();
        foreach ($providers as $provider) {
            $provider->allowed_form_ids = maybe_unserialize($provider->allowed_form_ids);
            $provider->assigned_services = maybe_unserialize($provider->assigned_services);
            $provider->weekend_days = maybe_unserialize($provider->weekend_days);
            $provider->holiday_dates = maybe_unserialize($provider->holiday_dates);
        }

        return $providers;
    }

    public function getServiceProvider($serviceId,$formId)
    {
        $query = wpFluent()->table($this->table);
        $query->where('status','active');
        $providers = $query->get();
        $validProviders = [];
        foreach ($providers as $key => $provider) {
            $provider->allowed_form_ids = maybe_unserialize($provider->allowed_form_ids);
            //check with allowed forms and services
            if ($formIds = $provider->allowed_form_ids) {
                if (!in_array($formId, $formIds)) {
                    unset($providers[$key]);
                }
            }
            $provider->assigned_services = maybe_unserialize($provider->assigned_services);
            if ($assignedServices = $provider->assigned_services) {
                if (!in_array($serviceId, $assignedServices)) {
                    unset($providers[$key]);
                }
            }
            $provider->weekend_days = maybe_unserialize($provider->weekend_days);
            $provider->holiday_dates = maybe_unserialize($provider->holiday_dates);
            $validProviders[] =$provider;
        }
        return $providers;



    }
    public function insert($data)
    {
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        $data['created_by'] = get_current_user_id();
        if(isset($data['allowed_form_ids'])) {
            $data['allowed_form_ids'] = maybe_serialize($data['allowed_form_ids']);
        }
        if(isset($data['assigned_services'])) {
            $data['assigned_services'] = maybe_serialize($data['assigned_services']);
        }
        if(isset($data['weekend_days'])) {
            $data['weekend_days'] = maybe_serialize($data['weekend_days']);
        }
        if(isset($data['holiday_dates'])) {
            $data['holiday_dates'] = maybe_serialize($data['holiday_dates']);
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
        if(isset($data['assigned_services'])) {
            $data['assigned_services'] = maybe_serialize($data['assigned_services']);
        }
        if(isset($data['weekend_days'])) {
            $data['weekend_days'] = maybe_serialize($data['weekend_days']);
        }
        if(isset($data['holiday_dates'])) {
            $data['holiday_dates'] = maybe_serialize($data['holiday_dates']);
        }

        if(isset($data['settings'])) {
            $data['settings'] = maybe_serialize($data['settings']);
        }

        return wpFluent()->table($this->table)
            ->where('id', $id)
            ->update($data);
    }
    public function getProvider($id)
    {
        $query = wpFluent()->table($this->table);
        $query->where('id',$id);
        $query->where('status','active');
        $provider = $query->first();
        if(!$provider){
            return false;
        }
        if($provider->weekend_days && $provider->weekend_days == ''){
            $provider->weekend_days= [];
        }
        if($provider->holiday_dates  && $provider->holiday_dates == ''){
            $provider->holiday_dates= [];
        }
        if($provider->allowed_form_ids  && $provider->allowed_form_ids == ''){
            $provider->allowed_form_ids= [];
        }
        if($provider->assigned_services  && $provider->assigned_services == ''){
            $provider->assigned_services= [];
        }
        $provider->assigned_services = maybe_unserialize($provider->assigned_services);
        $provider->allowed_form_ids = maybe_unserialize($provider->allowed_form_ids);
        $provider->weekend_days = maybe_unserialize($provider->weekend_days);
        $provider->holiday_dates = maybe_unserialize($provider->holiday_dates);
        return $provider;

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
				assigned_user INT(11),
				assigned_services varchar(100) NULL,
				allowed_form_ids varchar (100) NULL,
				weekend_days varchar(100) NULL,
				holiday_dates varchar(255) NULL,
				start_time varchar(10) NULL,
				end_time varchar(10) NULL,
				status varchar(10) DEFAULT 'active',
				created_by INT(11) NULL,
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id)
			  ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);
        }
    }

}
