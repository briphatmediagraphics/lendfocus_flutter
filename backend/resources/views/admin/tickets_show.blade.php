@extends('layouts.admin')

@section('title', 'Ticket Detail — Admin')

@section('content')
<h1 class="text-2xl font-bold">{{ $ticket->subject }}</h1>
<p class="text-sm text-muted">#{{ $ticket->ticket_number }} — {{ $ticket->created_at }}</p>

<div class="mt-4">
    <div class="bg-white border p-4 rounded">
        <strong>Message</strong>
        <p class="mt-2">{{ $ticket->message }}</p>
    </div>

    <h3 class="mt-4 font-semibold">Replies</h3>
    @foreach($ticket->replies as $r)
        <div class="border rounded p-3 mt-2 bg-card">
            <div class="text-sm text-muted">{{ $r->author_name ?? 'User' }} — {{ $r->created_at }}</div>
            <div class="mt-1">{{ $r->message }}</div>
        </div>
    @endforeach

    <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST" class="mt-4">
        @csrf
        <textarea name="message" rows="4" class="w-full border p-2" placeholder="Reply..."></textarea>
        <button class="mt-2 px-3 py-2 bg-primary text-white rounded">Send Reply</button>
    </form>
</div>

@endsection
