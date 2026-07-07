<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = [
        'name',
        'category',
        'content',
        'placeholders',
        'is_active'
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean'
    ];

    public function renderContent(array $values = [])
    {
        $content = $this->content;

        foreach ($values as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        return $content;
    }

    public function getPlaceholderListAttribute()
    {
        return $this->placeholders ? array_keys($this->placeholders) : [];
    }
}