# Security release verification — 26 October 2026

## Local verification

- Laravel feature suite: 14 tests pass with 180 assertions against in-memory SQLite; includes rendering/header checks, CSP nonce matching, HTTPS/canonical-host checks, untrusted forwarded headers, public import denial, importer overlap protection, production configuration fail-safes, nested input validation, both API throttles, latest-price ordering, history bounds, mass-assignment allowlists and private storage.
- JavaScript: two tests pass for popup payload escaping and rejection of dangerous source URL schemes.
- Production Vite build succeeds using patched Vite 7.3.7.
- Laravel Pint passes for the full PHP codebase. The empty template unit-test suite was removed so a fresh checkout can run the feature suite without a missing-directory error.
- `composer validate --strict` passes; the pre-existing manifest/lock mismatch is resolved.
- `composer audit`: zero known advisories after updates, including league/commonmark 2.10.4 and symfony/http-foundation 7.4.21.
- `npm audit`: zero known vulnerabilities after compatible updates, including Vite, shell-quote, source-map-js and follow-redirects.

Dependency audits cover the available advisory feeds at verification time, not undiscovered vulnerabilities. PHP on the local XAMPP installation is 8.2.12 and should be upgraded; local tests are not evidence that this runtime is suitable for public hosting.

## Deployment verification

The initial Hostinger inspection confirmed a production environment, HTTPS canonical URL, a separate `public_html` root, file-backed shared cache, and a clean tracked checkout. Before this release, web responses exposed a PHP version, had only an upgrade-insecure-requests CSP, and the session secure flag was not explicit in configuration.

Live deployment of application revision `4c50abf` was verified on 26 October 2026 at approximately 22:10 Europe/Riga:

- Home, blog, discounts, about, sitemap and llms pages returned 200 with the full CSP, HSTS, framing, nosniff, referrer and permissions headers. CSP nonce matched the rendered markup, and no PHP version header remained.
- HTTP redirected to the canonical HTTPS site (Hostinger's edge returned 301). Session cookie flags were Secure, HttpOnly and SameSite=Lax.
- `/.env`, `/.git/config`, `/composer.json`, `/.user.ini` and `/test.php` returned 403. The removed import POST returned 404. A nested fuel filter returned 422.
- Both public APIs returned valid data; history included truncation metadata. Referenced built CSS/JS returned 200 with static security headers.
- A real browser rendered map tiles, markers and station popups. Switching to 95 changed the price list and markers; reloading retrieved stored observations successfully.
- Production `composer audit --no-dev` returned no known advisories. Laravel's schedule listed the import task.
- The first private rollback backup preserves pre-release commit `aadef47`, public files and environment at `storage/app/private/deployments/20261026T200719Z-aadef47ea4957647ed06b33383d41bd99c4682d2`. It is not a database backup.

**Hosting exception:** `/robots.txt` returned 200 from Hostinger with a distinct plain-text response and none of the application's security headers, including when cache-busted. Application pages and built assets passed. Review the hosting/edge robots handling separately; this response is not evidence that Laravel's middleware failed. The smoke check deliberately recorded this as an exception instead of claiming every response passed.

System-only PHP directives in `deploy/php-production.ini` require separate hPanel/server verification and must not be inferred from CLI settings or response-header removal. Hostinger already disables `proc_open` in CLI, so deployment uses Composer `--no-scripts` and invokes package discovery directly; that restriction was preserved. Private-repository releases use verified Git bundles without storing a GitHub credential on hosting.
