<?php

namespace FF_Booking\Booking\Models;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class BookingModel
{
    private $table = 'ff_booking_entries';
    private $fields;

    public function getBookings($paginate = false, $atts = [])
    {
        $endDate = date('Y-m-d H:i:s', strtotime('+30 days'));
        $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        $ranges = ArrayHelper::get($_REQUEST, 'date_range');
        if (!empty($ranges[0])) {
            $startDate = date('Y-m-d H:i:s', strtotime($ranges[0]));
        }
        if (!empty($ranges[1])) {
            $endDate = $ranges[1];
            $endDate .= ' 23:59:59';
        }

        $defaultAtts = [
            'id' => null,
            'entry_id' => null,
            'search' => '',
            'status' => 'all',
            'sort_column' => 'id',
            'sort_by' => 'DESC',
        ];

        $atts = wp_parse_args($atts, $defaultAtts);

        $search = ArrayHelper::get($atts, 'search', '');
        $booking_status = ArrayHelper::get($atts, 'booking_status', 'all');
        $booking_id = ArrayHelper::get($atts, 'id', false);
        $booking_hash = ArrayHelper::get($atts, 'booking_hash', false);
        $entry_id = ArrayHelper::get($atts, 'entry_id', false);

        global $wpdb;

        $query = wpFluent()->table($this->table);
        $this->fields = [
            $this->table . '.id',
            $this->table . '.form_id',
            $this->table . '.booking_hash',
            $this->table . '.name',
            $this->table . '.email',
            $this->table . '.booking_date',
            $this->table . '.booking_time',
            $this->table . '.booking_status',
            $this->table . '.notes',
            $this->table . '.created_at',
            $this->table . '.user_id',
            $this->table . '.entry_id',
            $this->table . '.send_notification',
            wpFluent()->raw($wpdb->prefix . 'ff_booking_providers.title AS provider'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_providers.id AS provider_id'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.title AS service'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.notifications'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.duration'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.policy'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.description'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.append_info'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.allow_user_reschedule'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.allow_user_cancel'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.booking_type'),
            wpFluent()->raw($wpdb->prefix . 'ff_booking_services.id AS service_id'),
            wpFluent()->raw($wpdb->prefix . 'fluentform_forms.title AS form_title'),
        ];
        $query->select($this->fields);
        if ($booking_id) {
            $query->where($this->table . '.id', '=', $booking_id);
        }
        if ($booking_hash) {
            $query->where($this->table . '.booking_hash', '=', $booking_hash);
        }
        if ($entry_id) {
            $query->where($this->table . '.entry_id', '=', $entry_id);
        }
        $query->leftJoin('fluentform_forms', 'fluentform_forms.id', '=', $this->table . '.form_id');
        $query->join('ff_booking_providers', 'ff_booking_providers.id', '=', $this->table . '.provider_id');
        $query->join('ff_booking_services', 'ff_booking_services.id', '=', $this->table . '.service_id');
        if ($booking_status && $booking_status != 'all') {
            $query->where('booking_status', $booking_status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', '%' . $search . '%');
                $q->orWhere('title', 'LIKE', '%' . $search . '%');
            });
        }

        $query->where($this->table . '.created_at', '>=', $startDate);
        $query->where($this->table . '.created_at', '<=', $endDate);
        $query->orderBy('id', 'DESC');

