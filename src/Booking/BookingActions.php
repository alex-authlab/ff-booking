<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FF_Booking\Booking\Models\ServiceModel;
use \FluentForm\App\Modules\Form\FormFieldsParser;
use \FluentForm\Framework\Helpers\ArrayHelper;
use \FluentForm\App\Modules\Entries\Entries;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


class BookingActions
{

    private $form;
    public $data;
    private $submissionData;
    private $bookingInputs;
    private $bookingInputValues;

    public function init($form, $insertData, $data)
    {
        $this->form = $form;
        $this->data = $data;
        $this->setSubmissionData($insertData);
        $this->setupData();
        $this->setBookingInputs($form);
        $this->validate();
        $this->afterSubmissionInfo();
        add_action('fluentform_before_form_actions_processing', array($this, 'setBookingInputsValues'), 10, 3);
    }

    private function setSubmissionData($insertData)
    {
        $insertData = (array)$insertData;
        $insertData['response'] = json_decode($insertData['response'], true);
        $this->submissionData = $insertData;
    }

    private function setupData()
    {
        $formFields = FormFieldsParser::getFields($this->form, true);
        $bookingInputElements = ['booking_datetime', 'ff_booking_provider', 'ff_booking_service'];
        $bookingInputs = [];
        foreach ($formFields as $fieldKey => $field) {
            $element = ArrayHelper::get($field, 'element');
            if (in_array($element, $bookingInputElements)) {
                $bookingInputs[$fieldKey] = $field;
            }
        }
        $this->bookingInputs = $bookingInputs;
    }

    public function setBookingInputsValues($insertId, $formData, $form)
    {
        $bookingData = $this->bookingInputValues;
        $bookingData = [
            'entry_id'     => $insertId,
            'form_id'      => $form->id,
            'name'         => BookingHelper::getUserName($formData, $form),
            'email'        => BookingHelper::getUserEmail($formData, $form),
            'booking_status' => $this->getDefaultStatus($bookingData['service_id']),
            'booking_hash' => wp_generate_uuid4()
        ];
        $bookingData  = array_merge_recursive($bookingData, $this->bookingInputValues);
        $this->insertBooking($bookingData, $insertId, $formData, $form);
    }

    public function setBookingInputs($form)
    {
        $bookingInputs = $this->bookingInputs;
        $formattedData = [];
        if (!$bookingInputs && !$this->bookingInputs) {
            return [];
        }

        $data = $this->submissionData['response'];

        foreach ($bookingInputs as $bookingInput) {
            $name = ArrayHelper::get($bookingInput, 'attributes.name');
            if (!$name || !isset($data[$name])) {
                continue;
            }
            if (!$data[$name]) {
                continue;
            }
            $element = ArrayHelper::get($bookingInput, 'element');
            if ($element == 'booking_datetime') {
                $formattedData = $this->getFormattedDateTime($data[$name], $formattedData);
            } elseif ($element == 'ff_booking_provider') {
                $formattedData['provider_id'] =  ArrayHelper::get($data,$name);
            } elseif ($element == 'ff_booking_service') {
                $formattedData['service_id'] =  ArrayHelper::get($data,$name);
            }
        }
        $this->bookingInputValues = $formattedData;
    }

    public function getDefaultStatus($service_id)
    {
        $service = (new ServiceModel())->getService($service_id);
        if (!$service->default_booking_status) {
            return 'booked';
        }
        return $service->default_booking_status;
    }

    /**
     * @param $data
     * @param array $formattedData
     * @return array
     */
    public static function getFormattedDateTime($inputDateTime, array $formattedData)
    {
        $bookingDatetime = explode(" ", $inputDateTime);
        $formattedData['booking_date'] = $bookingDatetime[0];
        $formattedData['booking_time'] = $bookingDatetime[1];
        return $formattedData;
    }

    public function insertBooking($bookingData, $insertId, $formData, $form)
    {
        $bookinEntryId = (new BookingModel())->insert($bookingData);
        do_action('ff_booking_inserted', $bookinEntryId, $insertId, $bookingData);
        do_action('ff_booking_status_changing', $bookinEntryId, $insertId, $bookingData['booking_status']);
    }

    private function validate()
    {
        $values = $this->bookingInputValues;
        $serviceId = ArrayHelper::get($values, 'service_id');
        $providerId = ArrayHelper::get($values, 'provider_id');
        $bookingDate = ArrayHelper::get($values, 'booking_date');
        $bookingTime = ArrayHelper::get($values, 'booking_time');
        //valid date
        $response = (new DateTimeHandler($serviceId, $providerId, $this->form->id, $bookingDate))->isValidSlot(
            $bookingTime
        );
        if ($response['status'] == false) {
            wp_send_json(['errors' => $response['message']], 422);
        }
    }

    
    /**
     * Append booking info with submission message
     */
    public function afterSubmissionInfo()
    {
        
        add_filter('fluentform_submission_message_parse', function ($messageToShow, $insertId, $formData, $form) {
            $html = (new BookingInfo($insertId))->getConfirmationHtml();
            if ($html) {
                $messageToShow .= $html;
            }
            return $messageToShow;
        }, 10, 4);
    }




}
