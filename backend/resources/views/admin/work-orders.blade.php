@extends('layouts.admin')

@section('title', 'Mfuatilio wa Kazi — LandFocus Admin')

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">Mfuatilio wa Kazi</h1>
        <p class="mt-1 text-sm text-muted-foreground">Ondoa kazi kwa hatua, badili hali na maliza kazi kwa admin.</p>
    </div>
    <button id="refreshWorkflow" class="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90">Reload Workflow</button>
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="mb-4 text-lg font-semibold">Pending</h2>
        <div id="workflowPending" class="space-y-4"></div>
    </div>
    <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="mb-4 text-lg font-semibold">In Progress</h2>
        <div id="workflowInProgress" class="space-y-4"></div>
    </div>
    <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="mb-4 text-lg font-semibold">Completed</h2>
        <div id="workflowCompleted" class="space-y-4"></div>
    </div>
</div>

<script>
const API_BASE = '/admin/api';
const csrfToken = '{{ csrf_token() }}';
const headers = {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
};

let workersList = [];

const workflowContainers = {
    pending: document.getElementById('workflowPending'),
    in_progress: document.getElementById('workflowInProgress'),
    completed: document.getElementById('workflowCompleted'),
};

window.addEventListener('DOMContentLoaded', loadWorkflow);
document.getElementById('refreshWorkflow').addEventListener('click', loadWorkflow);

async function loadWorkflow() {
    await loadWorkers();

    const response = await fetch(`${API_BASE}/tasks`, { headers });
    const result = await response.json();
    if (!result.success) {
        alert('Kosa kupakia workflow');
        return;
    }

    Object.values(workflowContainers).forEach(container => container.innerHTML = '');

    result.tasks.forEach(task => {
        const card = document.createElement('div');
        card.className = 'rounded-2xl border border-border bg-background p-4 shadow-sm';
        card.innerHTML = `
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h3 class="font-semibold">${task.title}</h3>
                    <p class="text-sm text-muted-foreground">${task.assigned_to || 'Haijakabidhiwa'}</p>
                </div>
                <span class="rounded-full bg-secondary px-2 py-1 text-xs">${task.priority}</span>
            </div>
            <p class="mt-2 text-sm text-muted-foreground">${task.description || 'Hakuna maelezo'}</p>
            <div class="mt-3 grid gap-2 text-xs text-muted-foreground sm:grid-cols-2">
                ${task.due_date ? `<span>Due: ${new Date(task.due_date).toLocaleString()}</span>` : ''}
                <span>${task.completed ? 'Imetekelezwa' : task.status.replace('_', ' ')}</span>
                <span>${task.worker ? `Worker: ${task.worker.name}` : 'Mfanyakazi haijawekwa'}</span>
                ${task.approved ? '<span class="rounded-full bg-emerald-100 px-2 py-1 text-xs text-emerald-700">Approved</span>' : ''}
            </div>
            <div class="mt-3">
                <label class="mb-1 block text-xs text-muted-foreground" for="worker-${task.id}">Chagua Mfanyakazi</label>
                <select id="worker-${task.id}" data-task-id="${task.id}" data-status="${task.status}" data-completed="${task.completed}" class="assignWorkerSelect w-full rounded-lg border border-border bg-input px-3 py-2 text-sm">
                    ${renderWorkerOptions(task.worker?.id)}
                </select>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                ${task.status !== 'completed' ? `<button class="markDoneBtn rounded-lg bg-primary px-3 py-1 text-sm text-primary-foreground hover:bg-primary/90" data-id="${task.id}">Mark Done</button>` : ''}
                ${task.status === 'pending' ? `<button class="moveBtn rounded-lg border border-border px-3 py-1 text-sm" data-id="${task.id}" data-status="in_progress">Start</button>` : ''}
                ${task.status === 'in_progress' ? `<button class="moveBtn rounded-lg border border-border px-3 py-1 text-sm" data-id="${task.id}" data-status="pending">Back</button>` : ''}
                ${task.status === 'completed' && !task.approved ? `<button class="approveTaskBtn rounded-lg bg-emerald-500 px-3 py-1 text-sm text-white hover:bg-emerald-600" data-id="${task.id}">Approve</button>` : ''}
            </div>
        `;
        workflowContainers[task.status || 'pending'].appendChild(card);
    });
}

document.addEventListener('click', async (event) => {
    if (event.target.matches('.markDoneBtn')) {
        const id = event.target.dataset.id;
        await updateTaskStatus(id, 'completed', true);
    }

    if (event.target.matches('.moveBtn')) {
        const id = event.target.dataset.id;
        const status = event.target.dataset.status;
        await updateTaskStatus(id, status, status === 'completed');
    }

    if (event.target.matches('.approveTaskBtn')) {
        const id = event.target.dataset.id;
        await updateTaskStatus(id, 'completed', true, true);
    }
});

document.addEventListener('change', async (event) => {
    if (event.target.matches('.assignWorkerSelect')) {
        const taskId = event.target.dataset.taskId;
        const status = event.target.dataset.status || 'pending';
        const completed = event.target.dataset.completed === 'true';
        const workerId = event.target.value || null;
        await updateTaskStatus(taskId, status, completed, false, workerId);
    }
});

async function updateTaskStatus(id, status, completed = false, approved = false, workerId = null) {
    const response = await fetch(`${API_BASE}/tasks/${id}`, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ status, completed, approved, approved_by: approved ? 'Admin' : null, worker_id: workerId }),
    });
    const result = await response.json();
    if (result.success) {
        await loadWorkflow();
    } else {
        alert('Kosa kubadilisha hali ya kazi');
    }
}

async function loadWorkers() {
    const response = await fetch(`${API_BASE}/workers`, { headers });
    const result = await response.json();
    if (result.success) {
        workersList = result.workers;
    } else {
        workersList = [];
    }
}

function renderWorkerOptions(selectedId = null) {
    let options = '<option value="">Chagua Mfanyakazi</option>';
    workersList.forEach(worker => {
        const selected = worker.id === selectedId ? ' selected' : '';
        options += `<option value="${worker.id}"${selected}>${worker.name}${worker.role ? ` (${worker.role})` : ''}</option>`;
    });
    return options;
}
</script>
@endsection
