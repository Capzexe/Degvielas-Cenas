<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('security.force_https')) {
            $canonical = (string) config('app.url');
            $host = parse_url($canonical, PHP_URL_HOST);

            abort_unless(parse_url($canonical, PHP_URL_SCHEME) === 'https' && is_string($host), 503);
            abort_unless(strcasecmp($request->getHost(), $host) === 0, 400);

            if (! $request->isSecure()) {
                return redirect()->away(rtrim($canonical, '/').$request->getRequestUri(), 308);
            }
        }

        return $next($request);
    }
}
