@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Support Tickets</h1>
            <p class="text-gray-600 mt-2">View and manage your support requests.</p>
        </div>
        <a href="{{ route('buyer.support.tickets.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">New Ticket</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-secondary/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Ticket</th>
                        <th class="px-4 py-3 text-left font-semibold">Subject</th>
                        <th class="px-4 py-3 text-left font-semibold">Project</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Priority</th>
                        <th class="px-4 py-3 text-left font-semibold">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr class="border-b border-border/60 hover:bg-secondary/20">
                        <td class="px-4 py-3">{{ $ticket->ticket_number }}</td>
                        <td class="px-4 py-3">{{ $ticket->subject }}</td>
                        <td class="px-4 py-3">{{ $ticket->project->title ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ ucfirst($ticket->status) }}</td>
                        <td class="px-4 py-3">{{ ucfirst($ticket->priority) }}</td>
                        <td class="px-4 py-3">{{ $ticket->updated_at?->format('M d, Y') ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No support tickets yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
