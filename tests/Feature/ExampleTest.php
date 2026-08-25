<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that guests are redirected to the entry page.
     */
    public function test_guest_is_redirected_to_entry(): void
    {
        $this->withMiddleware();
        $response = $this->get('/');
        $response->assertStatus(302);
        $response->assertRedirect('/entry');
    }

    /**
     * Test that authenticated users can view the dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->withSession([
            'user_name' => 'Test User',
            'user_position' => 'Manager'
        ])->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test that authenticated users can access the settings page.
     */
    public function test_authenticated_user_can_access_settings(): void
    {
        $response = $this->withSession([
            'user_name' => 'Test User',
            'user_position' => 'Manager'
        ])->get('/settings');

        $response->assertStatus(200);
    }
}
