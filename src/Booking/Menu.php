<?php

namespace FF_Booking\Booking;

class Menu
{

    public $admin_pages = [];
    public $admin_subpages = [];

    public function register()
    {
        if (!empty($this->admin_pages) || !empty($this->admin_subpages)) {
            add_action('admin_menu', array($this, 'addAdminMenu'));
        }
    }

    /**
     * @param array $pages
     * @return $this
     */
    public function addPages(array $pages)
    {
        $this->admin_pages = $pages;
        return $this;
    }

    /**
     * @param array $subPages
     * @return $this
     */
    public function addSubPages(array $subPages)
    {
        $this->admin_subpages = array_merge($this->admin_subpages, $subPages);
        return $this;
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function withSubPages(string $title = null)
    {
        if (empty($this->admin_pages)) {
            return $this;
        }

        $admin_page = $this->admin_pages[0];

        $subpage = array(

            array(
                'parent_slug' => $admin_page['menu_slug'],
                'page_title' => $admin_page['page_title'],
                'menu_title' => ($title) ? $title : $admin_page['menu_title'],
                'capability' => $admin_page['capability'],
                'menu_slug' => $admin_page['menu_slug'],
                'callback' => $admin_page['callback']

            )
        );

        $this->admin_subpages = $subpage;

        return $this;
    }

    public function addAdminMenu()
    {
        foreach ($this->admin_pages as $page) {
            add_menu_page(
                $page['page_title'],
                $page['menu_title'],
                $page['capability'],
                $page['menu_slug'],
                $page['callback'],
                $page['icon_url'],
                $page['position']
            );
        }

        foreach ($this->admin_subpages as $page) {
            add_submenu_page(
                $page['parent_slug'],
                $page['page_title'],
                $page['menu_title'],
                $page['capability'],
                $page['menu_slug'],
                $page['callback']
            );
        }
    }

    public function registerScipts()
    {
        wp_register_style(
            'ff_booking_settings_css',
            FF_BOOKING_DIR_URL . 'public/js/booking-settings.css',
            [],
            FF_BOOKING_VER,
            ''
        );

        wp_register_script(
            'ff-booking-settings',
            FF_BOOKING_DIR_URL . 'public/js/booking-settings.js',
            ['jquery'],
            FF_BOOKING_VER,
            true
        );

        wp_register_script(
            'ff-booking-public-view',
            FF_BOOKING_DIR_URL . 'public/js/ff-booking-public-view.js',
            ['jquery'],
            FF_BOOKING_VER,
            true
        );
    }

    public function enqueScipts()
    {
        $pages = ['ff_simple_booking'];
        if (!isset($_GET['page']) || !in_array($_GET['page'], $pages)) {
            return;
        }
        wp_enqueue_style('ff_booking_settings_css');
        wp_enqueue_script('ff-booking-settings');
        if (function_exists('wp_enqueue_editor')) {
            add_filter('user_can_richedit', function ($status) {
                return true;
            });

            wp_enqueue_editor();
            wp_enqueue_media();
        }
    }

}
