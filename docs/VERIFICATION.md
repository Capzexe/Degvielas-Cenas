# Security release verification — 26 October 2026

## Local verification

- Laravel feature suite: all tests pass against in-memory SQLite; includes rendering/header checks, CSP nonce matching, HTTPS/canonical-host checks, untrusted forwarded headers, public import denial, nested input validation, both API throttles, latest-price ordering, history bounds, mass-assignment allowlists and private storage.
- JavaScript: two tests pass for popup payload escaping and rejection of dangerous source URL schemes.
- Production Vite build succeeds using patched Vite 7.3.7.
- Laravel Pint passes for changed PHP files and all added middleware, requests and security tests.
- `composer validate --strict` passes; the pre-existing manifest/lock mismatch is resolved.
- `composer audit`: zero known advisories after updates, including league/commonmark 2.10.4 and symfony/http-foundation 7.4.21.
- `npm audit`: zero known vulnerabilities after compatible updates, including Vite, shell-quote, source-map-js and follow-redirects.

Dependency audits cover the available advisory feeds at verification time, not undiscovered vulnerabilities. PHP on the local XAMPP installation is 8.2.12 and should be upgraded; local tests are not evidence that this runtime is suitable for public hosting.

## Deployment verification

The initial Hostinger inspection confirmed a production environment, HTTPS canonical URL, a separate `public_html` root, file-backed shared cache, and a clean tracked checkout. Before this release, web responses exposed a PHP version, had only an upgrade-insecure-requests CSP, and the session secure flag was not explicit in configuration.

Live release checks are recorded after deployment. System-only PHP directives in `deploy/php-production.ini` require separate hPanel/server verification and must not be inferred from CLI settings or response-header removal.
