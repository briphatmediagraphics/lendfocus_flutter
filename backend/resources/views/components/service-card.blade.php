@props(['service'])

@php
    $title = $lang === 'en' ? ($service['title_en'] ?? '') : ($service['title_sw'] ?? '');
    $desc = $lang === 'en' ? ($service['desc_en'] ?? '') : ($service['desc_sw'] ?? '');
@endphp

<div class="group rounded-2xl border border-border bg-card p-6 shadow-card transition-all hover:-translate-y-1 hover:border-primary/40 hover:shadow-soft">
    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl brand-gradient text-lg text-white">◆</div>
    <h3 class="text-lg font-semibold text-foreground">{{ $title }}</h3>
    <p class="mt-2 text-sm text-muted-foreground">{{ $desc }}</p>
</div>
