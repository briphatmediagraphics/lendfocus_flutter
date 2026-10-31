<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Plot;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Pagination\Paginator;

class ProjectController extends Controller
{
    /**
     * Show all projects with search and filters
     */
    public function index(Request $request): View
    {
        $query = Project::query();

        // Search by name or location
        if ($request->has('q') && $request->q) {
            $query->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('location', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
        }

        // Filter by location
        if ($request->has('location') && $request->location) {
            $query->where('location', $request->location);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $projects = $query->with('plots')
            ->paginate(12)
            ->appends($request->query());

        // Build overall plot stats for the list page
        $stats = [
            'open' => 0,
            'booked' => 0,
            'paid' => 0,
            'total' => 0,
            'value' => 0,
        ];

        foreach ($projects as $projectItem) {
            foreach ($projectItem->plots as $plot) {
                $stats['total']++;
                $stats['value'] += $plot->price_tzs ?? 0;
                if (in_array($plot->status, ['open', 'available'], true)) {
                    $stats['open']++;
                }
                if (in_array($plot->status, ['booked', 'held'], true)) {
                    $stats['booked']++;
                }
                if (in_array($plot->status, ['paid', 'sold'], true)) {
                    $stats['paid']++;
                }
            }
        }

        // Get unique locations for filter
        $locations = Project::distinct()->pluck('location');

        return view('projects.index', [
            'projects' => $projects,
            'locations' => $locations,
            'search' => $request->q ?? '',
            'location' => $request->location ?? '',
            'status' => $request->status ?? '',
            'stats' => $stats,
        ]);
    }

    /**
     * Show single project with plots
     */
    public function show(Project $project): View
    {
        $project->load(['plots' => function ($query) {
            $query->withCount('bookings');
        }]);

        return view('projects.show', [
            'project' => $project,
        ]);
    }

    /**
     * Show all plots for a project
     */
    public function plots(Project $project, Request $request): View
    {
        $query = $project->plots();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price_tzs', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price_tzs', '<=', $request->max_price);
        }

        // Filter by size range
        if ($request->has('min_size') && $request->min_size) {
            $query->where('size_sqm', '>=', $request->min_size);
        }
        if ($request->has('max_size') && $request->max_size) {
            $query->where('size_sqm', '<=', $request->max_size);
        }

        // Sort
        $sort = $request->get('sort', 'number');
        if ($sort === 'price_low') {
            $query->orderBy('price_tzs');
        } elseif ($sort === 'price_high') {
            $query->orderByDesc('price_tzs');
        } else {
            $query->orderBy('number');
        }

        $plots = $query->with(['bookings', 'project'])
            ->paginate(20)
            ->appends($request->query());

        // Get price range for filters
        $priceRange = $project->plots()->selectRaw('MIN(price_tzs) as min, MAX(price_tzs) as max')->first();
        $sizeRange = $project->plots()->selectRaw('MIN(size_sqm) as min, MAX(size_sqm) as max')->first();

        return view('projects.plots', [
            'project' => $project,
            'plots' => $plots,
            'priceRange' => $priceRange,
            'sizeRange' => $sizeRange,
        ]);
    }

    /**
     * Show single plot detail
     */
    public function plotDetail(Project $project, Plot $plot): View
    {
        // Verify plot belongs to project
        if ($plot->project_id !== $project->id) {
            abort(404);
        }

        $plot->load(['bookings', 'project']);

        // Get booking info if user is authenticated
        $userBooking = null;
        if (auth()->check()) {
            $userBooking = $plot->bookings()
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('projects.plot-detail', [
            'project' => $project,
            'plot' => $plot,
            'userBooking' => $userBooking,
        ]);
    }

    /**
     * Export plots as CSV
     */
    public function exportPlots(Project $project)
    {
        $plots = $project->plots()->get();

        $filename = "plots_{$project->slug}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $callback = function () use ($plots) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Plot Number', 'Size (sqm)', 'Price (TZS)', 'Status', 'Coordinates', 'Notes']);
            
            // Add data
            foreach ($plots as $plot) {
                fputcsv($file, [
                    $plot->number,
                    $plot->size_sqm,
                    $plot->price_tzs,
                    $plot->status,
                    json_encode($plot->coordinates),
                    $plot->notes,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get available plot statuses
     */
    protected function getPlotStatuses()
    {
        return ['open', 'booked', 'paid', 'reserved'];
    }

    /**
     * Show interactive map for a project
     */
    public function map(Project $project)
    {
        $project->load(['plots' => function ($q) { $q->withCount('bookings'); }]);

        return view('projects.map', compact('project'));
    }

    /**
     * Return plots data as JSON for map rendering
     */
    public function plotsJson(Project $project)
    {
        $query = $project->plots()->get();

        $plots = $query->map(function ($p) {
            return [
                'id' => $p->id,
                'number' => $p->number,
                'plot_number' => $p->plot_number ?? $p->number,
                'size_sqm' => $p->size_sqm,
                'price_tzs' => $p->price_tzs,
                'status' => $p->status,
                'coordinates' => $p->coordinates,
                'position_x' => $p->position_x,
                'position_y' => $p->position_y,
            ];
        });

        return response()->json(['success' => true, 'plots' => $plots]);
    }

    /**
     * Save drawn shapes (admin only)
     */
    public function saveMapShapes(Request $request, Project $project)
    {
        if (! session()->get('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate(['shape' => 'required|array']);

        $shapes = $project->map_shapes ?? [];
        $shapes[] = $data['shape'];

        $project->map_shapes = $shapes;
        $project->save();

        return response()->json(['success' => true]);
    }

    /**
     * Return shapes as JSON for admin UI
     */
    public function projectShapesJson(Project $project)
    {
        if (! session()->get('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $shapes = $project->map_shapes ?? [];

        return response()->json(['success' => true, 'shapes' => $shapes]);
    }

    /**
     * Admin: list map shapes for a project (HTML view)
     */
    public function adminShapes(Project $project)
    {
        if (! session()->get('is_admin')) {
            abort(403);
        }

        $shapes = $project->map_shapes ?? [];

        return view('admin.project-shapes', compact('project', 'shapes'));
    }

    /**
     * Admin: delete a map shape by index
     */
    public function deleteMapShape(Request $request, Project $project, $index)
    {
        if (! session()->get('is_admin')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return response('Unauthorized', 403);
        }

        $shapes = $project->map_shapes ?? [];

        if (! isset($shapes[$index])) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Shape not found'], 404);
            }
            return back()->with('error', 'Shape not found');
        }

        array_splice($shapes, $index, 1);
        $project->map_shapes = $shapes;
        $project->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Shape deleted');
    }
}

