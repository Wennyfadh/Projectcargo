@extends('layouts.app')

@section('title', 'Exceptions')
@section('page-title', 'Exceptions')

@section('content')
<div class="space-y-5 p-5 md:p-8">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Shipment exceptions</h2>
        <p class="mt-1 text-sm text-slate-500">Review reported disruptions and resolution progress.</p>
    </div>
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Shipment</th><th class="px-5 py-3">Exception</th><th class="px-5 py-3">Severity</th><th class="px-5 py-3">Impact</th><th class="px-5 py-3">Detected</th><th class="px-5 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exceptions as $exception)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-5 py-4"><a href="{{ route('tracking.show', $exception->shipment) }}" class="font-semibold text-cyan-700 hover:underline">{{ $exception->shipment?->shipment_number }}</a><div class="mt-1 text-xs text-slate-500">{{ $exception->shipment?->customer?->company_name }}</div></td>
                            <td class="min-w-56 px-5 py-4"><p class="font-semibold">{{ str_replace('_', ' ', $exception->exception_type) }}</p><p class="mt-1 text-xs text-slate-500">{{ $exception->description }}</p></td>
                            <td class="whitespace-nowrap px-5 py-4">{{ $exception->severity }}</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ $exception->delay_impact_hours }} hrs</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ $exception->detected_at?->format('M d, Y H:i') }}</td>
                            <td class="whitespace-nowrap px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $exception->status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ str_replace('_', ' ', $exception->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No exceptions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exceptions->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $exceptions->links() }}</div>@endif
    </section>
</div>
@endsection
