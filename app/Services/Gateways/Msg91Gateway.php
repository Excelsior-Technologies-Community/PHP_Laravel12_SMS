<?php

namespace App\Services\Gateways;

use Illuminate\Support\Facades\Http;

class Msg91Gateway
{
    protected $apiKey;
    protected $senderId;
    protected $route;

    public function __construct($credentials)
    {
        $this->apiKey = $credentials['api_key'];
        $this->senderId = $credentials['sender_id'];
        $this->route = $credentials['route'] ?? '4';
    }

    public function send($to, $message)
    {
        $response = Http::post('https://api.msg91.com/api/v5/flow/', [
            'sender' => $this->senderId,
            'mobiles' => $to,
            'authkey' => $this->apiKey,
            'message' => $message,
            'route' => $this->route,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['type']) && $data['type'] === 'success') {
            return [
                'success' => true,
                'message_id' => $data['messageId'] ?? null
            ];
        }

        throw new \Exception('MSG91 Error: ' . ($data['message'] ?? 'Unknown error'));
    }
}