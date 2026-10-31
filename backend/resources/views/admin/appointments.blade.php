@extends('layouts.admin')

@section('title', 'Maombi ya Ziara — LandFocus Admin')

@section('content')
<h1 class="text-2xl font-bold text-foreground">Maombi ya Ziara</h1>
<p class="mt-1 text-muted-foreground">Tazama maombi yote ya ziara na uthibitishe malipo yaliyowasilishwa.</p>

<div class="mt-8 overflow-hidden rounded-3xl border border-border bg-card">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-secondary text-muted-foreground">
            <tr>
                <th class="px-4 py-3">Mteja</th>
                <th class="px-4 py-3">Simu / Barua pepe</th>
                <th class="px-4 py-3">Aina</th>
                <th class="px-4 py-3">Tarehe</th>
                <th class="px-4 py-3">Mradi</th>
                <th class="px-4 py-3">Malipo</th>
                <th class="px-4 py-3">Hali</th>
                <th class="px-4 py-3">Vitendo</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border bg-background">
            @forelse($appointments as $appointment)
                <tr>
                    <td class="px-4 py-4 font-medium text-foreground">{{ $appointment->client_name }}</td>
                    <td class="px-4 py-4 text-muted-foreground">{{ $appointment->client_phone }}<br>{{ $appointment->client_email ?? '—' }}</td>
                    <td class="px-4 py-4 text-muted-foreground">{{ $appointment->appointment_type === 'site_visit' ? 'Tembelea eneo' : 'Mazungumzo' }}</td>
                    <td class="px-4 py-4 text-muted-foreground">{{ $appointment->scheduled_at->format('j F Y H:i') }}</td>
                    <td class="px-4 py-4 text-muted-foreground">{{ $appointment->property->title ?? '—' }}</td>
                    <td class="px-4 py-4 text-muted-foreground">
                        @if($appointment->payment_method)
                            <div>{{ strtoupper($appointment->payment_method) }}</div>
                            <div>{{ $appointment->payment_reference }}</div>
                            <div>{{ $appointment->payment_amount_tzs ? number_format($appointment->payment_amount_tzs) . ' TZS' : '—' }}</div>
                        @else
                            <span class="text-muted-foreground">Hakuna maelezo</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $appointment->payment_status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($appointment->payment_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($appointment->payment_status) }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('appointments.confirm', $appointment->id) }}" class="rounded-full border border-border bg-background px-3 py-1 text-xs font-medium hover:bg-secondary">Angalia</a>
                            <form action="{{ route('admin.appointments.status', $appointment->id) }}" method="POST" class="inline-flex">
                                @csrf
                                <input type="hidden" name="payment_status" value="approved">
                                <button type="submit" class="rounded-full bg-emerald-500 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-600">Thibitisha</button>
                            </form>
                            <form action="{{ route('admin.appointments.status', $appointment->id) }}" method="POST" class="inline-flex">
                                @csrf
                                <input type="hidden" name="payment_status" value="rejected">
                                <button type="submit" class="rounded-full bg-red-500 px-3 py-1 text-xs font-medium text-white hover:bg-red-600">Kataa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-sm text-muted-foreground">Hakuna maombi ya ziara yaliyopo kwa sasa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
