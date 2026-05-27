<?php

namespace Kaikon2\Kaikondb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBadUserAgent
{
    private const ALLOWED_BOTS = [
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'baiduspider',
    ];

    private const BLOCKED_PATTERNS = [
        'scrapy',
        'curl/',
        'wget',
        'python-requests',
        'httpclient',
        'java/',
        'libwww',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ua = strtolower(trim((string) $request->userAgent()));

        if ($ua === '') {
            return response('Forbidden', 403);
        }

        foreach (self::ALLOWED_BOTS as $bot) {
            if (str_contains($ua, $bot)) {
                return $next($request);
            }
        }

        foreach (self::BLOCKED_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return response('Forbidden', 403);
            }
        }

        return $next($request);
    }
}
