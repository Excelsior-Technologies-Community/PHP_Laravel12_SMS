<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tzsk\Sms\Facades\Sms;
use App\Models\SmsHistory;

class SmsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $messages = SmsHistory::when($search, function ($query) use ($search) {
            $query->where('number', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
        })
        ->oldest()
        ->paginate(2);

        return view('sms', compact('messages'));
    }

    public function sendSms(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required|max:160',
        ]);

        $number = $request->number;

        if (!str_starts_with($number, '+')) {
            $number = '+91' . $number;
        }

        try {

            Sms::via('twilio')->send($request->message, function ($sms) use ($number) {
                $sms->to($number);
            });

            SmsHistory::create([
                'number' => $number,
                'message' => $request->message,
                'status' => 'Sent',
            ]);

            return back()->with('success', '✅ SMS sent successfully!');

        } catch (\Exception $e) {

            SmsHistory::create([
                'number' => $number,
                'message' => $request->message,
                'status' => 'Failed',
            ]);

            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        SmsHistory::findOrFail($id)->delete();

        return back()->with('success', '🗑 SMS deleted successfully!');
    }
}