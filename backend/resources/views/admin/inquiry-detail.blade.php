@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="flex justify-between items-center mb-8">
        <div>
            <a href="{{ route('admin.inquiries') }}" class="text-blue-600 hover:underline">← Back to Inquiries</a>
            <h1 class="text-3xl font-bold mt-4">Inquiry {{ $inquiry->ticket_number }}</h1>
            <p class="text-gray-600">{{ $inquiry->name }} • {{ $inquiry->email ?? 'No email' }} • {{ $inquiry->phone }}</p>
        </div>
        <div class="space-x-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $inquiry->getStatusBadgeClass() }}">{{ ucfirst($inquiry->status) }}</span>
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $inquiry->getPriorityBadgeClass() }}">{{ ucfirst($inquiry->priority) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Message</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $inquiry->message }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Replies</h2>
                @forelse($inquiry->replies as $reply)
                <div class="mb-4 p-4 rounded-lg border border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold">{{ $reply->user->name ?? 'Staff' }}</p>
                            <p class="text-xs text-gray-500">{{ $reply->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        @if($reply->is_internal)
                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Internal</span>
                        @endif
                    </div>
                    <p class="text-gray-700 whitespace-pre-line">{{ $reply->message }}</p>
                </div>
                @empty
                <p class="text-gray-500">No replies yet.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Add Reply</h2>
                <form action="{{ route('admin.inquiries.reply', $inquiry) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea name="message" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex items-center gap-3 mb-4">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Internal note</span>
                        </label>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-5 py-3 rounded-lg hover:bg-blue-700">Send Reply</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Details</h2>
                <div class="space-y-3 text-sm text-gray-700">
                    <div><span class="font-semibold">Ticket:</span> {{ $inquiry->ticket_number }}</div>
                    <div><span class="font-semibold">Created:</span> {{ $inquiry->created_at->format('M d, Y H:i') }}</div>
                    <div><span class="font-semibold">Last Reply:</span> {{ $inquiry->last_replied_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                    <div><span class="font-semibold">Assigned To:</span> {{ $inquiry->assignedTo->name ?? 'Unassigned' }}</div>
                    <div><span class="font-semibold">Project:</span> {{ $inquiry->project->title ?? 'None' }}</div>
                    <div><span class="font-semibold">Property:</span> {{ $inquiry->property->name ?? 'None' }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold text-xl mb-4">Assign / Update</h2>
                <form action="{{ route('admin.inquiries.assign', $inquiry) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign to</label>
                        <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">Unassigned</option>
                            @foreach(App\Models\User::orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" @if($inquiry->assigned_to === $user->id) selected @endif>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-green-600 text-white px-5 py-3 rounded-lg hover:bg-green-700">Save Assignment</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection