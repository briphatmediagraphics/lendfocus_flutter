@extends('layouts.admin')

@section('title', 'Bookings — Admin')

@section('content')
<h1 class="text-2xl font-bold">Bookings</h1>

<div class="mt-4 mb-4 flex gap-3">
    <a href="{{ route('admin.bookings.export') }}" class="px-3 py-2 bg-gray-800 text-white rounded">Export CSV</a>
    <a href="{{ route('admin.settings') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Settings</a>
</div>

@if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mt-4">{{ session('success') }}</div>@endif
@if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mt-4">{{ session('error') }}</div>@endif

<table class="w-full mt-4 table-auto">
    <thead>
        <tr class="text-left">
            <th>Control #</th>
            <th>Plot</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Held Until</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $booking)
            <tr class="border-t">
                <td>{{ $booking->control_number }}</td>
                <td>{{ optional($booking->plot)->code ?? '—' }}</td>
                <td>{{ number_format($booking->amount,2) }} {{ $booking->currency }}</td>
                <td>{{ $booking->status }}</td>
                <td>{{ $booking->held_until }}</td>
                <td>
                    <form class="confirmable" data-confirm-title="Release booking" data-confirm-message="Release booking {{ $booking->control_number }}?" action="{{ route('admin.bookings.release', ['booking' => $booking->id]) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="px-2 py-1 bg-yellow-500 text-white rounded" type="submit">Release</button>
                    </form>

                    <form class="confirmable" data-confirm-title="Mark booking paid" data-confirm-message="Mark booking {{ $booking->control_number }} as paid?" action="{{ route('admin.bookings.mark_paid', ['booking' => $booking->id]) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="px-2 py-1 bg-green-600 text-white rounded" type="submit">Mark Paid</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
