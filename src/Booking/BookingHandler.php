<?php

namespace FF_Booking\Booking;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FF_Booking\Booking\AjaxHandler;
use FF_Booking\Booking\Components\BookingFields;
use FF_Booking\Booking\Components\Service;
use  \FluentForm\App\Helpers\Helper;

//use FluentFormPro\App\Modules\Acl\Acl;
//use FluentFormPro\App\Modules\Form\FormFieldsParser;
//use FluentFormPro\App\Services\FormBuilder\ShortCodeParser;
use \FluentForm\Framework\Helpers\ArrayHelper;


class BookingHandler
{
    private $app;

    private $hasBooking;

    public function init($app)
    {
        $this->app = $app;

        add_filter('fluentform_addons_extra_menu', function ($menus) {
            $menus['fluentform_booking'] = __('Fluent Forms Booking', 'fluentform');
            return $menus;
        }, 99, 1);

        add_action('fluentform_global_settings_component_booking_settings_global', [$this, 'renderSettings']);
        add_action('fluentform_addons_page_render_fluentform_booking', [$this, 'renderSettings']);


        if (!$this->isEnabled()) {
            return;
        }
        //components
        new BookingFields();
        add_filter('fluentform_global_settings_components', [$this, 'pushGlobalSettings'], 1, 1);
        add_action('fluentform_global_settings_component_booking_settings_global', [$this, 'renderGlobalSettings']);

        add_action('fluentform_before_form_actions_processing', array($this, 'maybeHandleBooking'), 10, 3);
        //add_action('fluentform_before_form_render', [$this, 'checkBookingForm']);

    }

    public function maybeHandleBooking($insertId, $formData, $form)
    {
        //check for booking date picker element
        if (!Helper::hasFormElement($form->id, 'booking_date')) {
            return;
        }

        $action = new BookingActions($insertId, $formData, $form);
        $action->bookingEntry();
    }

    public function checkBookingForm($form)
    {
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

    public function renderGlobalSettings()
    {
        echo '<div><h2>Booking Global Settings Here</h2></div>';
    }

    public function renderSettings()
    {
        wp_enqueue_style(
            'fluentform_settings_global',
            $this->app->publicUrl("css/settings_global.css"),
            [],
            FLUENTFORM_VERSION,
            'all'
        );

        wp_enqueue_script(
            'ff-booking-settings',
            FF_BOOKING_DIR_URL . 'public/js/booking-settings.js',
            ['jquery'],
            FLUENTFORMPRO_VERSION,
            true
        );

        //for later
        $settings = '';
        $nav = 'service';

        $data = [
            'is_setup' => $this->isEnabled(),
            'general' => $settings,
            'active_nav' => $nav,
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
