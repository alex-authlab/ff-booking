<?php

namespace FF_Booking\Booking\Components;

use FF_Booking\Booking\BookingHelper;
use FluentForm\Framework\Helpers\ArrayHelper;

class BookingDateTime extends \FluentForm\App\Services\FormBuilder\BaseFieldManager
{
    public function __construct()
    {
        parent::__construct(
            'booking_datetime',
            'Booking Input',
            ['booking', 'appointment'],
            'advanced'
        );
        add_filter('fluentform_validate_input_item_booking_datetime', array($this, 'validate'), 10, 5);
        add_filter('fluentform_response_render_booking_datetime', function ($value, $field, $formId, $isHtml) {
            $values = maybe_unserialize($value);
            if (!ArrayHelper::get($values, 'value')) {
                return $value;
            }
            $dateTime = explode(' ', $values['value']);
            $bookingId = ArrayHelper::get($values, 'booking_id');
                $date = ArrayHelper::get($dateTime, '0');
            $time = ArrayHelper::get($dateTime, '1');
            $time .= ArrayHelper::get($dateTime, '2');
            $dateFormat = BookingHelper::getBookingFieldDateFormat($formId);
            $date = date($dateFormat, strtotime($date));
            $url = admin_url('admin.php?page=fluent_forms_settings&component=booking_settings_global#/bookings/'.$bookingId);
            return "<span> <a href='{$url}'>{$date} {$time}</a> </span>";
        }, 10, 4);

    }

    function getComponent()
    {
        return [
            'index' => 99,
            'element' => $this->key,
            'attributes' => [
                'name' => 'booking_datetime',
                'value' => '',
                'id' => '',
                'class' => '',
                'type' => 'text',
                'placeholder' => '',
            ],
            'settings' => [
                'container_class' => '',
                'placeholder' => '',
                'label' => $this->title,
                'label_placement' => '',
                'help_message' => '',
                'date_config' => '',
                'target_email' => '',
                'target_name' => '',
                'date_format' => 'd/m/Y',
                'admin_field_label' => '',
                'validation_rules' => [
                    'required' => [
                        'value' => false,
                        'message' => __('This field is required', FF_BOOKING_SLUG),
                    ]
                ],
                'conditional_logics' => []
            ],
            'editor_options' => [
                'title' => __('Booking Time & Date', FF_BOOKING_SLUG),
                'icon_class' => 'ff-edit-date',
                'template' => 'inputText'
            ],
        ];
    }

    public function getGeneralEditorElements()
    {
        return [
            'label',
            'label_placement',
            'admin_field_label',
            'placeholder',
            'date_format',
            'target_email',
            'target_name',
            'validation_rules',
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'value',
            'container_class',
            'class',
            'help_message',
            'name',
            'date_config',
            'conditional_logics',
        ];
    }
    public function getEditorCustomizationSettings()
    {
        return [

            'target_name' => array(
                'template'  => 'targetField',
                'target_element'=>'input_name',
                'label' => 'User Name Field Mapping',
                'help_text' => 'Select Customer Name for this booking'
            ),
            'target_email' => array(
                'template'  => 'targetField',
                'target_element'=>'input_email',
                'label' => 'User Email Field Mapping',
                'help_text' => 'Select Customer Email for this booking'
            )
        ];
    }

    public function render($data, $form)
    {
        $data['settings']['container_class'] .= ' ff-booking-container';
        $data['attributes']['class'] .= ' ff-booking-date-time';
        $this->loadScripts($data, $form);
        (new \FluentForm\App\Services\FormBuilder\Components\DateTime)->compile($data, $form);
    }

    /**
     * @param $formId
     * @param $config
     */
    private function loadScripts($data, $form)
    {
        wp_enqueue_style('ff_booking_test', FF_BOOKING_DIR_URL . 'public/css/form_booking.css', '', FF_BOOKING_VER);
        wp_enqueue_script(
            'ff_booking_test_js',
            FF_BOOKING_DIR_URL . 'public/js/ff-booking-date-time.js',
            ['jquery'],
            FF_BOOKING_VER,
            true
        );
        $ffBookingVars = apply_filters('ff_booking_datetime_vars', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'formId' => $form->id,
            'datetime_element_id' => $data['attributes']['id'],
            'current_form_title' => $form->title,
            'has_pro' => defined('FLUENTFORMPRO'),
            'first_week_day' => BookingHelper::firstWeekDay(),

        ], $form);

        wp_localize_script('ff_booking_test_js', 'ff_booking_date_time_vars', $ffBookingVars);
        add_filter('fluentform/frontend_date_format', function ($config, $settings, $form) {
            if(!ArrayHelper::exists($settings,'target_email')){
                return $config; //not booking date picker field
            }
            $config['inline'] = true;
            $config['minDate'] = "today";
            $config['locale'] = array(
                "firstDayOfWeek"=> BookingHelper::firstWeekDay()
            );
            return $config;
        }, 10, 3);
    }

    public function validate($errorMessage, $field, $formData, $fields, $form)
    {

        $name = \FluentForm\Framework\Helpers\ArrayHelper::get($field, 'name');
        $value = \FluentForm\Framework\Helpers\ArrayHelper::get($formData, $name);
        //check if has time
        if (empty($value)) {
            return;
        }
        $formatValue = explode(' ', $value);
        if (!is_array($formatValue) || count($formatValue) < 2) {
            return ["Please select a time slot."];
        }
        return;
    }


}
