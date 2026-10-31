@extends('layouts.app')

@section('title', 'Testimonials - LandFocus')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Testimonials</h1>
        <p class="text-lg text-gray-600">What our satisfied customers say about LandFocus</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($testimonials as $testimonial)
        <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition-shadow">
            <div class="flex items-start mb-4">
                <div class="flex text-yellow-400 text-xl">
                    @for($i = 0; $i < 5; $i++)
                    ⭐
                    @endfor
                </div>
            </div>

            <blockquote class="text-gray-700 italic mb-6">
                "{{ $testimonial->quote }}"
            </blockquote>

            <div class="flex items-center">
                @if($testimonial->author_photo)
                <img src="{{ asset('storage/' . $testimonial->author_photo) }}" alt="{{ $testimonial->author_name }}" class="w-12 h-12 rounded-full mr-4 object-cover">
                @else
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 mr-4 flex items-center justify-center text-white font-bold">
                    {{ substr($testimonial->author_name, 0, 1) }}
                </div>
                @endif

                <div>
                    <p class="font-semibold text-gray-900">{{ $testimonial->author_name }}</p>
                    @if($testimonial->author_role)
                    <p class="text-sm text-gray-600">{{ $testimonial->author_role }}</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 text-lg">No testimonials available yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
