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
        $locale = session('locale', config('app.locale', 'id'));

        if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
