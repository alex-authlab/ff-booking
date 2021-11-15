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
    /**
     * @var mixed
     */
    private $formData;
    private $insertId;
    private $bookingData;
    /**
     * @var array
     */
    private $notifications;

    public function init()
    {
        add_action('ff_booking_inserted', array($this, 'sendInstantEmail'), 99, 3);
        add_action('ff_booking_status_changing', array($this, 'sendConfirmEmail'), 99, 3);
    }

    public function sendInstantEmail($bookinEntryId, $insertId, $bookingData)
    {
        $bookingData = (new BookingModel())->getBooking(['id' => $bookinEntryId]);
        $this->setupData($bookingData['entry_id']);

        $instantEmail = ArrayHelper::get($this->notifications, 'instant_email');
        $this->processEmail($instantEmail);

//        instant_email
//        confirm_email
//        send email on  booked status action
//        query_email
//        insert to db with a scheduled date calculated from number of days booking id after completed
//        reminder_email
//        same but before event date
//        add logs

    }

    public function sendConfirmEmail($bookinEntryId, $bookingStatus)
    {
        if ($bookingStatus != 'booked') {
            return;
        }
        $bookingData = (new BookingModel())->getBooking(['id' => $bookinEntryId]);
        if (ArrayHelper::get($bookingData, 'send_notification') != 'yes') {
            return;
        }
        $this->setupData($bookingData['entry_id']);

        $confirmEmail = ArrayHelper::get($this->notifications, 'confirm_email');
        $this->processEmail($confirmEmail);
    }


    private function processEmail($notification)
    {
        if (ArrayHelper::get($notification, 'status') != 'yes') {
            return;
        }
        $this->sendEmail($notification, 'user');
        $this->sendEmail($notification, 'provider');
    }

    public function sendEmail($notificationData, $receiver)
    {
        $bookingData = $this->bookingData;
        $links = '';
        if ($receiver == 'user') {
            $email = ArrayHelper::get($bookingData, 'userData.email');
            $links = $this->userEmailLinks($bookingData);

        } else {
            $email = ArrayHelper::get($bookingData, 'providerData.email');
            $links = $this->providerEmailLinks($bookingData);
        }
        if (!is_email($email)) {
            return;
        }
        //@todo add filters
        $emailBody = $this->parse($notificationData['body']);
        $emailBody = apply_filters('ffb_email_body', $emailBody);
        $emailBody .= $links;
        $email = $this->parse($email);
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

    private function userEmailLinks($bookingData)
    {
        $hash = ArrayHelper::get($bookingData,'bookingData.booking_hash');
        $pageLink = get_site_url() . '?ff_simple_booking=' . $hash;
        $cancelLink = ArrayHelper::get($bookingData,'userData.allow_user_cancel') =='yes';
        $reschedulelLink = ArrayHelper::get($bookingData,'userData.allow_user_reschedule') =='yes';
        if ($cancelLink || $reschedulelLink) {
            $html = '<p>Visit this link to make changes</p>
            <a style="color: #ffffff;
            background-color: #409EFF;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            padding: 0.5rem 1rem;
            border-color: #0072ff;" target="_blank" href="' . $pageLink . '">View Details</a>';
            return $html;
        }
        return '';
    }

    private function providerEmailLinks($bookingData)
    {
        return '';
    }

}
