<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CalendarEvent;
use App\Models\Inquiry;
use App\Models\Property;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Throwable;

class PublicPageController extends Controller
{
    public function home()
    {
        $properties = $this->fetchProperties();

        $events = $this->safeQuery(function () {
            return CalendarEvent::query()
                ->where(function ($query) {
                    $query->where('is_official', true)
                        ->orWhere('event_type', 'announcement');
                })
                ->where('start_at', '>=', now()->subDays(30))
                ->where('start_at', '<=', now()->addDays(30))
                ->orderBy('start_at')
                ->limit(6)
                ->get();
        }, collect());

        $gallery = $this->safeQuery(function () {
            return CalendarEvent::query()
                ->whereNotNull('media')
                ->where('media', '!=', '[]')
                ->where('start_at', '>=', now()->subDays(60))
                ->where('start_at', '<=', now()->addDays(60))
                ->orderByDesc('start_at')
                ->get()
                ->flatMap(fn ($event) => collect($event->mediaGallery()))
                ->take(9)
                ->all();
        }, []);

        $services = array_slice(config('landfocus.services'), 0, 8);

        return view('public.home', compact('properties', 'events', 'services', 'gallery'));
    }

    public function properties(Request $request)
    {
        $type = $request->query('type');
        $properties = $this->fetchProperties($type);

        return view('public.properties', compact('properties', 'type'));
    }

    private function supabase(): SupabaseService
    {
        return new SupabaseService();
    }

    private function fetchProperties(?string $type = null)
    {
        $supabase = $this->supabase();
        if ($supabase->isConfigured()) {
            $query = ['select' => '*'];
            if ($type) {
                $query['property_type'] = 'eq.' . $type;
            }

            $items = $supabase->fetch('properties', $query);
            if (! empty($items)) {
                return collect($items)->map(function ($item) {
                    return new Property((array) $item);
                });
            }
        }

        return $this->safeQuery(function () use ($type) {
            $query = Property::query()->orderByDesc('created_at');
            if ($type && in_array($type, ['land', 'house', 'farm', 'office'], true)) {
                $query->where('property_type', $type);
            }

            return $query->get();
        }, collect());
    }

    public function propertyDetail(string $id)
    {
        $property = $this->safeQuery(function () use ($id) {
            return Property::find($id);
        });

        if (! $property) {
            abort(404);
        }

        // If this property is linked to a project, load its plots for display
        $project = null;
        $plots = collect();
        if ($property->project_id) {
            $project = $this->safeQuery(function () use ($property) {
                return \App\Models\Project::find($property->project_id);
            });

            if ($project) {
                $plots = $this->safeQuery(function () use ($project) {
                    return $project->plots()->orderBy('position_y')->orderBy('position_x')->get();
                }, collect());
            }
        }

        return view('public.property-detail', compact('property', 'project', 'plots'));
    }

    public function services()
    {
        $services = config('landfocus.services');

        return view('public.services', compact('services'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function history()
    {
        $milestones = config('landfocus.milestones');

        return view('public.history', compact('milestones'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function book(Request $request)
    {
        $propertyId = $request->query('property_id');
        $properties = $this->safeQuery(function () {
            return Property::query()->where('status', 'available')->orderBy('title')->get();
        }, collect());
        $selectedProperty = $propertyId ? $this->safeQuery(function () use ($propertyId) {
            return Property::find($propertyId);
        }) : null;

        return view('public.book', compact('properties', 'selectedProperty', 'propertyId'));
    }

    public function setLang(string $lang)
    {
        if (in_array($lang, ['sw', 'en'], true)) {
            session(['lang' => $lang]);
        }

        return back();
    }

    private function safeQuery(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $default;
        }
    }
}
