@if(!empty($gallery) && count($gallery) > 0)
<section class="bg-secondary/10 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-foreground sm:text-4xl">{{ $lang === 'en' ? 'Event Gallery' : 'Gallery ya Tukio' }}</h2>
            <p class="mt-2 text-muted-foreground">{{ $lang === 'en' ? 'Photos and videos from our latest company events.' : 'Picha na video kutoka matukio yetu ya kampuni.' }}</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($gallery as $media)
                <article class="overflow-hidden rounded-3xl border border-border bg-card shadow-soft">
                    @if($media['type'] === 'video')
                        <video controls class="h-72 w-full object-cover" preload="metadata">
                            <source src="{{ $media['url'] }}" type="video/{{ pathinfo($media['url'], PATHINFO_EXTENSION) }}">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <img src="{{ $media['url'] }}" alt="{{ $media['type'] }}" class="h-72 w-full object-cover" loading="lazy" />
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
