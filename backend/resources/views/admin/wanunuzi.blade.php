@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Wanunuzi (Buyers)</h1>

    <div class="mb-4">
        <a href="{{ route('admin.wanunuzi.export') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded">Export CSV</a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Phone</th>
                    <th class="px-4 py-2 text-left">Joined</th>
                </tr>
            </thead>
            <tbody>
                @foreach($buyers as $b)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $b->id }}</td>
                    <td class="px-4 py-2">{{ $b->name }}</td>
                    <td class="px-4 py-2">{{ $b->email }}</td>
                    <td class="px-4 py-2">{{ $b->phone ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $b->created_at->format('Y-m-d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
