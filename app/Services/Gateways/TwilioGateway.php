<?php

namespace App\Services\Gateways;

use Twilio\Rest\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class TwilioGateway
{
    protected $client;
    protected $from;

    public function __construct($credentials = null)
    {
        // If credentials not passed, get from config
        if (!$credentials) {
            $credentials = [
                'account_sid' => config('sms-gateways.twilio.account_sid'),
                'auth_token' => config('sms-gateways.twilio.auth_token'),
                'from_number' => config('sms-gateways.twilio.from_number'),
            ];
        }

        // Log credentials for debugging
        Log::info('Twilio Credentials Check', [
            'account_sid' => $credentials['account_sid'] ? '✅ Set' : '❌ Missing',
            'auth_token' => $credentials['auth_token'] ? '✅ Set' : '❌ Missing',
            'from_number' => $credentials['from_number'] ? '✅ Set' : '❌ Missing',
        ]);

        // Check if credentials are set
        if (empty($credentials['account_sid']) || 
            empty($credentials['auth_token']) || 
            empty($credentials['from_number'])) {
            throw new Exception(
                'Twilio credentials are not properly configured. ' .
                'Please check your .env file for TWILIO_SID, TWILIO_TOKEN, and TWILIO_FROM.'
            );
        }

        try {
            $this->client = new Client(
                $credentials['account_sid'],
                $credentials['auth_token']
            );
            $this->from = $credentials['from_number'];
        } catch (Exception $e) {
            throw new Exception('Twilio Client Error: ' . $e->getMessage());
        }
    }

    public function send($to, $message)
    {
        try {
            Log::info('Sending SMS via Twilio', [
                'to' => $to,
                'from' => $this->from,
                'message_length' => strlen($message)
            ]);

            $response = $this->client->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'body' => $message
                ]
            );

            Log::info('Twilio Response', [
                'sid' => $response->sid,
                'status' => $response->status
            ]);

            return [
                'success' => true,
                'message_id' => $response->sid
            ];
        } catch (Exception $e) {
            Log::error('Twilio Error', [
                'error' => $e->getMessage(),
                'to' => $to
            ]);
            throw new Exception('Twilio Error: ' . $e->getMessage());
        }
    }
}