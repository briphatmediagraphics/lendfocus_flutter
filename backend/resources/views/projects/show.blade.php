@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Back Link -->
    <a href="{{ route('projects.index') }}" class="text-blue-600 hover:underline mb-6 inline-block">← Back to Projects</a>

    <!-- Project Header -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <!-- Main Image -->
        <div class="lg:col-span-2">
            @if($project->cover_path)
            <img src="{{ asset('storage/' . $project->cover_path) }}" alt="{{ $project->title }}"
                class="w-full h-96 object-cover rounded-lg shadow-lg">
            @else
            <div class="w-full h-96 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg shadow-lg flex items-center justify-center">
                <span class="text-white text-8xl">🏘️</span>
            </div>
            @endif
        </div>

        <!-- Quick Info -->
        <div class="bg-white rounded-lg shadow p-8">
            <h1 class="text-3xl font-bold mb-4">{{ $project->title }}</h1>

            <div class="space-y-4 mb-6 pb-6 border-b border-gray-200">
                <div>
                    <p class="text-sm text-gray-600">Location</p>
                    <p class="text-lg font-medium">{{ $project->location ?? 'N/A' }}</p>
                </div>

                @if($project->region)
                <div>
                    <p class="text-sm text-gray-600">Region</p>
                    <p class="text-lg font-medium">{{ $project->region }}</p>
                </div>
                @endif

                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="text-lg">
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            @if($project->status === 'active') bg-green-100 text-green-800
                            @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ ucfirst($project->status ?? 'pending') }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <a href="{{ route('projects.plots', $project) }}" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 text-center font-medium block">
                    Browse All Plots
                </a>
                <a href="{{ route('projects.export.plots', $project) }}" class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 text-center font-medium block">
                    📥 Export Plots (CSV)
                </a>
            </div>
        </div>
    </div>

    <!-- Project Description -->
    @if($project->description)
    <div class="bg-white rounded-lg shadow p-8 mb-12">
        <h2 class="text-2xl font-bold mb-4">About This Project</h2>
        <p class="text-gray-700 leading-relaxed">
            {{ $project->description }}
        </p>
    </div>
    @endif

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Total Plots</p>
            <p class="text-3xl font-bold">{{ $project->plots->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Available</p>
            <p class="text-3xl font-bold text-green-600">{{ $project->plots->where('status', 'open')->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Booked</p>
            <p class="text-3xl font-bold text-blue-600">{{ $project->plots->where('status', 'booked')->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-600">Sold</p>
            <p class="text-3xl font-bold text-purple-600">{{ $project->plots->where('status', 'paid')->count() }}</p>
        </div>
    </div>

    <!-- Plot Preview -->
    @if($project->plots->count() > 0)
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Available Plots</h2>
            <a href="{{ route('projects.plots', $project) }}" class="text-blue-600 hover:underline">
                View All →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($project->plots->where('status', 'open')->take(6) as $plot)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition">
                <p class="font-bold text-lg">Plot {{ $plot->number }}</p>
                <p class="text-gray-600 text-sm mb-2">{{ $plot->size_sqm }} sqm</p>
                <p class="text-blue-600 font-medium mb-3">{{ number_format($plot->price_tzs, 0) }} TZS</p>
                <a href="{{ route('projects.plot.detail', [$project, $plot]) }}" class="text-blue-600 hover:underline text-sm">
                    View Details →
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
