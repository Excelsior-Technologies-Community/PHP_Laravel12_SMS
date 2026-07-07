<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsHistory;
use Illuminate\Support\Facades\Log;

class SmsWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Get the message ID and status from provider webhook
        $messageId = $request->input('MessageSid') ?? $request->input('message_id');
        $status = $request->input('MessageStatus') ?? $request->input('status');

        if (!$messageId || !$status) {
            Log::warning('Webhook received without message_id or status', $request->all());
            return response()->json(['error' => 'Invalid webhook data'], 400);
        }

        // Find the SMS history
        $history = SmsHistory::where('message_id', $messageId)->first();

        if (!$history) {
            Log::warning('Webhook received for unknown message_id', ['message_id' => $messageId]);
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Update status based on webhook
        $statusMap = [
            'sent' => 'sent',
            'delivered' => 'delivered',
            'failed' => 'failed',
            'undelivered' => 'failed',
            'accepted' => 'sent',
            'queued' => 'pending',
        ];

        $newStatus = $statusMap[$status] ?? $history->status;

        $history->status = $newStatus;

        if ($newStatus == 'delivered') {
            $history->delivered_at = now();
        } elseif ($newStatus == 'failed') {
            $history->failed_at = now();
            $history->error_message = $request->input('ErrorMessage') ?? 'Delivery failed';
        }

        $history->save();

        Log::info('Webhook processed', [
            'message_id' => $messageId,
            'old_status' => $history->getOriginal('status'),
            'new_status' => $newStatus
        ]);

        return response()->json(['success' => true]);
    }
}