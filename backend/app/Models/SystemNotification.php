<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $table = 'system_notifications';

    protected $fillable = [
        'title',
        'body',
        'level',
        'is_active',
        'channel',
        'recipients',
        'sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'recipients' => 'array',
        'sent_at' => 'datetime',
    ];
}
