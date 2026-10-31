@extends('layouts.app')

@section('title', 'Miradi Zinazopatikana — LandFocus')

@section('content')
<section class="bg-secondary/40 py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <h1 class="text-3xl font-bold text-foreground sm:text-4xl">Miradi Zinazopatikana</h1>
        <p class="mt-2 text-muted-foreground">Chagua aina ya mradi unayohitaji</p>
        <div class="mt-6 flex flex-wrap gap-2">
            @php $filters = [null => 'Zote', 'land' => 'Ardhi', 'house' => 'Nyumba', 'farm' => 'Mashamba', 'office' => 'Ofisi']; @endphp
            @foreach($filters as $value => $label)
                <a href="{{ $value ? route('properties', ['type' => $value]) : route('properties') }}"
                   class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium {{ ($type ?? null) === $value || (!$type && !$value) ? 'bg-primary text-primary-foreground' : 'border border-border bg-background hover:bg-muted' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    @if($properties->isEmpty())
        <div class="rounded-2xl border border-dashed border-border p-16 text-center text-muted-foreground">
            Hakuna miradi zilizopatikana kwa kichujio hiki. Jaribu chujio lingine au wasiliana nasi.
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($properties as $property)
                @include('components.property-card', ['p' => $property])
            @endforeach
        </div>
    @endif
</section>
@endsection
