<?php

namespace FF_Booking\Booking;

if (!defined('ABSPATH')) {
    exit;
}

use FF_Booking\Booking\Components\BookingFields;
use FF_Booking\Booking\Components\BookingDateTime;
use FF_Booking\Booking\Components\Provider;
use FF_Booking\Booking\Components\Service;
use \FluentForm\App\Helpers\Helper;
use \FluentForm\Framework\Helpers\ArrayHelper;
use \FluentForm\App\Modules\Form\FormFieldsParser;


class BookingHandler
{

    public function init($app)
    {
        $this->addMenus();
        //global settings page , need to select one only
//        add_filter('fluentform_global_settings_components', [$this, 'pushGlobalSettings'], 1, 1);
//        add_action('fluentform_global_settings_component_booking_settings_global', [$this, 'renderSettings']);
        // add on page
        add_action('admin_enqueue_scripts', array($this,'enqueScripts'));
        add_action('fluentform_addons_page_render_fluentform_booking', array($this, 'renderSettings'));
        add_filter('fluentform_addons_extra_menu', function ($menus) {
            $menus['fluentform_booking'] = __('Fluent Forms Booking', 'fluentform');
            return $menus;
        }, 99, 1);

        if (!$this->isEnabled()) {
            return;
        }
        $this->addShortCodes();

        //components
        new BookingDateTime();
        new Service();
        new Provider();
        (new BookingNotification())->init();

        add_action('fluentform_before_insert_submission', array($this, 'maybeProccessBooking'), 10, 3);
        add_filter('fluentform_form_class', [$this, 'checkBookingForm'], 10, 2);
    }

    public function maybeProccessBooking($insertData, $data, $form)
    {
        if (!FormFieldsParser::hasElement($form, 'booking_datetime')) {
            return;
        }
        new BookingActions($form, $insertData, $data);

    }


    public function checkBookingForm($classes, $targetForm)
    {
        if (FormFieldsParser::hasElement($targetForm, 'booking_datetime')) {
            $classes .= ' ff_has_booking';
        }
        return $classes;
    }

    public function pushGlobalSettings($components)
    {
        $components['booking_settings'] = [
            'hash' => '',
            'title' => 'Booking Settings',
            'query' => [
                'component' => 'booking_settings_global'
            ]
        ];
        return $components;
    }

    public function renderSettings()
    {
        $data = [
            'time_zones' => \DateTimeZone::listIdentifiers(),
            'is_setup' => $this->isEnabled(),
            'ff_booking_admin_nonce' => wp_create_nonce('ff_booking_admin_nonce'),
            'active_nav' => 'Bookings',
            'ajaxUrl' => admin_url('admin-ajax.php'),
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
        $menu = new Menu();
        $menu->registerScipts();
        $menu->addPages($this->getPages())->withSubPages('Bookings')->addSubPages($this->getSubPages())->register();
    }

    public function enqueScripts()
    {
        (new Menu())->enqueScipts();
    }

    private function getPages()
    {
        return [
            [
                'page_title' => 'FF Booking',
                'menu_title' => 'FF Booking',
                'capability' => $this->getBookingCapability(),
                'menu_slug' => 'ff_simple_booking',
                'callback' => [$this, 'renderSettings'],
                'icon_url' => 'dashicons-marker',
                'position' => 25
            ]
        ];
    }

    private function getSubPages()
    {
        return [
            [
                'parent_slug' => 'ff_simple_booking',
                'page_title' => 'Service',
                'menu_title' => 'Service',
                'capability' => $this->getBookingCapability(),
                'menu_slug' => 'admin.php?page=ff_simple_booking#/service',
                'callback' => ''
            ],
            [
                'parent_slug' => 'ff_simple_booking',
                'page_title' => 'Provider',
                'menu_title' => 'Provider',
                'capability' => $this->getBookingCapability(),
                'menu_slug' => 'admin.php?page=ff_simple_booking#/provider',
                'callback' => ''
            ],
            [
                'parent_slug' => 'ff_simple_booking',
                'page_title' => 'Settings',
                'menu_title' => 'Settings',
                'capability' => $this->getBookingCapability(),
                'menu_slug' => 'admin.php?page=ff_simple_booking#/settings',
                'callback' => ''
            ],
        ];
    }


    private function addShortCodes()
    {
        //{ff_booking_info}
        add_filter('fluentform_shortcode_parser_callback_ff_booking_info', function ($value, $parser) {
            $entry = $parser::getEntry();
            return (new BookingInfo($entry->id))->bookingInfoHtml();
        }, 10, 2);
        //{ff_booking_info_page_link}
//        add_filter('fluentform_shortcode_parser_callback_ff_booking_info_page_link', function ($value, $parser) {
//            $entry = $parser::getEntry();
//            $data = (new BookingInfo($entry->id))->getBookingInfoData();
//            $hash = ArrayHelper::get($data,'bookingData.booking_hash');
//            $html = '';
//
//        }, 10, 2);
    }



}
