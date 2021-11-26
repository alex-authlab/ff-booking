<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Helpers\ArrayHelper;


class BookingNotification
{
    
    private $form;
    private $formData;
    private $insertId;
    private $bookingData;

    private $notifications;
    
    public function init()
    {
        add_action('ff_booking_status_changing', array($this, 'processEmail'), 10, 3);
    }
    
    public function processEmail($bookingEntryId, $insertId, $status)
    {
        $bookingData = (new BookingModel())->getBooking(['id' => $bookingEntryId]);
        
        if (ArrayHelper::get($bookingData, 'send_notification') != 'yes') {
            return;
        }
        $this->setupData($bookingData['entry_id']);
        $notifications = json_decode(ArrayHelper::get($bookingData, 'notifications'), true);
        
        $userEmail = ArrayHelper::get($notifications, 'user.' . $status);
        $providerEmail = ArrayHelper::get($notifications, 'provider.' . $status);
      
        $this->sendEmail($userEmail, 'user');
        $this->sendEmail($providerEmail, 'provider');
    }
    
    
    public function sendEmail($notificationData, $receiver)
    {
 
        $bookingData = $this->bookingData;
        if ($receiver == 'user') {
            $email = ArrayHelper::get($bookingData, 'userData.email');
        } else {
            $email = ArrayHelper::get($bookingData, 'providerData.email');
        }
        if (!is_email($email) || ArrayHelper::get($notificationData, 'status') != 'yes') {
            return;
        }
        //@todo add filters
        $emailBody = $this->parse( ArrayHelper::get($notificationData,'body'));
        $emailBody = apply_filters('ffb_email_body', $emailBody);
        $email = $this->parse($email);
        $subject = $this->parse( ArrayHelper::get($notificationData,'subject'));
        $headers = [
            'Content-Type: text/html; charset=utf-8'
        ];
       
        return wp_mail(
            $email,
            $subject,
            $emailBody,
            $headers,
            ''
        );
    }
    
    private function setupData($insertId)
    {
        $submission = wpFluent()->table('fluentform_submissions')->find($insertId);
        $formData = json_decode($submission->response, true);
        $form = wpFluent()->table('fluentform_forms')->find($submission->form_id);
        $bookingData = (new BookingInfo($insertId))->getBookingInfoData();
        $notifications = (new BookingInfo($insertId))->getNotifications();
        
        $this->insertId = $insertId;
        $this->formData = $formData;
        $this->form = $form;
        $this->bookingData = $bookingData;
        $this->notifications = $notifications ?: array();
    }
    
    private function parse($data)
    {
        $output = ShortCodeParser::parse(
            $data,
            $this->insertId,
            $this->formData,
            $this->form
        );
        return $output;
    }
    
    
}
