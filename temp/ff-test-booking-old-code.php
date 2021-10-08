<?php
/*
Plugin Name: Fluent Form Test Booking
Description: Booking
Version: 1.0
Author: WPManageNinja Support Team
Author URI: https://wpmanageninja.com
Plugin URI: https://wpmanageninja.com
License: GPLv2 or later
Text Domain: awesome_support_ext
*/



defined('ABSPATH') or die;
define('FF_BOOKING_TEST_TITLE', 'Booking FF');
define('FF_BOOKING_TEST_VERSION', '1.0.0');
define('FF_BOOKING_TEST_BOOKING_TEST_PATH', plugin_dir_path(__FILE__));
define('FF_BOOKING_TEST_URL', plugin_dir_url(__FILE__));

class BookingFFTest
{




    public function boot()
    {


//        add_action('fluentform_before_insert_submission',array($this,'dataInsert'),10,3);

        add_action('wp_enqueue_scripts', array($this, 'registerScripts'), 999);



        $this->includeFiles();
        /** Init the plugin */
        add_action('fluentform_form_element_start',array($this,'render') ,10,1);

    }


    protected function includeFiles()
    {
        require_once FF_BOOKING_TEST_BOOKING_TEST_PATH. 'Classes/AjaxHandler.php';
        require_once FF_BOOKING_TEST_BOOKING_TEST_PATH. 'Classes/Menu.php';
        require_once FF_BOOKING_TEST_BOOKING_TEST_PATH. 'Classes/AdminApp.php';
        //  Ajax Handlers
        $ajaxHandler = new AjaxHandler();
        $ajaxHandler->register();

        (new Menu())->register();

        add_action('ff_booking/render_admin_app', function () {
            (new AdminApp())->register();
        });


    }


 	public function render($form){
        $bookingForms = [6,1];
        if(!in_array($form->id, $bookingForms)){
            return;
        }
        $this->loadScripts($form->id);
 		echo "<h1>  Calendar </h1>";

        $slots = $this->getSlot();
        $slot_html = '';
        foreach ($slots as $s){
            $slot_html .= '<label class="ff-radio-inline">
						    <input type="radio" class="ff-btn  ff-btn-sm ff-booking-time" name="time_slot" value="'.$s.'">  '.$s.'
						</label>';
        }
 		echo '  <div class="ff-el-input--content ff-booking">
  
 					<input type="text" id="booking-date-picker" class="ff-el-form-control datepicker book-flatpicker"/>
 					<input type="text" class="ff-el-form-control datepicker " name="test"/>
 					
 					
 				</div>
 				
 				<div class="ff-timeslot-div ff-el-input--content" style="display: none">
 							
 				</div>
 				';
 		return;


 	}

    public function loadScripts($formId)
    {
        wp_enqueue_script('flatpickr');
        wp_enqueue_style('flatpickr');
        wp_enqueue_style('ff-booking-css');
        wp_enqueue_script('ff-booking-js-file');

        $dateFormat = '';
        if (!$dateFormat) {
            $dateFormat = 'd/m/Y';
        }

        $customConfigObject = '{}';

        $hasTime = '';
        $time24 = false;

        if($hasTime && strpos($dateFormat, 'H') !== false) {
            $time24 = true;
        }
        $settings = '';
        $config =  array(
            'dateFormat' => $dateFormat,
            'enableTime' => $hasTime,
            'noCalendar' => '',
            'disableMobile' => true,
            'time_24hr' => $time24,
            'inline'=> true,
            'minDate'=> "today",

        );

//        $config = json_encode($config, JSON_FORCE_OBJECT);
         wp_localize_script('ff-booking-js-file', 'ff_booking_vars', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'formId' => $formId,
             'dateConfig' => $config
         ]);



    }
    public function getSlot()
    {
        $start_time = strtotime('09:00');
        $end_time = strtotime('17:00');
        $time_gap = '1hour';
        $time = [];
 		    for ($i = $start_time ; $i <= $end_time ; $i = strtotime('+'.$time_gap, $i)){
            $time_slots[] = date("h:i a", $i);
        }
        return $time_slots;

 	}
 	public function registerScripts()
 	{

        wp_register_style('ff-booking-css', FF_BOOKING_TEST_URL.'assets/style.css');
        wp_register_script('ff-booking-js-file', FF_BOOKING_TEST_URL.'assets/booking.js','', '', true);


 	}

    public  function  dataInsert($insertData, $data, $form){



    }

}



 add_action('plugins_loaded',  function(){

     (new BookingFFTest())->boot();
 },10,1);


