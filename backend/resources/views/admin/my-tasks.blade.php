@extends('layouts.admin')

@section('title', 'Kazi Zangu — LandFocus Admin')

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">Kazi Zangu</h1>
        <p class="mt-1 text-sm text-muted-foreground">Tengeneza, badilisha na maliza kazi kwa admin panel.</p>
    </div>
    <button id="addTaskBtn" class="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90">+ Ongeza Kazi</button>
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-3" id="taskBoard">
    <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="mb-4 text-lg font-semibold">Pending</h2>
        <div id="pendingTasks" class="space-y-4"></div>
    </div>
    <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="mb-4 text-lg font-semibold">In Progress</h2>
        <div id="inProgressTasks" class="space-y-4"></div>
    </div>
    <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="mb-4 text-lg font-semibold">Completed</h2>
        <div id="completedTasks" class="space-y-4"></div>
    </div>
</div>

<div id="taskModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-2xl rounded-2xl bg-card p-6 shadow-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold" id="taskModalTitle">Ongeza Kazi Mpya</h2>
        <form id="taskForm" class="mt-6 grid gap-4">
            <input type="hidden" id="taskId">
            <input type="text" id="taskTitle" placeholder="Kichwa cha kazi" required class="rounded-lg border border-border bg-input px-3 py-2">
            <textarea id="taskDescription" placeholder="Maelezo ya kazi" class="h-28 rounded-lg border border-border bg-input px-3 py-2"></textarea>
            <div class="grid gap-4 sm:grid-cols-2">
                <input type="datetime-local" id="taskDueDate" class="rounded-lg border border-border bg-input px-3 py-2">
                <input type="text" id="taskAssignedTo" placeholder="Iliyotolewa kwa (simu au jina)" class="rounded-lg border border-border bg-input px-3 py-2">
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <select id="taskPriority" class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
                <select id="taskStatus" class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                <label class="flex items-center gap-2 rounded-lg border border-border bg-input px-3 py-2">
                    <input type="checkbox" id="taskCompleted" class="rounded">
                    <span>Imetekelezwa</span>
                </label>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="closeTaskModal" class="rounded-lg border border-border px-4 py-2 hover:bg-secondary">Ghairi</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90">Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<script>
const API_BASE = '/admin/api';
const csrfToken = '{{ csrf_token() }}';

const headers = {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
};

const taskBoard = {
    pending: document.getElementById('pendingTasks'),
    in_progress: document.getElementById('inProgressTasks'),
    completed: document.getElementById('completedTasks'),
};

let currentTaskId = null;

window.addEventListener('DOMContentLoaded', () => {
    loadTasks();
});

document.getElementById('addTaskBtn').addEventListener('click', () => {
    openTaskModal();
});

document.getElementById('closeTaskModal').addEventListener('click', () => {
    document.getElementById('taskModal').classList.add('hidden');
});

document.getElementById('taskForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    await saveTask();
});

async function loadTasks() {
    const response = await fetch(`${API_BASE}/tasks`, { headers });
    const result = await response.json();

    if (!result.success) {
        alert('Kosa kupakia kazi');
        return;
    }

    Object.values(taskBoard).forEach(container => container.innerHTML = '');

    result.tasks.forEach(task => {
        const card = createTaskCard(task);
        taskBoard[task.status || 'pending'].appendChild(card);
    });
}

function createTaskCard(task) {
    const card = document.createElement('div');
    card.className = 'rounded-2xl border border-border bg-background p-4 shadow-sm';
    card.innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="font-semibold">${task.title}</h3>
                <p class="mt-2 text-sm text-muted-foreground">${task.description || 'Hakuna maelezo'}</p>
            </div>
            <span class="rounded-full bg-secondary px-2 py-1 text-xs">${task.priority}</span>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
            ${task.due_date ? `<span>Due: ${new Date(task.due_date).toLocaleString()}</span>` : ''}
            ${task.assigned_to ? `<span>Assigned: ${task.assigned_to}</span>` : ''}
            <span>${task.completed ? 'Imetekelezwa' : 'Haijakamilika'}</span>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <button class="editTaskBtn rounded-lg border border-border px-3 py-1 text-sm hover:bg-secondary" data-id="${task.id}">Edit</button>
            <button class="deleteTaskBtn rounded-lg border border-border px-3 py-1 text-sm text-red-600 hover:bg-red-100" data-id="${task.id}">Delete</button>
            ${task.status !== 'completed' ? `<button class="completeTaskBtn rounded-lg bg-primary px-3 py-1 text-sm text-primary-foreground hover:bg-primary/90" data-id="${task.id}">Mark Done</button>` : ''}
        </div>
    `;
    return card;
}

document.addEventListener('click', async (event) => {
    if (event.target.matches('.editTaskBtn')) {
        const id = event.target.dataset.id;
        await editTask(id);
    }

    if (event.target.matches('.deleteTaskBtn')) {
        const id = event.target.dataset.id;
        if (confirm('Unataka kufuta kazi hii?')) {
            await deleteTask(id);
        }
    }

    if (event.target.matches('.completeTaskBtn')) {
        const id = event.target.dataset.id;
        await updateTaskStatus(id, 'completed', true);
    }
});

async function editTask(id) {
    const response = await fetch(`${API_BASE}/tasks/${id}`, { headers });
    const result = await response.json();

    if (!result.success) {
        alert('Kosa kupakia kazi');
        return;
    }

    const task = result.task;
    currentTaskId = task.id;
    document.getElementById('taskId').value = task.id;
    document.getElementById('taskTitle').value = task.title;
    document.getElementById('taskDescription').value = task.description || '';
    document.getElementById('taskDueDate').value = task.due_date ? new Date(task.due_date).toISOString().slice(0, 16) : '';
    document.getElementById('taskAssignedTo').value = task.assigned_to || '';
    document.getElementById('taskPriority').value = task.priority;
    document.getElementById('taskStatus').value = task.status;
    document.getElementById('taskCompleted').checked = task.completed;
    document.getElementById('taskModalTitle').textContent = 'Hariri Kazi';
    document.getElementById('taskModal').classList.remove('hidden');
}

async function saveTask() {
    const id = document.getElementById('taskId').value;
    const url = id ? `${API_BASE}/tasks/${id}` : `${API_BASE}/tasks`;
    const method = 'POST';

    const payload = {
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDescription').value,
        due_date: document.getElementById('taskDueDate').value || null,
        priority: document.getElementById('taskPriority').value,
        status: document.getElementById('taskStatus').value,
        assigned_to: document.getElementById('taskAssignedTo').value || null,
        completed: document.getElementById('taskCompleted').checked,
    };

    const response = await fetch(url, {
        method: method,
        headers: headers,
        body: JSON.stringify(payload),
    });

    const result = await response.json();
    if (result.success) {
        document.getElementById('taskModal').classList.add('hidden');
        document.getElementById('taskForm').reset();
        document.getElementById('taskId').value = '';
        currentTaskId = null;
        await loadTasks();
    } else {
        alert('Kosa: ' + JSON.stringify(result.errors || result.message));
    }
}

async function deleteTask(id) {
    const response = await fetch(`${API_BASE}/tasks/${id}`, {
        method: 'DELETE',
        headers: headers,
    });
    const result = await response.json();
    if (result.success) {
        await loadTasks();
    }
}

async function updateTaskStatus(id, status, completed = false) {
    const response = await fetch(`${API_BASE}/tasks/${id}`, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ status, completed }),
    });
    const result = await response.json();
    if (result.success) {
        await loadTasks();
    }
}
</script>
@endsection
