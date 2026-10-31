@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <a href="{{ route('buyer.plots') }}" class="text-blue-600 hover:underline mb-4 inline-block">← Back to Plots</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Plot Information -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h1 class="text-3xl font-bold mb-2">Plot {{ $booking->plot->number ?? 'N/A' }}</h1>
                <p class="text-gray-600 mb-4">{{ $booking->plot->project->name ?? 'Project' }}</p>

                <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b border-gray-200">
                    <div>
                        <p class="text-sm text-gray-600">Plot Size</p>
                        <p class="text-lg font-medium">{{ $booking->plot->size_sqm ?? 'N/A' }} sqm</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Price per sqm</p>
                        <p class="text-lg font-medium">
                            @php
                            $price_per_sqm = $booking->plot->size_sqm ? ($booking->price_tzs / $booking->plot->size_sqm) : 0;
                            @endphp
                            {{ number_format($price_per_sqm, 2) }} TZS
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Control Number</p>
                        <p class="text-lg font-medium">{{ $booking->control_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Booked Date</p>
                        <p class="text-lg font-medium">{{ $booking->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                @if($booking->plot->coordinates)
                <div>
                    <p class="text-sm text-gray-600 mb-2">Location Coordinates</p>
                    <p class="text-sm font-mono bg-gray-100 p-3 rounded">
                        {{ json_encode($booking->plot->coordinates) }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Payment Status -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold mb-6">Payment Status</h2>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Total Price</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($booking->price_tzs, 2) }}</p>
                        <p class="text-xs text-gray-600">TZS</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Total Paid</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($booking->total_paid ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-600">TZS</p>
                    </div>
                    <div class="bg-orange-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Balance Due</p>
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($booking->remaining_balance ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-600">TZS</p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium">Payment Progress</span>
                        <span class="text-sm font-medium">
                            @php
                            $percentage = $booking->price_tzs > 0 ? ($booking->total_paid / $booking->price_tzs) * 100 : 0;
                            $progressWidth = min($percentage, 100) . '%';
                            @endphp
                            {{ number_format($percentage, 1) }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        {!! '<div class="bg-green-600 h-2 rounded-full transition-all" style="width: ' . e($progressWidth) . '"></div>' !!}
                    </div>
                </div>

                <p class="text-sm text-gray-600">
                    Status:
                    <span class="font-medium px-2 py-1 rounded
                        @if($booking->status === 'paid') bg-green-100 text-green-800
                        @elseif($booking->status === 'booked') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </p>
            </div>

            <!-- Payment History -->
            @if($booking->payments->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-6">Payment History</h2>

                <div class="space-y-3">
                    @foreach($booking->payments as $payment)
                    <div class="border border-gray-200 rounded p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium">Payment - {{ $payment->method ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-600">Ref: {{ $payment->reference ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">{{ number_format($payment->amount_tzs, 2) }} TZS</p>
                                <p class="text-xs text-gray-600">
                                    @if($payment->status === 'verified')
                                        <span class="text-green-600">✓ Verified</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="text-yellow-600">⏳ Pending</span>
                                    @else
                                        {{ ucfirst($payment->status) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ $payment->paid_at ? $payment->paid_at->format('M d, Y H:i') : 'No date' }}
                        </p>
                        @if($payment->receipt)
                        <a href="{{ route('buyer.payment.receipt', $payment->id) }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                            Download Receipt →
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-bold text-lg mb-4">Quick Actions</h3>

                <div class="space-y-3">
                    @if($booking->remaining_balance > 0)
                    <a href="{{ route('buyer.payment.create', $booking) }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center block">
                        Make Payment
                    </a>
                    @else
                    <div class="w-full bg-green-100 text-green-800 px-4 py-2 rounded text-center font-medium">
                        ✓ Fully Paid
                    </div>
                    @endif

                    @if($booking->payments->count() > 0)
                    <a href="{{ route('buyer.payments') }}" class="block w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-center">
                        View All Payments
                    </a>
                    @endif

                    <button class="w-full bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                        Download Invoice
                    </button>
                </div>
            </div>

            <!-- Project Information -->
            @if($booking->plot->project)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-lg mb-4">Project Details</h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium">{{ $booking->plot->project->name ?? 'N/A' }}</p>
                    </div>

                    @if($booking->plot->project->location)
                    <div>
                        <p class="text-sm text-gray-600">Location</p>
                        <p class="font-medium">{{ $booking->plot->project->location }}</p>
                    </div>
                    @endif

                    @if($booking->plot->project->total_plots)
                    <div>
                        <p class="text-sm text-gray-600">Total Plots</p>
                        <p class="font-medium">{{ $booking->plot->project->total_plots }}</p>
                    </div>
                    @endif

                    @if($booking->plot->project->description)
                    <div>
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="text-sm">{{ Str::limit($booking->plot->project->description, 150) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
