@php
    $nav = [
        ['url' => route('home'), 'label' => $t['nav']['home'] ?? 'Mwanzo'],
        ['url' => route('services'), 'label' => $t['nav']['services'] ?? 'Huduma'],
        ['url' => route('properties'), 'label' => $t['nav']['properties'] ?? 'Miradi'],
        ['url' => route('book'), 'label' => $t['nav']['book'] ?? 'Ombi la Miradi'],
        ['url' => route('about'), 'label' => $t['nav']['about'] ?? 'Kuhusu'],
        ['url' => route('contact'), 'label' => $t['nav']['contact'] ?? 'Mawasiliano'],
    ];
    $current = request()->path() ?: '/';
@endphp

<header class="sticky top-0 z-40 border-b border-border/60 bg-background/80 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/landfocus-logo.png') }}" alt="LandFocus" class="h-9 w-9" width="36" height="36">
            <span class="text-lg font-bold text-foreground">Land<span class="text-primary">Focus</span></span>
        </a>

        <nav class="hidden items-center gap-6 md:flex">
            @foreach($nav as $item)
                @php $active = rtrim(parse_url($item['url'], PHP_URL_PATH) ?? '', '/') === rtrim($current, '/') || ($current === '/' && $item['url'] === route('home')); @endphp
                <a href="{{ $item['url'] }}" class="text-sm font-medium transition-colors {{ $active ? 'text-primary' : 'text-muted-foreground hover:text-primary' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden md:flex items-center gap-2">
            <a href="{{ route('lang', $lang === 'sw' ? 'en' : 'sw') }}" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="Language">
                🌐 {{ strtoupper($lang) }}
            </a>
            @if($isAdmin)
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">{{ $t['nav']['dashboard'] ?? 'Dashboard' }}</a>
            @else
                <a href="{{ route('admin.login') }}" class="inline-flex items-center rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium hover:bg-muted">{{ $t['nav']['login'] ?? 'Ingia' }}</a>
            @endif
        </div>

        <button id="mobile-menu-btn" class="md:hidden rounded-md p-2 text-foreground" aria-label="Menu">☰</button>
    </div>

    <div id="mobile-menu" class="hidden border-t border-border bg-background md:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach($nav as $item)
                <a href="{{ $item['url'] }}" class="block rounded-md px-3 py-2 text-sm font-medium text-foreground hover:bg-muted">{{ $item['label'] }}</a>
            @endforeach
            <div class="flex items-center justify-between rounded-md px-3 py-2">
                <span class="text-sm font-medium">{{ $t['lang']['sw'] ?? 'Kiswahili' }} / {{ $t['lang']['en'] ?? 'English' }}</span>
                <a href="{{ route('lang', $lang === 'sw' ? 'en' : 'sw') }}" class="text-sm font-medium text-primary">{{ strtoupper($lang) }}</a>
            </div>
            <a href="{{ $isAdmin ? route('admin.dashboard') : route('admin.login') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-primary hover:bg-muted">
                {{ $isAdmin ? ($t['nav']['dashboard'] ?? 'Dashboard') : ($t['nav']['login'] ?? 'Ingia') }}
            </a>
        </div>
    </div>
</header>
