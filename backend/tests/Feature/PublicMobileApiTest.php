<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicMobileApiTest extends TestCase
{
    public function test_public_services_are_returned_as_bilingual_json(): void
    {
        $this->getJson('/api/public/services')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title' => ['sw', 'en'], 'description' => ['sw', 'en']],
                ],
            ]);
    }

    public function test_property_filters_are_validated_as_json(): void
    {
        $this->getJson('/api/public/properties?type=unknown')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }

    public function test_property_list_only_returns_available_matches_with_image_urls(): void
    {
        Schema::create('properties', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->bigInteger('price');
            $table->string('currency');
            $table->string('location');
            $table->string('property_type');
            $table->string('status');
            $table->integer('area_sqm')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->json('images')->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        $now = now();
        DB::table('properties')->insert([
            [
                'id' => (string) Str::uuid(),
                'title' => 'Available land',
                'description' => null,
                'price' => 12000000,
                'currency' => 'TZS',
                'location' => 'Dar es Salaam',
                'property_type' => 'land',
                'status' => 'available',
                'images' => json_encode(['https://images.example.test/plot.jpg']),
                'featured' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Reserved land',
                'description' => null,
                'price' => 15000000,
                'currency' => 'TZS',
                'location' => 'Arusha',
                'property_type' => 'land',
                'status' => 'reserved',
                'images' => json_encode([]),
                'featured' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->getJson('/api/public/properties?type=land')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Available land')
            ->assertJsonPath('data.0.images.0', 'https://images.example.test/plot.jpg');
    }

    public function test_public_inquiry_is_persisted_in_mysql_schema_shape(): void
    {
        Schema::create('inquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_number')->nullable();
            $table->uuid('property_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->string('payment_option')->nullable();
            $table->boolean('callback_requested')->default(false);
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->postJson('/api/public/inquiries', [
            'name' => 'Asha Mushi',
            'email' => 'asha@example.test',
            'phone' => '+255712345678',
            'message' => 'Please share details about available plots.',
            'callback_requested' => true,
        ])
            ->assertCreated()
            ->assertJsonStructure(['message', 'data' => ['ticket_number']]);

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Asha Mushi',
            'phone' => '+255712345678',
            'status' => 'new',
            'callback_requested' => true,
        ]);
    }

    public function test_public_appointment_is_persisted(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone');
            $table->string('appointment_type');
            $table->dateTime('scheduled_at');
            $table->uuid('property_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $scheduledAt = now()->addDay()->startOfHour();
        $this->postJson('/api/public/appointments', [
            'client_name' => 'Juma Kweka',
            'client_phone' => '0712345678',
            'appointment_type' => 'site_visit',
            'scheduled_at' => $scheduledAt->toIso8601String(),
        ])
            ->assertCreated()
            ->assertJsonStructure(['message', 'data' => ['id']]);

        $this->assertDatabaseHas('appointments', [
            'client_name' => 'Juma Kweka',
            'client_phone' => '0712345678',
            'appointment_type' => 'site_visit',
        ]);
    }

    public function test_public_inquiry_requires_valid_customer_details(): void
    {
        $this->postJson('/api/public/inquiries', [
            'name' => 'A',
            'phone' => '1',
            'message' => 'Hi',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'message']);
    }

    public function test_public_appointment_requires_a_future_booking_time(): void
    {
        $this->postJson('/api/public/appointments', [
            'client_name' => 'Test Customer',
            'client_phone' => '0712345678',
            'appointment_type' => 'site_visit',
            'scheduled_at' => now()->subDay()->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('scheduled_at');
    }
}
