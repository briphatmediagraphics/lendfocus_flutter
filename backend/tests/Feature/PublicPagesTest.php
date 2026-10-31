<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_home_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Tafuta Ardhi, Nyumba na Mashamba kwa Urahisi')
            ->assertSee('Huduma Zetu');
    }

    public function test_properties_page_is_accessible(): void
    {
        $response = $this->get('/properties');

        $response->assertStatus(200)
            ->assertSee('Miradi Zinazopatikana');
    }

    public function test_contact_page_has_inquiry_form(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200)
            ->assertSee('Tuma Ujumbe');
    }

    public function test_appointment_submission_does_not_crash_without_database_tables(): void
    {
        $response = $this->post('/appointment', [
            'client_name' => 'Test User',
            'client_email' => 'test@example.com',
            'client_phone' => '0712345678',
            'appointment_type' => 'site_visit',
            'scheduled_at' => '2026-08-10T10:00',
            'notes' => 'Test booking',
        ]);

        $response->assertStatus(302);
    }

    public function test_contact_form_submission_does_not_crash_without_database_tables(): void
    {
        $response = $this->post('/inquiries', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0712345678',
            'message' => 'Test inquiry',
            'callback_requested' => true,
        ]);

        $response->assertStatus(302);
    }

    public function test_contact_form_submission_with_property_id_does_not_crash_without_database_tables(): void
    {
        $response = $this->post('/inquiries', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0712345678',
            'message' => 'Test inquiry',
            'property_id' => '00000000-0000-0000-0000-000000000001',
            'callback_requested' => true,
        ]);

        $response->assertStatus(302);
    }

    public function test_storage_route_serves_public_files_without_symlink(): void
    {
        $path = public_path('storage/test-upload.txt');
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, 'hello from storage');

        $response = $this->get('/storage/test-upload.txt');

        $response->assertStatus(200);
        $response->assertSee('hello from storage');

        unlink($path);
    }
}
