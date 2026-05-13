<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsHistory extends Model
{
    protected $fillable = [
        'number',
        'message',
        'status',
    ];
}