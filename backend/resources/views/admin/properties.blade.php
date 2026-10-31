@extends('layouts.admin')

@section('title', 'Miradi — LandFocus Admin')

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">Miradi / Properties</h1>
        <p class="mt-1 text-sm text-muted-foreground">Orodha ya miradi yote katika mfumo. Unaweza pia kuona tovuti ya umma kwa miradi hapa chini.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('projects.index') }}" target="_blank" class="rounded-lg border border-border bg-card px-4 py-2 text-sm text-foreground hover:bg-secondary">Angalia Miradi ya Umma</a>
        <button id="addPropertyBtn" class="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90">
            + Ongeza Miradi
        </button>
    </div>
</div>

<!-- Properties Table -->
<div class="mt-6 overflow-hidden rounded-2xl border border-border bg-card shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="propertiesTable">
            <thead class="border-b border-border bg-secondary/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Jina</th>
                    <th class="px-4 py-3 text-left font-semibold">Aina</th>
                    <th class="px-4 py-3 text-left font-semibold">Mahali</th>
                    <th class="px-4 py-3 text-left font-semibold">Bei</th>
                    <th class="px-4 py-3 text-left font-semibold">Hali</th>
                    <th class="px-4 py-3 text-left font-semibold">Vitendo</th>
                </tr>
            </thead>
            <tbody id="propertiesBody">
                @forelse($properties as $p)
                    <tr class="border-b border-border/60 hover:bg-secondary/20" data-id="{{ $p->id }}">
                        <td class="px-4 py-3"><a href="{{ route('property.detail', $p->id) }}" class="font-medium text-primary hover:underline">{{ $p->title }}</a></td>
                        <td class="px-4 py-3">{{ $p->typeLabel($lang) }}</td>
                        <td class="px-4 py-3">{{ $p->location }}</td>
                        <td class="px-4 py-3">{{ $p->currency }} {{ number_format($p->price) }}</td>
                        <td class="px-4 py-3"><span class="rounded-full bg-secondary px-2 py-0.5 text-xs">{{ $t['status'][$p->status] ?? $p->status }}</span></td>
                        <td class="px-4 py-3 space-x-2">
                            <button class="editBtn text-blue-600 hover:text-blue-800" data-id="{{ $p->id }}">✏️</button>
                            <button class="documentsBtn text-green-600 hover:text-green-800" data-id="{{ $p->id }}">📄</button>
                            <button class="deleteBtn text-red-600 hover:text-red-800" data-id="{{ $p->id }}">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Hakuna miradi bado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Property Modal -->
<div id="propertyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="w-full max-w-2xl rounded-2xl bg-card p-6 shadow-lg max-h-96 overflow-y-auto">
        <h2 class="text-2xl font-bold" id="modalTitle">Ongeza Miradi Mpya</h2>
        <form id="propertyForm" class="mt-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <input type="hidden" id="propertyId">
                
                <input type="text" id="title" placeholder="Jina la Miradi" required class="col-span-2 rounded-lg border border-border bg-input px-3 py-2">
                
                <select id="propertyType" required class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="">Chagua Aina</option>
                    <option value="land">Ardhi</option>
                    <option value="house">Nyumba</option>
                    <option value="farm">Shamba</option>
                    <option value="office">Ofisi</option>
                </select>
                
                <select id="status" required class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="">Chagua Hali</option>
                    <option value="available">Inapatikana</option>
                    <option value="reserved">Imehifadhiwa</option>
                    <option value="sold">Imeuzwa</option>
                    <option value="rented">Inahitajiwa</option>
                    <option value="pending">Inasubiri</option>
                </select>
                
                <input type="text" id="location" placeholder="Mahali" required class="col-span-2 rounded-lg border border-border bg-input px-3 py-2">
                
                <input type="number" id="price" placeholder="Bei" required class="rounded-lg border border-border bg-input px-3 py-2">
                
                <select id="currency" class="rounded-lg border border-border bg-input px-3 py-2">
                    <option value="TZS">TZS</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                </select>
                
                <input type="number" id="areaSqm" placeholder="Eneo (sqm)" class="rounded-lg border border-border bg-input px-3 py-2">
                <input type="number" id="bedrooms" placeholder="Kamar za Kulala" class="rounded-lg border border-border bg-input px-3 py-2">
                <input type="number" id="bathrooms" placeholder="Kamar za Kuogelea" class="rounded-lg border border-border bg-input px-3 py-2">
                
                <textarea id="description" placeholder="Maelezo" class="col-span-2 rounded-lg border border-border bg-input px-3 py-2 h-20"></textarea>
                <textarea id="features" placeholder="Sifa za Miradi" class="col-span-2 rounded-lg border border-border bg-input px-3 py-2 h-20"></textarea>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium">Picha za Miradi (zaidi ya moja)</label>
                    <input type="file" id="propertyImages" accept="image/*" multiple class="mt-2 w-full rounded-lg border border-border bg-input px-3 py-2">
                    <div id="currentImagesPreview" class="mt-4 grid grid-cols-3 gap-3"></div>
                </div>
                
                <label class="flex items-center gap-2 col-span-2">
                    <input type="checkbox" id="featured" class="rounded">
                    <span>Miradi ya Kufuata</span>
                </label>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" id="closeModal" class="rounded-lg border border-border px-4 py-2 hover:bg-secondary">Ghairi</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90">Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<!-- Documents Modal -->
