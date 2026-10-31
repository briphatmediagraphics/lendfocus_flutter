<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'plot_id',
        'customer_name',
        'customer_phone',
        'customer_id_no',
        'control_number',
        'price_tzs',
        'status',
        'held_until',
        'sales_officer_id',
        'notes',
    ];

    protected $casts = [
        'held_until' => 'datetime',
    ];

    protected $appends = [
        'total_paid',
        'remaining_balance',
    ];

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Get the user that made this booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booking's payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the booking's receipts.
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * Get total amount paid for this booking.
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'verified')->sum('amount_tzs') ?? 0;
    }

    /**
     * Get remaining balance for this booking.
     */
    public function getRemainingBalanceAttribute()
    {
        return ($this->price_tzs ?? 0) - $this->total_paid;
    }
}
