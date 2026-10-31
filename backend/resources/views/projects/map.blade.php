@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $project->title }}</h1>
            <p class="text-sm text-gray-600">{{ $project->location ?? '' }} · Jumla ya viwanja: {{ $project->plots->count() }}</p>
        </div>
        <div>
            <a href="{{ route('projects.export.plots', $project) }}" class="btn">Export CSV</a>
        </div>
    </div>

    <div id="map" style="height: 600px; width: 100%;" class="rounded-lg shadow"></div>

    <template id="plot-popup-template">
        <div>
            <h3 id="p-title"></h3>
            <div id="p-details"></div>
            <form id="reserveForm">
                <input type="text" name="customer_name" placeholder="Your name" required class="border rounded w-full px-2 py-1 mt-2" />
                <input type="text" name="customer_phone" placeholder="Phone (optional)" class="border rounded w-full px-2 py-1 mt-2" />
                <button type="submit" class="mt-2 rounded bg-blue-600 text-white px-4 py-2">Reserve & Pay</button>
            </form>
        </div>
    </template>
</div>

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster-src.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw-src.js"></script>
<script>
const projectId = '{{ $project->id }}';
const map = L.map('map').setView([{{ $project->plots->first()?->coordinates[1] ?? -6.7924 }}, {{ $project->plots->first()?->coordinates[0] ?? 39.2083 }}], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
}).addTo(map);

const markers = L.markerClusterGroup();
map.addLayer(markers);
let plotMarkers = {}; // id -> marker

// Sidebar list rendering
function renderSidebar(plots) {
    const containerId = 'plot-list';
    let sidebar = document.getElementById(containerId);
    if (!sidebar) return;
    sidebar.innerHTML = '';

    plots.forEach(plot => {
        const el = document.createElement('div');
        el.className = 'plot-list-item border-b py-2 px-2 hover:bg-gray-50 cursor-pointer';
        el.dataset.plotId = plot.id;
        el.innerHTML = `<div class=\"font-semibold\">${plot.plot_number || plot.number}</div><div class=\"text-sm text-gray-600\">${plot.status || ''} · ${plot.size_sqm || ''} sqm · ${plot.price_tzs || ''} TZS</div>`;
        el.addEventListener('click', () => {
            const m = plotMarkers[plot.id];
            if (m) {
                map.setView(m.getLatLng(), 17);
                m.openPopup && m.openPopup();
            }
        });
        sidebar.appendChild(el);
    });
}

async function loadPlots(filters = {}) {
    const q = new URLSearchParams(filters).toString();
    const res = await fetch('/api/miradi/' + projectId + '/plots' + (q ? '?' + q : ''));
    const payload = await res.json();
    const plots = payload.plots || [];

    markers.clearLayers();
    plotMarkers = {};

    plots.forEach(plot => {
        if (!plot.coordinates || !Array.isArray(plot.coordinates) || plot.coordinates.length < 2) return;
        const lon = plot.coordinates[0];
        const lat = plot.coordinates[1];
        const color = plot.status === 'paid' ? 'green' : (plot.status === 'booked' ? 'orange' : (plot.status === 'held' ? 'purple' : 'blue'));
        const marker = L.circleMarker([lat, lon], {color: color, radius: 8});

        // create popup content
        const popup = L.popup().setContent(generatePopupHtml(plot));
        marker.bindPopup(popup);
        marker.on('popupopen', () => attachFormHandler(plot, marker));

        markers.addLayer(marker);
        plotMarkers[plot.id] = marker;
    });

    renderSidebar(plots);
}

function generatePopupHtml(plot) {
    return `
        <div>
            <h3>Kiwanja ${plot.plot_number || plot.number}</h3>
            <div>Size: ${plot.size_sqm || 'N/A'} sqm</div>
            <div>Price: ${plot.price_tzs || 'N/A'} TZS</div>
            <div>Status: ${plot.status || 'N/A'}</div>
            <form class="reserve-form mt-2" data-plot-id="${plot.id}">
                <input type="text" name="customer_name" placeholder="Your name" required class="border rounded w-full px-2 py-1 mt-2" />
                <input type="text" name="customer_phone" placeholder="Phone (optional)" class="border rounded w-full px-2 py-1 mt-2" />
                <button type="submit" class="mt-2 rounded bg-blue-600 text-white px-4 py-2">Reserve & Pay</button>
            </form>
        </div>
    `;
}

function attachFormHandler(plot, marker) {
    const popupEl = marker.getPopup().getElement();
    if (!popupEl) return;
    const form = popupEl.querySelector('.reserve-form');
    if (!form) return;
    if (form.__bound) return; // prevent double bind
    form.__bound = true;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const data = Object.fromEntries(fd.entries());
        const resp = await fetch('/plots/' + plot.id + '/reserve', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
            body: new URLSearchParams(data),
        });
        const json = await resp.json();
        if (!json.success) {
            alert(json.message || 'Error reserving');
            return;
        }
        const booking = json.booking;
        // create stripe checkout via AJAX
        const checkout = await fetch('/bookings/' + booking.id + '/stripe-checkout', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'} });
        const cjson = await checkout.json().catch(()=>null);
        if (cjson && cjson.url) {
            window.location = cjson.url;
            return;
        }
        // fallback to redirect response
        if (checkout.redirected) {
            window.location = checkout.url;
            return;
        }
        alert('Failed to create checkout session');
    });
}

// Filters UI
function setupFilters() {
    const filtersForm = document.getElementById('map-filters');
    if (!filtersForm) return;
    filtersForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const fd = new FormData(filtersForm);
        const filters = Object.fromEntries(fd.entries());
        loadPlots(filters);
    });
}

// Admin drawing tools
@if(session()->get('is_admin'))
let drawControl;
map.on('draw:created', function (e) {
    const layer = e.layer;
    const geojson = layer.toGeoJSON();
    // send to server
    fetch('/api/projects/{{ $project->id }}/shapes', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({shape: geojson}),
    }).then(r=>r.json()).then(j=>{
        if (j.success) alert('Shape saved'); else alert('Failed to save shape');
    }).catch(()=>alert('Failed to save shape'));
});
@endif

// init
setupFilters();
loadPlots();

</script>
@endsection

@endsection
