@extends('layouts.admin')

@section('title', 'Beem Taarifa — LandFocus Admin')

@section('content')
<h1 class="text-2xl font-bold">Beem / Bulk Message</h1>
<p class="mt-1 text-sm text-muted-foreground">Tuma arifa za kundi kwa njia ya in-app, SMS, au email. Hii ni sehemu ya admin kwa ujumbe wa BEEM-style.</p>

<div id="notificationFlash" class="mt-4 hidden rounded-2xl border border-border bg-emerald-50 px-4 py-3 text-sm text-emerald-700"></div>

<div class="mt-6 flex flex-wrap items-center gap-3">
    <button id="addNotificationBtn" class="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90">+ Ongeza Taarifa</button>
    <input id="notificationSearch" type="text" placeholder="Tafuta taarifa..." class="min-w-0 flex-1 rounded-lg border border-border bg-input px-3 py-2 text-sm">
</div>

<div id="notificationsGrid" class="mt-6 grid gap-4 lg:grid-cols-2"></div>

<div id="notificationModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 px-4 py-10 sm:px-6">
    <div class="w-full max-w-2xl rounded-2xl bg-card p-6 shadow-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold" id="notificationModalTitle">Ongeza Taarifa</h2>
        <form id="notificationForm" class="mt-6 space-y-4">
            <input type="hidden" id="notificationId">
            <div class="grid gap-4 sm:grid-cols-2">
                <input type="text" id="notificationTitle" placeholder="Kichwa" required class="rounded-lg border border-border bg-input px-3 py-2 col-span-2">
                <textarea id="notificationBody" placeholder="Mwili wa taarifa" required class="col-span-2 rounded-lg border border-border bg-input px-3 py-2 h-28"></textarea>
                <select id="notificationChannel" required class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="in_app">In-App</option>
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                </select>
                <textarea id="notificationRecipients" placeholder="Nambari au barua pepe (kila mstari)" class="col-span-2 rounded-lg border border-border bg-input px-3 py-2 h-28"></textarea>
                <label class="flex items-center gap-2 rounded-lg border border-border bg-input px-3 py-2">
                    <input type="checkbox" id="notificationActive" class="rounded">
                    <span>Inafanya kazi</span>
                </label>
            </div>
            <div class="mt-3 rounded-2xl border border-border bg-slate-50 p-4 text-sm text-muted-foreground">
                <div class="flex items-center justify-between gap-3">
                    <span class="font-medium">Preview ya BEEM Payload</span>
                    <button type="button" id="previewPayloadBtn" class="rounded-lg border border-border px-3 py-1 text-xs hover:bg-secondary">Onyesha/Ficha</button>
                </div>
                <pre id="notificationPayloadPreview" class="mt-3 hidden overflow-x-auto rounded-lg border border-border bg-white p-3 text-xs text-slate-700"></pre>
            </div>
            <div class="flex flex-wrap gap-3 justify-end">
                <button type="button" id="closeNotificationModal" class="rounded-lg border border-border px-4 py-2 hover:bg-secondary">Ghairi</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90">Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<script>
const API_BASE = '/admin/api';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken };
let notifications = [];

async function loadNotifications() {
    const response = await fetch(`${API_BASE}/notifications`, { headers });
    const result = await response.json();
    notifications = result.success ? result.notifications : [];
    renderNotifications(notifications);
}

function renderNotifications(items) {
    const grid = document.getElementById('notificationsGrid');
    grid.innerHTML = '';
    if (!items.length) {
        grid.innerHTML = '<div class="col-span-full rounded-2xl border border-dashed border-border p-12 text-center text-muted-foreground">Hakuna taarifa kwa sasa.</div>';
        return;
    }

    items.forEach(notification => {
        const levelClasses = {
            info: 'bg-blue-100 text-blue-700',
            success: 'bg-emerald-100 text-emerald-700',
            warning: 'bg-amber-100 text-amber-700',
            danger: 'bg-rose-100 text-rose-700',
        };

        grid.innerHTML += `
            <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-foreground">${notification.title}</h3>
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold ${levelClasses[notification.level] || 'bg-secondary text-foreground'}">${notification.level}</span>
                            <span class="inline-flex rounded-full bg-secondary px-2 py-1 text-xs text-muted-foreground">${notification.channel || 'in_app'}</span>
                        </div>
                        <p class="mt-3 text-sm text-muted-foreground">${notification.body}</p>
                        <p class="mt-3 text-xs text-muted-foreground">Created: ${new Date(notification.created_at).toLocaleString('sw-TZ')}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Channel: ${notification.channel || 'in_app'}</p>
                        ${notification.sent_at ? `<p class="mt-1 text-xs text-muted-foreground">Sent: ${new Date(notification.sent_at).toLocaleString('sw-TZ')}</p>` : ''}
                        ${notification.recipients && notification.recipients.length ? `<p class="mt-2 text-xs text-muted-foreground">Receivers: ${notification.recipients.join(', ')}</p>` : ''}
                    </div>
                    <div class="flex gap-2">
                        <button class="editNotificationBtn text-blue-600 hover:text-blue-800" data-id="${notification.id}">✏️ Hariri</button>
                        <button class="deleteNotificationBtn text-red-600 hover:text-red-800" data-id="${notification.id}">🗑️ Futa</button>
                    </div>
                </div>
            </div>
        `;
    });
}

