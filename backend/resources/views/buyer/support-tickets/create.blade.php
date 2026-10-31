@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('buyer.support.tickets') }}" class="text-blue-600 hover:underline mb-4 inline-block">← Back to Support Tickets</a>
        <h1 class="text-3xl font-bold mb-6">New Support Ticket</h1>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <p class="font-bold">Please fix the following errors:</p>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('buyer.support.tickets.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Project (optional)</label>
                <select name="project_id" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500">
                    <option value="">None</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" @if(old('project_id') === $project->id) selected @endif>{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                <select name="priority" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500" required>
                    @foreach(['low','medium','high','urgent'] as $priority)
                    <option value="{{ $priority }}" @if(old('priority') === $priority) selected @endif>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                <textarea name="message" rows="6" required class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white rounded-lg px-6 py-3 hover:bg-blue-700">Submit Ticket</button>
        </form>
    </div>
</div>
@endsection
