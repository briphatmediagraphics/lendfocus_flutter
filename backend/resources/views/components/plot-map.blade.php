@php
    use App\Support\Land;

    $gridColumns = $gridColumns ?? 10;
    $selectedId = $selectedId ?? null;
    $interactive = $interactive ?? true;

    $hasCoords = $plots->contains(fn ($plot) => ($plot->position_x ?? 0) > 0 || ($plot->position_y ?? 0) > 0);

    $placed = $plots->values()->map(function ($plot, $index) use ($hasCoords, $gridColumns) {
        return [
            'plot' => $plot,
            'x' => $hasCoords ? max(1, (int) ($plot->position_x ?? 1)) : (($index % $gridColumns) + 1),
            'y' => $hasCoords ? max(1, (int) ($plot->position_y ?? 1)) : (int) floor($index / $gridColumns) + 1,
        ];
    });

    $columns = max($gridColumns, $placed->max('x') ?: 1, 1);
    $rows = max($placed->max('y') ?: 1, 1);
@endphp

@if($plots->isEmpty())
    <div class="surface-panel p-10 text-center text-sm text-muted-foreground">
        Hakuna viwanja vilivyoongezwa kwenye mradi huu bado.
    </div>
@else
    <div class="surface-panel overflow-x-auto p-3 sm:p-5">
        <div
            class="grid min-w-max gap-1.5 sm:gap-2"
            style="grid-template-columns: repeat({{ $columns }}, minmax(2.75rem, 1fr)); grid-template-rows: repeat({{ $rows }}, minmax(2.75rem, auto));"
            @if($interactive) id="plot-map-grid" @endif
        >
            @foreach($placed as $item)
                @php
                    $plot = $item['plot'];
                    $status = Land::normaliseStatus($plot->status);
                    $meta = Land::statusMeta($status);
                    $plotNumber = $plot->plot_number ?? $plot->number;
                @endphp
                @if($interactive)
                    <button
                        type="button"
                        data-plot-id="{{ $plot->id }}"
                        data-plot-number="{{ $plotNumber }}"
                        data-plot-status="{{ $status }}"
                        data-plot-status-label="{{ $meta['sw'] }}"
                        data-plot-size="{{ $plot->size_sqm ? $plot->size_sqm . ' sqm' : ($plot->size ?? '-') }}"
                        data-plot-price="{{ \App\Support\Brand::formatTzs($plot->price_tzs ?? 0) }}"
                        data-plot-notes="{{ $plot->notes ?? '' }}"
                        style="grid-column: {{ $item['x'] }}; grid-row: {{ $item['y'] }};"
                        title="Kiwanja {{ $plotNumber }} — {{ $meta['sw'] }}"
                        aria-label="Kiwanja {{ $plotNumber }}, {{ $meta['sw'] }}"
                        class="plot-cell flex aspect-square items-center justify-center rounded-md border text-[11px] font-semibold transition sm:text-xs {{ $meta['cell'] }} {{ $selectedId === $plot->id ? 'ring-2 ring-foreground ring-offset-2 ring-offset-card' : '' }}"
                    >
                        {{ $plotNumber }}
                    </button>
                @else
                    <div
                        style="grid-column: {{ $item['x'] }}; grid-row: {{ $item['y'] }};"
                        title="Kiwanja {{ $plotNumber }} — {{ $meta['sw'] }}"
                        class="flex aspect-square items-center justify-center rounded-md border text-[11px] font-semibold sm:text-xs {{ $meta['cell'] }}"
                    >
                        {{ $plotNumber }}
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
