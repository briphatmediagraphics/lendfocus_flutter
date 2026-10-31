@extends('layouts.admin')

@section('title', 'Timu — LandFocus Admin')

@section('content')
<h1 class="text-2xl font-bold">Timu</h1>
<p class="mt-1 text-sm text-muted-foreground">Wafanyakazi na majukumu ya uendeshaji.</p>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach([
        ['name' => 'Director', 'role' => 'Admin', 'email' => 'director@landfocus.co.tz'],
        ['name' => 'Sales Agent', 'role' => 'Agent', 'email' => 'agent@landfocus.co.tz'],
        ['name' => 'Field Worker', 'role' => 'Worker', 'email' => 'worker@landfocus.co.tz'],
    ] as $member)
        <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary">{{ strtoupper(substr($member['name'], 0, 1)) }}</div>
            <h3 class="mt-3 font-semibold">{{ $member['name'] }}</h3>
            <p class="text-sm text-muted-foreground">{{ $member['role'] }}</p>
            <p class="mt-1 text-xs text-muted-foreground">{{ $member['email'] }}</p>
        </div>
    @endforeach
</div>
@endsection
