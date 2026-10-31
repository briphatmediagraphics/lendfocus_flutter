<?php

namespace App\Support;

use Illuminate\Support\Collection;

class Land
{
    public const STATUS_META = [
        'open' => [
            'sw' => 'Wazi',
            'en' => 'Open',
            'dot' => 'bg-open',
            'chip' => 'bg-open/15 text-open border-open/30',
            'cell' => 'bg-open text-open-foreground border-open hover:brightness-110',
        ],
        'booked' => [
            'sw' => 'Imebookiwa',
            'en' => 'Booked',
            'dot' => 'bg-booked',
            'chip' => 'bg-booked/20 text-booked-foreground border-booked/40',
            'cell' => 'bg-booked text-booked-foreground border-booked hover:brightness-105',
        ],
        'paid' => [
            'sw' => 'Imelipwa',
            'en' => 'Paid',
            'dot' => 'bg-paid',
            'chip' => 'bg-paid/15 text-paid border-paid/30',
            'cell' => 'bg-paid text-paid-foreground border-paid hover:brightness-110',
        ],
    ];

    public static function normaliseStatus(?string $status): string
    {
        return match ($status) {
            'available', 'open' => 'open',
            'held', 'booked' => 'booked',
            'sold', 'paid' => 'paid',
            default => 'open',
        };
    }

    public static function statusMeta(?string $status): array
    {
        return self::STATUS_META[self::normaliseStatus($status)] ?? self::STATUS_META['open'];
    }

    public static function summarisePlots(iterable $plots): array
    {
        $stats = [
            'total' => 0,
            'open' => 0,
            'booked' => 0,
            'paid' => 0,
            'value' => 0,
        ];

        foreach ($plots as $plot) {
            $status = is_array($plot)
                ? self::normaliseStatus($plot['status'] ?? 'open')
                : self::normaliseStatus($plot->status ?? 'open');

            $price = is_array($plot)
                ? (float) ($plot['price'] ?? $plot['price_tzs'] ?? 0)
                : (float) ($plot->price_tzs ?? $plot->price ?? 0);

            $stats['total']++;
            $stats[$status]++;
            $stats['value'] += $price;
        }

        return $stats;
    }

    public static function summariseCollection(Collection $plots): array
    {
        return self::summarisePlots($plots);
    }

    public static function friendlyError(?string $message): string
    {
        if (! $message) {
            return 'Hitilafu imetokea. Jaribu tena.';
        }

        if (str_contains($message, 'PLOT_TAKEN')) {
            return 'Kiwanja hiki tayari kimechukuliwa na mtu mwingine. Onyesha upya ukurasa.';
        }

        if (str_contains($message, 'FORBIDDEN')) {
            return 'Huna ruhusa ya kufanya kitendo hiki.';
        }

        if (str_contains($message, 'NOT_FOUND')) {
            return 'Kiwanja hakikupatikana.';
        }

        if (str_contains($message, 'duplicate key')) {
            return 'Namba ya kiwanja tayari ipo kwenye mradi huu.';
        }

        return $message;
    }
}
