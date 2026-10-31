@extends('layouts.admin')

@section('title', 'Plots za Mradi — LandFocus Admin')

@section('content')
<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Plots za Mradi</h1>
        <p class="mt-1 text-sm text-muted-foreground">Manage plot inventory for {{ $project->title }}.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.project.plots.export', $project) }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">Export CSV</a>
        <button id="addPlotBtn" class="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90">+ Ongeza Plot</button>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="plotsTable">
            <thead class="border-b border-border bg-secondary/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Plot</th>
                    <th class="px-4 py-3 text-left font-semibold">Size (sqm)</th>
                    <th class="px-4 py-3 text-left font-semibold">Bei (TZS)</th>
                    <th class="px-4 py-3 text-left font-semibold">Hali</th>
                    <th class="px-4 py-3 text-left font-semibold">Bookings</th>
                    <th class="px-4 py-3 text-left font-semibold">Vitendo</th>
                </tr>
            </thead>
            <tbody id="plotsBody">
                @forelse($plots as $plot)
                <tr class="border-b border-border/60 hover:bg-secondary/20" data-id="{{ $plot->id }}">
                    <td class="px-4 py-3">{{ $plot->number }}</td>
                    <td class="px-4 py-3">{{ $plot->size_sqm }}</td>
                    <td class="px-4 py-3">{{ number_format($plot->price_tzs) }}</td>
                    <td class="px-4 py-3">{{ ucfirst($plot->status) }}</td>
                    <td class="px-4 py-3">{{ $plot->bookings_count }}</td>
                    <td class="px-4 py-3 space-x-2">
                        <button class="editPlotBtn text-blue-600 hover:text-blue-800" data-id="{{ $plot->id }}">✏️</button>
                        <button class="deletePlotBtn text-red-600 hover:text-red-800" data-id="{{ $plot->id }}">🗑️</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Hakuna plots kwa mradi huu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="plotModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="w-full max-w-2xl rounded-2xl bg-card p-6 shadow-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold" id="plotModalTitle">Ongeza Plot Mpya</h2>
        <form id="plotForm" class="mt-6 space-y-4">
            <input type="hidden" id="plotId">
            <div class="grid grid-cols-2 gap-4">
                <input type="text" id="plotNumber" placeholder="Plot Number" required class="col-span-2 rounded-lg border border-border bg-input px-3 py-2">
                <input type="number" id="plotSize" placeholder="Size (sqm)" required class="rounded-lg border border-border bg-input px-3 py-2">
                <input type="number" id="plotPrice" placeholder="Price (TZS)" required class="rounded-lg border border-border bg-input px-3 py-2">
                <select id="plotStatus" required class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="open">Open</option>
                    <option value="booked">Booked</option>
                    <option value="paid">Paid</option>
                    <option value="reserved">Reserved</option>
                </select>
                <textarea id="plotCoordinates" placeholder="Coordinates (JSON or comma-separated)" class="col-span-2 rounded-lg border border-border bg-input px-3 py-2 h-24"></textarea>
                <textarea id="plotNotes" placeholder="Notes" class="col-span-2 rounded-lg border border-border bg-input px-3 py-2 h-24"></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" id="cancelPlotBtn" class="rounded-lg border border-border px-4 py-2 hover:bg-secondary">Ghairi</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90">Hifadhi Plot</button>
            </div>
        </form>
    </div>
</div>

<script>
const projectId = '{{ $project->id }}';
const projectPlotsApi = `/admin/api/projects/${projectId}/plots`;
const headersJson = {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
};

const plotModal = document.getElementById('plotModal');
const plotForm = document.getElementById('plotForm');

function openPlotModal(edit = false, plot = null) {
    document.getElementById('plotModalTitle').textContent = edit ? 'Hariri Plot' : 'Ongeza Plot Mpya';
    document.getElementById('plotId').value = edit ? plot.id : '';
    document.getElementById('plotNumber').value = edit ? plot.number : '';
    document.getElementById('plotSize').value = edit ? plot.size_sqm : '';
    document.getElementById('plotPrice').value = edit ? plot.price_tzs : '';
    document.getElementById('plotStatus').value = edit ? plot.status : 'open';
    document.getElementById('plotCoordinates').value = edit && plot.coordinates ? JSON.stringify(plot.coordinates) : '';
    document.getElementById('plotNotes').value = edit ? plot.notes || '' : '';
    plotModal.classList.remove('hidden');
}

function closePlotModal() {
    plotModal.classList.add('hidden');
}

document.getElementById('addPlotBtn').addEventListener('click', () => openPlotModal(false));
document.getElementById('cancelPlotBtn').addEventListener('click', closePlotModal);

document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('editPlotBtn')) {
        const plotId = e.target.dataset.id;
        const response = await fetch(`${projectPlotsApi}/${plotId}`, { headers: headersJson });
        const result = await response.json();
        if (result.success) {
            openPlotModal(true, result.plot);
        } else {
            alert('Kosa: haikuweza kupakia plot.');
        }
    }
    if (e.target.classList.contains('deletePlotBtn')) {
        const plotId = e.target.dataset.id;
        if (!confirm('Hakuna uhakika? Futa plot hii?')) return;
        await deletePlot(plotId);
    }
});

async function deletePlot(plotId) {
    const response = await fetch(`${projectPlotsApi}/${plotId}`, {
        method: 'DELETE',
        headers: headersJson,
    });
    const result = await response.json();
    if (result.success) {
        location.reload();
    } else {
        alert('Kosa: ' + (result.message || 'Haikuweza kufuta.'));
    }
}

plotForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const plotId = document.getElementById('plotId').value;
    const body = {
        number: document.getElementById('plotNumber').value,
        size_sqm: document.getElementById('plotSize').value,
        price_tzs: document.getElementById('plotPrice').value,
        status: document.getElementById('plotStatus').value,
        coordinates: document.getElementById('plotCoordinates').value,
        notes: document.getElementById('plotNotes').value,
    };
    const url = plotId ? `${projectPlotsApi}/${plotId}` : projectPlotsApi;
    const response = await fetch(url, {
        method: 'POST',
        headers: headersJson,
        body: JSON.stringify(body),
    });
    const result = await response.json();
    if (result.success) {
        location.reload();
    } else {
        alert('Kosa: ' + (result.message || JSON.stringify(result.errors)));
    }
});
</script>
@endsection