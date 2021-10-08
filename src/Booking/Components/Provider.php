<?php

namespace FF_Booking\Booking\Components;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FF_Booking\Booking\BookingHelper;
use FF_Booking\Booking\Models\ProviderModel;
use \FluentForm\App\Modules\Component\Component;
use \FluentForm\App\Services\FormBuilder\BaseFieldManager;
use \FluentForm\App\Services\FormBuilder\Components\Select;
use \FluentForm\Framework\Helpers\ArrayHelper;

class Provider extends BaseFieldManager
{

    public function __construct(
        $key = 'ff_booking_provider',
        $title = 'Provider',
        $tags = ['provider', 'booking'],
        $position = 'advanced'
    ) {
        parent::__construct(
            $key,
            $title,
            $tags,
            $position
        );

        add_filter('fluentform_response_render_ff_booking_provider', function ($value, $field, $formId, $isHtml) {
            $provider = (array)(new ProviderModel())->getProvider($value);
            return ArrayHelper::get($provider,'title');
        }, 10, 4);
    }

    function getComponent()
    {
        return [
            'index' => 30,
            'element' => $this->key,
            'attributes' => [
                'name' => $this->key,
                'value' => '',
                'id' => '',
                'class' => '',
            ],
            'settings' => array(
                'dynamic_default_value' => '',
                'label' => __('Provider', 'fluentform'),
                'admin_field_label' => '',
                'help_message' => '',
                'container_class' => '',
                'label_placement' => '',
                'info' => 'test',
                'placeholder' => '- Select Service -',
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
                'title' => __('Provider', 'fluentform'),
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

    public function render($data, $form)
    {

        $class= ' ff_booking_provider';
        $data['attributes']['class'] .= " {$class}";
        $data['attributes']['id'] = $this->makeElementId($data, $form);
        add_filter('ff_booking_datetime_vars',function ($vars) use ($class,$data){
            $vars['ff_booking_provider_input_class'] = $class;
            $vars['ff_booking_provider_input_id'] = $data['attributes']['id'];
            return $vars;
        },99,1);

        $data['settings']['advanced_options'] = [];
        (new Select())->compile($data, $form);
    }
}
