# AutoServiss Laravel

AutoServiss is a Laravel training project for a small vehicle service booking system. It has separate customer and staff flows for vehicles, appointment booking, appointment status management, and simple invoice totals.

## Features

- Customer login and demo login.
- Staff/admin demo login.
- Customer vehicle management.
- Appointment booking with service selection.
- Appointment dashboard for customers and staff.
- Staff status updates for booked appointments.
- Simple invoice calculation for labour and parts.
- Feature tests for the main booking workflow.

## Tech Stack

- Laravel 12
- PHP 8.2+
- Blade views
- SQLite locally
- MySQL-compatible database in production

## Main Structure

```text
app/
  Enums/                         Appointment status and user role enums
  Http/Controllers/              Auth, vehicle, and appointment controllers
  Http/Requests/                 Form validation classes
  Models/                        User, Vehicle, Service, Appointment, Invoice
  Services/AppointmentService.php Booking and status update business logic

database/
  migrations/                    Database tables
  seeders/DatabaseSeeder.php     Demo customer, admin, service, and appointment data

resources/views/
  auth/                          Login screen
  appointments/                  Appointment dashboard, create, and detail pages
  vehicles/                      Customer vehicle pages
  layouts/app.blade.php          Main Blade layout and styling

routes/
  web.php                        Browser routes
```

## Local Setup

Open PowerShell:

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\autoserviss-laravel"
composer install
copy .env.example .env
& "C:\xampp\php\php.exe" artisan key:generate
& "C:\xampp\php\php.exe" artisan migrate --force
& "C:\xampp\php\php.exe" artisan db:seed
```

For local SQLite, make sure `.env` contains:

```env
DB_CONNECTION=sqlite
```

## Start Locally

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\autoserviss-laravel"
& "C:\xampp\php\php.exe" -S 127.0.0.1:9001 -t public
```

Open:

```text
http://127.0.0.1:9001/
```

## Demo Accounts

After running `php artisan db:seed`:

```text
Customer: client@example.com / password
Admin:    admin@example.com / password
```

The login page also has demo login buttons.

## Useful Commands

```powershell
& "C:\xampp\php\php.exe" artisan migrate:fresh --seed
& "C:\xampp\php\php.exe" artisan test
& "C:\xampp\php\php.exe" vendor/bin/pint
```

## Repository

This project is separate from the fuel-price website.
