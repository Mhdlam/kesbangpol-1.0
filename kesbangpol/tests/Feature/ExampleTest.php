<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sistem Informasi Pengelolaan');
    }


    public function test_authentication_pages_are_available_to_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke Sistem Kesbangpol');
        $this->get('/register')->assertOk();
    }
}

