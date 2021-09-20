<?php
/*
Plugin Name: FF Booking
Description: Booking Test
Version: 1.0
Author: WP Fluent Forms
Author URI: #
Plugin URI: #
License: GPLv2 or later
Text Domain: fluentformpro
Domain Path: /resources/languages
*/


use FF_Booking\Booking\AjaxHandler;

defined('ABSPATH') or die;

define('FF_BOOKING_VER', '1.0');
define('FF_BOOKING_DIR_URL', plugin_dir_url(__FILE__));
define('FF_BOOKINGDIR_PATH', plugin_dir_path(__FILE__));
define('FF_BOOKING_DIR_FILE', __FILE__);

include FF_BOOKINGDIR_PATH . 'autoload.php';

if (!class_exists('FFBooking')) {
    class FFBooking
    {

        /**
         * Bootstrap the Plugin
         * @param FluentForm\Framework\Foundation\Application $app
         * @return void
         */
        public function boot()
        {
            if (!defined('FLUENTFORM')) {
                $this->injectDependency();
                return;
            }

            if (!defined('FLUENTFORM_HAS_NIA')) {
                return add_action('admin_notices', function () {
                    $class = 'notice notice-error';
                    $message = 'You are using old version of WP Fluent Forms. Please update to latest from your plugins list';
                    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
                });
            }

            if (function_exists('wpFluentForm')) {
                $this->registerHooks(wpFluentForm());
            }
        }

        protected function registerHooks($fluentForm)
        {
            $this->ajaxHooks($fluentForm);
            $this->adminHooks($fluentForm);
            $this->publicHooks($fluentForm);

            (new \FF_Booking\Booking\BookingHandler)->init($fluentForm);
        }

        /**
         * Notify the user about the FluentForm dependency and instructs to install it.
         */
        protected function injectDependency()
        {
            add_action('admin_notices', function () {
                $pluginInfo = $this->getFluentFormInstallationDetails();

                $class = 'notice notice-error';

                $install_url_text = 'Click Here to Install the Plugin';

                if ($pluginInfo->action == 'activate') {
                    $install_url_text = 'Click Here to Activate the Plugin';
                }

                $message = 'FluentForm PRO Add-On Requires FluentForm Base Plugin, <b><a href="' . $pluginInfo->url
                    . '">' . $install_url_text . '</a></b>';

                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
            });
        }

        /**
         * Get the FluentForm plugin installation information e.g. the URL to install.
         *
         * @return \stdClass $activation
         */
        protected function getFluentFormInstallationDetails()
        {
            $activation = (object)[
                'action' => 'install',
                'url' => ''
            ];

            $allPlugins = get_plugins();

            if (isset($allPlugins['fluentform/fluentform.php'])) {
                $url = wp_nonce_url(
                    self_admin_url('plugins.php?action=activate&plugin=fluentform/fluentform.php'),
                    'activate-plugin_fluentform/fluentform.php'
                );

                $activation->action = 'activate';
            } else {
                $api = (object)[
                    'slug' => 'fluentform'
                ];

                $url = wp_nonce_url(
                    self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug),
                    'install-plugin_' . $api->slug
                );
            }

            $activation->url = $url;

            return $activation;
        }

        /**
         * Register admin/backend hooks
         * @return void
         */
        public function adminHooks($app)
        {
        }

        /**
         * Register public/frontend hooks
         * @return void
         */
        public function publicHooks($app)
        {
        }

        /**
         * Registers ajax hooks.
         */
        public function ajaxHooks($app)
        {
            $ajax = new AjaxHandler();
            add_action('wp_ajax_handle_booking_ajax_endpoint', [$ajax, 'init']);
            add_action('wp_ajax_nopriv_handle_booking_ajax_endpoint', [$ajax, 'init']);
        }

    }

    /**
     * Plugin init hook
     * @return void
     */
    add_action('init', function () {
        (new FFBooking)->boot();
    });

    register_activation_hook(__FILE__, function ($siteWide) {
        \FF_Booking\Booking\Migrations\Migration::migrate();
    });
}
