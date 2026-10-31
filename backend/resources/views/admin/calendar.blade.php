@extends('layouts.admin')

@section('title', 'Kalenda — LandFocus Admin')

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">Kalenda na Matukio</h1>
        <p class="mt-1 text-sm text-muted-foreground">Matukio rasmi na matangazo ya LandFocus.</p>
    </div>
    <button id="addEventBtn" class="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90">
        + Ongeza Matukio
    </button>
</div>

<!-- Month Navigation -->
<div class="mt-6 flex items-center justify-between">
    <button id="prevMonth" class="rounded-lg border border-border px-3 py-2 hover:bg-secondary">← Nyuma</button>
    <span id="monthYear" class="text-lg font-semibold"></span>
    <button id="nextMonth" class="rounded-lg border border-border px-3 py-2 hover:bg-secondary">Mbele →</button>
</div>

<!-- Events Grid -->
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" id="eventsGrid">
    <!-- Events will be loaded here -->
</div>

<!-- Add/Edit Event Modal -->
<div id="eventModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="w-full max-w-2xl rounded-2xl bg-card p-6 shadow-lg max-h-96 overflow-y-auto">
        <h2 class="text-2xl font-bold" id="eventModalTitle">Ongeza Matukio Mapya</h2>
        <form id="eventForm" class="mt-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <input type="hidden" id="eventId">
                
                <input type="text" id="eventTitle" placeholder="Jina la Matukio" required class="col-span-2 rounded-lg border border-border bg-input px-3 py-2">
                
                <input type="datetime-local" id="eventStartAt" required class="rounded-lg border border-border bg-input px-3 py-2">
                <input type="datetime-local" id="eventEndAt" class="rounded-lg border border-border bg-input px-3 py-2">
                
                <input type="text" id="eventLocation" placeholder="Mahali" class="col-span-2 rounded-lg border border-border bg-input px-3 py-2">
                
                <select id="eventType" class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="announcement">Tangazo</option>
                    <option value="meeting">Mkutano</option>
                    <option value="event">Matukio</option>
                    <option value="deadline">Muda wa Kumalizia</option>
                    <option value="holiday">Likizo</option>
                </select>

                <select id="eventColor" class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="#3b82f6">🔵 Bluu</option>
                    <option value="#ef4444">🔴 Nyekundu</option>
                    <option value="#10b981">🟢 Kijani</option>
                    <option value="#f59e0b">🟡 Njano</option>
                    <option value="#8b5cf6">🟣 Zambarau</option>
                </select>

                <select id="eventRepeatInterval" class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="none">Haina Rudia</option>
                    <option value="daily">Kila Siku</option>
                    <option value="monthly">Kila Mwezi</option>
                    <option value="yearly">Kila Mwaka</option>
                </select>
                
                <textarea id="eventDescription" placeholder="Maelezo" class="col-span-2 rounded-lg border border-border bg-input px-3 py-2 h-20"></textarea>
                
                <label class="flex items-center gap-2 col-span-2">
                    <input type="checkbox" id="eventIsOfficial" class="rounded">
                    <span>Matukio Rasmi</span>
                </label>

                <div class="col-span-2">
                    <label class="mb-2 block text-sm font-medium text-foreground">Picha / Video za Tukio</label>
                    <input type="file" id="eventMedia" name="media[]" multiple accept="image/*,video/*" class="w-full rounded-lg border border-border bg-input px-3 py-2" />
                    <div id="existingMediaList" class="mt-3 grid gap-3 sm:grid-cols-2"></div>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" id="closeEventModal" class="rounded-lg border border-border px-4 py-2 hover:bg-secondary">Ghairi</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90">Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<script>
const API_BASE = '/admin/api';
const csrfToken = '{{ csrf_token() }}';

let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();

const headers = {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
};

async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const contentType = response.headers.get('content-type') || '';

    if (response.redirected || response.status === 401 || response.status === 403) {
        window.location.href = '/admin/login';
        throw new Error('Session expired. Please log in again.');
    }

    if (!response.ok) {
        const text = await response.text();
        let message = 'The request failed.';

        try {
            const payload = text ? JSON.parse(text) : null;
            message = payload?.message || payload?.errors || message;
        } catch (error) {
            message = text ? text.substring(0, 120) : message;
        }

        throw new Error(message);
    }

    if (!contentType.includes('application/json')) {
        const text = await response.text();

        if (!text.trim()) {
            return { success: true };
        }

        if (text.trim().startsWith('<')) {
            if (response.status === 401 || response.status === 403 || response.redirected) {
                window.location.href = '/admin/login';
            }

            throw new Error('Your session expired or the request returned an HTML response. Please log in again.');
        }

        try {
            return JSON.parse(text);
        } catch (error) {
            if (response.status === 401 || response.status === 403 || response.redirected) {
                window.location.href = '/admin/login';
            }
            throw new Error('Server returned an unexpected response. Please log in again.');
        }
    }

    return response.json();
}

