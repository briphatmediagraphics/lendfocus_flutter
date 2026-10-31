<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Booking;
use App\Models\CalendarEvent;
use App\Models\Contract;
use App\Models\Document;
use App\Models\GalleryPhoto;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\Project;
use App\Models\Property;
use App\Models\Staff;
use App\Models\SupportTicket;
use App\Models\Task;
use App\Models\Testimonial;
use App\Models\Worker;
use App\Models\SystemNotification;
use App\Services\BeemService;
use App\Services\SupabaseService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AdminController extends Controller
{
    /** @return array<int, array{url: string, label: string, match: string, icon: string}> */
    protected function nav(): array
    {
        $t = config('translations.' . session('lang', 'sw') . '.admin', []);

        return [
            ['url' => route('admin.dashboard'), 'label' => 'Dashboard', 'match' => 'admin', 'icon' => '📊'],
            ['url' => route('admin.properties'), 'label' => $t['properties'] ?? 'Miradi', 'match' => 'admin/properties*', 'icon' => '🏠'],
            ['url' => route('admin.calendar'), 'label' => $t['calendar'] ?? 'Kalenda', 'match' => 'admin/calendar*', 'icon' => '📅'],
            ['url' => route('admin.inquiries'), 'label' => $t['inquiries'] ?? 'Maombi', 'match' => 'admin/inquiries*', 'icon' => '💬'],
            ['url' => route('admin.support-tickets'), 'label' => $t['supportTickets'] ?? 'Support Tickets', 'match' => 'admin/support-tickets*', 'icon' => '🎫'],
            ['url' => route('admin.work-orders'), 'label' => $t['workOrders'] ?? 'Mfuatilio wa Kazi', 'match' => 'admin/work-orders*', 'icon' => '🔀'],
            ['url' => route('admin.team'), 'label' => $t['team'] ?? 'Timu', 'match' => 'admin/team*', 'icon' => '👤'],
            ['url' => route('admin.workers'), 'label' => $t['workers'] ?? 'Wafanyakazi', 'match' => 'admin/workers*', 'icon' => '👥'],
            ['url' => route('admin.users'), 'label' => 'Watumiaji', 'match' => 'admin/users*', 'icon' => '👥'],
            ['url' => route('admin.wanunuzi'), 'label' => 'Wanunuzi', 'match' => 'admin/wanunuzi*', 'icon' => '🧑‍🤝‍🧑'],
            ['url' => route('admin.contracts'), 'label' => $t['contracts'] ?? 'Mikataba', 'match' => 'admin/contracts*', 'icon' => '📄'],
            ['url' => route('admin.reports'), 'label' => $t['reports'] ?? 'Ripoti', 'match' => 'admin/reports*', 'icon' => '📈'],
            ['url' => route('admin.my-tasks'), 'label' => $t['myTasks'] ?? 'Kazi Zangu', 'match' => 'admin/my-tasks*', 'icon' => '✅'],
            ['url' => route('admin.notifications'), 'label' => $t['notifications'] ?? 'Taarifa', 'match' => 'admin/notifications*', 'icon' => '🔔'],
        ];
    }

    protected function adminView(string $view, array $data = []): View
    {
        return view($view, array_merge($data, ['adminNav' => $this->nav()]));
    }

    public function dashboard()
    {
        $stats = [
            'properties' => $this->safeCount(Property::class, 'properties'),
            'sold' => $this->safeCount(Property::class, 'properties', fn ($query) => $query->where('status', 'sold')),
            'inquiries' => $this->safeCount(Inquiry::class, 'inquiries'),
            'newInquiries' => $this->safeCount(Inquiry::class, 'inquiries', fn ($query) => $query->where('status', 'new')),
            'appointments' => $this->safeCount(Appointment::class, 'appointments'),
        ];

        return $this->adminView('admin.dashboard', compact('stats'));
    }

    public function properties()
    {
        $properties = $this->safeGet(Property::class, 'properties', fn ($query) => $query->orderByDesc('created_at'));

        return $this->adminView('admin.properties', compact('properties'));
    }

    public function listProperties(): JsonResponse
    {
        if (! Schema::hasTable('properties')) {
            return response()->json(['success' => true, 'properties' => []]);
        }

        try {
            $properties = Property::orderByDesc('created_at')->get();
        } catch (Throwable) {
            $properties = collect();
        }

        return response()->json(['success' => true, 'properties' => $properties]);
    }

    public function projectPlots($projectId)
    {
        $project = Project::findOrFail($projectId);
        $plots = $project->plots()->withCount('bookings')->orderBy('number')->get();

        return $this->adminView('admin.project-plots', compact('project', 'plots'));
    }

    public function supportTickets()
    {
        $tickets = $this->safeGet(SupportTicket::class, 'support_tickets', fn ($query) => $query->with(['user', 'project', 'assignedTo'])->orderByDesc('created_at'));

        return $this->adminView('admin.support-tickets', compact('tickets'));
    }

    public function supportTicketDetail(SupportTicket $supportTicket)
    {
        $supportTicket->load(['user', 'project', 'assignedTo']);

        return $this->adminView('admin.support-ticket-detail', compact('supportTicket'));
    }

    public function updateSupportTicket(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,pending,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'admin_notes' => 'nullable|string',
        ]);

        $supportTicket->update(array_merge($validated, [
            'last_replied_at' => now(),
        ]));

        return back()->with('success', 'Support ticket updated successfully.');
    }

    public function listProjectPlots($projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        try {
            $plots = $project->plots()->withCount('bookings')->orderBy('number')->get();
        } catch (Throwable) {
            $plots = collect();
        }

        return response()->json(['success' => true, 'plots' => $plots]);
    }

    public function storeProjectPlot(Request $request, $projectId): JsonResponse
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'number' => 'required|string|max:255',
            'size_sqm' => 'required|numeric|min:0',
            'price_tzs' => 'required|numeric|min:0',
            'status' => 'required|in:open,booked,paid,reserved',
            'coordinates' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $plot = $project->plots()->create([
            'number' => $validated['number'],
            'size_sqm' => $validated['size_sqm'],
            'price_tzs' => $validated['price_tzs'],
            'status' => $validated['status'],
            'coordinates' => $validated['coordinates'] ? json_decode($validated['coordinates'], true) ?? explode(',', $validated['coordinates']) : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'plot' => $plot, 'message' => 'Plot created successfully']);
    }

    public function updateProjectPlot(Request $request, $projectId, $plotId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $plot = $project->plots()->findOrFail($plotId);

        $validated = $request->validate([
            'number' => 'required|string|max:255',
            'size_sqm' => 'required|numeric|min:0',
            'price_tzs' => 'required|numeric|min:0',
            'status' => 'required|in:open,booked,paid,reserved',
            'coordinates' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $plot->update([
            'number' => $validated['number'],
            'size_sqm' => $validated['size_sqm'],
            'price_tzs' => $validated['price_tzs'],
            'status' => $validated['status'],
            'coordinates' => $validated['coordinates'] ? json_decode($validated['coordinates'], true) ?? explode(',', $validated['coordinates']) : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'plot' => $plot, 'message' => 'Plot updated successfully']);
    }

    public function getProjectPlot($projectId, $plotId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $plot = $project->plots()->findOrFail($plotId);

        return response()->json(['success' => true, 'plot' => $plot]);
    }

    public function deleteProjectPlot($projectId, $plotId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $plot = $project->plots()->findOrFail($plotId);
        $plot->delete();

        return response()->json(['success' => true, 'message' => 'Plot deleted successfully']);
    }

    public function exportProjectPlots($projectId)
    {
        $project = Project::findOrFail($projectId);

        $filename = 'project_' . $project->id . '_plots_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($project) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['plot_id', 'number', 'status', 'price_tzs', 'size_sqm', 'position_x', 'position_y']);

            $project->plots()->orderBy('number')->chunk(200, function ($plots) use ($handle) {
                foreach ($plots as $p) {
                    fputcsv($handle, [$p->id, $p->number, $p->status, $p->price_tzs, $p->size_sqm, $p->position_x ?? '', $p->position_y ?? '']);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function inquiries()
    {
        $inquiries = $this->safeGet(Inquiry::class, 'inquiries', fn ($query) => $query->with('property')->orderByDesc('created_at'));

        return $this->adminView('admin.inquiries', compact('inquiries'));
    }

    public function calendar()
    {
        $events = $this->safeGet(CalendarEvent::class, 'calendar_events', fn ($query) => $query->orderBy('start_at'));

        return $this->adminView('admin.calendar', compact('events'));
    }

    public function myTasks()
    {
        $tasks = $this->safeGet(Task::class, 'tasks', fn ($query) => $query->orderBy('due_date'));

        return $this->adminView('admin.my-tasks', compact('tasks'));
    }

    public function notifications()
    {
        $notifications = $this->safeGet(SystemNotification::class, 'system_notifications', fn ($query) => $query->where('is_active', true)->orderByDesc('created_at'));

        return $this->adminView('admin.notifications', compact('notifications'));
    }

    // ============ USERS / BUYERS MANAGEMENT ============

    public function users()
    {
        if (! 
            Schema::hasTable('users')
        ) {
            $users = collect();
        } else {
            $users = \App\Models\User::orderBy('created_at', 'desc')->get();
        }

        return $this->adminView('admin.users', compact('users'));
    }

    public function exportUsers()
    {
        if (! Schema::hasTable('users')) {
            return response()->json(['success' => true, 'csv' => '']);
        }

        $filename = 'users_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'email', 'role', 'created_at']);

            \App\Models\User::orderBy('id')->chunk(200, function ($users) use ($handle) {
                foreach ($users as $u) {
                    fputcsv($handle, [$u->id, $u->name, $u->email, $u->role ?? '', $u->created_at]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function updateUserRole(Request $request, $userId)
    {
        $request->validate(['role' => 'required|string']);

        $user = \App\Models\User::findOrFail($userId);
        $user->role = $request->input('role');
        $user->save();

        $this->syncSupabase('users', $user->toArray(), $user->id);

        return back()->with('success', 'User role updated');
    }

    public function deleteUser($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $user->delete();
        $this->deleteSupabase('users', $userId);

        return back()->with('success', 'User deleted');
    }

    public function editUser($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        return $this->adminView('admin.user-edit', compact('user'));
    }

    public function updateUser(Request $request, $userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'role' => 'nullable|string',
        ]);

        $user->update($validated);
        $this->syncSupabase('users', $user->toArray(), $user->id);

        return redirect()->route('admin.users')->with('success', 'User updated');
    }

    public function buyers()
    {
        if (! Schema::hasTable('users')) {
            $buyers = collect();
        } else {
            // buyers are users with a role 'buyer' or customers
            $buyers = \App\Models\User::where('role', 'buyer')->orderByDesc('created_at')->get();
        }

        return $this->adminView('admin.wanunuzi', compact('buyers'));
    }

    public function exportBuyers()
    {
        if (! Schema::hasTable('users')) {
            return response()->json(['success' => true, 'csv' => '']);
        }

        $filename = 'buyers_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'email', 'phone', 'created_at']);

            \App\Models\User::where('role', 'buyer')->orderBy('id')->chunk(200, function ($users) use ($handle) {
                foreach ($users as $u) {
                    fputcsv($handle, [$u->id, $u->name, $u->email, $u->phone ?? '', $u->created_at]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function workOrders()
    {
        return $this->adminView('admin.work-orders');
    }

    public function team()
    {
        return $this->adminView('admin.team');
    }

    public function workers()
    {
        $workers = $this->safeGet(Worker::class, 'workers', fn ($query) => $query->orderBy('name'));
        return $this->adminView('admin.workers', compact('workers'));
    }

    public function contracts()
    {
        $contracts = $this->safeGet(Contract::class, 'contracts', fn ($query) => $query->orderByDesc('created_at'));
        return $this->adminView('admin.contracts', compact('contracts'));
    }

    public function reports()
    {
        $stats = [
            'properties' => $this->safeCount(Property::class, 'properties'),
            'available' => $this->safeCount(Property::class, 'properties', fn ($query) => $query->where('status', 'available')),
            'bookings' => $this->safeCount(Booking::class, 'bookings'),
            'payments' => $this->safeCount(Payment::class, 'payments'),
            'revenue' => $this->safeSum(Payment::class, 'payments', 'amount_tzs', fn ($query) => $query->where('status', 'verified')),
            'inquiries' => $this->safeCount(Inquiry::class, 'inquiries'),
            'appointments' => $this->safeCount(Appointment::class, 'appointments'),
            'tasks' => $this->safeCount(Task::class, 'tasks'),
            'notifications' => $this->safeCount(SystemNotification::class, 'system_notifications', fn ($query) => $query->where('is_active', true)),
        ];

        return $this->adminView('admin.reports', compact('stats'));
    }

    private function supabase(): SupabaseService
    {
        return new SupabaseService();
    }

    private function safeGet(string $model, string $table, ?callable $callback = null): mixed
    {
        if (! Schema::hasTable($table)) {
            return collect();
        }

        try {
            $query = $model::query();

            if ($callback) {
                $callback($query);
            }

            return $query->get();
        } catch (Throwable) {
            return collect();
        }
    }

    private function safeCreate(string $table, callable $callback): mixed
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        try {
            return $callback();
        } catch (Throwable) {
            return null;
        }
    }

    private function safeUpdate(string $table, callable $callback): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        try {
            $callback();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function safeDelete(string $table, callable $callback): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        try {
            $callback();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function safeCount(string $model, string $table, ?callable $callback = null): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        try {
            $query = $model::query();

            if ($callback) {
                $callback($query);
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeSum(string $model, string $table, string $column, ?callable $callback = null): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        try {
            $query = $model::query();

            if ($callback) {
                $callback($query);
            }

            return (int) $query->sum($column);
        } catch (Throwable) {
            return 0;
        }
    }

    private function syncSupabase(string $table, array $data, ?string $id = null): void
    {
        $supabase = $this->supabase();
        if (! $supabase->isConfigured()) {
            return;
        }

        if ($id === null) {
            $supabase->insert($table, $data);
            return;
        }

        $supabase->update($table, $data, 'id=eq.' . $id);
    }

    private function deleteSupabase(string $table, string $id): void
    {
        $supabase = $this->supabase();
        if (! $supabase->isConfigured()) {
            return;
        }

        $supabase->delete($table, 'id=eq.' . $id);
    }

    // ============ PROPERTIES CRUD ============

    public function storeProperty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|integer',
            'currency' => 'string',
            'location' => 'required|string',
            'property_type' => 'required|in:land,house,farm,office',
            'status' => 'required|in:available,reserved,sold,rented,pending',
            'area_sqm' => 'nullable|integer',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'features' => 'nullable|string',
            'featured' => 'boolean',
            'existing_images' => 'array',
            'existing_images.*' => 'string',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $property = Property::create(collect($validated)->except(['images', 'existing_images'])->all());
        $validated['images'] = $this->savePropertyImages($request, $validated['existing_images'] ?? [], [], $property->id);

        $property->forceFill(['images' => $validated['images']])->save();
        $this->syncSupabase('properties', $property->toArray());

        return response()->json(['success' => true, 'property' => $property, 'message' => 'Property created successfully']);
    }

    public function updateProperty(Request $request, $id): JsonResponse
    {
        $property = Property::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|integer',
            'currency' => 'string',
            'location' => 'required|string',
            'property_type' => 'required|in:land,house,farm,office',
            'status' => 'required|in:available,reserved,sold,rented,pending',
            'area_sqm' => 'nullable|integer',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'features' => 'nullable|string',
            'featured' => 'boolean',
            'existing_images' => 'array',
            'existing_images.*' => 'string',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $validated['images'] = $this->savePropertyImages($request, $validated['existing_images'] ?? [], $property->images ?? [], $property->id);

        $property->update($validated);
        $this->syncSupabase('properties', $property->toArray(), $property->id);

        return response()->json(['success' => true, 'property' => $property, 'message' => 'Property updated successfully']);
    }

    protected function imageStorageDirectory(?string $propertyId = null): string
    {
        $base = 'property_images';

        return $propertyId ? $base . '/' . $propertyId : $base;
    }

    protected function eventMediaDirectory(?string $eventId = null): string
    {
        $base = 'event_media';

        return $eventId ? $base . '/' . $eventId : $base;
    }

    private function savePropertyImages(Request $request, array $existingImages, array $previousImages = [], ?string $propertyId = null): array
    {
        $images = array_values(array_filter(array_unique($existingImages)));
        $directory = $this->imageStorageDirectory($propertyId ?? $request->input('property_id'));

        if (! empty($previousImages)) {
            $removed = array_diff($previousImages, $images);
            foreach ($removed as $removedPath) {
                if (! str_starts_with($removedPath, 'http') && Storage::disk('public')->exists($removedPath)) {
                    Storage::disk('public')->delete($removedPath);
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if (! $file->isValid()) {
                    continue;
                }

                $fileName = uniqid('property_', true) . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs($directory, $fileName, 'public');
                $this->mirrorToPublicStorage($filePath);
                $images[] = $filePath;
            }
        }

        return array_values(array_filter($images));
    }

    private function saveEventMedia(Request $request, array $existingMedia, array $previousMedia = [], ?string $eventId = null): array
    {
        $media = array_values(array_filter(array_unique($existingMedia)));
        $directory = $this->eventMediaDirectory($eventId ?? $request->input('event_id'));

        if (! empty($previousMedia)) {
            $removed = array_diff($previousMedia, $media);
            foreach ($removed as $removedPath) {
                if (! str_starts_with($removedPath, 'http') && Storage::disk('public')->exists($removedPath)) {
                    Storage::disk('public')->delete($removedPath);
                }
            }
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                if (! $file->isValid()) {
                    continue;
                }

                $fileName = uniqid('event_', true) . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs($directory, $fileName, 'public');
                $this->mirrorToPublicStorage($filePath);
                $media[] = $filePath;
            }
        }

        return array_values(array_filter($media));
    }

    public function deleteProperty($id): JsonResponse
    {
        $property = Property::findOrFail($id);
        $property->delete();
        $this->deleteSupabase('properties', $id);

        return response()->json(['success' => true, 'message' => 'Property deleted successfully']);
    }

    public function getProperty($id): JsonResponse
    {
        $property = Property::with('documentFiles')->findOrFail($id);

        return response()->json(['success' => true, 'property' => $property]);
    }

    // ============ CALENDAR EVENTS CRUD ============

    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'event_type' => 'string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date',
            'location' => 'nullable|string',
            'is_official' => 'boolean',
            'event_color' => 'nullable|regex:/^#[0-9a-f]{6}$/i',
            'repeat_interval' => 'required|in:none,daily,monthly,yearly',
            'existing_media' => 'array',
            'existing_media.*' => 'string',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,mkv|max:20480',
        ]);

        if ($validated['repeat_interval'] !== 'none' && empty($validated['repeat_group_id'])) {
            $validated['repeat_group_id'] = Str::uuid();
        }

        $event = CalendarEvent::create($validated);
        $validated['media'] = $this->saveEventMedia($request, $validated['existing_media'] ?? [], [], $event->id);
        $event->forceFill(['media' => $validated['media']])->save();

        $this->syncSupabase('calendar_events', $event->toArray());

        return response()->json(['success' => true, 'event' => $event, 'message' => 'Event created successfully']);
    }

    public function updateEvent(Request $request, $id): JsonResponse
    {
        $event = CalendarEvent::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'event_type' => 'string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date',
            'location' => 'nullable|string',
            'is_official' => 'boolean',
            'event_color' => 'nullable|regex:/^#[0-9a-f]{6}$/i',
            'repeat_interval' => 'required|in:none,daily,monthly,yearly',
            'existing_media' => 'array',
            'existing_media.*' => 'string',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,mkv|max:20480',
        ]);

        if ($validated['repeat_interval'] !== 'none' && empty($event->repeat_group_id)) {
            $validated['repeat_group_id'] = Str::uuid();
        }

        if ($validated['repeat_interval'] === 'none') {
            $validated['repeat_group_id'] = null;
        }

        $validated['media'] = $this->saveEventMedia($request, $validated['existing_media'] ?? [], $event->media ?? [], $event->id);
        $event->update($validated);
        $this->syncSupabase('calendar_events', $event->toArray(), $event->id);

        return response()->json(['success' => true, 'event' => $event, 'message' => 'Event updated successfully']);
    }

    public function deleteEvent($id): JsonResponse
    {
        $event = CalendarEvent::findOrFail($id);
        Storage::disk('public')->deleteDirectory($this->eventMediaDirectory($event->id));
        $event->delete();
        $this->deleteSupabase('calendar_events', $id);

        return response()->json(['success' => true, 'message' => 'Event deleted successfully']);
    }

    public function getEvent($id): JsonResponse
    {
        $event = CalendarEvent::findOrFail($id);

        $eventData = $event->toArray();
        $eventData['media_urls'] = $event->mediaUrls();

        return response()->json(['success' => true, 'event' => $eventData]);
    }

    public function listEvents(Request $request): JsonResponse
    {
        $query = CalendarEvent::query();

        if ($request->has('month') && $request->has('year')) {
            $month = (int) $request->input('month');
            $year = (int) $request->input('year');
            $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

            $query->where(function ($subQuery) use ($month, $year, $endOfMonth) {
                $subQuery->whereYear('start_at', $year)
                         ->whereMonth('start_at', $month);
            })
            ->orWhere(function ($subQuery) use ($endOfMonth) {
                $subQuery->whereIn('repeat_interval', ['daily', 'monthly', 'yearly'])
                         ->where('start_at', '<=', $endOfMonth);
            });
        }

        $events = $query->orderBy('start_at')->get();

        return response()->json(['success' => true, 'events' => $events]);
    }

    // ============ DOCUMENTS UPLOAD ============

    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'property_id' => 'nullable|uuid|exists:properties,id',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'public');
        $this->mirrorToPublicStorage($filePath);

        $document = Document::create([
            'property_id' => $request->input('property_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file_path' => $filePath,
            'file_type' => $this->getFileType($file->getClientOriginalExtension()),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => 'admin',
        ]);

        $this->syncSupabase('documents', $document->toArray());

        if ($request->input('property_id')) {
            $property = Property::find($request->input('property_id'));
            if ($property) {
                $docs = $property->documents ?? [];
                $docs[] = ['id' => $document->id, 'title' => $document->title];
                $property->update(['documents' => $docs]);
                $this->syncSupabase('properties', $property->toArray(), $property->id);
            }
        }

        return response()->json([
            'success' => true,
            'document' => $document,
            'message' => 'Document uploaded successfully',
        ]);
    }

    public function deleteDocument($id): JsonResponse
    {
        $document = Document::findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        $this->deleteSupabase('documents', $id);

        return response()->json(['success' => true, 'message' => 'Document deleted successfully']);
    }

    public function getPropertyDocuments($propertyId): JsonResponse
    {
        $documents = Document::where('property_id', $propertyId)->get();

        return response()->json(['success' => true, 'documents' => $documents]);
    }

    // ============ INQUIRIES MANAGEMENT ============

    public function updateInquiry(Request $request, $id): JsonResponse
    {
        $inquiry = Inquiry::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:new,contacted,closed',
            'notes' => 'nullable|string',
        ]);

        $inquiry->update($validated);
        $this->syncSupabase('inquiries', $inquiry->toArray(), $inquiry->id);

        return response()->json(['success' => true, 'inquiry' => $inquiry, 'message' => 'Inquiry updated successfully']);
    }

    public function deleteInquiry($id): JsonResponse
    {
        $inquiry = Inquiry::findOrFail($id);
        $inquiry->delete();
        $this->deleteSupabase('inquiries', $id);

        return response()->json(['success' => true, 'message' => 'Inquiry deleted successfully']);
    }

    // ============ TASKS CRUD ============

    public function listTasks(): JsonResponse
    {
        $tasks = Task::with('worker')->orderBy('due_date')->get();

        return response()->json(['success' => true, 'tasks' => $tasks]);
    }

    public function getTask($id): JsonResponse
    {
        $task = Task::with('worker')->findOrFail($id);

        return response()->json(['success' => true, 'task' => $task]);
    }

    public function storeTask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:pending,in_progress,completed',
            'assigned_to' => 'nullable|string',
            'worker_id' => 'nullable|uuid|exists:workers,id',
            'completed' => 'boolean',
            'approved' => 'boolean',
            'approved_by' => 'nullable|string',
        ]);

        $validated['completed'] = $request->boolean('completed');
        $validated['approved'] = $request->boolean('approved');
        $validated['completed_at'] = $validated['completed'] ? now() : null;
        $validated['approved_at'] = $validated['approved'] ? now() : null;

        $task = Task::create($validated);
        $this->syncSupabase('tasks', $task->toArray(), $task->id);

        return response()->json(['success' => true, 'task' => $task, 'message' => 'Task created successfully']);
    }

    public function exportTaskReport(Request $request)
    {
        $report = $request->query('report', 'tasks');
        $type = $request->query('type', 'csv');
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        if ($report === 'bookings') {
            $items = Booking::with(['plot', 'user'])
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->orderBy('created_at')
                ->get();
            $filenameBase = 'booking-report';
        } elseif ($report === 'payments') {
            $items = Payment::with(['booking.plot', 'user'])
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->orderBy('paid_at')
                ->get();
            $filenameBase = 'payment-report';
        } else {
            $items = Task::with('worker')
                ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
                ->orderBy('due_date')
                ->get();
            $filenameBase = 'task-report';
        }

        if ($type === 'pdf') {
            $filename = sprintf('%s-%s-%s_%s.pdf', $filenameBase, $year, str_pad((string) $month, 2, '0', STR_PAD_LEFT), now()->format('Ymd_His'));
            if ($report === 'bookings') {
                $pdfContent = $this->buildBookingReportPdf($items->toArray(), $month, $year);
            } elseif ($report === 'payments') {
                $pdfContent = $this->buildPaymentReportPdf($items->toArray(), $month, $year);
            } else {
                $pdfContent = $this->buildTaskReportPdf($items->toArray(), $month, $year);
            }

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = sprintf('%s-%s-%s_%s.csv', $filenameBase, $year, str_pad((string) $month, 2, '0', STR_PAD_LEFT), now()->format('Ymd_His'));
        $callback = function () use ($report, $items) {
            $handle = fopen('php://output', 'w');

            if ($report === 'bookings') {
                fputcsv($handle, ['Control Number', 'Plot', 'Customer Name', 'Phone', 'Email', 'Price (TZS)', 'Status', 'Total Paid', 'Remaining Balance', 'Created At']);
                foreach ($items as $booking) {
                    fputcsv($handle, [
                        $booking->control_number,
                        optional($booking->plot)->number,
                        $booking->customer_name,
                        $booking->customer_phone,
                        optional($booking->user)->email,
                        $booking->price_tzs,
                        $booking->status,
                        $booking->total_paid ?? 0,
                        $booking->remaining_balance ?? 0,
                        $booking->created_at?->format('Y-m-d H:i') ?? '',
                    ]);
                }
            } elseif ($report === 'payments') {
                fputcsv($handle, ['Reference', 'Booking Control', 'Plot', 'User Email', 'Amount (TZS)', 'Method', 'Status', 'Paid At', 'Verified At', 'Verified By']);
                foreach ($items as $payment) {
                    fputcsv($handle, [
                        $payment->reference,
                        optional($payment->booking)->control_number,
                        optional($payment->booking->plot)->number,
                        optional($payment->user)->email,
                        $payment->amount_tzs,
                        $payment->method,
                        $payment->status,
                        $payment->paid_at?->format('Y-m-d H:i') ?? '',
                        $payment->verified_at?->format('Y-m-d H:i') ?? '',
                        $payment->verified_by,
                    ]);
                }
            } else {
                fputcsv($handle, ['Title', 'Description', 'Due Date', 'Priority', 'Status', 'Assigned To', 'Worker', 'Completed', 'Approved', 'Completed At', 'Approved At']);
                foreach ($items as $task) {
                    fputcsv($handle, [
                        $task->title,
                        $task->description,
                        $task->due_date?->format('Y-m-d H:i') ?? '',
                        $task->priority,
                        $task->status,
                        $task->assigned_to,
                        optional($task->worker)->name,
                        $task->completed ? 'Yes' : 'No',
                        $task->approved ? 'Yes' : 'No',
                        $task->completed_at?->format('Y-m-d H:i') ?? '',
                        $task->approved_at?->format('Y-m-d H:i') ?? '',
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildTaskReportPdf(array $tasks, int $month, int $year): string
    {
        $monthName = Carbon::create($year, $month, 1)->format('F');
        $lines = [
            "LandFocus Task Report - {$monthName} {$year}",
            '--------------------------------------------------',
        ];

        foreach ($tasks as $task) {
            $lines[] = sprintf('Title: %s', $task['title'] ?? '');
            $lines[] = sprintf('Due Date: %s', isset($task['due_date']) ? Carbon::parse($task['due_date'])->format('Y-m-d H:i') : '');
            $lines[] = sprintf('Priority: %s | Status: %s | Completed: %s | Approved: %s',
                $task['priority'] ?? '',
                $task['status'] ?? '',
                ($task['completed'] ? 'Yes' : 'No'),
                ($task['approved'] ? 'Yes' : 'No')
            );
            $lines[] = sprintf('Assigned To: %s | Worker: %s',
                $task['assigned_to'] ?? '',
                isset($task['worker']) ? ($task['worker']['name'] ?? '') : ''
            );
            $lines[] = sprintf('Completed At: %s | Approved At: %s',
                isset($task['completed_at']) ? Carbon::parse($task['completed_at'])->format('Y-m-d H:i') : '',
                isset($task['approved_at']) ? Carbon::parse($task['approved_at'])->format('Y-m-d H:i') : ''
            );
            $lines[] = sprintf('Notes: %s', $task['description'] ?? '');
            $lines[] = str_repeat('-', 70);
        }

        $content = implode("\n", $lines);
        return $this->generateSimplePdf($content);
    }

    private function buildBookingReportPdf(array $bookings, int $month, int $year): string
    {
        $monthName = Carbon::create($year, $month, 1)->format('F');
        $lines = [
            "LandFocus Booking Report - {$monthName} {$year}",
            '--------------------------------------------------',
        ];

        foreach ($bookings as $booking) {
            $lines[] = sprintf('Control Number: %s', $booking['control_number'] ?? '');
            $lines[] = sprintf('Plot: %s | Price: %s TZS | Status: %s',
                $booking['plot']['number'] ?? '',
                $booking['price_tzs'] ?? 0,
                $booking['status'] ?? ''
            );
            $lines[] = sprintf('Customer: %s | Phone: %s | Email: %s',
                $booking['customer_name'] ?? '',
                $booking['customer_phone'] ?? '',
                $booking['user']['email'] ?? ''
            );
            $lines[] = sprintf('Total Paid: %s TZS | Remaining: %s TZS',
                $booking['total_paid'] ?? 0,
                $booking['remaining_balance'] ?? 0
            );
            $lines[] = sprintf('Created At: %s', isset($booking['created_at']) ? Carbon::parse($booking['created_at'])->format('Y-m-d H:i') : '');
            $lines[] = str_repeat('-', 70);
        }

        $content = implode("\n", $lines);
        return $this->generateSimplePdf($content);
    }

    private function buildPaymentReportPdf(array $payments, int $month, int $year): string
    {
        $monthName = Carbon::create($year, $month, 1)->format('F');
        $lines = [
            "LandFocus Payment Report - {$monthName} {$year}",
            '--------------------------------------------------',
        ];

        foreach ($payments as $payment) {
            $lines[] = sprintf('Reference: %s | Amount: %s TZS | Method: %s | Status: %s',
                $payment['reference'] ?? '',
                $payment['amount_tzs'] ?? 0,
                $payment['method'] ?? '',
                $payment['status'] ?? ''
            );
            $lines[] = sprintf('Booking: %s | Plot: %s | User: %s',
                $payment['booking']['control_number'] ?? '',
                $payment['booking']['plot']['number'] ?? '',
                $payment['user']['email'] ?? ''
            );
            $lines[] = sprintf('Paid At: %s | Verified At: %s | Verified By: %s',
                isset($payment['paid_at']) ? Carbon::parse($payment['paid_at'])->format('Y-m-d H:i') : '',
                isset($payment['verified_at']) ? Carbon::parse($payment['verified_at'])->format('Y-m-d H:i') : '',
                $payment['verified_by'] ?? ''
            );
            $lines[] = str_repeat('-', 70);
        }

        $content = implode("\n", $lines);
        return $this->generateSimplePdf($content);
    }

    private function generateSimplePdf(string $content): string
    {
        $lines = explode("\n", $content);
        $pdfLines = [];
        $y = 770;
        $pdfLines[] = 'BT';
        $pdfLines[] = '/F1 12 Tf';
        foreach ($lines as $line) {
            $escaped = $this->pdfEscape($line);
            $pdfLines[] = sprintf('1 0 0 1 40 %s Tm (%s) Tj', $y, $escaped);
            $y -= 16;
            if ($y < 40) {
                break;
            }
        }
        $pdfLines[] = 'ET';
        $stream = implode("\n", $pdfLines);

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";
        $objects[] = "5 0 obj\n<< /Length %d >>\nstream\n%s\nendstream\nendobj";

        $streamObject = sprintf($objects[4], strlen($stream), $stream);
        $fullObjects = [$objects[0], $objects[1], $objects[2], $objects[3], $streamObject];

        $pdf = "%PDF-1.4\n";
        $xref = [];
        $byteOffset = strlen($pdf);
        foreach ($fullObjects as $i => $object) {
            $xref[] = $byteOffset;
            $pdf .= $object . "\n";
            $byteOffset = strlen($pdf);
        }

        $xrefPosition = $byteOffset;
        $pdf .= "xref\n0 " . (count($fullObjects) + 1) . "\n0000000000 65535 f \n";
        foreach ($xref as $offset) {
            $pdf .= sprintf('%010d 00000 n \n', $offset);
        }
        $pdf .= "trailer\n<< /Size " . (count($fullObjects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    public function updateTask(Request $request, $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date',
            'priority' => 'sometimes|required|in:Low,Medium,High',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'assigned_to' => 'sometimes|nullable|string',
            'worker_id' => 'sometimes|nullable|uuid|exists:workers,id',
            'completed' => 'sometimes|boolean',
            'approved' => 'sometimes|boolean',
            'approved_by' => 'sometimes|nullable|string',
        ]);

        $validated['completed'] = $request->boolean('completed');
        $validated['approved'] = $request->boolean('approved');
        $validated['completed_at'] = $validated['completed'] ? now() : null;
        $validated['approved_at'] = $validated['approved'] ? now() : null;
        $task->update($validated);
        $this->syncSupabase('tasks', $task->toArray(), $task->id);

        return response()->json(['success' => true, 'task' => $task, 'message' => 'Task updated successfully']);
    }

    public function deleteTask($id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();
        $this->deleteSupabase('tasks', $id);

        return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
    }

    // ============ NOTIFICATIONS CRUD ============

    public function listNotifications(): JsonResponse
    {
        $notifications = SystemNotification::orderByDesc('created_at')->get();

        return response()->json(['success' => true, 'notifications' => $notifications]);
    }

    public function getNotification($id): JsonResponse
    {
        $notification = SystemNotification::findOrFail($id);

        return response()->json(['success' => true, 'notification' => $notification]);
    }

    public function storeNotification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'level' => 'required|in:info,warning,success,danger',
            'is_active' => 'boolean',
            'channel' => 'required|in:in_app,sms,email',
            'recipients' => 'nullable|array',
            'recipients.*' => 'string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['recipients'] = $this->parseRecipients($validated['recipients'] ?? []);
        $validated['sent_at'] = in_array($validated['channel'], ['sms', 'email']) && count($validated['recipients']) ? now() : null;

        $notification = SystemNotification::create($validated);
        $this->syncSupabase('system_notifications', $notification->toArray(), $notification->id);

        if (in_array($validated['channel'], ['sms', 'email']) && count($validated['recipients'])) {
            $beemResponse = (new BeemService())->send([
                'channel' => $validated['channel'],
                'title' => $validated['title'],
                'body' => $validated['body'],
                'recipients' => $validated['recipients'],
            ]);

            if (! $beemResponse['success']) {
                logger()->error('BEEM send failed', ['response' => $beemResponse, 'notification_id' => $notification->id]);
            }
        }

        return response()->json(['success' => true, 'notification' => $notification, 'message' => 'Notification created successfully']);
    }

    public function updateNotification(Request $request, $id): JsonResponse
    {
        $notification = SystemNotification::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'level' => 'required|in:info,warning,success,danger',
            'is_active' => 'boolean',
            'channel' => 'required|in:in_app,sms,email',
            'recipients' => 'nullable|array',
            'recipients.*' => 'string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['recipients'] = $this->parseRecipients($validated['recipients'] ?? []);
        $validated['sent_at'] = in_array($validated['channel'], ['sms', 'email']) && count($validated['recipients']) ? now() : null;
        $notification->update($validated);
        $this->syncSupabase('system_notifications', $notification->toArray(), $notification->id);

        if (in_array($validated['channel'], ['sms', 'email']) && count($validated['recipients'])) {
            $beemResponse = (new BeemService())->send([
                'channel' => $validated['channel'],
                'title' => $validated['title'],
                'body' => $validated['body'],
                'recipients' => $validated['recipients'],
            ]);

            if (! $beemResponse['success']) {
                logger()->error('BEEM send failed', ['response' => $beemResponse, 'notification_id' => $notification->id]);
            }
        }

        return response()->json(['success' => true, 'notification' => $notification, 'message' => 'Notification updated successfully']);
    }

    private function parseRecipients(array $recipients): array
    {
        return array_values(array_filter(array_map('trim', $recipients)));
    }

    public function deleteNotification($id): JsonResponse
    {
        $notification = SystemNotification::findOrFail($id);
        $notification->delete();
        $this->deleteSupabase('system_notifications', $id);

        return response()->json(['success' => true, 'message' => 'Notification deleted successfully']);
    }

    // ============ WORKERS CRUD ============

    public function listWorkers(): JsonResponse
    {
        $workers = Worker::orderBy('name')->get();

        return response()->json(['success' => true, 'workers' => $workers]);
    }

    public function getWorker($id): JsonResponse
    {
        $worker = Worker::findOrFail($id);

        return response()->json(['success' => true, 'worker' => $worker]);
    }

    public function storeWorker(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'role' => 'nullable|string',
            'phone' => 'nullable|string',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        $worker = Worker::create($validated);
        $this->syncSupabase('workers', $worker->toArray(), $worker->id);

        return response()->json(['success' => true, 'worker' => $worker, 'message' => 'Worker saved successfully']);
    }

    public function updateWorker(Request $request, $id): JsonResponse
    {
        $worker = Worker::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'role' => 'nullable|string',
            'phone' => 'nullable|string',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        $worker->update($validated);
        $this->syncSupabase('workers', $worker->toArray(), $worker->id);

        return response()->json(['success' => true, 'worker' => $worker, 'message' => 'Worker updated successfully']);
    }

    public function deleteWorker($id): JsonResponse
    {
        $worker = Worker::findOrFail($id);
        $worker->delete();
        $this->deleteSupabase('workers', $id);

        return response()->json(['success' => true, 'message' => 'Worker deleted successfully']);
    }

    // ============ CONTRACTS CRUD ============

    public function listContracts(): JsonResponse
    {
        $contracts = Contract::orderByDesc('created_at')->get();

        return response()->json(['success' => true, 'contracts' => $contracts]);
    }

    public function getContract($id): JsonResponse
    {
        $contract = Contract::findOrFail($id);

        return response()->json(['success' => true, 'contract' => $contract]);
    }

    public function storeContract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string',
            'property_name' => 'nullable|string',
            'status' => 'required|in:draft,pending,signed,cancelled',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'details' => 'nullable|string',
        ]);

        $contract = Contract::create($validated);
        $this->syncSupabase('contracts', $contract->toArray(), $contract->id);

        return response()->json(['success' => true, 'contract' => $contract, 'message' => 'Contract created successfully']);
    }

    public function updateContract(Request $request, $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);

        $validated = $request->validate([
            'client_name' => 'required|string',
            'property_name' => 'nullable|string',
            'status' => 'required|in:draft,pending,signed,cancelled',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'details' => 'nullable|string',
        ]);

        $contract->update($validated);
        $this->syncSupabase('contracts', $contract->toArray(), $contract->id);

        return response()->json(['success' => true, 'contract' => $contract, 'message' => 'Contract updated successfully']);
    }

    public function deleteContract($id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();
        $this->deleteSupabase('contracts', $id);

        return response()->json(['success' => true, 'message' => 'Contract deleted successfully']);
    }

    // ============ HELPER METHODS ============

    private function mirrorToPublicStorage(string $path): void
    {
        $source = storage_path('app/public/' . ltrim($path, '/'));
        if (! is_file($source)) {
            return;
        }

        $target = public_path('storage/' . ltrim($path, '/'));
        $directory = dirname($target);
        if (! is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }

        @copy($source, $target);
    }

    private function getFileType(string $extension): string
    {
        $extension = strtolower($extension);
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return 'image';
        } elseif ($extension === 'pdf') {
            return 'pdf';
        } elseif (in_array($extension, ['doc', 'docx'])) {
            return 'doc';
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            return 'spreadsheet';
        }

        return 'file';
    }

    // ============ TESTIMONIALS MANAGEMENT ============

    public function testimonials()
    {
        $testimonials = \App\Models\Testimonial::orderBy('sort_order')->get();
        return $this->adminView('admin.testimonials', compact('testimonials'));
    }

    public function storeTestimonial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quote' => 'required|string|min:10',
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $testimonial = \App\Models\Testimonial::create($validated);
        return response()->json(['success' => true, 'testimonial' => $testimonial]);
    }

    public function updateTestimonial(Request $request, $id): JsonResponse
    {
        $testimonial = \App\Models\Testimonial::findOrFail($id);
        $validated = $request->validate([
            'quote' => 'required|string|min:10',
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $testimonial->update($validated);
        return response()->json(['success' => true, 'testimonial' => $testimonial]);
    }

    public function deleteTestimonial($id): JsonResponse
    {
        \App\Models\Testimonial::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ============ GALLERY MANAGEMENT ============

    public function gallery()
    {
        $photos = \App\Models\GalleryPhoto::orderBy('sort_order')->get();
        $projects = Project::orderBy('title')->get();
        return $this->adminView('admin.gallery', compact('photos', 'projects'));
    }

    public function storeGalleryPhoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'required|image|max:10240',
            'alt_text' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('gallery', 'public');
        }

        $photo = \App\Models\GalleryPhoto::create($validated);
        return response()->json(['success' => true, 'photo' => $photo]);
    }

    public function updateGalleryPhoto(Request $request, $id): JsonResponse
    {
        $photo = \App\Models\GalleryPhoto::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|max:10240',
            'alt_text' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($request->hasFile('image_path')) {
            Storage::disk('public')->delete($photo->image_path);
            $validated['image_path'] = $request->file('image_path')->store('gallery', 'public');
        }

        $photo->update($validated);
        return response()->json(['success' => true, 'photo' => $photo]);
    }

    public function deleteGalleryPhoto($id): JsonResponse
    {
        $photo = \App\Models\GalleryPhoto::findOrFail($id);
        Storage::disk('public')->delete($photo->image_path);
        $photo->delete();
        return response()->json(['success' => true]);
    }

    // ============ STAFF MANAGEMENT ============

    public function staffManagement()
    {
        $staff = \App\Models\Staff::orderBy('sort_order')->get();
        return $this->adminView('admin.staff', compact('staff'));
    }

    public function storeStaff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('staff', 'public');
        }

        $member = \App\Models\Staff::create($validated);
        return response()->json(['success' => true, 'member' => $member]);
    }

    public function updateStaff(Request $request, $id): JsonResponse
    {
        $member = \App\Models\Staff::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($member->photo);
            $validated['photo'] = $request->file('photo')->store('staff', 'public');
        }

        $member->update($validated);
        return response()->json(['success' => true, 'member' => $member]);
    }

    public function deleteStaff($id): JsonResponse
    {
        $member = \App\Models\Staff::findOrFail($id);
        Storage::disk('public')->delete($member->photo);
        $member->delete();
        return response()->json(['success' => true]);
    }
}
