<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_appointments(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/appointments');
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Autoservisa rezervacijas');
    }
}
