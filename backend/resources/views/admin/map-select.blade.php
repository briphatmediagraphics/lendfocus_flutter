@extends('layouts.admin')

@section('title', 'Select Project — Admin Map')

@section('content')
<h1 class="text-2xl font-bold">Select project to open admin map</h1>

<ul class="mt-4 space-y-2">
    @foreach($projects as $p)
        <li class="p-3 border rounded"><a href="{{ route('admin.map', ['project' => $p->id]) }}" class="text-blue-600">{{ $p->title ?? 'Project #' . $p->id }}</a></li>
    @endforeach
</ul>

@endsection
