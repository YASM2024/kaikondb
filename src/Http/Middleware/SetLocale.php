<?php

namespace Kaikon2\Kaikondb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = ['ja', 'en'];
        $locale = session('locale', config('app.locale'));

        if (!in_array($locale, $availableLocales)) {
            $locale = config('app.locale'); // デフォルトにフォールバック
        }

        App::setLocale($locale);
        return $next($request);
    }
}