        if ($paginate) {
            $bookings = $query->paginate(null, $this->fields);
            foreach ($bookings['data'] as $index => $datum) {
                $bookings['data'][$index]->submission_url = admin_url(
                    'admin.php?page=fluent_forms&route=entries&form_id=' . $datum->form_id . '#/entries/' . $datum->entry_id
                );
                $bookings['data'][$index]->human_date = human_time_diff(
                    strtotime($datum->created_at),
                    strtotime(current_time('mysql'))
                );
            }

            return $bookings;
        }
        $bookings = $query->get();
        foreach ($bookings as $index => $datum) {
            $bookings[$index]->submission_url = admin_url(
                'admin.php?page=fluent_forms&route=entries&form_id=' . $datum->form_id . '#/entries/' . $datum->entry_id
            );
            $bookings[$index]->human_date = human_time_diff(
                strtotime($datum->created_at),
                strtotime(current_time('mysql'))
            );
        }
        return $bookings;
    }

    public function insert($data)
    {
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        return wpFluent()->table($this->table)
            ->insert($data);
    }

    public function bookedSlots($serviceId, $providerId, $formId, $range, $date = '')
    {
        $maxRange = date('Y-m-d', strtotime($range[1]));
        $query = wpFluent()->table($this->table);
        $query->where('service_id', $serviceId);
        $query->where('provider_id', $providerId);
        $query->where('form_id', $formId);
        $query->where('booking_status', '!=', 'draft');
        $query->where('booking_status', '!=', 'declined');
        if ($date != '') {
            $query->where('booking_date', $date);
        }
        $query->where('booking_date', '<=', $maxRange);
        return $query->get();
    }

    // to do ignore some draft status to allow booking
    public function bookedSlotGroupByDate($serviceId, $providerId, $formId, $min, $max)
    {
        $minRange = date('Y-m-d', strtotime($min));
        $maxRange = date('Y-m-d', strtotime($max));

        $query = wpFluent()->table($this->table);
        $query->select(array('booking_date', wpFluent()->raw('COUNT(id) as total_booked')));
        $query->groupBy('booking_date');
        $query->where('form_id', $formId);
        $query->where('service_id', $serviceId);
        $query->where('provider_id', $providerId);
        $query->where('booking_status', '!=', 'draft');
        $query->where('booking_status', '!=', 'declined');
        $query->whereBetween('booking_date', $minRange, $maxRange);
        return $query->get();
    }

    public function changeStatus($bookingId, $data)
    {
        return wpFluent()->table($this->table)
            ->where('id', $bookingId)
            ->update($data);
    }

    public function getBookingsOfSingleDay($serviceId, $providerId, $formId, $date, $time = '', $bookingId = false)
    {
        global $wpdb;

        $query = wpFluent()->table($this->table);
        $query->select([
            wpFluent()->raw('count(' . $wpdb->prefix . $this->table . '.id) as total'),
        ]);
        $query->where('form_id', $formId);
        $query->where('service_id', $serviceId);
        $query->where('provider_id', $providerId);
        $query->where('booking_date', $date);
        if ($time != '') {
            $time = $time . ':00';
            $query->where('booking_time', '=', $time);
        }
        if ($bookingId) {
            $query->where('id', '!=', $bookingId);
        }
        $query->where(function ($q) {
            $q->where('booking_status', '=', 'booked');
            $q->orWhere('booking_status', '=', 'pending');
            $q->orWhere('booking_status', '=', 'declined');
            $q->orWhere('booking_status', '=', 'canceled');
        });
        return $query->first();
    }

    public function getBooking($atts){
        $data = $this->getBookings(false ,$atts);
        if($data){
            $data = array_shift($data);
            return (array)$data;
        }
        return  false;
    }

    public function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . $this->table;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
				id BIGINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				form_id INT(11) NULL,
				name varchar (255) NULL,
				email varchar (255) NULL,
				entry_id INT(11) NULL,
				user_id INT(11) NULL,
				service_id INT(11) NULL,
				provider_id INT(11) NULL,
				booking_date date NULL,
				booking_time time NULL,
				booking_type varchar(255) NULL,
				booking_status varchar(255) NULL,
				notes text NULL,
				booking_hash varchar(255) NULL, 
				send_notification varchar (10) DEFAULT 'yes',
				created_at timestamp NULL,
				updated_at timestamp NULL,
				PRIMARY  KEY  (id)
			  ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);
        }
    }

    public function update($id, $data)
    {
        $data['updated_at'] = current_time('mysql');
//        vd($id);
//        vdd($data);
        return wpFluent()->table($this->table)
            ->where('id', $id)
            ->update($data);

    }


}
