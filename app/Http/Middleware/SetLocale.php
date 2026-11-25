<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported official world languages:
     * - id: Bahasa Indonesia (Official national language)
     * - en: English (UN Official / International Maritime & Logistics)
     * - zh: Chinese / 中文 (UN Official / Major Export Market)
     * - es: Spanish / Español (UN Official / Latin America & Philippines)
     * - ar: Arabic / العربية (UN Official / Middle East Export: Saudi, UAE)
     * - fr: French / Français (UN Official / Europe & West Africa)
     * - ru: Russian / Русский (UN Official / Eurasian Trade)
     */
    public const SUPPORTED_LOCALES = ['id', 'en', 'zh', 'es', 'ar', 'fr', 'ru'];

    public function handle(Request $request, Closure $next): Response
    {
        // Check query parameter (?lang=xx or ?locale=xx) first
        $requestedLocale = $request->query('locale') ?? $request->query('lang');
        if ($requestedLocale) {
            $normalized = strtolower(trim((string) $requestedLocale));
            if ($normalized === 'cn') {
                $normalized = 'zh';
            }
            if (in_array($normalized, self::SUPPORTED_LOCALES, true)) {
                session(['locale' => $normalized]);
            }
        }

        $locale = session('locale', config('app.locale', 'id'));
        if ($locale === 'cn') {
            $locale = 'zh';
        }

        if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.locale', 'id'));
        }

        return $next($request);
    }
}
