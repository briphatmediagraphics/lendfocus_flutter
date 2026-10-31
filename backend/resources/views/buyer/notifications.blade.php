@extends('layouts.buyer')

@section('title', 'Taarifa — LandFocus')

@section('content')
<div class="mx-auto max-w-5xl px-4">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">Taarifa</h1>
            <p class="mt-2 text-sm text-gray-600">Ona arifa za mfumo zilizotumwa kwa akaunti yako.</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($notifications as $notification)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">{{ ucfirst($notification->level) }}</p>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $notification->title }}</h2>
                    </div>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">{{ strtoupper($notification->channel) }}</span>
                </div>
                <p class="mt-3 text-gray-700">{{ $notification->body }}</p>
                @if($notification->recipients)
                    <p class="mt-3 text-sm text-gray-500">Sent to: {{ implode(', ', $notification->recipients) }}</p>
                @endif
                <p class="mt-3 text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-600">
                Hakuna taarifa kwa sasa.
            </div>
        @endforelse
    </div>
</div>
@endsection
