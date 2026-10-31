<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    public $fillable = [
        'property_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getDownloadUrlAttribute(): string
    {
        return url('storage/' . $this->file_path);
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->file_type, ['image', 'jpg', 'png', 'gif', 'webp']);
    }
}
