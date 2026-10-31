<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CalendarEvent extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_at',
        'end_at',
        'location',
        'is_official',
        'event_color',
        'repeat_interval',
        'repeat_group_id',
        'media',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_official' => 'boolean',
        'repeat_interval' => 'string',
        'media' => 'array',
    ];

    public function mediaUrls(): array
    {
        return array_values(array_filter(array_map(function ($item) {
            return $this->normalizeMediaUrl($item);
        }, $this->media ?? [])));
    }

    public function mediaGallery(): array
    {
        return array_values(array_filter(array_map(function ($item) {
            $url = $this->normalizeMediaUrl($item);
            if (empty($url)) {
                return null;
            }

            $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
            $type = in_array($extension, ['mp4', 'mov', 'avi', 'mkv'], true) ? 'video' : 'image';

            return [
                'url' => $url,
                'type' => $type,
                'original' => $item,
            ];
        }, $this->media ?? [])));
    }

    private function normalizeMediaUrl(?string $item): ?string
    {
        if (empty($item)) {
            return null;
        }

        if (str_starts_with($item, 'http')) {
            return $item;
        }

        if (str_starts_with($item, '/storage/')) {
            return $item;
        }

        if (str_starts_with($item, 'storage/')) {
            return '/' . $item;
        }

        return Storage::url(ltrim($item, '/'));
    }
}
