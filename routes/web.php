<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\SmsWebhookController;

// Main Routes
Route::get('/', function () { return redirect('/sms'); });
Route::get('/sms', [SmsController::class, 'index'])->name('sms.dashboard');
Route::post('/sms/send', [SmsController::class, 'sendSms'])->name('sms.send');
Route::get('/sms/retry/{id}', [SmsController::class, 'retry'])->name('sms.retry');
Route::delete('/sms/{id}', [SmsController::class, 'destroy'])->name('sms.delete');
Route::get('/sms/export', [SmsController::class, 'export'])->name('sms.export');

// Bulk SMS
Route::get('/sms/bulk', [SmsController::class, 'bulk'])->name('sms.bulk');
Route::post('/sms/bulk', [SmsController::class, 'bulkUpload'])->name('sms.bulk.upload');

// Template Routes
Route::get('/sms/templates', [SmsTemplateController::class, 'index'])->name('sms.templates');
Route::post('/sms/templates', [SmsTemplateController::class, 'store'])->name('sms.templates.store');
Route::post('/sms/template/{id}', [SmsTemplateController::class, 'update'])->name('sms.templates.update');
Route::delete('/sms/template/{id}', [SmsTemplateController::class, 'destroy'])->name('sms.templates.delete');
Route::get('/sms/template/toggle/{id}', [SmsTemplateController::class, 'toggle'])->name('sms.templates.toggle');

// Webhook Routes (for delivery status)
Route::post('/sms/webhook/twilio', [SmsWebhookController::class, 'handleTwilio'])->name('sms.webhook.twilio');
Route::post('/sms/webhook/msg91', [SmsWebhookController::class, 'handleMsg91'])->name('sms.webhook.msg91');
Route::post('/sms/webhook', [SmsWebhookController::class, 'handle'])->name('sms.webhook');