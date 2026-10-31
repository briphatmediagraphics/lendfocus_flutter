<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReleaseHeldBookings extends Command
{
    protected $signature = 'bookings:release-held';

    protected $description = 'Release bookings that were held and expired (held_until passed)';

    public function handle(): int
    {
        $now = Carbon::now();

        $held = Booking::where('status', 'held')
            ->whereNotNull('held_until')
            ->where('held_until', '<', $now)
            ->get();

        $this->info('Found ' . $held->count() . ' held bookings to release');

        foreach ($held as $booking) {
            try {
                $plot = $booking->plot;

                $booking->status = 'released';
                $booking->save();

                // If plot exists and there are no other active bookings that keep it held/booked/paid, mark open
                if ($plot) {
                    $other = $plot->bookings()->whereIn('status', ['held', 'booked', 'paid'])->where('id', '!=', $booking->id)->exists();
                    if (! $other) {
                        $plot->status = 'open';
                        $plot->save();
                    }
                }

                // Create in-app notifications: inform the customer and admins
                try {
                    // notify customer
                    if ($booking->user_id) {
                        \App\Models\SystemNotification::create([
                            'title' => 'Booking released',
                            'body' => 'Your reservation ' . ($booking->control_number ?? $booking->id) . ' has been released because it was not completed in time.',
                            'level' => 'info',
                            'is_active' => true,
                            'channel' => 'in_app',
                            'recipients' => [$booking->user?->email ?? null],
                            'sent_at' => now(),
                        ]);
                    }

                    // notify admins
                    \App\Models\SystemNotification::create([
                        'title' => 'Booking released',
                        'body' => 'Booking ' . ($booking->control_number ?? $booking->id) . ' was released automatically.',
                        'level' => 'warning',
                        'is_active' => true,
                        'channel' => 'admin',
                        'recipients' => [],
                        'sent_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // ignore notification failures
                }

                $this->info('Released booking ' . $booking->id);
            } catch (\Throwable $e) {
                $this->error('Failed to release booking ' . $booking->id . ': ' . $e->getMessage());
            }
        }

        return 0;
    }
}
