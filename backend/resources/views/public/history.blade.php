@extends('layouts.app')

@section('title', 'Historia — LandFocus')

@section('content')
<section class="hero-gradient py-16 text-white">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h1 class="text-4xl font-bold sm:text-5xl">{{ $lang === 'en' ? 'Our History' : 'Historia Yetu' }}</h1>
        <p class="mt-3 text-white/90">{{ $lang === 'en' ? 'The LandFocus journey from 2020 to today' : 'Safari ya LandFocus kutoka 2020 hadi leo' }}</p>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
    <div class="space-y-8">
        @foreach($milestones as $m)
            <div class="relative border-l-2 border-primary/30 pl-8">
                <span class="absolute -left-3 top-0 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground">{{ substr($m['year'], 2) }}</span>
                <p class="mt-1 text-muted-foreground">{{ $lang === 'en' ? ($m['en'] ?? '') : ($m['sw'] ?? '') }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
