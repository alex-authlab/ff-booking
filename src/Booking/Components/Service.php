<?php

namespace FF_Booking\Booking\Components;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FF_Booking\Booking\BookingHelper;
use \FluentForm\App\Modules\Component\Component;
use \FluentForm\App\Services\FormBuilder\BaseFieldManager;
use \FluentForm\App\Services\FormBuilder\Components\Select;
use \FluentForm\Framework\Helpers\ArrayHelper;

class Service extends BaseFieldManager
{
   
    public function __construct(
        $key = 'ff_booking_service',
        $title = 'Service',
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
        add_filter('fluent_editor_element_customization_settings', function ($values) {

            $extra['info'] = array(
                'template' => 'infoBlock',
                'label' => __('Info here', 'fluentform'),
                'help_text' => __('Please provide Maximum value', 'fluentform')
            );
            return array_merge($values,$extra);

        });
    }
    
    function getComponent()
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
                'label' => __('Service', 'fluentform'),
                'admin_field_label' => '',
                'help_message' => '',
                'container_class' => '',
                'label_placement' => '',
                'info'=> 'test',
                'placeholder' => '- Select -',
                'enable_select_2' => 'no',
                'validation_rules' => array(
                    'required' => array(
                        'value' => true,
                        'message' => __('This field is required', 'fluentform'),
                    ),
                ),
                'conditional_logics' => array(),
            ),
            'editor_options' => array(
                'title' => __('Service', 'fluentform'),
                'icon_class' => 'ff-edit-dropdown',
                'element' => 'select',
                'template' => 'select'
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
            'dynamic_default_value',
            'help_message',
            'container_class',
            'class',
            'conditional_logics',
            'enable_select_2',
            'info'
        ];
    }
    public function render($data, $form)
    {
       
        $serviceData = BookingHelper::getService ();
        $formattedOptions = [];

        foreach ($serviceData as $service) {
            $formattedOptions[] = [
                'label' => $service->name,
                'value' => $service->id,
                'calc_value' => ''
            ];
        }
        $data['attributes']['class'] .= ' ff_booking_service';
        $data['settings']['advanced_options'] = $formattedOptions;
        
        (new Select())->compile($data, $form);

      
    }
}
