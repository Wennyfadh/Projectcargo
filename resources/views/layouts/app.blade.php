<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CargoTrace') · PT Mayora Indah Tbk Global Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 800: '#102d48', 900: '#0b2036', 950: '#071a2d' },
                        ocean: { 500: '#1784a5', 600: '#116d8a' }
                    },
                    animation: {
                        'marquee': 'marquee 38s linear infinite',
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(0%)' },
                            '100%': { transform: 'translateX(-50%)' },
                        }
                    }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('head')
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-mono-numbers { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        .nav-link { transition: background-color .15s, color .15s; }
        .nav-link:hover { background: rgba(255,255,255,.09); color: white; }
        .nav-link.active { background: #14627d; color: white; box-shadow: inset 3px 0 #46c0d2; }
        .leaflet-container { z-index: 0; }

        /* Ticker styling with pause on hover */
        .marquee-track {
            display: inline-flex;
            white-space: nowrap;
            will-change: transform;
        }
        .marquee-container:hover .marquee-track {
            animation-play-state: paused !important;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased selection:bg-teal-500 selection:text-white">
    <div class="min-h-screen lg:flex">
        <!-- Sidebar Navigation -->
        <aside class="flex w-full flex-col bg-navy-900 text-slate-300 lg:fixed lg:inset-y-0 lg:w-56 z-30">
            <a href="{{ route('dashboard') }}" class="flex h-16 items-center gap-3 border-b border-white/10 px-5 text-lg font-bold text-white">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 to-ocean-600 font-black text-navy-950 shadow-md">CT</span>
                <span class="tracking-tight">CargoTrace</span>
            </a>
            <nav class="flex gap-1 overflow-x-auto p-3 lg:flex-1 lg:flex-col lg:overflow-y-auto">
                @php
                    $navigation = [
                        ['route' => 'dashboard', 'label' => __('messages.nav.dashboard'), 'icon' => '▦', 'roles' => ['admin', 'operator', 'customer']],
                        ['route' => 'shipments.index', 'label' => __('messages.nav.shipments'), 'icon' => '◇', 'roles' => ['admin', 'operator', 'customer']],
                        ['route' => 'currency.index', 'label' => __('messages.nav.currency'), 'icon' => '💱', 'roles' => ['admin', 'operator', 'customer']],
                        ['route' => 'tracking.index', 'label' => __('messages.nav.tracking'), 'icon' => '⌖', 'roles' => ['admin', 'operator', 'customer']],
                        ['route' => 'exceptions.index', 'label' => __('messages.nav.exceptions'), 'icon' => '△', 'roles' => ['admin', 'operator', 'customer']],
                        ['route' => 'carriers.index', 'label' => __('messages.nav.carriers'), 'icon' => '▱', 'roles' => ['admin', 'operator', 'customer']],
                        ['route' => 'reports.index', 'label' => __('messages.nav.reports'), 'icon' => '▤', 'roles' => ['admin', 'operator']],
                    ];
                @endphp
                @foreach($navigation as $item)
                    @if(in_array(auth()->user()->role, $item['roles'], true))
                        <a href="{{ route($item['route']) }}"
                           class="nav-link flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs($item['route']) || ($item['route'] === 'tracking.index' && request()->routeIs('tracking.show')) ? 'active' : '' }}">
                            <span class="w-5 text-center text-lg">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="hidden border-t border-white/10 p-4 lg:block">
                <div class="mb-1 truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                <div class="mb-3 text-xs capitalize text-slate-400">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full rounded-lg px-3 py-2 text-left text-xs font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">↪ &nbsp; {{ __('messages.nav.logout') }}</button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="min-w-0 flex-1 lg:ml-56 flex flex-col">
            <!-- 1. LIVE RUNNING TEXT MARQUEE TICKER (WAKTU, SUHU, KURS, SUKU BUNGA, BERITA) -->
            <div class="bg-navy-950 border-b border-navy-800 text-slate-300 text-xs shadow-sm sticky top-0 z-20 overflow-hidden">
                <div class="flex items-center h-10 px-3 md:px-5">
                    <!-- Left Badge -->
                    <div class="shrink-0 flex items-center gap-2 pr-3 md:pr-4 border-r border-navy-800 z-10 bg-navy-950">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                        <span class="font-bold text-[11px] uppercase tracking-wider text-white hidden sm:inline">LIVE FEED</span>
                        <span class="text-[10px] font-mono text-cyan-400 font-bold bg-navy-900 border border-navy-700 px-1.5 py-0.5 rounded">MAYORA OPS</span>
                    </div>

                    <!-- Marquee Ticker Track (Infinite Smooth Loop) -->
                    <div class="marquee-container flex-1 overflow-hidden relative cursor-default select-none py-1">
                        <div class="marquee-track animate-marquee items-center gap-8 text-[11px]">
                            <!-- Content Group 1 -->
                            <div class="inline-flex items-center gap-6">
                                <!-- A. Real-Time World Clocks -->
                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    🕒 {{ __('messages.ticker.world_clocks') }}:
                                </span>
                                <span class="font-mono-numbers text-slate-200">🇮🇩 JKT <strong id="clock-jkt" class="text-white">--:--:-- WIB</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇬🇧 LON <strong id="clock-lon" class="text-white">--:--:-- GMT</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇺🇸 NYC <strong id="clock-nyc" class="text-white">--:--:-- EST</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇦🇪 DXB <strong id="clock-dxb" class="text-white">--:--:-- GST</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇨🇳 SHA <strong id="clock-sha" class="text-white">--:--:-- CST</strong></span>

                                <span class="text-slate-600">|</span>

                                <!-- B. Real-Time Exchange Rates -->
                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    💱 {{ __('messages.ticker.fx_rates') }}:
                                </span>
                                @if(!empty($marketTelemetry['fx']['pairs']))
                                    @foreach($marketTelemetry['fx']['pairs'] as $pair)
                                        <span class="inline-flex items-center gap-1 font-mono-numbers">
                                            <span class="text-slate-400">{{ $pair['pair'] }}</span>
                                            <strong class="text-white">{{ $pair['formatted'] }}</strong>
                                            <span class="{{ $pair['direction'] === 'up' ? 'text-emerald-400' : 'text-rose-400' }} text-[10px] font-bold">
                                                {{ $pair['direction'] === 'up' ? '▲' : '▼' }} {{ $pair['change'] }}
                                            </span>
                                        </span>
                                    @endforeach
                                @else
                                    <span class="font-mono-numbers">USD/IDR <strong class="text-white">Rp 15.845</strong> <span class="text-emerald-400 text-[10px]">▲ +0.15%</span></span>
                                    <span class="font-mono-numbers">EUR/IDR <strong class="text-white">Rp 17.230</strong> <span class="text-rose-400 text-[10px]">▼ -0.08%</span></span>
                                    <span class="font-mono-numbers">CNY/IDR <strong class="text-white">Rp 2.185</strong> <span class="text-emerald-400 text-[10px]">▲ +0.05%</span></span>
                                    <span class="font-mono-numbers">SAR/IDR <strong class="text-white">Rp 4.225</strong></span>
                                @endif

                                <span class="text-slate-600">|</span>

                                <!-- C. Central Bank Policy Rates -->
                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    📈 {{ __('messages.ticker.policy_rates') }}:
                                </span>
                                <span class="font-mono-numbers text-slate-200">🇮🇩 BI-Rate <strong class="text-cyan-300">6.00%</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇺🇸 Fed Funds <strong class="text-cyan-300">4.75%</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇪🇺 ECB <strong class="text-cyan-300">3.40%</strong></span>
                                <span class="font-mono-numbers text-slate-200">🌐 SOFR <strong class="text-cyan-300">4.58%</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇨🇳 PBOC 1Y <strong class="text-cyan-300">3.10%</strong></span>

                                <span class="text-slate-600">|</span>

                                <!-- D. Major Ports Live Weather -->
                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    🌡️ {{ __('messages.ticker.port_weather') }}:
                                </span>
                                @if(!empty($marketTelemetry['port_weathers']))
                                    @foreach($marketTelemetry['port_weathers'] as $pw)
                                        <span class="inline-flex items-center gap-1 font-mono-numbers text-slate-200">
                                            <span>{{ $pw['flag'] }} {{ $pw['port'] }}</span>
                                            <strong class="text-amber-300">{{ $pw['temp'] }}°C</strong>
                                            <span>{{ $pw['icon'] }}</span>
                                        </span>
                                    @endforeach
                                @else
                                    <span class="font-mono-numbers text-slate-200">🇮🇩 Tanjung Priok <strong class="text-amber-300">29°C</strong> ⛅</span>
                                    <span class="font-mono-numbers text-slate-200">🇳🇱 Rotterdam <strong class="text-amber-300">12°C</strong> ☁️</span>
                                    <span class="font-mono-numbers text-slate-200">🇺🇸 Los Angeles <strong class="text-amber-300">19°C</strong> ☀️</span>
                                    <span class="font-mono-numbers text-slate-200">🇦🇪 Dubai <strong class="text-amber-300">31°C</strong> ☀️</span>
                                @endif

                                <span class="text-slate-600">|</span>

                                <!-- E. Real-Time Shipping & Trade News -->
                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    📰 {{ __('messages.ticker.news') }}:
                                </span>
                                @if(!empty($marketTelemetry['news']))
                                    @foreach(array_slice($marketTelemetry['news'], 0, 4) as $item)
                                        <span class="inline-flex items-center gap-1.5 text-slate-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
                                            <span class="hover:text-cyan-300 transition cursor-pointer" onclick="openMarketModal('tab-news')">{{ $item['title'] ?? 'Shipping Update' }}</span>
                                        </span>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Duplicate Copy for Seamless Infinite Scrolling -->
                            <div class="inline-flex items-center gap-6" aria-hidden="true">
                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    🕒 {{ __('messages.ticker.world_clocks') }}:
                                </span>
                                <span class="font-mono-numbers text-slate-200">🇮🇩 JKT <strong class="text-white clock-jkt-dup">--:--:-- WIB</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇬🇧 LON <strong class="text-white clock-lon-dup">--:--:-- GMT</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇺🇸 NYC <strong class="text-white clock-nyc-dup">--:--:-- EST</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇦🇪 DXB <strong class="text-white clock-dxb-dup">--:--:-- GST</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇨🇳 SHA <strong class="text-white clock-sha-dup">--:--:-- CST</strong></span>

                                <span class="text-slate-600">|</span>

                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    💱 {{ __('messages.ticker.fx_rates') }}:
                                </span>
                                @if(!empty($marketTelemetry['fx']['pairs']))
                                    @foreach($marketTelemetry['fx']['pairs'] as $pair)
                                        <span class="inline-flex items-center gap-1 font-mono-numbers">
                                            <span class="text-slate-400">{{ $pair['pair'] }}</span>
                                            <strong class="text-white">{{ $pair['formatted'] }}</strong>
                                            <span class="{{ $pair['direction'] === 'up' ? 'text-emerald-400' : 'text-rose-400' }} text-[10px] font-bold">
                                                {{ $pair['direction'] === 'up' ? '▲' : '▼' }} {{ $pair['change'] }}
                                            </span>
                                        </span>
                                    @endforeach
                                @endif

                                <span class="text-slate-600">|</span>

                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    📈 {{ __('messages.ticker.policy_rates') }}:
                                </span>
                                <span class="font-mono-numbers text-slate-200">🇮🇩 BI-Rate <strong class="text-cyan-300">6.00%</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇺🇸 Fed Funds <strong class="text-cyan-300">4.75%</strong></span>
                                <span class="font-mono-numbers text-slate-200">🇪🇺 ECB <strong class="text-cyan-300">3.40%</strong></span>
                                <span class="font-mono-numbers text-slate-200">🌐 SOFR <strong class="text-cyan-300">4.58%</strong></span>

                                <span class="text-slate-600">|</span>

                                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px] flex items-center gap-1">
                                    🌡️ {{ __('messages.ticker.port_weather') }}:
                                </span>
                                @if(!empty($marketTelemetry['port_weathers']))
                                    @foreach($marketTelemetry['port_weathers'] as $pw)
                                        <span class="inline-flex items-center gap-1 font-mono-numbers text-slate-200">
                                            <span>{{ $pw['flag'] }} {{ $pw['port'] }}</span>
                                            <strong class="text-amber-300">{{ $pw['temp'] }}°C</strong>
                                            <span>{{ $pw['icon'] }}</span>
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Action Button: Open Market & FX Modal -->
                    <div class="shrink-0 pl-3 md:pl-4 border-l border-navy-800 z-10 bg-navy-950">
                        <button type="button" onclick="openMarketModal('tab-fx')"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-900/60 hover:bg-cyan-800 text-cyan-200 border border-cyan-700/60 px-2.5 py-1 text-[11px] font-semibold transition shadow-sm hover:scale-102">
                            <span>📊</span>
                            <span class="hidden md:inline">{{ __('messages.ticker.detail_modal') }}</span>
                            <span class="md:hidden">Pasar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. STICKY TOP HEADER WITH SEARCH, OFFICIAL WORLD LANGUAGES SELECTOR & USER MENU -->
            <header class="sticky top-10 z-10 flex min-h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-4 md:px-8 backdrop-blur">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.16em] text-ocean-600">CargoTrace · {{ __('messages.app_subtitle') }}</p>
                    <h1 class="text-xl font-black text-navy-900 tracking-tight">@yield('page-title', __('messages.nav.dashboard'))</h1>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Search Input -->
                    <form action="{{ route('shipments.index') }}" method="GET" class="hidden sm:block">
                        <input name="search" value="{{ request('search') }}" placeholder="{{ __('messages.nav.search_placeholder') }}"
                               class="w-48 lg:w-60 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-500/10 transition">
                    </form>

                    <!-- BAHASA RESMI DI DUNIA (OFFICIAL WORLD LANGUAGES SELECTOR) -->
                    <div class="relative" id="langDropdownWrapper">
                        @php
                            $locales = [
                                'id' => ['name' => 'Bahasa Indonesia', 'native' => 'Indonesia', 'flag' => '🇮🇩', 'code' => 'ID'],
                                'en' => ['name' => 'English', 'native' => 'English (UN)', 'flag' => '🇬🇧', 'code' => 'EN'],
                                'zh' => ['name' => 'Chinese (Mandarin)', 'native' => '中文 (UN)', 'flag' => '🇨🇳', 'code' => 'ZH'],
                                'es' => ['name' => 'Spanish', 'native' => 'Español (UN)', 'flag' => '🇪🇸', 'code' => 'ES'],
                                'ar' => ['name' => 'Arabic', 'native' => 'العربية (UN)', 'flag' => '🇸🇦', 'code' => 'AR'],
                                'fr' => ['name' => 'French', 'native' => 'Français (UN)', 'flag' => '🇫🇷', 'code' => 'FR'],
                                'ru' => ['name' => 'Russian', 'native' => 'Русский (UN)', 'flag' => '🇷🇺', 'code' => 'RU'],
                            ];
                            $currentLocale = app()->getLocale();
                            $activeLang = $locales[$currentLocale] ?? $locales['id'];
                        @endphp
                        <button type="button" id="btnLangDropdown"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition">
                            <span class="text-base">{{ $activeLang['flag'] }}</span>
                            <span class="hidden md:inline">{{ $activeLang['native'] }}</span>
                            <span class="md:hidden font-mono">{{ $activeLang['code'] }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="langDropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl border border-slate-200 bg-white py-2 shadow-2xl z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                            <div class="px-3.5 py-2 border-b border-slate-100">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bahasa Resmi di Dunia</p>
                                <p class="text-xs font-semibold text-slate-800">UN Official Global Languages</p>
                            </div>
                            <div class="py-1">
                                @foreach($locales as $code => $lang)
                                    <a href="{{ route('locale.switch', $code) }}"
                                       class="flex items-center justify-between px-3.5 py-2 text-xs font-medium transition hover:bg-cyan-50 hover:text-ocean-700 {{ $currentLocale === $code ? 'bg-cyan-50/80 font-bold text-ocean-700' : 'text-slate-700' }}">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-lg">{{ $lang['flag'] }}</span>
                                            <div>
                                                <div class="leading-tight">{{ $lang['native'] }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $lang['name'] }}</div>
                                            </div>
                                        </div>
                                        @if($currentLocale === $code)
                                            <span class="text-ocean-600 font-bold">✓</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- User Avatar -->
                    <div class="grid h-9 w-9 place-items-center rounded-full bg-cyan-100 text-sm font-bold text-ocean-700 shadow-sm ring-1 ring-cyan-200">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden text-xs font-bold text-slate-700 md:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                        @csrf
                        <button class="text-xs font-semibold text-ocean-700 hover:underline">Log out</button>
                    </form>
                </div>
            </header>

            @if(session('status'))
                <div class="mx-4 mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 md:mx-8 shadow-sm">{{ session('status') }}</div>
            @endif

            <!-- Page Content -->
            <div class="flex-1">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- 3. COMPREHENSIVE MODAL: PUSAT DATA PASAR, KURS, SUKU BUNGA & BERITA REAL-TIME -->
    <div id="marketModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
        <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-navy-950 text-white shadow-md">
                        <span class="text-xl">📈</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-navy-950">Pusat Data Pasar & Valuta Asing Global</h3>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800">LIVE SYNC</span>
                        </div>
                        <p class="text-xs text-slate-500">Kurs mata uang ekspor, suku bunga acuan bank sentral, dan cuaca maritim.</p>
                    </div>
                </div>
                <button type="button" onclick="closeMarketModal()" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Tab Buttons -->
            <div class="flex overflow-x-auto border-b border-slate-200 bg-white px-6">
                <button type="button" onclick="switchModalTab('tab-fx')" id="btn-tab-fx"
                        class="tab-btn active px-4 py-3 text-xs font-bold border-b-2 border-ocean-600 text-ocean-700">
                    💱 Kurs Valuta Asing (FX)
                </button>
                <button type="button" onclick="switchModalTab('tab-interest')" id="btn-tab-interest"
                        class="tab-btn px-4 py-3 text-xs font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-800">
                    📈 Suku Bunga Bank Sentral
                </button>
                <button type="button" onclick="switchModalTab('tab-weather')" id="btn-tab-weather"
                        class="tab-btn px-4 py-3 text-xs font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-800">
                    🌡️ Suhu & Cuaca Pelabuhan
                </button>
                <button type="button" onclick="switchModalTab('tab-news')" id="btn-tab-news"
                        class="tab-btn px-4 py-3 text-xs font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-800">
                    📰 Berita Maritim & Logistik
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- TAB 1: KURS VALUTA ASING & KALKULATOR EKSPOR -->
                <div id="pane-tab-fx" class="tab-pane space-y-6">
                    <!-- Interactive Converter -->
                    <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50/70 to-cyan-50/50 p-5">
                        <h4 class="font-bold text-slate-900 text-sm">Kalkulator Valuta Asing Ekspor Mayora</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Konversi nominal ekspor antar mata uang pasar utama secara langsung.</p>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                            <div class="sm:col-span-5">
                                <label class="block text-[11px] font-bold uppercase text-slate-500">Nominal Mata Uang</label>
                                <input type="number" id="calcAmount" value="10000" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold font-mono outline-none focus:border-teal-500">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold uppercase text-slate-500">Dari</label>
                                <select id="calcFrom" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold outline-none focus:border-teal-500">
                                    <option value="USD">USD ($ - Dollar AS)</option>
                                    <option value="IDR">IDR (Rp - Rupiah)</option>
                                    <option value="EUR">EUR (€ - Euro)</option>
                                    <option value="CNY">CNY (¥ - Yuan China)</option>
                                    <option value="SAR">SAR (﷼ - Riyal Saudi)</option>
                                    <option value="AED">AED (د.إ - Dirham UAE)</option>
                                    <option value="PHP">PHP (₱ - Peso Filipina)</option>
                                    <option value="GBP">GBP (£ - Pound Inggris)</option>
                                </select>
                            </div>
                            <div class="sm:col-span-1 text-center py-2 text-slate-400 font-bold">➔</div>
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-bold uppercase text-slate-500">Ke</label>
                                <select id="calcTo" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold outline-none focus:border-teal-500">
                                    <option value="IDR">IDR (Rp - Rupiah)</option>
                                    <option value="USD">USD ($ - Dollar AS)</option>
                                    <option value="EUR">EUR (€ - Euro)</option>
                                    <option value="CNY">CNY (¥ - Yuan China)</option>
                                    <option value="SAR">SAR (﷼ - Riyal Saudi)</option>
                                    <option value="AED">AED (د.إ - Dirham UAE)</option>
                                    <option value="PHP">PHP (₱ - Peso Filipina)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 rounded-xl bg-white p-3.5 border border-slate-200/80 flex items-center justify-between">
                            <span class="text-xs text-slate-500">Hasil Estimasi Nilai Tukar:</span>
                            <span id="calcResult" class="text-lg font-black text-teal-700 font-mono">Rp 158.450.000</span>
                        </div>
                    </div>

                    <!-- Full Exchange Rate Table -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full text-left text-xs">
                            <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Pasangan Mata Uang</th>
                                    <th class="px-4 py-3">Kurs Terakhir</th>
                                    <th class="px-4 py-3">Perubahan (24h)</th>
                                    <th class="px-4 py-3">Negara Tujuan Ekspor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @if(!empty($marketTelemetry['fx']['pairs']))
                                    @foreach($marketTelemetry['fx']['pairs'] as $p)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3 font-bold text-slate-900 flex items-center gap-2">
                                                <span>{{ $p['flag'] }}</span>
                                                <span>{{ $p['pair'] }}</span>
                                            </td>
                                            <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $p['formatted'] }}</td>
                                            <td class="px-4 py-3 font-bold {{ $p['direction'] === 'up' ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $p['direction'] === 'up' ? '▲' : '▼' }} {{ $p['change'] }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-500">Pasar Ekspor Mayora</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: SUKU BUNGA ACUAN BANK SENTRAL -->
                <div id="pane-tab-interest" class="tab-pane hidden space-y-4">
                    <p class="text-xs text-slate-500">Suku bunga acuan mempengaruhi biaya pembiayaan modal kerja ekspor, penerbitan Letter of Credit (LC), dan sewa armada kapal.</p>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full text-left text-xs">
                            <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Bank Sentral / Lembaga</th>
                                    <th class="px-4 py-3">Instrumen Acuan</th>
                                    <th class="px-4 py-3">Suku Bunga</th>
                                    <th class="px-4 py-3">Arah Kebijakan</th>
                                    <th class="px-4 py-3">Dampak terhadap Logistik & Ekspor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @if(!empty($marketTelemetry['interest_rates']))
                                    @foreach($marketTelemetry['interest_rates'] as $ir)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3 font-bold text-slate-900">
                                                <div class="flex items-center gap-2">
                                                    <span>{{ $ir['flag'] }}</span>
                                                    <span>{{ $ir['bank'] }}</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 font-normal">{{ $ir['country'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 font-medium text-slate-700">{{ $ir['instrument'] }}</td>
                                            <td class="px-4 py-3 font-mono font-black text-cyan-800 text-sm">{{ $ir['rate'] }}</td>
                                            <td class="px-4 py-3">
                                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">{{ $ir['trend'] }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600 max-w-xs">{{ $ir['impact'] }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: SUHU & CUACA PELABUHAN -->
                <div id="pane-tab-weather" class="tab-pane hidden space-y-4">
                    <p class="text-xs text-slate-500">Kondisi meteorologi di pelabuhan ekspor dan transshipment internasional Mayora (via Open-Meteo API).</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @if(!empty($marketTelemetry['port_weathers']))
                            @foreach($marketTelemetry['port_weathers'] as $w)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                                                <span>{{ $w['flag'] }}</span>
                                                <span>{{ $w['port'] }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-400">{{ $w['city'] }}, {{ $w['country'] }} ({{ $w['code'] }})</div>
                                        </div>
                                        <span class="text-2xl">{{ $w['icon'] }}</span>
                                    </div>
                                    <div class="mt-3 flex items-baseline justify-between border-t border-slate-200/60 pt-2">
                                        <span class="text-xl font-black text-slate-800">{{ $w['temp'] }}°C</span>
                                        <span class="text-[11px] font-semibold text-teal-700">{{ $w['condition'] }}</span>
                                    </div>
                                    <div class="mt-1 text-[10px] text-slate-400">Kecepatan Angin: <strong>{{ $w['wind_knots'] }} kts</strong></div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- TAB 4: BERITA MARITIM & LOGISTIK -->
                <div id="pane-tab-news" class="tab-pane hidden space-y-3">
                    @if(!empty($marketTelemetry['news']))
                        @foreach($marketTelemetry['news'] as $n)
                            <a href="{{ $n['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer"
                               class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-ocean-500 hover:bg-cyan-50/30 transition">
                                <h4 class="font-bold text-slate-900 text-sm hover:text-ocean-700">{{ $n['title'] }}</h4>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                                    <span>Sumber: <strong class="text-slate-600">{{ $n['source']['name'] ?? 'Logistics Wire' }}</strong></span>
                                    <span class="font-semibold text-ocean-600">Baca Selengkapnya →</span>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-slate-100 bg-slate-50 px-6 py-3 flex items-center justify-between text-xs text-slate-400">
                <span>Sumber Data: <strong>Open-Meteo, Frankfurter European Central Bank, & Mayora Supply Chain Wire</strong></span>
                <button type="button" onclick="closeMarketModal()" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- CLIENT REALTIME ENGINE (WORLD CLOCKS, DROPDOWN, TICKER SYNC, FX CALCULATOR) -->
    <script>
        // 1. Language Dropdown Toggle
        const btnLang = document.getElementById('btnLangDropdown');
        const menuLang = document.getElementById('langDropdownMenu');
        if (btnLang && menuLang) {
            btnLang.addEventListener('click', (e) => {
                e.stopPropagation();
                menuLang.classList.toggle('hidden');
            });
            document.addEventListener('click', (e) => {
                if (!document.getElementById('langDropdownWrapper')?.contains(e.target)) {
                    menuLang.classList.add('hidden');
                }
            });
        }

        // 2. Real-Time World Clocks Engine (Updates every 1000ms via pure local math)
        function updateWorldClocks() {
            const now = new Date();

            const timeFormat = (tz) => {
                return new Intl.DateTimeFormat('en-GB', {
                    timeZone: tz,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                }).format(now);
            };

            const tJkt = timeFormat('Asia/Jakarta') + ' WIB';
            const tLon = timeFormat('Europe/London') + ' GMT';
            const tNyc = timeFormat('America/New_York') + ' EST';
            const tDxb = timeFormat('Asia/Dubai') + ' GST';
            const tSha = timeFormat('Asia/Shanghai') + ' CST';

            const setEl = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };

            setEl('clock-jkt', tJkt);
            setEl('clock-lon', tLon);
            setEl('clock-nyc', tNyc);
            setEl('clock-dxb', tDxb);
            setEl('clock-sha', tSha);

            document.querySelectorAll('.clock-jkt-dup').forEach(el => el.textContent = tJkt);
            document.querySelectorAll('.clock-lon-dup').forEach(el => el.textContent = tLon);
            document.querySelectorAll('.clock-nyc-dup').forEach(el => el.textContent = tNyc);
            document.querySelectorAll('.clock-dxb-dup').forEach(el => el.textContent = tDxb);
            document.querySelectorAll('.clock-sha-dup').forEach(el => el.textContent = tSha);
        }

        setInterval(updateWorldClocks, 1000);
        updateWorldClocks();

        // 3. Modal Toggles and Tab Switching
        window.openMarketModal = function(activeTab = 'tab-fx') {
            document.getElementById('marketModal').classList.remove('hidden');
            switchModalTab(activeTab);
        };

        window.closeMarketModal = function() {
            document.getElementById('marketModal').classList.add('hidden');
        };

        window.switchModalTab = function(tabName) {
            document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('border-ocean-600', 'text-ocean-700', 'active');
                el.classList.add('border-transparent', 'text-slate-500');
            });

            const pane = document.getElementById('pane-' + tabName);
            const btn = document.getElementById('btn-' + tabName);
            if (pane) pane.classList.remove('hidden');
            if (btn) {
                btn.classList.add('border-ocean-600', 'text-ocean-700', 'active');
                btn.classList.remove('border-transparent', 'text-slate-500');
            }
        };

        // Close modal on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMarketModal();
        });

        // 4. Currency Calculator Engine
        const fxRates = {
            'USD': 1.0,
            'IDR': 15845.0,
            'EUR': 0.9215,
            'CNY': 7.242,
            'SAR': 3.751,
            'AED': 3.673,
            'PHP': 58.75,
            'GBP': 0.789
        };

        function calculateFx() {
            const amount = parseFloat(document.getElementById('calcAmount')?.value || 0);
            const from = document.getElementById('calcFrom')?.value || 'USD';
            const to = document.getElementById('calcTo')?.value || 'IDR';

            // Convert to base USD first, then to target currency
            const amountInUsd = amount / (fxRates[from] || 1.0);
            const targetAmount = amountInUsd * (fxRates[to] || 1.0);

            const resultEl = document.getElementById('calcResult');
            if (resultEl) {
                let symbol = '';
                if (to === 'IDR') symbol = 'Rp ';
                else if (to === 'USD') symbol = '$ ';
                else if (to === 'EUR') symbol = '€ ';
                else if (to === 'CNY') symbol = '¥ ';
                else if (to === 'SAR') symbol = '﷼ ';
                else if (to === 'AED') symbol = 'د.إ ';
                else if (to === 'PHP') symbol = '₱ ';
                else if (to === 'GBP') symbol = '£ ';

                const decimals = (to === 'IDR') ? 0 : 2;
                resultEl.textContent = symbol + new Intl.NumberFormat('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(targetAmount);
            }
        }

        document.getElementById('calcAmount')?.addEventListener('input', calculateFx);
        document.getElementById('calcFrom')?.addEventListener('change', calculateFx);
        document.getElementById('calcTo')?.addEventListener('change', calculateFx);
        calculateFx();

        // 5. Periodic Background Telemetry Poller (Every 60s)
        async function refreshTelemetryData() {
            try {
                const res = await fetch("{{ route('market.telemetry') }}");
                if (res.ok) {
                    const data = await res.json();
                    if (data.fx && data.fx.rates) {
                        Object.assign(fxRates, data.fx.rates);
                        calculateFx();
                    }
                }
            } catch (err) {
                console.log('Background telemetry refresh skipped:', err);
            }
        }
        setInterval(refreshTelemetryData, 60000);
    </script>
    @stack('scripts')
</body>
</html>
