@extends('layouts.app')

@section('title', __('messages.page_titles.tracking_detail') . ' · ' . $shipment->shipment_number)
@section('page-title', __('messages.nav.tracking'))

@push('head')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <!-- Globe.gl 3D Earth Library -->
    <script src="{{ asset('vendor/globe/globe.gl.min.js') }}"></script>
    <script>
        if (typeof Globe === 'undefined') {
            document.write('<script src="https://unpkg.com/globe.gl@2.46.2/dist/globe.gl.min.js"><\/script>');
        }
    </script>
    <style>
        /* Map container setup */
        #liveTrackingMap {
            min-height: 540px;
            width: 100%;
            z-index: 1;
        }

        /* Animated Vessel Pulse Ripple (Unified Vibe across all modes) */
        @keyframes vesselRadarPulse {
            0% {
                transform: scale(0.6);
                opacity: 0.9;
            }
            70% {
                transform: scale(2.2);
                opacity: 0.15;
            }
            100% {
                transform: scale(2.6);
                opacity: 0;
            }
        }
        .vessel-pulse {
            position: absolute;
            width: 52px;
            height: 52px;
            left: -6px;
            top: -6px;
            border-radius: 50%;
            background: rgba(6, 182, 212, 0.45);
            animation: vesselRadarPulse 2.4s ease-out infinite;
            pointer-events: none;
            z-index: -1;
        }
        .vessel-pulse-inner {
            position: absolute;
            width: 28px;
            height: 28px;
            left: 6px;
            top: 6px;
            border-radius: 50%;
            background: rgba(0, 168, 132, 0.35);
            animation: vesselRadarPulse 2.4s ease-out infinite 0.6s;
            pointer-events: none;
            z-index: -1;
        }

        /* Air Cargo Plane Radar Pulse */
        @keyframes planeRadarPulse {
            0% { transform: scale(0.6); opacity: 0.9; }
            70% { transform: scale(2.2); opacity: 0.15; }
            100% { transform: scale(2.6); opacity: 0; }
        }
        .plane-pulse {
            position: absolute;
            width: 52px;
            height: 52px;
            left: -6px;
            top: -6px;
            border-radius: 50%;
            background: rgba(2, 132, 199, 0.45);
            animation: planeRadarPulse 2.4s ease-out infinite;
            pointer-events: none;
            z-index: -1;
        }
        .plane-pulse-inner {
            position: absolute;
            width: 28px;
            height: 28px;
            left: 6px;
            top: 6px;
            border-radius: 50%;
            background: rgba(56, 189, 248, 0.35);
            animation: planeRadarPulse 2.4s ease-out infinite 0.6s;
            pointer-events: none;
            z-index: -1;
        }

        /* Road Cargo Truck GPS Radar Pulse */
        @keyframes truckRadarPulse {
            0% { transform: scale(0.6); opacity: 0.9; }
            70% { transform: scale(2.2); opacity: 0.15; }
            100% { transform: scale(2.6); opacity: 0; }
        }
        .truck-pulse {
            position: absolute;
            width: 52px;
            height: 52px;
            left: -6px;
            top: -6px;
            border-radius: 50%;
            background: rgba(5, 150, 105, 0.45);
            animation: truckRadarPulse 2.4s ease-out infinite;
            pointer-events: none;
            z-index: -1;
        }
        .truck-pulse-inner {
            position: absolute;
            width: 28px;
            height: 28px;
            left: 6px;
            top: 6px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.35);
            animation: truckRadarPulse 2.4s ease-out infinite 0.6s;
            pointer-events: none;
            z-index: -1;
        }

        /* Floating Callout Popup matching user screenshot */
        .leaflet-popup-content-wrapper.vessel-callout-wrapper {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }
        .leaflet-popup-content.vessel-callout-content {
            margin: 0 !important;
            line-height: normal !important;
        }
        .leaflet-popup-tip-container.vessel-callout-tip {
            display: none !important;
        }

        .custom-vessel-callout {
            background: #ffffff;
            border-radius: 18px;
            padding: 11px 18px 12px 18px;
            box-shadow: 0 14px 30px -4px rgba(15, 23, 42, 0.18), 0 6px 12px -2px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.9);
            position: relative;
            white-space: nowrap;
            text-align: left;
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            transform: translateY(-10px);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .custom-vessel-callout:hover {
            transform: translateY(-13px);
            box-shadow: 0 18px 36px -4px rgba(15, 23, 42, 0.22);
        }
        .custom-vessel-callout .callout-sub {
            font-size: 13px;
            font-weight: 500;
            color: #475569; /* Slate 600 */
            letter-spacing: -0.01em;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .custom-vessel-callout .callout-dest {
            font-size: 15px;
            font-weight: 700;
            color: #00a884; /* Vibrant teal matching user screenshot */
            letter-spacing: -0.015em;
        }
        .custom-vessel-callout .callout-arrow {
            position: absolute;
            bottom: -9px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 9px solid transparent;
            border-right: 9px solid transparent;
            border-top: 9px solid #ffffff;
            filter: drop-shadow(0 2px 2px rgba(15, 23, 42, 0.08));
        }

        /* Custom Port Marker Pins */
        .port-pin {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            border: 2px solid #ffffff;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.15s ease;
        }
        .port-pin:hover {
            transform: scale(1.15);
        }
    </style>
@endpush

@section('content')
<div class="space-y-6 p-4 sm:p-6 md:p-8">
    <!-- Top Bar with Back Button & Export Reference -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('tracking.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-cyan-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('messages.tracking_page.view_all_shipments') }}
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ __('messages.tracking_page.badge') }}
            </span>
            <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">
                PT Mayora Indah Tbk Global Trade
            </span>
        </div>
    </div>

    <!-- Shipment Overview Header Card -->
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded bg-navy-900 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-cyan-300">
                        {{ $shipment->carrier?->scac ?? 'CARRIER' }}
                    </span>
                    <span id="headerModeBadge" class="inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-xs font-bold shadow-xs ring-1 ring-inset {{ $shipment->transport_mode === 'AIR' ? 'bg-sky-50 text-sky-700 ring-sky-300' : ($shipment->transport_mode === 'ROAD' ? 'bg-emerald-50 text-emerald-700 ring-emerald-300' : 'bg-cyan-50 text-cyan-700 ring-cyan-300') }}">
                        <span id="headerModeIcon">{{ $telemetry['transport_mode_icon'] }}</span>
                        <span id="headerModeLabel">{{ $telemetry['transport_mode_label'] }}</span>
                    </span>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Shipment No.</span>
                </div>
                <h2 class="mt-1.5 text-2xl font-extrabold text-slate-900 md:text-3xl tracking-tight">
                    {{ $shipment->shipment_number }}
                </h2>
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
                    <span class="font-medium text-slate-900" id="headerVehicleName">{{ $telemetry['transport_mode_icon'] }} {{ $shipment->vehicle_identifier ?? $shipment->vessel_name ?? 'Fleet Carrier' }}</span>
                    <span>•</span>
                    <span>Trip/Voyage: <strong class="text-slate-800">{{ $shipment->voyage_number ?? '—' }}</strong></span>
                    <span>•</span>
                    <span>Container/Unit: <strong class="font-mono text-cyan-800">{{ $shipment->container_number }}</strong></span>
                    <span>•</span>
                    <span>Seal: <strong class="font-mono text-slate-700">{{ $shipment->seal_number ?? '—' }}</strong></span>
                </div>
            </div>

            <!-- Status and Delay Badges -->
            <div class="flex flex-col items-end gap-2">
                @php
                    $statusStyles = [
                        'CREATED' => 'bg-slate-100 text-slate-700 ring-slate-300',
                        'BOOKED' => 'bg-sky-50 text-sky-800 ring-sky-300',
                        'PICKED_UP' => 'bg-blue-50 text-blue-800 ring-blue-300',
                        'IN_TRANSIT' => 'bg-cyan-50 text-cyan-800 ring-cyan-400 font-bold',
                        'DELIVERED' => 'bg-emerald-50 text-emerald-800 ring-emerald-300 font-bold',
                        'EXCEPTION' => 'bg-rose-50 text-rose-800 ring-rose-300 font-bold',
                    ];
                    $delayStyles = [
                        'ON_TIME' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'LOW_DELAY' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'MEDIUM_DELAY' => 'bg-orange-100 text-orange-800 border-orange-300',
                        'HIGH_DELAY' => 'bg-rose-100 text-rose-800 border-rose-300 font-bold animate-pulse',
                    ];
                @endphp
                <span class="rounded-full px-4 py-1.5 text-sm font-bold shadow-sm ring-1 ring-inset {{ $statusStyles[$shipment->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                    {{ __('messages.statuses.' . $shipment->status) ?? str_replace('_', ' ', $shipment->status) }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-semibold {{ $delayStyles[$shipment->delay_status] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $shipment->delay_status === 'ON_TIME' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ __('messages.delays.' . $shipment->delay_status) ?? str_replace('_', ' ', $shipment->delay_status) }}
                    @if($shipment->delay_duration_hours > 0)
                        (+{{ $shipment->delay_duration_hours }}h)
                    @endif
                </span>
            </div>
        </div>

        <!-- Port-to-Port Route Flow Bar -->
        <div class="mt-6 grid grid-cols-1 gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-3.5">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pelabuhan Muat (Origin)</span>
                <p class="mt-1 font-bold text-slate-900">{{ $shipment->origin?->name ?? 'Tanjung Priok' }}</p>
                <p class="text-xs text-slate-500">{{ $shipment->origin?->city }}, {{ $shipment->origin?->country_code }} ({{ $shipment->origin?->unlocode }})</p>
                <p class="mt-2 text-[11px] font-medium text-slate-400">Berangkat: {{ $shipment->actual_departure_at?->format('d M Y, H:i') ?? $shipment->expected_departure_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>

            <div class="rounded-xl bg-teal-50/70 p-3.5 border border-teal-100">
                <span class="text-xs font-semibold uppercase tracking-wider text-teal-700">Pelabuhan Tujuan (Destination)</span>
                <p class="mt-1 font-bold text-teal-900">{{ $shipment->destination?->name ?? 'Destination Port' }}</p>
                <p class="text-xs text-teal-700 font-medium">{{ $shipment->destination?->city }}, {{ $shipment->destination?->country_code }} ({{ $shipment->destination?->unlocode }})</p>
                <p class="mt-2 text-[11px] font-semibold text-teal-800">Estimasi Tiba: {{ $shipment->estimated_arrival_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>

            <div class="rounded-xl bg-slate-50 p-3.5">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Posisi Terkini (GPS/Satelit)</span>
                <p class="mt-1 font-bold text-slate-900">{{ $telemetry['current_position']['name'] }}</p>
                <p class="text-xs text-cyan-800 font-mono">
                    {{ number_format($telemetry['current_position']['latitude'], 4) }}°, {{ number_format($telemetry['current_position']['longitude'], 4) }}°
                </p>
                <p class="mt-2 text-[11px] text-slate-500">Speed: <strong id="telemetrySpeedBadge">{{ $telemetry['speed_formatted'] }}</strong> · Status: <strong>{{ str_replace('_', ' ', $shipment->status) }}</strong></p>
            </div>

            <div class="rounded-xl bg-slate-50 p-3.5">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Progres Pelayaran Global</span>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-teal-700">{{ $telemetry['route']['progress_percent'] }}%</span>
                    <span class="text-xs text-slate-500">{{ number_format($telemetry['route']['sailed_distance_km']) }} / {{ number_format($telemetry['route']['total_distance_km']) }} km</span>
                </div>
                <!-- Progress Bar -->
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-cyan-500 transition-all duration-1000"
                         style="width: {{ $telemetry['route']['progress_percent'] }}%"></div>
                </div>
                <p class="mt-1.5 text-[11px] text-slate-400 text-right">Sisa: ~{{ number_format($telemetry['route']['remaining_distance_km']) }} km</p>
            </div>
        </div>
    </section>

    <!-- Interactive Live Map Section (Matching User Reference Image) -->
    <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
        <!-- Map Card Header with Realtime Controls & Multi-Modal Switcher -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-3.5">
            <div class="flex items-center gap-3">
                <div id="mapHeaderIcon" class="grid h-8 w-8 place-items-center rounded-lg bg-teal-600 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <div>
                    <h3 id="mapHeaderTitle" class="text-base font-bold text-slate-900">Peta Pelacakan Rute & Posisi Real-Time</h3>
                    <p class="text-xs text-slate-500">Koordinat satelit live, lintasan dua warna, dan status armada per moda.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- 2D Map vs 3D Globe View Switcher -->
                <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1 text-xs shadow-xs">
                    <button type="button" id="btnDetailMap2D" onclick="switchDetailMapView('2D')"
                            title="{{ __('messages.dashboard.map.view_2d_title') }}"
                            class="map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-bold bg-navy-900 text-cyan-300 shadow-xs transition">
                        <span>{{ __('messages.dashboard.map.view_2d') }}</span>
                    </button>
                    <button type="button" id="btnDetailMap3D" onclick="switchDetailMapView('3D')"
                            title="{{ __('messages.dashboard.map.view_3d_title') }}"
                            class="map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-semibold text-slate-600 hover:text-slate-900 transition">
                        <span>{{ __('messages.dashboard.map.view_3d') }}</span>
                        <span class="rounded bg-teal-500/20 px-1 py-0.5 text-[9px] font-black text-teal-700 uppercase">3D</span>
                    </button>
                </div>

                <!-- Interactive Transport Mode Switcher / Simulator (Bagian 2) -->
                <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 text-xs shadow-xs">
                    <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Moda:</span>
                    <button type="button" onclick="switchTransportMode('SEA')" id="btnModeSEA"
                            class="mode-switch-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold transition hover:bg-slate-50">
                        🚢 Laut
                    </button>
                    <button type="button" onclick="switchTransportMode('AIR')" id="btnModeAIR"
                            class="mode-switch-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold transition hover:bg-slate-50">
                        ✈️ Udara
                    </button>
                    <button type="button" onclick="switchTransportMode('ROAD')" id="btnModeROAD"
                            class="mode-switch-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold transition hover:bg-slate-50">
                        🚛 Darat
                    </button>
                </div>

                <!-- Sync API button -->
                <button id="btnManualSync" type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-cyan-700">
                    <svg id="syncIcon" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span id="syncText">Sync API</span>
                </button>

                <div class="hidden text-xs text-slate-400 sm:block">
                    Terakhir diperbarui: <span id="syncTimestamp" class="font-mono font-medium text-slate-600">{{ $weather['synced_at'] ?? now()->format('H:i:s T') }}</span>
                </div>
            </div>
        </div>

        <!-- The Map Container (2D Leaflet and 3D Globe) -->
        <div class="relative">
            <!-- 2D Leaflet Map -->
            <div id="liveTrackingMap"></div>

            <!-- 3D Globe Container (Globe.gl) -->
            <div id="liveTrackingGlobeContainer" class="hidden min-h-[540px] w-full overflow-hidden rounded-b-2xl bg-[#040d1a] relative">
                <!-- Floating Controls in Top-Left (Weather card stays on top-right) -->
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2">
                    <button type="button" id="btnDetailGlobeAutoRotate" onclick="toggleDetailGlobeAutoRotate()"
                            title="{{ __('messages.dashboard.map.auto_rotate') }}"
                            class="flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/85 px-3 py-1.5 text-xs font-bold text-cyan-300 shadow-lg backdrop-blur-md transition hover:bg-navy-900">
                        <span id="detailGlobeAutoRotateIcon" class="inline-block transition-transform duration-500 animate-spin">🔄</span>
                        <span id="detailGlobeAutoRotateText">{{ __('messages.dashboard.map.auto_rotate') }}</span>
                    </button>
                    <button type="button" onclick="resetDetailGlobeView()"
                            title="{{ __('messages.dashboard.map.reset_view') }}"
                            class="flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/85 px-3 py-1.5 text-xs font-bold text-slate-200 shadow-lg backdrop-blur-md transition hover:bg-navy-900 hover:text-white">
                        <span>🎯</span>
                        <span>{{ __('messages.dashboard.map.reset_view') }}</span>
                    </button>
                </div>

                <!-- Globe Hint in Bottom-Left -->
                <div class="pointer-events-none absolute bottom-4 left-4 z-20 flex flex-wrap items-center gap-2 rounded-xl border border-white/10 bg-navy-950/80 px-3.5 py-2 text-xs text-slate-300 shadow-xl backdrop-blur-md">
                    <span class="flex items-center gap-1.5 font-bold text-teal-300">
                        <span class="h-2 w-2 rounded-full bg-teal-400 animate-ping"></span>
                        <span>{{ $shipment->shipment_number }}</span>
                    </span>
                    <span class="text-white/20">|</span>
                    <span class="text-[11px] text-slate-400">{{ __('messages.dashboard.map.globe_hint') }}</span>
                </div>
            </div>

            <!-- Floating Top-Right Glassmorphism Card: Live Marine Weather via Open-Meteo API -->
            <div class="pointer-events-auto absolute top-4 right-4 z-[900] w-64 md:w-72 rounded-2xl border border-white/60 bg-white/90 p-3.5 shadow-xl backdrop-blur-md transition-all">
                <div class="flex items-center justify-between border-b border-slate-200/70 pb-2">
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-teal-500"></span>
                        </span>
                        <span class="text-xs font-bold tracking-tight text-slate-800">Marine Weather API</span>
                    </div>
                    <span class="rounded bg-teal-100 px-1.5 py-0.5 text-[10px] font-bold text-teal-800">Open-Meteo</span>
                </div>

                <div class="mt-2.5 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="block text-[10px] font-medium uppercase text-slate-400">Kondisi Laut</span>
                        <p id="weatherCondition" class="font-bold text-slate-800">
                            {{ $weather['condition_icon'] ?? '⛅' }} {{ $weather['condition_text'] ?? 'Partly Cloudy' }}
                        </p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-medium uppercase text-slate-400">Suhu Udara</span>
                        <p id="weatherTemp" class="font-bold text-slate-800">
                            {{ $weather['temperature'] ?? 28.5 }} °C
                        </p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-medium uppercase text-slate-400">Kecepatan Angin</span>
                        <p id="weatherWind" class="font-bold text-slate-800">
                            {{ $weather['wind_speed_knots'] ?? 8.0 }} kts
                            <span class="text-[10px] font-normal text-slate-500">({{ $weather['wind_speed_kmh'] ?? 14.8 }} km/h)</span>
                        </p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-medium uppercase text-slate-400">Status Ombak</span>
                        <p id="weatherSea" class="font-bold text-teal-700">
                            {{ $weather['sea_state'] ?? 'Smooth Sea' }}
                        </p>
                    </div>
                </div>

                <div class="mt-2.5 flex items-center justify-between border-t border-slate-200/70 pt-2 text-[10px] text-slate-500">
                    <span class="font-mono" id="weatherCoords">
                        Lat {{ number_format($telemetry['current_position']['latitude'], 2) }}°, Lng {{ number_format($telemetry['current_position']['longitude'], 2) }}°
                    </span>
                    <span class="font-semibold text-teal-700">Live Satellite Data</span>
                </div>
            </div>

            <!-- Floating Bottom-Right GPS Re-Center FAB Button (Matching User Screenshot) -->
            <button id="btnRecenter" type="button" title="Pusatkan ke posisi kapal (Re-center)"
                    class="absolute bottom-6 right-6 z-[900] flex h-13 w-13 items-center justify-center rounded-full bg-white text-slate-700 shadow-2xl ring-1 ring-slate-200/80 transition-all hover:bg-slate-50 hover:scale-105 active:scale-95 focus:outline-none">
                <svg class="h-7 w-7 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <circle cx="12" cy="12" r="7" stroke="currentColor" />
                    <circle cx="12" cy="12" r="3" fill="currentColor" />
                    <line x1="12" y1="2" x2="12" y2="5" stroke="currentColor" stroke-linecap="round" />
                    <line x1="12" y1="19" x2="12" y2="22" stroke="currentColor" stroke-linecap="round" />
                    <line x1="2" y1="12" x2="5" y2="12" stroke="currentColor" stroke-linecap="round" />
                    <line x1="19" y1="12" x2="22" y2="12" stroke="currentColor" stroke-linecap="round" />
                </svg>
            </button>

            <!-- Map Legend in Bottom-Left -->
            <div class="pointer-events-none absolute bottom-4 left-4 z-[900] hidden rounded-xl bg-white/90 p-2.5 shadow-md backdrop-blur-sm sm:block">
                <div class="flex items-center gap-4 text-xs font-medium text-slate-700">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-5 rounded-full bg-slate-400"></span>
                        <span>Rute Terlewati</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span id="legendRemainingColor" class="h-2.5 w-6 rounded-full bg-[#00a884] transition-colors"></span>
                        <span id="legendRemainingText" class="font-bold text-[#00a884] transition-colors">Rute Menuju Tujuan</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Two-Column Section: Tracking Events History & Mayora Cargo Manifest -->
    <div class="grid gap-6 lg:grid-cols-12">
        <!-- Left: Tracking History Timeline -->
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7 lg:col-span-7">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Kronologi Perjalanan (Milestones)</h3>
                    <p class="text-xs text-slate-500">Catatan milestone logistik dari pabrik Jayanti Tangerang hingga pelabuhan tujuan.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ $shipment->trackingEvents->count() }} Recorded Events
                </span>
            </div>

            <div class="mt-6 space-y-6">
                @forelse($shipment->trackingEvents as $event)
                    <article class="relative flex gap-4 pl-1">
                        <!-- Vertical line -->
                        <div class="flex flex-col items-center">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full border-2 border-white shadow-sm {{ $event->is_exception ? 'bg-rose-500 text-white ring-4 ring-rose-100' : ($loop->last ? 'bg-[#00a884] text-white ring-4 ring-teal-100' : 'bg-cyan-600 text-white ring-2 ring-cyan-100') }}">
                                @if($event->is_exception)
                                    !
                                @elseif($loop->last)
                                    ✓
                                @else
                                    •
                                @endif
                            </span>
                            @if(! $loop->last)
                                <div class="w-0.5 grow bg-slate-200"></div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 pb-6 last:pb-0">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="font-bold text-slate-900">
                                    {{ str_replace('_', ' ', $event->event_type) }}
                                </h4>
                                <time class="rounded bg-slate-100 px-2 py-0.5 text-xs font-mono font-medium text-slate-600">
                                    {{ $event->event_time?->format('d M Y, H:i') }}
                                </time>
                            </div>

                            <p class="mt-1 text-sm text-slate-700 leading-relaxed">{{ $event->description }}</p>

                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                <span class="font-semibold text-slate-600">📍 {{ $event->location_name ?? $event->location?->name ?? 'Maritime Location' }}</span>
                                @if($event->latitude && $event->longitude)
                                    <button type="button"
                                            onclick="focusWaypoint({{ (float)$event->latitude }}, {{ (float)$event->longitude }}, '{{ addslashes($event->location_name ?? $event->event_type) }}')"
                                            class="inline-flex items-center gap-1 font-semibold text-teal-700 hover:text-teal-900 hover:underline">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat Titik di Peta
                                    </button>
                                @endif
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                    Status: {{ str_replace('_', ' ', $event->status) }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Belum ada riwayat tracking event yang tercatat untuk shipment ini.
                    </p>
                @endforelse
            </div>
        </section>

        <!-- Right: Mayora Export Cargo Manifest & Buyer Details -->
        <div class="space-y-6 lg:col-span-5">
            <!-- Mayora Product Manifest Card -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900">Manifes Produk Ekspor Mayora</h3>
                    <span class="rounded bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-900">FCL / Dry Cargo</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($shipment->items as $item)
                        @php
                            $productIcons = [
                                'Kopiko' => '🍬',
                                'Danisa' => '🍪',
                                'Torabika' => '☕',
                                'Beng' => '🍫',
                            ];
                            $icon = '📦';
                            foreach ($productIcons as $needle => $emoji) {
                                if (stripos($item->product_name, $needle) !== false) {
                                    $icon = $emoji;
                                    break;
                                }
                            }
                        @endphp
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-3.5">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">{{ $icon }}</span>
                                <div class="flex-1">
                                    <h4 class="font-bold text-slate-900 text-sm">{{ $item->product_name }}</h4>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-slate-500">
                                        <span>HS Code: <strong class="font-mono text-slate-700">{{ $item->hs_code ?? '1905.31.00' }}</strong></span>
                                        <span>•</span>
                                        <span>Jumlah: <strong class="text-slate-800">{{ number_format($item->packages_count) }} {{ $item->package_type ?? 'Cartons' }}</strong></span>
                                    </div>
                                    <div class="mt-1.5 flex justify-between text-xs text-slate-600 border-t border-slate-200/60 pt-1.5">
                                        <span>Berat Kotor: <strong>{{ number_format($item->gross_weight_kg, 1) }} kg</strong></span>
                                        <span>Volume: <strong>{{ number_format($item->volume_cbm ?? 0, 1) }} CBM</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">
                            Produk utama: Kopiko Coffee Candy 100g Pouch / Danisa Butter Cookies 454g
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 rounded-xl bg-slate-100/70 p-3 text-xs text-slate-600">
                    <p class="font-semibold text-slate-800">Petunjuk Khusus & Karantina:</p>
                    <p class="mt-1 text-slate-500 leading-relaxed">{{ $shipment->special_instructions ?? 'Handle with Care. Keep Dry & Ventilated. EU & FDA Food Safety Regulations Enclosed.' }}</p>
                </div>
            </section>

            <!-- Buyer & Consignee Information -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-3">Penerima Global (Consignee)</h3>

                <div class="mt-3 space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Perusahaan Pembeli</span>
                        <span class="font-bold text-slate-900">{{ $shipment->customer?->company_name ?? $shipment->customer?->name ?? 'Global Buyer' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Kontak Person</span>
                        <span class="font-semibold text-slate-800">{{ $shipment->customer?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Email Verifikasi</span>
                        <span class="font-mono text-cyan-800">{{ $shipment->customer?->email ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Operator Pelayaran</span>
                        <span class="font-semibold text-slate-800">{{ $shipment->carrier?->name }} ({{ $shipment->carrier?->scac }})</span>
                    </div>
                </div>

                @if($shipment->carrier?->tracking_url_template)
                    @php
                        $carrierUrl = str_replace(
                            ['{tracking_number}', '{container_number}'],
                            [$shipment->bill_of_lading_number ?? $shipment->container_number, $shipment->container_number],
                            $shipment->carrier->tracking_url_template
                        );
                    @endphp
                    <a href="{{ $carrierUrl }}" target="_blank" rel="noopener noreferrer"
                       class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-cyan-800">
                        <span>Buka Tracking Resmi {{ $shipment->carrier->name }}</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        // Server Telemetry Payload
        const telemetryData = @json($telemetry);
        const shipmentData = @json($shipment);

        const currentPos = [
            Number(telemetryData.current_position.latitude),
            Number(telemetryData.current_position.longitude)
        ];
        const originPos = [
            Number(telemetryData.origin.latitude),
            Number(telemetryData.origin.longitude)
        ];
        const destPos = [
            Number(telemetryData.destination.latitude),
            Number(telemetryData.destination.longitude)
        ];

        const destinationName = telemetryData.destination.name || telemetryData.destination.city || 'Tujuan Akhir';
        const destinationCountry = telemetryData.destination.country_code ? `, ${telemetryData.destination.country_code}` : '';
        const destinationFullLabel = destinationName + destinationCountry;

        // Initialize Leaflet Map
        const map = L.map('liveTrackingMap', {
            zoomControl: false // custom position
        }).setView(currentPos, 7);

        // Zoom control on top-left
        L.control.zoom({ position: 'topleft' }).addTo(map);

        // Layer 1: Esri World Street Map (Clean, high-detail pastel colors, no watermark!)
        const streetLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom',
            maxZoom: 18
        }).addTo(map);

        // Layer 2: OpenStreetMap Standard
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        });

        // Layer 3: CartoDB Voyager
        const voyagerLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO &copy; OpenStreetMap',
            subdomains: 'abcd',
            maxZoom: 19
        });

        // Layer 4: Esri Satellite
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS',
            maxZoom: 18
        });

        // Layer control
        L.control.layers({
            'Street Map (Clean)': streetLayer,
            'OpenStreetMap': osmLayer,
            'Voyager (Carto)': voyagerLayer,
            'Satellite Hybrid': satelliteLayer
        }, null, { position: 'topleft' }).addTo(map);

        // 1. Two-Tone Polylines (Matching user reference)
        // Traversed Route (Grey Line)
        const traversedCoords = telemetryData.route.traversed_points.map(p => [Number(p[0]), Number(p[1])]);
        let traversedPolyline = null;
        if (traversedCoords.length >= 2) {
            traversedPolyline = L.polyline(traversedCoords, {
                color: '#94a3b8', // slate 400
                weight: 4.5,
                opacity: 0.9,
                lineCap: 'round',
                lineJoin: 'round',
                dashArray: null
            }).addTo(map);
        }

        // Remaining Route (Bold Vivid Teal Line #00a884 matching screenshot!)
        const remainingCoords = telemetryData.route.remaining_points.map(p => [Number(p[0]), Number(p[1])]);
        let remainingPolyline = null;
        if (remainingCoords.length >= 2) {
            remainingPolyline = L.polyline(remainingCoords, {
                color: '#00a884', // bright cyan / teal matching screenshot!
                weight: 5.5,
                opacity: 0.95,
                lineCap: 'round',
                lineJoin: 'round'
            }).addTo(map);
        }

        // 2. Multi-Modal 3D SVG & Pulse Generator (Uniform Vibe across SEA, AIR, ROAD)
        function buildMarkerHtml(mode) {
            if (mode === 'AIR') {
                return `
                    <div class="relative cursor-pointer select-none">
                        <!-- Air Altitude Radar Pulse (Sky Blue #0284c7) -->
                        <div class="plane-pulse"></div>
                        <div class="plane-pulse-inner"></div>

                        <!-- 3D Cargo Aircraft (Boeing 747/777 Freighter style) -->
                        <svg class="h-10 w-10 filter drop-shadow-lg transition-transform hover:scale-115" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Vapor Wake / Cloud Shadow -->
                            <ellipse cx="32" cy="54" rx="24" ry="5" fill="#38bdf8" fill-opacity="0.35"/>
                            <ellipse cx="32" cy="53" rx="16" ry="3" fill="#0284c7" fill-opacity="0.25"/>

                            <!-- Jet Turbines Under Wings -->
                            <rect x="17" y="34" width="6" height="11" rx="3" fill="#475569"/>
                            <rect x="41" y="34" width="6" height="11" rx="3" fill="#475569"/>
                            <circle cx="20" cy="36" r="2.2" fill="#0284c7"/>
                            <circle cx="44" cy="36" r="2.2" fill="#0284c7"/>

                            <!-- Main Swept Wings -->
                            <path d="M32 23 L6 41 L6 45 L30 37 L32 37 L34 37 L58 45 L58 41 Z" fill="#f8fafc" stroke="#94a3b8" stroke-width="1"/>
                            <!-- Winglets Sky Blue -->
                            <path d="M6 41 L6 45 L9 43 Z" fill="#0284c7"/>
                            <path d="M58 41 L58 45 L55 43 Z" fill="#0284c7"/>

                            <!-- Horizontal Tail Stabilizers -->
                            <path d="M22 51 L32 45 L42 51 L32 49 Z" fill="#cbd5e1"/>

                            <!-- Fuselage Body (Aerodynamic White) -->
                            <path d="M32 7 C27 13 26 36 27 51 C27 53 37 53 37 51 C38 36 37 13 32 7 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.2"/>

                            <!-- Cockpit Windshield (Deep Sapphire Blue) -->
                            <path d="M29 13 C30 11 34 11 35 13 L34 16 L30 16 Z" fill="#0369a1"/>

                            <!-- Cargo Livery Stripe (Mayora Cyan / Blue) -->
                            <path d="M29.5 20 L34.5 20 L34.5 44 L29.5 44 Z" fill="#0284c7" fill-opacity="0.85"/>

                            <!-- Vertical Tail Fin (Rudder) -->
                            <path d="M31 43 L33 43 L34.5 53 L29.5 53 Z" fill="#0284c7"/>
                        </svg>
                    </div>
                `;
            } else if (mode === 'ROAD') {
                return `
                    <div class="relative cursor-pointer select-none">
                        <!-- Highway GPS Radar Pulse (Emerald Green #059669) -->
                        <div class="truck-pulse"></div>
                        <div class="truck-pulse-inner"></div>

                        <!-- 3D Heavy-Duty Container Truck / Wingbox -->
                        <svg class="h-10 w-10 filter drop-shadow-lg transition-transform hover:scale-115" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Asphalt Ground Shadow -->
                            <ellipse cx="32" cy="54" rx="26" ry="6" fill="#1e293b" fill-opacity="0.35"/>

                            <!-- Heavy Duty Wheels & Silver Rims -->
                            <rect x="11" y="46" width="7" height="7" rx="3.5" fill="#0f172a"/>
                            <circle cx="14.5" cy="49.5" r="1.8" fill="#94a3b8"/>
                            <rect x="23" y="46" width="7" height="7" rx="3.5" fill="#0f172a"/>
                            <circle cx="26.5" cy="49.5" r="1.8" fill="#94a3b8"/>
                            <rect x="41" y="46" width="7" height="7" rx="3.5" fill="#0f172a"/>
                            <circle cx="44.5" cy="49.5" r="1.8" fill="#94a3b8"/>
                            <rect x="50" y="46" width="7" height="7" rx="3.5" fill="#0f172a"/>
                            <circle cx="53.5" cy="49.5" r="1.8" fill="#94a3b8"/>

                            <!-- Steel Chassis Frame -->
                            <rect x="9" y="43" width="50" height="4" rx="1" fill="#475569"/>

                            <!-- 40ft High-Cube Cargo Container (Mayora Emerald Green #059669) -->
                            <rect x="20" y="18" width="39" height="25" rx="2" fill="#059669" stroke="#047857" stroke-width="1.2"/>
                            <!-- Corrugated Box Ribs -->
                            <line x1="26" y1="20" x2="26" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <line x1="32" y1="20" x2="32" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <line x1="38" y1="20" x2="38" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <line x1="44" y1="20" x2="44" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <line x1="50" y1="20" x2="50" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <!-- Container Top Highlight Stripe -->
                            <rect x="20" y="18" width="39" height="3" fill="#10b981"/>

                            <!-- Truck Cabin (Aerodynamic Heavy Hauler) -->
                            <path d="M6 43 L6 31 C6 28 8 25 11 25 L19 25 L19 43 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.2"/>
                            <!-- Panoramic Windshield (Sky Blue Glass) -->
                            <path d="M7 31 C7 28 9 27 12 27 L15 27 L15 35 L7 35 Z" fill="#0284c7"/>
                            <rect x="16" y="27" width="2.5" height="8" fill="#0369a1"/>
                            <!-- Golden Amber Headlights -->
                            <rect x="5.5" y="37" width="3.5" height="3" rx="0.8" fill="#f59e0b"/>
                            <!-- Front Bumper -->
                            <rect x="5" y="41" width="4" height="4" rx="1" fill="#64748b"/>
                        </svg>
                    </div>
                `;
            }

            // Default: SEA (Kapal Laut)
            return `
                <div class="relative cursor-pointer select-none">
                    <!-- Sonar radar pulse waves (Cyan / Teal) -->
                    <div class="vessel-pulse"></div>
                    <div class="vessel-pulse-inner"></div>

                    <!-- 3D Ship Icon -->
                    <svg class="h-10 w-10 filter drop-shadow-lg transition-transform hover:scale-115" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="32" cy="54" rx="26" ry="7" fill="#38bdf8" fill-opacity="0.35"/>
                        <ellipse cx="32" cy="53" rx="20" ry="4" fill="#0284c7" fill-opacity="0.25"/>

                        <path d="M8 38 L16 50 C24 53 40 53 48 50 L56 38 C56 38 46 41 32 41 C18 41 8 38 8 38 Z" fill="#ef4444"/>
                        <path d="M12 44 C20 48 44 48 52 44 L50 47 C42 50 22 50 14 47 Z" fill="#b91c1c"/>
                        <path d="M10 40 C22 43 42 43 54 40 L53 42 C41 45 23 45 11 42 Z" fill="#06b6d4"/>

                        <path d="M12 36 L15 39 C24 41 40 41 49 39 L52 36 C52 36 43 38 32 38 C21 38 12 36 12 36 Z" fill="#f8fafc"/>

                        <rect x="20" y="27" width="24" height="9" rx="2" fill="#ffffff" stroke="#cbd5e1" stroke-width="1"/>
                        <rect x="23" y="29.5" width="3" height="3" rx="0.5" fill="#0284c7"/>
                        <rect x="28" y="29.5" width="3" height="3" rx="0.5" fill="#0284c7"/>
                        <rect x="33" y="29.5" width="3" height="3" rx="0.5" fill="#0284c7"/>
                        <rect x="38" y="29.5" width="3" height="3" rx="0.5" fill="#0284c7"/>

                        <rect x="24" y="20" width="16" height="7" rx="1.5" fill="#ffffff" stroke="#cbd5e1" stroke-width="1"/>
                        <rect x="27" y="22" width="3" height="2.5" rx="0.5" fill="#0369a1"/>
                        <rect x="34" y="22" width="3" height="2.5" rx="0.5" fill="#0369a1"/>

                        <rect x="29" y="13" width="6" height="7" rx="1" fill="#dc2626"/>
                        <rect x="28.5" y="12" width="7" height="2" rx="0.5" fill="#1e293b"/>

                        <line x1="32" y1="12" x2="32" y2="7" stroke="#64748b" stroke-width="1.5"/>
                        <circle cx="32" cy="6.5" r="1.5" fill="#ef4444"/>
                    </svg>
                </div>
            `;
        }

        // Multi-Modal Configurations Dictionary
        const modeConfigs = {
            SEA: {
                label: 'Laut (Kapal Laut)',
                icon: '🚢',
                prefix: 'Dalam perjalanan ke',
                color: '#00a884',
                vehicle: shipmentData.vessel_name || 'Maersk Mc-Kinney Moller',
                speed: telemetryData.speed_formatted || '18.5 Kts (34.3 km/h)',
                badgeBg: 'bg-cyan-50 text-cyan-700 ring-cyan-300'
            },
            AIR: {
                label: 'Udara (Pesawat Cargo)',
                icon: '✈️',
                prefix: 'Dalam penerbangan ke',
                color: '#0284c7',
                vehicle: shipmentData.vehicle_identifier || 'Boeing 747-400F Cargo Express',
                speed: '840 km/h (Mach 0.78)',
                badgeBg: 'bg-sky-50 text-sky-700 ring-sky-300'
            },
            ROAD: {
                label: 'Darat (Mobil Truk)',
                icon: '🚛',
                prefix: 'Dalam perjalanan darat ke',
                color: '#059669',
                vehicle: shipmentData.vehicle_identifier || 'Truk Wingbox Volvo FMX 440',
                speed: '68 km/h',
                badgeBg: 'bg-emerald-50 text-emerald-700 ring-emerald-300'
            }
        };

        let currentActiveMode = telemetryData.transport_mode || 'SEA';

        function createModeMarkerIcon(mode) {
            return L.divIcon({
                html: buildMarkerHtml(mode),
                className: `custom-${mode.toLowerCase()}-marker`,
                iconSize: [40, 40],
                iconAnchor: [20, 20],
                popupAnchor: [0, -22]
            });
        }

        const vesselMarker = L.marker(currentPos, {
            icon: createModeMarkerIcon(currentActiveMode),
            zIndexOffset: 1000
        }).addTo(map);

        // 3. Permanent Floating Callout Badge
        function getCalloutHtml(mode) {
            const cfg = modeConfigs[mode] || modeConfigs.SEA;
            return `
                <div class="custom-vessel-callout" onclick="map.flyTo([${currentPos[0]}, ${currentPos[1]}], 8, { duration: 1.2 })">
                    <div class="callout-sub">
                        <span>${cfg.prefix}</span>
                    </div>
                    <div class="callout-dest" style="color: ${cfg.color}">${destinationFullLabel}</div>
                    <div class="callout-arrow"></div>
                </div>
            `;
        }

        vesselMarker.bindPopup(getCalloutHtml(currentActiveMode), {
            className: 'vessel-callout-wrapper',
            closeButton: false,
            autoClose: false,
            closeOnClick: false,
            offset: [0, -10]
        }).openPopup();

        // Mode Switching Function for Live Testing & Seamless Simulation
        window.switchTransportMode = function(mode) {
            if (!modeConfigs[mode]) return;
            currentActiveMode = mode;
            const cfg = modeConfigs[mode];

            // 1. Update button states
            ['SEA', 'AIR', 'ROAD'].forEach(m => {
                const btn = document.getElementById(`btnMode${m}`);
                if (!btn) return;
                if (m === mode) {
                    btn.className = 'mode-switch-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold shadow-sm transition bg-navy-900 text-cyan-300 ring-1 ring-cyan-400';
                } else {
                    btn.className = 'mode-switch-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold transition hover:bg-slate-100 text-slate-600';
                }
            });

            // 2. Change marker icon & radar pulse animation
            vesselMarker.setIcon(createModeMarkerIcon(mode));

            // 3. Update callout popup
            vesselMarker.setPopupContent(getCalloutHtml(mode));
            if (!vesselMarker.isPopupOpen()) {
                vesselMarker.openPopup();
            }

            // 4. Update polyline color
            if (remainingPolyline) {
                remainingPolyline.setStyle({ color: cfg.color });
            }

            // 5. Update Map Legend
            const legColor = document.getElementById('legendRemainingColor');
            const legText = document.getElementById('legendRemainingText');
            if (legColor) legColor.style.backgroundColor = cfg.color;
            if (legText) {
                legText.style.color = cfg.color;
                legText.textContent = `Rute ${cfg.label}`;
            }

            // 6. Update Top Header Badges & Info
            const headerIcon = document.getElementById('headerModeIcon');
            const headerLabel = document.getElementById('headerModeLabel');
            const headerVehicle = document.getElementById('headerVehicleName');
            const speedBadge = document.getElementById('telemetrySpeedBadge');
            const headerBadge = document.getElementById('headerModeBadge');

            if (headerIcon) headerIcon.textContent = cfg.icon;
            if (headerLabel) headerLabel.textContent = cfg.label;
            if (headerVehicle) headerVehicle.innerHTML = `${cfg.icon} ${cfg.vehicle}`;
            if (speedBadge) speedBadge.textContent = cfg.speed;
            if (headerBadge) {
                headerBadge.className = `inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-xs font-bold shadow-xs ring-1 ring-inset ${cfg.badgeBg}`;
            }

            // 7. Update 3D Globe if initialized
            if (typeof updateDetailGlobeData === 'function') {
                updateDetailGlobeData(mode);
            }
        };

        // Initialize button highlight matching current mode
        window.switchTransportMode(currentActiveMode);

        // Keep popup open when map moves
        map.on('zoomend moveend', () => {
            if (!vesselMarker.isPopupOpen()) {
                vesselMarker.openPopup();
            }
        });

        // 4. Origin & Destination Markers
        const originIcon = L.divIcon({
            html: `<div class="port-pin bg-emerald-600 text-white w-8 h-8">🏭</div>`,
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        const originMarker = L.marker(originPos, { icon: originIcon }).addTo(map)
            .bindPopup(`<strong>Origin:</strong> ${telemetryData.origin.name}<br><small>${telemetryData.origin.city}, ${telemetryData.origin.country_code}</small>`);

        const destIcon = L.divIcon({
            html: `<div class="port-pin bg-rose-600 text-white w-8 h-8">⚓</div>`,
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        const destMarker = L.marker(destPos, { icon: destIcon }).addTo(map)
            .bindPopup(`<strong>Destination:</strong> ${telemetryData.destination.name}<br><small>${telemetryData.destination.city}, ${telemetryData.destination.country_code}</small>`);

        // Intermediate Waypoints Markers
        telemetryData.waypoints.forEach(wp => {
            if (wp.latitude && wp.longitude) {
                const isException = wp.is_exception;
                const dotIcon = L.divIcon({
                    html: `<span class="block w-3 h-3 rounded-full border-2 border-white shadow ${isException ? 'bg-rose-500' : 'bg-cyan-600'}"></span>`,
                    className: '',
                    iconSize: [12, 12],
                    iconAnchor: [6, 6]
                });
                L.marker([wp.latitude, wp.longitude], { icon: dotIcon }).addTo(map)
                    .bindPopup(`<strong>${wp.event_type.replaceAll('_', ' ')}</strong><br><small>${wp.event_time}</small><br>${wp.location_name}<br><p class="text-xs text-slate-500 mt-1">${wp.description}</p>`);
            }
        });

        // Fit map bounds to view all points comfortably
        const allPoints = [originPos, currentPos, destPos];
        const bounds = L.latLngBounds(allPoints);
        map.fitBounds(bounds, { padding: [60, 60], maxZoom: 7 });

        // 5. GPS Re-Center FAB Button (Pusatkan ke posisi kapal)
        document.getElementById('btnRecenter').addEventListener('click', () => {
            map.flyTo(currentPos, Math.max(map.getZoom(), 7), {
                animate: true,
                duration: 1.2
            });
            setTimeout(() => {
                vesselMarker.openPopup();
            }, 600);
        });

        // 6. Interactive Waypoint Focus from timeline
        window.focusWaypoint = function(lat, lng, label) {
            map.flyTo([lat, lng], 9, {
                animate: true,
                duration: 1.0
            });
            L.popup()
                .setLatLng([lat, lng])
                .setContent(`<div class="p-1 font-bold text-slate-900">${label}</div>`)
                .openOn(map);
        };

        // 7. Dynamic API Refresh Handler ("sertakan maps dengan api")
        const syncButton = document.getElementById('btnManualSync');
        const syncIcon = document.getElementById('syncIcon');
        const syncText = document.getElementById('syncText');
        const syncTimestamp = document.getElementById('syncTimestamp');

        syncButton.addEventListener('click', async () => {
            syncIcon.classList.add('animate-spin');
            syncText.textContent = 'Syncing API...';
            syncButton.disabled = true;

            try {
                const response = await fetch("{{ route('tracking.data', $shipment) }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Update weather card
                    if (data.weather) {
                        document.getElementById('weatherCondition').innerHTML = `${data.weather.condition_icon || '⛅'} ${data.weather.condition_text || 'Fair'}`;
                        document.getElementById('weatherTemp').textContent = `${data.weather.temperature} °C`;
                        document.getElementById('weatherWind').innerHTML = `${data.weather.wind_speed_knots} kts <span class="text-[10px] font-normal text-slate-500">(${data.weather.wind_speed_kmh} km/h)</span>`;
                        document.getElementById('weatherSea').textContent = data.weather.sea_state;
                    }

                    // Update timestamp
                    syncTimestamp.textContent = data.synced_at || new Date().toLocaleTimeString();

                    // Animate marker slightly to indicate live ping
                    vesselMarker.setLatLng([
                        data.telemetry.current_position.latitude,
                        data.telemetry.current_position.longitude
                    ]);

                    syncText.textContent = 'API Synced!';
                    setTimeout(() => {
                        syncText.textContent = 'Sync API';
                    }, 2000);
                } else {
                    syncText.textContent = 'Sync Error';
                }
            } catch (err) {
                console.error('API Sync failed:', err);
                syncText.textContent = 'Offline';
            } finally {
                syncIcon.classList.remove('animate-spin');
                syncButton.disabled = false;
            }
        });

        // 8. 3D Globe Implementation for Tracking Detail (Globe.gl)
        let detailViewMode = '2D';
        let detailGlobeInstance = null;
        let isDetailGlobeAutoRotating = true;

        const detailGlobeContainer = document.getElementById('liveTrackingGlobeContainer');
        const detailMap2DContainer = document.getElementById('liveTrackingMap');
        const recenterDetailBtn = document.getElementById('btnRecenter');

        window.switchDetailMapView = function(mode) {
            detailViewMode = mode;
            const btn2D = document.getElementById('btnDetailMap2D');
            const btn3D = document.getElementById('btnDetailMap3D');

            if (mode === '3D') {
                btn3D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-bold bg-navy-900 text-cyan-300 shadow-xs transition';
                btn2D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-semibold text-slate-600 hover:text-slate-900 transition';
                detailMap2DContainer.classList.add('hidden');
                if (recenterDetailBtn) recenterDetailBtn.classList.add('hidden');
                detailGlobeContainer.classList.remove('hidden');

                if (!detailGlobeInstance) {
                    initDetailGlobe3D();
                } else {
                    detailGlobeInstance.resumeAnimation();
                    resizeDetailGlobe();
                }
            } else {
                btn2D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-bold bg-navy-900 text-cyan-300 shadow-xs transition';
                btn3D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-semibold text-slate-600 hover:text-slate-900 transition';
                detailGlobeContainer.classList.add('hidden');
                detailMap2DContainer.classList.remove('hidden');
                if (recenterDetailBtn) recenterDetailBtn.classList.remove('hidden');

                if (detailGlobeInstance) {
                    detailGlobeInstance.pauseAnimation();
                }
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
            }
        };

        function resizeDetailGlobe() {
            if (!detailGlobeInstance || !detailGlobeContainer) return;
            const width = detailGlobeContainer.clientWidth || 800;
            const height = detailGlobeContainer.clientHeight || 540;
            detailGlobeInstance.width(width).height(height);
        }

        function initDetailGlobe3D() {
            if (typeof Globe === 'undefined') {
                console.error('Globe.gl not loaded');
                return;
            }

            const width = detailGlobeContainer.clientWidth || 800;
            const height = detailGlobeContainer.clientHeight || 540;

            const blueMarbleUrl = "{{ asset('vendor/globe/earth-blue-marble.jpg') }}";
            const nightSkyUrl = "{{ asset('vendor/globe/night-sky.png') }}";
            const bumpUrl = "{{ asset('vendor/globe/earth-night.jpg') }}";

            detailGlobeInstance = Globe()(detailGlobeContainer)
                .globeImageUrl(blueMarbleUrl)
                .backgroundImageUrl(nightSkyUrl)
                .bumpImageUrl(bumpUrl)
                .showAtmosphere(true)
                .atmosphereColor('#38bdf8')
                .atmosphereAltitude(0.22)
                .width(width)
                .height(height);

            // Center on midpoint between origin and destination
            const midLat = (originPos[0] + destPos[0]) / 2;
            const midLng = (originPos[1] + destPos[1]) / 2;
            detailGlobeInstance.pointOfView({ lat: midLat, lng: midLng, altitude: 1.8 }, 0);

            detailGlobeInstance.controls().autoRotate = true;
            detailGlobeInstance.controls().autoRotateSpeed = 0.6;
            detailGlobeInstance.controls().enableDamping = true;
            detailGlobeInstance.controls().dampingFactor = 0.08;

            detailGlobeContainer.addEventListener('pointerdown', () => {
                if (isDetailGlobeAutoRotating && detailGlobeInstance) {
                    detailGlobeInstance.controls().autoRotate = false;
                }
            });

            detailGlobeContainer.addEventListener('pointerup', () => {
                if (isDetailGlobeAutoRotating && detailGlobeInstance) {
                    setTimeout(() => {
                        if (isDetailGlobeAutoRotating && detailGlobeInstance) {
                            detailGlobeInstance.controls().autoRotate = true;
                        }
                    }, 2500);
                }
            });

            updateDetailGlobeData(activeMode);
            window.addEventListener('resize', resizeDetailGlobe);
        }

        window.toggleDetailGlobeAutoRotate = function() {
            if (!detailGlobeInstance) return;
            isDetailGlobeAutoRotating = !isDetailGlobeAutoRotating;
            detailGlobeInstance.controls().autoRotate = isDetailGlobeAutoRotating;

            const icon = document.getElementById('detailGlobeAutoRotateIcon');
            const btn = document.getElementById('btnDetailGlobeAutoRotate');
            if (isDetailGlobeAutoRotating) {
                if (icon) icon.className = 'inline-block transition-transform duration-500 animate-spin';
                if (btn) btn.className = 'flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/85 px-3 py-1.5 text-xs font-bold text-cyan-300 shadow-lg backdrop-blur-md transition hover:bg-navy-900';
            } else {
                if (icon) icon.className = 'inline-block transition-transform duration-500';
                if (btn) btn.className = 'flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/50 px-3 py-1.5 text-xs font-bold text-slate-400 shadow-lg backdrop-blur-md transition hover:bg-navy-900 hover:text-white';
            }
        };

        window.resetDetailGlobeView = function() {
            if (!detailGlobeInstance) return;
            detailGlobeInstance.pointOfView({ lat: currentPos[0], lng: currentPos[1], altitude: 1.6 }, 1000);
        };

        function updateDetailGlobeData(mode) {
            if (!detailGlobeInstance) return;

            const arcColors = mode === 'AIR'
                ? ['#38bdf8', '#0284c7']
                : (mode === 'ROAD' ? ['#34d399', '#059669'] : ['#2dd4bf', '#00a884']);

            // 1. Great-Circle 3D Route Arc
            const arcs = [
                {
                    startLat: originPos[0],
                    startLng: originPos[1],
                    endLat: destPos[0],
                    endLng: destPos[1],
                    color: arcColors,
                    altitude: mode === 'AIR' ? 0.35 : (mode === 'ROAD' ? 0.12 : 0.22),
                    stroke: mode === 'AIR' ? 2.5 : 2.0
                }
            ];

            detailGlobeInstance
                .arcsData(arcs)
                .arcColor('color')
                .arcAltitude('altitude')
                .arcStroke('stroke')
                .arcDashLength(0.4)
                .arcDashGap(0.2)
                .arcDashAnimateTime(2000)
                .arcLabel(() => `
                    <div style="background: rgba(7, 26, 45, 0.95); border: 1px solid rgba(56, 189, 248, 0.4); padding: 10px 14px; border-radius: 12px; color: #fff; font-family: 'DM Sans', sans-serif; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                        <div style="font-size: 11px; font-weight: 800; color: #38bdf8; text-transform: uppercase;">{{ $shipment->shipment_number }}</div>
                        <div style="font-size: 13px; font-weight: 700; margin-top: 2px;">📍 {{ $shipment->origin?->city ?? 'Origin' }} ➔ {{ $shipment->destination?->city ?? 'Destination' }}</div>
                        <div style="font-size: 11px; color: #2dd4bf; margin-top: 4px;">{{ $shipment->transport_mode_icon }} {{ $shipment->vehicle_identifier ?? $shipment->vessel_name }}</div>
                    </div>
                `);

            // 2. Sonar Rings at Origin and Destination
            const rings = [
                { lat: originPos[0], lng: originPos[1], maxR: 4.5, propSpeed: 2, repeatPeriod: 1200, color: '#10b981' },
                { lat: destPos[0], lng: destPos[1], maxR: 3.5, propSpeed: 1.5, repeatPeriod: 1500, color: '#00a884' }
            ];

            detailGlobeInstance
                .ringsData(rings)
                .ringColor(d => d.color)
                .ringMaxRadius(d => d.maxR)
                .ringPropagationSpeed(d => d.propSpeed)
                .ringRepeatPeriod(d => d.repeatPeriod);

            // 3. HTML Markers (Origin Port, Live Vehicle, Destination Port)
            const vehicleIcon = mode === 'AIR' ? '✈️' : (mode === 'ROAD' ? '🚛' : '🚢');
            const markers = [
                {
                    lat: originPos[0],
                    lng: originPos[1],
                    type: 'origin',
                    name: 'Origin: {{ $shipment->origin?->name ?? 'Tanjung Priok' }}'
                },
                {
                    lat: currentPos[0],
                    lng: currentPos[1],
                    type: 'vehicle',
                    name: '{{ $shipment->shipment_number }}',
                    icon: vehicleIcon
                },
                {
                    lat: destPos[0],
                    lng: destPos[1],
                    type: 'destination',
                    name: 'Destination: ' + destinationFullLabel
                }
            ];

            detailGlobeInstance
                .htmlElementsData(markers)
                .htmlElement(d => {
                    const el = document.createElement('div');
                    if (d.type === 'vehicle') {
                        const bg = mode === 'AIR' ? 'rgba(2, 132, 199, 0.95)' : (mode === 'ROAD' ? 'rgba(5, 150, 105, 0.95)' : 'rgba(0, 168, 132, 0.95)');
                        el.innerHTML = `
                            <div style="cursor: pointer; text-align: center; transform: translate(-50%, -100%);">
                                <div style="display: inline-flex; align-items: center; gap: 5px; background: ${bg}; border: 2px solid #fff; padding: 4px 10px; border-radius: 20px; color: #fff; font-size: 11px; font-weight: 800; box-shadow: 0 6px 20px rgba(0,0,0,0.5);">
                                    <span style="font-size: 15px;">${d.icon}</span>
                                    <span style="font-family: monospace;">${d.name}</span>
                                </div>
                            </div>
                        `;
                    } else if (d.type === 'origin') {
                        el.innerHTML = `
                            <div style="cursor: default; text-align: center; transform: translate(-50%, -100%);">
                                <div style="display: inline-flex; align-items: center; gap: 4px; background: rgba(6, 78, 59, 0.95); border: 1.5px solid #10b981; padding: 2px 8px; border-radius: 16px; color: #6ee7b7; font-size: 10px; font-weight: 800;">
                                    <span>🏭</span>
                                    <span>ORIGIN</span>
                                </div>
                            </div>
                        `;
                    } else {
                        el.innerHTML = `
                            <div style="cursor: default; text-align: center; transform: translate(-50%, -100%);">
                                <div style="display: inline-flex; align-items: center; gap: 4px; background: rgba(15, 23, 42, 0.95); border: 1.5px solid #38bdf8; padding: 2px 8px; border-radius: 16px; color: #7dd3fc; font-size: 10px; font-weight: 800;">
                                    <span>🏁</span>
                                    <span>DEST</span>
                                </div>
                            </div>
                        `;
                    }
                    return el;
                });
        }
    </script>
@endpush
