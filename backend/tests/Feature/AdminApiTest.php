<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminApiTest extends TestCase
{
    public function test_admin_api_returns_json_unauthorized_when_not_authenticated(): void
    {
        $response = $this->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get('/admin/api/properties');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    public function test_admin_dashboard_is_accessible_without_database_tables(): void
    {
        $response = $this->withSession(['is_admin' => true])->get('/admin');

        $response->assertStatus(200);
    }

    public function test_admin_properties_api_returns_empty_collection_without_database_tables(): void
    {
        $response = $this->withSession(['is_admin' => true])->get('/admin/api/properties');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'properties' => [],
            ]);
    }
}
