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
    public $insertData;
    private $submissionId = null;
    private $bookingInputs;
    /**
     * @var array
     */
    private $bookingInputValues;
    /**
     * @var array|string
     */
    private $insertId;


    public function __construct($form, $insertData, $data)
    {
        $this->form = $form;
        $this->data = $data;
        $this->setSubmissionData($insertData);
        $this->setupData();
        $this->afterSubmissionInfo();
        $this->setBookingInputs();
        $this->validate();
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

    public function setBookingInputsValues($entryId, $formData, $form)
    {
        $bookingData = $this->bookingInputValues;
        $bookingData['form_id'] = $form->id;
        $bookingData['entry_id'] = $entryId;
        $bookingData['booking_hash'] = md5($form->id . '_booking_s_' . $bookingData['service_id'] . '_p_' . $bookingData['provider_id'] . '_' .$bookingData['booking_date'] . date('Y-m-d') . '-' . time() . '-' . mt_rand(100, 999));
        $bookingData['booking_status'] = $this->getDefaultStatus($bookingData['service_id']);
        $this->bookingInputValues = $bookingData;

        $this->insertBooking($bookingData);
    }

    public function setBookingInputs()
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
                $emailInput = ArrayHelper::get($bookingInput,'settings.target_email');
                $nameInput = ArrayHelper::get($bookingInput,'settings.target_name');
                $formattedData['email'] = $data[$emailInput];
                $formattedData['name'] = maybe_serialize($data[$nameInput]);

            } elseif ($element == 'ff_booking_provider') {
                $formattedData['provider_id'] = $data[$name];
            } elseif ($element == 'ff_booking_service') {
                $formattedData['service_id'] = $data[$name];
            }
        }
        $this->bookingInputValues = $formattedData;
    }

    private function getDefaultStatus($service_id)
    {
        $service = (new ServiceModel())->getService($service_id);
        if( !$service->default_booking_status){
            return 'booked';
        }
        return $service->default_booking_status;
    }

    /**
     * @param $data
     * @param array $formattedData
     * @return array
     */
    private function getFormattedDateTime($inputDateTime, array $formattedData)
    {
        $bookingDatetime = explode(" ", $inputDateTime);
        $formattedData['booking_date'] = $bookingDatetime[0];
        $timeFormat = '12';
        //if time format 12 hour append am/pm
        if ($timeFormat) {
            $formattedData['booking_time'] = $bookingDatetime[1] . ' ' . $bookingDatetime[2];
            $formattedData['booking_time'] = BookingHelper::convertTime('24', $formattedData['booking_time']);
        } else {
            $formattedData['booking_time'] = $bookingDatetime[1];
        }
        return $formattedData;
    }

    private function insertBooking( $bookingData)
    {

        $this->insertId = (new BookingModel())->insert($bookingData);
        do_action('ff_booking_inserted', $this->insertId ,'new',$bookingData);
        do_action('ff_booking_status_changing',  $this->insertId, $bookingData['booking_status'],$bookingData);

    }

    private function validate()
    {
        $values = $this->bookingInputValues;
        $serviceId = ArrayHelper::get($values,'service_id');
        $providerId = ArrayHelper::get($values,'provider_id');
        $bookingDate = ArrayHelper::get($values,'booking_date');
        $bookingTime = ArrayHelper::get($values,'booking_time');
        //valid date
        $response = (new DateTimeHandler($serviceId,$providerId,$this->form->id,$bookingDate))->isValidSlot($bookingTime);
        if($response['status'] == false){
            wp_send_json(['errors' => $response['message']], 422);
        }

    }

    public function modifyBookingInput( $insertId, $formData, $form)
    {

        $response = $formData;
        // Find the database Entry First
        $entry = wpFluent()->table('fluentform_submissions')
            ->where('id', $insertId)
            ->first();

        if (!$entry) {
            return ;
        }

        $bookingInputs = $this->bookingInputs;
        $bookingInputValue = [];
        if (!$bookingInputs && !$this->bookingInputs) {
            return [];
        }

        $data = $this->submissionData['response'];

        $bookingInputValue = '';
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
                $bookingInputValue = $data[$name];
                $bookingInputName = $name;
            }
        }
        if(empty($bookingInputValue)){
           return;
        }
        $origianlResponse = json_decode($entry->response, true);
        $valueWithBookingEntryID = [
            'value'=>$bookingInputValue,
            'booking_id'=>$this->insertId
        ];
        $response[$bookingInputName] = serialize($valueWithBookingEntryID);

        $diffs = [];
        foreach ($response as $resKey => $resvalue) {
            if (!isset($origianlResponse[$resKey]) || $origianlResponse[$resKey] != $resvalue) {
                $diffs[$resKey] = $resvalue;
            }
        }

        if (!$diffs) {
            return true;
        }

        $response = wp_parse_args($response, $origianlResponse);

        wpFluent()->table('fluentform_submissions')
            ->where('id', $insertId)
            ->update([
                'response' => json_encode($response),
                'updated_at' => current_time('mysql')
            ]);

        $entries = new Entries();

        $entries->updateEntryDiffs($insertId, $this->form_id, $diffs);

        $message = 'Entry data has been updated by with Booking Entry';

        do_action('ff_log_data', [
            'parent_source_id' => $entry->form_id,
            'source_type' => 'submission_item',
            'source_id' => $entry->id,
            'component' => 'FFBooking',
            'status' => 'info',
            'title' => 'Entry Booking Data Updated',
            'description' => $message,
        ]);

        return true;
    }

    private function afterSubmissionInfo()
    {
        // booking info after submission
        add_filter('fluentform_submission_message_parse', function ($messageToShow, $insertId, $formData, $form) {
            $html = (new BookingInfo($insertId))->getInfoHtml();
            if($html){
                $messageToShow.= $html;
            }
            return $messageToShow;
        }, 10, 4);
    }

}
