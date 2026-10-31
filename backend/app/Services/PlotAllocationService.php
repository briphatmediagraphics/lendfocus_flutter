<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\Project;
use App\Support\Land;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlotAllocationService
{
    /** @var array<string, string> */
    public const PAYMENT_METHODS = [
        'Cash' => 'cash',
        'M-Pesa' => 'mpesa',
        'Tigo Pesa' => 'tigopesa',
        'Airtel Money' => 'airtelmoney',
        'Benki' => 'crdb',
        'Nyingine' => 'cash',
    ];

    public function bulkCreatePlots(Project $project, array $data): int
    {
        $prefix = trim($data['prefix'] ?? 'A');
        $start = max(1, (int) ($data['start'] ?? 1));
        $count = min(500, max(1, (int) ($data['count'] ?? 1)));
        $price = max(0, (int) ($data['price_tzs'] ?? 0));
        $size = $data['size'] ?? null;
        $gridColumns = max(1, (int) ($project->grid_columns ?? 10));
        $existing = $project->plots()->count();

        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $index = $existing + $i;
            $rows[] = [
                'id' => (string) Str::uuid(),
                'project_id' => $project->id,
                'number' => $prefix . ($start + $i),
                'size_sqm' => is_numeric($size) ? (int) $size : 0,
                'price_tzs' => $price,
                'status' => 'open',
                'position_x' => ($index % $gridColumns) + 1,
                'position_y' => (int) floor($index / $gridColumns) + 1,
                'notes' => is_string($size) && ! is_numeric($size) ? $size : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Plot::insert($rows);

        return $count;
    }

    public function allocate(Plot $plot, array $data): Booking
    {
        $status = Land::normaliseStatus($plot->status);
        if ($status !== 'open') {
            throw new \RuntimeException('PLOT_TAKEN');
        }

        return DB::transaction(function () use ($plot, $data) {
            $plot->refresh();
            if (Land::normaliseStatus($plot->status) !== 'open') {
                throw new \RuntimeException('PLOT_TAKEN');
            }

            $price = (int) ($plot->price_tzs ?? 0);
            $newStatus = ($data['new_status'] ?? 'booked') === 'paid' ? 'paid' : 'booked';

            $booking = Booking::create([
                'plot_id' => $plot->id,
                'customer_name' => trim($data['full_name']),
                'customer_phone' => trim($data['phone']),
                'customer_id_no' => trim($data['national_id'] ?? '') ?: '-',
                'control_number' => $this->generateControlNumber(),
                'price_tzs' => $price,
                'status' => 'confirmed',
                'notes' => trim($data['address'] ?? '') ?: null,
            ]);

            $amount = (int) ($data['amount'] ?? 0);
            if ($amount > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'method' => $this->mapPaymentMethod($data['method'] ?? 'Cash'),
                    'amount_tzs' => $amount,
                    'paid_at' => $data['payment_date'] ?? now(),
                    'status' => 'approved',
                    'verified_at' => now(),
                ]);
            }

            $totalPaid = $amount;
            $plotStatus = ($newStatus === 'paid' || ($price > 0 && $totalPaid >= $price)) ? 'paid' : 'booked';
            $plot->update(['status' => $plotStatus]);

            return $booking->load('payments');
        });
    }

    public function recordPayment(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'method' => $this->mapPaymentMethod($data['method'] ?? 'Cash'),
                'reference' => $data['reference'] ?? null,
                'amount_tzs' => (int) $data['amount'],
                'paid_at' => $data['payment_date'] ?? now(),
                'status' => 'approved',
                'verified_at' => now(),
            ]);

            $plot = $booking->plot;
            if ($plot) {
                $totalPaid = (int) $booking->payments()->where('status', 'approved')->sum('amount_tzs');
                $target = (int) ($booking->price_tzs ?? $plot->price_tzs ?? 0);
                if ($target > 0 && $totalPaid >= $target) {
                    $plot->update(['status' => 'paid']);
                } elseif (Land::normaliseStatus($plot->status) === 'open') {
                    $plot->update(['status' => 'booked']);
                }
            }

            return $payment;
        });
    }

    public function release(Plot $plot): void
    {
        DB::transaction(function () use ($plot) {
            foreach ($plot->bookings as $booking) {
                $booking->payments()->delete();
                $booking->delete();
            }
            $plot->update(['status' => 'open']);
        });
    }

    protected function generateControlNumber(): string
    {
        do {
            $number = str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (Booking::where('control_number', $number)->exists());

        return $number;
    }

    protected function mapPaymentMethod(string $label): string
    {
        return self::PAYMENT_METHODS[$label] ?? 'cash';
    }
}
