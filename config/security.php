<?php

return [
    // Local development and automated tests may use HTTP. Every deployed
    // environment requires HTTPS and the canonical APP_URL host.
    'force_https' => ! in_array(env('APP_ENV', 'production'), ['local', 'testing'], true),
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
];
