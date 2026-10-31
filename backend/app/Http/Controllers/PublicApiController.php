<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicApiController extends Controller
{
    public function properties(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:land,house,farm,office'],
            'featured' => ['nullable', 'boolean'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $properties = Property::query()
            ->where('status', 'available')
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('property_type', $type))
            ->when(
                array_key_exists('featured', $filters),
                fn ($query) => $query->where('featured', (bool) $filters['featured'])
            )
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit($filters['limit'] ?? 24)
            ->get()
            ->map(fn (Property $property) => $this->propertyData($property));

        return response()->json(['data' => $properties]);
    }

    public function property(Property $property): JsonResponse
    {
        abort_unless($property->status === 'available', 404);

        return response()->json(['data' => $this->propertyData($property)]);
    }

    public function services(): JsonResponse
    {
        $services = array_map(static fn (array $service) => [
            'id' => $service['id'],
            'title' => ['sw' => $service['title_sw'], 'en' => $service['title_en']],
            'description' => ['sw' => $service['desc_sw'], 'en' => $service['desc_en']],
        ], config('landfocus.services', []));

        return response()->json(['data' => $services]);
    }

    public function storeInquiry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:7', 'max:20'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
            'property_id' => ['nullable', 'uuid', 'exists:properties,id'],
            'payment_option' => ['nullable', 'in:full,installment'],
            'callback_requested' => ['sometimes', 'boolean'],
        ]);

        $inquiry = DB::transaction(fn () => Inquiry::create([
            ...$data,
            'callback_requested' => $request->boolean('callback_requested'),
            'status' => 'new',
        ]));

        return response()->json([
            'message' => 'Thank you. Your inquiry has been received.',
            'data' => ['ticket_number' => $inquiry->ticket_number],
        ], 201);
    }

    public function storeAppointment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_name' => ['required', 'string', 'min:2', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['required', 'string', 'min:7', 'max:20'],
            'appointment_type' => ['required', 'in:site_visit,consultation'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'property_id' => ['nullable', 'uuid', 'exists:properties,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $appointment = DB::transaction(fn () => Appointment::create($data));

        return response()->json([
            'message' => 'Your appointment request has been received.',
            'data' => ['id' => $appointment->id],
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function propertyData(Property $property): array
    {
        $images = array_values(array_filter($property->imageUrls()));

        return [
            'id' => $property->id,
            'title' => $property->title,
            'description' => $property->description,
            'price' => $property->price,
            'currency' => $property->currency,
            'location' => $property->location,
            'property_type' => $property->property_type,
            'status' => $property->status,
            'area_sqm' => $property->area_sqm,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'images' => array_map(
                fn (string $image) => str_starts_with($image, 'http') ? $image : url($image),
                $images
            ),
            'featured' => (bool) $property->featured,
        ];
    }
}
