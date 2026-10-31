@extends('layouts.app')

@section('title', 'Wasiliana Nasi — LandFocus')

@section('content')
<section class="hero-gradient py-16 text-white">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h1 class="text-4xl font-bold sm:text-5xl">Wasiliana Nasi</h1>
        <p class="mt-3 text-white/90">Tupo tayari kukusaidia kupata miradi sahihi.</p>
    </div>
</section>

<section class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2">
    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-foreground">Njia za Kuwasiliana</h2>
        @foreach([
            ['label' => 'Simu', 'value' => $company['phone'], 'href' => 'tel:+' . $company['phone_raw']],
            ['label' => 'WhatsApp', 'value' => 'Tuma ujumbe sasa', 'href' => $social['whatsapp']],
            ['label' => 'Barua pepe', 'value' => $company['email'], 'href' => 'mailto:' . $company['email']],
            ['label' => 'Ofisi', 'value' => $company['address'], 'href' => null],
        ] as $c)
            @if($c['href'])
                <a href="{{ $c['href'] }}" class="flex items-start gap-4 rounded-2xl border border-border bg-card p-5 transition-all hover:border-primary/40 hover:shadow-card">
            @else
                <div class="flex items-start gap-4 rounded-2xl border border-border bg-card p-5">
            @endif
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl brand-gradient text-white">◆</div>
                <div>
                    <p class="text-sm text-muted-foreground">{{ $c['label'] }}</p>
                    <p class="font-semibold text-foreground">{{ $c['value'] }}</p>
                </div>
            @if($c['href'])</a>@else</div>@endif
        @endforeach
    </div>

    <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
        <h2 class="text-2xl font-bold text-foreground">Tuma Ujumbe</h2>
        <form method="POST" action="{{ route('inquiries.store') }}" class="mt-6 space-y-4">
            @csrf
            <div><label class="text-sm font-medium">Jina kamili</label><input name="name" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('name') }}"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="text-sm font-medium">Simu</label><input name="phone" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="+255..." value="{{ old('phone') }}"></div>
                <div><label class="text-sm font-medium">Barua pepe (hiari)</label><input name="email" type="email" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('email') }}"></div>
            </div>
            <div><label class="text-sm font-medium">Ujumbe</label><textarea name="message" required rows="5" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Eleza unachohitaji...">{{ old('message') }}</textarea></div>
            <button type="submit" class="w-full rounded-md bg-primary px-4 py-3 text-base font-medium text-primary-foreground hover:bg-primary/90">Tuma</button>
        </form>
    </div>
</section>
@endsection
