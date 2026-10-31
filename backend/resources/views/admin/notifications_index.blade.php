@extends('layouts.admin')

@section('title', 'Notifications — Admin')

@section('content')
<h1 class="text-2xl font-bold">System Notifications</h1>

<ul class="mt-4 space-y-3">
    @foreach($notes as $n)
        <li class="p-3 border rounded bg-white">
            <div class="text-sm text-muted">{{ $n->created_at }}</div>
            <div class="font-semibold">{{ $n->title }}</div>
            <div class="mt-1 text-sm">{{ $n->body }}</div>
            <div class="mt-2 text-xs text-gray-600">Recipients: {{ json_encode($n->recipients) }}</div>
        </li>
    @endforeach
</ul>

<div class="mt-4">{{ $notes->links() }}</div>
@endsection
