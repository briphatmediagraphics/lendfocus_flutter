@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('buyer.support.tickets') }}" class="text-blue-600 hover:underline">← Back to tickets</a>
            <h1 class="mt-4 text-3xl font-bold">{{ $supportTicket->ticket_number }}</h1>
            <p class="mt-1 text-gray-600">{{ $supportTicket->subject }}</p>
        </div>
        <div class="space-x-2">
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportTicket->getStatusBadgeClass() }}">{{ ucfirst($supportTicket->status) }}</span>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportTicket->getPriorityBadgeClass() }}">{{ ucfirst($supportTicket->priority) }}</span>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-xl font-bold">Your original message</h2>
                <p class="whitespace-pre-line text-gray-700">{{ $supportTicket->message }}</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold">Conversation</h2>

                @forelse($supportTicket->replies as $reply)
                    <div class="mb-4 rounded-xl border border-gray-200 p-4 {{ $reply->sender_type === 'admin' ? 'bg-blue-50' : 'bg-gray-50' }}">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <strong>{{ $reply->sender_type === 'admin' ? 'LandFocus Team' : 'You' }}</strong>
                            <span class="text-gray-500">{{ $reply->created_at?->format('M d, Y H:i') }}</span>
                        </div>
                        <p class="whitespace-pre-line text-gray-700">{{ $reply->message }}</p>
                    </div>
                @empty
                    <p class="text-gray-500">No replies yet. We will be in touch soon.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold">Ticket details</h2>
                <div class="space-y-2 text-sm text-gray-700">
                    <div><span class="font-semibold">Project:</span> {{ $supportTicket->project->title ?? 'N/A' }}</div>
                    <div><span class="font-semibold">Created:</span> {{ $supportTicket->created_at?->format('M d, Y H:i') }}</div>
                    <div><span class="font-semibold">Last update:</span> {{ $supportTicket->last_replied_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold">Reply</h2>
                <form action="{{ route('buyer.support.tickets.reply', $supportTicket) }}" method="POST">
                    @csrf
                    <textarea name="message" rows="6" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none" placeholder="Write your message to the support team..." required>{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="mt-4 w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700">Send Reply</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
