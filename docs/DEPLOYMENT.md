# Hostinger deployment and rollback

The existing server checkout is `/home/u522832521/domains/olivedrab-falcon-903315.hostingersite.com`. Only its `public_html/` directory is web-accessible; the app and `.env` remain in its parent. The repository's `public/` directory is the asset source. Do not upload the whole checkout into `public_html`.

## Before a release

1. Review the diff and run the checks in README. Use current supported PHP releases locally and on the web/CLI runtimes. Run `composer audit` and `npm audit`; do not suppress advisories just to pass.
2. Confirm the production `APP_URL` is the canonical HTTPS URL and `APP_ENV=production`, `APP_DEBUG=false`. Preserve `APP_KEY`, database credentials and existing data. Set `TRUSTED_PROXIES` only to verified proxy addresses if the hosting topology requires it; do not trust arbitrary forwarded headers.
3. In hPanel, apply the web-runtime directives from `deploy/php-production.ini` where the plan permits. System-only controls such as disabled process functions cannot be installed by `.user.ini`. Test any `open_basedir` change with real CA, temporary and application paths. Keep CLI Composer/process access separate.
4. Maintain database backups through Hostinger before schema changes. This hardening release adds no schema migration. The script backs up the public directory and environment; those backups are not database backups.
5. Build assets, commit the application changes, push the reviewed commit, and note its full SHA. `public/build` contains historical tracked assets even though it is now ignored; force-add the newly built output deliberately when committing a release. Never force-add `.env` or `vendor`.

## Release helper

`deploy/hostinger-release.sh` is intentionally tied to the existing Hostinger root. It checks the expected remote SHA and clean tracked state, takes private rollback backups, enters maintenance mode, fast-forwards, installs locked production dependencies, aligns production environment flags, refreshes caches and copies the built public files.

Build the archive from `public/` so it contains only `build/`, then upload it and the helper over authenticated SSH/SCP. Do not accept asset archives from untrusted sources.

```bash
npm ci
npm test
npm run build
tar -czf /tmp/cenalitra-assets.tar.gz -C public build
# Use the existing Hostinger SSH key and verified known_hosts entry.
scp -P 65002 /tmp/cenalitra-assets.tar.gz deploy/hostinger-release.sh USER@HOST:/tmp/
ssh -p 65002 USER@HOST 'bash /tmp/hostinger-release.sh /home/u522832521/domains/olivedrab-falcon-903315.hostingersite.com FULL_COMMIT_SHA /tmp/cenalitra-assets.tar.gz'
```

Use explicit `-i`, `BatchMode=yes`, `StrictHostKeyChecking=yes` and the correct account for automated connections. The helper does not delete old hashed assets, preventing broken in-flight pages. A failed release leaves maintenance mode enabled for deliberate recovery; investigate the error before reopening traffic.

## Verify the live release

- Home, blog, discount and about pages return 200; APIs return valid data.
- HTTP redirects to HTTPS. HTTPS responses contain the full CSP, HSTS, X-Frame-Options, nosniff, referrer and permissions policies. Session cookies are Secure, HttpOnly and SameSite=Lax. No PHP version header is present.
- Browser console has no CSP script errors; map tiles, markers, popups, fuel switching and reload work.
- `POST /api/prices/refresh` returns 404 without importing. Nested filters return 422. Check 429 behavior in automated local tests rather than flooding the live site.
- `/.env`, `/.git/config`, `/composer.json`, `/.user.ini`, and arbitrary `.php` paths return 403/404. Inspect status codes without logging potentially sensitive bodies.
- `php artisan schedule:list` shows the intended schedule; choose either cron or the GitHub job. Confirm the shared cache is writable and persists across processes. The workflow pins the existing SSH host key; verify rotations through a trusted Hostinger channel.

## Rollback

Use the exact private backup path printed by the helper. It contains the prior commit SHA, environment and public-directory archive. Keep these files outside the web root and permission-restricted.

```bash
cd /home/u522832521/domains/olivedrab-falcon-903315.hostingersite.com
php artisan down --retry=60
# Read and verify the recorded SHA first. No reset --hard is necessary.
git checkout --detach VERIFIED_PREVIOUS_SHA
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
cp VERIFIED_BACKUP/environment.backup .env
chmod 600 .env
tar -xzf VERIFIED_BACKUP/public-html.tar.gz
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Extra hashed assets left after rollback are harmless. Restore a database backup only if a separate schema/data change requires it and its recovery has been reviewed. After recovery, deliberately reattach the desired branch before a future release.
