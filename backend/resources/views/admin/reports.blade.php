@extends('layouts.admin')

@section('title', 'Ripoti — LandFocus Admin')

@section('content')
<h1 class="text-2xl font-bold">Ripoti</h1>
<p class="mt-1 text-sm text-muted-foreground">Muhtasari wa shughuli za mfumo.</p>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        ['label' => 'Jumla ya Miradi', 'value' => $stats['properties']],
        ['label' => 'Inapatikana', 'value' => $stats['available']],
        ['label' => 'Bookings', 'value' => $stats['bookings']],
        ['label' => 'Malipo', 'value' => $stats['payments']],
        ['label' => 'Mapato (TZS)', 'value' => number_format($stats['revenue'])],
        ['label' => 'Maombi', 'value' => $stats['inquiries']],
        ['label' => 'Miadi', 'value' => $stats['appointments']],
        ['label' => 'Kazi', 'value' => $stats['tasks']],
        ['label' => 'Taarifa zinazoendelea', 'value' => $stats['notifications']],
    ] as $s)
        <div class="rounded-2xl border border-border bg-card p-5 shadow-card">
            <p class="text-sm text-muted-foreground">{{ $s['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-primary">{{ $s['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 rounded-2xl border border-border bg-card p-6 shadow-card">
    <h2 class="text-xl font-semibold">Export Ripoti ya Kazi</h2>
    <p class="mt-2 text-sm text-muted-foreground">Pakia ripoti za kazi kwa mwezi huu au mwezi uliotolewa.</p>

    <div class="mt-4 grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <label class="block">
            <span class="text-sm text-muted-foreground">Mwaka</span>
            <select id="exportYear" class="mt-2 w-full rounded-lg border border-border bg-input px-3 py-2">
            </select>
        </label>
        <label class="block">
            <span class="text-sm text-muted-foreground">Mwezi</span>
            <select id="exportMonth" class="mt-2 w-full rounded-lg border border-border bg-input px-3 py-2">
            </select>
        </label>
        <label class="block">
            <span class="text-sm text-muted-foreground">Ripoti</span>
            <select id="exportReportCategory" class="mt-2 w-full rounded-lg border border-border bg-input px-3 py-2">
                <option value="tasks">Kazi</option>
                <option value="bookings">Bookings</option>
                <option value="payments">Malipo</option>
            </select>
        </label>
        <label class="block">
            <span class="text-sm text-muted-foreground">Aina</span>
            <select id="exportType" class="mt-2 w-full rounded-lg border border-border bg-input px-3 py-2">
                <option value="csv">CSV</option>
                <option value="pdf">PDF</option>
            </select>
        </label>
        <div class="flex items-end">
            <button id="exportReportBtn" class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Pakua Ripoti</button>
        </div>
    </div>
</div>

<script>
function populateExportSelectors() {
    const yearSelect = document.getElementById('exportYear');
    const monthSelect = document.getElementById('exportMonth');
    const today = new Date();
    const currentYear = today.getFullYear();

    for (let year = currentYear; year >= currentYear - 2; year -= 1) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === currentYear) option.selected = true;
        yearSelect.appendChild(option);
    }

    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    monthNames.forEach((month, index) => {
        const option = document.createElement('option');
        option.value = index + 1;
        option.textContent = `${month} (${index + 1})`;
        if (index === today.getMonth()) option.selected = true;
        monthSelect.appendChild(option);
    });
}

function downloadTaskReport() {
    const year = document.getElementById('exportYear').value;
    const month = document.getElementById('exportMonth').value;
    const type = document.getElementById('exportType').value;
    const report = document.getElementById('exportReportCategory').value;
    const url = `${location.origin}/admin/api/reports/export?report=${encodeURIComponent(report)}&type=${encodeURIComponent(type)}&year=${encodeURIComponent(year)}&month=${encodeURIComponent(month)}`;
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', () => {
    populateExportSelectors();
    document.getElementById('exportReportBtn').addEventListener('click', downloadTaskReport);
});
</script>
@endsection
