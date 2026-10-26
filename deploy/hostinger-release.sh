#!/usr/bin/env bash
# Usage: bash hostinger-release.sh APP_ROOT EXPECTED_COMMIT ASSET_ARCHIVE [GIT_BUNDLE]
set -Eeuo pipefail
umask 077

app_root=$(realpath -- "$1")
expected_commit=$2
asset_archive=$(realpath -- "$3")
expected_root=/home/u522832521/domains/olivedrab-falcon-903315.hostingersite.com
[[ "$app_root" == "$expected_root" ]] || { echo 'Unexpected application root'; exit 1; }
[[ "$expected_commit" =~ ^[0-9a-f]{40}$ ]] || { echo 'Expected full commit SHA'; exit 1; }
[[ -f "$asset_archive" && -d "$app_root/public_html" ]] || exit 1
cd "$app_root"

exec 9>storage/framework/deployment.lock
flock -n 9 || { echo 'Another deployment is running'; exit 1; }
git diff --quiet && git diff --cached --quiet || { echo 'Tracked server changes require review'; exit 1; }
if [[ -n ${4:-} ]]; then
    # Transfer private repositories without copying GitHub credentials to hosting.
    git bundle verify "$4"
    git fetch "$4" refs/heads/main:refs/remotes/origin/main
else
    git fetch origin main
fi
[[ $(git rev-parse origin/main) == "$expected_commit" ]] || { echo 'Remote head changed; review the new commit first'; exit 1; }
git merge-base --is-ancestor HEAD "$expected_commit"

previous_commit=$(git rev-parse HEAD)
backup="storage/app/private/deployments/$(date -u +%Y%m%dT%H%M%SZ)-$previous_commit"
mkdir -p "$backup"
chmod 700 storage/app/private/deployments "$backup"
printf '%s\n' "$previous_commit" > "$backup/previous-commit"
cp -p .env "$backup/environment.backup"
tar -czf "$backup/public-html.tar.gz" public_html
chmod 600 "$backup/"*
printf 'Rollback backup: %s\n' "$backup"

# No destructive cleanup: hashed old assets remain available for in-flight pages.
# On failure leave maintenance mode enabled for operator review and rollback.
php artisan down --retry=60
git merge --ff-only "$expected_commit"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

# Align only security-related environment flags. Preserve keys and DB credentials.
php <<'PHP'
<?php
$path = '.env';
$content = file_get_contents($path);
foreach (['APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'SESSION_SECURE_COOKIE' => 'true'] as $key => $value) {
    $pattern = '/^'.preg_quote($key, '/').'=.*/m';
    $content = preg_match($pattern, $content)
        ? preg_replace($pattern, $key.'='.$value, $content)
        : rtrim($content).PHP_EOL.$key.'='.$value.PHP_EOL;
}
if (file_put_contents($path, $content, LOCK_EX) === false) {
    throw new RuntimeException('Could not update production security flags');
}
chmod($path, 0600);
PHP

php artisan optimize:clear
php artisan migrate --force

# Archive is built locally from public/build and contains build/ only.
tar -xzf "$asset_archive" -C public_html --no-same-owner --no-same-permissions
cp -a public/images/. public_html/images/
cp public/index.php public/.htaccess public/.user.ini public/favicon.svg public_html/
find public_html/build -type d -exec chmod 755 {} +
find public_html/build -type f -exec chmod 644 {} +
chmod 644 public_html/index.php public_html/.htaccess public_html/.user.ini public_html/favicon.svg
# The deployed runtime has a public_html override; cache against that path.
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
printf 'Deployed %s\n' "$(git rev-parse HEAD)"
