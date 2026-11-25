@extends('layouts.app')

@section('title', __('messages.page_titles.reports'))
@section('page-title', __('messages.nav.reports'))

@section('content')
<div class="space-y-5 p-5 md:p-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">{{ __('messages.reports_page.title') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('messages.reports_page.sub') }}</p>
    </div>
    <section class="grid gap-5 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-bold text-slate-900">{{ __('messages.reports_page.by_status') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($statusBreakdown as $row)
                    <div class="flex items-center justify-between py-3 text-sm">
                        <span>{{ __('messages.statuses.' . $row->status) ?? str_replace('_', ' ', $row->status) }}</span>
                        <strong>{{ $row->total }}</strong>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">{{ __('messages.reports_page.no_data') }}</p>
                @endforelse
            </div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-bold text-slate-900">{{ __('messages.reports_page.by_carrier') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($carrierBreakdown as $row)
                    <div class="flex items-center justify-between py-3 text-sm">
                        <span>{{ $row->carrier?->name ?? '—' }}</span>
                        <strong>{{ $row->total }}</strong>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">{{ __('messages.reports_page.no_data') }}</p>
                @endforelse
            </div>
        </article>
    </section>
    <p class="text-xs text-slate-500">{{ __('messages.reports_page.disclaimer') }}</p>
</div>
@endsection
