<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MapBookingController extends Controller
{
    public function reserve(Request $request, Plot $plot)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
        ]);

        // Prevent reserving already booked/paid plots
        if (in_array($plot->status, ['booked', 'paid'], true)) {
            return response()->json(['success' => false, 'message' => 'Plot is not available'], 422);
        }

        $booking = Booking::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'plot_id' => $plot->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'price_tzs' => $plot->price_tzs,
            'status' => 'held',
            'control_number' => 'BK-' . Str::upper(Str::random(8)),
            'notes' => 'Reserved via map',
            // Hold reservation for 5 days by default
            'held_until' => now()->addDays(5),
        ]);

        // Mark plot as held so map shows correct status
        try {
            $plot->status = 'held';
            $plot->save();
        } catch (\Throwable $e) {
            // non-fatal
        }

        return response()->json(['success' => true, 'booking' => $booking]);
    }
}
