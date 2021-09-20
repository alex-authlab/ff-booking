<?php

namespace FF_Booking\Booking;

use \FluentForm\App\Modules\Form\FormFieldsParser;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


class BookingActions
{

    private $form;
    private $data;
    private $submissionData;
    private $insertData;
    private $submissionId = null;


    public function __construct($insertId, $formData, $form)
    {
        $this->form = $form;
        $this->submissionId = $insertId;
        $this->submissionData = $formData;
    }


    public function bookingEntry()
    {
        //$formSettings = PaymentHelper::getFormSettings($this->form->id, 'public');
        $submission = $this->submissionData;

//        $inputData = FormFieldsParser::getElement( $this->form, ['input_booking_ff']);
//         get input name ,
//         to do limit single booking input component
        $input = FormFieldsParser::getInputsByElementTypes($this->form, ['input_booking_ff']);
        $bookingInput = key($input);

        $date = $this->submissionData[$bookingInput]['date'];
        $date = BookingHelper::regularDateToMysql($date);
        $data = [
            'form_id' => $this->form->id,
            'entry_id' => $this->submissionId,
            'service_id' => $this->submissionData[$bookingInput]['service_id'],
            'date' => $date,
            'time' => $this->submissionData[$bookingInput]['time'],
            'booking_status' => 'booked',
            'updated_at' => current_time('mysql'),
            'created_at' => current_time('mysql')
        ];

        $entry = wpFluent()->table('alex_booking_entries')->insert($data);
    }

}
