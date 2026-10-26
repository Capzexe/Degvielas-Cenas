# Application security audit — 26 October 2026

Scope: Laravel routes, controllers, models, importer, Vue/Blade rendering, configuration, dependency manifests, GitHub import workflow, and the existing Hostinger layout. This is a source review and targeted regression exercise, not a certification or a full penetration test. Current primary references are linked below; there is no single universal “2026 Laravel security standard”.

## Findings, fixes and tests

| Risk and exploit scenario | Implemented fix / exact source | How to verify |
| --- | --- | --- |
| **High: unauthenticated job execution.** Anyone could POST to `/api/prices/refresh` and consume web workers, trigger outgoing requests and mutate imported data. A ten-minute cache cooldown did not provide authorization. | Removed the route and controller. Imports are CLI-only via SSH/scheduler; `FetchFuelPrices` uses a shared one-hour cache lock. The UI reloads stored data. | `SecurityTest::test_public_requests_cannot_run_imports` asserts 404, no Artisan call, and no price writes. Test concurrent CLI runs in staging; the second exits without importing. |
| **High: stored DOM XSS sink.** A compromised upstream source or contaminated database could place HTML in station names, addresses or labels. Leaflet interpreted the interpolated popup as HTML. | `resources/js/security.js` escapes every popup field. Source links accept only HTTPS URLs without credentials. JSON-LD now uses safe hex escaping and a CSP nonce. | `npm test` injects image/script payloads into every popup field and checks unsafe URL schemes. Inspect popups with these values in a local database; the text must display literally. |
| **Medium: missing layered browser protections.** A malicious site could frame the app; injected scripts had no meaningful CSP restriction. | Global `SecurityHeaders` middleware emits nonce-based script CSP, DENY framing, nosniff, referrer and permissions policies. HSTS is emitted for production HTTPS. Public Apache rules also protect static/error responses. | Header and nonce regression tests cover normal, validation-error and missing routes. On the live site inspect `curl -I` and browser console. |
| **Medium: insecure production defaults and host trust.** Misconfigured debug mode could reveal stack traces; HTTP could expose cookies; forged Host or forwarded headers could poison URLs or bypass IP limits. | `config/app.php` disables debug outside local/testing. Sessions force Secure outside those environments, HttpOnly and Lax. `EnforceHttps` requires an HTTPS `APP_URL`, validates the exact canonical host and redirects HTTP with 308. Only explicitly configured proxy IPs are trusted; forwarded Host is never trusted. | HTTPS/host-spoof and forwarded-IP tests. In staging set `APP_DEBUG=true` with `APP_ENV=production`; errors must remain generic. Check cookie flags and HTTP redirects. |
| **Medium: API resource exhaustion.** Repeated history queries could hydrate every matching observation in memory; public reads had no shared throttle. | Named 60/minute public and 10/minute history IP limiters; scalar FormRequests reject nested filters; history hydrates at most 10,001 rows and exposes at most 10,000 with a truncation flag. | Validation, rate-limit and 10,001-record regression tests in `SecurityTest`. No live load testing is needed. |
| **Medium: unsafe operational defaults.** Automatic local-disk serving and accidental public copies could expose private debug snapshots, backups or configuration. | Local disk stays under `storage/app/private` and automatic serving is disabled. Public rules deny dotfiles, backups, manifests and direct PHP execution except the front controller. Deployment keeps source and secrets outside `public_html`. | Private storage configuration test; HTTP checks for `/.env`, `/.git/config`, `/composer.json`, `/test.php` must return 403/404. Never print response bodies during secret-exposure checks. |
| **Medium: SSH workflow trusted a live key scan.** An attacker controlling that connection could substitute their own server key during a fresh CI run. | Workflow now pins the Ed25519 host key from the pre-existing authenticated Hostinger connection, enforces strict checking and grants no GitHub token permissions. | Compare the pinned fingerprint through Hostinger's trusted management channel after a legitimate host-key rotation; an unexpected replacement must fail. |

## Reviewed controls that were already safe

- `User`, `Station` and `FuelPrice` have explicit `$fillable` fields; no model uses `$guarded = []`. Role is not mass assignable. Existing passwords use Laravel's hashed cast and sensitive user fields are hidden.
- No `request()->all()` assignment, untrusted SQL concatenation, `eval`, shell execution from HTTP input or upload endpoint was found. Both public query controllers now consume only FormRequest-validated fields.
- Two `selectRaw('MAX(id)')` expressions remain intentionally: they contain constant SQL and no input. Fuel filters, dates and price ranges use query-builder bindings. Removing this static aggregation would not improve SQL injection protection.
- Public routes intentionally authorize read access. No login, password reset, JWT/Sanctum, logout or model-write API exists, so there is no active token lifecycle or authenticated mutation route to retrofit. The unused user model is not an authentication feature.
- Blade escapes page content. Structured-data blocks now additionally escape HTML-sensitive JSON characters. Import URLs come from trusted source definitions; no HTTP parameter selects an outbound URL. Geocoding sends text as query parameters to a fixed HTTPS endpoint.

## CSP decisions and operational limits

Scripts allow self and a cryptographic nonce, without `unsafe-inline` or `unsafe-eval`. Fonts and connections are same-origin; OpenStreetMap tile images and data images are explicitly allowed. Objects, arbitrary default resources, framing and base URLs are denied. Leaflet requires inline style attributes to position tiles and markers, so `style-src` includes `unsafe-inline`; it is not permitted for scripts. Local Vite development adds only the configured hot-file HTTP/WebSocket origin. Verify the browser map after deploying any CSP change.

