@extends('layouts.admin')

@section('title', 'Maombi — LandFocus Admin')

@section('content')
@php $lang = session('lang', 'sw'); @endphp
<h1 class="text-2xl font-bold">Maombi ya Wateja</h1>
<p class="mt-1 text-sm text-muted-foreground">Ujumbe kutoka fomu za mawasiliano na maombi ya miradi.</p>

<div class="mt-4 flex flex-wrap items-center gap-2">
    <button type="button" class="inquiry-filter-btn rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium text-foreground" data-filter="all">Yote</button>
    <button type="button" class="inquiry-filter-btn rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium text-foreground" data-filter="project">Maombi ya Miradi</button>
    <button type="button" class="inquiry-filter-btn rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium text-foreground" data-filter="contact">Ujumbe wa Mawasiliano</button>
</div>

<div class="mt-6 space-y-4" id="inquiriesList">
    @forelse($inquiries as $inquiry)
        <div class="rounded-2xl border border-border bg-card p-5 shadow-card" data-type="{{ $inquiry->isProjectRequest() ? 'project' : 'contact' }}">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="font-semibold">{{ $inquiry->name }}</h3>
                    <p class="text-sm text-muted-foreground">{{ $inquiry->phone }} @if($inquiry->email) · {{ $inquiry->email }} @endif</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-secondary px-2 py-0.5 text-xs font-semibold">{{ $inquiry->status }}</span>
                    <span class="rounded-full bg-primary px-2 py-0.5 text-xs font-semibold text-primary-foreground">{{ $inquiry->typeLabel() }}</span>
                </div>
            </div>
            @if($inquiry->property)
                <p class="mt-2 text-sm"><span class="text-muted-foreground">Mradi:</span> {{ $inquiry->property->title }}</p>
            @endif
            <p class="mt-3 text-sm text-foreground">{{ $inquiry->message }}</p>
            <p class="mt-2 text-xs text-muted-foreground">{{ $inquiry->created_at->format('d M Y H:i') }}</p>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-border p-12 text-center text-muted-foreground">Hakuna maombi bado.</div>
    @endforelse
</div>

<script>
    const inquiryFilterButtons = document.querySelectorAll('.inquiry-filter-btn');
    const inquiryCards = document.querySelectorAll('#inquiriesList [data-type]');

    inquiryFilterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;

            inquiryFilterButtons.forEach(btn => btn.classList.remove('bg-primary', 'text-primary-foreground'));
            button.classList.add('bg-primary', 'text-primary-foreground');

            inquiryCards.forEach(card => {
                if (filter === 'all' || card.dataset.type === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
