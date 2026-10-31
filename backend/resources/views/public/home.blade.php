@extends('layouts.app')

@section('title', 'LandFocus — Ardhi, Nyumba & Mashamba Tanzania')

@section('content')
<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <img src="{{ asset('images/hero-landfocus.jpg') }}" alt="" class="h-full w-full object-cover" width="1920" height="1080">
        <div class="absolute inset-0 hero-gradient opacity-90"></div>
    </div>
    <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:py-40">
        <div class="max-w-2xl text-white">
            <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-medium backdrop-blur">Kampuni inayoaminika Tanzania</span>
            <h1 class="mt-4 text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl">Tafuta Ardhi, Nyumba na Mashamba kwa Urahisi</h1>
            <p class="mt-6 text-lg text-white/90">
                LandFocus inakusaidia kupata miradi sahihi kwa bei nzuri, kutoka uuzaji wa viwanja na mashamba, usajili wa hati miliki, upimaji, mipango miji na tathmini, hadi ushauri wa ardhi na ramani za nyumba.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('properties') }}" class="inline-flex items-center rounded-md bg-white px-6 py-3 text-base font-semibold text-primary hover:bg-white/90">Tazama Miradi →</a>
                <a href="{{ route('contact') }}" class="inline-flex items-center rounded-md border border-white/30 bg-white/10 px-6 py-3 text-base font-semibold text-white hover:bg-white/20">Wasiliana Nasi</a>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="mb-12 text-center">
        <h2 class="text-3xl font-bold text-foreground sm:text-4xl">Huduma Zetu</h2>
        <p class="mt-3 text-muted-foreground">Huduma kamili kwa miradi na ushauri wa kitaalamu</p>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($services as $service)
            @include('components.service-card', ['service' => $service])
        @endforeach
    </div>
    <div class="mt-10 text-center">
        <a href="{{ route('services') }}" class="inline-flex items-center rounded-md border border-border bg-background px-4 py-2 text-sm font-medium hover:bg-muted">Tazama huduma zote →</a>
    </div>
</section>

@include('components.official-events', ['events' => $events])

@include('components.event-gallery', ['gallery' => $gallery, 'lang' => session('lang', 'sw')])

<section class="bg-secondary/30 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mb-12 flex items-end justify-between">
            <div>
                <h2 class="text-3xl font-bold text-foreground sm:text-4xl">Miradi Zilizopo Sasa</h2>
                <p class="mt-2 text-muted-foreground">Vipande, nyumba na mashamba bora yaliyochaguliwa kwa ajili yako</p>
            </div>
            <a href="{{ route('properties') }}" class="hidden sm:inline-flex items-center rounded-md border border-border bg-background px-4 py-2 text-sm font-medium hover:bg-muted">Tazama zote</a>
        </div>
        @if($properties->isEmpty())
            <div class="rounded-2xl border border-dashed border-border p-12 text-center text-muted-foreground">
                Hakuna miradi zilizoorodheshwa kwa sasa. Wasiliana nasi tujue unachohitaji.
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($properties->take(6) as $property)
                    @include('components.property-card', ['p' => $property])
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">Kwa Nini <span class="text-gradient">LandFocus?</span></h2>
            <p class="mt-4 text-muted-foreground">Tumejitolea kuhakikisha kila mteja anapata miradi kwa urahisi, usalama na bei nzuri.</p>
            <div class="mt-8 space-y-5">
                @foreach([
                    ['title' => 'Hati Halali', 'desc' => 'Miradi yote yana nyaraka kamili na yaliyothibitishwa.'],
                    ['title' => 'Huduma ya Karibu', 'desc' => 'Tunakusaidia hatua kwa hatua hadi upate mradi wako.'],
                    ['title' => 'Uzoefu wa Miaka', 'desc' => 'Tumesaidia mamia ya wateja kupata miradi wanayohitaji.'],
                ] as $item)
                    <div class="flex gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">✓</div>
                        <div>
                            <h3 class="font-semibold text-foreground">{{ $item['title'] }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="overflow-hidden rounded-3xl shadow-soft">
            <img src="{{ asset('images/hero-landfocus.jpg') }}" alt="LandFocus development" class="h-full w-full object-cover" loading="lazy">
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6">
    <div class="hero-gradient rounded-3xl px-8 py-14 text-center text-white shadow-soft sm:px-16">
        <h2 class="text-3xl font-bold sm:text-4xl">Tayari kupata mradi wako?</h2>
        <p class="mx-auto mt-3 max-w-xl text-white/90">Tuma ombi lako sasa na timu yetu itakusaidia kupata kile unachohitaji.</p>
        <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center rounded-md bg-white px-6 py-3 text-base font-semibold text-primary hover:bg-white/90">Anza Sasa →</a>
    </div>
</section>
@endsection
