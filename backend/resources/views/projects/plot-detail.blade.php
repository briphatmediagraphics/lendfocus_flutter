@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <a href="{{ route('projects.plots', $project) }}" class="text-blue-600 hover:underline mb-6 inline-block">
        ← Back to Plots
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Plot Header -->
            <div class="bg-white rounded-lg shadow p-8 mb-6">
                <h1 class="text-4xl font-bold mb-2">Plot {{ $plot->number }}</h1>
                <p class="text-gray-600 mb-6">{{ $project->title }} - {{ $project->location }}</p>

                <!-- Status Banner -->
                <div class="mb-6 p-4 rounded-lg
                    @if($plot->status === 'open') bg-green-50 border border-green-200
                    @elseif($plot->status === 'booked') bg-yellow-50 border border-yellow-200
                    @elseif($plot->status === 'paid') bg-blue-50 border border-blue-200
                    @else bg-gray-50 border border-gray-200
                    @endif">
                    <p class="text-sm font-medium mb-2">Current Status</p>
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-bold
                        @if($plot->status === 'open') bg-green-200 text-green-800
                        @elseif($plot->status === 'booked') bg-yellow-200 text-yellow-800
                        @elseif($plot->status === 'paid') bg-blue-200 text-blue-800
                        @else bg-gray-200 text-gray-800
                        @endif">
                        {{ ucfirst($plot->status) }}
                    </span>
                </div>

                <!-- Plot Specifications -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pb-6 border-b border-gray-200">
                    <div>
                        <p class="text-sm text-gray-600">Plot Size</p>
                        <p class="text-2xl font-bold">{{ $plot->size_sqm }}<span class="text-lg">sqm</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Price</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($plot->price_tzs, 0) }}<span class="text-lg">TZS</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Price/sqm</p>
                        <p class="text-2xl font-bold">{{ number_format($plot->price_tzs / $plot->size_sqm, 0) }}<span class="text-lg">TZS</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Bookings</p>
                        <p class="text-2xl font-bold">{{ $plot->bookings->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Plot Coordinates (if available) -->
            @if($plot->coordinates)
            <div class="bg-white rounded-lg shadow p-8 mb-6">
                <h2 class="text-xl font-bold mb-4">Location Coordinates</h2>
                <div class="bg-gray-100 p-4 rounded text-sm font-mono">
                    {{ json_encode($plot->coordinates, JSON_PRETTY_PRINT) }}
                </div>
            </div>
            @endif

            <!-- Plot Notes -->
            @if($plot->notes)
            <div class="bg-white rounded-lg shadow p-8 mb-6">
                <h2 class="text-xl font-bold mb-4">Additional Information</h2>
                <p class="text-gray-700">{{ $plot->notes }}</p>
            </div>
            @endif

            <!-- Project Information -->
            <div class="bg-white rounded-lg shadow p-8">
                <h2 class="text-xl font-bold mb-4">About This Project</h2>
                <h3 class="text-lg font-medium mb-2">{{ $project->title }}</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Location</p>
                        <p class="font-medium">{{ $project->location ?? 'N/A' }}</p>
                    </div>
                    @if($project->region)
                    <div>
                        <p class="text-sm text-gray-600">Region</p>
                        <p class="font-medium">{{ $project->region }}</p>
                    </div>
                    @endif
                </div>

                @if($project->description)
                <p class="text-gray-700 mb-4">{{ Str::limit($project->description, 300) }}</p>
                @endif

                <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline">
                    View Full Project →
                </a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Booking Information -->
            @if($userBooking)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-lg mb-4 text-blue-900">Your Booking</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-blue-700">Control Number</p>
                        <p class="font-mono font-bold">{{ $userBooking->control_number }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700">Booking Date</p>
                        <p class="font-medium">{{ $userBooking->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700">Status</p>
                        <span class="inline-block px-2 py-1 rounded text-xs font-bold
                            @if($userBooking->status === 'paid') bg-green-200 text-green-800
                            @elseif($userBooking->status === 'booked') bg-yellow-200 text-yellow-800
                            @else bg-gray-200 text-gray-800
                            @endif">
                            {{ ucfirst($userBooking->status) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-blue-200">
                    <a href="{{ route('buyer.plot.detail', $userBooking->id) }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center block font-medium">
                        View Your Booking
                    </a>
                </div>
            </div>
            @elseif(auth('supabase')->check() && $plot->status === 'open')
            <!-- CTA to Book -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-lg mb-4">Ready to Book?</h3>
                <p class="text-sm text-gray-700 mb-4">
                    This plot is available. Contact our team to make a booking.
                </p>
                <button onclick="document.getElementById('inquiry-form').scrollIntoView({behavior: 'smooth'})"
                    class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-medium">
                    Make an Inquiry
                </button>
            </div>
            @elseif(!auth('supabase')->check())
            <!-- Login CTA -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-lg mb-4">Interested in This Plot?</h3>
                <p class="text-sm text-gray-700 mb-4">
                    Create an account to view your bookings and manage your purchases.
                </p>
                <a href="{{ route('home') }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-center block mb-2">
                    Login / Register
                </a>
            </div>
            @endif

            <!-- Quick Facts -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-bold text-lg mb-4">Quick Facts</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-2">✓</span>
                        <span>{{ number_format($plot->size_sqm, 0) }} square meters</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-2">✓</span>
                        <span>{{ number_format($plot->price_tzs, 0) }} TZS total</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-2">✓</span>
                        <span>{{ number_format($plot->price_tzs / $plot->size_sqm, 0) }} TZS per sqm</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-600 mr-2">✓</span>
                        <span>{{ $plot->status === 'open' ? 'Available now' : 'Not currently available' }}</span>
                    </li>
                </ul>
            </div>

            <!-- Contact Support -->
            <div class="bg-gray-100 rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4">Need Help?</h3>
                <p class="text-sm text-gray-700 mb-4">
                    Have questions about this plot? Contact our team.
                </p>
                <a href="{{ route('contact') }}" class="block w-full bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 text-center font-medium">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
