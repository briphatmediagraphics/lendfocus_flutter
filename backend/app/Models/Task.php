<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'assigned_to',
        'worker_id',
        'completed',
        'completed_at',
        'approved',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
