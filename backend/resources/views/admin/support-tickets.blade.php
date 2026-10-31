@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Support Tickets</h1>
            <p class="text-gray-600 mt-2">Manage customer support requests and service tickets.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-secondary/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Ticket</th>
                        <th class="px-4 py-3 text-left font-semibold">Subject</th>
                        <th class="px-4 py-3 text-left font-semibold">Customer</th>
                        <th class="px-4 py-3 text-left font-semibold">Project</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Priority</th>
                        <th class="px-4 py-3 text-left font-semibold">Updated</th>
                        <th class="px-4 py-3 text-left font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr class="border-b border-border/60 hover:bg-secondary/20">
                        <td class="px-4 py-3">{{ $ticket->ticket_number }}</td>
                        <td class="px-4 py-3">{{ $ticket->subject }}</td>
                        <td class="px-4 py-3">{{ $ticket->user->name ?? $ticket->name ?? 'Guest' }}</td>
                        <td class="px-4 py-3">{{ $ticket->project->title ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $ticket->getStatusBadgeClass() }}">{{ ucfirst($ticket->status) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $ticket->getPriorityBadgeClass() }}">{{ ucfirst($ticket->priority) }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $ticket->updated_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.support-ticket.detail', $ticket) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">No support tickets found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
