<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LandFocus — Ardhi, Nyumba & Mashamba Tanzania')</title>
    <meta name="description" content="@yield('meta_description', 'Karibu LandFocus — Kampuni inayoongoza katika kuuza ardhi, nyumba, mashamba na ofisi Tanzania.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    @include('components.header')

    @if(session('success'))
        <div class="fixed top-20 right-4 z-50 max-w-sm rounded-2xl border border-border bg-card px-4 py-3 text-sm shadow-soft" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="fixed top-20 right-4 z-50 max-w-sm rounded-2xl border border-destructive/30 bg-card px-4 py-3 text-sm text-destructive shadow-soft" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @yield('content')

    @include('components.footer')
    @include('components.chat-fab')

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
