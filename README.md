# CenaLitrā.lv — public fuel-price comparison for Latvia

CenaLitrā helps Latvian drivers compare published petrol, diesel and LPG prices before deciding where to refuel. It combines source-labelled prices, station locations and loyalty discounts in one Latvian-language website. Observations from public sources do not guarantee the price at the pump.

**Live website:** [CenaLitrā on Hostinger](https://olivedrab-falcon-903315.hostingersite.com/)

## Key features

- **Price comparison:** compare 95, 98, Diesel and LPG with source labels and observation times.
- **Interactive map:** Leaflet and OpenStreetMap show verified station locations; network-wide and published minimum prices remain clearly labelled.
- **Automated collection:** source-specific parsers collect Circle K, Viada, Virši and Straujupīte prices every six hours where parsing succeeds. Missing or unsupported data is not fabricated.
- **Useful context:** loyalty discounts, Latvian articles, structured data, sitemap and crawler metadata.
- **History API:** retrieve up to one year of observations with bounded results and explicit truncation metadata.

## Tech stack

| Layer | Technology |
| --- | --- |
| Frontend | Vue 3, Tailwind CSS 4, Vite, Leaflet |
| Backend | Laravel 12, PHP 8.2+; use a current patched release |
| Database | SQLite locally; MySQL-compatible database on Hostinger |
| Hosting and jobs | Hostinger, GitHub Actions, Laravel scheduler |
| External services | Fuel-chain public pages, OpenStreetMap tiles, Nominatim geocoding |

## Technical architecture and decisions

- Laravel handles ingestion, persistence and public JSON endpoints. Vue renders the interactive comparison; Blade serves the content pages and search metadata.
- Source-specific parsers distinguish station prices from network-wide and published minimum prices. Each observation retains its source and timestamp so users can judge its meaning and freshness.
- Imports run through trusted CLI jobs with a shared cache lock. Public HTTP traffic can read observations but cannot start scraping or write model data.

```text
Public fuel pages → CLI importer → stations / prices → JSON API → Vue + Leaflet
                                          └─────────→ Blade content + SEO
```

## Local setup

Install Git, Composer 2, Node.js 22.12+, and a supported patched PHP release with Laravel's required extensions plus pdo_sqlite, curl, mbstring, xml, fileinfo and zip.

```bash
git clone https://github.com/Capzexe/Degvielas-Cenas.git
cd Degvielas-Cenas
composer install
npm ci
cp .env.example .env
# PowerShell alternative: Copy-Item .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

The example uses SQLite and disables debug output by default. Set `APP_ENV=local`, `APP_URL=http://127.0.0.1:8000`, `DB_CONNECTION=sqlite`, and `SESSION_SECURE_COOKIE=false` for local HTTP. Leave `DB_DATABASE` unset to use `database/database.sqlite`.

Open [the local website](http://127.0.0.1:8000). For hot reload, run `npm run dev` in a second terminal. Populate prices separately:

```bash
php artisan fetch:fuel-prices
# Optional private parser snapshots:
php artisan fetch:fuel-prices --debug-source=Viada
```

A new database is empty until an import succeeds. Imports contact external sites and may take several minutes when geocoding uncached stations. Debug files are stored under `storage/app/private/debug`.

## Pages and API

| URL | Purpose |
| --- | --- |
| `/` | Comparison and map |
| `/degvielas-atlaides` | Fuel discounts |
| `/blog`, `/blog/{slug}` | Latvian articles |
| `/par-projektu` | About the project |
| `/sitemap.xml`, `/robots.txt`, `/llms.txt` | Discovery metadata |
| `/api/stations/cheapest?fuel_type=95` | Latest prices sorted by fuel |
| `/api/prices/history?fuel_type=95&days=30` | Historical observations |

`fuel_type` accepts `95`, `98`, `Diesel` or `LPG`. It is optional for stations and required for history. `days` is an integer from 1 to 365, default 90. History considers the latest 10,000 matching observations, returns up to six series, and sets `truncated: true` when older matching records were omitted. Invalid input returns `422` with `Accept: application/json`.

Public API reads share a 60-per-minute IP limit; history also has a 10-per-minute IP limit. Exceeding either returns `429` with `Retry-After`. Shared networks share a budget. The reload button retrieves stored data; it does not trigger an import.

## Security

Global middleware enforces production HTTPS, canonical hosts, a nonce CSP, HSTS, clickjacking protection, MIME protection, referrer and browser-permissions policies. Production session cookies are Secure, HttpOnly and SameSite=Lax. Debug output stays disabled in deployed environments even if `APP_DEBUG=true` is mistakenly configured.

The public API is read-only. There are no login, registration, password-reset, token-issuing or file-upload routes. Models use explicit mass-assignment allowlists. Private storage remains outside the document root and automatic temporary file serving is disabled.

See the [audit and verification instructions](docs/SECURITY-AUDIT.md), [security policy](SECURITY.md) and [deployment guide](docs/DEPLOYMENT.md). They explain the Leaflet style exception, server-only PHP controls, future authentication/upload patterns and operational limitations. Hardening is not a guarantee of absence of vulnerabilities.

## Verification

```bash
npm test
npm run build
php artisan test
php vendor/bin/pint --test
composer validate --strict
composer audit
npm audit
```

Tests use isolated in-memory SQLite and never import live fuel prices. Rerun dependency audits before releases; results change over time. Dependabot is configured for weekly Composer, npm and workflow updates.

## Deployment and scheduling

Production uses a separate `public_html/` directory alongside the Laravel app, supported by `bootstrap/app.php`. Deploy the application and newly generated `public/build/`, and copy public assets and web rules to `public_html/`. Never upload the repository root, `.env`, database dumps or `vendor/` into the document root. Never regenerate the production `APP_KEY`.

Follow the [deployment and rollback guide](docs/DEPLOYMENT.md). The included GitHub Actions workflow uses the `HOSTINGER_SSH_KEY` repository secret and a pinned SSH host key to run the importer every six hours in UTC. Alternatively, use one scheduler cron:

```cron
* * * * * cd /absolute/path/to/application && php artisan schedule:run >> /dev/null 2>&1
```

Laravel schedules every six hours in Europe/Riga. Choose one scheduling mechanism; the shared import lock also prevents simultaneous runs.

## Project structure

```text
app/Console/Commands/        Import command and shared lock
app/Services/FuelPrices/     Parsers, source definitions and geocoding
app/Http/Controllers/Api/    Read-only public API
app/Http/Requests/           Validated filters and history bounds
app/Http/Middleware/         HTTPS and response protections
resources/js/               Vue map and safe popup rendering
resources/views/            Public content and structured data
deploy/                     PHP and Nginx configuration examples
docs/                       Audit and deployment instructions
tests/                      Laravel and JavaScript regression tests
```

## Future improvements

- Add verified station feeds for more chains, plus parser-health monitoring and freshness alerts.
- Add an accessible history chart with pagination or daily aggregation, preserving source provenance and request budgets.