function openNotificationModal(notification = null) {
    document.getElementById('notificationId').value = notification?.id || '';
    document.getElementById('notificationTitle').value = notification?.title || '';
    document.getElementById('notificationBody').value = notification?.body || '';
    document.getElementById('notificationLevel').value = notification?.level || 'info';
    document.getElementById('notificationChannel').value = notification?.channel || 'in_app';
    document.getElementById('notificationRecipients').value = notification?.recipients ? notification.recipients.join('\n') : '';
    document.getElementById('notificationActive').checked = notification ? notification.is_active : true;
    document.getElementById('notificationModalTitle').textContent = notification ? 'Hariri Taarifa' : 'Ongeza Taarifa';
    document.getElementById('notificationModal').classList.remove('hidden');
}

function closeNotificationModal() {
    document.getElementById('notificationModal').classList.add('hidden');
}

async function saveNotification(event) {
    event.preventDefault();
    const id = document.getElementById('notificationId').value;
    const payload = {
        title: document.getElementById('notificationTitle').value,
        body: document.getElementById('notificationBody').value,
        level: document.getElementById('notificationLevel').value,
        channel: document.getElementById('notificationChannel').value,
        recipients: document.getElementById('notificationRecipients').value.split('\n').map(line => line.trim()).filter(Boolean),
        is_active: document.getElementById('notificationActive').checked,
    };
    const url = id ? `${API_BASE}/notifications/${id}` : `${API_BASE}/notifications`;
    const response = await fetch(url, { method: 'POST', headers, body: JSON.stringify(payload) });
    const result = await response.json();
    if (result.success) {
        closeNotificationModal();
        await loadNotifications();
        showNotificationFlash('Taarifa imehifadhiwa kwa mafanikio.', 'success');
    } else {
        showNotificationFlash('Kosa: ' + (result.message || 'Tafadhali jaribu tena.'), 'error');
    }
}

document.getElementById('previewPayloadBtn').addEventListener('click', () => {
    const preview = document.getElementById('notificationPayloadPreview');
    if (preview.classList.contains('hidden')) {
        const payload = {
            title: document.getElementById('notificationTitle').value,
            body: document.getElementById('notificationBody').value,
            channel: document.getElementById('notificationChannel').value,
            recipients: document.getElementById('notificationRecipients').value.split('\n').map(line => line.trim()).filter(Boolean),
        };
        preview.textContent = JSON.stringify(payload, null, 2);
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});

function showNotificationFlash(message, type = 'success') {
    const flash = document.getElementById('notificationFlash');
    flash.textContent = message;
    flash.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'bg-red-50', 'text-red-700');
    if (type === 'success') {
        flash.classList.add('bg-emerald-50', 'text-emerald-700');
    } else {
        flash.classList.add('bg-red-50', 'text-red-700');
    }
    flash.classList.remove('hidden');
    setTimeout(() => flash.classList.add('hidden'), 6000);
}

async function deleteNotification(id) {
    if (!confirm('Hakika ungependa kufuta taarifa hii?')) {
        return;
    }
    await fetch(`${API_BASE}/notifications/${id}`, { method: 'DELETE', headers });
    await loadNotifications();
}

function filterNotifications(query) {
    const filtered = notifications.filter(notification => {
        const text = `${notification.title} ${notification.body}`.toLowerCase();
        return text.includes(query.toLowerCase());
    });
    renderNotifications(filtered);
}

document.getElementById('addNotificationBtn').addEventListener('click', () => openNotificationModal());
document.getElementById('closeNotificationModal').addEventListener('click', closeNotificationModal);
document.getElementById('notificationForm').addEventListener('submit', saveNotification);
document.getElementById('notificationSearch').addEventListener('input', (e) => filterNotifications(e.target.value));

document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editNotificationBtn')) {
        const id = e.target.dataset.id;
        const response = await fetch(`${API_BASE}/notifications/${id}`, { headers });
        const result = await response.json();
        if (result.success) {
            openNotificationModal(result.notification);
        }
    }
    if (e.target.classList.contains('deleteNotificationBtn')) {
        deleteNotification(e.target.dataset.id);
    }
});

loadNotifications();
</script>
@endsection
