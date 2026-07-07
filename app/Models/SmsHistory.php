<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsHistory extends Model
{
    protected $fillable = [
        'number',
        'message',
        'status',
        'gateway',
        'message_id',
        'error_message',
        'retry_count',
        'sent_at',
        'delivered_at',
        'failed_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-500/20 text-yellow-300 border border-yellow-400',
            'sent' => 'bg-blue-500/20 text-blue-300 border border-blue-400',
            'delivered' => 'bg-green-500/20 text-green-300 border border-green-400',
            'failed' => 'bg-red-500/20 text-red-300 border border-red-400',
        ];

        return $badges[$this->status] ?? 'bg-gray-500/20 text-gray-300';
    }

    public function getStatusIconAttribute()
    {
        $icons = [
            'pending' => '⏳',
            'sent' => '📤',
            'delivered' => '✅',
            'failed' => '❌',
        ];

        return $icons[$this->status] ?? '📨';
    }
}