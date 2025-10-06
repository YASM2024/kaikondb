<?php

namespace Kaikon2\Kaikondb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\IpUtils;

class FilterByWhitelistIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $trustedIps = config('kaikon.TRUSTED_IPS', []);

        if (!IpUtils::checkIp($ip, $trustedIps)) {
            return response('Access denied: IP not trusted.', 403);
        }

        return $next($request);
    }
}
