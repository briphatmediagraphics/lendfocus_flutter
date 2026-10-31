<!DOCTYPE html>
<html lang="{{ $lang ?? 'sw' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — LandFocus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="flex min-h-screen items-center justify-center bg-secondary/30 p-4">
    <div class="w-full max-w-md rounded-2xl border border-border bg-card p-8 shadow-card">
        <div class="mb-6 text-center">
            <img src="{{ asset('images/landfocus-logo.png') }}" alt="" class="mx-auto h-12 w-12">
            <h1 class="mt-4 text-2xl font-bold">Land<span class="text-primary">Focus</span> Admin</h1>
            <p class="mt-2 text-sm text-muted-foreground">Ingia ili kusimamia mfumo</p>
        </div>
        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" required autofocus class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                @error('password')<p class="mt-1 text-sm text-destructive">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">Ingia</button>
        </form>
        <p class="mt-6 text-center text-sm"><a href="{{ route('home') }}" class="text-primary hover:underline">← Rudi kwenye tovuti</a></p>
    </div>
</body>
</html>
