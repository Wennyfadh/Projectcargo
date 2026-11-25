@extends('layouts.app')

@section('title', __('messages.page_titles.dashboard'))
@section('page-title', __('messages.nav.dashboard'))

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="{{ asset('vendor/globe/globe.gl.min.js') }}"></script>
    <script>
        if (typeof Globe === 'undefined') {
            document.write('<script src="https://unpkg.com/globe.gl@2.46.2/dist/globe.gl.min.js"><\/script>');
        }
    </script>
    <style>
        .scene-container canvas {
            outline: none;
        }
        .globe-vehicle-marker {
            pointer-events: auto;
            user-select: none;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .globe-origin-marker {
            pointer-events: auto;
            user-select: none;
        }
        @keyframes vesselRadarPulse {
            0% { transform: scale(0.6); opacity: 0.9; }
            70% { transform: scale(2.2); opacity: 0.15; }
            100% { transform: scale(2.6); opacity: 0; }
        }
        .vessel-pulse {
            position: absolute;
            width: 44px;
            height: 44px;
            left: -4px;
            top: -4px;
            border-radius: 50%;
            background: rgba(6, 182, 212, 0.45);
            animation: vesselRadarPulse 2.4s ease-out infinite;
            pointer-events: none;
            z-index: -1;
        }

        /* Plane Radar Pulse */
        @keyframes planeRadarPulse {
            0% { transform: scale(0.6); opacity: 0.9; }
            70% { transform: scale(2.2); opacity: 0.15; }
            100% { transform: scale(2.6); opacity: 0; }
        }
        .plane-pulse {
            position: absolute;
            width: 44px;
            height: 44px;
            left: -4px;
            top: -4px;
            border-radius: 50%;
            background: rgba(2, 132, 199, 0.45);
            animation: planeRadarPulse 2.4s ease-out infinite;
            pointer-events: none;
            z-index: -1;
        }

        /* Truck Radar Pulse */
        @keyframes truckRadarPulse {
            0% { transform: scale(0.6); opacity: 0.9; }
            70% { transform: scale(2.2); opacity: 0.15; }
            100% { transform: scale(2.6); opacity: 0; }
        }
        .truck-pulse {
            position: absolute;
            width: 44px;
            height: 44px;
            left: -4px;
            top: -4px;
            border-radius: 50%;
            background: rgba(5, 150, 105, 0.45);
            animation: truckRadarPulse 2.4s ease-out infinite;
            pointer-events: none;
            z-index: -1;
        }
        .leaflet-popup-content-wrapper.dash-vessel-popup {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }
        .leaflet-popup-content.dash-vessel-content {
            margin: 0 !important;
            line-height: normal !important;
        }
        .leaflet-popup-tip-container.dash-vessel-tip {
            display: none !important;
        }
        .custom-dash-callout {
            background: #ffffff;
            border-radius: 16px;
            padding: 9px 15px 10px 15px;
            box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(226, 232, 240, 0.9);
            position: relative;
            white-space: nowrap;
            text-align: left;
            font-family: 'DM Sans', sans-serif;
            transform: translateY(-8px);
        }
        .custom-dash-callout .callout-sub {
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 2px;
        }
        .custom-dash-callout .callout-dest {
            font-size: 13px;
            font-weight: 700;
            color: #00a884;
        }
        .custom-dash-callout .callout-arrow {
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 8px solid #ffffff;
        }
    </style>
@endpush

@section('content')
<div class="space-y-5 p-5 md:p-8">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">{{ __('messages.dashboard.overview_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('messages.dashboard.overview_subtitle') }}</p>
        </div>
        <a href="{{ route('shipments.index') }}" class="rounded-lg bg-ocean-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-ocean-700">{{ __('messages.dashboard.view_shipments') }}</a>
    </div>

    <section class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        @foreach([
            [__('messages.dashboard.stats.total_shipments'), $dashboard['total_shipments'], '◇', 'bg-cyan-100 text-cyan-700'],
            [__('messages.dashboard.stats.in_transit'), $dashboard['in_transit'], '⇢', 'bg-sky-100 text-sky-700'],
            [__('messages.dashboard.stats.delivered'), $dashboard['delivered'], '✓', 'bg-emerald-100 text-emerald-700'],
            [__('messages.dashboard.stats.delayed'), $dashboard['delayed'], '◷', 'bg-amber-100 text-amber-700'],
            [__('messages.dashboard.stats.open_exceptions'), $dashboard['open_exceptions'], '△', 'bg-rose-100 text-rose-700']
        ] as [$label, $value, $icon, $color])
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-medium text-slate-500 md:text-sm">{{ $label }}</p>
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $color }}">{{ $icon }}</span>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900 md:text-3xl">{{ number_format($value) }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-12">
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-5">
            <div class="mb-4">
                <h3 class="font-bold text-slate-900">{{ __('messages.dashboard.charts.shipments_by_status') }}</h3>
                <p class="text-xs text-slate-500">{{ __('messages.dashboard.charts.shipments_by_status_sub') }}</p>
            </div>
            <div class="h-64"><canvas id="statusChart"></canvas></div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-4">
            <div class="mb-4">
                <h3 class="font-bold text-slate-900">{{ __('messages.dashboard.charts.carrier_distribution') }}</h3>
                <p class="text-xs text-slate-500">{{ __('messages.dashboard.charts.carrier_distribution_sub') }}</p>
            </div>
            <div class="h-64"><canvas id="carrierChart"></canvas></div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3">
            <div class="mb-4">
                <h3 class="font-bold text-slate-900">{{ __('messages.dashboard.performance.title') }}</h3>
                <p class="text-xs text-slate-500">{{ __('messages.dashboard.performance.sub') }}</p>
            </div>
            <div class="space-y-6 pt-2">
                <div>
                    <div class="mb-2 flex justify-between text-sm"><span class="text-slate-600">{{ __('messages.dashboard.performance.on_time_rate') }}</span><strong>{{ $dashboard['on_time_rate'] }}%</strong></div>
                    <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-emerald-500" style="width: {{ min($dashboard['on_time_rate'], 100) }}%"></div></div>
                </div>
                <div>
                    <div class="mb-2 flex justify-between text-sm"><span class="text-slate-600">{{ __('messages.dashboard.performance.exception_rate') }}</span><strong>{{ $dashboard['exception_rate'] }}%</strong></div>
                    <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-rose-500" style="width: {{ min($dashboard['exception_rate'], 100) }}%"></div></div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('messages.dashboard.performance.open_issues') }}</p>
                    <p class="mt-1 text-2xl font-bold text-rose-600">{{ $dashboard['open_exceptions'] }}</p>
                    <a href="{{ route('exceptions.index') }}" class="mt-2 inline-block text-xs font-semibold text-ocean-700 hover:underline">{{ __('messages.dashboard.performance.review_exceptions') }}</a>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-12">
        <article class="relative rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-8">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900">{{ __('messages.dashboard.map.title') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('messages.dashboard.map.sub') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- 2D vs 3D Globe Toggle Switcher -->
                    <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-100 p-1 text-xs shadow-inner">
                        <button type="button" id="btnDashMap2D" onclick="switchDashboardView('2D')"
                                title="{{ __('messages.dashboard.map.view_2d_title') }}"
                                class="map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-bold bg-white text-navy-950 shadow-xs transition">
                            <span>{{ __('messages.dashboard.map.view_2d') }}</span>
                        </button>
                        <button type="button" id="btnDashMap3D" onclick="switchDashboardView('3D')"
                                title="{{ __('messages.dashboard.map.view_3d_title') }}"
                                class="map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-semibold text-slate-600 hover:text-slate-900 transition">
                            <span>{{ __('messages.dashboard.map.view_3d') }}</span>
                            <span class="rounded bg-teal-500/20 px-1.5 py-0.5 text-[9px] font-black text-teal-700 uppercase">3D</span>
                        </button>
                    </div>

                    <!-- Filter Moda Bar -->
                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs">
                        <button type="button" onclick="filterDashboardModa('ALL')" id="dashModa-ALL"
                                class="dash-moda-btn rounded-lg px-2.5 py-1 font-bold bg-white text-navy-900 shadow-xs">
                            {{ __('messages.dashboard.map.filter_all') }}
                        </button>
                        <button type="button" onclick="filterDashboardModa('SEA')" id="dashModa-SEA"
                                class="dash-moda-btn rounded-lg px-2.5 py-1 font-medium text-slate-600 hover:text-slate-900">
                            {{ __('messages.dashboard.map.filter_sea') }}
                        </button>
                        <button type="button" onclick="filterDashboardModa('AIR')" id="dashModa-AIR"
                            class="dash-moda-btn rounded-lg px-2.5 py-1 font-medium text-slate-600 hover:text-slate-900">
                            {{ __('messages.dashboard.map.filter_air') }}
                        </button>
                        <button type="button" onclick="filterDashboardModa('ROAD')" id="dashModa-ROAD"
                            class="dash-moda-btn rounded-lg px-2.5 py-1 font-medium text-slate-600 hover:text-slate-900">
                            {{ __('messages.dashboard.map.filter_road') }}
                        </button>
                    </div>

                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live
                    </span>
                </div>
            </div>
            <div class="relative">
                <!-- 2D Leaflet Flat Map -->
                <div id="shipmentMap" class="h-96 md:h-[440px] overflow-hidden rounded-xl border border-slate-200"></div>

                <!-- 3D Globe Container (Globe.gl) -->
                <div id="globe3dContainer" class="hidden h-96 md:h-[440px] w-full overflow-hidden rounded-xl border border-slate-800 bg-[#040d1a] relative">
                    <!-- Globe Controls in Top-Right -->
                    <div class="absolute top-3 right-3 z-20 flex items-center gap-2">
                        <button type="button" id="btnGlobeAutoRotate" onclick="toggleGlobeAutoRotate()"
                                title="{{ __('messages.dashboard.map.auto_rotate') }}"
                                class="flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/85 px-3 py-1.5 text-xs font-bold text-cyan-300 shadow-lg backdrop-blur-md transition hover:bg-navy-900">
                            <span id="globeAutoRotateIcon" class="inline-block transition-transform duration-500">🔄</span>
                            <span id="globeAutoRotateText">{{ __('messages.dashboard.map.auto_rotate') }}</span>
                        </button>
                        <button type="button" onclick="resetGlobeView()"
                                title="{{ __('messages.dashboard.map.reset_view') }}"
                                class="flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/85 px-3 py-1.5 text-xs font-bold text-slate-200 shadow-lg backdrop-blur-md transition hover:bg-navy-900 hover:text-white">
                            <span>🎯</span>
                            <span>{{ __('messages.dashboard.map.reset_view') }}</span>
                        </button>
                    </div>

                    <!-- Globe Legend in Bottom-Left -->
                    <div class="pointer-events-none absolute bottom-3 left-3 z-20 flex flex-wrap items-center gap-2 rounded-xl border border-white/10 bg-navy-950/80 px-3 py-1.5 text-xs text-slate-300 shadow-xl backdrop-blur-md">
                        <span class="flex items-center gap-1 font-bold text-teal-300">
                            <span class="h-2 w-2 rounded-full bg-teal-400 animate-ping"></span>
                            <span>Mayora Hub (ID)</span>
                        </span>
                        <span class="text-white/20">|</span>
                        <span class="flex items-center gap-1.5 text-[11px]">
                            <span class="inline-flex items-center gap-0.5"><span class="h-2 w-2.5 rounded-xs bg-[#00a884]"></span> 🚢</span>
                            <span class="inline-flex items-center gap-0.5"><span class="h-2 w-2.5 rounded-xs bg-[#0284c7]"></span> ✈️</span>
                            <span class="inline-flex items-center gap-0.5"><span class="h-2 w-2.5 rounded-xs bg-[#059669]"></span> 🚛</span>
                        </span>
                        <span class="text-white/20 hidden md:inline">|</span>
                        <span class="text-[11px] text-slate-400 hidden md:inline">{{ __('messages.dashboard.map.globe_hint') }}</span>
                    </div>

                    <!-- Floating Interactive Shipment Card on 3D Globe -->
                    <div id="globeShipmentCard" class="hidden absolute top-4 left-4 z-30 max-w-xs sm:max-w-sm rounded-2xl border border-cyan-400/30 bg-navy-950/95 p-4 text-white shadow-2xl backdrop-blur-md transition-all">
                        <div class="flex items-start justify-between gap-3 border-b border-white/10 pb-2">
                            <div class="flex items-center gap-2">
                                <span id="globeCardModeIcon" class="text-lg">🚢</span>
                                <div>
                                    <span id="globeCardCarrier" class="rounded bg-white/10 px-2 py-0.5 text-[10px] font-bold text-cyan-300 font-mono">CARRIER</span>
                                    <h4 id="globeCardShipmentNo" class="text-sm font-black text-white">CT-SHIPMENT</h4>
                                </div>
                            </div>
                            <button type="button" onclick="closeGlobeShipmentCard()" class="rounded-lg p-1 text-slate-400 hover:bg-white/10 hover:text-white">✕</button>
                        </div>
                        <div class="mt-2.5 space-y-1.5 text-xs text-slate-300">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('messages.dashboard.map.origin') }}:</span>
                                <span id="globeCardOrigin" class="font-bold text-white">Jakarta (ID)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('messages.dashboard.map.destination') }}:</span>
                                <span id="globeCardDestination" class="font-bold text-cyan-300">Rotterdam (NL)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('messages.dashboard.map.status') }}:</span>
                                <span id="globeCardStatus" class="rounded-full px-2 py-0.5 text-[10px] font-bold bg-cyan-500/20 text-cyan-300">IN_TRANSIT</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('messages.dashboard.map.carrier') }}:</span>
                                <span id="globeCardVehicle" class="font-medium text-slate-200">MV Kopiko Express</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-white/10 flex justify-end">
                            <a id="globeCardTrackLink" href="#" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-teal-500 to-cyan-500 px-3 py-1.5 text-xs font-bold text-navy-950 shadow-md hover:from-teal-400 hover:to-cyan-400 transition">
                                <span>{{ __('messages.shipments_page.btn_track') }}</span>
                                <span>➔</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Re-center button for 2D Leaflet -->
                <button id="btnRecenterDash" type="button" title="{{ __('messages.dashboard.map.recenter') }}"
                        class="absolute bottom-4 right-4 z-[900] flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow-xl ring-1 ring-slate-200 transition hover:bg-slate-50 hover:scale-105 active:scale-95">
                    <svg class="h-5 w-5 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="7" stroke="currentColor"/>
                        <circle cx="12" cy="12" r="3" fill="currentColor"/>
                        <line x1="12" y1="2" x2="12" y2="5" stroke="currentColor" stroke-linecap="round"/>
                        <line x1="12" y1="19" x2="12" y2="22" stroke="currentColor" stroke-linecap="round"/>
                        <line x1="2" y1="12" x2="5" y2="12" stroke="currentColor" stroke-linecap="round"/>
                        <line x1="19" y1="12" x2="22" y2="12" stroke="currentColor" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-4">
            <div class="mb-4">
                <h3 class="font-bold text-slate-900">{{ __('messages.dashboard.operations_context.title') }}</h3>
                <p class="text-xs text-slate-500">{{ __('messages.dashboard.operations_context.sub') }}</p>
            </div>
            <div class="border-b border-slate-100 pb-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold">{{ __('messages.dashboard.operations_context.port_weather') }}</p>
                    <span class="text-xs text-slate-400">{{ __('messages.dashboard.operations_context.weather_context') }}</span>
                </div>
                @if(!empty($weather['current']))
                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-slate-50 p-3"><span class="block text-xs text-slate-500">{{ __('messages.dashboard.operations_context.temperature') }}</span><strong>{{ $weather['current']['temperature_2m'] ?? 'N/A' }} °C</strong></div>
                        <div class="rounded-lg bg-slate-50 p-3"><span class="block text-xs text-slate-500">{{ __('messages.dashboard.operations_context.wind_speed') }}</span><strong>{{ $weather['current']['wind_speed_10m'] ?? 'N/A' }} km/h</strong></div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">{{ __('messages.dashboard.operations_context.weather_unavailable') }}</p>
                @endif
                <p class="mt-2 text-xs text-slate-400">{{ __('messages.dashboard.operations_context.weather_disclaimer') }}</p>
            </div>
            <div class="pt-4">
                <p class="text-sm font-semibold">{{ __('messages.dashboard.operations_context.shipping_news') }}</p>
                <div class="mt-2 space-y-3">
                    @forelse(($news ?? []) as $item)
                        <a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="block rounded-lg bg-slate-50 p-3 hover:bg-cyan-50">
                            <span class="line-clamp-2 text-sm font-medium text-slate-800">{{ $item['title'] ?? 'Shipping industry update' }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $item['source']['name'] ?? 'Industry source' }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('messages.dashboard.operations_context.news_empty') }}</p>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="font-bold text-slate-900">{{ __('messages.dashboard.recent_shipments.title') }}</h3>
                <p class="text-xs text-slate-500">{{ __('messages.dashboard.recent_shipments.sub') }}</p>
            </div>
            <a href="{{ route('shipments.index') }}" class="text-sm font-semibold text-ocean-700 hover:underline">{{ __('messages.dashboard.view_shipments') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">{{ __('messages.dashboard.recent_shipments.th_shipment') }}</th>
                        <th class="px-5 py-3">{{ __('messages.dashboard.recent_shipments.th_customer') }}</th>
                        <th class="px-5 py-3">{{ __('messages.dashboard.recent_shipments.th_carrier') }}</th>
                        <th class="px-5 py-3">{{ __('messages.dashboard.recent_shipments.th_origin') }}</th>
                        <th class="px-5 py-3">{{ __('messages.dashboard.recent_shipments.th_destination') }}</th>
                        <th class="px-5 py-3">{{ __('messages.dashboard.recent_shipments.th_eta') }}</th>
                        <th class="px-5 py-3">{{ __('messages.dashboard.recent_shipments.th_status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $shipment)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-5 py-3 font-semibold"><a class="text-ocean-700 hover:underline" href="{{ route('tracking.show', $shipment) }}">{{ $shipment->shipment_number }}</a></td>
                            <td class="whitespace-nowrap px-5 py-3">{{ $shipment->customer?->company_name ?? $shipment->customer?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">{{ $shipment->carrier?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">{{ $shipment->origin?->city ?? $shipment->origin?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">{{ $shipment->destination?->city ?? $shipment->destination?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3">{{ $shipment->estimated_arrival_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $shipment->status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-700' : ($shipment->delay_status !== 'ON_TIME' ? 'bg-rose-100 text-rose-700' : 'bg-cyan-100 text-cyan-800') }}">{{ __('messages.statuses.' . $shipment->status) ?? str_replace('_', ' ', $shipment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">{{ __('messages.dashboard.recent_shipments.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const statusData = @json($statusBreakdown);
        const carrierData = @json($carrierBreakdown);
        const routePoints = @json($routePoints);
        const statusTranslations = @json(__('messages.statuses'));
        const transportCallouts = @json(__('messages.transport_modes.callouts'));
        const palette = ['#1483a1', '#28a879', '#e99538', '#d84c5c', '#8795a7', '#7856a8'];

        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: statusData.map(item => statusTranslations[item.label] || item.label.replaceAll('_', ' ')),
                datasets: [{ data: statusData.map(item => item.value), backgroundColor: palette, borderRadius: 6, maxBarThickness: 48 }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } }
        });
        new Chart(document.getElementById('carrierChart'), {
            type: 'doughnut',
            data: { labels: carrierData.map(item => item.label), datasets: [{ data: carrierData.map(item => item.value), backgroundColor: palette, borderWidth: 0 }] },
            options: { maintainAspectRatio: false, cutout: '66%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } }
        });

        const map = L.map('shipmentMap', { zoomControl: true }).setView([-2, 118], 4);
        
        // Clean Basemap (Esri World Street Map - No watermark)
        const streetTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 18
        }).addTo(map);

        const voyagerTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO &copy; OpenStreetMap',
            subdomains: 'abcd',
            maxZoom: 19
        });

        const osmTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        });

        const satelliteTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 18
        });

        L.control.layers({
            'World Street Map': streetTiles,
            'Voyager (Carto)': voyagerTiles,
            'OpenStreetMap': osmTiles,
            'Satellite Imagery': satelliteTiles
        }, null, { position: 'topleft' }).addTo(map);

        const bounds = [];
        const safeText = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));

        // Multi-Modal 3D SVG Templates
        function getDashMarkerHtml(mode) {
            if (mode === 'AIR') {
                return `
                    <div class="relative cursor-pointer select-none">
                        <div class="plane-pulse"></div>
                        <svg class="h-8 w-8 filter drop-shadow-md" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="32" cy="54" rx="20" ry="5" fill="#38bdf8" fill-opacity="0.35"/>
                            <rect x="17" y="34" width="5" height="9" rx="2.5" fill="#475569"/>
                            <rect x="42" y="34" width="5" height="9" rx="2.5" fill="#475569"/>
                            <path d="M32 23 L6 41 L6 45 L30 37 L32 37 L34 37 L58 45 L58 41 Z" fill="#f8fafc" stroke="#94a3b8" stroke-width="1"/>
                            <path d="M6 41 L6 45 L9 43 Z" fill="#0284c7"/>
                            <path d="M58 41 L58 45 L55 43 Z" fill="#0284c7"/>
                            <path d="M32 7 C27 13 26 36 27 51 C27 53 37 53 37 51 C38 36 37 13 32 7 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.2"/>
                            <path d="M29 13 C30 11 34 11 35 13 L34 16 L30 16 Z" fill="#0369a1"/>
                            <path d="M31 43 L33 43 L34.5 53 L29.5 53 Z" fill="#0284c7"/>
                        </svg>
                    </div>
                `;
            } else if (mode === 'ROAD') {
                return `
                    <div class="relative cursor-pointer select-none">
                        <div class="truck-pulse"></div>
                        <svg class="h-8 w-8 filter drop-shadow-md" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="32" cy="54" rx="22" ry="5" fill="#1e293b" fill-opacity="0.35"/>
                            <rect x="11" y="46" width="6" height="6" rx="3" fill="#0f172a"/>
                            <circle cx="14" cy="49" r="1.5" fill="#94a3b8"/>
                            <rect x="23" y="46" width="6" height="6" rx="3" fill="#0f172a"/>
                            <circle cx="26" cy="49" r="1.5" fill="#94a3b8"/>
                            <rect x="42" y="46" width="6" height="6" rx="3" fill="#0f172a"/>
                            <circle cx="45" cy="49" r="1.5" fill="#94a3b8"/>
                            <rect x="50" y="46" width="6" height="6" rx="3" fill="#0f172a"/>
                            <circle cx="53" cy="49" r="1.5" fill="#94a3b8"/>
                            <rect x="9" y="43" width="50" height="4" rx="1" fill="#475569"/>
                            <rect x="20" y="19" width="39" height="24" rx="2" fill="#059669" stroke="#047857" stroke-width="1.2"/>
                            <line x1="26" y1="20" x2="26" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <line x1="33" y1="20" x2="33" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <line x1="40" y1="20" x2="40" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <line x1="47" y1="20" x2="47" y2="41" stroke="#047857" stroke-width="1.2"/>
                            <path d="M6 43 L6 31 C6 28 8 25 11 25 L19 25 L19 43 Z" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.2"/>
                            <path d="M7 31 C7 28 9 27 12 27 L15 27 L15 35 L7 35 Z" fill="#0284c7"/>
                            <rect x="5.5" y="37" width="3.5" height="3" rx="0.8" fill="#f59e0b"/>
                        </svg>
                    </div>
                `;
            }

            // Default: SEA (Kapal)
            return `
                <div class="relative cursor-pointer select-none">
                    <div class="vessel-pulse"></div>
                    <svg class="h-8 w-8 filter drop-shadow-md" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="32" cy="54" rx="24" ry="6" fill="#38bdf8" fill-opacity="0.35"/>
                        <path d="M8 38 L16 50 C24 53 40 53 48 50 L56 38 C56 38 46 41 32 41 C18 41 8 38 8 38 Z" fill="#ef4444"/>
                        <path d="M10 40 C22 43 42 43 54 40 L53 42 C41 45 23 45 11 42 Z" fill="#06b6d4"/>
                        <rect x="20" y="27" width="24" height="9" rx="2" fill="#ffffff" stroke="#cbd5e1" stroke-width="1"/>
                        <rect x="24" y="20" width="16" height="7" rx="1.5" fill="#ffffff" stroke="#cbd5e1" stroke-width="1"/>
                        <rect x="29" y="13" width="6" height="7" rx="1" fill="#dc2626"/>
                        <circle cx="32" cy="6.5" r="1.5" fill="#ef4444"/>
                    </svg>
                </div>
            `;
        }

        // Store map items categorized by transport mode for interactive filtering
        const modeLayers = {
            SEA: [],
            AIR: [],
            ROAD: []
        };
        const allShipmentItems = [];

        routePoints.forEach((point, index) => {
            const mode = point.transport_mode || 'SEA';
            const origin = [Number(point.origin_latitude), Number(point.origin_longitude)];
            const destination = [Number(point.destination_latitude), Number(point.destination_longitude)];
            const current = [Number(point.latitude), Number(point.longitude)];

            const hasOrigin = origin.every(Number.isFinite);
            const hasDest = destination.every(Number.isFinite);
            const hasCurr = current.every(Number.isFinite);

            const pointLayers = [];
            const routeAccentColor = mode === 'AIR' ? '#0284c7' : (mode === 'ROAD' ? '#059669' : '#00a884');
            const calloutPrefix = transportCallouts[mode] || point.callout_prefix || 'En route to';

            if (hasOrigin && hasCurr) {
                // Traversed segment: subtle grey
                const pastLine = L.polyline([origin, current], {
                    color: '#94a3b8',
                    weight: 3.5,
                    opacity: 0.85,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);
                pointLayers.push(pastLine);
                bounds.push(origin);
            }

            if (hasCurr && hasDest && point.status !== 'DELIVERED') {
                // Remaining segment: colored by mode
                const remainLine = L.polyline([current, destination], {
                    color: routeAccentColor,
                    weight: 4.5,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);
                pointLayers.push(remainLine);
                bounds.push(destination);
            }

            if (hasCurr) {
                bounds.push(current);

                const calloutContent = `
                    <div class="custom-dash-callout" onclick="window.location.href='${point.tracking_url}'">
                        <div class="callout-sub">
                            <span>${safeText(calloutPrefix)}</span>
                        </div>
                        <div class="callout-dest" style="color: ${routeAccentColor}">${safeText(point.destination_label)}</div>
                        <div class="mt-1 flex items-center justify-between gap-3 text-[11px] text-slate-500 border-t border-slate-100 pt-1">
                            <span class="font-bold text-slate-700">${safeText(point.shipment_number)}</span>
                            <span class="font-semibold" style="color: ${routeAccentColor}">{{ __('messages.shipments_page.btn_track') }}</span>
                        </div>
                        <div class="callout-arrow"></div>
                    </div>
                `;

                const markerIcon = L.divIcon({
                    html: getDashMarkerHtml(mode),
                    className: `custom-${mode.toLowerCase()}-marker-dash`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                    popupAnchor: [0, -18]
                });

                let marker;
                if (point.status === 'IN_TRANSIT') {
                    marker = L.marker(current, { icon: markerIcon, zIndexOffset: 500 }).addTo(map);
                    marker.bindPopup(calloutContent, {
                        className: 'dash-vessel-popup',
                        closeButton: false,
                        autoClose: false,
                        closeOnClick: false,
                        offset: [0, -10]
                    });
                    if (index === 0) {
                        marker.openPopup();
                    }
                } else {
                    marker = L.marker(current).addTo(map).bindPopup(calloutContent, {
                        className: 'dash-vessel-popup',
                        closeButton: false
                    });
                }
                pointLayers.push(marker);
            }

            // Register into mode group
            if (!modeLayers[mode]) modeLayers[mode] = [];
            modeLayers[mode].push(...pointLayers);
            allShipmentItems.push({ mode, layers: pointLayers, current, origin, destination });
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [35, 35], maxZoom: 5 });
        }

        // Interactive Moda Filter Handler
        window.filterDashboardModa = function(selectedMode) {
            currentFilterMode = selectedMode;

            // Update button styles
            ['ALL', 'SEA', 'AIR', 'ROAD'].forEach(m => {
                const btn = document.getElementById(`dashModa-${m}`);
                if (!btn) return;
                if (m === selectedMode) {
                    btn.className = 'dash-moda-btn rounded-lg px-2.5 py-1 font-bold bg-white text-navy-900 shadow-xs ring-1 ring-slate-200';
                } else {
                    btn.className = 'dash-moda-btn rounded-lg px-2.5 py-1 font-medium text-slate-600 hover:text-slate-900';
                }
            });

            // Filter layers on 2D map
            const visibleBounds = [];
            allShipmentItems.forEach(item => {
                const isVisible = (selectedMode === 'ALL' || item.mode === selectedMode);
                item.layers.forEach(layer => {
                    if (isVisible) {
                        if (!map.hasLayer(layer)) map.addLayer(layer);
                    } else {
                        if (map.hasLayer(layer)) map.removeLayer(layer);
                    }
                });
                if (isVisible) {
                    if (item.current && Number.isFinite(item.current[0])) visibleBounds.push(item.current);
                    if (item.destination && Number.isFinite(item.destination[0])) visibleBounds.push(item.destination);
                }
            });

            if (visibleBounds.length > 0 && currentViewMode === '2D') {
                map.flyToBounds(visibleBounds, { padding: [40, 40], maxZoom: 6, duration: 1.0 });
            }

            // Sync with 3D Globe
            if (globeInstance) {
                updateGlobeData(selectedMode);
            }
        };

        document.getElementById('btnRecenterDash').addEventListener('click', () => {
            if (bounds.length > 1) {
                map.flyToBounds(bounds, { padding: [35, 35], maxZoom: 5, duration: 1.2 });
            }
        });

        // 3D Globe Implementation (Globe.gl)
        let currentViewMode = '2D';
        let currentFilterMode = 'ALL';
        let globeInstance = null;
        let isGlobeAutoRotating = true;

        const globeContainer = document.getElementById('globe3dContainer');
        const map2DContainer = document.getElementById('shipmentMap');
        const recenter2DBtn = document.getElementById('btnRecenterDash');

        window.switchDashboardView = function(view) {
            currentViewMode = view;
            const btn2D = document.getElementById('btnDashMap2D');
            const btn3D = document.getElementById('btnDashMap3D');

            if (view === '3D') {
                btn3D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-bold bg-navy-950 text-cyan-300 shadow-xs transition';
                btn2D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-semibold text-slate-600 hover:text-slate-900 transition';
                map2DContainer.classList.add('hidden');
                if (recenter2DBtn) recenter2DBtn.classList.add('hidden');
                globeContainer.classList.remove('hidden');

                if (!globeInstance) {
                    initGlobe3D();
                } else {
                    globeInstance.resumeAnimation();
                    resizeGlobe();
                    updateGlobeData(currentFilterMode);
                }
            } else {
                btn2D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-bold bg-white text-navy-950 shadow-xs transition';
                btn3D.className = 'map-view-btn flex items-center gap-1.5 rounded-lg px-3 py-1 font-semibold text-slate-600 hover:text-slate-900 transition';
                globeContainer.classList.add('hidden');
                map2DContainer.classList.remove('hidden');
                if (recenter2DBtn) recenter2DBtn.classList.remove('hidden');

                if (globeInstance) {
                    globeInstance.pauseAnimation();
                }
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
            }
        };

        function resizeGlobe() {
            if (!globeInstance || !globeContainer) return;
            const width = globeContainer.clientWidth || 800;
            const height = globeContainer.clientHeight || 440;
            globeInstance.width(width).height(height);
        }

        function initGlobe3D() {
            if (typeof Globe === 'undefined') {
                console.error('Globe.gl library is not loaded');
                return;
            }

            const width = globeContainer.clientWidth || 800;
            const height = globeContainer.clientHeight || 440;

            const blueMarbleUrl = "{{ asset('vendor/globe/earth-blue-marble.jpg') }}";
            const nightSkyUrl = "{{ asset('vendor/globe/night-sky.png') }}";
            const bumpUrl = "{{ asset('vendor/globe/earth-night.jpg') }}";

            globeInstance = Globe()(globeContainer)
                .globeImageUrl(blueMarbleUrl)
                .backgroundImageUrl(nightSkyUrl)
                .bumpImageUrl(bumpUrl)
                .showAtmosphere(true)
                .atmosphereColor('#38bdf8')
                .atmosphereAltitude(0.22)
                .width(width)
                .height(height);

            // Centered on Indonesia / Southeast Asia global trade hub
            globeInstance.pointOfView({ lat: -2, lng: 118, altitude: 2.2 }, 0);

            // Controls config
            globeInstance.controls().autoRotate = true;
            globeInstance.controls().autoRotateSpeed = 0.65;
            globeInstance.controls().enableDamping = true;
            globeInstance.controls().dampingFactor = 0.08;

            globeContainer.addEventListener('pointerdown', () => {
                if (isGlobeAutoRotating && globeInstance) {
                    globeInstance.controls().autoRotate = false;
                }
            });

            globeContainer.addEventListener('pointerup', () => {
                if (isGlobeAutoRotating && globeInstance) {
                    setTimeout(() => {
                        if (isGlobeAutoRotating && globeInstance) {
                            globeInstance.controls().autoRotate = true;
                        }
                    }, 2500);
                }
            });

            updateGlobeData(currentFilterMode);

            window.addEventListener('resize', resizeGlobe);
        }

        window.toggleGlobeAutoRotate = function() {
            if (!globeInstance) return;
            isGlobeAutoRotating = !isGlobeAutoRotating;
            globeInstance.controls().autoRotate = isGlobeAutoRotating;

            const icon = document.getElementById('globeAutoRotateIcon');
            const btn = document.getElementById('btnGlobeAutoRotate');
            if (isGlobeAutoRotating) {
                if (icon) icon.className = 'inline-block transition-transform duration-500 animate-spin';
                if (btn) btn.className = 'flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/85 px-3 py-1.5 text-xs font-bold text-cyan-300 shadow-lg backdrop-blur-md transition hover:bg-navy-900';
            } else {
                if (icon) icon.className = 'inline-block transition-transform duration-500';
                if (btn) btn.className = 'flex items-center gap-1.5 rounded-xl border border-white/20 bg-navy-950/50 px-3 py-1.5 text-xs font-bold text-slate-400 shadow-lg backdrop-blur-md transition hover:bg-navy-900 hover:text-white';
            }
        };

        window.resetGlobeView = function() {
            if (!globeInstance) return;
            globeInstance.pointOfView({ lat: -2, lng: 118, altitude: 2.2 }, 1200);
        };

        window.closeGlobeShipmentCard = function() {
            const card = document.getElementById('globeShipmentCard');
            if (card) card.classList.add('hidden');
        };

        window.showGlobeShipmentCard = function(point) {
            if (!point) return;
            const card = document.getElementById('globeShipmentCard');
            if (!card) return;

            document.getElementById('globeCardModeIcon').textContent = point.transport_mode_icon || '🚢';
            document.getElementById('globeCardCarrier').textContent = point.carrier || 'CARRIER';
            document.getElementById('globeCardShipmentNo').textContent = point.shipment_number;
            document.getElementById('globeCardOrigin').textContent = point.origin_city || point.origin || 'Tangerang / Tanjung Priok (ID)';
            document.getElementById('globeCardDestination').textContent = point.destination_label || point.destination_city || 'Destination';
            
            const statusBadge = document.getElementById('globeCardStatus');
            statusBadge.textContent = statusTranslations[point.status] || point.status;
            statusBadge.className = `rounded-full px-2 py-0.5 text-[10px] font-bold ${point.status === 'DELIVERED' ? 'bg-emerald-500/20 text-emerald-300' : (point.delay_status !== 'ON_TIME' ? 'bg-rose-500/20 text-rose-300' : 'bg-cyan-500/20 text-cyan-300')}`;

            document.getElementById('globeCardVehicle').textContent = point.vehicle_name || point.vessel_name || 'Fleet Vehicle';
            document.getElementById('globeCardTrackLink').href = point.tracking_url;

            card.classList.remove('hidden');
        };

        function updateGlobeData(mode) {
            if (!globeInstance) return;

            const filtered = routePoints.filter(p => mode === 'ALL' || p.transport_mode === mode);

            // 1. Great-Circle 3D Curved Arcs
            const arcs = [];
            filtered.forEach(p => {
                const startLat = Number(p.origin_latitude || -6.1018);
                const startLng = Number(p.origin_longitude || 106.8837);
                const endLat = Number(p.destination_latitude);
                const endLng = Number(p.destination_longitude);

                if (Number.isFinite(startLat) && Number.isFinite(startLng) && Number.isFinite(endLat) && Number.isFinite(endLng)) {
                    const color = p.transport_mode === 'AIR'
                        ? ['#38bdf8', '#0284c7']
                        : (p.transport_mode === 'ROAD' ? ['#34d399', '#059669'] : ['#2dd4bf', '#00a884']);

                    arcs.push({
                        startLat,
                        startLng,
                        endLat,
                        endLng,
                        color,
                        altitude: p.transport_mode === 'AIR' ? 0.35 : (p.transport_mode === 'ROAD' ? 0.12 : 0.24),
                        stroke: p.transport_mode === 'AIR' ? 2.2 : 1.8,
                        point: p
                    });
                }
            });

            globeInstance
                .arcsData(arcs)
                .arcColor('color')
                .arcAltitude('altitude')
                .arcStroke('stroke')
                .arcDashLength(0.38)
                .arcDashGap(0.22)
                .arcDashAnimateTime(2200)
                .arcLabel(d => `
                    <div style="background: rgba(7, 26, 45, 0.95); border: 1px solid rgba(56, 189, 248, 0.4); padding: 10px 14px; border-radius: 12px; color: #fff; font-family: 'DM Sans', sans-serif; box-shadow: 0 10px 25px rgba(0,0,0,0.5); min-width: 220px; text-align: left;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px; margin-bottom: 6px;">
                            <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #38bdf8;">${safeText(d.point.transport_mode)} · ${safeText(d.point.carrier)}</span>
                            <span style="font-size: 14px;">${d.point.transport_mode_icon || '🚢'}</span>
                        </div>
                        <div style="font-size: 14px; font-weight: 800; color: #fff; font-family: monospace;">${safeText(d.point.shipment_number)}</div>
                        <div style="font-size: 12px; color: #cbd5e1; margin-top: 4px;">📍 ${safeText(d.point.origin_city || 'Jakarta')} ➔ <strong style="color: #67e8f9;">${safeText(d.point.destination_label)}</strong></div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; font-size: 11px;">
                            <span style="color: #94a3b8;">${safeText(d.point.vehicle_name)}</span>
                            <span style="color: #34d399; font-weight: 700;">${statusTranslations[d.point.status] || d.point.status}</span>
                        </div>
                    </div>
                `)
                .onArcClick(d => {
                    showGlobeShipmentCard(d.point);
                    globeInstance.pointOfView({ lat: d.endLat, lng: d.endLng, altitude: 1.6 }, 1000);
                });

            // 2. Sonar Rings on Hubs (Jakarta Origin & Global Destination Ports)
            const rings = [
                { lat: -6.1018, lng: 106.8837, maxR: 4.8, propSpeed: 2.2, repeatPeriod: 1200, color: '#10b981' }
            ];
            filtered.forEach(p => {
                if (Number.isFinite(Number(p.destination_latitude)) && Number.isFinite(Number(p.destination_longitude))) {
                    rings.push({
                        lat: Number(p.destination_latitude),
                        lng: Number(p.destination_longitude),
                        maxR: 3.2,
                        propSpeed: 1.6,
                        repeatPeriod: 1600,
                        color: p.transport_mode === 'AIR' ? '#38bdf8' : (p.transport_mode === 'ROAD' ? '#34d399' : '#00a884')
                    });
                }
            });

            globeInstance
                .ringsData(rings)
                .ringColor(d => d.color)
                .ringMaxRadius(d => d.maxR)
                .ringPropagationSpeed(d => d.propSpeed)
                .ringRepeatPeriod(d => d.repeatPeriod);

            // 3. HTML Moving Vehicle Markers & Origin Pin
            const markers = [
                {
                    lat: -6.1018,
                    lng: 106.8837,
                    type: 'hub',
                    title: 'PT Mayora Indah Tbk Global Trade Hub'
                }
            ];

            filtered.forEach(p => {
                if (Number.isFinite(Number(p.latitude)) && Number.isFinite(Number(p.longitude))) {
                    markers.push({
                        lat: Number(p.latitude),
                        lng: Number(p.longitude),
                        type: 'vehicle',
                        mode: p.transport_mode,
                        icon: p.transport_mode_icon || '🚢',
                        title: p.shipment_number,
                        point: p
                    });
                }
            });

            globeInstance
                .htmlElementsData(markers)
                .htmlElement(d => {
                    const el = document.createElement('div');
                    if (d.type === 'hub') {
                        el.innerHTML = `
                            <div style="cursor: pointer; text-align: center; transform: translate(-50%, -100%);">
                                <div style="display: inline-flex; align-items: center; gap: 4px; background: rgba(6, 78, 59, 0.95); border: 1.5px solid #10b981; padding: 2px 7px; border-radius: 20px; color: #6ee7b7; font-size: 10px; font-weight: 800; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); backdrop-filter: blur(4px);">
                                    <span>🏭</span>
                                    <span>MAYORA HUB</span>
                                </div>
                            </div>
                        `;
                    } else {
                        const bg = d.mode === 'AIR' ? 'rgba(2, 132, 199, 0.92)' : (d.mode === 'ROAD' ? 'rgba(5, 150, 105, 0.92)' : 'rgba(0, 168, 132, 0.92)');
                        const border = d.mode === 'AIR' ? '#7dd3fc' : (d.mode === 'ROAD' ? '#6ee7b7' : '#5eead4');
                        el.innerHTML = `
                            <div style="cursor: pointer; text-align: center; transform: translate(-50%, -100%); transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);"
                                 onmouseover="this.style.transform='translate(-50%, -115%) scale(1.15)'"
                                 onmouseout="this.style.transform='translate(-50%, -100%) scale(1)'">
                                <div style="display: inline-flex; align-items: center; gap: 4px; background: ${bg}; border: 1.5px solid ${border}; padding: 3px 8px; border-radius: 20px; color: #fff; font-size: 11px; font-weight: 800; box-shadow: 0 6px 18px rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
                                    <span style="font-size: 13px;">${d.icon}</span>
                                    <span style="font-family: monospace; font-size: 10px; letter-spacing: 0.5px;">${safeText(d.title)}</span>
                                </div>
                            </div>
                        `;
                        el.onclick = (e) => {
                            e.stopPropagation();
                            showGlobeShipmentCard(d.point);
                            globeInstance.pointOfView({ lat: d.lat, lng: d.lng, altitude: 1.5 }, 900);
                        };
                    }
                    return el;
                });
        }
    </script>
@endpush
