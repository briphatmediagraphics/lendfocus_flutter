<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    protected function buyer(): ?User
    {
        return Auth::check() ? Auth::user() : null;
    }

    protected function createTicketNotification(SupportTicket $ticket, string $title, string $body, string $level = 'info', array $recipients = [], string $channel = 'in_app'): void
    {
        SystemNotification::create([
            'title' => $title,
            'body' => $body,
            'level' => $level,
            'is_active' => true,
            'channel' => $channel,
            'recipients' => $recipients,
            'sent_at' => now(),
        ]);
    }

    protected function sendEmailIfAvailable(SupportTicket $ticket, string $subject, string $message): void
    {
        $recipient = $ticket->customer_email ?: ($ticket->user?->email ?? null);

        if (! $recipient) {
            return;
        }

        try {
            Mail::raw($message, function ($mail) use ($recipient, $subject) {
                $mail->to($recipient)->subject($subject);
            });
        } catch (\Throwable) {
            // Email is optional; the ticket still remains tracked in the database.
        }
    }

    public function index(): View
    {
        $user = $this->buyer();

        $query = SupportTicket::query()->with('project');

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $tickets = $query->orderByDesc('created_at')->get();

        return view('buyer.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $supportTicket): View
    {
        $user = $this->buyer();

        if ($user && $supportTicket->user_id !== $user->id) {
            abort(403);
        }

        $supportTicket->load(['project', 'user', 'replies.user']);

        return view('buyer.support-tickets.show', compact('supportTicket'));
    }

    public function create(): View
    {
        $projects = Project::orderBy('title')->get();

        return view('buyer.support-tickets.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $user = $this->buyer();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'customer_email' => 'nullable|email|max:255',
        ]);

        $customerName = $validated['customer_name'] ?? ($user?->name ?? 'Customer');
        $customerEmail = $validated['customer_email'] ?? ($user?->email ?? 'customer@landfocus.co.tz');
        $customerPhone = $validated['customer_phone'] ?? '';

        $ticket = SupportTicket::create([
            'user_id' => $user?->id,
            'project_id' => $validated['project_id'] ?? null,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'last_replied_at' => now(),
        ]);

        $this->createTicketNotification(
            $ticket,
            'New support ticket: ' . $ticket->ticket_number,
            "A new support ticket was created by {$customerName} for '{$ticket->subject}'.",
            'info',
            ['admins'],
            'admin',
        );

        $this->sendEmailIfAvailable(
            $ticket,
            'LandFocus support ticket received',
            "Hello {$customerName},\n\nYour support ticket {$ticket->ticket_number} has been received. Our team will respond shortly.\n\nSubject: {$ticket->subject}\n\nMessage:\n{$ticket->message}"
        );

        $redirectRoute = $request->route() && $request->route()->getName() === 'support.store'
            ? 'support.create'
            : 'buyer.support.tickets';

        return redirect()->route($redirectRoute)
            ->with('success', 'Support request submitted successfully.');
    }

    public function reply(Request $request, SupportTicket $supportTicket): \Illuminate\Http\RedirectResponse
    {
        $user = $this->buyer();

        abort_unless($user && $supportTicket->user_id === $user->id, 403);

        $validated = $request->validate([
            'message' => 'required|string|min:10',
        ]);

        $supportTicket->replies()->create([
            'user_id' => $user->id,
            'sender_type' => 'customer',
            'message' => $validated['message'],
        ]);

        $supportTicket->update([
            'status' => 'pending',
            'last_replied_at' => now(),
        ]);

        $this->createTicketNotification(
            $supportTicket,
            'Customer replied to ticket ' . $supportTicket->ticket_number,
            "Customer {$user->name} sent a new reply to support ticket {$supportTicket->ticket_number}.",
            'info',
            ['admins'],
            'admin',
        );

        $this->sendEmailIfAvailable(
            $supportTicket,
            'LandFocus support ticket update',
            "Hello {$supportTicket->customer_name},\n\nYour recent reply to support ticket {$supportTicket->ticket_number} has been logged. Our team will review it and respond soon.\n\nReply:\n{$validated['message']}"
        );

        return back()->with('success', 'Your reply has been sent to the support team.');
    }

    public function adminReply(Request $request, SupportTicket $supportTicket): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10',
            'status' => 'nullable|in:open,pending,resolved,closed',
            'is_internal' => 'nullable|boolean',
        ]);

        $isInternal = (bool) ($validated['is_internal'] ?? false);

        $supportTicket->replies()->create([
            'user_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $validated['message'],
            'is_internal' => $isInternal,
        ]);

        $supportTicket->update([
            'status' => $validated['status'] ?? $supportTicket->status,
            'last_replied_at' => now(),
        ]);

        $recipientName = $supportTicket->customer_name ?: ($supportTicket->user?->name ?? 'Customer');

        if (! $isInternal) {
            $this->createTicketNotification(
                $supportTicket,
                'Support ticket update: ' . $supportTicket->ticket_number,
                "The LandFocus support team replied to ticket {$supportTicket->ticket_number}.",
                'success',
                ['customer'],
                'user',
            );

            $this->sendEmailIfAvailable(
                $supportTicket,
                'LandFocus support ticket update',
                "Hello {$recipientName},\n\nAn update has been posted to your support ticket {$supportTicket->ticket_number}.\n\nMessage:\n{$validated['message']}\n\nYou can log in to your dashboard to continue the conversation."
            );
        }

        return back()->with('success', $isInternal ? 'Internal note saved.' : 'Reply sent to the customer.');
    }

    public function createPublic(): View
    {
        $projects = Project::orderBy('title')->get();

        return view('support-create', compact('projects'));
    }

    public function storePublic(Request $request)
    {
        return $this->store($request);
    }
}
