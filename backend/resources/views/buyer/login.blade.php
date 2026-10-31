@extends('layouts.app')

@section('title', 'Buyer Login — LandFocus')

@section('content')
<div class="container mx-auto px-4 py-16">
    <div class="mx-auto max-w-md rounded-3xl border border-border bg-card p-8 shadow-card">
        <div class="mb-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary">Buyer portal</p>
            <h1 class="mt-3 text-3xl font-bold">Login to your account</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('buyer.login.post') }}" class="space-y-5">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-border bg-background px-4 py-3" placeholder="you@example.com">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Password</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-border bg-background px-4 py-3" placeholder="Password">
            </div>

            <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 font-medium text-primary-foreground hover:bg-primary/90">Login</button>
        </form>

        <p class="mt-6 text-center text-sm text-muted-foreground">
            Need help? <a href="{{ route('support.create') }}" class="text-primary hover:underline">Open a support ticket</a>
        </p>
    </div>
</div>
@endsection
