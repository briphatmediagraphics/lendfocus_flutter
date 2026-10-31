@extends('layouts.buyer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('buyer.plot.detail', $booking->id) }}" class="text-blue-600 hover:underline mb-6 inline-block">
            ← Back to Plot
        </a>

        <h1 class="text-3xl font-bold mb-8">Record Payment</h1>

        <!-- Booking Summary -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Booking Summary</h2>

            <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-200">
                <div>
                    <p class="text-sm text-gray-600">Plot</p>
                    <p class="font-medium">{{ $booking->plot->number }} - {{ $booking->plot->project->title ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Price</p>
                    <p class="font-bold text-lg">{{ number_format($booking->price_tzs, 2) }} TZS</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Already Paid</p>
                    <p class="font-medium text-green-600">{{ number_format($booking->total_paid ?? 0, 2) }} TZS</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Remaining Balance</p>
                    <p class="font-bold text-lg text-orange-600">{{ number_format($remainingBalance, 2) }} TZS</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium">Payment Progress</span>
                    <span class="text-sm font-medium">
                        @php
                        $percentage = $booking->price_tzs > 0 ? (($booking->total_paid ?? 0) / $booking->price_tzs) * 100 : 0;
                        $progressWidth = min($percentage, 100) . '%';
                        @endphp
                        {{ number_format($percentage, 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    {!! '<div class="bg-green-600 h-2 rounded-full transition-all" style="width: ' . e($progressWidth) . '"></div>' !!}
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-bold mb-6">Payment Details</h2>

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

            <form action="{{ route('buyer.payment.store', $booking) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Amount (TZS) *
                    </label>
                    <input type="number" name="amount_tzs" step="0.01"
                        value="{{ old('amount_tzs', $remainingBalance) }}"
                        max="{{ $remainingBalance }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        required>
                    @error('amount_tzs')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-600 mt-2">Maximum: {{ number_format($remainingBalance, 2) }} TZS</p>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Method *
                    </label>
                    <select name="method"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        required>
                        <option value="">Select a method</option>
                        @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}" @if(old('method') === $key) selected @endif>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @error('method')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reference Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Transaction Reference / Confirmation # *
                    </label>
                    <input type="text" name="reference"
                        value="{{ old('reference') }}"
                        placeholder="e.g., 2024XXXXXXXX or receipt number"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        required>
                    @error('reference')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Date *
                    </label>
                    <input type="date" name="paid_at"
                        value="{{ old('paid_at', now()->format('Y-m-d')) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        required>
                    @error('paid_at')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Additional Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Additional Notes (Optional)
                    </label>
                    <textarea name="notes" rows="3"
                        placeholder="Any additional information about this payment..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-900">
                        <strong>Note:</strong> After submission, your payment will be reviewed by our team. 
                        You'll receive a confirmation email once the payment is verified. Your receipt will be available for download.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                        Submit Payment
                    </button>
                    <a href="{{ route('buyer.plot.detail', $booking->id) }}" class="flex-1 bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 font-medium text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Payment Methods Info -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-bold mb-2">💳 M-Pesa</h3>
                <p class="text-sm text-gray-600">
                    Send payment to: <strong>+255...</strong>
                    <br>Include your control number in the message.
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-bold mb-2">🏦 Bank Transfer</h3>
                <p class="text-sm text-gray-600">
                    Contact our team for bank details.
                    <br>Reference: Your control number
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
