<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'booking_id',
        'method',
        'reference',
        'amount_tzs',
        'paid_at',
        'verified_by',
        'verified_at',
        'status',
        'receipt_file',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that made this payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booking this payment is for.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the receipt for this payment.
     */
    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}
