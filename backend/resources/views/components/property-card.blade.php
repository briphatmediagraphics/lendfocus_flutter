@props(['p'])

@php
    $property = is_array($p) ? (object) $p : $p;
    $img = $property->coverImage();
    $statusLabels = $t['status'] ?? [];
@endphp

<div class="group overflow-hidden rounded-2xl border border-border bg-card shadow-card transition-all hover:-translate-y-1 hover:shadow-soft">
    <a href="{{ route('property.detail', $property->id) }}" class="block">
        <div class="relative aspect-[4/3] overflow-hidden bg-muted">
            <img src="{{ $img }}" alt="{{ $property->title }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            <div class="absolute left-3 top-3 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground shadow">
                {{ $property->typeLabel($lang) }}
            </div>
            @if(count($property->images ?? []) > 1)
                <div class="absolute left-3 bottom-3 rounded-full bg-black/70 px-3 py-1 text-xs font-semibold text-white">
                    +{{ count($property->images) - 1 }} picha zaidi
                </div>
            @endif
            @if($property->status !== 'available')
                <div class="absolute right-3 top-3 rounded-full bg-destructive/95 px-3 py-1 text-xs font-semibold text-destructive-foreground">
                    {{ $statusLabels[$property->status] ?? $property->status }}
                </div>
            @endif
        </div>
    </a>
    <div class="space-y-3 p-4">
        <a href="{{ route('property.detail', $property->id) }}" class="block">
            <h3 class="line-clamp-1 text-lg font-semibold text-foreground group-hover:text-primary">{{ $property->title }}</h3>
        </a>
        <p class="flex items-center gap-1 text-sm text-muted-foreground">📍 {{ $property->location }}</p>
        <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
            @if($property->area_sqm)<span>⬜ {{ number_format($property->area_sqm) }} m²</span>@endif
            @if($property->bedrooms)<span>🛏 {{ $property->bedrooms }}</span>@endif
            @if($property->bathrooms)<span>🚿 {{ $property->bathrooms }}</span>@endif
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-xl font-bold text-primary">{{ $property->currency }} {{ number_format($property->price) }}</p>
            <a href="{{ route('book', ['property_id' => $property->id]) }}" class="inline-flex items-center rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium hover:bg-muted">Ombi</a>
        </div>
    </div>
</div>
