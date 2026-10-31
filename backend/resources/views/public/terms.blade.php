@extends('layouts.app')

@section('title', 'Masharti — LandFocus')

@section('content')
<section class="hero-gradient py-16 text-white">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h1 class="text-4xl font-bold sm:text-5xl">{{ $lang === 'en' ? 'Terms & Conditions' : 'Masharti na Vigezo' }}</h1>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 prose prose-lg text-muted-foreground">
    <p>{{ $lang === 'en'
        ? 'By using the LandFocus website and services, you agree to our terms regarding property listings, inquiries, appointments, and data handling. All property information is provided in good faith; final terms are confirmed in writing during transactions.'
        : 'Kwa kutumia tovuti na huduma za LandFocus, unakubali masharti yetu kuhusu orodha za miradi, maombi, miadi na usimamizi wa data. Taarifa zote za miradi zinatolewa kwa nia njema; masharti ya mwisho yanathibitishwa kwa maandishi wakati wa miamala.' }}</p>
    <p class="mt-4">{{ $lang === 'en'
        ? 'LandFocus reserves the right to update listings, prices, and availability without prior notice. Contact us for the latest information on any property.'
        : 'LandFocus inahifadhi haki ya kusasisha orodha, bei na upatikanaji bila taarifa ya awali. Wasiliana nasi kwa taarifa za hivi karibuni kuhusu mradi wowote.' }}</p>
</section>
@endsection
