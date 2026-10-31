<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use Illuminate\View\View;

class GalleryController extends Controller
{
    /**
     * Display all gallery photos
     */
    public function index(): View
    {
        $photos = GalleryPhoto::where('is_active', true)
            ->orderBy('sort_order')
            ->paginate(12);

        return view('gallery.index', compact('photos'));
    }

    /**
     * Display gallery photos for a specific project
     */
    public function project($projectId): View
    {
        $photos = GalleryPhoto::where('project_id', $projectId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->paginate(12);

        return view('gallery.project', compact('photos'));
    }
}
