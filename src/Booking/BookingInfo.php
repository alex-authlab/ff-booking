<?php

namespace FF_Booking\Booking;

use FluentForm\Framework\Helpers\ArrayHelper;
use FF_Booking\Booking\Models\BookingModel;

class BookingInfo
{
    private $info;

    /**
     * @param $insertId
     */
    public function __construct($insertId)
    {
        $this->setInfo($insertId);
    }

    public function setInfo($insertId)
    {
        $this->info = (new BookingModel())->getBooking(['entry_id' => $insertId]);
    }

    public function getInfoHtml()
    {
        $data = $this->info;
        $enabled = ArrayHelper::get($data,'append_info') =='yes';
        
        if(!is_array($data) || !$enabled){
            return false;
        }
        $status = ucfirst( ArrayHelper::get($data,'booking_status'));
        $service = ArrayHelper::get($data,'service');
        $provider = ArrayHelper::get($data,'provider');
        $date = ArrayHelper::get($data,'booking_date');
        $date = date('l F j Y',strtotime($date));
        $time = ArrayHelper::get($data,'booking_time');
        $time = date('h:i a',strtotime($time));
        $html = '<table class="ff_all_data" style="margin: 10px 0;" width="600" cellpadding="0" cellspacing="0">
                    <tbody>
                      <tr>
                        <th style="text-align: left;" >
                          Appoinement '.$status.'
                        </th>          
                      </tr>
                      <tr>
                         <td>
                        Your Appointment for '.$service.' by '.$provider.'
                        </td>  
                      </tr>  
                      <tr>
                        <td><b>Time</b> : '.$time.'</td>  
                      </tr>
                      <tr>
                         <td><b>Date</b> : '.$date.'</td>  
                      </tr> 
                    </tbody>
                  </table>';
        return $html;
    }


}
