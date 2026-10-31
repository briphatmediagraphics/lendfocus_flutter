<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Inquiry extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'project_id',
        'property_id',
        'name',
        'email',
        'phone',
        'message',
        'payment_option',
        'callback_requested',
        'status',
        'priority',
        'notes',
        'admin_notes',
        'last_replied_at',
        'assigned_to',
    ];

    protected $casts = [
        'callback_requested' => 'boolean',
        'last_replied_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ticket_number) {
                $model->ticket_number = 'TKT-' . strtoupper(Str::random(10));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(InquiryReply::class)->orderByDesc('created_at');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isProjectRequest(): bool
    {
        return !empty($this->project_id);
    }

    public function typeLabel(): string
    {
        return $this->isProjectRequest() ? 'Project Inquiry' : 'General Message';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'open' => 'bg-red-100 text-red-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}

