<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmsGateway;

class SmsGatewaySeeder extends Seeder
{
    public function run()
    {
        // Clear existing gateways
        SmsGateway::truncate();

        $gateways = [
            [
                'name' => 'Twilio',
                'provider_class' => 'App\Services\Gateways\TwilioGateway',
                'credentials' => [
                    'account_sid' => env('TWILIO_SID'),    // ✅ TWILIO_SID
                    'auth_token' => env('TWILIO_TOKEN'),   // ✅ TWILIO_TOKEN
                    'from_number' => env('TWILIO_FROM'),   // ✅ TWILIO_FROM
                ],
                'priority' => 1,
                'is_active' => true,
                'is_fallback' => false,
            ],
            [
                'name' => 'MSG91',
                'provider_class' => 'App\Services\Gateways\Msg91Gateway',
                'credentials' => [
                    'api_key' => env('MSG91_API_KEY'),
                    'sender_id' => env('MSG91_SENDER_ID'),
                    'route' => '4',
                ],
                'priority' => 2,
                'is_active' => false,
                'is_fallback' => true,
            ],
        ];

        foreach ($gateways as $gateway) {
            // Skip if Twilio credentials are missing
            if ($gateway['name'] == 'Twilio') {
                $creds = $gateway['credentials'];
                if (empty($creds['account_sid']) || empty($creds['auth_token']) || empty($creds['from_number'])) {
                    echo "⚠️  Twilio credentials missing in .env file!\n";
                    echo "   Please set: TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM\n";
                    continue;
                }
            }

            SmsGateway::create($gateway);
            echo "✅ Gateway '{$gateway['name']}' created successfully!\n";
        }

        echo "\n🎯 Gateway Seeder Completed!\n";
    }
}