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
        $providerData = BookingHelper::getService();
        $formattedOptions = [];

        foreach ($providerData as $provider) {
            $formattedOptions[] = [
                'label' => $provider->name,
                'value' => $provider->id,
                'calc_value' => ''
            ];
        }
        $data['attributes']['class'] .= ' ff_booking_provider';
        $data['settings']['advanced_options'] = $formattedOptions;
        (new Select())->compile($data, $form);
    }
}
