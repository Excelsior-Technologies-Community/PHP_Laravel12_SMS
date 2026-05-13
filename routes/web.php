<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;

Route::get('/', function () {
    return redirect('/sms');
});

Route::get('/sms', [SmsController::class, 'index']);

Route::post('/sms/send', [SmsController::class, 'sendSms']);

Route::delete('/sms/{id}', [SmsController::class, 'destroy']);