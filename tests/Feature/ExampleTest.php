<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Degvielas Cenas Latvijā');
    }

    public function test_public_content_pages_load(): void
    {
        $this->get('/degvielas-atlaides')->assertStatus(200);
        $this->get('/blog')->assertStatus(200);
        $this->get('/par-projektu')->assertStatus(200);
    }
}
