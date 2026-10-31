@extends('layouts.admin')

@section('title', 'Dashboard — LandFocus Admin')

@section('content')
<h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
<p class="mt-1 text-muted-foreground">Simamia miradi, maombi, timu na ripoti kutoka kituo kimoja.</p>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['label' => 'Miradi', 'value' => $stats['properties'], 'url' => route('admin.properties')],
        ['label' => 'Imeuzwa', 'value' => $stats['sold'], 'url' => route('admin.properties')],
        ['label' => 'Maombi', 'value' => $stats['inquiries'], 'url' => route('admin.inquiries')],
        ['label' => 'Maombi Mapya', 'value' => $stats['newInquiries'], 'url' => route('admin.inquiries')],
    ] as $card)
        <a href="{{ $card['url'] }}" class="rounded-2xl border border-border bg-card p-5 shadow-card transition hover:border-primary/30">
            <p class="text-sm text-muted-foreground">{{ $card['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-primary">{{ $card['value'] }}</p>
        </a>
    @endforeach
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach([
        ['title' => 'Miradi', 'desc' => 'Simamia orodha, upatikanaji na miradi maalum.', 'url' => route('admin.properties')],
        ['title' => 'Maombi', 'desc' => 'Kagua ujumbe wa wateja na badilisha kuwa leads.', 'url' => route('admin.inquiries')],
        ['title' => 'Kalenda', 'desc' => 'Matukio rasmi na ratiba za shughuli.', 'url' => route('admin.calendar')],
        ['title' => 'Mfuatilio wa Kazi', 'desc' => 'Ratibu kazi za ziara na ukaguzi.', 'url' => route('admin.work-orders')],
        ['title' => 'Timu', 'desc' => 'Simamia wafanyakazi na majukumu.', 'url' => route('admin.team')],
        ['title' => 'Ripoti', 'desc' => 'Tazama takwimu za shughuli na miradi.', 'url' => route('admin.reports')],
    ] as $mod)
        <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
            <h3 class="font-semibold text-foreground">{{ $mod['title'] }}</h3>
            <p class="mt-2 text-sm text-muted-foreground">{{ $mod['desc'] }}</p>
            <a href="{{ $mod['url'] }}" class="mt-4 inline-flex rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">Fungua</a>
        </div>
    @endforeach
</div>
@endsection
