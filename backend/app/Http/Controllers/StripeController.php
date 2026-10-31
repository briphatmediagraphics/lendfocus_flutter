<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class StripeController extends Controller
{
    public function createCheckout(\Illuminate\Http\Request $request, Booking $booking)
    {
        // Ensure booking exists and not already paid
        if ($booking->status === 'paid') {
            return redirect()->back()->with('error', 'Booking already paid');
        }

        $stripeSecret = env('STRIPE_SECRET');
        $currency = env('STRIPE_CURRENCY', 'usd');

        if (! $stripeSecret) {
            return redirect()->back()->with('error', 'Stripe is not configured. Please set STRIPE_SECRET in .env');
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        $amount = max(0, (int) ($booking->price_tzs ?? 0));

        // Convert amount to smallest currency unit for Stripe
        // If currency has cents, multiply by 100; for integer currencies adjust as needed.
        $amountInCents = (int) round($amount * (float) env('STRIPE_AMOUNT_MULTIPLIER', 1));

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => ['name' => 'Booking ' . ($booking->control_number ?? $booking->id)],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => URL::route('buyer.payment.receipt', ['paymentId' => '{CHECKOUT_SESSION_ID}']),
                'cancel_url' => url()->previous(),
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);

            // Return redirect for normal requests, JSON for AJAX/JS clients
            if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json(['success' => true, 'url' => $session->url]);
            }

            return redirect($session->url);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout creation failed: ' . $e->getMessage());
            if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => 'Failed to create Stripe checkout session.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to create Stripe checkout session.');
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            if ($webhookSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                $event = json_decode($payload);
            }
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // Handle the checkout.session.completed event
        $type = is_object($event) ? ($event->type ?? null) : ($event->type ?? null);

        if ($type === 'checkout.session.completed') {
            $session = is_object($event) ? $event->data->object : $event->data->object;
            $bookingId = $session->metadata->booking_id ?? null;
            $amountTotal = $session->amount_total ?? null;

            if ($bookingId) {
                $booking = Booking::find($bookingId);
                if ($booking) {
                    // record payment
                    $payment = Payment::create([
                        'user_id' => $booking->user_id,
                        'booking_id' => $booking->id,
                        'method' => 'stripe',
                        'reference' => $session->id,
                        'amount_tzs' => $booking->price_tzs,
                        'paid_at' => now(),
                        'status' => 'verified',
                    ]);

                    // mark booking as paid
                    $booking->update(['status' => 'paid']);
                }
            }
        }

        return response('OK', 200);
    }
}
