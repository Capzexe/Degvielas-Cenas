# CenaLitrā.lv

CenaLitrā.lv is a Laravel and Vue web application for tracking publicly available fuel prices in Latvia. It shows the cheapest known prices by fuel type, displays station locations on a map, lists fuel discount offers, and includes SEO pages for long-term organic traffic.

The project is intentionally conservative with data: if a station chain does not publish a reliable price source, the app does not invent prices.

## Features

- Cheapest fuel prices for 95, 98, diesel, and LPG.
- Full-width interactive station map with Leaflet.
- Public Latvian UI with dark theme and CenaLitrā branding.
- Real price collection from supported public sources.
- Historical price records stored on every fetch.
- Fuel discount page with only concrete, usable offers.
- SEO blog, about page, sitemap, robots.txt, and llms.txt.
- JSON API endpoints for the Vue frontend.

## Tech Stack

- Laravel 12
- PHP 8.2+
- Vue 3 Composition API
- Tailwind CSS
- Vite
- Leaflet
- SQLite for local development
- MySQL-compatible hosting for production

## Main Project Structure

```text
app/
  Console/Commands/FetchFuelPrices.php      Fuel price refresh command
  Http/Controllers/Api/                     JSON API controllers
  Models/Station.php                        Gas station model
  Models/FuelPrice.php                      Fuel price model
  Services/FuelPrices/                      Source definitions and page parsing
  Support/BlogPosts.php                     Static SEO blog content
  Support/DiscountOffers.php                Static discount offer content

database/
  migrations/                               Database structure
  seeders/DatabaseSeeder.php                Empty base seeder

resources/
  css/app.css                               Tailwind entry file
  js/components/GasTracker.vue              Main Vue fuel tracker
  views/                                    Laravel Blade pages and layout

routes/
  web.php                                   Public website routes
  api.php                                   Frontend API routes
  console.php                               Scheduler setup

public/
  images/cenalitra-logo.png                 Website logo
  favicon.svg                               Browser icon
  build/                                    Compiled frontend assets
```

## Local Setup

Use the renamed project folder:

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\cenalitra-lv"
```

Install PHP and Node dependencies if they are missing:

```powershell
composer install
npm install
```

Create the environment file if it does not exist:

```powershell
copy .env.example .env
& "C:\xampp\php\php.exe" artisan key:generate
```

For local SQLite, make sure `.env` contains:

```env
DB_CONNECTION=sqlite
```

Then prepare the database:

```powershell
& "C:\xampp\php\php.exe" artisan migrate --force
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
```

## Start The Website Locally

Open two PowerShell windows.

Window 1: start Vite for frontend assets.

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\cenalitra-lv"
npm run dev
```

Window 2: start Laravel through XAMPP PHP.

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\cenalitra-lv"
& "C:\xampp\php\php.exe" -S 127.0.0.1:9000 -t public
```

Open:

```text
http://127.0.0.1:9000/
```

## Refresh Fuel Prices

Run this any time you want to collect fresh prices:

```powershell
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
```

The command stores a new price row each time it finds a valid public price. This means the app can build historical data over time even if the history chart is not currently displayed on the home page.

## Supported Data Sources

The scraper is built around source-specific public pages and only saves data it can actually parse.

Currently supported:

- Circle K
- Viada
- Virši
- Straujupīte

Not currently filled automatically:

- Neste, because a public station-level online price feed has not been confirmed.
- KOOL, because the public page did not expose usable price text to the scraper.

## Main URLs

```text
/                         Home page and fuel map
/degvielas-atlaides       Fuel discounts
/blog                     SEO blog
/par-projektu             About page
/sitemap.xml              XML sitemap
/robots.txt               Search crawler rules
/llms.txt                 LLM-readable project summary
```

## API URLs

```text
/api/stations/cheapest?fuel_type=95
/api/stations/cheapest?fuel_type=98
/api/stations/cheapest?fuel_type=Diesel
/api/stations/cheapest?fuel_type=LPG
/api/prices/history?fuel_type=95&days=30
```

## Production Build

Compile frontend assets before deploying CSS or Vue changes:

```powershell
npm run build
```

This creates production files in:

```text
public/build
```

## Hostinger Deployment Notes

The app can be hosted on Hostinger if the hosting plan supports PHP, Composer dependencies, and a database.

Recommended production steps:

```bash
git pull --ff-only
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If Vue, CSS, logo, favicon, or other public assets change, upload or copy the updated `public/build`, `public/images`, and favicon files to the hosting public folder.

For automatic refresh, add a cron job:

```bash
php /home/USER/domains/DOMAIN/artisan schedule:run
```

or run the fuel command directly:

```bash
php /home/USER/domains/DOMAIN/artisan fetch:fuel-prices
```

## Development Notes

- Website pages are Blade files in `resources/views`.
- The main interactive tracker is Vue in `resources/js/components/GasTracker.vue`.
- Backend API logic is in `app/Http/Controllers/Api`.
- Data parsing is in `app/Services/FuelPrices`.
- The database tables are `stations` and `prices`.
- Public website routes live in `routes/web.php`.
- JSON API routes live in `routes/api.php`.

## Testing

Run Laravel tests:

```powershell
& "C:\xampp\php\php.exe" artisan test
```

Run PHP formatting:

```powershell
& "C:\xampp\php\php.exe" vendor/bin/pint
```

## Repository

GitHub:

```text
https://github.com/Capzexe/Degvielas-Cenas
```
