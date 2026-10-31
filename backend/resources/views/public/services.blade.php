@extends('layouts.app')

@section('title', 'Huduma Zetu — LandFocus')

@section('content')
<section class="hero-gradient py-16 text-white">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h1 class="text-4xl font-bold sm:text-5xl">Huduma Zetu</h1>
        <p class="mt-3 text-white/90">Huduma kamili za ardhi, nyumba, mashamba na ushauri wa kitaalamu</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($services as $service)
            @include('components.service-card', ['service' => $service])
        @endforeach
    </div>
</section>
@endsection
