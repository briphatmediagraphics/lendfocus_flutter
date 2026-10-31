@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<h1 class="text-2xl font-bold">Admin Settings</h1>

@if(session('success')) <div class="bg-green-100 text-green-800 p-3 rounded mt-4">{{ session('success') }}</div> @endif

<form method="POST" action="{{ route('admin.settings.update') }}" class="mt-4 max-w-xl">
    @csrf
    <div class="mb-3">
        <label class="block text-sm">Stripe Publishable Key</label>
        <input type="text" name="stripe_publishable" value="{{ old('stripe_publishable', $stripeKey) }}" class="w-full border p-2" />
    </div>

    <div class="mb-3">
        <label class="block text-sm">Stripe Secret Key</label>
        <input type="text" name="stripe_secret" value="{{ old('stripe_secret', $stripeSecret) }}" class="w-full border p-2" />
    </div>

    <div class="mb-3">
        <label class="block text-sm">Booking hold days</label>
        <input type="number" name="hold_days" value="{{ old('hold_days', $holdDays) }}" class="w-32 border p-2" />
        <p class="text-xs text-muted mt-1">Number of days to hold a booking before auto-release.</p>
    </div>

    <button class="px-3 py-2 bg-green-600 text-white rounded">Save</button>
</form>

@endsection
