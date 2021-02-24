<?php

namespace FF_Booking\Booking\Components;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FF_Booking\Booking\BookingHelper;
use \FluentForm\App\Modules\Component\Component;
use \FluentForm\App\Services\FormBuilder\BaseFieldManager;
use \FluentForm\App\Services\FormBuilder\Components\DateTime;
use \FluentForm\Framework\Helpers\ArrayHelper;

class BookingDate extends BaseFieldManager
{
    
    public function __construct(
        $key = 'booking_date',
        $title = 'Booking DateTime',
        $tags = ['service', 'booking'],
        $position = 'advanced'
    )
    {
        parent::__construct(
            $key,
            $title,
            $tags,
            $position
        );

    }
    
    public function getComponent()
    {
        return [
            'index'          => 29,
            'element'        => $this->key,
            'attributes'     => [
                'name'        => $this->key,
                'value' => '',
                'id' => '',
                'class' => '',
            ],
            'settings' => array(
                'dynamic_default_value' => '',
                'label' => __('Booking DateTime', 'fluentform'),
                'admin_field_label' => '',
                'help_message' => '',
                'container_class' => '',
                'label_placement' => '',
                'validation_rules' => array(
                    'required' => array(
                        'value' => true,
                        'message' => __('This field is required', 'fluentform'),
                    ),
                ),
                'conditional_logics' => array(),
            ),
            'editor_options' => array(
                'title' => __('Booking DateTime', 'fluentform'),
                    'icon_class' => 'ff-edit-date',
                'element' => 'text',
                'template' => 'inputText'
            )
        ];
    }
    
    public function getGeneralEditorElements()
    {
        return [
            'label',
            'admin_field_label',
            'placeholder',
            'label_placement',
        ];
    }
    
    public function getAdvancedEditorElements()
    {
        return [
            'name',
            'help_message',
            'container_class',
            'class',
            'conditional_logics',
        ];
    }
    
    public function generalEditorElement()
    {
        
        
        return [
        
        ];
    }
    
    // todo remove static id and name
    
    public function render($data, $form)
    {
        $data = [
            'element' => 'input_date',
            'attributes' =>  [
                'type' => 'text',
                'name' => 'ff_date_slot',
                'value' =>'',
                'id' =>'ff-booking-datetime',
                'class' => 'ff-booking-picker-element',
                'data-name' => 'ff_date'
            ],
            'settings' =>[
                'container_class' => 'ff-booking-container',
                'label'=> 'Select Booking',
                'date_config' => '',
                'is_time_enabled' => 1
            ]
        
        ];
        $elementName = $data['element'];
        $data = apply_filters('fluenform_rendering_field_data_' . $elementName, $data, $form);
    
        wp_enqueue_script('flatpickr');
        wp_enqueue_style('flatpickr');
        wp_enqueue_style ('ff_booking_test',FF_BOOKING_DIR_URL.'public/css/form_booking.css','',FF_BOOKING_VER);
    
        $data['settings']['container_class'] .= ' ff-booking-container';
        $data['attributes']['class'] = trim(
            'ff-el-form-control ff-el-datepicker ' . $data['attributes']['class']
        );
        
    
    
        if($tabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }
    
   
        $elMarkup = "<input data-type-datepicker value='' " . $this->buildAttributes($data['attributes']) . ">";
        $elMarkup.= "<div class='ff-timeslot-div'></div>";
        $this->setDateFormatConfig($data['settings'], $form, $data['attributes']['id']);
    
        $html = $this->buildElementMarkup($elMarkup, $data, $form);
        echo apply_filters('fluenform_rendering_field_html_' . $elementName, $html, $data, $form);
    }
    
    
    
    
    private function setDateFormatConfig($settings, $form, $id)
    {
      
        
  
        add_action('wp_footer', function () use ( $id, $form) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
    
                    let $slotTimeElements      = jQuery('.ff-timeslot-div');
                    let $slot             = '.ff-booking-time-slot';
                    var fulBookedDate = [];
                    
              
                    const bookingTestFF   = {
        
        
                        init() {
                            this.setupPicker();
                            this.setTimeDate();
                            this.getFullBooked();
                        },
        
                        setupPicker() {
            
                            if(typeof flatpickr == 'undefined') {
                                return;
                            }
                            flatpickr.localize(window.fluentFormVars.date_i18n);
                            let temp = this;
    
                            var config = {
                                "inline": true,
                                "dateFormat":"d-m-Y",
                                "enableTime":false,
                                "noCalendar":false,
                                "disableMobile":true,
                                "time_24hr":true,
                                onChange: function(selectedDates, dateStr, instance) {
                                    temp.setupSlotData();
                                },
                                
                                "disable": fulBookedDate ,
                            };
                           
            
                            if (!config.locale) {
                                config.locale = 'default';
                            }
            
                            if(jQuery('#<?php echo $id; ?>').length) {
                                flatpickr('#<?php echo $id; ?>', config);
                            }
                            
                        },
                        getFullBooked(){
                            
                            jQuery('#ff-booking-service').on('change', ()=>{
                                
                             
                                    jQuery.post(fluentFormVars.ajaxUrl, {
                                            action: 'handle_booking_ajax_endpoint',
                                            route: 'get_full_booked',
                                            service_id: jQuery('#ff-booking-service').val(),
                                            form_id: <?php echo $form->id ?>
                                        })
                                        .then(response => {
                                            if(response.success == true){
                                               
                                               fulBookedDate = response.data.dates;
                                               //re render picker
                                               this.setupPicker();
                                               
                                            }
                                        })
                                }
                            );
                           
                            
                            
                        },
        
                        setupSlotData() {
                            
                            $slotTimeElements.html('<div class="booking-loader"></div>');
                            
                            jQuery.post(fluentFormVars.ajaxUrl, {
                                    action: 'handle_booking_ajax_endpoint',
                                    route: 'get_slots',
                                    service_id: jQuery('#ff-booking-service').val(),
                                    date: jQuery('#ff-booking-datetime').val(),
                                    form_id: <?php echo $form->id ?>
                                })
                                .then(response => {
                                   if(response.success == true){
                                       $slotTimeElements.html(response.data.html);
                                   }
                                })
                                .always(() =>{
                                    jQuery('.booking-loader').remove()
                                }
                            );
                            
                            
                        },
        
                        setTimeDate() {
                            $(document).on('change', $slot , function(e) {
                                // get time date
                                let time = $(this).val();
                                let date = $pickerElement.val();
                                console.log($(this).val());
                            });
                        }
                    };
                    
                    bookingTestFF.init();
                    $(document).on('reInitExtras', '.<?php echo $form->instance_css_class; ?>', function () {
                        bookingTestFF.init();
                    });
                });
            </script>
            <?php
        }, 99999);
    }
   
}
