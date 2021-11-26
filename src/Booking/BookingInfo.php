<?php

namespace FF_Booking\Booking;

use FluentForm\Framework\Helpers\ArrayHelper;
use FF_Booking\Booking\Models\BookingModel;

class BookingInfo
{
    private $data;
    private $notifications;
    private $submissionInfoEnabled;

    /**
     * @param $insertId
     */
    public function __construct($insertId)
    {
        $this->setupData($insertId);
    }

    /**
     * @param $insertId
     * @return array|false
     */
    private function setupData($insertId)
    {
        $data = (new BookingModel())->getBooking(['entry_id' => $insertId]);
        if(!$data){
            return ;
        }
        $this->data = [
            'bookingData' => $this->getBookingData($data),
            'userData' => $this->getUserData($data),
            'providerData' => $this->getProvider($data)
        ];
        $this->submissionInfoEnabled = ArrayHelper::get($data, 'append_info') == 'yes';
        $this->notifications = json_decode($data['notifications'], true);
    }

    /**
     * Booking Providers
     *
     * @param array $data
     * @todo action bttns
     */
    private function getProvider(array $data)
    {
        $providerData = ArrayHelper::only($data, ['provider_id', 'provider']);
        if ($provider = get_user_by('id', $providerData['provider_id'])) {
            $providerData['email'] = $provider->user_email;
        }
        return $providerData;
    }

    /**
     * Booking Information
     *
     * @param array $data
     * @return mixed
     */
    private function getBookingData($data)
    {
        $bookingData = ArrayHelper::only($data, [
            'service',
            'provider',
            'booking_date',
            'booking_time',
            'booking_status',
            'duration',
            'booking_hash',
            'policy',
            'description',
        ]);

        $time = ArrayHelper::get($data, 'booking_time');
        $bookingData['booking_time'] = BookingHelper::formatTime($time);

        $date = ArrayHelper::get($data, 'booking_date');
        $bookingData['booking_date'] = BookingHelper::formatDate($date,'l F j Y');
        return $bookingData;
    }
    public function getConfirmationHtml()
    {
        if (!$this->submissionInfoEnabled) {
            return false;
        }
        $data = $this->data;
        $description = ucfirst(ArrayHelper::get($data, 'bookingData.description'));
        $descriptionHtml = '';
        if ($description) {
            $descriptionHtml = '<tr><td> ' . $description . '</td></tr> ';
        }
        $bookingDate = ArrayHelper::get($data, 'bookingData.booking_date');
        $bookingTime = ArrayHelper::get($data, 'bookingData.booking_time');
        $status = ucfirst(ArrayHelper::get($data, 'bookingData.booking_status'));
        $service = ArrayHelper::get($data, 'bookingData.service');
        $provider = ArrayHelper::get($data, 'providerData.provider');

        $html = '<table class="ff_all_data" style="margin: 10px 0;" width="600" cellpadding="0" cellspacing="0">
                    <tbody>
                      <tr>
                        <th style="text-align: left;" >
                          Appoinement ' . $status . '
                        </th>          
                      </tr>
                      <tr>
                         <td>
                        Your Appointment for <b>' . $service . '</b> by <b>' . $provider . '</b>
                        </td>  
                      </tr>  
                      <tr>
                        <td><b>Time</b> : ' . $bookingTime . '</td>  
                      </tr>
                      <tr>
                         <td><b>Date</b> : ' . $bookingDate . '</td>  
                      </tr> 
                      ' . $descriptionHtml . '
                    </tbody>
                  </table>';
        //add filter
        return $html;
    }

    public function getNotifications()
    {
        $notifications = $this->notifications;
        if(is_array($notifications) && count($notifications)> 0){
            return $notifications;
        }
        return false;
    }

    public function getBookingInfoData()
    {
        return $this->data;
    }

    public function bookingInfoHtml()
    {
        $bookingInfo = ArrayHelper::get($this->data,'bookingData');
        if(!$bookingInfo){
            return ;
        }
        $html = '<table width="600" cellpadding="0" cellspacing="0"><tbody>';
        $html .= '<tr><td style="padding: 6px 12px 12px 12px;"> Hello,    </td></tr>';
        $html .= '<tr><td style="padding: 6px 12px 12px 12px;">Here is your Booking Update   </td></tr>';

        foreach ($bookingInfo as $key => $value) {
            if (empty($value) || $key == 'booking_hash') {
                continue;
            }
            $label = str_replace(' ', '_', $key);
            $html .= sprintf(
                "<tr class=\"field-label\"><th style=\"padding: 6px 12px; background-color: #f8f8f8; text-align: left;\"><strong>%s</strong></th></tr><tr class=\"field-value\"><td style=\"padding: 6px 12px 12px 12px;\">%s</td></tr>",
                ucwords($label), $value
            );
        }
        $html .= '</tbody></table>';
        return $html;
    }

    public function infoPageLinkHtml ()
    {
        
    }
    
    /**
     * @param array $data
     * @return mixed
     */
    private function getUserData($data)
    {
        $userData = ArrayHelper::only($data, ['name', 'email', 'allow_user_cancel', 'allow_user_reschedule']);
        $name = maybe_unserialize(ArrayHelper::get($data, 'name'));
        $userData['name'] = is_array($name) ? join(' ', $name) : $name;
        return $userData;
    }
    
    
}
