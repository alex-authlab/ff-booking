<?php

namespace FF_Booking\Booking\Components;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FF_Booking\Booking\BookingHelper;
use FF_Booking\Booking\Models\ServiceModel;
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
    ) {
        parent::__construct(
            $key,
            $title,
            $tags,
            $position
        );
        add_filter('fluentform_response_render_ff_booking_service', function ($value, $field, $formId, $isHtml) {
            $service = (array)(new ServiceModel())->getService($value);
            return ArrayHelper::get($service,'title');
        }, 10, 4);
    }

    function getComponent()
    {
        return [
            'index' => 29,
            'element' => $this->key,
            'attributes' => [
                'name' => $this->key,
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
                'info' => 'test',
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
        $serviceData = (new ServiceModel())->getServices();
        $formattedOptions = [];
        foreach ($serviceData as $service) {
            if ($formIds = $service->allowed_form_ids) {
                if (!in_array($form->id, $formIds)) {
                    continue;
                }
            }
            $formattedOptions[] = [
                'label' => $service->title,
                'value' => $service->id,
                'calc_value' => $service->calc_value
            ];
        }
        $class = 'ff_booking_service';
        $data['attributes']['class'] .= " {$class}";
        $data['attributes']['id'] = $this->makeElementId($data, $form);
        $data['settings']['advanced_options'] = $formattedOptions;
        add_filter('ff_booking_datetime_vars', function ($vars) use ($class, $data) {
            $vars['ff_booking_service_input_class'] = $class;
            $vars['ff_booking_service_input_id'] = $data['attributes']['id'];
            return $vars;
        }, 99, 1);

        (new Select())->compile($data, $form);
    }
}
