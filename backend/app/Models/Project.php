<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Plot;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'title',
        'slug',
        'type',
        'location',
        'region',
        'district',
        'cover_path',
        'cover_image',
        'description',
        'geojson',
        'status',
        'base_price_tzs',
        'total_plots',
        'map_layout_type',
        'created_by',
    ];

    protected $casts = [
        'geojson' => 'array',
    ];

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    public static function boot(): void
    {
        parent::boot();

        static::created(function ($project) {
            $total = (int) ($project->total_plots ?? 0);
            if ($total <= 0) {
                return;
            }

            // simple grid layout: compute columns as ceil(sqrt(total))
            $cols = (int) ceil(sqrt($total));

            for ($i = 1; $i <= $total; $i++) {
                $number = (string) $i;
                $x = (($i - 1) % $cols) + 1;
                $y = (int) floor(($i - 1) / $cols) + 1;

                $project->plots()->create([
                    'number' => $number,
                    'size_sqm' => 0,
                    'price_tzs' => 0,
                    'status' => 'open',
                    'position_x' => $x,
                    'position_y' => $y,
                ]);
            }
        });
    }
}
