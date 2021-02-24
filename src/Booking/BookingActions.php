<?php

namespace FF_Booking\Booking;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


Class BookingActions {

 	private $form;

    private $data;

    private $submissionData;
    private $insertData;

    private $submissionId = null;

    private $orderItems = [];

    private $quantityItems = [];

    public $selectedPaymentMethod = '';

    public $methodSettings = [];

    protected $paymentInputs = null;

    protected $currency = null;

    protected $methodField = null;

    protected $discountCodes = [];

    protected $couponField = [];

    public function __construct( $insertId, $formData, $form )
    {
        $this->form = $form;
        $this->submissionId = $insertId;
        $this->submissionData = $formData;
    
    
    
    }



	 public function bookingEntry()
    {
        //$formSettings = PaymentHelper::getFormSettings($this->form->id, 'public');
        $submission = $this->submissionData;
    
        $date = $this->submissionData['ff_date_slot'];
        $date = BookingHelper::regularDateToMysql ($date);
        $data = [
            'form_id'        => $this->form['id'],
            'entry_id'  => $this->submissionId,
            'service_id'  => $this->submissionData['ff_service_id'],
            'date' => $date,
            'time'=>  $this->submissionData['ff_time_slot'],
            'status'=>'booked',
            'updated_at' => current_time('mysql'),
            'created_at' => current_time('mysql')
         ];
       
        $entry = wpFluent()->table('alex_booking_entries')->insert($data);

       
    }

}
