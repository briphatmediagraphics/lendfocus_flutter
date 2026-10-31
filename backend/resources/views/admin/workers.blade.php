@extends('layouts.admin')

@section('title', 'Wafanyakazi — LandFocus Admin')

@section('content')
<h1 class="text-2xl font-bold">Wafanyakazi</h1>
<p class="mt-1 text-sm text-muted-foreground">Orodha ya wafanyakazi wa uwanjani na ofisini.</p>

<div class="mt-6 overflow-hidden rounded-2xl border border-border bg-card shadow-card">
    <table class="w-full text-sm">
        <thead class="border-b border-border bg-secondary/50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Jina</th>
                <th class="px-4 py-3 text-left font-semibold">Jukumu</th>
                <th class="px-4 py-3 text-left font-semibold">Simu</th>
                <th class="px-4 py-3 text-left font-semibold">Hali</th>
            </tr>
        </thead>
        <tbody>
            @foreach([
                ['name' => 'Juma Hassan', 'role' => 'Surveyor', 'phone' => '+255 712 000 001', 'status' => 'Active'],
                ['name' => 'Amina Said', 'role' => 'Sales Agent', 'phone' => '+255 712 000 002', 'status' => 'Active'],
                ['name' => 'Peter Mwangi', 'role' => 'Site Inspector', 'phone' => '+255 712 000 003', 'status' => 'On leave'],
            ] as $w)
                <tr class="border-b border-border/60">
                    <td class="px-4 py-3 font-medium">{{ $w['name'] }}</td>
                    <td class="px-4 py-3">{{ $w['role'] }}</td>
                    <td class="px-4 py-3">{{ $w['phone'] }}</td>
                    <td class="px-4 py-3"><span class="rounded-full bg-secondary px-2 py-0.5 text-xs">{{ $w['status'] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
