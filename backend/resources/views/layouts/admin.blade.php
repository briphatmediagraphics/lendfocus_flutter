<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin — LandFocus')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Optional: Tailwind Play CDN for quick prototyping (uncomment if you don't use compiled app.css) -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
</head>
<body class="min-h-screen bg-secondary/30 text-foreground antialiased">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-r border-border bg-card md:flex md:flex-col">
            <a href="{{ route('home') }}" class="flex items-center gap-2 border-b border-border px-6 py-4">
                <img src="{{ asset('images/landfocus-logo.png') }}" alt="" class="h-8 w-8">
                <span class="font-bold">Land<span class="text-primary">Focus</span></span>
            </a>
            <nav class="flex-1 space-y-1 p-3">
                @foreach($adminNav ?? [] as $item)
                    <a href="{{ $item['url'] }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm {{ request()->is(ltrim($item['match'], '/')) ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}">
                        <span class="text-base">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-border p-3">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-secondary hover:text-foreground">
                        <span>↩</span> Toka
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1">
            <div class="border-b border-border bg-card px-4 py-3 md:hidden">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="font-bold">Land<span class="text-primary">Focus</span></a>
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="text-sm text-muted-foreground">Toka</button></form>
                </div>
                <nav class="mt-3 flex gap-1 overflow-x-auto">
                    @foreach($adminNav ?? [] as $item)
                        <a href="{{ $item['url'] }}" class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs {{ request()->is(ltrim($item['match'], '/')) ? 'bg-primary text-primary-foreground' : 'text-muted-foreground' }}">{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            </div>

            <div class="p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-4 rounded-2xl border border-border bg-card px-4 py-3 text-sm">{{ session('success') }}</div>
                @endif

                <div class="mb-6 rounded-2xl border border-border bg-secondary p-4 text-sm text-muted-foreground">
                    <p>Logged in as: <span class="font-semibold text-foreground">admin</span></p>
                    <p class="mt-2">Admin anaweza kusimamia timu, miradi, ripoti na maudhui ya mfumo.</p>
                </div>

                @yield('content')

                <!-- Toast container for session messages (uses Alpine for auto-dismiss) -->
                @if(session('success') || session('error'))
                    <script>
                        window._sessionSuccess = {!! json_encode(session('success')) !!};
                        window._sessionError = {!! json_encode(session('error')) !!};
                    </script>
                @else
                    <script>
                        window._sessionSuccess = null;
                        window._sessionError = null;
                    </script>
                @endif

                @yield('content')

                <!-- Confirmation modal (Flowbite/Alpine compatible) -->
                <div id="confirmModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full">
                    <div class="relative p-4 w-full max-w-md h-full md:h-auto mx-auto mt-24">
                        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <div class="p-4 text-center">
                                <h3 id="confirmModalTitle" class="text-lg font-medium text-gray-900">Confirm</h3>
                                <p id="confirmModalBody" class="text-sm text-gray-500 mt-2">Are you sure?</p>
                                <div class="mt-4 flex justify-center gap-2">
                                    <button id="confirmModalCancel" type="button" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
                                    <button id="confirmModalConfirm" type="button" class="px-4 py-2 bg-red-600 text-white rounded">Confirm</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Toast container -->
                <div id="toastContainer" class="fixed right-4 bottom-6 z-50 space-y-3"></div>

                <script>
                    const csrfToken = '{{ csrf_token() }}';

                    function showToast(type, message) {
                        const container = document.getElementById('toastContainer');
                        if (!container) return;
                        const id = 'toast-' + Date.now();
                        const colors = {
                            success: 'bg-green-600',
                            error: 'bg-red-600',
                            info: 'bg-blue-600'
                        };
                        const el = document.createElement('div');
                        el.id = id;
                        el.className = colors[type] + ' text-white max-w-sm rounded-lg px-4 py-3 shadow-lg';
                        el.innerHTML = '<div class="font-semibold">' + (type === 'success' ? 'Success' : (type === 'error' ? 'Error' : 'Info')) + '</div>' + '<div class="text-sm mt-1">' + message + '</div>';
                        container.appendChild(el);
                        setTimeout(() => { el.remove(); }, 4000);
                    }

                    // Confirmation modal helper
                    window.openConfirmModal = function(title, body, onConfirm) {
                        const modal = document.getElementById('confirmModal');
                        const titleEl = document.getElementById('confirmModalTitle');
                        const bodyEl = document.getElementById('confirmModalBody');
                        const btnConfirm = document.getElementById('confirmModalConfirm');
                        const btnCancel = document.getElementById('confirmModalCancel');

                        titleEl.textContent = title || 'Confirm';
                        bodyEl.textContent = body || 'Are you sure?';

                        modal.classList.remove('hidden');

                        function cleanup() {
                            modal.classList.add('hidden');
                            btnConfirm.removeEventListener('click', confirmHandler);
                            btnCancel.removeEventListener('click', cancelHandler);
                        }

                        function confirmHandler() { try { onConfirm(); } catch(e){} cleanup(); }
                        function cancelHandler() { cleanup(); }

                        btnConfirm.addEventListener('click', confirmHandler);
                        btnCancel.addEventListener('click', cancelHandler);
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        // Show server-side session toasts
                        if (window._sessionSuccess) showToast('success', window._sessionSuccess);
                        if (window._sessionError) showToast('error', window._sessionError);

                        // intercept forms with class 'confirmable'
                        document.querySelectorAll('form.confirmable').forEach(function(form){
                            form.addEventListener('submit', function(e){
                                const msg = form.getAttribute('data-confirm-message') || 'Are you sure?';
                                const title = form.getAttribute('data-confirm-title') || 'Confirm action';
                                e.preventDefault();
                                window.openConfirmModal(title, msg, function(){
                                    // submit via fetch to allow JSON response handling
                                    const action = form.action;
                                    const method = (form.method || 'POST').toUpperCase();
                                    const formData = new FormData(form);
                                    const headers = { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };
                                    fetch(action, { method: method, headers: headers, body: formData })
                                        .then(r => r.json())
                                        .then(json => {
                                            if (json.success) {
                                                showToast('success', json.message || 'Action completed');
                                                setTimeout(()=> location.reload(), 800);
                                            } else {
                                                showToast('error', json.message || 'Action failed');
                                            }
                                        }).catch(err => { showToast('error', 'Request failed'); });
                                });
                            });
                        });
                    });
                </script>
            </div>
        </main>
    </div>

    <!-- Alpine for small interactivity and Flowbite components -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/flowbite@1.6.4/dist/flowbite.min.js"></script>
</body>
</html>
