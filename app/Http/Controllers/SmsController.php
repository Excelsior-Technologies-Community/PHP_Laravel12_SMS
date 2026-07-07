<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsHistory;
use App\Models\SmsTemplate;
use App\Models\SmsGateway;
use App\Services\SmsGatewayManager;
use App\Jobs\ProcessBulkSmsJob;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsController extends Controller
{
    protected $smsManager;

    public function __construct(SmsGatewayManager $smsManager)
    {
        $this->smsManager = $smsManager;
    }

    // Dashboard
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $query = SmsHistory::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($status && $status != 'all') {
            $query->where('status', $status);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total' => SmsHistory::count(),
            'sent' => SmsHistory::where('status', 'sent')->count(),
            'delivered' => SmsHistory::where('status', 'delivered')->count(),
            'failed' => SmsHistory::where('status', 'failed')->count(),
            'pending' => SmsHistory::where('status', 'pending')->count(),
        ];

        $templates = SmsTemplate::where('is_active', true)->get();

        return view('sms', compact('messages', 'stats', 'templates', 'search', 'status'));
    }

    // Bulk SMS page
    public function bulk(Request $request)
    {
        $templates = SmsTemplate::where('is_active', true)->get();
        return view('sms-bulk', compact('templates'));
    }

    // Send single SMS
    public function sendSms(Request $request)
    {
        $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:160',
            'template_id' => 'nullable|exists:sms_templates,id',
            'placeholders' => 'nullable|array'
        ]);

        $number = $request->number;
        $message = $request->message;

        // Format number
        if (!str_starts_with($number, '+')) {
            $number = '+91' . $number;
        }

        // Apply template if selected
        if ($request->template_id) {
            $template = SmsTemplate::find($request->template_id);
            if ($template) {
                $message = $template->renderContent($request->placeholders ?? []);
            }
        }

        // Create history
        $history = SmsHistory::create([
            'number' => $number,
            'message' => $message,
            'status' => 'pending'
        ]);

        try {
            // Send via gateway manager (with failover)
            $result = $this->smsManager->send($number, $message, $history->id);

            if ($result['success']) {
                $history->status = 'sent';
                $history->gateway = $result['gateway'];
                $history->message_id = $result['message_id'] ?? null;
                $history->sent_at = now();
                $history->save();

                return back()->with('success', '✅ SMS sent successfully via ' . $result['gateway']);
            } else {
                $history->status = 'failed';
                $history->failed_at = now();
                $history->error_message = $result['error'] ?? 'Unknown error';
                $history->save();

                return back()->with('error', '❌ Failed to send SMS: ' . ($result['error'] ?? 'Unknown error'));
            }

        } catch (Exception $e) {
            $history->status = 'failed';
            $history->failed_at = now();
            $history->error_message = $e->getMessage();
            $history->save();

            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    // Bulk Upload
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls,txt',
            'template_id' => 'nullable|exists:sms_templates,id'
        ]);

        $file = $request->file('file');
        $templateId = $request->template_id;

        // Parse CSV/Excel
        $smsData = $this->parseSmsFile($file);

        if (empty($smsData)) {
            return back()->with('error', '❌ No valid data found in file! Please check the format.');
        }

        // Dispatch job for processing
        ProcessBulkSmsJob::dispatch($smsData, $templateId);

        return back()->with('success', '✅ ' . count($smsData) . ' SMS added to queue! They will be processed in the background.');
    }

   protected function parseSmsFile($file)
{
    $data = [];
    $extension = $file->getClientOriginalExtension();

    if ($extension == 'csv' || $extension == 'txt') {
        $handle = fopen($file->getPathname(), 'r');
        
        // Read headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [];
        }

        // Normalize headers
        $headers = array_map('strtolower', $headers);
        $headers = array_map('trim', $headers);

        // ✅ Find number and message columns
        $numberCol = null;
        $messageCol = null;
        
        foreach ($headers as $index => $header) {
            // Number column - multiple variations
            if (in_array($header, ['number', 'phone', 'mobile', 'mobilenumber', 'phonenumber', 'contact', 'to', 'phone_number', 'mobile_number'])) {
                $numberCol = $index;
            }
            // Message column - multiple variations
            if (in_array($header, ['message', 'sms', 'text', 'body', 'content', 'msg', 'sms_text', 'message_text'])) {
                $messageCol = $index;
            }
        }

        // ❌ If columns not found, show error
        if ($numberCol === null || $messageCol === null) {
            fclose($handle);
            // Log error
            \Illuminate\Support\Facades\Log::error('CSV columns not found', [
                'headers' => $headers,
                'number_col' => $numberCol,
                'message_col' => $messageCol
            ]);
            return [];
        }

        // Parse rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) > max($numberCol, $messageCol)) {
                $number = trim($row[$numberCol]);
                $message = trim($row[$messageCol]);
                
                // ✅ Skip empty rows
                if (!empty($number) && !empty($message)) {
                    // Format number - Add +91 if not present
                    if (!str_starts_with($number, '+')) {
                        // Remove any spaces or special chars
                        $number = preg_replace('/[^0-9]/', '', $number);
                        if (strlen($number) == 10) {
                            $number = '+91' . $number;
                        } elseif (strlen($number) == 12 && str_starts_with($number, '91')) {
                            $number = '+' . $number;
                        } else {
                            $number = '+' . $number;
                        }
                    }
                    
                    $data[] = [
                        'number' => $number,
                        'message' => $message,
                        'placeholders' => [
                            'number' => $number,
                            'message' => $message
                        ]
                    ];
                }
            }
        }
        fclose($handle);
    }

    return $data;
}

    // Delete SMS
    public function destroy($id)
    {
        SmsHistory::findOrFail($id)->delete();
        return back()->with('success', '🗑️ SMS deleted successfully!');
    }

    // Retry failed SMS
    public function retry($id)
    {
        $history = SmsHistory::findOrFail($id);

        if ($history->status != 'failed') {
            return back()->with('error', '❌ Only failed messages can be retried!');
        }

        if ($history->retry_count >= 3) {
            return back()->with('error', '❌ Maximum retry attempts (3) exceeded!');
        }

        $history->status = 'pending';
        $history->retry_count++;
        $history->save();

        try {
            $result = $this->smsManager->send($history->number, $history->message, $history->id);

            if ($result['success']) {
                $history->status = 'sent';
                $history->gateway = $result['gateway'];
                $history->message_id = $result['message_id'];
                $history->sent_at = now();
                $history->save();

                return back()->with('success', '✅ SMS retried and sent successfully!');
            } else {
                $history->status = 'failed';
                $history->failed_at = now();
                $history->error_message = $result['error'] ?? 'Unknown error';
                $history->save();

                return back()->with('error', '❌ Retry failed: ' . ($result['error'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            $history->status = 'failed';
            $history->failed_at = now();
            $history->error_message = $e->getMessage();
            $history->save();

            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    // Export
    public function export()
    {
        $messages = SmsHistory::all();
        $filename = 'sms_export_' . date('Y-m-d') . '.csv';

        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['ID', 'Number', 'Message', 'Status', 'Gateway', 'Created At', 'Sent At', 'Delivered At']);

        foreach ($messages as $msg) {
            fputcsv($handle, [
                $msg->id,
                $msg->number,
                $msg->message,
                $msg->status,
                $msg->gateway,
                $msg->created_at,
                $msg->sent_at,
                $msg->delivered_at
            ]);
        }

        fclose($handle);
        exit;
    }
}