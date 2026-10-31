@php
    $mapSrc = sprintf(
        'https://www.openstreetmap.org/export/embed.html?bbox=%s%%2C%s%%2C%s%%2C%s&layer=mapnik&marker=%s%%2C%s',
        $company['lng'] - 0.02,
        $company['lat'] - 0.02,
        $company['lng'] + 0.02,
        $company['lat'] + 0.02,
        $company['lat'],
        $company['lng']
    );
@endphp

<footer class="mt-16 border-t border-border bg-secondary/50">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4">
        <div>
            <h3 class="text-lg font-bold text-foreground">Land<span class="text-primary">Focus</span></h3>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ $lang === 'en'
                    ? 'Your trusted partner in land, houses, farms and project services across Tanzania.'
                    : 'Mwenzio wa kuaminika katika biashara ya ardhi, nyumba, mashamba na huduma za miradi nchini Tanzania.' }}
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($social as $label => $href)
                    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($label) }}" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-background text-xs font-bold text-muted-foreground transition hover:border-primary/40 hover:text-primary">{{ strtoupper(substr($label, 0, 1)) }}</a>
                @endforeach
            </div>
        </div>
        <div>
            <h4 class="font-semibold text-foreground">{{ $lang === 'en' ? 'Links' : 'Viungo' }}</h4>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('home') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'Home' : 'Mwanzo' }}</a></li>
                <li><a href="{{ route('services') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'Services' : 'Huduma' }}</a></li>
                <li><a href="{{ route('properties') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'Projects' : 'Miradi' }}</a></li>
                <li><a href="{{ route('about') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'About' : 'Kuhusu sisi' }}</a></li>
                <li><a href="{{ route('history') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'History' : 'Historia' }}</a></li>
                <li><a href="{{ route('contact') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'Contact' : 'Mawasiliano' }}</a></li>
                <li><a href="{{ route('terms') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'Terms' : 'Masharti' }}</a></li>
                <li><a href="{{ route('assistant') }}" class="text-muted-foreground hover:text-primary">{{ $lang === 'en' ? 'LandFocus Assistant' : 'Msaidizi wa LandFocus' }}</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-foreground">{{ $lang === 'en' ? 'Property Types' : 'Aina za Miradi' }}</h4>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach(['land', 'house', 'farm', 'office'] as $type)
                    <li><a href="{{ route('properties', ['type' => $type]) }}" class="text-muted-foreground hover:text-primary">{{ config("landfocus.property_types.{$type}.{$lang}") }}</a></li>
                @endforeach
            </ul>
            <h4 class="mt-6 font-semibold text-foreground">{{ $lang === 'en' ? 'Contact' : 'Wasiliana' }}</h4>
            <ul class="mt-3 space-y-2 text-sm text-muted-foreground">
                <li>📞 {{ $company['phone'] }}</li>
                <li>✉ {{ $company['email'] }}</li>
                <li>📍 {{ $company['address'] }}</li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-foreground">{{ $lang === 'en' ? 'Our Location' : 'Eneo Letu' }}</h4>
            <div class="mt-3 overflow-hidden rounded-xl border border-border">
                <iframe title="{{ $lang === 'en' ? 'LandFocus office map' : 'Ramani ya ofisi ya LandFocus' }}" src="{{ $mapSrc }}" class="h-48 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <a href="https://www.google.com/maps?q={{ $company['lat'] }},{{ $company['lng'] }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-xs text-primary hover:underline">
                {{ $lang === 'en' ? 'Open in Google Maps' : 'Fungua kwenye Google Maps' }}
            </a>
        </div>
    </div>
    <div class="border-t border-border py-4 text-center text-xs text-muted-foreground">
        © {{ date('Y') }} LandFocus. {{ $lang === 'en' ? 'All rights reserved.' : 'Haki zote zimehifadhiwa.' }}
    </div>
</footer>