<div id="documentsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="w-full max-w-2xl rounded-2xl bg-card p-6 shadow-lg max-h-96 overflow-y-auto">
        <h2 class="text-2xl font-bold">Nyaraka za Miradi</h2>
        
        <div class="mt-4">
            <h3 class="font-semibold mb-3">Kupakia Nyaraka Mpya</h3>
            <form id="documentForm" class="space-y-3">
                <input type="hidden" id="docPropertyId">
                <input type="text" id="docTitle" placeholder="Jina la Nyaraka" required class="w-full rounded-lg border border-border bg-input px-3 py-2">
                <textarea id="docDescription" placeholder="Maelezo" class="w-full rounded-lg border border-border bg-input px-3 py-2 h-16"></textarea>
                <input type="file" id="docFile" required class="w-full rounded-lg border border-border bg-input px-3 py-2">
                <button type="submit" class="w-full rounded-lg bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90">Pakua Nyaraka</button>
            </form>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold mb-3">Nyaraka Zilizopo</h3>
            <div id="documentsList" class="space-y-2 max-h-40 overflow-y-auto">
                <!-- Documents will be loaded here -->
            </div>
        </div>

        <button type="button" id="closeDocumentsModal" class="mt-6 w-full rounded-lg border border-border px-4 py-2 hover:bg-secondary">Ghairi</button>
    </div>
</div>

<script>
const API_BASE = '/admin/api';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Add CSRF token to all requests
const headers = {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
};
const formHeaders = {
    'X-CSRF-TOKEN': csrfToken,
};

let existingImages = [];

// Modal Controls
document.getElementById('addPropertyBtn').addEventListener('click', () => {
    resetPropertyForm();
    document.getElementById('propertyModal').classList.remove('hidden');
});

document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('propertyModal').classList.add('hidden');
});

document.getElementById('closeDocumentsModal').addEventListener('click', () => {
    document.getElementById('documentsModal').classList.add('hidden');
});

// Edit Button Handler
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('editBtn')) {
        const id = e.target.dataset.id;
        loadPropertyData(id);
    }
});

// Documents Button Handler
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('documentsBtn')) {
        const id = e.target.dataset.id;
        loadDocuments(id);
    }
});

// Delete Button Handler
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('deleteBtn')) {
        const id = e.target.dataset.id;
        if (confirm('Hakika ungependa kufuta miradi hii?')) {
            deleteProperty(id);
        }
    }
});

