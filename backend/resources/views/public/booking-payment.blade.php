@extends('layouts.app')

@section('title', 'Tuma Malipo — LandFocus')

@section('content')
<main class="mx-auto w-full max-w-2xl flex-1 px-4 py-10">
    <div class="rounded-3xl border border-border bg-secondary/10 p-6">
        <h1 class="text-2xl font-bold">Tuma Maelezo ya Malipo</h1>
        <p class="mt-2 text-sm text-muted-foreground">Chagua njia uliyotumia na uingize kumbukumbu ya malipo yako ili timu yetu iweze kuithibitisha.</p>

        <form action="{{ route('appointments.receipt.submit', $appointment->id) }}" method="POST" enctype="multipart/form-data" class="mt-8 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Njia ya Malipo</label>
                <select name="method" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="">Chagua njia</option>
                    <option value="airtelmoney">Airtel Money</option>
                    <option value="crdb">CRDB</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="tigopesa">Tigo Pesa</option>
                    <option value="halopesa">Halo Pesa</option>
                    <option value="nmb">NMB</option>
                    <option value="cash">Fedha taslimu</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Nomino ya Malipo / Kumbukumbu</label>
                <input name="reference" required class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('reference') }}" placeholder="Mfano: 1234567890" />
            </div>

            <div>
                <label class="text-sm font-medium">Kiasi (TZS)</label>
                <input name="amount_tzs" type="number" min="0" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('amount_tzs') }}" placeholder="Kiasi uliolipwa" />
            </div>

            <div>
                <label class="text-sm font-medium">Tarehe ya Malipo</label>
                <input name="paid_at" type="datetime-local" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" value="{{ old('paid_at') }}" />
            </div>

            <div>
                <label class="text-sm font-medium">Picha ya Risiti (hiari)</label>
                <input name="receipt_image" type="file" accept="image/*" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
            </div>

            <button type="submit" class="w-full rounded-md bg-primary px-4 py-3 text-base font-medium text-primary-foreground hover:bg-primary/90">Tuma maelezo ya malipo</button>
        </form>

        <div class="mt-6 rounded-2xl border border-border bg-background p-5 text-sm text-muted-foreground">
            <p class="font-semibold">Maelekezo ya malipo</p>
            <p class="mt-2">Tuma kwa Airtel Money <strong>657808178</strong> jina <strong>LANDFOCUS COMPANY LIMITED</strong>, au CRDB <strong>0150549731700</strong> jina <strong>LANDFOCUS COMPANY LIMITED</strong>.</p>
        </div>
    </div>
</main>
@endsection
