@extends('layouts.admin')

@section('title', 'Payments — Admin')

@section('content')
<h1 class="text-2xl font-bold">Payments</h1>

<div class="mt-4 mb-4">
    <a href="{{ route('admin.payments.export') }}" class="px-3 py-2 bg-gray-800 text-white rounded">Export CSV</a>
</div>

<table class="w-full mt-4 table-auto">
    <thead>
        <tr class="text-left">
            <th>ID</th>
            <th>Booking</th>
            <th>Amount</th>
            <th>Provider</th>
            <th>Status</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $p)
            <tr class="border-t">
                <td>{{ $p->id }}</td>
                <td>{{ optional($p->booking)->control_number ?? '-' }}</td>
                <td>{{ number_format($p->amount,2) }} {{ $p->currency }}</td>
                <td>{{ $p->provider }}</td>
                <td>{{ $p->status }}</td>
                <td>{{ $p->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-4">{{ $payments->links() }}</div>
@endsection
