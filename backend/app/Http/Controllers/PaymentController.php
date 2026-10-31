<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Show payment form for a booking
     */
    public function create(Booking $booking): View
    {
        // Only allow owner or admin to pay
        if (!$this->canAccessBooking($booking)) {
            abort(403);
        }

        $remainingBalance = max(0, $booking->price_tzs - ($booking->total_paid ?? 0));

        return view('payments.create', [
            'booking' => $booking,
            'remainingBalance' => $remainingBalance,
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }

    /**
     * Process payment
     */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        if (!$this->canAccessBooking($booking)) {
            abort(403);
        }

        $validated = $request->validate([
            'amount_tzs' => 'required|numeric|min:1',
            'method' => 'required|in:mpesa,stripe,payway,bank_transfer,cash',
            'reference' => 'required|string|max:100',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Create payment record
        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->storeAs(
                'receipts/' . $booking->id,
                uniqid('receipt_') . '.' . $request->file('receipt_file')->getClientOriginalExtension()
            );
        }

        $currentUser = Auth::user();

        $payment = Payment::create([
            'user_id' => $currentUser?->id ?? $booking->user_id,
            'booking_id' => $booking->id,
            'method' => $validated['method'],
            'reference' => $validated['reference'],
            'amount_tzs' => $validated['amount_tzs'],
            'paid_at' => $validated['paid_at'],
            'status' => 'pending', // Pending admin verification
            'receipt_file' => $receiptPath,
        ]);

        // Notify buyer (in-app) that payment was recorded
        SystemNotification::create([
            'title' => 'Payment recorded',
            'body' => 'Your payment of ' . number_format($payment->amount_tzs, 2) . ' TZS has been recorded and is pending verification.',
            'level' => 'info',
            'is_active' => true,
            'channel' => 'in_app',
            'recipients' => [$payment->user->email ?? $currentUser?->email ?? null],
            'sent_at' => now(),
        ]);

        // Notify admins (in-app global)
        SystemNotification::create([
            'title' => 'New payment pending verification',
            'body' => 'Payment ' . ($payment->reference ?? $payment->id) . ' for booking #' . $booking->id . ' requires verification.',
            'level' => 'warning',
            'is_active' => true,
            'channel' => 'admin',
            'recipients' => [],
            'sent_at' => now(),
        ]);
        if ($receiptPath) {

            Receipt::create([
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'storage_path' => $receiptPath,
                'uploaded_by' => Auth::id() ?? $payment->user_id,
            ]);
        }

        if (! $receiptPath) {
            $this->generateReceipt($payment, $booking);
        }

        return redirect()->route('buyer.payment.receipt', $payment->id)
            ->with('success', 'Payment recorded! Waiting for admin verification.');
    }

    /**
     * Admin verify payment
     */
    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        if (! session()->get('is_admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string',
        ]);

        $payment->update([
            'status' => $validated['status'],
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        // Notify buyer about verification result
        $message = $validated['status'] === 'verified'
            ? 'Your payment has been verified.'
            : 'Your payment verification was rejected.';

        SystemNotification::create([
            'title' => 'Payment ' . ucfirst($validated['status']),
            'body' => $message,
            'level' => $validated['status'] === 'verified' ? 'success' : 'danger',
            'is_active' => true,
            'channel' => 'in_app',
            'recipients' => [$payment->user->email ?? null],
            'sent_at' => now(),
        ]);
        // If verified, update booking status if fully paid
        if ($validated['status'] === 'verified') {
            $this->updateBookingStatus($payment->booking);
        }

        return back()->with('success', 'Payment ' . $validated['status'] . ' successfully!');
    }

    /**
     * Record payment as admin
     */
    public function adminRecord(Request $request, Booking $booking): RedirectResponse
    {
        if (! session()->get('is_admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'amount_tzs' => 'required|numeric|min:1',
            'method' => 'required|in:mpesa,stripe,payway,bank_transfer,cash',
            'reference' => 'required|string|max:100',
            'paid_at' => 'required|date',
            'status' => 'required|in:pending,verified',
            'notes' => 'nullable|string',
        ]);

        $payment = Payment::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'method' => $validated['method'],
            'reference' => $validated['reference'],
            'amount_tzs' => $validated['amount_tzs'],
            'paid_at' => $validated['paid_at'],
            'status' => $validated['status'],
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
            'verified_by' => Auth::id(),
        ]);

        // Generate receipt
        $this->generateReceipt($payment, $booking);

        // Update booking status
        if ($validated['status'] === 'verified') {
            $this->updateBookingStatus($booking);
        }

        return redirect()->route('admin.bookings')
            ->with('success', 'Payment recorded successfully!');
    }

    /**
     * List payments for admin
     */
    public function adminIndex(Request $request)
    {
        if (! session()->get('is_admin')) {
            abort(403);
        }

        $query = Payment::with(['booking.plot.project', 'booking.user', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by method
        if ($request->has('method') && $request->method) {
            $query->where('method', $request->method);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('paid_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('paid_at', '<=', $request->to_date);
        }

        $payments = $query->orderByDesc('paid_at')->paginate(20);

        return view('admin.payments.index', [
            'payments' => $payments,
            'methods' => $this->getPaymentMethods(),
        ]);
    }

    /**
     * Get payment methods
     */
    protected function getPaymentMethods(): array
    {
        return [
            'mpesa' => 'M-Pesa',
            'stripe' => 'Stripe',
            'payway' => 'PayWay',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
        ];
    }

    /**
     * Generate receipt for payment
     */
    protected function generateReceipt(Payment $payment, Booking $booking): void
    {
        if (! Receipt::where('payment_id', $payment->id)->exists()) {
            Receipt::create([
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'storage_path' => null,
                'uploaded_by' => $payment->user_id,
            ]);
        }
    }

    /**
     * Update booking status based on payments
     */
    protected function updateBookingStatus(Booking $booking): void
    {
        $totalPaid = $booking->payments()
            ->where('status', 'verified')
            ->sum('amount_tzs');

        if ($totalPaid >= $booking->price_tzs) {
            $booking->update(['status' => 'paid']);
            SystemNotification::create([
                'title' => 'Booking Paid',
                'body' => 'Your booking for plot ' . optional($booking->plot)->number . ' has been marked as paid.',
                'level' => 'success',
                'is_active' => true,
                'channel' => 'in_app',
                'recipients' => [$booking->user->email ?? null],
                'sent_at' => now(),
            ]);
        } elseif ($totalPaid > 0) {
            $booking->update(['status' => 'booked']);
            SystemNotification::create([
                'title' => 'Booking Partially Paid',
                'body' => 'A partial payment has been received for your booking: plot ' . optional($booking->plot)->number . '.',
                'level' => 'info',
                'is_active' => true,
                'channel' => 'in_app',
                'recipients' => [$booking->user->email ?? null],
                'sent_at' => now(),
            ]);
        }
    }

    /**
     * Check if user can access booking
     */
    protected function canAccessBooking(Booking $booking): bool
    {
        if (Auth::check()) {
            return $booking->user_id === Auth::id();
        }
        return false;
    }
}
