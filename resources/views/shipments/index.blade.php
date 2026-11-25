@extends('layouts.app')

@section('title', __('messages.page_titles.shipments'))
@section('page-title', __('messages.nav.shipments'))

@section('content')
<div class="space-y-6 p-5 md:p-8">
    <!-- Header Banner with Brand Theme -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy-950 via-navy-900 to-ocean-600 p-6 text-white shadow-lg">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-cyan-300 backdrop-blur">
                    <span>☕ PT Mayora Indah Tbk</span>
                    <span>•</span>
                    <span>{{ __('messages.shipments_page.badge') }}</span>
                </div>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-white md:text-3xl">{{ __('messages.shipments_page.banner_title') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-300">
                    {{ __('messages.shipments_page.banner_sub') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-center backdrop-blur">
                    <span class="block text-2xl font-extrabold text-cyan-400">{{ $shipments->total() }}</span>
                    <span class="text-xs uppercase tracking-wider text-slate-300">{{ __('messages.shipments_page.stat_active') }}</span>
                </span>
            </div>
        </div>
        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-ocean-500/20 blur-2xl"></div>
    </div>

    <!-- Filter & Search Toolbar -->
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('shipments.index') }}" class="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_auto]">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('messages.shipments_page.quick_search') }}</label>
                <div class="relative">
                    <input name="search" value="{{ request('search') }}" placeholder="{{ __('messages.shipments_page.search_placeholder') }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pl-10 text-sm font-medium text-slate-800 placeholder-slate-400 outline-none transition focus:border-ocean-500 focus:bg-white focus:ring-4 focus:ring-ocean-500/10">
                    <span class="absolute left-3.5 top-3 text-slate-400">🔍</span>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('messages.shipments_page.filter_status') }}</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none transition focus:border-ocean-500 focus:bg-white focus:ring-4 focus:ring-ocean-500/10">
                    <option value="">{{ __('messages.shipments_page.all_statuses') }}</option>
                    @foreach(['CREATED' => __('messages.statuses.CREATED'), 'BOOKED' => __('messages.statuses.BOOKED'), 'PICKED_UP' => __('messages.statuses.PICKED_UP'), 'IN_TRANSIT' => __('messages.statuses.IN_TRANSIT'), 'DELIVERED' => __('messages.statuses.DELIVERED'), 'CANCELLED' => __('messages.statuses.CANCELLED')] as $val => $lbl)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('messages.shipments_page.filter_carrier') }}</label>
                <select name="carrier_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-800 outline-none transition focus:border-ocean-500 focus:bg-white focus:ring-4 focus:ring-ocean-500/10">
                    <option value="">{{ __('messages.shipments_page.all_carriers') }}</option>
                    @foreach($carriers as $c)
                        <option value="{{ $c->id }}" @selected((string) request('carrier_id') === (string) $c->id)>
                            {{ $c->name }} ({{ $c->scac }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-ocean-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-ocean-700 focus:ring-4 focus:ring-ocean-500/20">
                    {{ __('messages.shipments_page.btn_filter') }}
                </button>
                <a href="{{ route('shipments.index') }}" class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">
                    {{ __('messages.shipments_page.btn_reset') }}
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
                    {{ $shipments->count() }} / {{ $shipments->total() }} {{ __('messages.shipments_page.stat_active') }}
                </p>
            </div>
            <p class="text-xs text-slate-500">{{ __('messages.app_subtitle') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_shipment') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.dashboard.recent_shipments.th_customer') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_carrier') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_route') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_eta') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_status') }}</th>
                        <th class="px-6 py-3.5 text-right">{{ __('messages.shipments_page.th_actions') }}</th>
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
                                    {{ $shipment->transport_mode_icon }} {{ $shipment->vehicle_identifier ?? $shipment->vessel_name ?? 'Fleet' }} ({{ $shipment->voyage_number ?? '—' }})
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
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold border {{ $shipment->delay_status === 'HIGH_DELAY' ? 'bg-rose-100 text-rose-700 border-rose-300' : ($shipment->delay_status === 'MEDIUM_DELAY' ? 'bg-amber-100 text-amber-800 border-amber-300' : ($shipment->delay_status === 'LOW_DELAY' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300')) }}">
                                        {{ __('messages.delays.' . $shipment->delay_status) ?? str_replace('_', ' ', $shipment->delay_status) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Lifecycle Status -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold text-white shadow-sm {{ $shipment->status === 'DELIVERED' ? 'bg-emerald-500' : ($shipment->status === 'IN_TRANSIT' ? 'bg-ocean-500' : ($shipment->status === 'PICKED_UP' ? 'bg-amber-500' : ($shipment->status === 'BOOKED' ? 'bg-indigo-500' : 'bg-slate-500'))) }}">
                                    {{ __('messages.statuses.' . $shipment->status) ?? str_replace('_', ' ', $shipment->status) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <a href="{{ route('tracking.show', $shipment) }}" class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-ocean-700 transition hover:bg-ocean-600 hover:text-white">
                                    <span>{{ __('messages.shipments_page.btn_track') }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-500">
                                {{ __('messages.shipments_page.no_results') }}
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
