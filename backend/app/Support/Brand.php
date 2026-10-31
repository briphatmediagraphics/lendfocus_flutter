<?php

namespace App\Support;

class Brand
{
    public static function all(): array
    {
        return config('landfocus.brand', []);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return config("landfocus.brand.{$key}", $default);
    }

    public static function formatTzs(int|float|null $value = 0): string
    {
        return 'TSh ' . number_format((float) ($value ?? 0), 0, '.', ',');
    }

    public static function formatDate(?string $value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return '-';
        }
    }
}