// Initialize
loadEvents();
updateMonthDisplay();

// Month Navigation
document.getElementById('prevMonth').addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
    }
    updateMonthDisplay();
    loadEvents();
});

document.getElementById('nextMonth').addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
    }
    updateMonthDisplay();
    loadEvents();
});

// Add Event Button
document.getElementById('addEventBtn').addEventListener('click', () => {
    resetEventForm();
    document.getElementById('eventModal').classList.remove('hidden');
});

document.getElementById('closeEventModal').addEventListener('click', () => {
    document.getElementById('eventModal').classList.add('hidden');
});

// Event Form Submit
document.getElementById('eventForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const eventId = document.getElementById('eventId').value;
    const url = eventId ? `${API_BASE}/events/${eventId}` : `${API_BASE}/events`;
    const formData = new FormData();

    formData.append('title', document.getElementById('eventTitle').value);
    formData.append('description', document.getElementById('eventDescription').value);
    formData.append('event_type', document.getElementById('eventType').value);
    formData.append('start_at', document.getElementById('eventStartAt').value);
    formData.append('end_at', document.getElementById('eventEndAt').value || '');
    formData.append('location', document.getElementById('eventLocation').value);
    formData.append('is_official', document.getElementById('eventIsOfficial').checked ? '1' : '0');
    formData.append('event_color', document.getElementById('eventColor').value);
    formData.append('repeat_interval', document.getElementById('eventRepeatInterval').value);

    const existingMediaInputs = document.querySelectorAll('#existingMediaList input[name="existing_media[]"]');
    existingMediaInputs.forEach((input) => {
        formData.append('existing_media[]', input.value);
    });

    const files = document.getElementById('eventMedia').files;
    for (let i = 0; i < files.length; i++) {
        formData.append('media[]', files[i]);
    }

    try {
        const result = await fetchJson(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        if (result.success) {
            alert(result.message);
            document.getElementById('eventModal').classList.add('hidden');
            loadEvents();
        } else {
            alert('Error: ' + JSON.stringify(result.errors || result.message));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la mtandao: ' + error.message);
    }
});

// Load Events
async function loadEvents() {
    try {
        const result = await fetchJson(`${API_BASE}/events?month=${currentMonth}&year=${currentYear}`, {
            method: 'GET',
            headers: headers,
        });

        const grid = document.getElementById('eventsGrid');
        grid.innerHTML = '';

        let events = [];
        if (result.success && result.events.length > 0) {
            events = expandRecurringEvents(result.events, currentMonth, currentYear);
        }

        if (events.length > 0) {
            events.forEach(event => {
                const eventElement = createEventCard(event);
                grid.appendChild(eventElement);
            });
        } else {
            grid.innerHTML = '<div class="col-span-full rounded-2xl border border-dashed border-border p-12 text-center text-muted-foreground">Hakuna matukio katika mwezi huu.</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la upakuzi: ' + error.message);
    }
}

function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'rounded-2xl border border-border bg-card p-5 shadow-card';
    card.style.borderLeftColor = event.event_color || '#3b82f6';
    card.style.borderLeftWidth = '4px';
    
    const startDate = new Date(event.start_at);
    
    card.innerHTML = `
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold">${event.title}</h3>
            ${event.is_official ? '<span class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">Rasmi</span>' : ''}
        </div>
        <p class="mt-2 text-sm text-muted-foreground">📅 ${startDate.toLocaleDateString('sw-TZ', { year: 'numeric', month: 'short', day: 'numeric' })} ${startDate.toLocaleTimeString('sw-TZ', { hour: '2-digit', minute: '2-digit' })}</p>
        ${event.location ? `<p class="mt-1 text-sm text-muted-foreground">📍 ${event.location}</p>` : ''}
        ${event.description ? `<p class="mt-3 text-sm">${event.description}</p>` : ''}
        <div class="mt-4 flex gap-2">
            <button class="editEventBtn text-blue-600 hover:text-blue-800" data-id="${event.id}">✏️ Hariri</button>
            <button class="deleteEventBtn text-red-600 hover:text-red-800" data-id="${event.id}">🗑️ Futa</button>
        </div>
    `;

    return card;
}

// Edit Event
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('editEventBtn')) {
        const id = e.target.dataset.id;
        loadEventData(id);
    }
});

// Delete Event
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('deleteEventBtn')) {
        const id = e.target.dataset.id;
        if (confirm('Hakika ungependa kufuta matukio hii?')) {
            deleteEvent(id);
        }
    }
});

