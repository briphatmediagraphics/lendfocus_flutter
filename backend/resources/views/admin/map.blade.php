@extends('layouts.admin')

@section('title', 'Admin Map')

@section('content')
<h1 class="text-2xl font-bold">Admin Map — {{ $project->title ?? 'Select project' }}</h1>

<div class="flex gap-4 mt-4">
    <div id="map" style="height:600px; flex:1;"></div>
    <div id="shapes-panel" style="width:360px; max-height:600px; overflow:auto; background:#fff; padding:12px; border:1px solid #ddd; border-radius:6px;">
        <h3 class="text-lg font-semibold">Saved Shapes</h3>
        <div id="shapes-list">Loading…</div>
    </div>
</div>

<!-- load Leaflet and plugins from CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([ -6.7924, 39.2083 ], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);
    var drawControl = new L.Control.Draw({ edit: { featureGroup: drawnItems } });
    map.addControl(drawControl);

    map.on(L.Draw.Event.CREATED, function (e) {
        var layer = e.layer;
        var geojson = layer.toGeoJSON();

    fetch('{{ route('projects.shapes.save', ['project' => $project->id ?? '']) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ shape: geojson })
    }).then(r => r.json()).then(j => {
        if (j.success) {
            if (typeof showToast === 'function') showToast('success', 'Shape saved'); else alert('Shape saved');
            loadShapes();
        } else if (typeof showToast === 'function') showToast('error', 'Save failed'); else alert('Save failed');
    }).catch(err => { if (typeof showToast === 'function') showToast('error', 'Save error'); else alert('Save error'); });

    drawnItems.addLayer(layer);
    });

    // Load plots for project via existing endpoint if available
    @if(isset($project))
    fetch('{{ url('/api/miradi') }}/' + '{{ $project->id }}' + '/plots')
        .then(r => r.json())
        .then(data => {
            data.forEach(function(plot) {
                if (plot.coords && (plot.coords.lat || plot.coords.lng || plot.coords.lon)) {
                    var lat = plot.coords.lat || plot.coords[1] || plot.coords[0];
                    var lng = plot.coords.lng || plot.coords.lon || plot.coords[0] || plot.coords[1];
                    L.circleMarker([lat, lng], { radius:6 }).addTo(map).bindPopup(plot.code || 'Plot');
                }
            });
        }).catch(()=>{});
    @endif

    // Shapes sidebar load
    function loadShapes() {
        var list = document.getElementById('shapes-list');
        list.innerHTML = 'Loading…';

        fetch('{{ url('/api/projects') }}/' + '{{ $project->id ?? "" }}' + '/shapes')
            .then(r => r.json())
            .then(j => {
                if (! j.success) { list.innerHTML = 'Unauthorized or no shapes.'; return; }
                var html = '';
                if (!j.shapes || j.shapes.length === 0) html = '<div>No shapes saved</div>';
                else {
                    j.shapes.forEach(function(s, idx){
                        html += '<div style="border-bottom:1px solid #eee;padding:8px;">';
                        html += '<div style="font-size:12px;color:#666">Shape #' + idx + '</div>';
                        html += '<pre style="font-size:11px;max-height:120px;overflow:auto">' + JSON.stringify(s, null, 2) + '</pre>';
                        html += '<div style="margin-top:6px"><button data-index="'+idx+'" class="delete-shape px-2 py-1 bg-red-600 text-white rounded">Delete</button> <button data-index="'+idx+'" class="zoom-shape px-2 py-1 bg-blue-600 text-white rounded">Zoom</button></div>';
                        html += '</div>';
                    });
                }
                list.innerHTML = html;

                // Attach handlers
                document.querySelectorAll('.delete-shape').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        var idx = btn.getAttribute('data-index');
                        window.openConfirmModal('Delete shape', 'Delete shape #' + idx + '?', function(){
                            fetch('{{ url('/projects') }}/' + '{{ $project->id ?? "" }}' + '/shapes/' + idx, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            }).then(r => r.json()).then(res => {
                                if (res.success) {
                                    if (typeof showToast === 'function') showToast('success', 'Shape deleted'); else alert('Shape deleted');
                                    loadShapes();
                                } else {
                                    if (typeof showToast === 'function') showToast('error', res.message || 'Delete failed'); else alert(res.message || 'Delete failed');
                                }
                            }).catch(err => { if (typeof showToast === 'function') showToast('error', 'Delete failed'); else alert('Delete failed'); });
                        });
                    });
                });

                document.querySelectorAll('.zoom-shape').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        var idx = parseInt(btn.getAttribute('data-index'));
                        var s = j.shapes[idx];
                        if (!s) return;
                        try {
                            var gj = s;
                            var layer = L.geoJSON(gj);
                            drawnItems.clearLayers();
                            drawnItems.addLayer(layer);
                            map.fitBounds(layer.getBounds());
                        } catch(e){ alert('Cannot zoom to shape'); }
                    });
                });
            }).catch(()=>{ list.innerHTML = 'Failed to load shapes'; });
    }

    // load initially
    @if(isset($project))
    loadShapes();
    @endif

});
</script>

@endsection
