<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sms', function () {
    return view('sms');
});
Route::post('/sms/send', [SmsController::class, 'sendSms']);

