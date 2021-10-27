<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Helpers\ArrayHelper;


class BookingNotification
{

    public function init()
    {
        add_action('ff_booking_inserted', array($this, 'sendInstantEmail'), 99, 5);
//        add_action('ff_booking_status_changing', array($this, 'emailAction'), 99, 3);
    }

    public function sendInstantEmail($bookingData, $bookinEntryId,$insertId, $formData, $form)
    {
        $this->insertId = $insertId;
        $this->formData = $formData;
        $this->form = $form;
        $emailData = (new BookingInfo($insertId))->getBookingInfoData();
        $notifications = (new BookingInfo($insertId))->getNotifications();
        if (!$notifications) {
            return;
        }
        $instantEmail = ArrayHelper::get($notifications, 'instant_email');
        $this->processEmail($instantEmail, $emailData );
//        foreach ($notifications as $key => $notification) {
//            if (ArrayHelper::get($notification, 'status') != 'yes') {
//                continue;
//            }
//            if ($key == 'instant_email') {
//            }
//            if ($key == 'confirm_email') {
//                //send email on  bookied satus action
//            }
//            if ($key == 'query_email') {
//                //insert ot db with booking id
//            }
//            if ($key == 'reminder_email') {
//                //insert ot db with booking id
//            }
//        }
    }

    private function parse($data,$emailData)
    {
        $output = ShortCodeParser::parse(
            $data,
            $this->insertId,
            $this->formData,
            $this->form
        );
        return $output;

    }

    private function processEmail($notification, $emailData)
    {
        if (ArrayHelper::get($notification, 'status') != 'yes') {
            return;
        }
        $this->sendEmail($notification, $emailData, 'user');
        $this->sendEmail($notification, $emailData, 'provider');
    }

    public function sendEmail($notificationData, $emailData, $receiver)
    {
        if ($receiver == 'user') {
            $email = ArrayHelper::get($emailData, 'userData.email');
        } else {
            $email = ArrayHelper::get($emailData, 'providerData.email');
        }
        if (!is_email($email)) {
            return ;
        }
        //@todo add & parse shortcode with fluent parser
        $emailBody = $this->parse($notificationData['body'],$emailData );
        $headers = [
            'Content-Type: text/html; charset=utf-8'
        ];
        return wp_mail(
            $email,
            $notificationData['subject'],
            $emailBody,
            $headers,
            ''
        );
    }


}
