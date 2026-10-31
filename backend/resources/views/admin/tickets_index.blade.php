@extends('layouts.admin')

@section('title', 'Support Tickets — Admin')

@section('content')
<h1 class="text-2xl font-bold">Support Tickets</h1>

<table class="w-full mt-4 table-auto">
    <thead>
        <tr class="text-left">
            <th>#</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Replies</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $t)
            <tr class="border-t">
                <td>{{ $t->ticket_number }}</td>
                <td>{{ $t->subject }}</td>
                <td>{{ $t->status }}</td>
                <td>{{ $t->replies_count }}</td>
                <td>{{ $t->created_at }}</td>
                <td><a href="{{ route('admin.tickets.show', $t->id) }}" class="text-blue-600">View</a></td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
