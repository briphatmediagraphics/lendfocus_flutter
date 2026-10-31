@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
        <h1 class="text-3xl font-bold">{{ __('My Plots') }}</h1>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('buyer.plots.export', ['type' => 'csv']) }}" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">
                📥 Export Bookings CSV
            </a>
            <a href="{{ route('buyer.plots.export', ['type' => 'pdf']) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                📄 Export Bookings PDF
            </a>
        </div>
    </div>

    @if($bookings->count() > 0)
    <div class="space-y-4">
        @foreach($bookings as $booking)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <h3 class="text-lg font-bold">{{ $booking->plot->number ?? 'Plot' }}</h3>
                        <p class="text-gray-600">{{ $booking->plot->project->name ?? 'Project' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Plot Size</p>
                        <p class="text-lg font-medium">{{ $booking->plot->size_sqm ?? 'N/A' }} sqm</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Price</p>
                        <p class="text-lg font-medium text-blue-600">{{ number_format($booking->price_tzs, 2) }} TZS</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 pt-4 border-t border-gray-200">
                    <div>
                        <p class="text-sm text-gray-600">Total Paid</p>
                        <p class="text-lg font-medium text-green-600">{{ number_format($booking->total_paid ?? 0, 2) }} TZS</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Balance Due</p>
                        <p class="text-lg font-medium text-orange-600">{{ number_format($booking->remaining_balance ?? 0, 2) }} TZS</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="text-lg font-medium">
                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                @if($booking->status === 'paid') bg-green-100 text-green-800
                                @elseif($booking->status === 'booked') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex gap-2 pt-4 border-t border-gray-200">
                    <a href="{{ route('buyer.plot.detail', $booking->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        View Details
                    </a>
                    @if($booking->payments->count() > 0)
                    <a href="{{ route('buyer.payments') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Payment History
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $bookings->links() }}
    </div>
    @else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500 mb-4">No plots booked yet</p>
        <a href="{{ route('properties') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 inline-block">
            Browse Properties
        </a>
    </div>
    @endif
</div>
@endsection
