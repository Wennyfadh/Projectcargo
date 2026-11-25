@extends('layouts.app')

@section('title', __('messages.page_titles.carriers'))
@section('page-title', __('messages.nav.carriers'))

@section('content')
<div class="space-y-6 p-5 md:p-8">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy-950 via-navy-900 to-ocean-600 p-6 text-white shadow-lg">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-cyan-300 backdrop-blur">
                    <span>⚓ {{ __('messages.carriers_page.badge') }}</span>
                    <span>•</span>
                    <span>{{ __('messages.carriers_page.port_hub') }}</span>
                </div>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-white md:text-3xl">{{ __('messages.carriers_page.banner_title') }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-300">
                    {{ __('messages.carriers_page.banner_sub') }}
                </p>
            </div>
            <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-center backdrop-blur">
                <span class="block text-2xl font-extrabold text-cyan-400">{{ $carriers->total() }}</span>
                <span class="text-xs uppercase tracking-wider text-slate-300">{{ __('messages.carriers_page.partner_carriers') }}</span>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 h-48 w-48 rounded-full bg-cyan-500/20 blur-2xl"></div>
    </div>

    <!-- Carrier Cards Grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($carriers as $carrier)
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-navy-900 font-mono text-sm font-black text-cyan-400 shadow-sm">
                            {{ $carrier->scac }}
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $carrier->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $carrier->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $carrier->is_active ? __('messages.carriers_page.status_active') : __('messages.carriers_page.status_inactive') }}
                        </span>
                    </div>

                    <h3 class="mt-4 text-base font-bold text-slate-900 group-hover:text-ocean-600">
                        {{ $carrier->name }}
                    </h3>

                    <div class="mt-3 space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">✉️</span>
                            <span class="truncate">{{ $carrier->contact_email ?? 'operations@carrier.com' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">📞</span>
                            <span>{{ $carrier->contact_phone ?? '+62-21-5000' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 border-t border-slate-100 pt-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-slate-400">{{ __('messages.carriers_page.partner_carriers') }}</span>
                            <div class="text-sm font-extrabold text-navy-900">{{ $carrier->shipments_count }} {{ __('messages.shipments_page.stat_active') }}</div>
                        </div>
                        @if($carrier->website_url)
                            <a href="{{ $carrier->website_url }}" target="_blank" rel="noopener noreferrer"
                               class="rounded-lg bg-slate-50 px-2.5 py-1.5 text-xs font-bold text-ocean-700 hover:bg-ocean-50">
                                {{ __('messages.carriers_page.tracking_portal') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Carrier Detailed Table -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <h3 class="text-sm font-bold text-slate-800">{{ __('messages.carriers_page.banner_title') }}</h3>
            <p class="text-xs text-slate-500">{{ __('messages.carriers_page.banner_sub') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-3.5">SCAC</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.th_carrier') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.carriers_page.contact_email') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.carriers_page.tracking_portal') }}</th>
                        <th class="px-6 py-3.5">{{ __('messages.shipments_page.stat_active') }}</th>
                        <th class="px-6 py-3.5 text-right">{{ __('messages.shipments_page.th_status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($carriers as $carrier)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="rounded-lg bg-navy-900 px-2.5 py-1 font-mono text-xs font-bold text-cyan-300">
                                    {{ $carrier->scac }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 font-bold text-slate-800">
                                {{ $carrier->name }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                <div>{{ $carrier->contact_email }}</div>
                                <div class="mt-0.5 text-slate-400">{{ $carrier->contact_phone }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs">
                                @if($carrier->website_url)
                                    <a href="{{ $carrier->website_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-ocean-600 hover:underline">
                                        {{ parse_url($carrier->website_url, PHP_URL_HOST) }} ↗
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-bold text-cyan-800 border border-cyan-200">
                                    🚢 {{ $carrier->shipments_count }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $carrier->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $carrier->is_active ? __('messages.carriers_page.status_active') : __('messages.carriers_page.status_inactive') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">{{ __('messages.reports_page.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($carriers->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                {{ $carriers->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
