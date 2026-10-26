<?php

namespace Tests\Feature;

use App\Models\FuelPrice;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_and_errors_have_security_headers(): void
    {
        foreach (['/', '/missing-page', '/api/prices/history'] as $path) {
            $response = $this->getJson($path);
            $response->assertHeader('X-Frame-Options', 'DENY');
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
            $policy = $response->headers->get('Content-Security-Policy');
            $this->assertStringContainsString("frame-ancestors 'none'", $policy);
            $this->assertStringContainsString("object-src 'none'", $policy);
            $this->assertStringNotContainsString('unsafe-eval', $policy);
            $this->assertStringNotContainsString("script-src 'unsafe-inline'", $policy);
        }
    }

    public function test_csp_nonces_match_markup_and_change_between_requests(): void
    {
        $first = $this->get('/')->assertOk();
        preg_match("/nonce-([^']+)/", $first->headers->get('Content-Security-Policy'), $matches);
        $first->assertSee('nonce="'.$matches[1].'"', false);
        $second = $this->get('/')->assertOk();
        $this->assertStringNotContainsString('nonce-'.$matches[1], $second->headers->get('Content-Security-Policy'));
    }

    public function test_production_http_redirects_to_canonical_https_and_rejects_host_spoofing(): void
    {
        config(['security.force_https' => true, 'app.url' => 'https://prices.example']);
        $this->get('http://prices.example/blog?x=1')->assertStatus(308)->assertRedirect('https://prices.example/blog?x=1');
        $this->withHeaders(['X-Forwarded-Proto' => 'https'])->get('http://prices.example/blog')->assertStatus(308);
        $this->get('https://attacker.example/')->assertStatus(400);
        $this->get('https://prices.example/blog')->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_public_requests_cannot_run_imports(): void
    {
        Http::preventStrayRequests();
        Artisan::spy();
        $this->postJson('/api/prices/refresh')->assertNotFound();
        Artisan::shouldNotHaveReceived('call');
        $this->assertDatabaseCount('prices', 0);
    }

    public function test_overlapping_cli_imports_do_not_start_network_requests(): void
    {
        Http::preventStrayRequests();
        $lock = Cache::lock('fuel-prices:import', 3600);
        $this->assertTrue($lock->get());

        try {
            $this->artisan('fetch:fuel-prices')->expectsOutput('Cenu imports jau darbojas.')->assertSuccessful();
            $this->assertDatabaseCount('prices', 0);
        } finally {
            $lock->release();
        }
    }

    public function test_production_configuration_cannot_enable_debug_or_insecure_session_cookies(): void
    {
        $server = $_SERVER;
        $environment = $_ENV;

        try {
            foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'true', 'SESSION_SECURE_COOKIE' => 'false'] as $key => $value) {
                $_SERVER[$key] = $_ENV[$key] = $value;
            }

            $app = require config_path('app.php');
            $session = require config_path('session.php');
            $this->assertFalse($app['debug']);
            $this->assertTrue($session['secure']);
            $this->assertTrue($session['http_only']);
            $this->assertSame('lax', $session['same_site']);
        } finally {
            $_SERVER = $server;
            $_ENV = $environment;
        }
    }

    public function test_malformed_and_nested_filters_are_rejected(): void
    {
        foreach (['fuel_type[]=95', 'fuel_type[admin]=1', 'fuel_type=Diesel%27%20OR%201%3D1', 'fuel_type=invalid'] as $query) {
            $this->getJson('/api/stations/cheapest?'.$query)->assertUnprocessable()->assertJsonValidationErrors('fuel_type');
        }
        foreach (['days[]=30', 'days=366', 'days=0', 'days=1.5'] as $query) {
            $this->getJson('/api/prices/history?fuel_type=95&'.$query)->assertUnprocessable()->assertJsonValidationErrors('days');
        }
        $this->getJson('/api/prices/history')->assertUnprocessable()->assertJsonValidationErrors('fuel_type');
    }

    public function test_read_api_and_history_are_rate_limited(): void
    {
        for ($request = 0; $request < 10; $request++) {
            $this->getJson('/api/prices/history?fuel_type=95')->assertOk();
        }
        $this->getJson('/api/prices/history?fuel_type=95')->assertTooManyRequests()->assertHeader('Retry-After');
    }

    public function test_station_reads_are_limited_and_forwarded_ips_are_not_blindly_trusted(): void
    {
        for ($request = 0; $request < 60; $request++) {
            $this->withHeaders(['X-Forwarded-For' => '198.51.100.'.($request + 1)])
                ->getJson('/api/stations/cheapest?fuel_type=95')->assertOk();
        }
        $this->getJson('/api/stations/cheapest?fuel_type=95')->assertTooManyRequests();
    }

    public function test_only_latest_prices_are_returned_in_price_order(): void
    {
        foreach (['A' => [1.8, 1.6], 'B' => [1.7, 1.5]] as $name => $prices) {
            $station = Station::create(['name' => $name, 'brand' => $name, 'address' => $name, 'latitude' => 56.9, 'longitude' => 24.1]);
            foreach ($prices as $price) {
                FuelPrice::create(['station_id' => $station->id, 'fuel_type' => '95', 'price' => $price, 'source_type' => 'official_station', 'source_label' => 'Official', 'fetched_at' => now()]);
            }
        }
        $this->getJson('/api/stations/cheapest?fuel_type=95')->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('data.0.name', 'B')
            ->assertJsonPath('data.0.selected_fuel_price', 1.5)->assertJsonCount(1, 'data.0.latest_prices');
        $this->getJson('/api/prices/history?fuel_type=95')->assertOk()->assertJsonPath('truncated', false)->assertJsonCount(2, 'series');
    }

    public function test_history_has_a_hard_record_budget(): void
    {
        $station = Station::create(['name' => 'A', 'brand' => 'A', 'address' => 'A', 'latitude' => 56.9, 'longitude' => 24.1]);
        $row = ['station_id' => $station->id, 'fuel_type' => '95', 'price' => 1.6, 'source_type' => 'official_station', 'source_label' => 'Official', 'fetched_at' => now()];
        foreach (array_chunk(array_fill(0, 10001, $row), 500) as $chunk) {
            FuelPrice::insert($chunk);
        }
        $this->getJson('/api/prices/history?fuel_type=95')->assertOk()
            ->assertJsonPath('truncated', true)->assertJsonCount(10000, 'series.0.points');
    }

    public function test_privileged_attributes_and_private_storage_are_not_exposed(): void
    {
        $user = new User;
        $user->fill(['name' => 'Visitor', 'email' => 'visitor@example.com', 'password' => 'test-only-password', 'role' => 'admin']);
        $this->assertNull($user->getAttribute('role'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
        $this->assertFalse(config('filesystems.disks.local.serve'));
        $this->assertSame(storage_path('app/private'), config('filesystems.disks.local.root'));
    }
}
