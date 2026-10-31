@extends('layouts.app')

@section('title', ($t['book']['title'] ?? 'Ombi la Miradi') . ' — LandFocus')

@section('content')
<main class="mx-auto w-full max-w-xl flex-1 px-4 py-10">
    <h1 class="text-2xl font-bold">{{ $t['book']['title'] ?? 'Ombi la Miradi' }}</h1>
    <p class="mt-1 text-sm text-muted-foreground">{{ $t['book']['subtitle'] ?? 'Panga ombi lako kwa timu yetu' }}</p>

    @if($selectedProperty)
        <div class="mt-6 rounded-2xl border border-border bg-secondary/10 p-4 text-sm text-muted-foreground">
            <p class="font-semibold text-foreground">Miradi iliyochaguliwa</p>
            <p>{{ $selectedProperty->title }}</p>
            <p>{{ $selectedProperty->location }}</p>
            <p>{{ $selectedProperty->currency }} {{ number_format($selectedProperty->price) }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('appointments.store') }}" class="mt-8 space-y-4">
        @csrf
        <div><label class="text-sm font-medium">{{ $t['book']['name'] ?? 'Jina lako' }}</label><input name="client_name" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('client_name') }}"></div>
        <div><label class="text-sm font-medium">{{ $t['book']['email'] ?? 'Barua pepe' }}</label><input name="client_email" type="email" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('client_email') }}"></div>
        <div><label class="text-sm font-medium">{{ $t['book']['phone'] ?? 'Simu' }}</label><input name="client_phone" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('client_phone') }}"></div>
        <div>
            <label class="text-sm font-medium">{{ $t['book']['type'] ?? 'Aina ya ombi' }}</label>
            <select name="appointment_type" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="site_visit">{{ $t['book']['siteVisit'] ?? 'Tembelea eneo' }}</option>
                <option value="consultation">{{ $t['book']['consultation'] ?? 'Mazungumzo' }}</option>
            </select>
        </div>
        <div><label class="text-sm font-medium">{{ $t['book']['date'] ?? 'Tarehe na muda' }}</label><input name="scheduled_at" type="datetime-local" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('scheduled_at') }}"></div>
        <div>
            <label class="text-sm font-medium">{{ $t['book']['property'] ?? 'Mradi (hiari)' }}</label>
            <select name="property_id" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">—</option>
                @foreach($properties as $p)
                    <option value="{{ $p->id }}" @selected(old('property_id', $propertyId) == $p->id)>{{ $p->title }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="text-sm font-medium">{{ $t['book']['notes'] ?? 'Maelezo' }}</label><textarea name="notes" rows="3" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">{{ old('notes') }}</textarea></div>
        <button type="submit" class="w-full rounded-md bg-primary px-4 py-3 text-base font-medium text-primary-foreground hover:bg-primary/90">{{ $t['book']['submit'] ?? 'Tuma ombi' }}</button>
    </form>
</main>
@endsection
