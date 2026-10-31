@extends('layouts.admin')

@section('title', 'Mikataba — LandFocus Admin')

@section('content')
<h1 class="text-2xl font-bold">Mikataba</h1>
<p class="mt-1 text-sm text-muted-foreground">Simamia mikataba ya wateja na hati za kisheria.</p>

<div class="mt-6 space-y-4">
    @foreach([
        ['client' => 'John Mwamba', 'property' => 'Kiwanja cha Ardhi - Goba', 'status' => 'Draft', 'date' => '2026-07-20'],
        ['client' => 'Grace Kimaro', 'property' => 'Nyumba ya Makazi - Mbezi', 'status' => 'Signed', 'date' => '2026-07-15'],
    ] as $c)
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-border bg-card p-5 shadow-card">
            <div>
                <h3 class="font-semibold">{{ $c['client'] }}</h3>
                <p class="text-sm text-muted-foreground">{{ $c['property'] }}</p>
                <p class="mt-1 text-xs text-muted-foreground">{{ $c['date'] }}</p>
            </div>
            <span class="rounded-full {{ $c['status'] === 'Signed' ? 'bg-primary/10 text-primary' : 'bg-secondary text-muted-foreground' }} px-3 py-1 text-xs font-semibold">{{ $c['status'] }}</span>
        </div>
    @endforeach
</div>
@endsection
