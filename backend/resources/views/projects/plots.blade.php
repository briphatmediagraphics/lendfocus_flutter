@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline mb-4 inline-block">
            ← Back to Project
        </a>
        <h1 class="text-3xl font-bold">{{ $project->title }} - Available Plots</h1>
    </div>

    <!-- Filters Sidebar and Plot Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Filters -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h3 class="font-bold text-lg mb-4">Filters</h3>

                <form action="{{ route('projects.plots', $project) }}" method="GET" class="space-y-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">All</option>
                            <option value="open" @if(request('status') === 'open') selected @endif>Open</option>
                            <option value="booked" @if(request('status') === 'booked') selected @endif>Booked</option>
                            <option value="paid" @if(request('status') === 'paid') selected @endif>Paid</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Range (TZS)</label>
                        <input type="number" name="min_price" placeholder="Min"
                            value="{{ request('min_price') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2">
                        <input type="number" name="max_price" placeholder="Max"
                            value="{{ request('max_price') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @if($priceRange)
                        <p class="text-xs text-gray-600 mt-2">
                            Range: {{ number_format($priceRange->min, 0) }} - {{ number_format($priceRange->max, 0) }} TZS
                        </p>
                        @endif
                    </div>

                    <!-- Size Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Plot Size (sqm)</label>
                        <input type="number" name="min_size" placeholder="Min"
                            value="{{ request('min_size') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2">
                        <input type="number" name="max_size" placeholder="Max"
                            value="{{ request('max_size') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @if($sizeRange)
                        <p class="text-xs text-gray-600 mt-2">
                            Range: {{ number_format($sizeRange->min, 0) }} - {{ number_format($sizeRange->max, 0) }} sqm
                        </p>
                        @endif
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="number" @if(request('sort') === 'number' || !request('sort')) selected @endif>Plot Number</option>
                            <option value="price_low" @if(request('sort') === 'price_low') selected @endif>Price: Low to High</option>
                            <option value="price_high" @if(request('sort') === 'price_high') selected @endif>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                            Apply
                        </button>
                        <a href="{{ route('projects.plots', $project) }}" class="flex-1 bg-gray-200 text-gray-800 px-3 py-2 rounded-lg hover:bg-gray-300 text-sm text-center font-medium">
                            Reset
                        </a>
                    </div>

                    <!-- Export -->
                    <a href="{{ route('projects.export.plots', $project) }}" class="block w-full bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 text-sm text-center font-medium">
                        📥 Export CSV
                    </a>
                </form>
            </div>
        </div>

        <!-- Plots Grid -->
        <div class="lg:col-span-3">
            @if($plots->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @foreach($plots as $plot)
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                    <!-- Plot Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 text-white">
                        <h3 class="text-2xl font-bold">Plot {{ $plot->number }}</h3>
                        <p class="text-blue-100 text-sm">{{ $plot->size_sqm }} sqm</p>
                    </div>

                    <!-- Plot Details -->
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-200">
                            <div>
                                <p class="text-xs text-gray-600">Price</p>
                                <p class="text-lg font-bold text-blue-600">
                                    {{ number_format($plot->price_tzs, 0) }} TZS
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Price/sqm</p>
                                <p class="text-lg font-bold">
                                    {{ number_format($plot->price_tzs / $plot->size_sqm, 0) }} TZS
                                </p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($plot->status === 'open') bg-green-100 text-green-800
                                @elseif($plot->status === 'booked') bg-yellow-100 text-yellow-800
                                @elseif($plot->status === 'paid') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($plot->status) }}
                            </span>

                            @if($plot->bookings_count > 0)
                            <span class="ml-2 text-xs text-gray-600">
                                ({{ $plot->bookings_count }} booking{{ $plot->bookings_count > 1 ? 's' : '' }})
                            </span>
                            @endif
                        </div>

                        <!-- Notes -->
                        @if($plot->notes)
                        <p class="text-sm text-gray-600 mb-4">{{ $plot->notes }}</p>
                        @endif

                        <!-- Action Button -->
                        <a href="{{ route('projects.plot.detail', [$project, $plot]) }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center font-medium block">
                            View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $plots->links() }}
            </div>
            @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 text-lg">No plots found matching your criteria</p>
                <a href="{{ route('projects.plots', $project) }}" class="text-blue-600 hover:underline mt-4 inline-block">
                    Clear filters
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
