@extends('layouts.app')

@section('title', 'Shipments · PT Mayora Indah Tbk')
@section('page-title', 'Global Shipment Directory')

@section('content')
<div class="space-y-6 p-5 md:p-8">
    <!-- Header Banner with Brand Theme -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy-950 via-navy-900 to-ocean-600 p-6 text-white shadow-lg">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-cyan-300 backdrop-blur">
                    <span>☕ PT Mayora Indah Tbk</span>
                    <span>•</span>
                    <span>Global Export Visibility</span>
                </div>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-white md:text-3xl">Active Export Shipments</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-300">
                    Real-time container visibility, multimodal voyage milestones, and arrival schedules for confectionery exports to 5 continents.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-center backdrop-blur">
                    <span class="block text-2xl font-extrabold text-cyan-400">{{ $shipments->total() }}</span>
                    <span class="text-xs uppercase tracking-wider text-slate-300">Active Shipments</span>
                </span>
            </div>
        </div>
        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-ocean-500/20 blur-2xl"></div>
    </div>

    <!-- Filter & Search Toolbar -->
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('shipments.index') }}" class="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_auto]">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Quick Search</label>
                <div class="relative">
                    <input name="search" value="{{ request('search') }}" placeholder="Search by Shipment No, Container, B/L..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pl-10 text-sm font-medium text-slate-800 placeholder-slate-400 outline-none transition focus:border-ocean-500 focus:bg-white focus:ring-4 focus:ring-ocean-500/10">
                    <span class="absolute left-3.5 top-3 text-slate-400">🔍</span>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Lifecycle Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none transition focus:border-ocean-500 focus:bg-white focus:ring-4 focus:ring-ocean-500/10">
                    <option value="">All Statuses (Global Fleet)</option>
                    @foreach(['CREATED' => 'Created (Booking Draft)', 'BOOKED' => 'Booked (Space Confirmed)', 'PICKED_UP' => 'Picked Up (Factory Drayage)', 'IN_TRANSIT' => 'In Transit (Ocean Voyage)', 'DELIVERED' => 'Delivered (Consignee e-POD)', 'CANCELLED' => 'Cancelled'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Ocean Carrier</label>
                <select name="carrier_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none transition focus:border-ocean-500 focus:bg-white focus:ring-4 focus:ring-ocean-500/10">
                    <option value="">All Shipping Lines</option>
                    @foreach($carriers as $c)
                        <option value="{{ $c->id }}" @selected((string) request('carrier_id') === (string) $c->id)>
                            {{ $c->name }} ({{ $c->scac }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-ocean-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-ocean-700 focus:ring-4 focus:ring-ocean-500/20">
                    Apply Filter
                </button>
                <a href="{{ route('shipments.index') }}" class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <!-- Table of Shipments -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <p class="text-sm font-bold text-slate-800">
                    Showing <span class="text-ocean-600">{{ $shipments->count() }}</span> of {{ $shipments->total() }} International Shipments
                </p>
            </div>
            <p class="text-xs text-slate-500">PT Mayora Indah Tbk Global Supply Chain</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-3.5">Shipment & Container</th>
                        <th class="px-6 py-3.5">Commodity / Export Item</th>
                        <th class="px-6 py-3.5">Buyer (Consignee)</th>
                        <th class="px-6 py-3.5">Carrier & Vessel</th>
                        <th class="px-6 py-3.5">Trade Route</th>
                        <th class="px-6 py-3.5">ETA & Delay</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-normal">
                    @forelse($shipments as $shipment)
                        <tr class="transition hover:bg-slate-50/80">
                            <!-- Shipment & Container -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <a href="{{ route('tracking.show', $shipment) }}" class="font-bold text-ocean-600 hover:text-ocean-700 hover:underline">
                                    {{ $shipment->shipment_number }}
                                </a>
                                <div class="mt-1 flex items-center gap-1.5 font-mono text-xs text-slate-600">
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 font-semibold text-slate-700 border border-slate-200">{{ $shipment->container_number }}</span>
                                    <span class="text-[10px] text-slate-400">({{ $shipment->container_type }})</span>
                                </div>
                                <div class="mt-0.5 text-[11px] text-slate-400">B/L: {{ $shipment->bill_of_lading_number }}</div>
                            </td>

                            <!-- Commodity / Item -->
                            <td class="px-6 py-4">
                                @php
                                    $firstItem = $shipment->items->first();
                                    $itemCount = $shipment->items->count();
                                @endphp
                                @if($firstItem)
                                    <div class="font-semibold text-slate-800 line-clamp-1">
                                        @if(str_contains(strtolower($firstItem->product_name), 'kopiko')) 🍬
                                        @elseif(str_contains(strtolower($firstItem->product_name), 'danisa')) 🍪
                                        @elseif(str_contains(strtolower($firstItem->product_name), 'torabika')) ☕
                                        @elseif(str_contains(strtolower($firstItem->product_name), 'beng')) 🍫
                                        @else 📦
                                        @endif
                                        {{ $firstItem->product_name }}
                                    </div>
                                    <div class="mt-0.5 text-xs text-slate-500">
                                        {{ number_format($firstItem->quantity) }} Master Cartons • <span class="font-mono text-[11px]">HS {{ $firstItem->hs_code }}</span>
                                    </div>
                                    @if($itemCount > 1)
                                        <span class="mt-1 inline-block rounded bg-purple-50 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 border border-purple-200">
                                            +{{ $itemCount - 1 }} more export product
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400">Standard Confectionery Cargo</span>
                                @endif
                            </td>

                            <!-- Customer / Consignee -->
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900 line-clamp-1">
                                    {{ $shipment->customer?->company_name ?? $shipment->customer?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $shipment->customer?->name ?? '' }}
                                </div>
                            </td>

                            <!-- Carrier & Vessel -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="rounded bg-navy-900 px-2 py-0.5 font-mono text-xs font-bold text-cyan-300">
                                        {{ $shipment->carrier?->scac ?? 'CARRIER' }}
                                    </span>
                                    <span class="font-semibold text-slate-700">{{ $shipment->carrier?->name }}</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-500 italic">
                                    🚢 {{ $shipment->vessel_name ?? 'TBA' }} ({{ $shipment->voyage_number ?? '—' }})
                                </div>
                            </td>

                            <!-- Trade Route -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-slate-800">🇮🇩 {{ $shipment->origin?->city ?? 'Jakarta' }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">{{ $shipment->origin?->unlocode }}</div>
                                    </div>
                                    <span class="text-slate-300">➔</span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800">
                                            @if($shipment->destination?->country_code === 'PH') 🇵🇭
                                            @elseif($shipment->destination?->country_code === 'NL') 🇳🇱
                                            @elseif($shipment->destination?->country_code === 'US') 🇺🇸
                                            @elseif($shipment->destination?->country_code === 'CN') 🇨🇳
                                            @elseif($shipment->destination?->country_code === 'AE') 🇦🇪
                                            @elseif($shipment->destination?->country_code === 'SA') 🇸🇦
                                            @elseif($shipment->destination?->country_code === 'GB') 🇬🇧
                                            @elseif($shipment->destination?->country_code === 'DE') 🇩🇪
                                            @elseif($shipment->destination?->country_code === 'NG') 🇳🇬
                                            @elseif($shipment->destination?->country_code === 'AU') 🇦🇺
                                            @elseif($shipment->destination?->country_code === 'BR') 🇧🇷
                                            @elseif($shipment->destination?->country_code === 'JP') 🇯🇵
                                            @endif
                                            {{ $shipment->destination?->city ?? 'Destination' }}
                                        </div>
                                        <div class="text-[10px] font-mono text-slate-400">{{ $shipment->destination?->unlocode }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- ETA & Delay Status -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-xs font-semibold text-slate-800">
                                    📅 {{ $shipment->estimated_arrival_at?->format('d M Y, H:i') ?? '—' }}
                                </div>
                                <div class="mt-1">
                                    @if($shipment->delay_status === 'HIGH_DELAY')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-700 animate-pulse border border-rose-300">
                                            ⚠️ High Delay (+{{ $shipment->delay_duration_hours }}h)
                                        </span>
                                    @elseif($shipment->delay_status === 'MEDIUM_DELAY')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-300">
                                            ⚠️ Medium Delay (+{{ $shipment->delay_duration_hours }}h)
                                        </span>
                                    @elseif($shipment->delay_status === 'LOW_DELAY')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800 border border-yellow-300">
                                            ⏱ Low Delay (+{{ $shipment->delay_duration_hours }}h)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-300">
                                            ✓ On Time (0h)
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Lifecycle Status -->
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($shipment->status === 'DELIVERED')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold text-white shadow-sm shadow-emerald-500/20">
                                        ✓ Delivered
                                    </span>
                                @elseif($shipment->status === 'IN_TRANSIT')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-ocean-500 px-3 py-1 text-xs font-bold text-white shadow-sm shadow-ocean-500/20">
                                        🚢 In Transit
                                    </span>
                                @elseif($shipment->status === 'PICKED_UP')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white">
                                        🚚 Picked Up
                                    </span>
                                @elseif($shipment->status === 'BOOKED')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500 px-3 py-1 text-xs font-bold text-white">
                                        📑 Booked
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-500 px-3 py-1 text-xs font-bold text-white">
                                        Draft Created
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <a href="{{ route('tracking.show', $shipment) }}" class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-ocean-700 transition hover:bg-ocean-600 hover:text-white">
                                    <span>Track</span>
                                    <span>➔</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="mx-auto max-w-sm">
                                    <span class="text-4xl">🚢</span>
                                    <h4 class="mt-2 text-base font-bold text-slate-800">No shipments found</h4>
                                    <p class="mt-1 text-xs text-slate-500">There are no export shipments matching your current filter criteria.</p>
                                    <a href="{{ route('shipments.index') }}" class="mt-4 inline-block rounded-xl bg-ocean-600 px-4 py-2 text-xs font-bold text-white">Reset Filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                {{ $shipments->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
