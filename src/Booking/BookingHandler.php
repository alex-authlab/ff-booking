<?php

namespace FF_Booking\Booking;

if (!defined('ABSPATH')) {
    exit;
}

use FF_Booking\Booking\Components\BookingFields;
use FF_Booking\Booking\Components\BookingDateTime;
use FF_Booking\Booking\Components\Provider;
use FF_Booking\Booking\Components\Service;
use \FluentForm\App\Modules\Form\FormFieldsParser;


class BookingHandler
{

    public function init($app)
    {
        $this->addMenus();
        add_action('admin_enqueue_scripts', array($this,'enqueScripts'));
        if (!$this->isEnabled()) {
            return;
        }

        //components
        new BookingDateTime();
        new Service();
        new Provider();
        (new BookingNotification())->init();
        (new BookingShortCodes())->init();
        add_action('fluentform_before_insert_submission', array($this, 'maybeProccessBooking'), 10, 3);
        add_filter('fluentform_form_class', array($this, 'checkBookingForm'), 10, 2);
    }

    public function maybeProccessBooking($insertData, $data, $form)
    {
        if (!FormFieldsParser::hasElement($form, 'booking_datetime')) {
            return;
        }
        (new BookingActions())->init($form, $insertData, $data);

    }


    public function checkBookingForm($classes, $targetForm)
    {
        if (FormFieldsParser::hasElement($targetForm, 'booking_datetime')) {
            $classes .= ' ff_has_booking';
        }
        return $classes;
    }
    

    public function renderSettings()
    {
        $data = [
            'time_zones'             => \DateTimeZone::listIdentifiers(),
            'is_setup'               => $this->isEnabled(),
            'ff_booking_admin_nonce' => wp_create_nonce('ff_booking_admin_nonce'),
            'active_nav'             => 'Bookings',
            'ajaxUrl'                => admin_url('admin-ajax.php'),
            'booking_status'         => BookingHelper::bookingStatuses(),
        ];
    
        wp_localize_script('ff-booking-settings', 'ff_booking_settings', $data);
    
        ob_start();
        include_once(plugin_dir_path(__FILE__) . 'view/admin-booking-view.php');
        echo ob_get_clean();
    }

    public function isEnabled()
    {
        return get_option('_ff_booking_status') == 'yes';
    }

    /**
     * @return string
     */
    private function getBookingCapability()
    {
        return 'manage_options';
    }

    private function addMenus()
    {
        $menu = new MenuApi();
        $menu->registerScipts();
        $menu->addPages($this->getPages())->withSubPages('Bookings')->addSubPages($this->getSubPages())->register();
    }

    public function enqueScripts()
    {
        (new MenuApi())->enqueScipts();
    }

    private function getPages()
    {
        return [
            [
                'page_title' => 'FF Booking',
                'menu_title' => 'FF Booking',
                'capability' => $this->getBookingCapability(),
                'menu_slug'  => 'ff_simple_booking',
                'callback'   => [$this, 'renderSettings'],
                'icon_url'   => 'dashicons-marker',
                'position'   => 25
            ]
        ];
    }

    private function getSubPages()
    {
        return [
            [
                'parent_slug' => 'ff_simple_booking',
                'page_title'  => 'Services',
                'menu_title'  => 'Services',
                'capability'  => $this->getBookingCapability(),
                'menu_slug'   => 'admin.php?page=ff_simple_booking#/services',
                'callback'    => ''
            ],
            [
                'parent_slug' => 'ff_simple_booking',
                'page_title'  => 'Provider',
                'menu_title'  => 'Provider',
                'capability'  => $this->getBookingCapability(),
                'menu_slug'   => 'admin.php?page=ff_simple_booking#/provider',
                'callback'    => ''
            ],
            [
                'parent_slug' => 'ff_simple_booking',
                'page_title'  => 'Settings',
                'menu_title'  => 'Settings',
                'capability'  => $this->getBookingCapability(),
                'menu_slug'   => 'admin.php?page=ff_simple_booking#/settings',
                'callback'    => ''
            ],
        ];
    }
    



}
