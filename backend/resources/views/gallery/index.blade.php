@extends('layouts.app')

@section('title', 'Gallery - LandFocus')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Photo Gallery</h1>
        <p class="text-lg text-gray-600">Project photos, development visuals, and portfolio proof</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($photos as $photo)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow group">
            <div class="relative overflow-hidden bg-gray-200 h-64">
                <img src="{{ asset('storage/' . $photo->image_path) }}" 
                     alt="{{ $photo->alt_text ?? $photo->title }}" 
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
            </div>

            <div class="p-4">
                <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ $photo->title }}</h3>
                @if($photo->description)
                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($photo->description, 100) }}</p>
                @endif
                @if($photo->project_id)
                <p class="text-xs text-gray-500">Project ID: {{ $photo->project_id }}</p>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 text-lg">No gallery photos available yet.</p>
        </div>
        @endforelse
    </div>

    @if($photos->hasPages())
    <div class="mt-8">
        {{ $photos->links() }}
    </div>
    @endif
</div>
@endsection
