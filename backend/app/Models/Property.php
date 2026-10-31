<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'price',
        'currency',
        'location',
        'property_type',
        'status',
        'area_sqm',
        'bedrooms',
        'bathrooms',
        'images',
        'featured',
        'features',
        'documents',
        'document_notes',
    ];

    protected $casts = [
        'images' => 'array',
        'documents' => 'array',
        'featured' => 'boolean',
        'price' => 'integer',
    ];

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function documentFiles(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function coverImage(): string
    {
        $fallbacks = config('landfocus.fallback_images');
        $images = $this->images ?? [];

        if (! empty($images[0])) {
            $image = $images[0];
            if (str_starts_with($image, 'http')) {
                return $image;
            }

            if (str_starts_with($image, '/storage/')) {
                return $image;
            }

            if (str_starts_with($image, 'storage/')) {
                return '/' . $image;
            }

            return Storage::url(ltrim($image, '/'));
        }

        $fallback = $fallbacks[$this->property_type] ?? $fallbacks['house'];

        return asset(ltrim($fallback, '/'));
    }

    public function typeLabel(string $lang = 'sw'): string
    {
        return config("landfocus.property_types.{$this->property_type}.{$lang}")
            ?? $this->property_type;
    }

    public function imageUrls(): array
    {
        $images = $this->images ?? [];

        return array_map(function ($image) {
            if (empty($image)) {
                return null;
            }

            if (str_starts_with($image, 'http')) {
                return $image;
            }

            if (str_starts_with($image, '/storage/')) {
                return $image;
            }

            if (str_starts_with($image, 'storage/')) {
                return '/' . $image;
            }

            return Storage::url(ltrim($image, '/'));
        }, $images);
    }
}
