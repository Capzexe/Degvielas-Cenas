<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustProxies
{
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve cached configuration at request time, after Laravel boots.
        // Do not infer trust from a request hostname or accept forwarded Host.
        $request->setTrustedProxies(
            config('security.trusted_proxies', []),
            Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO,
        );

        return $next($request);
    }
}
