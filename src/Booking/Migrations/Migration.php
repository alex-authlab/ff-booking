<?php


namespace FF_Booking\Booking\Migrations;

use FF_Booking\Booking\Models\BookingModel;
use FF_Booking\Booking\Models\ProviderModel;
use FF_Booking\Booking\Models\ServiceModel;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Migration
{
    public function run($network_wide = false)
    {
        global $wpdb;
        if ($network_wide) {
            // Retrieve all site IDs from this network (WordPress >= 4.6 provides easy to use functions for that).
            if (function_exists('get_sites') && function_exists('get_current_network_id')) {
                $site_ids = get_sites(array('fields' => 'ids', 'network_id' => get_current_network_id()));
            } else {
                $site_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;");
            }
            // Install the plugin for all these sites.
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                $this->migrate();
                restore_current_blog();
            }
        } else {
            $this->migrate();
        }
    }

    public function migrate()
    {
        update_option('_ff_booking_status', 'yes');
        (new ServiceModel())->migrate();
        (new ProviderModel())->migrate();
        (new BookingModel())->migrate();
    }
}
