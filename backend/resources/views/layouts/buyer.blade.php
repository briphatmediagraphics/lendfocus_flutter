<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LandFocus') }} - Dashboard</title>

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">
                            LandFocus
                        </a>
                    </div>

                    <div class="hidden md:flex space-x-1">
                        @foreach($buyerNav as $nav)
                        <a href="{{ $nav['url'] }}" class="px-3 py-2 rounded-md text-sm font-medium 
                            @if(request()->is(str_replace('*', '*', $nav['match'])) || (request()->routeIs('dashboard') && $nav['match'] === 'dashboard'))
                                bg-blue-600 text-white
                            @else
                                text-gray-700 hover:bg-gray-100
                            @endif">
                            {{ $nav['icon'] }} {{ $nav['label'] }}
                            @if(!empty($nav['count']) && $nav['count'] > 0)
                                <span class="ml-2 inline-flex items-center rounded-full bg-red-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $nav['count'] }}</span>
                            @endif
                        </a>
                        @endforeach
                    </div>

                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ $buyer->email ?? 'Buyer' }}</span>
                        <button onclick="document.getElementById('logout-form').submit()" class="text-sm text-red-600 hover:text-red-700">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <form id="logout-form" action="{{ route('buyer.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <!-- Mobile Menu -->
        <div class="md:hidden bg-white border-b border-gray-200 px-4 py-2">
            <div class="space-y-1">
                @foreach($buyerNav as $nav)
                <a href="{{ $nav['url'] }}" class="block px-3 py-2 rounded-md text-sm font-medium 
                    @if(request()->is(str_replace('*', '*', $nav['match'])))
                        bg-blue-600 text-white
                    @else
                        text-gray-700 hover:bg-gray-100
                    @endif">
                    {{ $nav['icon'] }} {{ $nav['label'] }}
                    @if(!empty($nav['count']) && $nav['count'] > 0)
                        <span class="ml-2 inline-flex items-center rounded-full bg-red-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $nav['count'] }}</span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

        <!-- Main Content -->
        <main class="py-8">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12 py-8">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <h3 class="font-bold text-lg mb-4">LandFocus</h3>
                        <p class="text-sm text-gray-600">Your trusted real estate partner</p>
                    </div>
                    <div>
                        <h4 class="font-medium mb-4">Quick Links</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><a href="{{ route('home') }}" class="hover:text-blue-600">Home</a></li>
                            <li><a href="{{ route('properties') }}" class="hover:text-blue-600">Properties</a></li>
                            <li><a href="{{ route('contact') }}" class="hover:text-blue-600">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-4">Contact</h4>
                        <p class="text-sm text-gray-600">
                            Phone: +255 XXX XXX XXX<br>
                            Email: info@landfocus.com<br>
                            Address: Dar es Salaam, Tanzania
                        </p>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-8 text-center text-sm text-gray-600">
                    <p>&copy; {{ date('Y') }} LandFocus. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
