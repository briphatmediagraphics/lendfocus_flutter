@php
    use App\Support\Land;

    $counts = $counts ?? null;
@endphp

<div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
    @foreach(Land::STATUS_META as $status => $meta)
        <span class="flex items-center gap-2">
            <span class="size-3.5 rounded-sm {{ $meta['dot'] }}"></span>
            <span class="font-medium">{{ $meta['sw'] }}</span>
            <span class="text-muted-foreground">
                ({{ $meta['en'] }}@if($counts) · {{ $counts[$status] ?? 0 }}@endif)
            </span>
        </span>
    @endforeach
</div>
