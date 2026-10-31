@extends('layouts.app')

@section('title', 'Msaidizi wa LandFocus — Uliza Maswali')

@section('content')
<main class="mx-auto flex w-full max-w-3xl flex-1 flex-col px-4 py-8 sm:px-6">
    <div class="mb-6 flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl brand-gradient text-white text-2xl">🤖</div>
        <div>
            <h1 class="text-2xl font-bold text-foreground">{{ $lang === 'en' ? 'LandFocus Assistant' : 'Msaidizi wa LandFocus' }}</h1>
            <p class="text-sm text-muted-foreground">{{ $lang === 'en' ? 'Ask questions about our services' : 'Uliza maswali kuhusu huduma zetu' }}</p>
        </div>
    </div>

    <div class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-card">
        <div class="flex-1 space-y-4 overflow-y-auto p-4 sm:p-6" style="min-height:320px;max-height:55vh">
            <div class="flex justify-start">
                <div class="max-w-[85%] rounded-2xl bg-secondary px-4 py-3 text-sm text-foreground">
                    {{ $lang === 'en'
                        ? 'Hello! I can help with questions about LandFocus services, properties, and how to submit requests. Try one of the suggestions below or type your question.'
                        : 'Habari! Ninaweza kukusaidia kuhusu huduma za LandFocus, miradi, na jinsi ya kutuma ombi. Jaribu mojawapo ya mapendekezo hapa chini au andika swali lako.' }}
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('assistant.ask') }}" class="border-t border-border p-4">
            @csrf
            <div class="flex gap-2">
                <textarea name="question" rows="2" required placeholder="{{ $lang === 'en' ? 'Type your question...' : 'Andika swali lako...' }}" class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm">{{ old('question') }}</textarea>
                <button type="submit" class="inline-flex shrink-0 items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">→</button>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach(($lang === 'en'
                    ? ['What services do you offer?', 'How do I submit a request?', 'Where is your office?']
                    : ['Huduma zenu ni zipi?', 'Nawezaje kutuma ombi?', 'Ofisi yenu iko wapi?']) as $s)
                    <button type="submit" name="question" value="{{ $s }}" class="rounded-full border border-border px-3 py-1 text-xs text-muted-foreground hover:bg-muted">{{ $s }}</button>
                @endforeach
            </div>
        </form>
    </div>

    @if(!empty($answer))
        <div class="mt-4 rounded-2xl border border-border bg-secondary/30 p-4 text-sm">{{ $answer }}</div>
    @endif
</main>
@endsection
