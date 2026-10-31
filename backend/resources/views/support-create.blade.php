@extends('layouts.app')

@section('title', 'Support — LandFocus')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="mx-auto max-w-3xl rounded-3xl border border-border bg-card p-6 shadow-card sm:p-8">
        <div class="mb-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary">LandFocus support</p>
            <h1 class="mt-3 text-3xl font-bold">Submit a support ticket</h1>
            <p class="mt-2 text-sm text-muted-foreground">Tell us what you need help with and our team will respond as soon as possible.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('support.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium">Full name</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="w-full rounded-xl border border-border bg-background px-4 py-3" placeholder="Your name" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Phone number</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="w-full rounded-xl border border-border bg-background px-4 py-3" placeholder="+255..." required>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Email address</label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="w-full rounded-xl border border-border bg-background px-4 py-3" placeholder="you@example.com" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-xl border border-border bg-background px-4 py-3" placeholder="Need help with a property booking" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Project (optional)</label>
                <select name="project_id" class="w-full rounded-xl border border-border bg-background px-4 py-3">
                    <option value="">No project selected</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Priority</label>
                <select name="priority" class="w-full rounded-xl border border-border bg-background px-4 py-3" required>
                    @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Message</label>
                <textarea name="message" rows="6" class="w-full rounded-xl border border-border bg-background px-4 py-3" placeholder="Describe your issue in detail" required>{{ old('message') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('home') }}" class="rounded-xl border border-border px-4 py-3 text-sm text-muted-foreground hover:bg-secondary">Cancel</a>
                <button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90">Send ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection
