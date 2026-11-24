<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(protected ExternalApiService $externalApiService)
    {
    }

    public function index(): JsonResponse
    {
        $shipments = Shipment::query()->with(['carrier', 'customer', 'origin', 'destination', 'currentLocation'])->get();

        $metrics = [
            'total_shipments' => $shipments->count(),
            'in_transit' => $shipments->where('status', 'IN_TRANSIT')->count(),
            'delivered' => $shipments->where('status', 'DELIVERED')->count(),
            'delayed' => $shipments->whereIn('delay_status', ['LOW_DELAY', 'MEDIUM_DELAY', 'HIGH_DELAY'])->count(),
            'open_exceptions' => $shipments->sum(fn ($shipment) => $shipment->exceptions()->whereIn('status', ['OPEN', 'INVESTIGATING'])->count()),
            'on_time_rate' => $shipments->count() > 0
                ? round(($shipments->where('delay_status', 'ON_TIME')->count() / $shipments->count()) * 100, 2)
                : 0,
            'exception_rate' => $shipments->count() > 0
                ? round(($shipments->filter(fn ($shipment) => $shipment->exceptions()->exists())->count() / $shipments->count()) * 100, 2)
                : 0,
        ];

        $statusBreakdown = Shipment::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->get();
        $carrierBreakdown = Shipment::query()->selectRaw('carrier_id, COUNT(*) as total')->groupBy('carrier_id')->with('carrier')->get();

        $weather = $this->externalApiService->fetchMarineWeather($shipments->first()?->origin ?? $shipments->first()?->destination);
        $news = $this->externalApiService->fetchGlobalNews();

        return response()->json([
            'metrics' => $metrics,
            'status_breakdown' => $statusBreakdown,
            'carrier_breakdown' => $carrierBreakdown,
            'weather' => $weather,
            'news' => $news,
        ]);
    }
}
