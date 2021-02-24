<?php

namespace FF_Booking\Booking\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Services
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . '_alex_booking_services';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT,
				name varchar(255) ,
				duration varchar(100),
				details varchar(500),
				start_time varchar(100),
				end_time varchar(100),
				status varchar(100),
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id)
			  ) $charsetCollate;";

            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);
        }
    }
}
