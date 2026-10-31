<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plot extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'project_id',
        'number',
        'plot_number',
        'size_sqm',
        'price_tzs',
        'status',
        'position_x',
        'position_y',
        'coordinates',
        'notes',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'position_x' => 'integer',
        'position_y' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
