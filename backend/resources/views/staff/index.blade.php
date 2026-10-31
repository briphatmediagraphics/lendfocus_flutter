@extends('layouts.app')

@section('title', 'Leadership & Management - LandFocus')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Leadership & Management</h1>
        <p class="text-lg text-gray-600">Meet the team that drives our vision forward</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($staff as $member)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            @if($member->photo)
            <div class="h-64 overflow-hidden bg-gray-200">
                <img src="{{ asset('storage/' . $member->photo) }}" 
                     alt="{{ $member->name }}" 
                     class="w-full h-full object-cover">
            </div>
            @else
            <div class="h-64 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                <div class="text-white text-6xl font-bold opacity-30">
                    {{ substr($member->name, 0, 1) }}
                </div>
            </div>
            @endif

            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $member->name }}</h3>
                <p class="text-blue-600 font-semibold mb-2">{{ $member->position }}</p>

                @if($member->department)
                <p class="text-sm text-gray-600 mb-3">📁 {{ $member->department }}</p>
                @endif

                @if($member->bio)
                <p class="text-gray-700 text-sm mb-4">{{ Str::limit($member->bio, 120) }}</p>
                @endif

                <div class="flex gap-4 text-sm">
                    @if($member->email)
                    <a href="mailto:{{ $member->email }}" class="text-blue-600 hover:underline">
                        📧 Email
                    </a>
                    @endif

                    @if($member->phone)
                    <a href="tel:{{ $member->phone }}" class="text-blue-600 hover:underline">
                        📱 Call
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 text-lg">No staff members available yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
