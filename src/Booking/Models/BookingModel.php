<?php

namespace FF_Booking\Booking\Models;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class BookingModel
{
    private $table = 'alex_booking_entries';

    public function getBookings($paginate = false)
    {
        $from = date('Y-m-d', strtotime('-30 days'));
        $to = date('Y-m-d', strtotime('+1 days'));

        if (!empty($ranges[0])) {
            $from = $ranges[0];
        }
        if (!empty($ranges[1])) {
            $time = strtotime($ranges[1]) + 24 * 60 * 60;
            $to = date('Y-m-d H:i:s', $time);
        }
        $defaultAtts = [
            'search' => '',
            'status' => 'all',
            'sort_column' => 'id',
            'sort_by' => 'DESC',
            'per_page' => 10,
            'page' => 1
        ];

        $atts = wp_parse_args($_REQUEST, $defaultAtts);

        $search = ArrayHelper::get($atts, 'search', '');
        $status = ArrayHelper::get($atts, 'status', 'all');

        $shortColumn = ArrayHelper::get($atts, 'sort_column', 'id');
        $sortBy = ArrayHelper::get($atts, 'sort_by', 'DESC');

        $query = wpFluent()->table($this->table)
            ->orderBy($this->table.'.'.$shortColumn, $sortBy);

        $query->join('fluentform_forms', 'fluentform_forms.id', '=', $this->table.'.form_id');

        if ($status && $status != 'all') {
            $query->where('status', $status);
        }
        $query->whereBetween('date', $from, $to);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', '%' . $search . '%');
                $q->orWhere('title', 'LIKE', '%' . $search . '%');
            });
        }

        if ($paginate) {
            $providers = $query->paginate();

            foreach ($providers as $index=>$datum){
                $providers[$index]->submission_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $datum->form_id . '#/entries/' . $datum->entry_id);
            }
            return $providers;
        }
        $providers = $query->get();
        foreach ($providers as $index=>$datum){
            $providers[$index]->submission_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $datum->form_id . '#/entries/' . $datum->entry_id);
        }
        return $providers;
    }

    public function bookings($atts = [], $withFields = false)
    {
        $from = date('Y-m-d', strtotime('-30 days'));
        $to = date('Y-m-d', strtotime('+1 days'));

        $ranges = ArrayHelper::get($atts,'date_range', []);

        if (!empty($ranges[0])) {
            $from = $ranges[0];
        }

        if (!empty($ranges[1])) {
            $time = strtotime($ranges[1]) + 24 * 60 * 60;
            $to = date('Y-m-d H:i:s', $time);
        }

        $defaultAtts = [
            'search' => '',
            'status' => 'all',
            'sort_column' => 'id',
            'sort_by' => 'DESC',
            'per_page' => 10,
            'page' => 1
        ];

        $atts = wp_parse_args($atts, $defaultAtts);

        $perPage = ArrayHelper::get($atts, 'per_page', 10);
        $search = ArrayHelper::get($atts, 'search', '');
        $status = ArrayHelper::get($atts, 'status', 'all');

        $shortColumn = ArrayHelper::get($atts, 'sort_column', 'id');
        $sortBy = ArrayHelper::get($atts, 'sort_by', 'DESC');

        $query = wpFluent()->table($this->table)
            ->orderBy($this->table.'.'.$shortColumn, $sortBy);

        if ($status && $status != 'all') {
            $query->where('status', $status);
        }
        $query->join('fluentform_forms', 'fluentform_forms.id', '=', $this->table.'.form_id');
        $query->whereBetween('date', $from, $to);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', '%' . $search . '%');
                $q->orWhere('title', 'LIKE', '%' . $search . '%');
            });
        }

        $currentPage = intval(ArrayHelper::get($atts, 'page', 1));
        $total = $query->count();
        $skip = $perPage * ($currentPage - 1);
        $data = (array) $query->select('*')->limit($perPage)->offset($skip)->get();

        $dataCount = count($data);

        $from = $dataCount > 0 ? ($currentPage - 1) * $perPage + 1 : null;

        $to = $dataCount > 0 ? $from + $dataCount - 1 : null;
        $lastPage = (int) ceil($total / $perPage);

        foreach ($data as $index=>$datum){
            $data[$index]->submission_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $datum->form_id . '#/entries/' . $datum->entry_id);
        }
        $bookings = array(
            'current_page'  => $currentPage,
            'per_page'      => $perPage,
            'from'          => $from,
            'to'            => $to,
            'last_page'     => $lastPage,
            'total'         => $total,
            'data'          => $data,
        );


        return $bookings;
    }
}
