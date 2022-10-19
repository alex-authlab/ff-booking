<?php

namespace FF_Booking\Booking\Addons;

use FF_Booking\Booking\BookingHelper;
use FF_Booking\Booking\BookingInfo;
use FF_Booking\Booking\Models\BookingModel;
use FluentForm\Framework\Helpers\ArrayHelper;

if (!defined('ABSPATH')) {
    exit;
}

class GoogleCalendarController
{
    private $clientId = '706994733947-ooa5hgstrpdolu991ocpug7l7cmsph6u.apps.googleusercontent.com';
    private $clientSecret = '2ZtC-jLAHr93_EsqjmyZ7gCF';
    private $redirect = 'urn:ietf:wg:oauth:2.0:oob';

    private $optionKey = '_ffsb_gcalendar_settings';
    private $apiUrl = 'https://www.googleapis.com/calendar/v3/';
    private $providerId;

    public function __construct()
    {
        $this->providerId = get_current_user_id();
    }

    public function init()
    {
        if (!BookingHelper::getSettingsByKey('enable_gcalendar')) {
            return;
        }
        add_filter('ffs_provider_view_config', array($this, 'addViewConfig'), 10, 1);

        add_action('ff_booking_status_changing', array($this, 'addEvent'), 10, 3);
    }

    public function makeRequest($url, $bodyArgs, $type = 'GET', $headers = false)
    {
        if (!$headers) {
            $headers = array(
                'Content-Type'              => 'application/http',
                'Content-Transfer-Encoding' => 'binary',
                'MIME-Version'              => '1.0',
            );
        }
        $args = [
            'headers' => $headers
        ];
        if ($bodyArgs) {
            $args['body'] = json_encode($bodyArgs);
        }

        $args['method'] = $type;
        $request = wp_remote_request($url, $args);

        if (is_wp_error($request)) {
            $message = $request->get_error_message();
            return new \WP_Error(423, $message);
        }

        $body = json_decode(wp_remote_retrieve_body($request), true);

        if (!empty($body['error'])) {
            $error = 'Unknown Error';
            if (isset($body['error_description'])) {
                $error = $body['error_description'];
            } else {
                if (!empty($body['error']['message'])) {
                    $error = $body['error']['message'];
                }
            }
            return new \WP_Error(423, $error);
        }

        return $body;
    }

    public function generateAccessKey($token)
    {
        $body = [
            'code'          => $token,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirect,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret
        ];
        return $this->makeRequest('https://accounts.google.com/o/oauth2/token', $body, 'POST');
    }

    public function getAccessToken()
    {
        $tokens = get_user_meta($this->providerId, $this->optionKey, true);

        if (!$tokens['status'] == true) {
            return false;
        }
        if (($tokens['created_at'] + $tokens['expires_in'] - 30) < time()) {
            // It's expired so we have to re-issue again
            $refreshTokens = $this->refreshToken($tokens);

            if (!is_wp_error($refreshTokens)) {
                $tokens['access_token'] = $refreshTokens['access_token'];
                $tokens['expires_in'] = $refreshTokens['expires_in'];
                $tokens['created_at'] = time();
                update_user_meta($this->providerId, $this->optionKey, $tokens);
            } else {
                return false;
            }
        }

        return $tokens['access_token'];
    }

