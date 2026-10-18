<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['ms', 'en'];
        $locale = config('app.locale', 'ms');

        if ($request->hasSession() && $request->session()->has('locale')) {
            $sessionLocale = $request->session()->get('locale');
            if (in_array($sessionLocale, $supportedLocales, true)) {
                $locale = $sessionLocale;
            }
        } elseif ($request->user() && !empty($request->user()->locale)) {
            $userLocale = $request->user()->locale;
            if (in_array($userLocale, $supportedLocales, true)) {
                $locale = $userLocale;
                if ($request->hasSession()) {
                    $request->session()->put('locale', $locale);
                }
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
