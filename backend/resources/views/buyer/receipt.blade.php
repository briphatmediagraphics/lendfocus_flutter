@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('buyer.payments') }}" class="text-blue-600 hover:underline mb-4 inline-block">← Back to Payments</a>

        <!-- Receipt Card -->
        <div class="bg-white rounded-lg shadow p-8 mb-6">
            <!-- Header -->
            <div class="text-center border-b border-gray-200 pb-6 mb-6">
                <h1 class="text-3xl font-bold">RECEIPT</h1>
                <p class="text-gray-600 mt-2">Payment Confirmation</p>
            </div>

            <!-- Receipt Details -->
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div>
                    <p class="text-sm text-gray-600">Receipt #</p>
                    <p class="text-lg font-medium">{{ $payment->id }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Date</p>
                    <p class="text-lg font-medium">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Payment Method</p>
                    <p class="text-lg font-medium">{{ ucfirst($payment->method ?? 'Unknown') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Reference</p>
                    <p class="text-lg font-medium font-mono">{{ $payment->reference ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Transaction Details -->
            <div class="border border-gray-200 rounded p-6 mb-8">
                <h2 class="font-bold text-lg mb-4">Transaction Details</h2>

                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Plot Number</span>
                        <span class="font-medium">{{ $payment->booking->plot->number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Project</span>
                        <span class="font-medium">{{ $payment->booking->plot->project->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Plot Size</span>
                        <span class="font-medium">{{ $payment->booking->plot->size_sqm ?? 'N/A' }} sqm</span>
                    </div>
                    <div class="border-t border-gray-200 pt-4 flex justify-between text-lg font-bold">
                        <span>Payment Amount</span>
                        <span class="text-green-600">{{ number_format($payment->amount_tzs, 2) }} TZS</span>
                    </div>
                </div>
            </div>

            <!-- Buyer Information -->
            <div class="border border-gray-200 rounded p-6 mb-8">
                <h2 class="font-bold text-lg mb-4">Buyer Information</h2>

                <div class="space-y-2">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium">{{ $payment->booking->customer_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-medium">{{ $payment->booking->customer_phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">ID Number</p>
                        <p class="font-medium">{{ $payment->booking->customer_id_no ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-blue-50 border border-blue-200 rounded p-4 text-center mb-8">
                <p class="text-sm text-gray-600 mb-2">Payment Status</p>
                <p class="text-lg font-bold">
                    @if($payment->status === 'verified')
                        <span class="text-green-600">✓ VERIFIED & CONFIRMED</span>
                    @elseif($payment->status === 'pending')
                        <span class="text-yellow-600">⏳ PENDING VERIFICATION</span>
                    @else
                        <span class="text-gray-600">{{ ucfirst($payment->status) }}</span>
                    @endif
                </p>
                @if($payment->verified_at)
                <p class="text-xs text-gray-600 mt-2">Verified on {{ $payment->verified_at->format('M d, Y H:i') }}</p>
                @endif
            </div>

            <!-- Footer -->
            <div class="text-center border-t border-gray-200 pt-6 text-sm text-gray-600">
                <p>Thank you for your payment</p>
                <p class="mt-2">LandFocus - Real Estate Solutions</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-center">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                🖨️ Print Receipt
            </button>
            @if($payment->receipt && $payment->receipt->file_path)
            <a href="{{ route('buyer.payment.receipt', $payment->id) }}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                ⬇️ Download PDF
            </a>
            @endif
            <a href="{{ route('buyer.payments') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300">
                Back to Payments
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        body { background: white; }
        .container { margin: 0; padding: 0; }
        button { display: none; }
        a { display: none; }
    }
</style>
@endsection
