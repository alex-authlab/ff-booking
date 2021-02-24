<?php

namespace FF_Booking\Booking\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Bookings
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

        $table = $wpdb->prefix . 'alex_booking_entries';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT,
				form_id int(100) ,
				service_id int(100) ,
				entry_id int(100) ,
				user_id int(100) NULL,
				date DATE NOT NULL,
				time varchar(100),
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
