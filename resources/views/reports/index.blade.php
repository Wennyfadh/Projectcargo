@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="space-y-5 p-5 md:p-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Operations reports</h2>
        <p class="mt-1 text-sm text-slate-500">A current snapshot of shipment volume by status and carrier.</p>
    </div>
    <section class="grid gap-5 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-bold text-slate-900">Shipments by status</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($statusBreakdown as $row)
                    <div class="flex items-center justify-between py-3 text-sm"><span>{{ str_replace('_', ' ', $row->status) }}</span><strong>{{ $row->total }}</strong></div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">No shipment data available.</p>
                @endforelse
            </div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-bold text-slate-900">Shipments by carrier</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($carrierBreakdown as $row)
                    <div class="flex items-center justify-between py-3 text-sm"><span>{{ $row->carrier?->name ?? 'Unknown carrier' }}</span><strong>{{ $row->total }}</strong></div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">No carrier shipment data available.</p>
                @endforelse
            </div>
        </article>
    </section>
    <p class="text-xs text-slate-500">Reports are based on shipment records visible to your account.</p>
</div>
@endsection
