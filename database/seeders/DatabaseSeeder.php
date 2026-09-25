<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $client = User::query()->updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Demo Klients',
                'password' => 'password',
                'role' => Role::Customer,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Servisa Admins',
                'password' => 'password',
                'role' => Role::Admin,
            ],
        );

        $oil = Service::query()->updateOrCreate(
            ['name' => 'Ellas un filtru maina'],
            [
                'description' => 'Standarta apkopes darbs.',
                'duration_minutes' => 60,
                'price_cents' => 6500,
            ],
        );

        Service::query()->updateOrCreate(
            ['name' => 'Diagnostika'],
            [
                'description' => 'Elektronikas un mehanikas parbaude.',
                'duration_minutes' => 90,
                'price_cents' => 4500,
            ],
        );

        $vehicle = $client->vehicles()->firstOrCreate(
            ['registration_number' => 'AB-1234'],
            [
                'make' => 'Volkswagen',
                'model' => 'Golf',
                'year' => 2016,
            ],
        );

        $client->appointments()->firstOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'scheduled_at' => now()->addDay()->setTime(10, 0),
            ],
            [
                'service_id' => $oil->id,
                'customer_notes' => 'Mainit ari salona filtru, ja vajag.',
            ],
        );
    }
}
