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
        $key = 'ff_service_id',
        $title = 'Service List',
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
//        add_filter('fluentform_response_render_'.$this->key, function ($value) {
//
//        });
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
                'label' => __('Service List', 'fluentform'),
                'admin_field_label' => '',
                'help_message' => '',
                'container_class' => '',
                'label_placement' => '',
                'placeholder' => '- Select -',
                'post_type_selection' => 'post',
                'post_extra_query_params' => '',
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
                'title' => __('Service Selection', 'fluentform'),
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
            'enable_select_2'
        ];
    }
    
    public function generalEditorElement()
    {
       
        
        return [
        
        ];
    }
    //     todo remove static id and name
    public function render($data, $form)
    {
       
        $serviceData = BookingHelper::getService ();
        $formattedOptions = [];
        
      
        foreach ($serviceData as $d) {
            $formattedOptions[] = [
                'label' => $d->name,
                'value' => $d->id,
                'calc_value' => ''
            ];
        }
        wp_enqueue_script('choices');
        wp_enqueue_style('ff_choices');
        $data['attributes']['class'] .= ' ff_has_multi_select';
        $data['attributes']['id'] = 'ff-booking-service';
        $data['settings']['advanced_options'] = $formattedOptions;
        
        (new Select())->compile($data, $form);

      
    }
}