async function loadEventData(id) {
    try {
        const result = await fetchJson(`${API_BASE}/events/${id}`, {
            method: 'GET',
            headers: headers,
        });

        if (result.success) {
            const e = result.event;
            document.getElementById('eventId').value = e.id;
            document.getElementById('eventTitle').value = e.title;
            document.getElementById('eventDescription').value = e.description || '';
            document.getElementById('eventType').value = e.event_type;
            document.getElementById('eventLocation').value = e.location || '';
            document.getElementById('eventIsOfficial').checked = e.is_official;
            document.getElementById('eventColor').value = e.event_color || '#3b82f6';
            
            // Format datetime for input
            const startDate = new Date(e.start_at);
            document.getElementById('eventStartAt').value = startDate.toISOString().slice(0, 16);
            
            if (e.end_at) {
                const endDate = new Date(e.end_at);
                document.getElementById('eventEndAt').value = endDate.toISOString().slice(0, 16);
            }
            document.getElementById('eventRepeatInterval').value = e.repeat_interval || 'none';
            
            document.getElementById('eventModalTitle').textContent = 'Hariri Matukio';
            renderExistingMedia(e.media || [], e.media_urls || []);
            document.getElementById('eventModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la upakuzi: ' + error.message);
    }
}

async function deleteEvent(id) {
    try {
        const result = await fetchJson(`${API_BASE}/events/${id}`, {
            method: 'DELETE',
            headers: headers,
        });

        if (result.success) {
            alert(result.message);
            loadEvents();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la kufuta: ' + error.message);
    }
}

function updateMonthDisplay() {
    const monthName = new Date(currentYear, currentMonth - 1).toLocaleDateString('sw-TZ', { month: 'long', year: 'numeric' });
    document.getElementById('monthYear').textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);
}

function resetEventForm() {
    document.getElementById('eventForm').reset();
    document.getElementById('eventId').value = '';
    document.getElementById('eventRepeatInterval').value = 'none';
    document.getElementById('eventModalTitle').textContent = 'Ongeza Matukio Mapya';
    document.getElementById('existingMediaList').innerHTML = '';
    document.getElementById('eventMedia').value = '';
}

function renderExistingMedia(mediaItems, mediaUrls) {
    const container = document.getElementById('existingMediaList');
    container.innerHTML = '';

    if (!mediaItems || mediaItems.length === 0) {
        return;
    }

    mediaItems.forEach((mediaPath, index) => {
        const mediaUrl = mediaUrls[index] || mediaPath;
        const itemWrapper = document.createElement('div');
        itemWrapper.className = 'group rounded-2xl border border-border bg-background p-3';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'existing_media[]';
        input.value = mediaPath;

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = 'Ondoa';
        removeButton.className = 'mt-3 inline-flex items-center rounded-md border border-border px-2 py-1 text-xs text-red-600 hover:bg-red-50';
        removeButton.addEventListener('click', () => {
            itemWrapper.remove();
        });

        if (mediaUrl.match(/\.(mp4|mov|avi|mkv)$/i)) {
            itemWrapper.innerHTML = `<video controls class="h-36 w-full rounded-xl object-cover" src="${mediaUrl}"></video>`;
        } else {
            itemWrapper.innerHTML = `<img class="h-36 w-full rounded-xl object-cover" src="${mediaUrl}" alt="Event media" />`;
        }

        itemWrapper.appendChild(input);
        itemWrapper.appendChild(removeButton);
        container.appendChild(itemWrapper);
    });
}

function expandRecurringEvents(events, month, year) {
    const startOfMonth = new Date(year, month - 1, 1);
    const endOfMonth = new Date(year, month, 0, 23, 59, 59);
    const result = [];

    for (const event of events) {
        const startAt = new Date(event.start_at);
        const repeat = event.repeat_interval || 'none';

        if (repeat === 'none') {
            result.push(event);
            continue;
        }

        const occurrences = [];
        if (repeat === 'daily') {
            const day = new Date(Math.max(startAt, startOfMonth));
            while (day <= endOfMonth) {
                occurrences.push({ ...event, start_at: day.toISOString(), id: `${event.id}-${day.toISOString()}` });
                day.setDate(day.getDate() + 1);
            }
        } else if (repeat === 'monthly') {
            const originalDay = startAt.getDate();
            const occurrence = new Date(year, month - 1, originalDay, startAt.getHours(), startAt.getMinutes(), startAt.getSeconds());
            if (occurrence >= startOfMonth && occurrence <= endOfMonth) {
                occurrences.push({ ...event, start_at: occurrence.toISOString(), id: `${event.id}-${occurrence.toISOString()}` });
            }
        } else if (repeat === 'yearly') {
            const occurrence = new Date(year, startAt.getMonth(), startAt.getDate(), startAt.getHours(), startAt.getMinutes(), startAt.getSeconds());
            if (occurrence >= startOfMonth && occurrence <= endOfMonth) {
                occurrences.push({ ...event, start_at: occurrence.toISOString(), id: `${event.id}-${occurrence.toISOString()}` });
            }
        }

        if (occurrences.length > 0) {
            result.push(...occurrences);
        }
    }

    return result.sort((a, b) => new Date(a.start_at) - new Date(b.start_at));
}
</script>
@endsection
