@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-3xl font-bold">{{ __('Payment History') }}</h1>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('buyer.payments.export', ['type' => 'csv']) }}" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">
                📥 Export CSV
            </a>
            <a href="{{ route('buyer.payments.export', ['type' => 'pdf']) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                📄 Export PDF
            </a>
        </div>
    </div>

    @if($payments->count() > 0)
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Plot / Project</th>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Method</th>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Reference</th>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Amount</th>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-medium text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm">
                        <div>
                            <p class="font-medium">Plot {{ $payment->booking->plot->number ?? 'N/A' }}</p>
                            <p class="text-gray-600 text-xs">{{ $payment->booking->plot->project->name ?? 'N/A' }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ ucfirst($payment->method ?? 'Unknown') }}</td>
                    <td class="px-6 py-4 text-sm font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                        {{ $payment->reference ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-green-600">
                        {{ number_format($payment->amount_tzs, 2) }} TZS
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            @if($payment->status === 'verified') bg-green-100 text-green-800
                            @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @if($payment->status === 'verified')
                                ✓ Verified
                            @elseif($payment->status === 'pending')
                                ⏳ Pending
                            @else
                                {{ ucfirst($payment->status) }}
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($payment->receipt)
                        <a href="{{ route('buyer.payment.receipt', $payment->id) }}" class="text-blue-600 hover:underline">
                            Receipt
                        </a>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $payments->links() }}
    </div>
    @else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500 mb-4">No payments recorded yet</p>
        <a href="{{ route('buyer.plots') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 inline-block">
            View My Plots
        </a>
    </div>
    @endif

    <!-- Summary Card -->
    @if($payments->count() > 0)
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Payment Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border-l-4 border-green-600 pl-4">
                <p class="text-sm text-gray-600">Total Verified Payments</p>
                <p class="text-2xl font-bold text-green-600">
                    {{ number_format($payments->where('status', 'verified')->sum('amount_tzs'), 2) }} TZS
                </p>
            </div>
            <div class="border-l-4 border-yellow-600 pl-4">
                <p class="text-sm text-gray-600">Pending Verification</p>
                <p class="text-2xl font-bold text-yellow-600">
                    {{ number_format($payments->where('status', 'pending')->sum('amount_tzs'), 2) }} TZS
                </p>
            </div>
            <div class="border-l-4 border-blue-600 pl-4">
                <p class="text-sm text-gray-600">Total Payments</p>
                <p class="text-2xl font-bold text-blue-600">
                    {{ number_format($payments->sum('amount_tzs'), 2) }} TZS
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
