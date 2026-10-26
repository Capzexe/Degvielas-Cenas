<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);
        $nonce = Vite::cspNonce();
        $scripts = "'self' 'nonce-{$nonce}'";
        $connections = "'self'";
        $styles = "'self' 'unsafe-inline'";

        // Vite's hot-file URL is local configuration, never request input.
        if (app()->environment('local') && Vite::isRunningHot()) {
            $url = trim(file_get_contents(Vite::hotFile()));
            $parts = parse_url($url);
            if (isset($parts['scheme'], $parts['host'])) {
                $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
                $scripts .= ' '.$origin;
                $styles .= ' '.$origin;
                $connections .= ' '.$origin.' '.preg_replace('/^http/', 'ws', $origin);
            }
        }

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'none'",
            "script-src {$scripts}",
            "script-src-attr 'none'",
            // Leaflet positions tiles and markers with inline style attributes.
            "style-src {$styles}",
            "img-src 'self' data: https://*.tile.openstreetmap.org",
            "font-src 'self'",
            "connect-src {$connections}",
            "object-src 'none'",
            "base-uri 'none'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]));
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->remove('X-Powered-By');

        if (config('security.force_https') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
