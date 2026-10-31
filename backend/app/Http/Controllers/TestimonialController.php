<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    /**
     * Display testimonials on public page
     */
    public function index(): View
    {
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('testimonials.index', compact('testimonials'));
    }

    /**
     * Display single testimonial
     */
    public function show(Testimonial $testimonial): View
    {
        return view('testimonials.show', compact('testimonial'));
    }
}