// Property Form Submit
document.getElementById('propertyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const propertyId = document.getElementById('propertyId').value;
    const url = propertyId ? `${API_BASE}/properties/${propertyId}` : `${API_BASE}/properties`;
    const formData = new FormData();

    formData.append('title', document.getElementById('title').value);
    formData.append('description', document.getElementById('description').value);
    formData.append('price', parseInt(document.getElementById('price').value));
    formData.append('currency', document.getElementById('currency').value);
    formData.append('location', document.getElementById('location').value);
    formData.append('property_type', document.getElementById('propertyType').value);
    formData.append('status', document.getElementById('status').value);
    formData.append('area_sqm', document.getElementById('areaSqm').value || '');
    formData.append('bedrooms', document.getElementById('bedrooms').value || '');
    formData.append('bathrooms', document.getElementById('bathrooms').value || '');
    formData.append('features', document.getElementById('features').value);
    formData.append('featured', document.getElementById('featured').checked ? '1' : '0');

    existingImages.forEach((src) => {
        formData.append('existing_images[]', src);
    });

    const imageFiles = document.getElementById('propertyImages').files;
    for (let i = 0; i < imageFiles.length; i++) {
        formData.append('images[]', imageFiles[i]);
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: formHeaders,
            body: formData,
        });

        const result = await response.json();
        if (result.success) {
            alert(result.message);
            document.getElementById('propertyModal').classList.add('hidden');
            location.reload();
        } else {
            alert('Error: ' + JSON.stringify(result.errors || result.message));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la mtandao: ' + error.message);
    }
});

// Document Form Submit
document.getElementById('documentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const propertyId = document.getElementById('docPropertyId').value;
    const formData = new FormData();
    
    formData.append('title', document.getElementById('docTitle').value);
    formData.append('description', document.getElementById('docDescription').value);
    formData.append('file', document.getElementById('docFile').files[0]);
    formData.append('property_id', propertyId);

    try {
        const response = await fetch(`${API_BASE}/documents`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        const result = await response.json();
        if (result.success) {
            alert(result.message);
            document.getElementById('documentForm').reset();
            loadDocuments(propertyId);
        } else {
            alert('Error uploading document');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la mtandao: ' + error.message);
    }
});

// Load Property Data
async function loadPropertyData(id) {
    try {
        const response = await fetch(`${API_BASE}/properties/${id}`, {
            method: 'GET',
            headers: headers,
        });

        const result = await response.json();
        if (result.success) {
            const p = result.property;
            document.getElementById('propertyId').value = p.id;
            document.getElementById('title').value = p.title;
            document.getElementById('description').value = p.description || '';
            document.getElementById('price').value = p.price;
            document.getElementById('currency').value = p.currency;
            document.getElementById('location').value = p.location;
            document.getElementById('propertyType').value = p.property_type;
            document.getElementById('status').value = p.status;
            document.getElementById('areaSqm').value = p.area_sqm || '';
            document.getElementById('bedrooms').value = p.bedrooms || '';
            document.getElementById('bathrooms').value = p.bathrooms || '';
            document.getElementById('features').value = p.features || '';
            document.getElementById('featured').checked = p.featured;
            existingImages = p.images || [];
            renderPropertyImages();
            document.getElementById('propertyImages').value = '';
            document.getElementById('modalTitle').textContent = 'Hariri Miradi';
            document.getElementById('propertyModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la upakuzi: ' + error.message);
    }
}

// Load Documents
async function loadDocuments(propertyId) {
    try {
        const response = await fetch(`${API_BASE}/properties/${propertyId}/documents`, {
            method: 'GET',
            headers: headers,
        });

        const result = await response.json();
        document.getElementById('docPropertyId').value = propertyId;
        
        const docsList = document.getElementById('documentsList');
        docsList.innerHTML = '';

        if (result.success && result.documents.length > 0) {
            result.documents.forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'flex items-center justify-between p-3 border border-border rounded-lg';
                docElement.innerHTML = `
                    <div>
                        <p class="font-semibold">${doc.title}</p>
                        <p class="text-xs text-muted-foreground">${(doc.file_size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                    <div class="space-x-2">
                        <a href="/storage/${doc.file_path}" target="_blank" class="text-blue-600 hover:text-blue-800">⬇️</a>
                        <button class="deleteDocBtn text-red-600 hover:text-red-800" data-id="${doc.id}">🗑️</button>
                    </div>
                `;
                docsList.appendChild(docElement);
            });
        } else {
            docsList.innerHTML = '<p class="text-center text-muted-foreground">Hakuna nyaraka.</p>';
        }

        document.getElementById('documentsModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la upakuzi: ' + error.message);
    }
}

