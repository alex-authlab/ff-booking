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
    private $app;


    public function init($app)
    {
        $this->app = $app;

        //global settings page , need to select one only
        add_filter('fluentform_global_settings_components', [$this, 'pushGlobalSettings'], 1, 1);
        add_action('fluentform_global_settings_component_booking_settings_global', [$this, 'renderSettings']);
        // add on page
        add_action('fluentform_addons_page_render_fluentform_booking', [$this, 'renderSettings']);
        add_filter('fluentform_addons_extra_menu', function ($menus) {
            $menus['fluentform_booking'] = __('Fluent Forms Booking', 'fluentform');
            return $menus;
        }, 99, 1);
        
        if (!$this->isEnabled()) {
            return;
        }
        //components
        new BookingDateTime();
        new Service();
        new Provider();
        add_action('fluentform_before_insert_submission', array($this, 'maybeProccessBooking'), 10, 3);
        add_filter('fluentform_form_class', [$this, 'checkBookingForm'],10,2);

    }

    public function maybeProccessBooking($insertData, $data, $form)
    {
        if(!FormFieldsParser::hasElement($form, 'booking_datetime')){
            return;
        }
        new BookingActions($form, $insertData, $data);
    }


    public function checkBookingForm($classes, $targetForm)
    {
        if(FormFieldsParser::hasElement($targetForm, 'booking_datetime')){
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
//        wp_enqueue_style(
//            'fluentform_settings_global',
//            $this->app->publicUrl("css/settings_global.css"),
//            [],
//            FLUENTFORM_VERSION,
//            'all'
//        );
        wp_enqueue_style(
            'ff_booking_settings_css',
            FF_BOOKING_DIR_URL . 'public/js/booking-settings.css',
            [],
            FLUENTFORM_VERSION,
            ''
        );

        wp_enqueue_script(
            'ff-booking-settings',
            FF_BOOKING_DIR_URL . 'public/js/booking-settings.js',
            ['jquery'],
            FLUENTFORMPRO_VERSION,
            true
        );

        $nav = 'service';
        $data = [
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



}
