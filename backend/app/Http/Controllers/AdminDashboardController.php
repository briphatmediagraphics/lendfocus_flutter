<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Plot;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SystemNotification;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        // Protect all admin routes with auth + admin middleware
        $this->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class]);
    }

    public function index()
    {
        // Provide legacy-friendly $stats array used by existing admin dashboard view
        $stats = [
            'properties' => Project::count(),
            'sold' => Booking::where('status', 'paid')->count(),
            'inquiries' => SupportTicket::count(),
            'newInquiries' => SupportTicket::where('status', 'open')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::with(['plot', 'project'])->orderBy('created_at', 'desc')->paginate(40);

        return view('admin.bookings_index', compact('bookings'));
    }

    public function releaseBooking(Request $request, Booking $booking)
    {
        if ($booking->status !== 'held') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Booking is not held.'], 400);
            }
            return back()->with('error', 'Booking is not held.');
        }

        $booking->status = 'released';
        $booking->save();

        if ($booking->plot) {
            $booking->plot->status = 'open';
            $booking->plot->save();
        }

        SystemNotification::create([
            'title' => 'Booking released',
            'body' => 'Booking ' . $booking->control_number . ' was released by admin.',
            'recipients' => json_encode(['admin']),
            'data' => json_encode(['booking_id' => $booking->id])
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Booking released']);
        }

        return back()->with('success', 'Booking released');
    }

    public function markPaid(Request $request, Booking $booking)
    {
        if ($booking->status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Booking already paid'], 400);
            }
            return back()->with('error', 'Booking already paid');
        }

        $booking->status = 'paid';
        $booking->save();

        Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->amount,
            'currency' => $booking->currency ?? 'TZS',
            'provider' => 'manual',
            'provider_id' => 'admin_marked',
            'status' => 'succeeded'
        ]);

        if ($booking->plot) {
            $booking->plot->status = 'booked';
            $booking->plot->save();
        }

        SystemNotification::create([
            'title' => 'Booking marked paid',
            'body' => 'Booking ' . $booking->control_number . ' was marked paid by admin.',
            'recipients' => json_encode(['admin']),
            'data' => json_encode(['booking_id' => $booking->id])
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Booking marked as paid']);
        }

        return back()->with('success', 'Booking marked as paid');
    }

    public function exportBookings()
    {
        $fileName = 'bookings_export_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','control_number','plot_id','project_id','user_id','status','amount','currency','held_until','created_at']);

            Booking::with('plot')->orderBy('id')->chunk(200, function ($bookings) use ($handle) {
                foreach ($bookings as $b) {
                    fputcsv($handle, [
                        $b->id,
                        $b->control_number,
                        $b->plot_id,
                        $b->project_id,
                        $b->user_id,
                        $b->status,
                        (string)$b->amount,
                        $b->currency,
                        $b->held_until,
                        $b->created_at,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    public function tickets()
    {
        $tickets = SupportTicket::withCount('replies')->orderBy('created_at', 'desc')->paginate(40);

        return view('admin.tickets_index', compact('tickets'));
    }

    public function ticketView($id)
    {
        $ticket = SupportTicket::with('replies')->findOrFail($id);

        return view('admin.tickets_show', compact('ticket'));
    }

    public function ticketReply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $data = $request->validate(["message" => "required|string"]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => null,
            'author_name' => session()->get('admin_name', 'Admin'),
            'message' => $data['message']
        ]);

        $ticket->status = 'open';
        $ticket->save();

        return back()->with('success', 'Reply sent');
    }

    public function payments()
    {
        $payments = Payment::with('booking')->orderBy('created_at', 'desc')->paginate(40);

        return view('admin.payments_index', compact('payments'));
    }

    public function exportPayments()
    {
        $fileName = 'payments_export_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','booking_id','amount','currency','provider','provider_id','status','created_at']);

            Payment::orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->booking_id,
                        (string)$r->amount,
                        $r->currency,
                        $r->provider,
                        $r->provider_id,
                        $r->status,
                        $r->created_at,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    public function notifications()
    {
        $notes = SystemNotification::orderBy('created_at', 'desc')->paginate(50);

        return view('admin.notifications_index', compact('notes'));
    }

    public function map(Project $project = null)
    {
        // If project id specified via query param, allow viewing that project
        if ($project) {
            return view('admin.map', compact('project'));
        }

        $projects = Project::orderBy('title')->get();
        return view('admin.map-select', compact('projects'));
    }

    /**
     * Admin settings UI
     */
    public function settings()
    {
        $stripeKey = AdminSetting::get('stripe_publishable');
        $stripeSecret = AdminSetting::get('stripe_secret');
        $holdDays = AdminSetting::get('hold_days', 5);

        return view('admin.settings', compact('stripeKey', 'stripeSecret', 'holdDays'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'stripe_publishable' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'hold_days' => 'nullable|integer|min:0'
        ]);

        AdminSetting::set('stripe_publishable', $data['stripe_publishable'] ?? '');
        AdminSetting::set('stripe_secret', $data['stripe_secret'] ?? '');
        AdminSetting::set('hold_days', $data['hold_days'] ?? 5);

        return back()->with('success', 'Settings saved');
    }
}
