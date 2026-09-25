<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoserviceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/appointments')->assertRedirect('/login');
    }

    public function test_customer_can_add_vehicle(): void
    {
        $user = User::factory()->create(['role' => Role::Customer]);

        $this->actingAs($user)->post('/vehicles', [
            'make' => 'BMW',
            'model' => '320d',
            'year' => 2018,
            'registration_number' => 'AA-100',
        ])->assertRedirect('/vehicles');

        $this->assertDatabaseHas('vehicles', [
            'user_id' => $user->id,
            'make' => 'BMW',
            'model' => '320d',
        ]);
    }

    public function test_customer_can_book_appointment(): void
    {
        [$user, $vehicle, $service] = $this->customerSetup();
        $time = now()->addDays(2)->setTime(11, 0)->format('Y-m-d H:i:s');

        $this->actingAs($user)->post('/appointments', [
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => $time,
            'customer_notes' => 'Parbaudit bremzes.',
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'status' => AppointmentStatus::Booked->value,
        ]);
    }

    public function test_customer_and_admin_have_different_dashboards(): void
    {
        $customer = User::factory()->create(['role' => Role::Customer]);
        $admin = User::factory()->create(['role' => Role::Admin]);

        $this->actingAs($customer)->get('/appointments')
            ->assertOk()
            ->assertSee('Mans servisa panelis')
            ->assertDontSee('Servisa darba panelis');

        $this->actingAs($admin)->get('/appointments')
            ->assertOk()
            ->assertSee('Servisa darba panelis')
            ->assertDontSee('Mans servisa panelis');
    }

    public function test_same_time_cannot_be_booked_twice(): void
    {
        [$user, $vehicle, $service] = $this->customerSetup();
        $time = now()->addDays(3)->setTime(9, 0)->format('Y-m-d H:i:s');

        Appointment::query()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => $time,
        ]);

        $this->actingAs($user)->from('/appointments/create')->post('/appointments', [
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => $time,
        ])->assertRedirect('/appointments/create')
            ->assertSessionHasErrors('scheduled_at');
    }

    public function test_customer_cannot_book_on_weekend(): void
    {
        [$user, $vehicle, $service] = $this->customerSetup();
        $saturday = now()->next('Saturday')->setTime(10, 0)->format('Y-m-d H:i:s');

        $this->actingAs($user)->from('/appointments/create')->post('/appointments', [
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => $saturday,
        ])->assertRedirect('/appointments/create')
            ->assertSessionHasErrors('scheduled_at');

        $this->assertDatabaseMissing('appointments', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'scheduled_at' => $saturday,
        ]);
    }

    public function test_customer_cannot_open_other_customer_appointment(): void
    {
        [$owner, $vehicle, $service] = $this->customerSetup();
        $other = User::factory()->create(['role' => Role::Customer]);

        $appointment = Appointment::query()->create([
            'user_id' => $owner->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => now()->addDays(4),
        ]);

        $this->actingAs($other)->get('/appointments/'.$appointment->id)->assertForbidden();
    }

    public function test_admin_can_update_status_and_invoice(): void
    {
        [$user, $vehicle, $service] = $this->customerSetup();
        $admin = User::factory()->create(['role' => Role::Admin]);
        $appointment = Appointment::query()->create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'service_id' => $service->id,
            'scheduled_at' => now()->addDays(5),
        ]);

        $this->actingAs($admin)->patch('/appointments/'.$appointment->id.'/status', [
            'status' => AppointmentStatus::Ready->value,
            'admin_notes' => 'Auto gatavs sanemsanai.',
            'labor_eur' => 35,
            'parts_eur' => 12.50,
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => AppointmentStatus::Ready->value,
        ]);

        $this->assertDatabaseHas('invoices', [
            'appointment_id' => $appointment->id,
            'labor_cents' => 3500,
            'parts_cents' => 1250,
        ]);
    }

    /**
     * @return array{0: User, 1: \App\Models\Vehicle, 2: Service}
     */
    private function customerSetup(): array
    {
        $user = User::factory()->create(['role' => Role::Customer]);
        $vehicle = $user->vehicles()->create([
            'make' => 'Audi',
            'model' => 'A4',
            'year' => 2017,
        ]);
        $service = Service::query()->create([
            'name' => 'Diagnostika',
            'duration_minutes' => 60,
            'price_cents' => 4500,
        ]);

        return [$user, $vehicle, $service];
    }
}
