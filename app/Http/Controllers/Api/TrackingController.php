<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ExternalApiService;
use App\Services\TrackingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TrackingService $trackingService,
        protected ExternalApiService $externalApiService
    ) {
    }

    public function index(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        $events = $this->trackingService->history($shipment);
        $telemetry = $this->trackingService->getRouteTelemetry($shipment);
        $weather = $this->externalApiService->fetchWeatherForCoordinates(
            $telemetry['current_position']['latitude'],
            $telemetry['current_position']['longitude']
        );

        return response()->json([
            'shipment_number' => $shipment->shipment_number,
            'status' => $shipment->status,
            'delay_status' => $shipment->delay_status,
            'telemetry' => $telemetry,
            'weather' => $weather,
            'events' => $events,
        ]);
    }

    public function store(Request $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('update', $shipment);

        $event = $this->trackingService->record($shipment, $request->all(), $request->user());

        return response()->json($event, 201);
    }
}
