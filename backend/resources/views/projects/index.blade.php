@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Page Header -->
    <div class="mb-12">
        <h1 class="text-4xl font-bold mb-4">{{ __('Our Projects') }} / Miradi Yetu</h1>
        <p class="text-xl text-gray-600">Discover our premium real estate projects</p>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <form action="{{ route('projects.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Projects</label>
                    <input type="text" name="q" value="{{ old('q', $search) }}"
                        placeholder="Search by name, location..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <select name="location" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Locations</option>
                        @foreach($locations as $projectLocation)
                        <option value="{{ $projectLocation }}" {{ $projectLocation === old('location', $location) ? 'selected' : '' }}>{{ $projectLocation }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="planning" {{ $status === 'planning' ? 'selected' : '' }}>Planning</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Search
                </button>
                <a href="{{ route('projects.index') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Projects Grid -->
    @if($projects->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        @foreach($projects as $project)
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
            <!-- Project Image -->
            @if($project->cover_path)
            <div class="h-48 bg-gray-200 overflow-hidden">
                <img src="{{ asset('storage/' . $project->cover_path) }}" alt="{{ $project->title }}"
                    class="w-full h-full object-cover hover:scale-105 transition">
            </div>
            @else
            <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                <span class="text-white text-6xl">🏘️</span>
            </div>
            @endif

            <!-- Project Info -->
            <div class="p-6">
                <h3 class="text-xl font-bold mb-2">{{ $project->title }}</h3>
                
                <p class="text-gray-600 text-sm mb-4">
                    📍 {{ $project->location ?? 'N/A' }}
                    @if($project->region)
                    , {{ $project->region }}
                    @endif
                </p>

                <p class="text-gray-700 text-sm mb-4 line-clamp-3">
                    {{ Str::limit($project->description, 150) }}
                </p>

                <!-- Project Stats -->
                <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-200">
                    <div>
                        <p class="text-xs text-gray-600">Total Plots</p>
                        <p class="text-lg font-bold">{{ $project->plots()->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Available</p>
                        <p class="text-lg font-bold text-green-600">
                            {{ $project->plots()->where('status', 'open')->count() }}
                        </p>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mb-4">
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        @if($project->status === 'active') bg-green-100 text-green-800
                        @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ ucfirst($project->status ?? 'pending') }}
                    </span>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <a href="{{ route('projects.show', $project) }}" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center text-sm">
                        View Details
                    </a>
                    <a href="{{ route('projects.plots', $project) }}" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-center text-sm">
                        Browse Plots
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $projects->links() }}
    </div>
    @else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500 text-lg">No projects found matching your criteria</p>
    </div>
    @endif
</div>
@endsection
