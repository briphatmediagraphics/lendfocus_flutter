@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Map Shapes — {{ $project->title }}</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($shapes as $i => $shape)
            <div class="rounded-lg border p-4 bg-white">
                <div class="mb-2 text-sm text-gray-600">Shape #{{ $i }}</div>
                <pre class="text-xs bg-gray-50 p-3 rounded overflow-auto">{{ json_encode($shape, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                <form action="{{ route('admin.projects.shapes.delete', ['project' => $project->id, 'index' => $i]) }}" method="POST" onsubmit="return confirm('Delete shape #{{ $i }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="mt-3 rounded bg-red-600 text-white px-3 py-2">Delete</button>
                </form>
            </div>
        @empty
            <div class="rounded-lg border p-4 bg-white">No shapes saved for this project.</div>
        @endforelse
    </div>
</div>
@endsection
