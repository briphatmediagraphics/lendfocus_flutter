@extends('layouts.app')

@section('title', 'Kuhusu LandFocus — Kampuni ya Real Estate Tanzania')

@section('content')
<section class="hero-gradient py-20 text-white">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h1 class="text-4xl font-bold sm:text-5xl">Kuhusu LandFocus</h1>
        <p class="mt-4 text-lg text-white/90">
            LandFocus ni kampuni ya Tanzania iliyoanzishwa mwaka 2020. Mwaka 2026, mfumo wetu wa mtandaoni umezinduliwa ili kuleta huduma za ardhi, nyumba, mashamba na ofisi kwa haraka, kwa uwazi, na kwa urahisi.
        </p>
    </div>
</section>

<section class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
    <p class="text-lg text-muted-foreground">
        Tunatoa huduma za kitaalamu za uuzaji wa viwanja, usajili wa hati miliki, upimaji wa viwanja, mipango miji, tathmini ya ardhi, ramani za nyumba, na ushauri wa uwekezaji. Kila huduma inafanywa kwa nyaraka halali, uwazi kamili na mteja akiwa katikati ya kila hatua.
    </p>
    <p class="mt-4 text-muted-foreground">
        Kampuni yetu inasimamia mradi kwa hatua zote muhimu ili kuhakikisha unapata ardhi au mali kwa usalama.
    </p>

    <div class="mt-6 rounded-3xl border border-border bg-card p-6 shadow-card">
        <h2 class="text-xl font-semibold text-foreground">Huduma Kuu</h2>
        <ul class="mt-4 space-y-3 text-muted-foreground">
            <li>• Uuzaji wa viwanja, mashamba, nyumba na ofisi</li>
            <li>• Usajili wa hati miliki na mabadiliko ya matumizi ya ardhi</li>
            <li>• Upimaji wa viwanja, levelling, na demarcation</li>
            <li>• Mipango miji, ramani za nyumba na site planning</li>
            <li>• Tathmini ya ardhi na ushauri wa uwekezaji</li>
        </ul>
    </div>

    <div class="mt-8 rounded-3xl border border-border bg-card p-6 shadow-card">
        <h2 class="text-xl font-semibold text-foreground">Message from the Director</h2>
        <div class="mt-6 flex flex-col gap-6 md:flex-row md:items-center">
            <img src="{{ asset('images/director-landfocus.png') }}" alt="Director of LandFocus" class="h-36 w-36 rounded-3xl object-cover">
            <p class="text-muted-foreground">
                {{ $lang === 'en'
                    ? 'At LandFocus, we believe every client deserves transparent, professional land and property services. Our team is committed to guiding you through every step — from search to title registration.'
                    : 'Katika LandFocus, tunaamini kila mteja anastahili huduma za uwazi na za kitaalamu za ardhi na mali. Timu yetu imejitolea kukuongoza hatua kwa hatua — kutoka utafutaji hadi usajili wa hati.' }}
            </p>
        </div>
    </div>
</section>
@endsection
