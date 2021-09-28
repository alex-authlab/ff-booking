<?php

namespace FF_Booking\Booking\Components;

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
                'date_format' => 'd/m/Y',
                'admin_field_label' => '',
                'validation_rules' => [
                    'required' => [
                        'value' => false,
                        'message' => __('This field is required', 'fluentformpro'),
                    ]
                ],
                'conditional_logics' => []
            ],
            'editor_options' => [
                'title' => __('Booking Time & Date', 'fluentformpro'),
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
        wp_localize_script('ff_booking_test_js', 'ff_booking_date_time_vars', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'formId' => $form->id,
            'datetime_element_id' =>$data['attributes']['id']
        ]);
        add_filter('fluentform/frontend_date_format', function ($config, $settings, $form) {
            $config['inline'] = true;
            return $config;
        }, 10, 3);
    }


}
