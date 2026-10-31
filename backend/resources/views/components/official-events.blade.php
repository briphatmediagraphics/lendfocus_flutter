@if(!empty($events) && count($events) > 0)
<section class="bg-secondary/30 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mb-8 text-center">
            <span class="mb-3 inline-flex rounded-full bg-secondary px-3 py-1 text-xs font-semibold">{{ $lang === 'en' ? 'Official' : 'Rasmi' }}</span>
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">
                {{ $lang === 'en' ? 'Official Events & Announcements' : 'Matukio Rasmi na Matangazo' }}
            </h2>
            <p class="mt-2 text-muted-foreground">
                {{ $lang === 'en' ? 'Upcoming activities and announcements from LandFocus' : 'Shughuli na matangazo yajayo kutoka LandFocus' }}
            </p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($events->take(6) as $event)
                <article class="rounded-2xl border border-border bg-card p-5 shadow-card transition hover:border-primary/30">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold text-foreground">{{ $event->title }}</h3>
                        <span class="shrink-0 rounded-full bg-secondary px-2 py-0.5 text-[10px] font-semibold">{{ $lang === 'en' ? 'Official' : 'Rasmi' }}</span>
                    </div>
                    <p class="mt-3 flex items-center gap-2 text-sm text-muted-foreground">📅 {{ $event->start_at->format($lang === 'en' ? 'M j, Y H:i' : 'd M Y H:i') }}</p>
                    @if($event->location)
                        <p class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">📍 {{ $event->location }}</p>
                    @endif
                    @if($event->description)
                        <p class="mt-3 text-sm text-muted-foreground">{{ \Illuminate\Support\Str::limit($event->description, 120) }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
