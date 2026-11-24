@extends('layouts.app')

@section('title', 'Monitoring Kurs Real-Time & Deteksi Perubahan Mata Uang')
@section('page-title', 'Monitoring Kurs & Risiko Valas Ekspor')

@push('head')
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .font-mono-num { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        @keyframes pulseAlert {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.88; transform: scale(1.004); }
        }
        .alert-pulse-box {
            animation: pulseAlert 3s ease-in-out infinite;
        }
    </style>
@endpush

@section('content')
<div class="space-y-6 p-4 sm:p-6 md:p-8">
    <!-- 1. Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-navy-950 via-navy-900 to-ocean-600 p-6 md:p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span id="headerLiveStatusBadge" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-300 ring-1 ring-inset ring-emerald-500/30">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span id="headerLiveStatusText">Live FX API Stream 100% Real-Time</span>
                    </span>
                    <span class="rounded-full bg-cyan-400/20 px-3 py-1 text-xs font-bold text-cyan-200">
                        Agnostik Multi-Mata Uang Global
                    </span>
                    <span id="headerProviderName" class="text-xs text-slate-300">
                        {{ $ratesData['provider'] ?? 'Open Exchange Rates (open.er-api.com)' }}
                    </span>
                </div>
                <h2 class="mt-3 text-2xl font-black md:text-3xl tracking-tight">
                    Monitoring Kurs & Deteksi Volatilitas Valas
                </h2>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Pantau pergerakan nilai tukar mata uang pasar ekspor PT Mayora Indah Tbk secara <strong class="text-white">100% real-time hidup dari API eksternal</strong> untuk seluruh mata uang (Asia, Eropa, Timur Tengah, Amerika). Dilengkapi algoritma deteksi lonjakan (*change detection*) antar-fetch dan kalkulator muatan kargo 1-to-1 serta 1-to-Many.
                </p>
            </div>

            <!-- Global Quick Stats in Banner -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-white/10 p-3.5 backdrop-blur-md border border-white/10 text-center">
                    <span class="block text-[10px] uppercase font-bold tracking-wider text-cyan-200">Mata Uang Aktif</span>
                    <strong class="mt-0.5 block text-xl font-black text-white font-mono-num">{{ count($supportedCurrencies) }}</strong>
                    <span class="text-[10px] text-slate-300">Pasar Ekspor</span>
                </div>
                <div class="rounded-2xl bg-white/10 p-3.5 backdrop-blur-md border border-white/10 text-center">
                    <span class="block text-[10px] uppercase font-bold tracking-wider text-cyan-200">Ambang Batas</span>
                    <strong id="badgeThreshold" class="mt-0.5 block text-xl font-black text-amber-300 font-mono-num">≥ 0.25%</strong>
                    <span class="text-[10px] text-slate-300">Trigger Alert</span>
                </div>
                <div class="col-span-2 sm:col-span-1 rounded-2xl bg-white/10 p-3.5 backdrop-blur-md border border-white/10 text-center">
                    <span class="block text-[10px] uppercase font-bold tracking-wider text-cyan-200">Status Data API</span>
                    <strong id="syncStatusText" class="mt-0.5 block text-xl font-black text-emerald-300">Real-Time</strong>
                    <span id="lastUpdatedHeader" class="text-[10px] text-slate-300 font-mono-num">
                        {{ $ratesData['time_last_update_utc'] ?? $ratesData['synced_at'] ?? now()->format('H:i:s') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Simulation Mode Notice Banner (Distinct from Real-Time API Data) -->
    <div id="simulationActiveNotice" class="hidden rounded-2xl border-2 border-amber-400 bg-amber-50 p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-500 text-white text-lg">🧪</span>
                <div>
                    <h4 class="text-sm font-extrabold text-amber-950">Mode Uji Coba Simulasi Volatilitas Aktif</h4>
                    <p class="text-xs text-amber-800">
                        Perubahan angka di bawah ini merupakan <strong>Simulasi Buatan (Artificial Test)</strong> untuk menguji respons notifikasi banner, bukan angka resmi dari feed API pasar real-time.
                    </p>
                </div>
            </div>
            <button type="button" onclick="resetSimulation()"
                    class="rounded-xl bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 text-xs font-bold shadow-xs transition">
                🔄 Kembalikan ke Kurs Asli API Real-Time
            </button>
        </div>
    </div>

    <!-- 2. Dynamic Multi-Currency Change Detection Alert Banner -->
    <div id="detectionAlertBanner" class="hidden transition-all duration-300">
        <div class="alert-pulse-box rounded-2xl border-2 border-amber-400 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-100/80 p-5 shadow-lg">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-amber-500 text-white shadow-md text-xl">
                        ⚠️
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-extrabold text-amber-950">
                                Peringatan Deteksi Perubahan Nilai Tukar (FX Volatility Alert)
                            </h3>
                            <span id="alertCountBadge" class="rounded-full bg-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-900 font-mono-num">
                                0 Mata Uang Terdeteksi
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-amber-900 leading-relaxed max-w-3xl">
                            Sistem mendeteksi fluktuasi mata uang yang melampaui ambang batas (<strong id="alertThresholdText">0.25%</strong>) terhadap mata uang dasar (<strong id="alertBaseText">{{ $baseCurrency }}</strong>) antar pembaruan data real-time. Tinjau estimasi nilai invoice dan strategi lindung nilai (*hedging*).
                        </p>

                        <!-- Chips of Triggered Currencies -->
                        <div id="triggeredCurrenciesList" class="mt-3 flex flex-wrap gap-2">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="dismissAlert()" class="rounded-xl bg-white/90 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm border border-amber-200 hover:bg-white transition">
                        Tutup Alert
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Control Panel: Base Currency, Threshold, Live API Status & Auto-Refresh -->
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Left: Base Currency & Threshold -->
            <div class="flex flex-wrap items-center gap-4">
                <!-- Base Currency Selector -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Mata Uang Dasar (Base Currency)</label>
                    <div class="mt-1 relative">
                        <select id="selectBaseCurrency" onchange="changeBaseCurrency(this.value)"
                                class="rounded-xl border border-slate-200 bg-slate-50 py-2 pl-3 pr-8 text-xs font-bold text-slate-800 shadow-sm outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-500/10 cursor-pointer">
                            @foreach($supportedCurrencies as $code => $meta)
                                <option value="{{ $code }}" {{ $baseCurrency === $code ? 'selected' : '' }}>
                                    {{ $meta['flag'] }} {{ $code }} - {{ $meta['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Threshold Selector -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Ambang Batas Deteksi (Threshold)</label>
                    <div class="mt-1 relative">
                        <select id="selectThreshold" onchange="changeThreshold(this.value)"
                                class="rounded-xl border border-slate-200 bg-slate-50 py-2 pl-3 pr-8 text-xs font-bold text-slate-800 shadow-sm outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-500/10 cursor-pointer">
                            <option value="0.10">0.10% (Sangat Sensitif)</option>
                            <option value="0.25" selected>0.25% (Rekomendasi Standar)</option>
                            <option value="0.50">0.50% (Moderat)</option>
                            <option value="1.00">1.00% (Volatilitas Tinggi)</option>
                        </select>
                    </div>
                </div>

                <!-- Live Stream Indicator Badge -->
                <div class="hidden md:block">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Sumber Data</label>
                    <div class="mt-1 inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs font-bold text-emerald-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="controlLiveApiBadge">API Real-Time Hidup</span>
                    </div>
                </div>
            </div>

            <!-- Right: Countdown, Refresh Now, Simulation Trigger Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Auto-Refresh Countdown Display -->
                <div class="hidden sm:flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs text-slate-600">
                    <span class="h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                    <span>Auto-refresh: <strong id="refreshCountdownText" class="font-mono-num text-slate-900">60s</strong></span>
                </div>

                <!-- Refresh Button -->
                <button type="button" id="btnManualRefresh" onclick="refreshRates(true)"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-ocean-600 hover:bg-ocean-700 text-white px-3.5 py-2 text-xs font-bold shadow-sm transition active:scale-95">
                    <svg id="refreshSpinner" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span id="refreshBtnLabel">Refresh Sekarang</span>
                </button>

                <!-- Simulation / Test Mode Dropdown/Buttons -->
                <div class="flex items-center gap-1 bg-amber-50 p-1 rounded-xl border border-amber-200">
                    <span class="text-[10px] font-bold text-amber-800 uppercase px-2 hidden md:inline">🧪 Uji Coba:</span>
                    <button type="button" onclick="simulateVolatility('surge')" title="Uji coba simulasi lonjakan kurs (+1.5%)"
                            class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 text-[11px] font-bold transition shadow-sm">
                        +1.5% Surge
                    </button>
                    <button type="button" onclick="simulateVolatility('drop')" title="Uji coba simulasi penurunan kurs (-1.2%)"
                            class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1 text-[11px] font-bold transition shadow-sm">
                        -1.2% Drop
                    </button>
                    <button type="button" onclick="resetSimulation()" title="Kembalikan ke data kurs asli API"
                            class="rounded-lg bg-white hover:bg-slate-100 text-slate-700 px-2 py-1 text-[11px] font-bold border border-slate-200 transition">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Two-Column Main Workspace: Chart.js Trend & Instant Cargo Currency Calculator -->
    <div class="grid gap-6 lg:grid-cols-12">
        <!-- Left: Chart.js Trend Visualizer (7 Cols) -->
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-7">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Grafik Tren Fluktuasi Nilai Tukar</h3>
                    <p class="text-xs text-slate-500">Histori dan tren pergerakan nilai tukar pasar ekspor Mayora.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Target Pair Selector -->
                    <select id="chartTargetCurrency" onchange="updateChartData()"
                            class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-700 outline-none focus:border-ocean-500">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="CNY">CNY (¥)</option>
                        <option value="SAR">SAR (﷼)</option>
                        <option value="SGD">SGD (S$)</option>
                        <option value="PHP">PHP (₱)</option>
                        <option value="JPY">JPY (¥)</option>
                        <option value="MYR">MYR (RM)</option>
                        <option value="IDR">IDR (Rp)</option>
                    </select>

                    <!-- Days Range Buttons -->
                    <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-[11px] font-bold">
                        <button type="button" onclick="setChartDays(7, this)" class="chart-day-btn active rounded-md bg-white px-2 py-0.5 text-ocean-700 shadow-sm">7H</button>
                        <button type="button" onclick="setChartDays(14, this)" class="chart-day-btn rounded-md px-2 py-0.5 text-slate-600 hover:text-slate-900">14H</button>
                        <button type="button" onclick="setChartDays(30, this)" class="chart-day-btn rounded-md px-2 py-0.5 text-slate-600 hover:text-slate-900">30H</button>
                    </div>
                </div>
            </div>

            <div class="mt-4 relative h-64 sm:h-72 w-full">
                <canvas id="fxTrendChart"></canvas>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-2 border-t border-slate-100 pt-3 text-xs text-center">
                <div class="rounded-xl bg-slate-50 p-2">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Kurs Tertinggi</span>
                    <strong id="chartStatMax" class="font-mono-num text-slate-800 font-bold">—</strong>
                </div>
                <div class="rounded-xl bg-slate-50 p-2">
                    <span class="block text-[10px] text-slate-400 uppercase font-semibold">Kurs Terendah</span>
                    <strong id="chartStatMin" class="font-mono-num text-slate-800 font-bold">—</strong>
                </div>
                <div class="rounded-xl bg-teal-50 p-2 border border-teal-100">
                    <span class="block text-[10px] text-teal-700 uppercase font-semibold">Kurs Terkini Real-Time</span>
                    <strong id="chartStatCurrent" class="font-mono-num text-teal-900 font-bold">—</strong>
                </div>
            </div>
        </section>

        <!-- Right: Cek Nilai Tukar Multi-Moda (1-to-1 & 1-to-Many) -->
        <section id="checkerSectionContainer" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-5 flex flex-col justify-between transition-all duration-300">
            <div>
                <!-- Header with Mode Selector Tabs -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-slate-900">Cek Nilai Tukar & Konversi</h3>
                            <span id="activeModeBadge" class="rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-bold text-teal-800">1-TO-1</span>
                        </div>
                        <p class="text-xs text-slate-500">Cek kurs satu-per-satu spesifik atau sekaligus banyak negara secara dinamis.</p>
                    </div>

                    <!-- Mode Selector Tab Pills -->
                    <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs">
                        <button type="button" onclick="switchCheckerMode('1-to-1')" id="tabMode1to1"
                                class="checker-tab-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition bg-white text-navy-900 shadow-xs">
                            <span>🎯 1-to-1</span>
                        </button>
                        <button type="button" onclick="switchCheckerMode('1-to-many')" id="tabMode1toMany"
                                class="checker-tab-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                            <span>🌐 1-to-Many</span>
                        </button>
                    </div>
                </div>

                <!-- Shared Input: Nominal Muatan / Invoice -->
                <div class="mt-4 space-y-3.5">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700">Nominal Muatan / Nilai Valas</label>
                            <span class="text-[10px] text-slate-400">Default: 1 unit mata uang</span>
                        </div>
                        <div class="mt-1 relative rounded-xl shadow-sm">
                            <input type="number" id="calcInputAmount" value="1" step="any" min="0"
                                   placeholder="Masukkan nominal..."
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono-num text-lg font-black text-slate-900 outline-none focus:border-ocean-500 focus:bg-white focus:ring-2 focus:ring-ocean-500/10 transition">
                        </div>

                        <!-- Quick Nominal Chips -->
                        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mr-1">Cepat:</span>
                            <button type="button" onclick="setCheckerAmount(1)" class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2 py-0.5 font-mono font-bold text-slate-700 transition">1</button>
                            <button type="button" onclick="setCheckerAmount(100)" class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2 py-0.5 font-mono font-bold text-slate-700 transition">100</button>
                            <button type="button" onclick="setCheckerAmount(1000)" class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2 py-0.5 font-mono font-bold text-slate-700 transition">1.000</button>
                            <button type="button" onclick="setCheckerAmount(10000)" class="rounded-lg bg-slate-100 hover:bg-slate-200 px-2 py-0.5 font-mono font-bold text-slate-700 transition">10.000</button>
                            <button type="button" onclick="setPresetCargo(32000, 'USD', 'IDR')" class="rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-2 py-0.5 font-bold transition">🍬 $32K Kopiko</button>
                            <button type="button" onclick="setPresetCargo(38500, 'USD', 'EUR')" class="rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-2 py-0.5 font-bold transition">🍪 $38.5K Danisa</button>
                        </div>
                    </div>

                    <!-- Shared: Mata Uang Asal (Source Currency) -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Mata Uang Asal (Source / Acuan)</label>
                        <select id="calcFromCurrency" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-800 outline-none focus:border-ocean-500 cursor-pointer">
                            @foreach($supportedCurrencies as $code => $meta)
                                <option value="{{ $code }}" {{ $code === 'USD' ? 'selected' : '' }}>
                                    {{ $meta['flag'] }} {{ $code }} - {{ $meta['name'] }} ({{ $meta['symbol'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- ========================================== -->
                    <!-- VIEW 1: MODE SATU-PER-SATU (1-TO-1) -->
                    <!-- ========================================== -->
                    <div id="viewMode1to1" class="space-y-3.5 transition-all">
                        <!-- Target Currency & Swap Button -->
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Ke Mata Uang Tujuan (Target)</label>
                                <select id="calcToCurrency" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-800 outline-none focus:border-ocean-500 cursor-pointer">
                                    @foreach($supportedCurrencies as $code => $meta)
                                        <option value="{{ $code }}" {{ $code === 'IDR' ? 'selected' : '' }}>
                                            {{ $meta['flag'] }} {{ $code }} - {{ $meta['name'] }} ({{ $meta['symbol'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Swap Button -->
                            <div class="pt-4">
                                <button type="button" onclick="swapCheckerCurrencies()" title="Tukar posisi mata uang asal & tujuan (Swap)"
                                        class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-slate-50 hover:bg-ocean-50 hover:border-ocean-300 hover:text-ocean-700 text-slate-700 shadow-xs transition active:scale-90">
                                    <svg id="swapIconSvg" class="h-4 w-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- 1-to-1 Result Highlight Box (100% Dynamic from Live API) -->
                        <div class="rounded-2xl bg-gradient-to-br from-teal-50 via-emerald-50 to-teal-100/60 border border-teal-200/90 p-4 shadow-xs">
                            <div class="flex items-center justify-between text-xs text-teal-800 font-semibold border-b border-teal-200/50 pb-2">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Hasil Konversi 1-to-1 (Live API):</span>
                                </span>
                                <span id="calcRateRatio" class="font-mono-num text-[11px] font-bold text-teal-700">
                                    <span class="inline-block animate-pulse text-slate-400">Mengambil kurs live...</span>
                                </span>
                            </div>
                            
                            <div id="calcConvertedResult" class="mt-2 text-2xl font-black text-teal-950 font-mono-num tracking-tight">
                                <span class="inline-block animate-pulse text-slate-400 text-lg">Menghitung...</span>
                            </div>

                            <!-- Inverse Rate Display (Nilai Balik) -->
                            <div class="mt-2 flex items-center justify-between text-[11px] text-teal-700/80 pt-1.5 border-t border-teal-200/40">
                                <span>Nilai Balik (Inverse Rate):</span>
                                <span id="calcInverseRatio" class="font-mono-num font-semibold">
                                    <span class="inline-block animate-pulse text-slate-400">Menghitung...</span>
                                </span>
                            </div>
                        </div>

                        <!-- Preset Cargo Mayora Matrix -->
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Preset Muatan Logistik Mayora:</span>
                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                <button type="button" onclick="setPresetCargo(32000, 'USD', 'IDR')"
                                        class="rounded-xl border border-slate-200 bg-slate-50 hover:bg-cyan-50 hover:border-cyan-200 p-2 text-left transition">
                                    <span class="font-bold text-slate-800 block">🍬 1 FCL Kopiko 100g</span>
                                    <span class="text-[10px] text-slate-500">1.600 Karton · $32.000</span>
                                </button>
                                <button type="button" onclick="setPresetCargo(38500, 'USD', 'EUR')"
                                        class="rounded-xl border border-slate-200 bg-slate-50 hover:bg-cyan-50 hover:border-cyan-200 p-2 text-left transition">
                                    <span class="font-bold text-slate-800 block">🍪 1 FCL Danisa 454g</span>
                                    <span class="text-[10px] text-slate-500">1.400 Karton · $38.500</span>
                                </button>
                                <button type="button" onclick="setPresetCargo(9500, 'USD', 'IDR')"
                                        class="rounded-xl border border-slate-200 bg-slate-50 hover:bg-cyan-50 hover:border-cyan-200 p-2 text-left transition">
                                    <span class="font-bold text-slate-800 block">✈️ Air Express Kopiko</span>
                                    <span class="text-[10px] text-slate-500">500 Kg Sample · $9.500</span>
                                </button>
                                <button type="button" onclick="setPresetCargo(185000000, 'IDR', 'USD')"
                                        class="rounded-xl border border-slate-200 bg-slate-50 hover:bg-cyan-50 hover:border-cyan-200 p-2 text-left transition">
                                    <span class="font-bold text-slate-800 block">🚛 1 Truk Tronton</span>
                                    <span class="text-[10px] text-slate-500">12 Ton Domestik · Rp 185Jt</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- VIEW 2: MODE SEKALIGUS BANYAK (1-TO-MANY) -->
                    <!-- ========================================== -->
                    <div id="viewMode1toMany" class="hidden space-y-3.5 transition-all">
                        <!-- Region Quick Presets & Target Filter -->
                        <div>
                            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                <span>Pilih Pasar Tujuan Ekspor:</span>
                                <span id="multiCountLabel" class="text-ocean-700 font-bold">13 Mata Uang Aktif</span>
                            </div>
                            
                            <!-- Filter Preset Chips -->
                            <div class="flex flex-wrap items-center gap-1 text-[11px]">
                                <button type="button" onclick="filterMultiRegion('ALL')" id="btnFilterRegion-ALL"
                                        class="region-filter-btn rounded-lg bg-navy-900 text-cyan-300 px-2 py-0.5 font-bold transition shadow-2xs">
                                    ✅ Semua
                                </button>
                                <button type="button" onclick="filterMultiRegion('ASEAN')" id="btnFilterRegion-ASEAN"
                                        class="region-filter-btn rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 font-semibold transition">
                                    🌏 ASEAN
                                </button>
                                <button type="button" onclick="filterMultiRegion('EUROPE')" id="btnFilterRegion-EUROPE"
                                        class="region-filter-btn rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 font-semibold transition">
                                    🇪🇺 Eropa
                                </button>
                                <button type="button" onclick="filterMultiRegion('MIDEAST')" id="btnFilterRegion-MIDEAST"
                                        class="region-filter-btn rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 font-semibold transition">
                                    🕌 Timteng
                                </button>
                                <button type="button" onclick="filterMultiRegion('EASTASIA')" id="btnFilterRegion-EASTASIA"
                                        class="region-filter-btn rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 font-semibold transition">
                                    🇨🇳 Asia Timur
                                </button>
                            </div>

                            <!-- Target Currency Checkbox / Chips Bar -->
                            <div class="mt-2 flex flex-wrap gap-1 max-h-24 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/80 p-1.5" id="multiTargetChipsContainer">
                                <!-- Populated dynamically with toggleable currency badges -->
                            </div>
                        </div>

                        <!-- 1-to-Many Output Grid Cards Container -->
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-2.5">
                            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold px-1 pb-2 border-b border-slate-200/60">
                                <span>Hasil Konversi Real-Time ke Pasar Global:</span>
                                <span class="font-mono-num text-[11px] text-slate-400" id="multiBaseReferenceLabel">Acuan: 1 USD</span>
                            </div>

                            <!-- Multi Currency Grid -->
                            <div id="multiCurrencyGrid" class="mt-2.5 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-80 overflow-y-auto pr-1">
                                <!-- Populated dynamically with converted cards from real API data -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- 5. Watchlist & Full Multi-Currency Exchange Rates Table -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-4">
            <div>
                <h3 class="font-bold text-slate-900">Watchlist & Tabel Kurs Multi-Mata Uang Terintegrasi</h3>
                <p class="text-xs text-slate-500">
                    Nilai tukar seluruh mata uang negara tujuan ekspor terhadap mata uang dasar (<strong id="tableBaseLabel">{{ $baseCurrency }}</strong>) bersumber 100% dari respons API live.
                </p>
            </div>

            <!-- Table Search Input -->
            <div class="flex items-center gap-2">
                <input type="text" id="tableSearchInput" onkeyup="filterCurrencyTable()" placeholder="Cari mata uang, negara..."
                       class="w-48 sm:w-60 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-500/10">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs" id="currencyTable">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Mata Uang & Negara</th>
                        <th class="px-6 py-3.5">Wilayah Pasar</th>
                        <th class="px-6 py-3.5 text-right">Kurs Real-Time API</th>
                        <th class="px-6 py-3.5 text-right">Snapshot Sesi Sebelumnya</th>
                        <th class="px-6 py-3.5 text-right">Selisih (Delta Riil)</th>
                        <th class="px-6 py-3.5 text-center">Perubahan (%)</th>
                        <th class="px-6 py-3.5 text-center">Status Ambang Batas</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="currencyTableBody">
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                            <span class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-ocean-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Mengambil data kurs real-time dari API...</span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-6 py-3 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
            <span id="tableDataSourceFooter">Sumber Data: <strong id="footerProviderLabel">{{ $ratesData['provider'] ?? 'Open Exchange Rates (open.er-api.com)' }}</strong></span>
            <span>Deteksi perubahan otomatis membandingkan dengan <strong>Snapshot LocalStorage</strong></span>
        </div>
    </section>

    <!-- 6. Central Bank Interest Rates Table -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
            <h3 class="font-bold text-slate-900">Suku Bunga Acuan Bank Sentral Pasar Ekspor Utama</h3>
            <p class="text-xs text-slate-500">Benchmark suku bunga acuan moneter untuk negosiasi kontrak ekspor, fasilitas Letter of Credit (LC), dan pembiayaan freight maritim.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3">Bank Sentral / Lembaga</th>
                        <th class="px-6 py-3">Instrumen Acuan</th>
                        <th class="px-6 py-3">Tingkat Bunga</th>
                        <th class="px-6 py-3">Arah Kebijakan</th>
                        <th class="px-6 py-3">Dampak Terhadap Biaya Ekspor Mayora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($policyRates as $ir)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ $ir['flag'] }}</span>
                                    <span>{{ $ir['bank'] }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-normal">{{ $ir['country'] }}</div>
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-700">{{ $ir['instrument'] }}</td>
                            <td class="px-6 py-3.5 font-mono-num font-black text-cyan-800 text-sm">{{ $ir['rate'] }}</td>
                            <td class="px-6 py-3.5">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700">{{ $ir['trend'] }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-600 max-w-sm leading-relaxed">{{ $ir['impact'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // Initial State Passed from Server
    let currentBase = "{{ $baseCurrency }}";
    let activeThreshold = 0.25; // 0.25% default
    let liveRatesData = @json($ratesData);
    const supportedCurrenciesMeta = @json($supportedCurrencies);

    let isSimulationActive = false;
    let simulatedDeltas = {}; // Key: Currency Code, Value: artificial percent shift

    let refreshSecondsLeft = 60;
    let countdownInterval = null;
    let chartInstance = null;
    let chartDays = 7;

    const SNAPSHOT_STORAGE_KEY_PREFIX = 'cargotrace_fx_snapshot_';

    // 1. Initial Page Load Execution
    document.addEventListener('DOMContentLoaded', async () => {
        // Initialize multi targets
        initMultiTargets();
        setupCalculatorListeners();
        initChart();
        startAutoRefreshCountdown();

        // If server provided live rates, render immediately then fetch latest
        if (liveRatesData && liveRatesData.rates && Object.keys(liveRatesData.rates).length > 0) {
            processAndRenderRates(liveRatesData);
        } else {
            await fetchLiveRatesFlow(currentBase, false);
        }
    });

    // 2. Multi-Provider Direct Live FX Fetch Engine
    async function fetchLiveRatesFlow(base, isManual = false) {
        const spinner = document.getElementById('refreshSpinner');
        const btnLabel = document.getElementById('refreshBtnLabel');

        if (spinner) spinner.classList.add('animate-spin');
        if (btnLabel) btnLabel.textContent = 'Memperbarui...';

        try {
            // First attempt: Direct fetch to open.er-api.com (100% real-time, CORS enabled)
            let fetched = null;
            try {
                const apiRes = await fetch(`https://open.er-api.com/v6/latest/${base}`, { cache: 'no-store' });
                if (apiRes.ok) {
                    const json = await apiRes.json();
                    if (json.result === 'success' && json.rates && Object.keys(json.rates).length > 10) {
                        fetched = {
                            result: 'success',
                            is_live: true,
                            provider: 'Open Exchange Rates (open.er-api.com)',
                            base_code: base,
                            rates: json.rates,
                            time_last_update_utc: json.time_last_update_utc || new Date().toUTCString(),
                            synced_at: new Date().toLocaleTimeString('id-ID'),
                            last_fetched_at: new Date().toISOString()
                        };
                    }
                }
            } catch (directErr) {
                console.warn('Direct open.er-api.com failed, attempting fallback...', directErr);
            }

            // Second attempt: api.exchangerate-api.com
            if (!fetched) {
                try {
                    const fallbackRes = await fetch(`https://api.exchangerate-api.com/v4/latest/${base}`, { cache: 'no-store' });
                    if (fallbackRes.ok) {
                        const json = await fallbackRes.json();
                        if (json.rates && Object.keys(json.rates).length > 10) {
                            fetched = {
                                result: 'success',
                                is_live: true,
                                provider: 'ExchangeRate-API (api.exchangerate-api.com)',
                                base_code: base,
                                rates: json.rates,
                                time_last_update_utc: json.date || new Date().toUTCString(),
                                synced_at: new Date().toLocaleTimeString('id-ID'),
                                last_fetched_at: new Date().toISOString()
                            };
                        }
                    }
                } catch (fallbackErr) {
                    console.warn('Fallback direct API failed, trying Laravel backend...', fallbackErr);
                }
            }

            // Third attempt: Laravel backend route /api/currency/rates
            if (!fetched) {
                const backendRes = await fetch(`{{ route('currency.rates') }}?base=${base}`);
                if (backendRes.ok) {
                    const json = await backendRes.json();
                    if (json.rates && Object.keys(json.rates).length > 0) {
                        fetched = json;
                    }
                }
            }

            if (fetched && fetched.rates) {
                liveRatesData = fetched;
                processAndRenderRates(fetched, isManual);

                // Update UI metadata
                updateMetadataUI(fetched);

                if (btnLabel) btnLabel.textContent = 'Tersinkron!';
                setTimeout(() => {
                    if (btnLabel) btnLabel.textContent = 'Refresh Sekarang';
                }, 1500);
            } else {
                throw new Error('Semua provider gagal mengembalikan kurs');
            }
        } catch (err) {
            console.error('Fetch live rates error:', err);
            if (btnLabel) btnLabel.textContent = 'Gagal Refresh';
            document.getElementById('syncStatusText').textContent = 'Gagal';
            document.getElementById('syncStatusText').className = 'mt-0.5 block text-xl font-black text-rose-300';
        } finally {
            if (spinner) spinner.classList.remove('animate-spin');
            refreshSecondsLeft = 60;
        }
    }

    // 3. Process Rates, Compare with Snapshot, and Render UI
    function processAndRenderRates(data, isManualRefresh = false) {
        const currentRates = data.rates || {};
        const storageKey = SNAPSHOT_STORAGE_KEY_PREFIX + currentBase;

        // Retrieve previous snapshot from localStorage
        let prevSnapshot = {};
        try {
            const raw = localStorage.getItem(storageKey);
            if (raw) prevSnapshot = JSON.parse(raw);
        } catch (e) {
            prevSnapshot = {};
        }

        const isFirstSessionSnapshot = (!prevSnapshot || Object.keys(prevSnapshot).length === 0);

        // If first load or manual refresh was explicitly triggered, ensure snapshot exists
        if (isFirstSessionSnapshot) {
            prevSnapshot = { ...currentRates };
            try {
                localStorage.setItem(storageKey, JSON.stringify(prevSnapshot));
            } catch (e) {}
        }

        // Render Table & Change Detection
        renderCurrencyTable(currentRates, prevSnapshot);
        detectChangeAndAlert(currentRates, prevSnapshot);
        runCheckerCalculations();

        // Update chart
        updateChartData();

        // Save current rates as new baseline for subsequent refreshes if not in simulation
        if (!isSimulationActive && !isFirstSessionSnapshot && isManualRefresh) {
            try {
                localStorage.setItem(storageKey, JSON.stringify(currentRates));
            } catch (e) {}
        }
    }

    function updateMetadataUI(data) {
        const provider = data.provider || 'Open Exchange Rates (open.er-api.com)';
        const updatedTime = data.time_last_update_utc || data.synced_at || new Date().toLocaleTimeString('id-ID');

        document.getElementById('headerProviderName').textContent = provider;
        document.getElementById('footerProviderLabel').textContent = provider;
        document.getElementById('lastUpdatedHeader').textContent = updatedTime;
        document.getElementById('syncStatusText').textContent = 'Real-Time';
        document.getElementById('syncStatusText').className = 'mt-0.5 block text-xl font-black text-emerald-300';
    }

    // 4. Render Full Currency Table with Live Rates & Deltas
    function renderCurrencyTable(currentRates, baselineRates) {
        const tbody = document.getElementById('currencyTableBody');
        if (!tbody) return;

        let rowsHtml = '';
        const subset = Object.keys(supportedCurrenciesMeta);

        subset.forEach(code => {
            if (code === currentBase) return;

            const meta = supportedCurrenciesMeta[code];
            let rate = currentRates[code] || 1.0;

            // Apply simulation if any
            if (isSimulationActive && simulatedDeltas[code]) {
                rate = rate * (1 + simulatedDeltas[code] / 100);
            }

            const baseline = baselineRates[code] || rate;
            const delta = rate - baseline;
            const percentChange = baseline > 0 ? ((rate - baseline) / baseline) * 100 : 0;
            const isBeyondThreshold = Math.abs(percentChange) >= activeThreshold;

            const directionIcon = percentChange > 0.0001 ? '▲' : (percentChange < -0.0001 ? '▼' : '▬');
            const colorClass = percentChange > 0.0001 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' :
                              (percentChange < -0.0001 ? 'text-rose-600 bg-rose-50 border-rose-200' : 'text-slate-600 bg-slate-50 border-slate-200');

            rowsHtml += `
                <tr class="hover:bg-slate-50/80 transition ${isBeyondThreshold ? 'bg-amber-50/30' : ''}">
                    <td class="px-6 py-4 font-bold text-slate-900">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">${meta.flag}</span>
                            <div>
                                <span class="font-mono-num font-black text-sm text-slate-900">${code}</span>
                                <span class="text-[11px] text-slate-400 block">${meta.name}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">${meta.region}</span>
                    </td>
                    <td class="px-6 py-4 text-right font-mono-num font-extrabold text-sm text-slate-900">
                        ${formatCurrencyValue(rate, code)}
                    </td>
                    <td class="px-6 py-4 text-right font-mono-num text-xs text-slate-500">
                        ${formatCurrencyValue(baseline, code)}
                    </td>
                    <td class="px-6 py-4 text-right font-mono-num font-semibold text-xs ${percentChange >= 0 ? 'text-emerald-700' : 'text-rose-700'}">
                        ${delta > 0 ? '+' : ''}${formatDelta(delta, code)}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold border ${colorClass}">
                            ${directionIcon} ${percentChange > 0 ? '+' : ''}${percentChange.toFixed(2)}%
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        ${isBeyondThreshold ?
                            `<span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold text-amber-900 animate-pulse">
                                ⚠️ Volatilitas Terdeteksi
                             </span>` :
                            `<span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">Stabil</span>`
                        }
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button type="button" onclick="setCalculatorPair('${currentBase}', '${code}', ${rate})"
                                class="rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 font-bold px-2.5 py-1 text-[11px] transition">
                            Konversi ➔
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = rowsHtml;
    }

    // 5. Change Detection & Real-Time Alert Engine
    function detectChangeAndAlert(currentRates, baselineRates) {
        const banner = document.getElementById('detectionAlertBanner');
        const triggeredList = document.getElementById('triggeredCurrenciesList');
        const countBadge = document.getElementById('alertCountBadge');
        const thresholdText = document.getElementById('alertThresholdText');
        const baseText = document.getElementById('alertBaseText');

        if (!banner || !triggeredList) return;

        thresholdText.textContent = activeThreshold.toFixed(2) + '%';
        baseText.textContent = currentBase;

        const triggered = [];
        const subset = Object.keys(supportedCurrenciesMeta);

        subset.forEach(code => {
            if (code === currentBase) return;

            let rate = currentRates[code] || 1.0;
            if (isSimulationActive && simulatedDeltas[code]) {
                rate = rate * (1 + simulatedDeltas[code] / 100);
            }

            const baseline = baselineRates[code] || rate;
            const percentChange = baseline > 0 ? ((rate - baseline) / baseline) * 100 : 0;

            if (Math.abs(percentChange) >= activeThreshold) {
                triggered.push({
                    code: code,
                    meta: supportedCurrenciesMeta[code],
                    rate: rate,
                    baseline: baseline,
                    percentChange: percentChange,
                    delta: rate - baseline
                });
            }
        });

        if (triggered.length > 0) {
            countBadge.textContent = `${triggered.length} Pasangan Melampaui Batas (≥ ${activeThreshold.toFixed(2)}%)`;
            let chipsHtml = '';

            triggered.forEach(item => {
                const isSurge = item.percentChange > 0;
                chipsHtml += `
                    <div class="inline-flex items-center gap-1.5 rounded-xl border ${isSurge ? 'border-emerald-300 bg-emerald-50 text-emerald-900' : 'border-rose-300 bg-rose-50 text-rose-900'} px-3 py-1 text-xs font-bold shadow-sm">
                        <span>${item.meta.flag}</span>
                        <span>${currentBase}/${item.code}:</span>
                        <span>${isSurge ? '▲ +' : '▼ '}${item.percentChange.toFixed(2)}%</span>
                        <span class="text-[10px] font-normal opacity-75">(${formatDelta(item.delta, item.code)})</span>
                    </div>
                `;
            });

            triggeredList.innerHTML = chipsHtml;
            banner.classList.remove('hidden');
        } else {
            banner.classList.add('hidden');
        }
    }

    function dismissAlert() {
        document.getElementById('detectionAlertBanner')?.classList.add('hidden');
    }

    // 6. Simulation / Test Mode (Explicitly Separated from Real API Data)
    window.simulateVolatility = function(type) {
        isSimulationActive = true;
        document.getElementById('simulationActiveNotice')?.classList.remove('hidden');

        if (type === 'surge') {
            simulatedDeltas = {
                'USD': 1.65,
                'EUR': 1.45,
                'CNY': 0.95,
                'SAR': 1.60,
                'PHP': 1.85,
            };
        } else if (type === 'drop') {
            simulatedDeltas = {
                'USD': -1.25,
                'EUR': -1.40,
                'SGD': -1.15,
                'MYR': -0.90,
                'JPY': -1.80,
            };
        }

        const currentRates = liveRatesData.rates || {};
        const storageKey = SNAPSHOT_STORAGE_KEY_PREFIX + currentBase;
        let baselineRates = {};
        try {
            baselineRates = JSON.parse(localStorage.getItem(storageKey)) || currentRates;
        } catch (e) {
            baselineRates = currentRates;
        }

        renderCurrencyTable(currentRates, baselineRates);
        detectChangeAndAlert(currentRates, baselineRates);
        runCheckerCalculations();
    };

    window.resetSimulation = function() {
        isSimulationActive = false;
        simulatedDeltas = {};
        document.getElementById('simulationActiveNotice')?.classList.add('hidden');

        const currentRates = liveRatesData.rates || {};
        const storageKey = SNAPSHOT_STORAGE_KEY_PREFIX + currentBase;
        let baselineRates = {};
        try {
            baselineRates = JSON.parse(localStorage.getItem(storageKey)) || currentRates;
        } catch (e) {
            baselineRates = currentRates;
        }

        renderCurrencyTable(currentRates, baselineRates);
        detectChangeAndAlert(currentRates, baselineRates);
        runCheckerCalculations();
    };

    // 7. Base Currency & Threshold Controls
    window.changeBaseCurrency = function(newBase) {
        window.location.href = "{{ route('currency.index') }}?base=" + newBase;
    };

    window.changeThreshold = function(newThreshold) {
        activeThreshold = parseFloat(newThreshold);
        document.getElementById('badgeThreshold').textContent = '≥ ' + activeThreshold.toFixed(2) + '%';
        
        const currentRates = liveRatesData.rates || {};
        const storageKey = SNAPSHOT_STORAGE_KEY_PREFIX + currentBase;
        let baselineRates = {};
        try {
            baselineRates = JSON.parse(localStorage.getItem(storageKey)) || currentRates;
        } catch (e) {
            baselineRates = currentRates;
        }

        renderCurrencyTable(currentRates, baselineRates);
        detectChangeAndAlert(currentRates, baselineRates);
    };

    // 8. Auto-Refresh & Manual Refresh Trigger
    window.refreshRates = async function(isManual = false) {
        await fetchLiveRatesFlow(currentBase, isManual);
    };

    function startAutoRefreshCountdown() {
        if (countdownInterval) clearInterval(countdownInterval);
        refreshSecondsLeft = 60;

        countdownInterval = setInterval(() => {
            refreshSecondsLeft--;
            const countdownEl = document.getElementById('refreshCountdownText');
            if (countdownEl) countdownEl.textContent = refreshSecondsLeft + 's';

            if (refreshSecondsLeft <= 0) {
                refreshRates(false);
            }
        }, 1000);
    }

    // 9. Chart.js FX Trend Visualizer
    function initChart() {
        const ctx = document.getElementById('fxTrendChart')?.getContext('2d');
        if (!ctx) return;

        const defaultTarget = currentBase === 'USD' ? 'IDR' : 'USD';
        document.getElementById('chartTargetCurrency').value = defaultTarget;

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: `${currentBase} / ${defaultTarget}`,
                    data: [],
                    borderColor: '#0284c7', // Sky ocean
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0284c7',
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 10,
                        backgroundColor: '#071322',
                        titleFont: { size: 12 },
                        bodyFont: { size: 13, weight: 'bold' }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });

        updateChartData();
    }

    window.updateChartData = async function() {
        if (!chartInstance) return;
        const target = document.getElementById('chartTargetCurrency')?.value || 'USD';

        try {
            const res = await fetch(`{{ route('currency.history') }}?base=${currentBase}&target=${target}&days=${chartDays}`);
            if (res.ok) {
                const data = await res.json();
                chartInstance.data.labels = data.labels;
                chartInstance.data.datasets[0].label = `${currentBase} / ${target}`;
                chartInstance.data.datasets[0].data = data.values;
                chartInstance.update();

                document.getElementById('chartStatMax').textContent = formatCurrencyValue(data.max, target);
                document.getElementById('chartStatMin').textContent = formatCurrencyValue(data.min, target);
                document.getElementById('chartStatCurrent').textContent = formatCurrencyValue(data.current, target);
            }
        } catch (e) {
            console.error('Failed to load chart history:', e);
        }
    };

    window.setChartDays = function(days, btn) {
        chartDays = days;
        document.querySelectorAll('.chart-day-btn').forEach(b => {
            b.classList.remove('active', 'bg-white', 'text-ocean-700', 'shadow-sm');
            b.classList.add('text-slate-600');
        });
        btn.classList.add('active', 'bg-white', 'text-ocean-700', 'shadow-sm');
        btn.classList.remove('text-slate-600');
        updateChartData();
    };

    // 10. Multi-Mode Currency Checker Engine (1-to-1 & 1-to-Many)
    let currentCheckerMode = '1-to-1';
    let activeMultiTargets = new Set();
    let swapRotationDeg = 0;

    function initMultiTargets() {
        const from = document.getElementById('calcFromCurrency')?.value || 'USD';
        activeMultiTargets.clear();
        Object.keys(supportedCurrenciesMeta).forEach(code => {
            if (code !== from) {
                activeMultiTargets.add(code);
            }
        });
    }

    function setupCalculatorListeners() {
        document.getElementById('calcInputAmount')?.addEventListener('input', runCheckerCalculations);
        document.getElementById('calcFromCurrency')?.addEventListener('change', async () => {
            initMultiTargets();
            renderMultiTargetChips();
            runCheckerCalculations();
        });
        document.getElementById('calcToCurrency')?.addEventListener('change', runCheckerCalculations);

        renderMultiTargetChips();
    }

    function getAdjustedRate(code, allRates) {
        let rate = allRates[code] || 1.0;
        if (isSimulationActive && simulatedDeltas[code]) {
            rate = rate * (1 + simulatedDeltas[code] / 100);
        }
        return rate;
    }

    function computePairRate(from, to, allRates) {
        if (from === to) return 1.0;

        const rateFrom = getAdjustedRate(from, allRates);
        const rateTo = getAdjustedRate(to, allRates);

        if (from === currentBase) {
            return rateTo;
        } else if (to === currentBase) {
            return rateFrom > 0 ? (1.0 / rateFrom) : 1.0;
        } else {
            return rateFrom > 0 ? (rateTo / rateFrom) : rateTo;
        }
    }

    function runCheckerCalculations() {
        calculateCheckerOneToOne();
        calculateCheckerOneToMany();
    }

    // --- Mode 1-to-1 (Fokus Tunggal) ---
    function calculateCheckerOneToOne() {
        const amount = parseFloat(document.getElementById('calcInputAmount')?.value || 0);
        const from = document.getElementById('calcFromCurrency')?.value || 'USD';
        const to = document.getElementById('calcToCurrency')?.value || 'IDR';

        const allRates = liveRatesData.rates || {};
        const unitRate = computePairRate(from, to, allRates);
        const converted = amount * unitRate;
        const inverseRate = unitRate > 0 ? (1.0 / unitRate) : 0.0;

        const resultEl = document.getElementById('calcConvertedResult');
        const ratioEl = document.getElementById('calcRateRatio');
        const inverseEl = document.getElementById('calcInverseRatio');

        if (resultEl) {
            resultEl.textContent = formatWithCurrencySymbol(converted, to);
        }
        if (ratioEl) {
            ratioEl.textContent = `1 ${from} = ${formatWithCurrencySymbol(unitRate, to)}`;
        }
        if (inverseEl) {
            inverseEl.textContent = `1 ${to} = ${formatWithCurrencySymbol(inverseRate, from)}`;
        }
    }

    // --- Mode 1-to-Many (Sekaligus Banyak Mata Uang) ---
    function calculateCheckerOneToMany() {
        const amount = parseFloat(document.getElementById('calcInputAmount')?.value || 0);
        const from = document.getElementById('calcFromCurrency')?.value || 'USD';
        const allRates = liveRatesData.rates || {};

        const refLabel = document.getElementById('multiBaseReferenceLabel');
        if (refLabel) {
            refLabel.textContent = `Acuan: ${amount >= 100 ? new Intl.NumberFormat('id-ID').format(amount) : amount} ${from}`;
        }

        const grid = document.getElementById('multiCurrencyGrid');
        if (!grid) return;

        let cardsHtml = '';
        let targetCount = 0;

        Object.keys(supportedCurrenciesMeta).forEach(code => {
            if (code === from) return;
            if (!activeMultiTargets.has(code)) return;

            targetCount++;
            const meta = supportedCurrenciesMeta[code];
            const unitRate = computePairRate(from, code, allRates);
            const converted = amount * unitRate;

            cardsHtml += `
                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-2xs hover:border-ocean-300 hover:shadow-sm transition">
                    <div class="flex items-center justify-between gap-1 border-b border-slate-100 pb-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base">${meta.flag}</span>
                            <div>
                                <span class="font-bold text-xs text-slate-900 font-mono">${code}</span>
                                <span class="text-[10px] text-slate-400 block -mt-0.5">${meta.symbol}</span>
                            </div>
                        </div>
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-600 truncate max-w-[95px]">${meta.region}</span>
                    </div>

                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-[10px] text-slate-400 font-medium">Hasil Konversi:</span>
                        <strong class="font-mono-num text-sm font-black text-slate-900">${formatWithCurrencySymbol(converted, code)}</strong>
                    </div>

                    <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-100 pt-1.5">
                        <span class="font-mono-num text-[10px] text-slate-500">1 ${from} = ${formatCurrencyValue(unitRate, code)}</span>
                        <button type="button" onclick="focusOneToOnePair('${code}')"
                                class="inline-flex items-center gap-0.5 text-ocean-600 hover:text-ocean-800 font-bold hover:underline">
                            <span>Fokus</span>
                            <span aria-hidden="true">➔</span>
                        </button>
                    </div>
                </div>
            `;
        });

        if (targetCount === 0) {
            cardsHtml = `
                <div class="col-span-full py-8 text-center text-xs text-slate-400">
                    Tidak ada mata uang yang dipilih. Silakan klik chip di atas untuk menampilkan mata uang.
                </div>
            `;
        }

        grid.innerHTML = cardsHtml;

        const countLabel = document.getElementById('multiCountLabel');
        if (countLabel) {
            countLabel.textContent = `${targetCount} Mata Uang Aktif`;
        }
    }

    function renderMultiTargetChips() {
        const container = document.getElementById('multiTargetChipsContainer');
        if (!container) return;

        const from = document.getElementById('calcFromCurrency')?.value || 'USD';
        let chipsHtml = '';

        Object.keys(supportedCurrenciesMeta).forEach(code => {
            if (code === from) return;
            const meta = supportedCurrenciesMeta[code];
            const isChecked = activeMultiTargets.has(code);

            chipsHtml += `
                <button type="button" onclick="toggleTargetCurrency('${code}')"
                        class="inline-flex items-center gap-1 rounded-lg border px-2 py-0.5 text-[11px] font-semibold transition ${
                            isChecked
                                ? 'bg-cyan-50 border-cyan-300 text-cyan-900 font-bold shadow-2xs'
                                : 'bg-white border-slate-200 text-slate-400 hover:text-slate-600'
                        }">
                    <span>${meta.flag}</span>
                    <span>${code}</span>
                    <span>${isChecked ? '✓' : '+'}</span>
                </button>
            `;
        });

        container.innerHTML = chipsHtml;
    }

    window.toggleTargetCurrency = function(code) {
        if (activeMultiTargets.has(code)) {
            activeMultiTargets.delete(code);
        } else {
            activeMultiTargets.add(code);
        }
        renderMultiTargetChips();
        calculateCheckerOneToMany();
    };

    window.filterMultiRegion = function(region) {
        const from = document.getElementById('calcFromCurrency')?.value || 'USD';
        activeMultiTargets.clear();

        const regionMap = {
            ASEAN: ['IDR', 'SGD', 'MYR', 'PHP', 'THB', 'VND'],
            EUROPE: ['EUR', 'GBP'],
            MIDEAST: ['SAR', 'AED'],
            EASTASIA: ['CNY', 'JPY'],
        };

        if (region === 'ALL') {
            Object.keys(supportedCurrenciesMeta).forEach(c => {
                if (c !== from) activeMultiTargets.add(c);
            });
        } else if (regionMap[region]) {
            regionMap[region].forEach(c => {
                if (c !== from && supportedCurrenciesMeta[c]) activeMultiTargets.add(c);
            });
        }

        // Update button styles
        document.querySelectorAll('.region-filter-btn').forEach(b => {
            b.className = 'region-filter-btn rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 font-semibold transition';
        });
        const activeBtn = document.getElementById(`btnFilterRegion-${region}`);
        if (activeBtn) {
            activeBtn.className = 'region-filter-btn rounded-lg bg-navy-900 text-cyan-300 px-2 py-0.5 font-bold transition shadow-2xs';
        }

        renderMultiTargetChips();
        calculateCheckerOneToMany();
    };

    window.switchCheckerMode = function(mode) {
        currentCheckerMode = mode;

        const view1 = document.getElementById('viewMode1to1');
        const view2 = document.getElementById('viewMode1toMany');
        const tab1 = document.getElementById('tabMode1to1');
        const tab2 = document.getElementById('tabMode1toMany');
        const badge = document.getElementById('activeModeBadge');

        if (mode === '1-to-many') {
            view1?.classList.add('hidden');
            view2?.classList.remove('hidden');

            tab1.className = 'checker-tab-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition';
            tab2.className = 'checker-tab-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition bg-white text-navy-900 shadow-xs';

            if (badge) {
                badge.textContent = '1-TO-MANY';
                badge.className = 'rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-bold text-cyan-800';
            }

            renderMultiTargetChips();
            calculateCheckerOneToMany();
        } else {
            view2?.classList.add('hidden');
            view1?.classList.remove('hidden');

            tab2.className = 'checker-tab-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition';
            tab1.className = 'checker-tab-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition bg-white text-navy-900 shadow-xs';

            if (badge) {
                badge.textContent = '1-TO-1';
                badge.className = 'rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-bold text-teal-800';
            }

            calculateCheckerOneToOne();
        }
    };

    window.swapCheckerCurrencies = function() {
        const fromEl = document.getElementById('calcFromCurrency');
        const toEl = document.getElementById('calcToCurrency');
        const svg = document.getElementById('swapIconSvg');

        if (svg) {
            swapRotationDeg += 180;
            svg.style.transform = `rotate(${swapRotationDeg}deg)`;
        }

        const temp = fromEl.value;
        fromEl.value = toEl.value;
        toEl.value = temp;

        initMultiTargets();
        renderMultiTargetChips();
        runCheckerCalculations();
    };

    window.setCheckerAmount = function(amt) {
        const input = document.getElementById('calcInputAmount');
        if (input) {
            input.value = amt;
            runCheckerCalculations();
        }
    };

    window.focusOneToOnePair = function(targetCode) {
        document.getElementById('calcToCurrency').value = targetCode;
        switchCheckerMode('1-to-1');
        calculateCheckerOneToOne();
    };

    window.setPresetCargo = function(amount, from, to) {
        document.getElementById('calcInputAmount').value = amount;
        document.getElementById('calcFromCurrency').value = from;
        document.getElementById('calcToCurrency').value = to;

        initMultiTargets();
        renderMultiTargetChips();
        switchCheckerMode('1-to-1');
        runCheckerCalculations();
    };

    window.setCalculatorPair = function(from, to, currentRate) {
        document.getElementById('calcFromCurrency').value = from;
        document.getElementById('calcToCurrency').value = to;

        initMultiTargets();
        renderMultiTargetChips();
        switchCheckerMode('1-to-1');
        runCheckerCalculations();

        // Scroll to calculator
        document.getElementById('calcInputAmount')?.focus();
    };

    // 11. Table Search Filter
    window.filterCurrencyTable = function() {
        const query = (document.getElementById('tableSearchInput')?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#currencyTableBody tr');

        rows.forEach(r => {
            const text = r.textContent.toLowerCase();
            r.style.display = text.includes(query) ? '' : 'none';
        });
    };

    // Helper Formatter Functions
    function formatCurrencyValue(val, curr) {
        if (curr === 'IDR' || curr === 'VND') {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(val);
        }
        if (val >= 100) {
            return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
        }
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 }).format(val);
    }

    function formatDelta(delta, curr) {
        if (curr === 'IDR' || curr === 'VND') {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(delta);
        }
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 }).format(delta);
    }

    function formatWithCurrencySymbol(val, curr) {
        const symbol = (supportedCurrenciesMeta[curr]?.symbol || curr) + ' ';
        const decimals = (curr === 'IDR' || curr === 'VND') ? 0 : 2;
        return symbol + new Intl.NumberFormat('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(val);
    }
</script>
@endpush
