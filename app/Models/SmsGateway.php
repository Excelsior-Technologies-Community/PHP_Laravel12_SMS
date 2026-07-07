<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $fillable = [
        'name',
        'provider_class',
        'credentials',
        'priority',
        'is_active',
        'is_fallback'
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
        'is_fallback' => 'boolean'
    ];

    // Get active gateways ordered by priority
    public static function getActiveGateways()
    {
        return self::where('is_active', true)
            ->orderBy('priority')
            ->get();
    }

    // Get primary gateway
    public static function getPrimary()
    {
        return self::where('is_active', true)
            ->where('is_fallback', false)
            ->orderBy('priority')
            ->first();
    }

    // Get fallback gateways
    public static function getFallbacks()
    {
        return self::where('is_active', true)
            ->where('is_fallback', true)
            ->orderBy('priority')
            ->get();
    }
}