@extends('layouts.app')

@section('title', 'Uthibitisho wa Ombi — LandFocus')

@section('content')
<main class="mx-auto w-full max-w-2xl flex-1 px-4 py-10">
    <div class="rounded-3xl border border-border bg-secondary/10 p-6">
        <h1 class="text-2xl font-bold">Ombi lako limepokelewa</h1>
        <p class="mt-2 text-base text-muted-foreground">Asante kwa kuomba ziara au ushauri. Tafadhali tuma malipo kwa chaguo moja kati ya hizi ili kuthibitisha ombi lako.</p>

        <div class="mt-6 rounded-2xl border border-border bg-background p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Maelezo ya Ombi</h2>
            <dl class="mt-4 space-y-3 text-sm text-muted-foreground">
                <div class="flex justify-between gap-4">
                    <dt class="font-medium">Jina</dt>
                    <dd>{{ $appointment->client_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-medium">Simu</dt>
                    <dd>{{ $appointment->client_phone }}</dd>
                </div>
                @if($appointment->client_email)
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium">Barua pepe</dt>
                        <dd>{{ $appointment->client_email }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <dt class="font-medium">Aina ya ombi</dt>
                    <dd>{{ $appointment->appointment_type === 'site_visit' ? 'Tembelea eneo' : 'Mazungumzo' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-medium">Tarehe iliyopangwa</dt>
                    <dd>{{ $appointment->scheduled_at->format('j F Y, H:i') }}</dd>
                </div>
                @if($appointment->property)
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium">Mradi</dt>
                        <dd>{{ $appointment->property->title }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="mt-6 rounded-2xl border border-border bg-background p-5 shadow-sm">
            <h2 class="text-lg font-semibold">Maelekezo ya Malipo</h2>
            <p class="mt-3 text-sm text-muted-foreground">Tuma malipo kwa moja ya njia zifuatazo ili kuwezesha shirika letu kuthibitisha ombi lako. Ikiwa unahitaji risiti, tuma SMS au WhatsApp kwa nambari yetu baada ya malipo.</p>

            <div class="mt-4 space-y-4 text-sm text-foreground">
                <div class="rounded-2xl border border-border bg-secondary/10 p-4">
                    <p class="font-semibold">Airtel Money</p>
                    <p>Nomino ya malipo: <strong>657808178</strong></p>
                    <p>Jina: <strong>LANDFOCUS COMPANY LIMITED</strong></p>
                </div>
                <div class="rounded-2xl border border-border bg-secondary/10 p-4">
                    <p class="font-semibold">CRDB Bank</p>
                    <p>Akaunti: <strong>0150549731700</strong></p>
                    <p>Jina: <strong>LANDFOCUS COMPANY LIMITED</strong></p>
                </div>
            </div>

            <p class="mt-4 text-sm text-muted-foreground">Malipo haya yanatumika kwa ombi lako la ziara ya eneo au kulipia viwanja/mashamba. Baada ya malipo, tutawasiliana nawe kwa maelezo zaidi.</p>
        </div>

        @if($appointment->payment_method)
            <div class="mt-6 rounded-2xl border border-border bg-background p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Taarifa za malipo yaliyowasilishwa</h2>
                <p class="mt-3 text-sm text-muted-foreground">Umewasilisha maelezo ya malipo kwa:</p>
                <ul class="mt-3 space-y-2 text-sm text-foreground">
                    <li><strong>Njia:</strong> {{ strtoupper($appointment->payment_method) }}</li>
                    <li><strong>Kumbukumbu:</strong> {{ $appointment->payment_reference }}</li>
                    <li><strong>Kiasi:</strong> {{ $appointment->payment_amount_tzs ? number_format($appointment->payment_amount_tzs) . ' TZS' : 'Haijajulikana' }}</li>
                    <li><strong>Tarehe ya Malipo:</strong> {{ $appointment->payment_submitted_at?->format('j F Y H:i') ?? 'Haijajulikana' }}</li>
                    <li><strong>Hali:</strong> {{ ucfirst($appointment->payment_status) }}</li>                    @if($appointment->receipt_image_path)
                        <li><strong>Risiti:</strong> <a href="{{ asset('storage/' . $appointment->receipt_image_path) }}" target="_blank" class="text-primary hover:underline">Angalia picha</a></li>
                    @endif                </ul>
            </div>
        @endif

        <div class="mt-6 space-y-4">
            <a href="{{ route('appointments.receipt', $appointment->id) }}" class="inline-flex w-full items-center justify-center rounded-md bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90">Tuma maelezo ya malipo</a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('book', ['property_id' => $appointment->property_id]) }}" class="inline-flex items-center justify-center rounded-md border border-border bg-background px-4 py-3 text-sm font-medium hover:bg-muted">Rudi kwa OMBI</a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90">Nyumbani</a>
            </div>
        </div>
    </div>
</main>
@endsection
