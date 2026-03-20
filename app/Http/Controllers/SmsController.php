<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tzsk\Sms\Facades\Sms;

class SmsController extends Controller
{
    public function sendSms(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required',
        ]);

        // Ensure number has country code
        $number = $request->number;

        if (!str_starts_with($number, '+')) {
            $number = '+91' . $number; // default India
        }

        try {
            Sms::via('twilio')->send($request->message, function ($sms) use ($number) {
                $sms->to($number);
            });

            return back()->with('success', '✅ SMS sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}



// public function sendSms(Request $request)
    // {
    //     $request->validate([
    //         'number' => 'required',
    //         'message' => 'required',
    //     ]);

    //     // ✅ For testing (log)
    //     Log::info('SMS TEST', [
    //         'to' => $request->number,
    //         'message' => $request->message,
    //     ]);

    //     // ✅ Correct REAL SMS syntax (if you enable later)
    //     /*
    //     Sms::send($request->message, function($sms) use ($request) {
    //         $sms->to($request->number);
    //     });
    //     */

    //     return back()->with('success', 'SMS logged successfully (not sent)');
    // }