HTTPS enforcement and HSTS rely on the correct environment and canonical `APP_URL`. Local/testing are explicit exceptions. Do not mark a deployed instance as local. Configure additional domains as redirects at the web server, not by trusting every Host. If a real proxy terminates TLS, set only verified proxy IPs/CIDRs in `TRUSTED_PROXIES`; never use `*`. Edge protection is still required against volumetric traffic before PHP.

`public/.user.ini` applies supported per-directory error/session controls. `deploy/php-production.ini` is the exact recommended **web-SAPI** template for hPanel/server settings: `expose_php`, `file_uploads`, `disable_functions` and some other directives cannot be enforced by Laravel or a per-directory file. Keep Composer/deployment CLI settings separate because process functions may be needed there. Tailor `open_basedir` to real app/temp/CA paths before enabling it; it can break legitimate operations and is not a sandbox. Do not assume these system controls are active until verified in the hosting panel or a private web-runtime diagnostic. CLI PHP may differ from the web runtime.

## Secure upload pattern for a future authenticated feature

No upload route is enabled. Keep `file_uploads=Off` until one is intentionally introduced. If adding station attachments, install a policy/Gate and a private persistence model first. This example accepts only raster images, verifies file content and extension, limits nested keys, and never stores the original filename. Do not accept SVG/HTML or serve uploaded content inline from the application origin.

```php
// AppServiceProvider::boot(); role must be assigned out-of-band, never from a request.
Gate::define('upload-station-attachment', fn (User $user, Station $station): bool => $user->role === 'admin');

// app/Http/Requests/StoreStationAttachmentRequest.php
class StoreStationAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('upload-station-attachment', $this->route('station')) ?? false;
    }

    public function rules(): array
    {
        return [
            'attachment' => ['required', File::types(['jpg', 'jpeg', 'png'])->max(5120), 'extensions:jpg,jpeg,png'],
            'metadata' => ['sometimes', 'array:caption'],
            'metadata.caption' => ['sometimes', 'nullable', 'string', 'max:200'],
        ];
    }
}

// An authenticated controller, after authorization through that FormRequest:
$path = $request->file('attachment')->store('station-attachments', 'local');
abort_if($path === false, 500); // Laravel generates a random filename.
// Persist $path and owner/station IDs server-side. Quarantine/scan before release.
// Download by opaque attachment ID after a policy check, never by a supplied path:
Gate::authorize('view', $attachment);
return Storage::disk('local')->download($attachment->path, 'attachment', [
    'Content-Type' => 'application/octet-stream',
    'X-Content-Type-Options' => 'nosniff',
]);
```

Imports needed for that example are `App\Models\User`, `App\Models\Station`, `Illuminate\Foundation\Http\FormRequest`, `Illuminate\Validation\Rules\File`, and the `Gate`/`Storage` facades. Wire a POST route only after authentication, a named upload limiter, policy tests and a CSRF-protected web session are in place. Test anonymous 401/403, wrong-role 403, unknown nested keys 422, oversized/type-spoofed files 422, randomized private paths and cross-user download denial. Add malware scanning and image decoding/re-encoding for hostile uploads; MIME validation alone cannot certify harmless content.

## Future authentication and token requirements

Prefer Laravel's maintained authentication scaffolding rather than custom password cryptography. Attach a named login limiter keyed by normalized identity **and** IP (for example 5/minute), a reset-request limiter (3/minute per IP plus per-identity protection), and a limiter on reset submission. Preserve CSRF protection, regenerate the session after login, return neutral reset messages, and keep reset tokens expiring and single-use. Add server-side model policies to each create/update/delete/download route; UI visibility is not authorization.

If Sanctum is later installed, prefer HttpOnly session cookies for this same-origin SPA. For real API clients, create minimal abilities and an explicit expiration, reveal plaintext only once, never persist it in browser localStorage, and require both authentication and ability middleware. Revoke the current personal access token on API logout; for session logout call `Auth::logout()`, invalidate the session and regenerate its CSRF token. Test expired/revoked tokens, cross-user access and reset/login throttles before enabling those routes. JWT is not currently needed.

## Secrets, deployment and verification

`.env` is ignored and must remain outside the web root. Do not commit private keys, tokens, auth.json, snapshots or database exports. Rotate any secret if an exposure is confirmed; do not rotate `APP_KEY` blindly because it affects encrypted data and sessions. Use least-privilege database credentials, private logs, restricted backup permissions and one maintained scheduler.

Run `php artisan test`, `npm test`, `npm run build`, `php vendor/bin/pint --test`, `composer validate --strict`, `composer audit`, and `npm audit`. Dependency results and deployment evidence belong in [verification notes](VERIFICATION.md). Rerun audits on every release and review automatic update PRs. Update the old local XAMPP runtime before exposing a development server beyond loopback.

## Primary references

- [Laravel 12 requests, trusted proxies and hosts](https://laravel.com/docs/12.x/requests)
- [Laravel 12 validation and file rules](https://laravel.com/docs/12.x/validation)
- [Laravel 12 rate limiting](https://laravel.com/docs/12.x/routing#rate-limiting)
- [Laravel 12 Vite CSP nonces](https://laravel.com/docs/12.x/vite#content-security-policy-csp-nonce)
- [PHP session security directives](https://www.php.net/manual/en/session.security.ini.php)
- [PHP core directives and their configuration scopes](https://www.php.net/manual/en/ini.core.php)
