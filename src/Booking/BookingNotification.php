<?php

namespace FF_Booking\Booking;

use FF_Booking\Booking\Models\BookingModel;
use FluentForm\Framework\Helpers\ArrayHelper;


class BookingNotification
{

    public function init()
    {
        add_action('ff_booking_inserted', array($this, 'emailAction'), 99, 3);
        add_action('ff_booking_status_changing', array($this, 'emailAction'), 99, 3);
    }

    public function emailAction($bookingId, $status, $bookingData)
    {
        $infoObject = (new BookingModel())->getBookings(false, ['id' => $bookingId]);
        $infoArray = (array)$infoObject[0];
        $notifications = json_decode($infoArray['notifications'], true);
        $userEmail = ArrayHelper::get($infoArray,'email');
        if(!$userEmail){
            return;
        }
        $emailBody = $this->getBody($infoArray);
        foreach ($notifications as $key => $notification) {
            if($key == 'instant_email'){
//                sendemail
                if(ArrayHelper::get($notification,'status')!='yes'){
                    return;
                }
                $this->sendEmail($notification,$userEmail,$emailBody);

            }
            if($key == 'confirm_email'){
                //send email on  bookied satus action
            }
            if($key == 'query_email'){
                //insert ot db with booking id
            }
            if($key == 'reminder_email'){
                //insert ot db with booking id
            }
        }


    }

    public function sendEmail($data, $userEmail, $emailBody)
    {
        $headers = [
            'Content-Type: text/html; charset=utf-8'
        ];
        return wp_mail(
            $userEmail,
            $data['subject'],
            $emailBody,
            $headers,
            ''
        );
    }


    private function getBody($bookingData)
    {

        $emailData =  ArrayHelper::only($bookingData,[
            'name',
            'service',
            'provider',
            'booking_date',
            'booking_time',
            'booking_status',
            'duration',
            'booking_hash',
            'policy',
            'description',
            'created_at'
        ]);
        extract($emailData);
        $userName = maybe_unserialize($name);
        $firstName = ArrayHelper::get($userName,'first_name');
        $lastName = ArrayHelper::get($userName,'last_name');


        $html = '<table class="ff_all_data" width="600" cellpadding="0" cellspacing="0"><tbody>';
        $html.= '<tr><td style="padding: 6px 12px 12px 12px;"> Hello ,'.$firstName .' '.$lastName.'   </td></tr>';
        $html.= '<tr><td style="padding: 6px 12px 12px 12px;">Here is your Booking Information   </td></tr>';
        foreach ($emailData as $key => $value) {
            if (!empty($value)) {
                $label = str_replace(' ', '_',$key);
                if ( $key == 'name') {
                   continue;
                }
                elseif ($key == 'booking_hash'){
                    $label = "View Details";
                    $value = '<a href="#">Link</a>';
                }
                elseif ($key == 'booking_date'){
                    $value = date('l F j, Y',strtotime($value));
                }
                elseif ($key == 'booking_time'){
                    $value = date('h:i a',strtotime($value));
                }
                $html .= '<tr class="field-label"><th style="padding: 6px 12px; background-color: #f8f8f8; text-align: left;"><strong>' .ucwords( $label ). '</strong></th></tr><tr class="field-value"><td style="padding: 6px 12px 12px 12px;">' . $value . '</td></tr>';
            }
        }
       return $html;


    }
}
