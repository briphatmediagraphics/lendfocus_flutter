@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">{{ __('My Profile') }}</h1>

        <div class="bg-white rounded-lg shadow p-8">
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4">{{ __('Profile Information') }}</h2>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Full Name</p>
                        <p class="text-lg font-medium">{{ $profile->full_name ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="text-lg font-medium">{{ $profile->email ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone Number</p>
                        <p class="text-lg font-medium">{{ $profile->phone ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Member Since</p>
                        <p class="text-lg font-medium">
                            @if($profile->created_at)
                                {{ $profile->created_at->format('M d, Y') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('buyer.profile.edit') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Edit Profile
                </a>
                <a href="{{ route('buyer.dashboard') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
