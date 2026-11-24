@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <style>
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
            <h2 class="text-2xl font-bold text-slate-900">Shipment overview</h2>
            <p class="mt-1 text-sm text-slate-500">A live view of your global supply chain operations.</p>
        </div>
        <a href="{{ route('shipments.index') }}" class="rounded-lg bg-ocean-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-ocean-700">View shipments <span aria-hidden="true">→</span></a>
    </div>

    <section class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        @foreach([
            ['Total Shipments', $dashboard['total_shipments'], '◇', 'bg-cyan-100 text-cyan-700'],
            ['In Transit', $dashboard['in_transit'], '⇢', 'bg-sky-100 text-sky-700'],
            ['Delivered', $dashboard['delivered'], '✓', 'bg-emerald-100 text-emerald-700'],
            ['Delayed', $dashboard['delayed'], '◷', 'bg-amber-100 text-amber-700'],
            ['Open Exceptions', $dashboard['open_exceptions'], '△', 'bg-rose-100 text-rose-700']
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
                <h3 class="font-bold text-slate-900">Shipments by status</h3>
                <p class="text-xs text-slate-500">Current distribution of shipments</p>
            </div>
            <div class="h-64"><canvas id="statusChart"></canvas></div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-4">
            <div class="mb-4">
                <h3 class="font-bold text-slate-900">Carrier distribution</h3>
                <p class="text-xs text-slate-500">Shipment volume by carrier</p>
            </div>
            <div class="h-64"><canvas id="carrierChart"></canvas></div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3">
            <div class="mb-4">
                <h3 class="font-bold text-slate-900">Delivery performance</h3>
                <p class="text-xs text-slate-500">Operational KPIs</p>
            </div>
            <div class="space-y-6 pt-2">
                <div>
                    <div class="mb-2 flex justify-between text-sm"><span class="text-slate-600">On-time rate</span><strong>{{ $dashboard['on_time_rate'] }}%</strong></div>
                    <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-emerald-500" style="width: {{ min($dashboard['on_time_rate'], 100) }}%"></div></div>
                </div>
                <div>
                    <div class="mb-2 flex justify-between text-sm"><span class="text-slate-600">Exception rate</span><strong>{{ $dashboard['exception_rate'] }}%</strong></div>
                    <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-rose-500" style="width: {{ min($dashboard['exception_rate'], 100) }}%"></div></div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Open issues requiring attention</p>
                    <p class="mt-1 text-2xl font-bold text-rose-600">{{ $dashboard['open_exceptions'] }}</p>
                    <a href="{{ route('exceptions.index') }}" class="mt-2 inline-block text-xs font-semibold text-ocean-700 hover:underline">Review exceptions →</a>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-12">
        <article class="relative rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-8">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900">Shipment Map (Live Multi-Modal Tracking)</h3>
                    <p class="text-xs text-slate-500">Lintasan satelit real-time: Kapal Laut 🚢, Pesawat Udara ✈️, dan Truk Darat 🚛</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Filter Moda Bar -->
                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs">
                        <button type="button" onclick="filterDashboardModa('ALL')" id="dashModa-ALL"
                                class="dash-moda-btn rounded-lg px-2.5 py-1 font-bold bg-white text-navy-900 shadow-xs">
                            Semua Moda
                        </button>
                        <button type="button" onclick="filterDashboardModa('SEA')" id="dashModa-SEA"
                                class="dash-moda-btn rounded-lg px-2.5 py-1 font-medium text-slate-600 hover:text-slate-900">
                            🚢 Laut
                        </button>
                        <button type="button" onclick="filterDashboardModa('AIR')" id="dashModa-AIR"
                            class="dash-moda-btn rounded-lg px-2.5 py-1 font-medium text-slate-600 hover:text-slate-900">
                            ✈️ Udara
                        </button>
                        <button type="button" onclick="filterDashboardModa('ROAD')" id="dashModa-ROAD"
                            class="dash-moda-btn rounded-lg px-2.5 py-1 font-medium text-slate-600 hover:text-slate-900">
                            🚛 Darat
                        </button>
                    </div>

                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live
                    </span>
                </div>
            </div>
            <div class="relative">
                <div id="shipmentMap" class="h-96 overflow-hidden rounded-xl border border-slate-200"></div>
                <button id="btnRecenterDash" type="button" title="Pusatkan Peta (Fit All Shipments)"
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
                <h3 class="font-bold text-slate-900">Operations context</h3>
                <p class="text-xs text-slate-500">External data for planning awareness</p>
            </div>
            <div class="border-b border-slate-100 pb-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold">Port weather</p>
                    <span class="text-xs text-slate-400">Weather context</span>
                </div>
                @if(!empty($weather['current']))
                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-slate-50 p-3"><span class="block text-xs text-slate-500">Temperature</span><strong>{{ $weather['current']['temperature_2m'] ?? 'N/A' }} °C</strong></div>
                        <div class="rounded-lg bg-slate-50 p-3"><span class="block text-xs text-slate-500">Wind speed</span><strong>{{ $weather['current']['wind_speed_10m'] ?? 'N/A' }} km/h</strong></div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">Weather information is unavailable right now.</p>
                @endif
                <p class="mt-2 text-xs text-slate-400">Weather is planning context, not proof of shipment delay.</p>
            </div>
            <div class="pt-4">
                <p class="text-sm font-semibold">Shipping news</p>
                <div class="mt-2 space-y-3">
                    @forelse(($news ?? []) as $item)
                        <a href="{{ $item['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="block rounded-lg bg-slate-50 p-3 hover:bg-cyan-50">
                            <span class="line-clamp-2 text-sm font-medium text-slate-800">{{ $item['title'] ?? 'Shipping industry update' }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $item['source']['name'] ?? 'Industry source' }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">No shipping news is available right now.</p>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="font-bold text-slate-900">Recent shipments</h3>
                <p class="text-xs text-slate-500">Latest updated shipments</p>
            </div>
            <a href="{{ route('shipments.index') }}" class="text-sm font-semibold text-ocean-700 hover:underline">All shipments →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Shipment No.</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Carrier</th>
                        <th class="px-5 py-3">Origin</th><th class="px-5 py-3">Destination</th><th class="px-5 py-3">ETA</th><th class="px-5 py-3">Status</th>
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
                            <td class="whitespace-nowrap px-5 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $shipment->status === 'DELIVERED' ? 'bg-emerald-100 text-emerald-700' : ($shipment->delay_status !== 'ON_TIME' ? 'bg-rose-100 text-rose-700' : 'bg-cyan-100 text-cyan-800') }}">{{ str_replace('_', ' ', $shipment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">No shipments found for this account.</td></tr>
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
        const palette = ['#1483a1', '#28a879', '#e99538', '#d84c5c', '#8795a7', '#7856a8'];

        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: statusData.map(item => item.label.replaceAll('_', ' ')),
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
            const calloutPrefix = point.callout_prefix || (mode === 'AIR' ? 'Dalam penerbangan ke' : (mode === 'ROAD' ? 'Dalam perjalanan darat ke' : 'Dalam perjalanan ke'));

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
                            <span class="font-semibold" style="color: ${routeAccentColor}">Live Tracking →</span>
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

            // Filter layers on map
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

            if (visibleBounds.length > 0) {
                map.flyToBounds(visibleBounds, { padding: [40, 40], maxZoom: 6, duration: 1.0 });
            }
        };

        document.getElementById('btnRecenterDash').addEventListener('click', () => {
            if (bounds.length > 1) {
                map.flyToBounds(bounds, { padding: [35, 35], maxZoom: 5, duration: 1.2 });
            }
        });
    </script>
@endpush
