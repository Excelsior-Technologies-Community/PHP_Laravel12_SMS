<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsHistory;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsGatewayManager
{
    protected $gateway;
    protected $fallbackGateways = [];

    public function __construct()
    {
        $this->loadGateways();
    }

    protected function loadGateways()
    {
        try {
            // Get primary gateway from database
            $primary = SmsGateway::getPrimary();
            
            if ($primary) {
                $this->gateway = $this->instantiateGateway($primary);
                Log::info('Primary gateway loaded', [
                    'name' => $primary->name,
                    'provider' => $primary->provider_class
                ]);
            } else {
                // If no gateway in DB, try to use Twilio directly
                Log::warning('No gateway found in database, trying Twilio directly');
                $this->gateway = new \App\Services\Gateways\TwilioGateway();
            }

            // Get fallback gateways
            $fallbacks = SmsGateway::getFallbacks();
            foreach ($fallbacks as $fallback) {
                $gateway = $this->instantiateGateway($fallback);
                if ($gateway) {
                    $this->fallbackGateways[] = $gateway;
                    Log::info('Fallback gateway loaded', [
                        'name' => $fallback->name
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to load gateways: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function instantiateGateway($gatewayModel)
    {
        try {
            $class = $gatewayModel->provider_class;
            if (class_exists($class)) {
                return new $class($gatewayModel->credentials);
            }
            return null;
        } catch (Exception $e) {
            Log::error('Failed to instantiate gateway: ' . $gatewayModel->name, [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function send($to, $message, $historyId = null)
    {
        $attempts = 0;
        
        // Build gateway list
        $gateways = [];
        if ($this->gateway) {
            $gateways[] = $this->gateway;
        }
        foreach ($this->fallbackGateways as $fallback) {
            $gateways[] = $fallback;
        }

        // If no gateways available, throw exception
        if (empty($gateways)) {
            throw new Exception('No SMS gateways available. Please configure at least one gateway.');
        }

        foreach ($gateways as $gateway) {
            if (!$gateway) continue;

            $attempts++;
            try {
                Log::info('Attempting to send SMS via gateway', [
                    'gateway' => get_class($gateway),
                    'attempt' => $attempts,
                    'to' => $to
                ]);

                $result = $gateway->send($to, $message);
                
                // Update history if exists
                if ($historyId) {
                    $history = SmsHistory::find($historyId);
                    if ($history) {
                        $history->status = 'sent';
                        $history->gateway = get_class($gateway);
                        $history->message_id = $result['message_id'] ?? null;
                        $history->sent_at = now();
                        $history->save();
                    }
                }

                return [
                    'success' => true,
                    'gateway' => get_class($gateway),
                    'message_id' => $result['message_id'] ?? null,
                    'attempt' => $attempts
                ];

            } catch (Exception $e) {
                Log::warning("SMS gateway failed: " . get_class($gateway), [
                    'error' => $e->getMessage(),
                    'attempt' => $attempts,
                    'to' => $to
                ]);

                // Update history with error
                if ($historyId) {
                    $history = SmsHistory::find($historyId);
                    if ($history) {
                        $history->error_message = $e->getMessage();
                        $history->retry_count = $attempts;
                        $history->save();
                    }
                }

                continue;
            }
        }

        // All gateways failed
        if ($historyId) {
            $history = SmsHistory::find($historyId);
            if ($history) {
                $history->status = 'failed';
                $history->failed_at = now();
                $history->save();
            }
        }

        return [
            'success' => false,
            'error' => 'All gateways failed after ' . $attempts . ' attempts',
            'attempt' => $attempts
        ];
    }
}