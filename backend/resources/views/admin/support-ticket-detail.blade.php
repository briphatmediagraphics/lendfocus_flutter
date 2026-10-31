@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="flex justify-between items-center mb-8">
        <div>
            <a href="{{ route('admin.support-tickets') }}" class="text-blue-600 hover:underline">← Back to Support Tickets</a>
            <h1 class="text-3xl font-bold mt-4">Support Ticket {{ $supportTicket->ticket_number }}</h1>
            <p class="text-gray-600 mt-1">{{ $supportTicket->subject }}</p>
        </div>
        <div class="space-x-2">
            <span class="px-3 py-1 rounded-full text-xs {{ $supportTicket->getStatusBadgeClass() }}">{{ ucfirst($supportTicket->status) }}</span>
            <span class="px-3 py-1 rounded-full text-xs {{ $supportTicket->getPriorityBadgeClass() }}">{{ ucfirst($supportTicket->priority) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Message</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $supportTicket->message }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Admin Notes</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $supportTicket->admin_notes ?? 'No internal notes added yet.' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Conversation</h2>
                <div class="space-y-4">
                    @forelse($supportTicket->replies as $reply)
                        <div class="rounded-xl border p-4 {{ $reply->sender_type === 'admin' ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }}">
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <strong>{{ $reply->sender_type === 'admin' ? 'Admin' : 'Customer' }}</strong>
                                <span class="text-gray-500">{{ $reply->created_at?->format('M d, Y H:i') }}</span>
                            </div>
                            <p class="whitespace-pre-line text-gray-700">{{ $reply->message }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500">No replies yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Reply to Customer</h2>
                <form action="{{ route('admin.support-ticket.reply', $supportTicket) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea name="message" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>{{ old('message') }}</textarea>
                    </div>
                    <div class="mb-4 flex items-center gap-3">
                        <input type="checkbox" name="is_internal" value="1" id="is_internal">
                        <label for="is_internal" class="text-sm text-gray-700">Internal note only</label>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            @foreach(['open','pending','resolved','closed'] as $status)
                            <option value="{{ $status }}" @if($supportTicket->status === $status) selected @endif>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-5 py-3 rounded-lg hover:bg-blue-700">Send Reply</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Details</h2>
                <div class="space-y-3 text-sm text-gray-700">
                    <div><span class="font-semibold">Customer:</span> {{ $supportTicket->user->name ?? 'Guest' }}</div>
                    <div><span class="font-semibold">Email:</span> {{ $supportTicket->user->email ?? 'N/A' }}</div>
                    <div><span class="font-semibold">Project:</span> {{ $supportTicket->project->title ?? 'N/A' }}</div>
                    <div><span class="font-semibold">Assigned To:</span> {{ $supportTicket->assignedTo->name ?? 'Unassigned' }}</div>
                    <div><span class="font-semibold">Last Reply:</span> {{ $supportTicket->last_replied_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Update Ticket</h2>
                <form action="{{ route('admin.support-ticket.update', $supportTicket) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            @foreach(['open','pending','resolved','closed'] as $status)
                            <option value="{{ $status }}" @if($supportTicket->status === $status) selected @endif>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            @foreach(['low','medium','high','urgent'] as $priority)
                            <option value="{{ $priority }}" @if($supportTicket->priority === $priority) selected @endif>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign to</label>
                        <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Unassigned</option>
                            @foreach(App\Models\User::orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" @if($supportTicket->assigned_to === $user->id) selected @endif>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                        <textarea name="admin_notes" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2">{{ old('admin_notes', $supportTicket->admin_notes) }}</textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-5 py-3 rounded-lg hover:bg-blue-700">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
