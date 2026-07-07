<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SmsHistory;
use App\Services\SmsGatewayManager;

class ProcessBulkSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $smsData;
    protected $templateId;

    public function __construct(array $smsData, $templateId = null)
    {
        $this->smsData = $smsData;
        $this->templateId = $templateId;
    }

    public function handle(SmsGatewayManager $smsManager)
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($this->smsData as $data) {
            $number = $data['number'];
            $message = $data['message'];

            // Apply template if exists
            if ($this->templateId) {
                $template = \App\Models\SmsTemplate::find($this->templateId);
                if ($template) {
                    $message = $template->renderContent($data['placeholders'] ?? []);
                }
            }

            // Create history entry
            $history = SmsHistory::create([
                'number' => $number,
                'message' => $message,
                'status' => 'pending'
            ]);

            // Send SMS
            $result = $smsManager->send($number, $message, $history->id);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        // Log results
        \Illuminate\Support\Facades\Log::info("Bulk SMS completed", [
            'total' => count($this->smsData),
            'success' => $successCount,
            'failed' => $failCount
        ]);
    }
}