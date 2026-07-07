<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsTemplate;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $templates = SmsTemplate::all();
        return view('sms-templates', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'content' => 'required|string',
            'placeholders' => 'nullable|json'
        ]);

        SmsTemplate::create([
            'name' => $request->name,
            'category' => $request->category,
            'content' => $request->content,
            'placeholders' => json_decode($request->placeholders, true) ?: [],
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', '✅ Template created successfully!');
    }

    public function update(Request $request, $id)
    {
        $template = SmsTemplate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'content' => 'required|string',
            'placeholders' => 'nullable|json'
        ]);

        $template->update([
            'name' => $request->name,
            'category' => $request->category,
            'content' => $request->content,
            'placeholders' => json_decode($request->placeholders, true) ?: [],
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', '✅ Template updated successfully!');
    }

    public function destroy($id)
    {
        SmsTemplate::findOrFail($id)->delete();
        return back()->with('success', '🗑️ Template deleted!');
    }

    public function toggle($id)
    {
        $template = SmsTemplate::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        return back()->with('success', '✅ Template status updated!');
    }
}