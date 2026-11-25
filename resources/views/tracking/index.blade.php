@extends('layouts.app')

@section('title', __('messages.page_titles.tracking'))
@section('page-title', __('messages.nav.tracking'))

@section('content')
<div class="space-y-6 p-4 sm:p-6 md:p-8">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy-900 via-navy-800 to-ocean-600 p-6 text-white shadow-lg">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold backdrop-blur-sm text-cyan-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ __('messages.tracking_page.badge') }}
                </span>
                <h2 class="mt-2 text-2xl font-black md:text-3xl tracking-tight">{{ __('messages.tracking_page.banner_title') }}</h2>
                <p class="mt-1 max-w-2xl text-xs md:text-sm text-slate-300">
                    {{ __('messages.tracking_page.banner_sub') }}
                </p>
            </div>
            <a href="{{ route('shipments.index') }}"
               class="rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-navy-900 shadow-sm transition hover:bg-cyan-50 hover:text-ocean-600">
                {{ __('messages.tracking_page.view_all_shipments') }}
            </a>
        </div>
    </div>

    <!-- Multi-Modal Filter & Active Shipments Table -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-4">
            <div>
                <h3 class="font-bold text-slate-900">{{ __('messages.tracking_page.active_fleet_title') }}</h3>
                <p class="text-xs text-slate-500">{{ __('messages.tracking_page.active_fleet_sub') }}</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                @php $activeMode = request('mode', 'ALL'); @endphp
                <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 text-xs shadow-xs">
                    <a href="{{ route('tracking.index') }}"
                       class="rounded-lg px-3 py-1.5 font-semibold transition {{ $activeMode === 'ALL' ? 'bg-navy-900 text-cyan-300 shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ __('messages.dashboard.map.filter_all') }}
                    </a>
                    <a href="{{ route('tracking.index', ['mode' => 'SEA']) }}"
                       class="rounded-lg px-3 py-1.5 font-semibold transition {{ $activeMode === 'SEA' ? 'bg-navy-900 text-cyan-300 shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ __('messages.dashboard.map.filter_sea') }}
                    </a>
                    <a href="{{ route('tracking.index', ['mode' => 'AIR']) }}"
                       class="rounded-lg px-3 py-1.5 font-semibold transition {{ $activeMode === 'AIR' ? 'bg-navy-900 text-cyan-300 shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ __('messages.dashboard.map.filter_air') }}
                    </a>
                    <a href="{{ route('tracking.index', ['mode' => 'ROAD']) }}"
                       class="rounded-lg px-3 py-1.5 font-semibold transition {{ $activeMode === 'ROAD' ? 'bg-navy-900 text-cyan-300 shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ __('messages.dashboard.map.filter_road') }}
                    </a>
                </div>

                <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-800">
                    {{ $shipments->total() }} {{ __('messages.shipments_page.stat_active') }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_shipment') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_mode') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_route') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_eta') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_status') }}</th>
                        <th class="px-6 py-3.5 text-right">{{ __('messages.shipments_page.th_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $shipment)
                        <tr class="transition hover:bg-slate-50/80">
                            <!-- Shipment & Vehicle -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-navy-900 px-2 py-0.5 text-[10px] font-bold text-cyan-300 font-mono">
                                        {{ $shipment->carrier?->scac ?? 'SCAC' }}
                                    </span>
                                    <a href="{{ route('tracking.show', $shipment) }}" class="font-bold text-cyan-800 hover:underline">
                                        {{ $shipment->shipment_number }}
                                    </a>
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $shipment->transport_mode_icon }} {{ $shipment->vehicle_identifier ?? $shipment->vessel_name ?? 'Fleet Vehicle' }} · <span class="font-mono">{{ $shipment->container_number }}</span>
                                </div>
                            </td>

                            <!-- Transport Mode Badge -->
                            <td class="whitespace-nowrap px-6 py-4">
                                @php
                                    $modeStyle = match($shipment->transport_mode) {
                                        'AIR' => 'bg-sky-50 text-sky-700 ring-sky-300',
                                        'ROAD' => 'bg-emerald-50 text-emerald-700 ring-emerald-300',
                                        default => 'bg-cyan-50 text-cyan-700 ring-cyan-300',
                                    };
                                    $modeLabel = __('messages.transport_modes.labels.' . $shipment->transport_mode) ?? $shipment->transport_mode_label;
                                @endphp
                                <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $modeStyle }}">
                                    {{ $shipment->transport_mode_icon }} {{ $modeLabel }}
                                </span>
                            </td>

                            <!-- Destination & Route -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-bold text-teal-700">
                                    {{ $shipment->destination?->name ?? $shipment->destination?->city ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $shipment->destination?->city }}, {{ $shipment->destination?->country_code }} ({{ $shipment->destination?->unlocode }})
                                </div>
                            </td>

                            <!-- ETA & Date -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-xs font-semibold text-slate-800">
                                    📅 {{ $shipment->estimated_arrival_at?->format('d M Y, H:i') ?? '—' }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $shipment->status === 'IN_TRANSIT' ? 'bg-cyan-50 text-cyan-800 ring-1 ring-cyan-200' : 'bg-slate-100 text-slate-700' }}">
                                    @if($shipment->status === 'IN_TRANSIT')
                                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-600 animate-pulse"></span>
                                    @endif
                                    {{ __('messages.statuses.' . $shipment->status) ?? str_replace('_', ' ', $shipment->status) }}
                                </span>
                                @if($shipment->delay_status && $shipment->delay_status !== 'ON_TIME')
                                    <div class="mt-1 text-[11px] font-semibold text-rose-600">
                                        ⚠️ {{ __('messages.delays.' . $shipment->delay_status) ?? str_replace('_', ' ', $shipment->delay_status) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Action Button to Open Map -->
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <a href="{{ route('tracking.show', $shipment) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700 hover:shadow-md">
                                    <span>{{ __('messages.tracking_page.btn_open_map') }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                {{ __('messages.shipments_page.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $shipments->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
