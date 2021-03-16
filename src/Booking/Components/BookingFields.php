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

class BookingFields extends BaseFieldManager
{
    
    public function __construct(
        $key = 'input_booking_ff',
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
            'index' => 0,
            'element' => 'input_booking_ff',
            'attributes' => [
                'name' => 'booking',
                'data-type' => 'booking-element'
            ],
            'settings' => [
                'container_class' => '',
                'admin_field_label' => 'Booking',
                'conditional_logics' => [],
                'label_placement' => 'top'
            ],
            'fields' => [
                'service' => [
                    'element' => 'booking_service',
                    'attributes' => [
                        'type' => 'select', 'name' => 'service_id', 'value' => '', 'id' => '', 'class' => '',
                        'placeholder' => __('- Select Service -', 'fluentform'),
                    ],
                    'settings' => [
            
                        'container_class' => '',
                        'label' => __('Service', 'fluentform'),
                        'help_message' => '',
                        'visible' => true,
                        'validation_rules' => [
                            'required' => [
                                'value' => false,
                                'message' => __('This field is required', 'fluentform'),
                            ],
                        ],
                        'advanced_options' => '',
                        'calc_value_status' => false,
                        'enable_image_input' => false,
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText'
                    ],
                ],
                'booking_date' => [
                    'element' => 'booking_date',
                    'attributes' => [
                        'type' => 'text',
                        'name' => 'date',
                        'value' => '',
                        'id' => '',
                        'class' => '',
                        'placeholder' => __('Booking Date', 'fluentform'),
                    ],
                    'settings' => [
                        'container_class' => '',
                        'label' => __('Date', 'fluentform'),
                        'help_message' => '',
                        'visible' => true,
                        'validation_rules' => [
                            'required' => [
                                'value' => false,
                                'message' => __('This field is required', 'fluentform'),
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText'
                    ],
                ],
                'booking_time' => [
                    'element' => 'booking_time',
                    'attributes' => [
                        'type' => 'hidden',
                        'name' => 'time',
                        'value' => '',
                        'id' => '',
                        'class' => '',
                        'required' => false,
                    ],
                    'settings' => [
                        'container_class' => '',
                        'help_message' => '',
                        'error_message' => '',
                        'visible' => true,
                        'validation_rules' => [
                            'required' => [
                                'value' => false,
                                'message' => __('This field is required', 'fluentform'),
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText'
                    ],
                ],
                
            ],
            'editor_options' => [
                'title' => 'Booking Fields',
                'element' => 'booking-fields',
                'icon_class' => 'ff-edit-date',
                'template' => 'nameFields',
                
            ],
        ];
    }
    
    public function getGeneralEditorElements()
    {
        return [
            'label',
            'admin_field_label',
            'name_fields',
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
    
    
    public function render($data, $form)
    {
        
      
        $elementName = $data['element'];
    
        $data = apply_filters('fluenform_rendering_field_data_'.$elementName, $data, $form);
    
        $rootName = $data['attributes']['name'];
    
        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';
    
        if (empty($data['attributes']['class'])) {
            $data['attributes']['class'] = '';
        }
    
        $data['attributes']['class'] .= $hasConditions;
        $data['attributes']['class'] .= ' ff-field_container ff-name-field-wrapper';
        if($containerClass = ArrayHelper::get($data, 'settings.container_class')) {
            $data['attributes']['class'] .= ' '.$containerClass;
        }
        $atts = $this->buildAttributes(
            ArrayHelper::except($data['attributes'], 'name')
        );
    
        $html = "<div {$atts}>";
        $html .= "<div class='ff-t-container ff-booking-container'>";
    
        $labelPlacement = ArrayHelper::get($data, 'settings.label_placement');
        $labelPlacementClass = '';
    
        if ($labelPlacement) {
            $labelPlacementClass = ' ff-el-form-'.$labelPlacement;
        }
    
        wp_enqueue_script('flatpickr');
        wp_enqueue_style('flatpickr');
        wp_enqueue_style ('ff_booking_test',FF_BOOKING_DIR_URL.'public/css/form_booking.css','',FF_BOOKING_VER);
        $inputIds = [];
        $dateField = '';
        foreach ($data['fields'] as $field) {
          
                $fieldName = $field['attributes']['name'];
                $field['attributes']['name'] = $rootName . '[' . $fieldName . ']';
                @$field['attributes']['class'] = trim(
                    'ff-el-form-control ' .
                    $field['attributes']['class']
                );
            
                if ($tabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex()) {
                    $field['attributes']['tabindex'] = $tabIndex;
                }
            
            
                @$field['settings']['container_class'] .= $labelPlacementClass;
    
                $inputIds [$field['element']] = $id = $field['attributes']['id'] = $this->makeElementId($field, $form);
                if($field['element'] == 'booking_date'){
                    
                    $dateField = $field ;
                    @$field['attributes']['class'] .= ' ff-el-datepicker';
                    $elMarkup = "<input ".$this->buildAttributes($field['attributes']).">";
                    $elMarkup.= "<div id='slot-holder-".$id."' class='ff-timeslot-div'></div>";
    
    
                }
                
                else if($field['attributes']['type'] == 'select'){
                    
        
                    $defaultValues = (array)$this->extractValueFromAttributes($field);
        
                    $elMarkup = "<select " . $this->buildAttributes($field['attributes']) . ">" . $this->buildOptions($field, $defaultValues) . "</select>";
                }else{
                    $elMarkup = "<input ".$this->buildAttributes($field['attributes']).">";
        
                }
            
                $inputTextMarkup = $this->buildElementMarkup($elMarkup, $field, $form);
                $html .= "<div class=''>{$inputTextMarkup}</div>";
        }
    
        $html .= "</div>";
        $html .= "</div>";
        $this->setDateFormatConfig($dateField, $form, $dateField['attributes']['id'],$inputIds);
        echo apply_filters('fluenform_rendering_field_html_'.$elementName, $html, $data, $form);


    }
    
    
    
    
    private function setDateFormatConfig($settings, $form, $id,$inputIds)
    {
      
        
  
        add_action('wp_footer', function () use ( $id, $form,$inputIds) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
    
                    var $inputDateID = '<?php echo $inputIds['booking_date']; ?>';
                    var $inputTime = jQuery('#<?php echo $inputIds['booking_time']; ?>');
                    var $inputService = jQuery('#<?php echo $inputIds['booking_service']; ?>');
                    var $slotTimeElements = jQuery('#slot-holder-' + $inputDateID);
                    var $slot = '.ff-booking-time-slot';
                    var fulBookedDate = [];
    
    
                    const bookingTestFF   = {
        
        
                        init() {
                            this.setupPicker();
                            this.setTimeDate();
                            this.getFullBooked();
                            this.setServiceEvent();
                        },
        
                        setupPicker() {
            
                            if(typeof flatpickr == 'undefined') {
                                return;
                            }
                            flatpickr.localize(window.fluentFormVars.date_i18n);
                            let that = this;
    
                            var config = {
                                "inline": true,
                                "dateFormat":"d-m-Y",
                                "enableTime":false,
                                "noCalendar":false,
                                "disableMobile":true,
                                "time_24hr":true,
                                onChange: function(selectedDates, dateStr, instance) {
                                    that.setupSlotData();
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
    
                            $inputService.on('change', ()=>{
                                
                             
                                    jQuery.post(fluentFormVars.ajaxUrl, {
                                            action: 'handle_booking_ajax_endpoint',
                                            route: 'get_full_booked',
                                            service_id: $inputService.val(),
                                            selected_date: jQuery('#'+$inputDateID ).val(),
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
                            if(!$inputService.val() || !jQuery('#'+$inputDateID ).val() ){
                                $slotTimeElements.html('Please select a service and date first');
                                return;
                            }
                            $slotTimeElements.html('<div class="booking-loader"></div>');
                            
                            jQuery.post(fluentFormVars.ajaxUrl, {
                                    action: 'handle_booking_ajax_endpoint',
                                    route: 'get_slots',
                                    service_id: $inputService.val(),
                                    date: jQuery('#'+$inputDateID ).val(),
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
                            
                            //all inputs inside current slot holder
                            jQuery(document).on('click','#slot-holder-' + $inputDateID+ ' .ff-booking-time' , function(e) {
                                // set time
                                let time = jQuery(this).val();
                                $inputTime.val( time );
                            });
                        },
                        setServiceEvent(){
                            
                            jQuery($inputService).on('change',(e)=>{
                                    e.preventDefault();
                                    this.setupSlotData();
                                    this.getFullBooked();
                            })
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
    protected function buildOptions($data, $defaultValues)
    {
        $serviceData = BookingHelper::getService ();
        if ( $serviceData ) {
            $options = $serviceData;
            $formattedOptions = [];
            foreach ($options as $value) {
                $formattedOptions[] = [
                    'label'      => $value->name,
                    'value'      => $value->id,
                    'calc_value' => ''
                ];
            }
        }
        
      
        
        $opts = '';
        if (!empty($data['settings']['placeholder'])) {
            $opts .= '<option value="">' . $data['settings']['placeholder'] . '</option>';
        } else if (!empty($data['attributes']['placeholder'])) {
            $opts .= '<option value="">' . $data['attributes']['placeholder'] . '</option>';
        }
        
        foreach ($formattedOptions as $option) {
            if (in_array($option['value'], $defaultValues)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            
            $atts = [
                'data-calc_value'        => ArrayHelper::get($option, 'calc_value'),
                'data-custom-properties' => ArrayHelper::get($option, 'calc_value'),
                'value'                  => ArrayHelper::get($option, 'value'),
            ];
            
            $opts .= "<option " . $this->buildAttributes($atts) . " {$selected}>{$option['label']}</option>";
        }
        
        return $opts;
    }
    
    private function  getServiceList(){
        $serviceData = BookingHelper::getService ();
        $formattedOptions = [];
    
    
        foreach ($serviceData as $d) {
            $formattedOptions[] = [
                'label' => $d->name,
                'value' => $d->id,
                'calc_value' => ''
            ];
        }
        return [$formattedOptions];
    }
    
    
}
