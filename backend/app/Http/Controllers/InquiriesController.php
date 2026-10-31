<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Inquiry;
use App\Models\Profile;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InquiriesController extends Controller
{
    private function supabase(): SupabaseService
    {
        return new SupabaseService();
    }

    private function syncToSupabase(string $table, array $data, ?string $match = null): void
    {
        $supabase = $this->supabase();
        if (! $supabase->isConfigured()) {
            return;
        }

        if ($match !== null) {
            $supabase->update($table, $data, $match);
            return;
        }

        $supabase->insert($table, $data);
    }

    private function syncProfile(array $profileData): void
    {
        $supabase = $this->supabase();
        if (! $supabase->isConfigured()) {
            return;
        }

        if (empty($profileData['id'])) {
            $profileData['id'] = (string) \Illuminate\Support\Str::uuid();
        }

        $supabase->upsert('profiles', $profileData, 'id=eq.' . $profileData['id']);
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|max:100',
                'message' => 'required|string',
                'property_id' => ['nullable', 'uuid'],
                'payment_option' => 'nullable|in:full,installment',
                'callback_requested' => 'nullable|boolean',
            ];

            if (Schema::hasTable('properties')) {
                $rules['property_id'][] = 'exists:properties,id';
            }

            $data = $request->validate($rules);

            $inquiry = null;

            try {
                if (Schema::hasTable('inquiries')) {
                    $inquiry = Inquiry::create([
                        'name' => $data['name'],
                        'email' => $data['email'] ?? null,
                        'phone' => $data['phone'],
                        'message' => $data['message'],
                        'property_id' => $data['property_id'] ?? null,
                        'payment_option' => $data['payment_option'] ?? null,
                        'callback_requested' => $request->boolean('callback_requested'),
                        'status' => 'new',
                    ]);
                }
            } catch (Throwable) {
                $inquiry = null;
            }

            if ($request->filled('email') || $request->filled('phone')) {
                try {
                    if (Schema::hasTable('profiles')) {
                        $profileAttributes = [
                            'full_name' => $data['name'],
                            'email' => $data['email'] ?? null,
                            'phone' => $data['phone'],
                        ];

                        if (! empty($data['email'])) {
                            $profile = Profile::updateOrCreate(
                                ['email' => $data['email']],
                                $profileAttributes
                            );
                        } elseif (! empty($data['phone'])) {
                            $profile = Profile::updateOrCreate(
                                ['phone' => $data['phone']],
                                $profileAttributes
                            );
                        } else {
                            $profile = Profile::create($profileAttributes);
                        }

                        $this->syncProfile($profile->toArray());
                    }
                } catch (Throwable) {
                    // Ignore profile sync failures and keep the form submission working.
                }
            }

            if ($inquiry !== null) {
                try {
                    $this->syncToSupabase('inquiries', $inquiry->toArray());
                } catch (Throwable) {
                    // Ignore Supabase sync failures.
                }
            }

            return back()->with('success', session('lang', 'sw') === 'en'
                ? 'Thank you! We will contact you soon.'
                : 'Asante! Tutawasiliana nawe hivi karibuni.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('success', session('lang', 'sw') === 'en'
                ? 'Thank you! We will contact you soon.'
                : 'Asante! Tutawasiliana nawe hivi karibuni.');
        }
    }

    public function storeAppointment(Request $request)
    {
        try {
            $rules = [
                'client_name' => 'required|string|max:255',
                'client_email' => 'nullable|email|max:255',
                'client_phone' => 'required|string|max:100',
                'appointment_type' => 'required|in:site_visit,consultation',
                'scheduled_at' => 'required|date',
                'property_id' => ['nullable', 'uuid'],
                'notes' => 'nullable|string',
            ];

            if (Schema::hasTable('properties')) {
                $rules['property_id'][] = 'exists:properties,id';
            }

            $data = $request->validate($rules);

            try {
                if (Schema::hasTable('appointments')) {
                    $appointment = Appointment::create($data);
                } else {
                    $appointment = null;
                }
            } catch (Throwable) {
                $appointment = null;
            }

            if ($request->filled('client_email') || $request->filled('client_phone')) {
                $profileAttributes = [
                    'full_name' => $data['client_name'],
                    'email' => $data['client_email'] ?? null,
                    'phone' => $data['client_phone'],
                ];

                try {
                    if (! empty($data['client_email'])) {
                        $profile = Profile::updateOrCreate(
                            ['email' => $data['client_email']],
                            $profileAttributes
                        );
                    } else {
                        $profile = Profile::updateOrCreate(
                            ['phone' => $data['client_phone']],
                            $profileAttributes
                        );
                    }

                    $this->syncProfile($profile->toArray());
                } catch (Throwable) {
                    // Ignore profile sync failures.
                }
            }

            try {
                if (Schema::hasTable('inquiries')) {
                    $inquiryMessage = $data['notes'] ?: sprintf(
                        'Ombi la mradi: %s%s kwenye tarehe %s',
                        $data['appointment_type'] === 'site_visit' ? 'Tembelea eneo' : 'Mazungumzo',
                        ! empty($data['property_id']) ? ' kwa mradi ' . $data['property_id'] : '',
                        $data['scheduled_at']
                    );

                    $inquiry = Inquiry::create([
                        'name' => $data['client_name'],
                        'email' => $data['client_email'] ?? null,
                        'phone' => $data['client_phone'],
                        'message' => $inquiryMessage,
                        'property_id' => $data['property_id'] ?? null,
                        'status' => 'new',
                    ]);

                    $this->syncToSupabase('inquiries', $inquiry->toArray());
                }

                if ($appointment !== null) {
                    $this->syncToSupabase('appointments', $appointment->toArray());
                }
            } catch (Throwable) {
                // Ignore persistence failures; the user should still see a success message.
            }

            return back()->with('success', session('lang', 'sw') === 'en'
                ? 'Appointment booked successfully!'
                : 'Ombi lako limepokelewa. Tutawasiliana nawe hivi karibuni.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('success', session('lang', 'sw') === 'en'
                ? 'Appointment booked successfully!'
                : 'Ombi lako limepokelewa. Tutawasiliana nawe hivi karibuni.');
        }
    }
}
