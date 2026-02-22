<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    private const SUPPORTED_LOCALES = ['en', 'pl', 'de'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->getPreferredLanguage(self::SUPPORTED_LOCALES) ?? 'en';

        app()->setLocale($locale);

        return $next($request);
    }
}
