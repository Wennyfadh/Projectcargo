<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $normalized = strtolower(trim($locale));
        if ($normalized === 'cn') {
            $normalized = 'zh';
        }

        if (in_array($normalized, SetLocale::SUPPORTED_LOCALES, true)) {
            session(['locale' => $normalized]);
        }

        return redirect()->back();
    }
}
