@extends('layouts.app')

@section('title', $property->title . ' — LandFocus')

@section('content')
@php $images = !empty($property->imageUrls()) ? $property->imageUrls() : [$property->coverImage()]; @endphp

<section class="bg-secondary/40 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <a href="{{ route('properties') }}" class="text-sm text-muted-foreground hover:text-primary">← Rudi kwenye miradi</a>
        <h1 class="mt-4 text-3xl font-bold text-foreground">{{ $property->title }}</h1>
        <p class="mt-2 text-muted-foreground">📍 {{ $property->location }}</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <div class="grid gap-10 lg:grid-cols-2">
        <div>
            <div class="overflow-hidden rounded-2xl border border-border bg-muted">
                <img src="{{ $images[0] }}" alt="{{ $property->title }}" class="aspect-[4/3] w-full object-cover">
            </div>
            @if(count($images) > 1)
                <div class="mt-3 grid grid-cols-4 gap-2">
                    @foreach($images as $img)
                        <img src="{{ $img }}" alt="" class="aspect-square rounded-lg object-cover">
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                <p class="text-3xl font-bold text-primary">{{ $property->currency }} {{ number_format($property->price) }}</p>
                <div class="mt-4 flex flex-wrap gap-2 text-sm text-muted-foreground">
                    <span class="rounded-full bg-secondary px-3 py-1">{{ $property->typeLabel($lang) }}</span>
                    <span class="rounded-full bg-secondary px-3 py-1">{{ $t['status'][$property->status] ?? $property->status }}</span>
                    @if($property->area_sqm)<span class="rounded-full bg-secondary px-3 py-1">{{ number_format($property->area_sqm) }} m²</span>@endif
                </div>
                @if($property->description)
                    <p class="mt-6 text-muted-foreground">{{ $property->description }}</p>
                @endif
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('book', ['property_id' => $property->id]) }}" class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">Omba ziara</a>
                    <a href="https://wa.me/{{ $company['phone_raw'] }}" class="inline-flex items-center rounded-md border border-border px-4 py-2 text-sm font-medium hover:bg-muted">WhatsApp</a>
                    @if(isset($project) && $project)
                        <a href="{{ route('projects.map', ['project' => $project->id]) }}" class="inline-flex items-center rounded-md border border-border px-4 py-2 text-sm font-medium hover:bg-muted">Open Project Map</a>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('inquiries.store') }}" class="mt-6 rounded-2xl border border-border bg-card p-6 shadow-card">
                @csrf
                <input type="hidden" name="property_id" value="{{ $property->id }}">
                <h2 class="text-xl font-bold">Tuma ombi la ununuzi</h2>
                <div class="mt-4 space-y-4">
                    <div><label class="text-sm font-medium">Jina</label><input name="name" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('name') }}"></div>
                    <div><label class="text-sm font-medium">Simu</label><input name="phone" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('phone') }}"></div>
                    <div><label class="text-sm font-medium">Barua pepe</label><input name="email" type="email" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('email') }}"></div>
                    <div><label class="text-sm font-medium">Ujumbe</label><textarea name="message" required rows="4" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">{{ old('message') }}</textarea></div>
                    <button type="submit" class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">Tuma ombi</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
