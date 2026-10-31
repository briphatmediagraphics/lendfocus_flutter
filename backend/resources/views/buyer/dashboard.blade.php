@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">{{ __('Dashboard') }}</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">{{ __('Total Plots') }}</div>
            <div class="text-3xl font-bold text-gray-900">{{ $stats['total_plots'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">{{ __('Total Paid') }}</div>
            <div class="text-3xl font-bold text-green-600">{{ number_format($stats['total_paid'], 2) }} TZS</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">{{ __('Balance Due') }}</div>
            <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['total_balance'], 2) }} TZS</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">{{ __('Completed') }}</div>
            <div class="text-3xl font-bold text-blue-600">{{ $stats['completed_plots'] }}</div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold">{{ __('My Recent Plots') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Plot #</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Project</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Price</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Paid</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Balance</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $booking->plot->number ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $booking->plot->project->title ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-medium">{{ number_format($booking->price_tzs, 2) }} TZS</td>
                        <td class="px-6 py-4 text-sm text-green-600 font-medium">
                            {{ number_format($booking->total_paid ?? 0, 2) }} TZS
                        </td>
                        <td class="px-6 py-4 text-sm text-orange-600 font-medium">
                            {{ number_format($booking->remaining_balance ?? 0, 2) }} TZS
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                @if($booking->status === 'paid') bg-green-100 text-green-800
                                @elseif($booking->status === 'booked') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('buyer.plot.detail', $booking->id) }}" class="text-blue-600 hover:underline">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No plots yet. <a href="{{ route('properties') }}" class="text-blue-600 hover:underline">Browse properties</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments & Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold">{{ __('Recent Payments') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Ref</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Amount</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">{{ $payment->reference }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ number_format($payment->amount_tzs, 2) }} TZS</td>
                            <td class="px-6 py-4 text-sm">{{ optional($payment->paid_at)->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm">{{ ucfirst($payment->status) }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($payment->receipt && $payment->receipt->file_path)
                                <a href="{{ route('buyer.payment.receipt', $payment->id) }}" class="text-blue-600 hover:underline">Download</a>
                                @else
                                —
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold">{{ __('Recent Activity') }}</h2>
            </div>
            <div class="px-6 py-4">
                @if($activities->isEmpty())
                <p class="text-gray-500">No recent activity.</p>
                @else
                <ul class="space-y-3">
                    @foreach($activities as $act)
                    <li class="text-sm text-gray-700">{{ $act->title ?? $act->body ?? 'Notification' }} <span class="text-xs text-gray-400">· {{ $act->created_at->diffForHumans() }}</span></li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