    private function getStandardHeader()
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken()
        ];
    }

    public function getAUthUrl()
    {
        return 'https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&client_id=' . $this->clientId . '&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&response_type=code&scope=https://www.googleapis.com/auth/calendar.events+https://www.googleapis.com/auth/calendar.readonly';
    }

    private function refreshToken($tokens)
    {
        $args = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $tokens['refresh_token'],
            'grant_type'    => 'refresh_token'
        ];

        return $this->makeRequest('https://accounts.google.com/o/oauth2/token', $args, 'POST');
    }

    public function addViewConfig($config)
    {
        $settings = $this->getSettings();
        $config['gCalendarData'] = [
            'authUrl'    => $this->getAUthUrl(),
            'status'     => ArrayHelper::get($settings, 'status'),
        ];
        return $config;
    }

    public function addEvent($bookingEntryId, $insertId, $status)
    {
        $bookingData = (new BookingModel())->getBooking(['entry_id' => $insertId]);
        $this->providerId = ArrayHelper::get($bookingData, 'assigned_user');
        if (!$this->isActive()) {
            return;
        }
        $eventData = $this->getEventData($bookingData);

        if ($eventId = ArrayHelper::get($bookingData, 'addon_data.google_calendar.event_id')) {
            $resp = $this->pushEvent($eventData, 'PUT', $eventId);
            if (!is_wp_error($resp)) {
                do_action('ff_log_data', [
                    'parent_source_id' => $eventData['form_id'],
                    'source_type'      => 'submission_item',
                    'source_id'        => $insertId,
                    'component'        => 'ff_booking',
                    'status'           => 'success',
                    'title'            => 'FF Simple Booking - Google Calendar ',
                    'description'      => 'Event has been Updated in Google calendar'
                ]);
            }
            return;
        }

        $resp = $this->pushEvent($eventData, 'POST', '');

        if (!is_wp_error($resp)) {
            $googleData['addon_data'] = json_encode([
                'google_calendar' => [
                    'event_id'   => ArrayHelper::get($resp, 'id'),
                    'event_link' => ArrayHelper::get($resp, 'htmlLink'),
                ]
            ]);
            (new BookingModel())->update($bookingEntryId, $googleData);
            do_action('ff_log_data', [
                'parent_source_id' => $eventData['form_id'],
                'source_type'      => 'submission_item',
                'source_id'        => $insertId,
                'component'        => 'booking',
                'status'           => 'success',
                'title'            => 'FF Simple Booking - Google Calendar',
                'description'      => 'Event has been pushed to Google calendar'
            ]);
        }
    }

    public function pushEvent($data, $reqType, $eventId)
    {
        $eventArgs = array(
            'summary'     => $data['summary'],
            'location'    => $data['location'],
            'description' => $data['description'],
//            'status'      => $data['status'],
            'attendees'   => array(
                array('email' => $data['email']),
            ),
        );

        // full day event
        if ($data['booking_type'] == 'date_slot') {
            $eventArgs['start'] = array(
                'date'     => $data['date'],
                'timeZone' => $data['timeZone'],
            );
            $eventArgs['end'] = array(
                'date'     => $data['date'],
                'timeZone' => $data['timeZone'],
            );
        } else {
            //time event
            $eventArgs['start'] = array(
                'dateTime' => $data['startDateTime'],
                'timeZone' => $data['timeZone'],
            );
            $eventArgs['end'] = array(
                'dateTime' => $data['endDateTime'],
                'timeZone' => $data['timeZone'],
            );
        }


        $endPoint = 'calendars/primary/events/' . $eventId;
        return $this->makeRequest($this->apiUrl . $endPoint, $eventArgs, $reqType, $this->getStandardHeader());
    }

    public function saveSettings($settings)
    {
        if (empty($settings['access_code'])) {
            $integrationSettings = [
                'access_code' => '',
                'status'      => false
            ];
            // Update the reCaptcha details with siteKey & secretKey.
            update_user_meta($this->providerId, $this->optionKey, $integrationSettings);
            wp_send_json_success([
                'message' => __('Your settings has been updated', FF_BOOKING_SLUG),
                'status'  => false
            ], 200);
        }

        // Verify API key now
        try {
            $accessCode = sanitize_textarea_field($settings['access_code']);
            $result = $this->generateAccessKey($accessCode);

            if (is_wp_error($result)) {
                throw new \Exception($result->get_error_message());
            }

            $result['access_code'] = $accessCode;
            $result['created_at'] = time();
            $result['status'] = true;
            update_user_meta($this->providerId, $this->optionKey, $result);
        } catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 400);
        }

        wp_send_json_success([
            'message' => __('Your Google Calendar api key has been verified and successfully set', FF_BOOKING_SLUG),
            'status'  => true
        ], 200);
    }

    public function getSettings()
    {
        $settings = get_user_meta($this->providerId, $this->optionKey, true);
        if (!$settings) {
            $settings = [];
        }
        $defaults = [
            'access_code' => ''
        ];

        return wp_parse_args($settings, $defaults);
    }

    public function isActive($providerId = '')
    {
        $settings = $this->getSettings();
        return ArrayHelper::isTrue($settings, 'status');
    }

    /**
     * @param $bookingData
     * @return array
     * @throws \Exception
     */
    public function getEventData($bookingData)
    {
        $bookingDate = ArrayHelper::get($bookingData, 'booking_date');
        $bookingStartTime = ArrayHelper::get($bookingData, 'booking_time');
        $duration = ArrayHelper::get($bookingData, 'duration');
        $startDateTime = new \DateTime(
            $bookingDate . ' ' . $bookingStartTime,
            new \DateTimeZone(BookingHelper::getTimeZone())
        );

        $eventData = [
            'form_id'          => ArrayHelper::get($bookingData, 'form_id'),
            'booking_type'     => ArrayHelper::get($bookingData, 'booking_type'),
            'summary'          => ArrayHelper::get($bookingData, 'service'),
            'email'            => ArrayHelper::get($bookingData, 'email'),
            'date'             => ArrayHelper::get($bookingData, 'booking_date'),
            'location'         => ArrayHelper::get($bookingData, 'in_person_location'),
            'description'      => ArrayHelper::get($bookingData, 'description'),
            'startDateTime'    => $startDateTime->format(\Datetime::ATOM),
            'endDateTime'      => $startDateTime->modify(BookingHelper::timeDurationLength($duration))->format(\Datetime::ATOM),
            'timeZone'         => BookingHelper::getTimeZone(),
        ];
        if ($bookingData['booking_status'] == 'booked') {
            $eventData['status'] = 'confirmed';
        } elseif ($bookingData['booking_status'] == 'canceled' || $bookingData['booking_status'] == 'rejected') {
            $eventData['status'] = 'cancelled';
        } elseif ($bookingData['booking_status'] == 'pending') {
            $eventData['status'] = 'tentative';
        }
        return $eventData;
    }
}
