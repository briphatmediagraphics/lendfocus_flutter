<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\Receipt;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BuyerDashboardController extends Controller
{
    /**
     * Get the authenticated buyer
     */
    protected function buyer()
    {
        return Auth::user();
    }

    /**
     * Get buyer nav items
     */
    protected function nav(): array
    {
        $t = config('translations.' . session('lang', 'sw') . '.buyer', []);
        $notificationsCount = 0;
        $buyer = $this->buyer();

        if ($buyer) {
            $notificationsCount = SystemNotification::where('is_active', true)
                ->where(function ($query) use ($buyer) {
                    $query->where('channel', 'in_app')
                          ->orWhereJsonContains('recipients', $buyer->email);
                })
                ->count();
        }

        return [
            ['url' => route('buyer.dashboard'), 'label' => 'Dashboard', 'match' => 'dashboard', 'icon' => '📊'],
            ['url' => route('buyer.plots'), 'label' => $t['myPlots'] ?? 'Miplot Yangu', 'match' => 'buyer/plots*', 'icon' => '🏘️'],
            ['url' => route('buyer.payments'), 'label' => $t['payments'] ?? 'Malipo', 'match' => 'buyer/payments*', 'icon' => '💳'],
            ['url' => route('buyer.notifications'), 'label' => $t['notifications'] ?? 'Taarifa', 'match' => 'buyer/notifications*', 'icon' => '🔔', 'count' => $notificationsCount],
            ['url' => route('buyer.support.tickets'), 'label' => $t['supportTickets'] ?? 'Support', 'match' => 'buyer/support-tickets*', 'icon' => '🎫'],
            ['url' => route('buyer.profile'), 'label' => $t['profile'] ?? 'Profaili', 'match' => 'buyer/profile*', 'icon' => '👤'],
        ];
    }

    /**
     * Helper to return buyer views with nav
     */
    protected function buyerView(string $view, array $data = []): View
    {
        return view($view, array_merge($data, ['buyerNav' => $this->nav(), 'buyer' => $this->buyer()]));
    }

    /**
     * Show buyer dashboard
     */
    public function dashboard(): View
    {
        $user = $this->buyer();
        $bookings = Booking::where('user_id', $user->id)
            ->with(['plot.project', 'payments'])
            ->get();

        $stats = [
            'total_plots' => $bookings->count(),
            'total_paid' => $bookings->sum(function ($booking) {
                return $booking->payments()->where('status', 'verified')->sum('amount_tzs');
            }),
            'total_balance' => $bookings->sum(function ($booking) {
                return max(0, $booking->price_tzs - $booking->payments()->where('status', 'verified')->sum('amount_tzs'));
            }),
            'completed_plots' => $bookings->where('status', 'paid')->count(),
        ];

        // Compute per-booking aggregates for display
        $bookings->transform(function ($booking) {
            $totalPaid = $booking->payments()->where('status', 'verified')->sum('amount_tzs');
            $booking->total_paid = $totalPaid;
            $booking->remaining_balance = max(0, $booking->price_tzs - $totalPaid);
            return $booking;
        });

        $recentPayments = Payment::where('user_id', $user->id)
            ->with(['booking.plot.project'])
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        $activities = SystemNotification::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('channel', 'in_app')
                      ->orWhereJsonContains('recipients', $user->email);
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return $this->buyerView('buyer.dashboard', [
            'bookings' => $bookings,
            'stats' => $stats,
            'recentPayments' => $recentPayments,
            'activities' => $activities,
        ]);
    }

    /**
     * Show buyer's plots
     */
    public function plots(): View
    {
        $user = $this->buyer();
        $bookings = Booking::where('user_id', $user->id)
            ->with(['plot.project', 'payments', 'receipts'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return $this->buyerView('buyer.plots', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Show single plot details
     */
    public function plotDetail($bookingId): View
    {
        $user = $this->buyer();
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $user->id)
            ->with(['plot.project', 'payments', 'receipts'])
            ->firstOrFail();

        return $this->buyerView('buyer.plot-detail', [
            'booking' => $booking,
        ]);
    }

    /**
     * Show buyer's payment history
     */
    public function payments(): View
    {
        $user = $this->buyer();
        $payments = Payment::where('user_id', $user->id)
            ->with(['booking.plot.project', 'receipt'])
            ->orderByDesc('paid_at')
            ->paginate(15);

        return $this->buyerView('buyer.payments', [
            'payments' => $payments,
        ]);
    }

    /**
     * Export buyer payment history as CSV
     */
    public function exportPayments(Request $request)
    {
        $type = $request->query('type', 'csv');
        $user = $this->buyer();
        $payments = Payment::where('user_id', $user->id)
            ->with(['booking.plot.project', 'receipt'])
            ->orderByDesc('paid_at')
            ->get();

        $filenameBase = 'payment-history-user' . ($user->id ?? 'guest') . '-' . now()->format('Ymd_His');

        if ($type === 'pdf') {
            $filename = $filenameBase . '.pdf';
            $pdf = $this->buildPaymentHistoryPdf($payments->toArray());

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = $filenameBase . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Plot', 'Project', 'Method', 'Reference', 'Amount (TZS)', 'Status', 'Receipt', 'Verified At']);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->paid_at?->format('Y-m-d H:i') ?? '',
                    optional($payment->booking->plot)->number,
                    optional($payment->booking->plot->project)->name,
                    ucfirst($payment->method ?? 'unknown'),
                    $payment->reference,
                    $payment->amount_tzs,
                    ucfirst($payment->status),
                    $payment->receipt ? 'Yes' : 'No',
                    $payment->verified_at?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export buyer booking history as CSV/PDF
     */
    public function exportBookings(Request $request)
    {
        $type = $request->query('type', 'csv');
        $user = $this->buyer();
        $bookings = Booking::where('user_id', $user->id)
            ->with(['plot.project'])
            ->orderByDesc('created_at')
            ->get();

        $filenameBase = 'booking-history-user' . ($user->id ?? 'guest') . '-' . now()->format('Ymd_His');

        if ($type === 'pdf') {
            $filename = $filenameBase . '.pdf';
            $pdf = $this->buildBookingHistoryPdf($bookings->toArray());

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = $filenameBase . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Plot', 'Project', 'Price (TZS)', 'Status', 'Total Paid', 'Remaining Balance', 'Booked At']);

            foreach ($bookings as $booking) {
                fputcsv($handle, [
                    optional($booking->plot)->number,
                    optional($booking->plot->project)->name,
                    $booking->price_tzs,
                    ucfirst($booking->status),
                    $booking->total_paid ?? 0,
                    $booking->remaining_balance ?? 0,
                    $booking->created_at?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function notifications(): View
    {
        $user = $this->buyer();
        $notifications = SystemNotification::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('channel', 'in_app');
                $query->orWhereJsonContains('recipients', $user->email);
            })
            ->orderByDesc('created_at')
            ->get();

        return $this->buyerView('buyer.notifications', [
            'notifications' => $notifications,
        ]);
    }

    private function buildPaymentHistoryPdf(array $payments): string
    {
        $lines = [
            'LandFocus Payment History',
            '--------------------------------------------------',
        ];

        foreach ($payments as $payment) {
            $lines[] = sprintf('Date: %s', isset($payment['paid_at']) ? Carbon::parse($payment['paid_at'])->format('Y-m-d H:i') : 'N/A');
            $lines[] = sprintf('Plot: %s', $payment['booking']['plot']['number'] ?? 'N/A');
            $lines[] = sprintf('Project: %s', $payment['booking']['plot']['project']['name'] ?? 'N/A');
            $lines[] = sprintf('Method: %s | Reference: %s | Amount: %s TZS', ucfirst($payment['method'] ?? 'unknown'), $payment['reference'] ?? '', $payment['amount_tzs'] ?? 0);
            $lines[] = sprintf('Status: %s | Receipt: %s', ucfirst($payment['status'] ?? ''), ! empty($payment['receipt']) ? 'Yes' : 'No');
            $lines[] = sprintf('Verified At: %s', isset($payment['verified_at']) ? Carbon::parse($payment['verified_at'])->format('Y-m-d H:i') : 'N/A');
            $lines[] = str_repeat('-', 70);
        }

        return $this->generateSimplePdf(implode("\n", $lines));
    }

    private function buildBookingHistoryPdf(array $bookings): string
    {
        $lines = [
            'LandFocus Booking History',
            '--------------------------------------------------',
        ];

        foreach ($bookings as $booking) {
            $lines[] = sprintf('Plot: %s | Project: %s', $booking['plot']['number'] ?? 'N/A', $booking['plot']['project']['name'] ?? 'N/A');
            $lines[] = sprintf('Price: %s TZS | Status: %s', $booking['price_tzs'] ?? 0, ucfirst($booking['status'] ?? ''));
            $lines[] = sprintf('Total Paid: %s TZS | Remaining Balance: %s TZS', $booking['total_paid'] ?? 0, $booking['remaining_balance'] ?? 0);
            $lines[] = sprintf('Booked At: %s', isset($booking['created_at']) ? Carbon::parse($booking['created_at'])->format('Y-m-d H:i') : 'N/A');
            $lines[] = str_repeat('-', 70);
        }

        return $this->generateSimplePdf(implode("\n", $lines));
    }

    private function generateSimplePdf(string $content): string
    {
        $lines = explode("\n", $content);
        $pdfLines = ['BT', '/F1 12 Tf'];
        $y = 770;

        foreach ($lines as $line) {
            $escaped = $this->pdfEscape($line);
            $pdfLines[] = sprintf('1 0 0 1 40 %s Tm (%s) Tj', $y, $escaped);
            $y -= 16;
            if ($y < 40) {
                break;
            }
        }

        $pdfLines[] = 'ET';
        $stream = implode("\n", $pdfLines);
        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";
        $objects[] = sprintf("5 0 obj\n<< /Length %d >>\nstream\n%s\nendstream\nendobj", strlen($stream), $stream);

        $pdf = "%PDF-1.4\n";
        $xref = [];
        $byteOffset = strlen($pdf);

        foreach ($objects as $object) {
            $xref[] = $byteOffset;
            $pdf .= $object . "\n";
            $byteOffset = strlen($pdf);
        }

        $xrefPosition = $byteOffset;
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($xref as $offset) {
            $pdf .= sprintf('%010d 00000 n \n', $offset);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    /**
     * Show payment receipt
     */
    public function receipt($paymentId)
    {
        $user = $this->buyer();
        $payment = Payment::where('id', $paymentId)
            ->where('user_id', $user->id)
            ->with(['booking.plot.project', 'receipt'])
            ->firstOrFail();

        // If receipt file exists, download it
        if ($payment->receipt && $payment->receipt->file_path) {
            if (Storage::disk('local')->exists($payment->receipt->file_path)) {
                return Storage::download($payment->receipt->file_path);
            }
        }

        // Otherwise show receipt view
        return $this->buyerView('buyer.receipt', [
            'payment' => $payment,
        ]);
    }

    /**
     * Show buyer profile
     */
    public function profile(): View
    {
        $user = $this->buyer();
        $profile = $user->profile ?? new \App\Models\Profile();

        return $this->buyerView('buyer.profile', [
            'profile' => $profile,
        ]);
    }

    /**
     * Update buyer profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        $user = $this->buyer();
        $profile = $user->profile ?? new \App\Models\Profile(['user_id' => $user->id]);

        $profile->update($validated);

        return redirect()->route('buyer.profile')
            ->with('success', 'Profile updated successfully');
    }

    /**
     * Show buyer edit profile page
     */
    public function editProfile(): View
    {
        $user = $this->buyer();
        $profile = $user->profile ?? new \App\Models\Profile();

        return $this->buyerView('buyer.profile-edit', [
            'profile' => $profile,
        ]);
    }

    /**
     * Logout buyer
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Logged out successfully');
    }
}