// Delete Document
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('deleteDocBtn')) {
        const id = e.target.dataset.id;
        if (confirm('Hakika ungependa kufuta nyaraka hii?')) {
            deleteDocument(id);
        }
    }
});

async function deleteDocument(id) {
    try {
        const response = await fetch(`${API_BASE}/documents/${id}`, {
            method: 'DELETE',
            headers: headers,
        });

        const result = await response.json();
        if (result.success) {
            alert(result.message);
            const propertyId = document.getElementById('docPropertyId').value;
            loadDocuments(propertyId);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la kufuta: ' + error.message);
    }
}

// Delete Property
async function deleteProperty(id) {
    try {
        const response = await fetch(`${API_BASE}/properties/${id}`, {
            method: 'DELETE',
            headers: headers,
        });

        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Kosa la kufuta: ' + error.message);
    }
}

// Reset Form
function resetPropertyForm() {
    document.getElementById('propertyForm').reset();
    document.getElementById('propertyId').value = '';
    existingImages = [];
    document.getElementById('currentImagesPreview').innerHTML = '<p class="col-span-3 text-sm text-muted-foreground">Hakuna picha zilizopakiwa.</p>';
    document.getElementById('propertyImages').value = '';
    document.getElementById('modalTitle').textContent = 'Ongeza Miradi Mpya';
}

function renderPropertyImages() {
    const preview = document.getElementById('currentImagesPreview');
    preview.innerHTML = '';

    if (!existingImages.length) {
        preview.innerHTML = '<p class="col-span-3 text-sm text-muted-foreground">Hakuna picha zilizopakiwa.</p>';
        return;
    }

    existingImages.forEach((src, index) => {
        const previewSrc = src.startsWith('http') || src.startsWith('/storage/') ? src : `/storage/${src.replace(/^\//, '')}`;
        const card = document.createElement('div');
        card.className = 'relative overflow-hidden rounded-2xl border border-border bg-background';
        card.innerHTML = `
            <img src="${previewSrc}" alt="Image ${index + 1}" class="h-24 w-full object-cover">
            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-black/60 p-1 text-xs text-white">
                <button type="button" class="moveImageBtn rounded-full bg-white/10 px-2 py-1 hover:bg-white/20" data-index="${index}" data-action="left">◀</button>
                <span class="flex-1 text-center">Picha ${index + 1}</span>
                <button type="button" class="moveImageBtn rounded-full bg-white/10 px-2 py-1 hover:bg-white/20" data-index="${index}" data-action="right">▶</button>
            </div>
            <button type="button" class="removeImageBtn absolute right-2 top-2 rounded-full bg-black/70 px-2 py-1 text-xs text-white" data-index="${index}">Ondoa</button>
        `;
        preview.appendChild(card);
    });
}

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('removeImageBtn')) {
        const index = parseInt(e.target.dataset.index, 10);
        existingImages.splice(index, 1);
        renderPropertyImages();
    }

    if (e.target.classList.contains('moveImageBtn')) {
        const index = parseInt(e.target.dataset.index, 10);
        const action = e.target.dataset.action;
        const targetIndex = action === 'left' ? index - 1 : index + 1;
        if (targetIndex < 0 || targetIndex >= existingImages.length) {
            return;
        }

        const temp = existingImages[targetIndex];
        existingImages[targetIndex] = existingImages[index];
        existingImages[index] = temp;
        renderPropertyImages();
    }
});
</script>
@endsection
